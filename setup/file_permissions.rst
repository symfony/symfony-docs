Setting up or Fixing File Permissions
=====================================

One important Symfony requirement is that the ``app/cache`` and ``app/logs``
directories must be writable both by the web server and the command line user.

On Linux and macOS systems, if your web server user is different from your
command line user, you need to configure permissions properly to avoid issues.
There are several ways to achieve that:

1. Use the same User for the CLI and the Web Server
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Edit your web server configuration (commonly ``httpd.conf`` or ``apache2.conf``
for Apache) and set its user to be the same as your CLI user (e.g. for Apache,
update the ``User`` and ``Group`` directives).

.. caution::

    If this solution is used in a production server, be sure this user only has
    limited privileges (no access to private data or servers, execution of
    unsafe binaries, etc.) as a compromised server would give to the hacker
    those privileges.

2. Using ACL on a System that Supports ``chmod +a`` (macOS)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On macOS systems, the ``chmod`` command supports the ``+a`` flag to define an
ACL. Use the following script to determine your web server user and grant the
needed permissions:

.. code-block:: terminal

    $ rm -rf app/cache/*
    $ rm -rf app/logs/*

    $ HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
    $ sudo chmod +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
    $ sudo chmod +a "$(whoami) allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs

3. Using ACL on a System that Supports ``setfacl`` (Linux/BSD)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Most Linux and BSD distributions don't support ``chmod +a``, but do support
another utility called ``setfacl``. You may need to install ``setfacl`` and
`enable ACL support`_ on your disk partition before using it. Then, use the
following script to determine your web server user and grant the needed permissions:

.. code-block:: terminal

    $ HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
    # if this doesn't work, try adding `-n` option
    $ sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX app/cache app/logs
    $ sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX app/cache app/logs

.. note::

    The first ``setfacl`` command sets permissions for future files and folder,
    while the second one sets permissions on the existing files and folders.
    Both of these commands assign permissions for the system user and the Apache 
    user.

    ``setfacl`` isn't available on NFS mount points. However, storing cache and
    logs over NFS is strongly discouraged for performance reasons.

4. Without Using ACL
~~~~~~~~~~~~~~~~~~~~

If none of the previous methods work for you, change the umask so that the
cache and log directories are group-writable or world-writable (depending
if the web server user and the command line user are in the same group or not).
To achieve this, put the following line at the beginning of the ``app/console``,
``web/app.php`` and ``web/app_dev.php`` files::

    umask(0002); // This will let the permissions be 0775

    // or

    umask(0000); // This will let the permissions be 0777

.. note::

    Changing the umask is not thread-safe, so the ACL methods are recommended
    when they are available.

.. _`enable ACL support`: https://help.ubuntu.com/community/FilePermissionsACLs
