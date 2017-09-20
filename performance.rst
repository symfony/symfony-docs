.. index::
   single: Tests

Performance
===========

Symfony is fast, right out of the box. Of course, if you really need speed, the
following performance checklists show how to make your application even faster.

.. index::
single: Performance; Byte code cache

Symfony Application Checklist
-----------------------------

#. :ref:`Install APCu Polyfill if your server uses APC <performance-install-apcu-polyfill>`
#. :ref:`Enable APCu Caching for the Autoloader <performance-autoloader-apcu-cache>`
#. :ref:`Use Bootstrap Files <performance-use-bootstrap-files>`

.. _performance-install-apcu-polyfill:

Install APCu Polyfill if your server uses APC
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. tip::

    If your production server still uses the legacy APC PHP extension instead of
    OPcache, install the `APCu Polyfill component`_ in your application to enable
    compatibility with `APCu PHP functions`_ and unlock support for advanced Symfony
    features, such as the APCu Cache adapter.

.. _performance-autoloader-apc-cache:

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
   single: Performance; Bootstrap files

.. _performance-use-bootstrap-files:

Use Bootstrap Files
~~~~~~~~~~~~~~~~~~~

The Symfony Standard Edition includes a script to generate a so-called
`bootstrap file`_, which is a large file containing the code of the most
commonly used classes. This saves a lot of IO operations because Symfony no
longer needs to look for and read those files.

If you're using the Symfony Standard Edition, then you're probably already
using the bootstrap file. To be sure, open your front controller (usually
``app.php``) and check to make sure that the following line exists::

    require_once __DIR__.'/../app/bootstrap.php.cache';

Note that there are two disadvantages when using a bootstrap file:

* the file needs to be regenerated whenever any of the original sources change
  (i.e. when you update the Symfony source or vendor libraries);

* when debugging, one will need to place break points inside the bootstrap file.

If you're using the Symfony Standard Edition, the bootstrap file is automatically
rebuilt after updating the vendor libraries via the ``composer install`` command.

.. note::

  Even when using a byte code cache, performance will improve when using a
  bootstrap file since there will be fewer files to monitor for changes. Of
  course, if this feature is disabled in the byte code cache (e.g.
  ``apc.stat=0`` in APC), there is no longer a reason to use a bootstrap file.

Production Server Checklist
---------------------------

#. :ref:`Use the OPcache byte code cache <performance-use-opcache>`
#. :ref:`Configure OPcache for maximum performance <performance-configure-opcache>`
#. :ref:`Don't check PHP timestamps <performance-dont-check-timestamps>`
#. :ref:`Configure the PHP realpath Cache <performance-configure-realpath-cache>`
#. :ref:`Optimize Composer Autoloader <performance-optimize-composer-autoloader>`

.. index::
   single: Performance; Byte code cache

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

.. index::
   single: Performance; Autoloader

.. _performance-optimize-composer-autoloader:

Optimize Composer Autoloader
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The class loader used while developing the application is optimized to find
new and changed classes. In production servers, PHP files should never change,
unless a new application version is deployed. That's why you can optimize
Composer's autoloader to scan the entire application once and build a "class map",
which is a big array of the locations of all the classes and it's stored
in ``vendor/composer/autoload_classmap.php``.

Execute this command to generate the class map (and make it part of your
deployment process too):

.. code-block:: bash

    $ composer dump-autoload --optimize --no-dev --classmap-authoritative

* ``--optimize`` dumps every PSR-0 and PSR-4 compatible class used in your
  application;
* ``--no-dev`` excludes the classes that are only needed in the development
  environment (e.g. tests);
* ``--classmap-authoritative`` prevents Composer from scanning the file
  system for classes that are not found in the class map.

Learn more
----------

* :doc:`/http_cache/varnish`
* :doc:`/http_cache/form_csrf_caching`

.. _`byte code caches`: https://en.wikipedia.org/wiki/List_of_PHP_accelerators
.. _`OPcache`: http://php.net/manual/en/book.opcache.php
.. _`APC`: http://php.net/manual/en/book.apc.php
.. _`APCu Polyfill component`: https://github.com/symfony/polyfill-apcu
.. _`APCu PHP functions`: http://php.net/manual/en/ref.apcu.php
.. _`bootstrap file`: https://github.com/sensiolabs/SensioDistributionBundle/blob/master/Composer/ScriptHandler.php
