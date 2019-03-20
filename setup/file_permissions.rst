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
