Setting up or Fixing File Permissions
=====================================

Symfony generates certain files in the ``var/`` directory of your project when
running the application. In the ``dev`` :ref:`environment <configuration-environments>`,
the ``bin/console`` and ``public/index.php`` files use ``umask()`` to make sure
that the directory is writable. This means that you don't need to configure
permissions when developing the application in your local machine.

However, using ``umask()`` is not considered safe in production. That's why you
often need to configure some permissions explicitly in your production servers
as explained in this article.

Permissions Required by Symfony Applications
--------------------------------------------

These are the permissions required to run Symfony applications:

* The ``var/log/`` directory must exist and must be writable by both your
  web server user and the terminal user;
* The ``var/cache/`` directory must be writable by the terminal user (the
  user running ``cache:warmup`` or ``cache:clear`` commands);
* The ``var/cache/`` directory must be writable by the web server user if you use
  a :doc:`filesystem-based cache </cache/adapters/filesystem_adapter>`.

.. _setup-file-permissions:

Configuring Permissions for Symfony Applications
------------------------------------------------

On Linux and macOS systems, if your web server user is different from your
command line user, you need to configure permissions properly to avoid issues.
There are several ways to achieve that, listed here from the most to the least
recommended:

* If your system supports **ACL** (most Linux distributions do), use it. It
  grants permissions per user, without changing any group membership;
* If you control the configuration of your web server or container, run both
  sides as the **same user**. This is usually the case in container-based
  setups (e.g. Docker);
* If ACL is unavailable (e.g. on NFS) **and** you need two distinct users, use
  a shared group.

1. Using ACL on a System that Supports ``setfacl`` (Linux/BSD)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using Access Control Lists (ACL) permissions is the safest and
recommended method to make the ``var/`` directory writable. You may need to
install ``setfacl`` and `enable ACL support`_ on your disk partition before
using this method. Then, use the following script to determine your web
server user and grant the needed permissions:

.. code-block:: terminal

    $ HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx|[c]addy|[f]rankenphp' | grep -v root | head -1 | cut -d\  -f1)

    # if the following commands don't work, try adding `-n` option to `setfacl`

    # set permissions for future files and folders
    $ sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
    # set permissions on the existing files and folders
    $ sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var

.. tip::

    If ``$HTTPDUSER`` is empty (e.g. the web server is not running or the
    script doesn't work on your system), replace ``$HTTPDUSER`` with the web
    server user manually. Check your web server's configuration or use the
    common default user names: ``www-data`` for Apache/nginx on Debian/Ubuntu,
    ``apache`` or ``nginx`` on RHEL/Fedora, ``caddy`` for Caddy, and
    ``frankenphp`` for FrankenPHP.

Both of these commands assign permissions for the system user (the one
running these commands) and the web server user.

.. note::

    ``setfacl`` isn't available on NFS mount points. However, storing cache and
    logs over NFS is strongly discouraged for performance reasons. If you
    can't use ACL, see the last section.

2. Use the Same User for the CLI and the Web Server
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If the same user runs both the commands and the web server, there is no
permission problem left to solve: every file has a single owner. This is why
container-based setups (e.g. Docker), where a single user runs everything,
usually don't need any of the other methods.

Edit your web server configuration (commonly ``httpd.conf`` or ``apache2.conf``
for Apache) and set its user to be the same as your CLI user (e.g. for Apache,
update the ``User`` and ``Group`` directives).

When using PHP-FPM, set the ``user`` and ``group`` directives of your pool
configuration instead. Keep ``listen.owner`` and ``listen.group`` set to the
web server user, so that it can still connect to the socket:

.. code-block:: ini

    ; /etc/php/8.5/fpm/pool.d/www.conf
    [www]
    user = deployer
    group = deployer

    listen.owner = www-data
    listen.group = www-data

Then give that user the ownership of the ``var/`` directory and restart PHP-FPM:

.. code-block:: terminal

    $ sudo chown -R deployer:deployer var
    # the service name depends on your system (e.g. php-fpm on RHEL/Fedora)
    $ sudo systemctl restart php8.5-fpm

.. danger::

    If this solution is used in a production server, be sure this user only has
    limited privileges (no access to private data or servers, execution of
    unsafe binaries, etc.) as a compromised server would give those privileges
    to the hacker. Use a user dedicated to the application, not your personal
    account.

3. Without Using ACL
~~~~~~~~~~~~~~~~~~~~

If ACL is not available on your system (e.g. on NFS mount points), you can get
a similar result using only standard Linux commands. Two variants are possible,
depending on whether the web server user and your terminal user can share a
group. Prefer the first one, as the second makes the files world-writable.

Both variants rely on the `umask`_, which defines the permissions of
**newly created** files. This matters because the problem is not the files
that already exist, but the thousands of files recreated by every
``cache:clear``. The usual ``umask`` of ``0022`` creates files as ``0644``,
i.e. read-only for everyone but their owner.

Variant A: Using a Shared Group (Recommended)
.............................................

Put both users in a dedicated group, give that group the ownership of ``var/``
and make new files inherit it. Use the same script as above to determine your
web server user:

.. code-block:: terminal

    $ HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx|[c]addy|[f]rankenphp' | grep -v root | head -1 | cut -d\  -f1)

    # use a dedicated group instead of the personal group of your terminal user;
    # otherwise, the web server user could read every file of that group
    $ sudo groupadd symfony
    $ sudo usermod -aG symfony "$HTTPDUSER"
    $ sudo usermod -aG symfony $(whoami)

    # only var/ is shared, the rest of the project is left untouched
    $ sudo chgrp -R symfony var

    # 2775 = setgid + rwx for the owner + rwx for the group + r-x for the others
    # the setgid bit (2) makes new entries inherit the group of their parent
    # directory, instead of the primary group of the user who created them
    $ sudo find var -type d -exec chmod 2775 {} \;

    # 664 = rw- for the owner + rw- for the group + r-- for the others
    $ sudo find var -type f -exec chmod 664 {} \;

The group digit (``7`` and ``6`` above) is the one that solves the problem: it
must allow the group to write. The usual ``755`` and ``644`` permissions only
grant ``r-x`` and ``r--`` to the group, which is why the two users can't write
to each other's files.

The `setgid bit`_ replaces the inheritance provided by ACL. Without it, files
created by the web server would get its own primary group, and your terminal
user couldn't write to them. With it, permissions stay correct after each
``cache:clear``.

The setgid bit only propagates the *group*, never the write permission, so you
must also set a ``umask`` of ``0002`` on both sides.

For the terminal user, configure it at the session level rather than in
``~/.bashrc`` or ``~/.zshrc``, which are not read by cron jobs and most
non-interactive shells used by deployment scripts. On Debian/Ubuntu, add this
line to ``/etc/pam.d/common-session`` and
``/etc/pam.d/common-session-noninteractive``; on RHEL/Fedora, add it to
``/etc/pam.d/system-auth``. It changes the ``umask`` of every user on the
machine:

.. code-block:: text

    session optional pam_umask.so umask=0002

For PHP-FPM, set the `UMask`_ option in a systemd drop-in file created with
``sudo systemctl edit php8.5-fpm``:

.. code-block:: ini

    [Service]
    UMask=0002

Group membership and ``umask`` are read when the process starts, so restart
PHP-FPM to apply them. Your terminal user must also open a new session (log out
and log back in) for its new group membership to take effect:

.. code-block:: terminal

    $ sudo systemctl restart php8.5-fpm

To check the result, load any page of your application so that PHP-FPM creates
some cache files, and then run these commands as your terminal user:

.. code-block:: terminal

    # files created by the web server must belong to the "symfony" group
    # and be group-writable (-rw-rw-r-- ... symfony)
    $ ls -l var/cache/prod/

    # must succeed without any "Permission denied" error
    $ APP_ENV=prod php bin/console cache:clear

Variant B: World-Writable Files
...............................

If the two users can't share a group (e.g. on a shared host where you can't run
``usermod``), the only remaining option is to make the files writable by
everyone. Compared to the ``0002`` used above, a ``umask`` of ``0000`` also
grants the write permission to the *others*, i.e. to every user and process of
the machine, and not only to the members of the shared group.

Put the following lines at the beginning of the ``bin/console`` and
``public/index.php`` files::

    // the last 0 (for the others) is what makes the files world-writable
    umask(0000); // new directories: 0777, new files: 0666

The ``umask`` only applies to new files, so also make the existing contents
of ``var/`` world-writable once (e.g. with ``chmod -R a+rwX var``).

Setting the ``umask`` from PHP also works for variant A, as an alternative to
configuring it for each process::

    // makes new files group-writable, so that the web server user and the
    // terminal user can write to the files created by each other
    umask(0002); // new directories: 0775, new files: 0664

.. warning::

    Changing the ``umask`` is not thread-safe, so the ACL method is recommended
    when it is available. A ``umask`` of ``0000`` makes the cache and log files
    writable by **any** user or process on the machine. As Symfony includes and
    executes the PHP files it compiles in ``var/cache/``, anything able to write
    there can run arbitrary code, so only use it when no other method is
    possible.

.. _`enable ACL support`: https://help.ubuntu.com/community/FilePermissionsACLs
.. _`umask`: https://en.wikipedia.org/wiki/Umask
.. _`setgid bit`: https://en.wikipedia.org/wiki/Setuid#setgid_on_directories
.. _`UMask`: https://www.freedesktop.org/software/systemd/man/systemd.exec.html#UMask=
