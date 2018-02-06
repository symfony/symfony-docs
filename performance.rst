.. index::
   single: Performance; Byte code cache; OPcache; APC

Performance
===========

Symfony is fast, right out of the box. However, you can make it faster if you
optimize your servers and your applications as explained in the following
performance checklists.

Symfony Application Checklist
-----------------------------

#. :ref:`Install APCu Polyfill if your server uses APC <performance-install-apcu-polyfill>`
#. :ref:`Enable APC Caching for the Autoloader <performance-autoloader-apc-cache>`
#. :ref:`Use Bootstrap Files <performance-use-bootstrap-files>`

Production Server Checklist
---------------------------

#. :ref:`Use the OPcache byte code cache <performance-use-opcache>`
#. :ref:`Configure OPcache for maximum performance <performance-configure-opcache>`
#. :ref:`Don't check PHP files timestamps <performance-dont-check-timestamps>`
#. :ref:`Configure the PHP realpath Cache <performance-configure-realpath-cache>`
#. :ref:`Optimize Composer Autoloader <performance-optimize-composer-autoloader>`

.. _performance-install-apcu-polyfill:

Install APCu Polyfill if your Server Uses APC
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your production server still uses the legacy APC PHP extension instead of
OPcache, install the `APCu Polyfill component`_ in your application to enable
compatibility with `APCu PHP functions`_ and unlock support for advanced Symfony
features, such as the APCu Cache adapter.

.. _performance-autoloader-apc-cache:

Enable APC Caching for the Autoloader
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The class autoloading mechanism is one of the slowest parts in PHP applications
that make use of lots of classes, such as Symfony. A simple way to improve its
performance is to use the :class:`Symfony\\Component\\ClassLoader\\ApcClassLoader`,
which caches the location of each class after it's located the first time.

To use it, adapt your front controller file like this::

    // app.php
    // ...

    $loader = require_once __DIR__.'/../app/bootstrap.php.cache';

    // Change 'sf' by something unique to this app to prevent
    // conflicts with other applications running in the same server
    $loader = new ApcClassLoader('sf', $loader);
    $loader->register(true);

    // ...

For more details, see :doc:`/components/class_loader/cache_class_loader`.

.. note::

    When using the APC autoloader, if you add new classes, they will be found
    automatically and everything will work the same as before (i.e. no
    reason to "clear" the cache). However, if you change the location of a
    particular namespace or prefix, you'll need to flush your APC cache. Otherwise,
    the autoloader will still be looking at the old location for all classes
    inside that namespace.

.. _performance-use-bootstrap-files:

Use Bootstrap Files
~~~~~~~~~~~~~~~~~~~

.. caution::

    Thanks to the optimizations introduced in PHP 7, bootstrap files are no
    longer necessary when running your Symfony applications with PHP 7 or a
    newer PHP version.

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

.. _performance-use-opcache:

Use the OPcache Byte Code Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

OPcache stores the compiled PHP files to avoid having to recompile them for
every request. There are some `byte code caches`_ available, but as of PHP
5.5, PHP comes with `OPcache`_ built-in. For older versions, the most widely
used byte code cache is `APC`_.

.. _performance-configure-opcache:

Configure OPcache for Maximum Performance
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default OPcache configuration is not suited for Symfony applications, so
it's recommended to change these settings as follows:

.. code-block:: ini

    ; php.ini
    ; maximum memory that OPcache can use to store compiled PHP files
    opcache.memory_consumption=256M

    ; maximum number of files that can be stored in the cache
    opcache.max_accelerated_files=20000

.. _performance-dont-check-timestamps:

Don't Check PHP Files Timestamps
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In production servers, PHP files should never change, unless a new application
version is deployed. However, by default OPcache checks if cached files have
changed their contents since they were cached. This check introduces some
overhead that can be avoided as follows:

.. code-block:: ini

    ; php.ini
    opcache.validate_timestamps=0

After each deploy, you must empty and regenerate the cache of OPcache. Otherwise
you won't see the updates made in the application. Given than in PHP, the CLI
and the web processes don't share the same OPcache, you cannot clear the web
server OPcache by executing some command in your terminal. These are some of the
possible solutions:

1. Restart the web server;
2. Call the ``apc_clear_cache()`` or ``opcache_reset()`` functions via the
   web server (i.e. by having these in a script that you execute over the web);
3. Use the `cachetool`_ utility to control APC and OPcache from the CLI.

.. _performance-configure-realpath-cache:

Configure the PHP realpath Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a relative path is transformed into its real and absolute path, PHP
caches the result to improve performance. Applications that open many PHP files,
such as Symfony projects, must use at least these values:

.. code-block:: ini

    ; php.ini
    ; maximum memory allocated to store the results
    realpath_cache_size=4096K

    ; save the results for 10 minutes (600 seconds)
    realpath_cache_ttl=600

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
.. _`bootstrap file`: https://github.com/sensiolabs/SensioDistributionBundle/blob/master/Composer/ScriptHandler.php
.. _`Composer's autoloader optimization`: https://getcomposer.org/doc/articles/autoloader-optimization.md
.. _`APC`: http://php.net/manual/en/book.apc.php
.. _`APCu Polyfill component`: https://github.com/symfony/polyfill-apcu
.. _`APCu PHP functions`: http://php.net/manual/en/ref.apcu.php
.. _`cachetool`: https://github.com/gordalina/cachetool
