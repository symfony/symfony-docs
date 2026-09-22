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

* If your system supports **ACL** (most Linux distributions do), use it (see
  the first method below). It is the safest one, because the permissions are
  granted on the ``var/`` directory only;
* If you control the configuration of your web server or your container, making
  both sides run as the **same user** removes the problem entirely. This is the
  default in container-based setups (Docker, FrankenPHP);
* If ACL is unavailable (e.g. on NFS) **and** you really need two distinct
  users, fall back to the last method, which relies on a shared group.

1. Using ACL on a System that Supports ``setfacl`` (Linux/BSD)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using Access Control Lists (ACL) permissions is the most safe and
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
    can't use ACL, see the next section.

2. Use the same User for the CLI and the Web Server
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If the same user runs both the commands and the web server, there is no
permission problem left to solve: every file has a single owner, and no
inheritance mechanism is needed. This is why container-based setups (Docker,
FrankenPHP), where a single user runs everything, usually don't need any of the
other methods.

Edit your web server configuration (commonly ``httpd.conf`` or ``apache2.conf``
for Apache) and set its user to be the same as your CLI user (e.g. for Apache,
update the ``User`` and ``Group`` directives).

When using PHP-FPM, set the ``user`` and ``group`` directives of your pool
configuration. Keep ``listen.owner`` and ``listen.group`` set to the web server
user, so that it can still reach the socket:

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
    $ sudo systemctl restart php-fpm

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

Both variants rely on the `umask`_, which defines the permissions of **newly
created** files. This matters because the problem is not the files that already
exist, but the thousands of files recreated by every ``cache:clear``. A default
``umask`` of ``0022`` creates files as ``0644``, i.e. read-only for everyone but
their owner.

Variant A: Using a Shared Group (recommended)
.............................................

Put both users in a dedicated group, give that group the ownership of ``var/``
and let new files inherit it. Use the same script as above to determine your
web server user:

.. code-block:: terminal

    $ HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx|[c]addy|[f]rankenphp' | grep -v root | head -1 | cut -d\  -f1)

    # a dedicated group, so that the web server user doesn't get access
    # to everything the terminal user can read
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

The middle digit is the one that solves the problem: it must allow the group to
write. The default permissions of ``755`` and ``644`` only grant ``r-x`` and
``r--`` to the group, which is precisely why the two users can't write to each
other's files.

The `setgid bit`_ is what replaces the inheritance provided by ACL. Without it,
files created by the web server would keep its own primary group, and your
terminal user could not write to them. With it, permissions stay correct after
each ``cache:clear``.

The setgid bit only propagates the *group*, never the write permission, so you
must also set a ``umask`` of ``0002`` on both sides.

For the terminal user, configure it at the session level rather than in
``~/.bashrc`` or ``~/.zshrc``, which are not read by the non-interactive shells
used by deployment scripts and cron jobs. Add this line to
``/etc/pam.d/common-session`` on Debian/Ubuntu, or to
``/etc/pam.d/system-auth`` on RHEL/Fedora:

.. code-block:: text

    session optional pam_umask.so umask=0002

For PHP-FPM, set the `UMask`_ option in a systemd drop-in file created with
``sudo systemctl edit php-fpm``:

.. code-block:: ini

    [Service]
    UMask=0002

Group membership and ``umask`` are read when the process starts, so restart
your web server to apply them. Your terminal user must also open a new session
(log out and log back in) for its new group membership to take effect:

.. code-block:: terminal

    $ sudo systemctl restart php-fpm

Check the result by creating a file as the web server user and making sure your
terminal user can write to it:

.. code-block:: terminal

    $ sudo -u "$HTTPDUSER" touch var/cache/test
    $ ls -l var/cache/test
    $ rm var/cache/test

The file must belong to the ``symfony`` group and be group-writable
(``-rw-rw-r-- ... symfony``), and the ``rm`` command must succeed.

.. warning::

    Adding the web server user to a group gives it access to **every** file
    readable by that group, not only to ``var/``. This is why a dedicated group
    is used above instead of the personal group of your terminal user. Make sure
    your home directory is not traversable by the web server user
    (``chmod 750 ~``). ACL doesn't have this drawback, as permissions are set on
    ``var/`` only, which is why it remains the recommended method.

Variant B: World-Writable Files
...............................

If the two users can't share a group (e.g. on a shared host where you can't run
``usermod``), the only remaining option is to make the files writable by
everyone. Compared to the ``0002`` used above, a ``umask`` of ``0000`` also
grants the write permission to the *others*, i.e. to every user and process of
the machine, and not only to the members of the shared group.

Put the following lines at the beginning of the ``bin/console`` and
``public/index.php`` files::

    // the 0 for the others is what makes the files world-writable
    umask(0000); // new directories: 0777, new files: 0666

Setting the ``umask`` from PHP also works for variant A, as an alternative to
configuring it for each process::

    // makes new files writable by the group, so that the other user
    // can delete the cache files created by the first one
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
