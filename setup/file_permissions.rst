Setting up or Fixing File Permissions
=====================================

In Symfony 3.x, you needed to do some extra work to make sure that your cache directory
was writable. But that is no longer true! In Symfony 4, everything works automatically:

* In the ``dev`` environment, ``umask()`` is used in ``bin/console`` and ``public/index.php``
  so that any created files are writable by everyone.

* In the ``prod`` environment (i.e. when ``APP_ENV`` is ``prod`` and ``APP_DEBUG``
  is ``0``), as long as you run ``php bin/console cache:warmup``, no cache files
  will need to be written to disk at runtime. The only exception is when using
  a filesystem-based cache, such as Doctrine's query result cache or Symfony's
  cache with a filesystem provider configured.

* In all environments, the log directory (``var/log/`` by default) must exist
  and be writable by your web server user and terminal user. One way this can
  be done is by using ``chmod -R 777 var/log/``. Be aware that your logs are
  readable by any user on your production system.

Filesystem-based cache
~~~~~~~~~~~~~~~~~~~~~~

If you're using a filesystem-based cache such as Doctrine's query result cache, with ``APP_DEBUG`` is ``0`` you have to
change the umask so that the cache directory is group-writable or world-writable (depending
if the web server user and the command line user are in the same group or not).

To achieve this, update ``bin/console``, and ``public/index.php`` files:

find this section::

    if ($_SERVER['APP_DEBUG']) {
        umask(0000);

        Debug::enable();
    }

..

Exemple to force groupe-writable when ``APP_DEBUG`` is ``0``::

    if ($_SERVER['APP_DEBUG']) {
        umask(0000);

        Debug::enable();
    } else {
        umask(0002);
    }

..
