Setting up or Fixing File Permissions
=====================================

The ``var/`` directory in a Symfony application is used to store generated
files (cache and logs) and file-based cache files. In the production
environment, you often need to add explicit permissions to let Symfony
write files into this directory.

.. tip::

    In dev environments, ``umask()`` is used in ``bin/console`` and
    ``public/index.php`` to make sure the directory is writable. However,
    this is not a safe method and should not be used in production.

Setting up File Permissions in Production
-----------------------------------------

This section describes the required permissions. See
:ref:`the next section <setup-file-permissions>` on how to add the
permissions.

* The ``var/log/`` directory must exist and must be writable by both your
  web server user and the terminal user;
* The ``var/cache/`` directory must be writable by the terminal user (the
  user running ``cache:warmup`` or ``cache:clear``). It must also be writable
  by the web server user if you're using the
  :doc:`filesystem cache provider </components/cache/adapters/filesystem_adapter>`;
  or Doctrine query result cache.

.. _setup-file-permissions:

Configuring File Permissions on Linux and macOS System
------------------------------------------------------

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
    # if this doesn't work, try adding `-n` option

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
``web/app.php`` and ``web/app_dev.php`` files::

    umask(0002); // This will let the permissions be 0775

    // or

    umask(0000); // This will let the permissions be 0777

.. caution::

    Changing the ``umask`` is not thread-safe, so the ACL methods are recommended
    when they are available.

.. _`enable ACL support`: https://help.ubuntu.com/community/FilePermissionsACLs
