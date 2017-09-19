.. index::
   single: Tests

Performance
===========

Symfony is fast, right out of the box. Of course, if you really need speed,
there are many ways that you can make Symfony even faster. In this article,
you'll explore some of the ways to make your Symfony application even faster.

.. index::
   single: Performance; Byte code cache

Use a Byte Code Cache (e.g. OPcache)
------------------------------------

The first thing that you should do to improve your performance is to use a
"byte code cache". These caches store the compiled PHP files to avoid having
to recompile them for every request.

There are a number of `byte code caches`_ available, some of which are open
source. As of PHP 5.5, PHP comes with `OPcache`_ built-in. For older versions,
the most widely used byte code cache is `APC`_.

.. tip::

    If your server still uses the legacy APC PHP extension, install the
    `APCu Polyfill component`_ in your application to enable compatibility with
    `APCu PHP functions`_ and unlock support for advanced Symfony features, such
    as the APCu Cache adapter.

Using a byte code cache really has no downside, and Symfony has been designed
to perform really well in this type of environment.

Monitoring Source File Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Most byte code caches monitor the source files for changes. This ensures that if
the source of a file changes, the byte code is recompiled automatically.
This is really convenient, but it adds overhead.

For this reason, some byte code caches offer an option to disable these checks.
For example, to disable these checks in APC, simply add ``apc.stat=0`` to your
``php.ini`` configuration.

When disabling these checks, it will be up to the server administrators to
ensure that the cache is cleared whenever any source files change. Otherwise,
the updates you've made in the application won't be seen.

For the same reasons, the byte code cache must also be cleared when deploying
the application (for example by calling ``apc_clear_cache()`` PHP function when
using APC and ``opcache_reset()`` when using OPcache).

.. note::

    In PHP, the CLI and the web processes don't share the same OPcache. This
    means that you cannot clear the web server OPcache by executing some command
    in your terminal. You either need to restart the web server or call the
    ``apc_clear_cache()`` or ``opcache_reset()`` functions via the web server
    (i.e. by having these in a script that you execute over the web).

Optimizing all the Files Used by Symfony
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, PHP's OPcache saves up to 2,000 files in the byte code cache. This
number is too low for the typical Symfony application, so you should set a
higher limit with the `opcache.max_accelerated_files`_ configuration option:

.. code-block:: ini

    ; php.ini
    opcache.max_accelerated_files = 20000

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

Use Composer's Class Map Functionality
--------------------------------------

By default, the Symfony Standard Edition uses Composer's autoloader
in the `autoload.php`_ file. This autoloader is easy to use, as it will
automatically find any new classes that you've placed in the registered
directories.

Unfortunately, this comes at a cost, as the loader iterates over all configured
namespaces to find a particular file, making ``file_exists()`` calls until it
finally finds the file it's looking for.

The simplest solution is to tell Composer to build an optimized "class map",
which is a big array of the locations of all the classes and it's stored
in ``vendor/composer/autoload_classmap.php``.

The class map can be generated from the command line, and might become part of
your deploy process:

.. code-block:: bash

    $ composer dump-autoload --optimize --no-dev --classmap-authoritative

``--optimize``
  Dumps every PSR-0 and PSR-4 compatible class used in your application.
``--no-dev``
  Excludes the classes that are only needed in the development environment
  (e.g. tests).
``--classmap-authoritative``
  Prevents Composer from scanning the file system for classes that are not
  found in the class map.

Caching the Autoloader with APCu
--------------------------------

Another solution is to cache the location of each class after it's located
the first time: use `composer install --apcu-autoloader`.

.. note::

    When using the APCu autoloader, if you add new classes, they will be found
    automatically and everything will work the same as before (i.e. no
    reason to "clear" the cache). However, if you change the location of a
    particular namespace or prefix, you'll need to flush your APCu cache. Otherwise,
    the autoloader will still be looking at the old location for all classes
    inside that namespace.

.. index::
   single: Performance; APCu

Learn more
----------

* :doc:`/http_cache/varnish`
* :doc:`/http_cache/form_csrf_caching`

.. _`byte code caches`: https://en.wikipedia.org/wiki/List_of_PHP_accelerators
.. _`OPcache`: http://php.net/manual/en/book.opcache.php
.. _`opcache.max_accelerated_files`: http://php.net/manual/en/opcache.configuration.php#ini.opcache.max-accelerated-files
.. _`APC`: http://php.net/manual/en/book.apc.php
.. _`APCu Polyfill component`: https://github.com/symfony/polyfill-apcu
.. _`APCu PHP functions`: http://php.net/manual/en/ref.apcu.php
.. _`autoload.php`: https://github.com/symfony/symfony-standard/blob/master/app/autoload.php
.. _`bootstrap file`: https://github.com/sensiolabs/SensioDistributionBundle/blob/master/Composer/ScriptHandler.php
