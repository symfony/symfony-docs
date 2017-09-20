.. index::
   single: Tests

Performance
===========

Symfony is fast, right out of the box. Of course, if you really need speed, the
following performance checklists show how to make your application even faster.

.. index::
single: Performance; Byte code cache

Production Server Checklist
---------------------------

#. :ref:`Use the OPcache byte code cache <performance-use-opcache>`
#. :ref:`Configure OPcache for maximum performance <performance-configure-opcache>`
#. :ref:`Don't check PHP timestamps <performance-dont-check-timestamps>`
#. :ref:`Configure the PHP realpath Cache <performance-configure-realpath-cache>`
#. :ref:`Optimize Composer Autoloader <performance-optimize-composer-autoloader>`


.. _performance-use-opcache:

Use the OPcache byte code cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

OPcache stores the compiled PHP files to avoid having to recompile them for
every request. There are some `byte code caches`_ available, but as of PHP
5.5, PHP comes with `OPcache`_ built-in. For older versions, the most widely
used byte code cache is `APC`_.

.. _performance-configure-opcache:

Configure OPcache for maximum performance
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default OPcache configuration is not suited for Symfony application, so
it's recommended to change these settings as follows:

.. code-block:: ini

    ; php.ini
    ; maximum memory that OPcache can use to store compiled PHP files
    opcache.memory_consumption=256M

    ; maximum number of files that can be stored in the cache
    opcache.max_accelerated_files=20000

.. _performance-dont-check-timestamps:

Don't check PHP timestamps
~~~~~~~~~~~~~~~~~~~~~~~~~~

In production servers, PHP files should never change, unless a new application
version is deployed. However, by default OPcache checks if cached files have
changed their contents since caching them. This check introduces some overhead
that can be avoided as follows:

.. code-block:: ini

    ; php.ini

    ; after each deploy, call `opcache_reset()` or restart the web server
    ; to empty the cache and regenerate the cached files. Otherwise you won't
    ; see the updates made in the application
    opcache.validate_timestamps=0

.. note::

    The OPcache is different for the web server and the command console.
    You cannot clear the web server OPcache by executing some command
    in your terminal. You either need to restart the web server or call the
    ``opcache_reset()`` function via the web server (i.e. by having this in
    a script that you execute over the web).

.. _performance-configure-realpath-cache:

Configure the PHP realpath Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a relative path is transformed into its real and absolute path, PHP
caches the result to improve performance. The default config of this cache
is not suited for applications that open many PHP files, such as Symfony.
It's recommended to change these settings as follows:

.. code-block:: ini

    ; php.ini
    ; maximum memory allocated to store the results
    realpath_cache_size=4096K

    ; save the results for 10 minutes (600 seconds)
    realpath_cache_ttl=600


.. _performance-optimize-composer-autoloader:

Configure the PHP realpath Cache
--------------------------------

PHP uses an internal cache to store the result of mapping file paths to their
real and absolute file system paths. This increases the performance for
applications like Symfony that open many PHP files, especially on Windows
systems.

By default, PHP sets a ``realpath_cache_size`` of ``16K`` which is too low for
Symfony. Consider updating this value at least to ``4096K``. In addition, cached
paths are only stored for ``120`` seconds by default. Consider updating this
value too using the ``realpath_cache_ttl`` option:

.. code-block:: ini

    ; php.ini
    realpath_cache_size=4096K
    realpath_cache_ttl=600

.. index::
   single: Performance; Autoloader

Optimize Composer Autoloader
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The class loader used while developing the application is optimized to find
new and changed classes. In production servers, PHP files should never change,
unless a new application version is deployed.
That's why you can use `Composer's autoloader optimization`
to scan the entire application once and build a "class map",
which is a big array of the locations of all the classes and it's stored
in ``vendor/composer/autoload_classmap.php``.

Execute this command to generate the class map at install time (and thus make it part of your
deployment process too):

.. code-block:: bash

    $ composer install --no-dev --optimize-autoloader --classmap-authoritative --apcu-autoloader

``--no-dev``
  Excludes the classes that are only needed in the development environment
  (e.g. tests).
``--optimize-autoloader``
  Dumps every PSR-0 and PSR-4 compatible class used in your application.
``--classmap-authoritative``
  Prevents Composer from scanning the file system for classes that are not
  found in the class map.
``--apcu-autoloader``
  You need to install APCu PHP extension to use this option.
  It will cache the classmap in APCu. It won't generate the classmap though,
  so you need to always use it with ``--optimize-autoloader``


.. tip::

    If your production server still uses the legacy APC PHP extension instead of
    OPcache, install the `APCu Polyfill component`_ in your application to enable
    compatibility with `APCu PHP functions`_ and unlock support for advanced Symfony
    features, such as the APCu Cache adapter.

.. note::

    When using the APCu autoloader, if you add new classes, they will be found
    automatically and everything will work the same as before (i.e. no
    reason to "clear" the cache). However, if you change the location of a
    particular namespace or prefix, you'll need to flush your APCu cache. Otherwise,
    the autoloader will still be looking at the old location for all classes
    inside that namespace.

Learn more
----------

* :doc:`/http_cache/varnish`
* :doc:`/http_cache/form_csrf_caching`

.. _`byte code caches`: https://en.wikipedia.org/wiki/List_of_PHP_accelerators
.. _`OPcache`: http://php.net/manual/en/book.opcache.php
.. _`Composer's autoloader optimization`: https://getcomposer.org/doc/articles/autoloader-optimization.md
.. _`APC`: http://php.net/manual/en/book.apc.php
.. _`APCu Polyfill component`: https://github.com/symfony/polyfill-apcu
.. _`APCu PHP functions`: http://php.net/manual/en/ref.apcu.php
