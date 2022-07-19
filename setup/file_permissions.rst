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
  a :doc:`filesystem-based cache </components/cache/adapters/filesystem_adapter>`.

.. _setup-file-permissions:

Configuring Permissions for Symfony Applications
------------------------------------------------

On Linux and macOS systems, if your web server user is different from your
command line user, you need to configure permissions properly to avoid issues.
There are several ways to achieve that:

1. Using ACL on a System that Supports ``setfacl`` (Linux/BSD)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using Access Control Lists (ACL) permissions is the most safe and
recommended method to make the ``var/`` directory writable. You may need to
install ``setfacl`` and `enable ACL support`_ on your disk partition before
using this method. Then, use the following script to determine your web
server user and grant the needed permissions:

.. code-block:: terminal

    $ HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)

    # if the following commands don't work, try adding `-n` option to `setfacl`

    # set permissions for future files and folders
    $ sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
    # set permissions on the existing files and folders
    $ sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var

Both of these commands assign permissions for the system user (the one
running these commands) and the web server user.

.. note::

    ``setfacl`` isn't available on NFS mount points. However, storing cache and
    logs over NFS is strongly discouraged for performance reasons.

2. Use the same User for the CLI and the Web Server
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Edit your web server configuration (commonly ``httpd.conf`` or ``apache2.conf``
for Apache) and set its user to be the same as your CLI user (e.g. for Apache,
update the ``User`` and ``Group`` directives).

.. caution::

    If this solution is used in a production server, be sure this user only has
    limited privileges (no access to private data or servers, execution of
    unsafe binaries, etc.) as a compromised server would give to the hacker
    those privileges.

3. Without Using ACL
~~~~~~~~~~~~~~~~~~~~

If none of the previous methods work for you, change the ``umask`` so that the
cache and log directories are group-writable or world-writable (depending
if the web server user and the command line user are in the same group or not).
To achieve this, put the following line at the beginning of the ``bin/console``,
and ``public/index.php`` files::

    umask(0002); // This will let the permissions be 0775

    // or

    umask(0000); // This will let the permissions be 0777

.. caution::

    Changing the ``umask`` is not thread-safe, so the ACL methods are recommended
    when they are available.

.. _`enable ACL support`: https://help.ubuntu.com/community/FilePermissionsACLs
