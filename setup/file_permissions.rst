Setting up or Fixing File Permissions
=====================================

In Symfony 3.x, you needed to do some extra work to make sure that your cache directory
was writable. But that is no longer true! In Symfony 4, everything works automatically:

* In the ``dev`` environment, ``umask()`` is used in ``bin/console`` and ``web/index.php``
  so that any created files are writable by everyone.

* In the ``prod`` environment (i.e. when ``APP_ENV`` is ``prod`` and ``APP_DEBUG``
  is ``0``), as long as you run ``php bin/console cache:warmup``, no cache files
  will need to be written to disk at runtime.

.. note::

    If you decide to store log files on disk, you *will* need to make sure your
    logs directory (e.g. ``var/log/``) is writable by your web server user and
    terminal user. One way this can be done is by using ``chmod 777 -R var/log/``.
    Just be aware that your logs are readable by any user on your production system.
