Setting up Permissions
----------------------

One common issue is that the ``app/cache`` and ``app/logs`` directories
must be writable both by the web server and the command line user. On
a UNIX system, if your web server user is different from your command
line user, you can run the following commands just once in your project
to ensure that permissions will be setup properly. Change ``www-data``
to the web server user and ``yourname`` to your command line user:

**1. Using ACL on a system that supports chmod +a**

.. code-block:: bash

    Many systems allow you to use the ``chmod +a`` command. Try this first,
    and if you get an error - try the next method:

    rm -rf app/cache/*
    rm -rf app/logs/*

    sudo chmod +a "www-data allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
    sudo chmod +a "yourname allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs

**2. Using Acl on a system that does not support chmod +a**

Some systems, like Ubuntu, don't support ``chmod +a``, but do support
another utility called ``setfacl``. On some systems, this will need to
be installed before using it:

.. code-block:: bash

    sudo setfacl -R -m u:www-data:rwx -m u:yourname:rwx app/cache app/logs
    sudo setfacl -dR -m u:www-data:rwx -m u:yourname:rwx app/cache app/logs

**3. Without using ACL**

If you don't have access to changing the ACL of the directories, you will
need to change the umask so that the cache and log directories will
be group-writable or world-writable (depending if the web server user
and the command line user are in the same group or not). To achieve
this, put the following line at the beginning of the ``app/console``,
``web/app.php`` and ``web/app_dev.php`` files:

.. code-block:: php

    umask(0002); // This will let the permissions be 0775

    // or

    umask(0000); // This will let the permissions be 0777

Note that using the ACL is recommended when you have access to them
on your server because changing the umask is not thread-safe.