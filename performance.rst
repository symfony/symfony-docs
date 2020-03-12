.. index::
   single: Performance; Byte code cache; OPcache; APC

Performance
===========

Symfony is fast, right out of the box. However, you can make it faster if you
optimize your servers and your applications as explained in the following
performance checklists.

Symfony Application Checklist
-----------------------------

These are the code and configuration changes that you can make in your Symfony
application to improve its performance:

#. :ref:`Install APCu Polyfill if your server uses APC <performance-install-apcu-polyfill>`
#. :ref:`Dump the service container into a single file <performance-service-container-single-file>`
#. :ref:`Restrict the number of locales enabled in the application <performance-enabled-locales>`

.. _performance-install-apcu-polyfill:

Install APCu Polyfill if your Server Uses APC
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your production server still uses the legacy APC PHP extension instead of
OPcache, install the `APCu Polyfill component`_ in your application to enable
compatibility with `APCu PHP functions`_ and unlock support for advanced Symfony
features, such as the APCu Cache adapter.

.. _performance-service-container-single-file:

Dump the Service Container into a Single File
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony compiles the :doc:`service container </service_container>` into multiple
small files by default. Set this parameter to ``true`` to compile the entire
container into a single file, which could improve performance when using
"class preloading" in PHP 7.4 or newer versions:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            # ...
            container.dumper.inline_factories: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <!-- ... -->
                <parameter key="container.dumper.inline_factories">true</parameter>
            </parameters>
        </container>

    .. code-block:: php

        // config/services.php

        // ...
        $container->setParameter('container.dumper.inline_factories', true);


.. _performance-enabled-locales:

Restrict the Number of Locales Enabled in the Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use the :ref:`framework.translator.enabled_locales <reference-translator-enabled-locales>`
option to only generate the translation files actually used in your application.

Production Server Checklist
---------------------------

These are the changes that you can make in your production server to improve
performance when running Symfony applications:

#. :ref:`Use the OPcache byte code cache <performance-use-opcache>`
#. :ref:`Use the OPcache class preloading <performance-use-preloading>`
#. :ref:`Configure OPcache for maximum performance <performance-configure-opcache>`
#. :ref:`Don't check PHP files timestamps <performance-dont-check-timestamps>`
#. :ref:`Configure the PHP realpath Cache <performance-configure-realpath-cache>`
#. :ref:`Optimize Composer Autoloader <performance-optimize-composer-autoloader>`

.. _performance-use-opcache:

Use the OPcache Byte Code Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

OPcache stores the compiled PHP files to avoid having to recompile them for
every request. There are some `byte code caches`_ available, but as of PHP
5.5, PHP comes with `OPcache`_ built-in. For older versions, the most widely
used byte code cache is `APC`_.

.. _performance-use-preloading:

Use the OPcache class preloading
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Starting from PHP 7.4, OPcache can compile and load classes at start-up and
make them available to all requests until the server is restarted, improving
performance significantly.

During container compilation, Symfony generates the file with the list of
classes to preload. The only requirement is that you need to set both
``container.dumper.inline_factories`` and  ``container.dumper.inline_class_loader``
parameters to ``true``.

The preload file path is the same as the compiled service container but with the
``preload`` suffix:

.. code-block:: ini

    ; php.ini
    opcache.preload=/path/to/project/var/cache/prod/srcApp_KernelProdContainer.preload.php

.. _performance-configure-opcache:

Configure OPcache for Maximum Performance
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default OPcache configuration is not suited for Symfony applications, so
it's recommended to change these settings as follows:

.. code-block:: ini

    ; php.ini
    ; maximum memory that OPcache can use to store compiled PHP files
    opcache.memory_consumption=256

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
you won't see the updates made in the application. Given that in PHP, the CLI
and the web processes don't share the same OPcache, you cannot clear the web
server OPcache by executing some command in your terminal. These are some of the
possible solutions:

1. Restart the web server;
2. Call the ``apc_clear_cache()`` or ``opcache_reset()`` functions via the
   web server (i.e. by having these in a script that you execute over the web);
3. Use the `cachetool`_ utility to control APC and OPcache from the CLI.

.. _performance-configure-realpath-cache:

Configure the PHP ``realpath`` Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a relative path is transformed into its real and absolute path, PHP
caches the result to improve performance. Applications that open many PHP files,
such as Symfony projects, should use at least these values:

.. code-block:: ini

    ; php.ini
    ; maximum memory allocated to store the results
    realpath_cache_size=4096K

    ; save the results for 10 minutes (600 seconds)
    realpath_cache_ttl=600

.. note::

    PHP disables the ``realpath`` cache when the `open_basedir`_ config option
    is enabled.

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

.. code-block:: terminal

    $ composer dump-autoload --no-dev --classmap-authoritative

* ``--no-dev`` excludes the classes that are only needed in the development
  environment (i.e. ``require-dev`` dependencies and ``autoload-dev`` rules);
* ``--classmap-authoritative`` creates a class map for PSR-0 and PSR-4 compatible classes
  used in your application and prevents Composer from scanning the file system for
  classes that are not found in the class map. (see: `Composer's autoloader optimization`_).

Learn more
----------

* :doc:`/http_cache/varnish`

.. _`byte code caches`: https://en.wikipedia.org/wiki/List_of_PHP_accelerators
.. _`OPcache`: https://www.php.net/manual/en/book.opcache.php
.. _`Composer's autoloader optimization`: https://getcomposer.org/doc/articles/autoloader-optimization.md
.. _`APC`: https://www.php.net/manual/en/book.apc.php
.. _`APCu Polyfill component`: https://github.com/symfony/polyfill-apcu
.. _`APCu PHP functions`: https://www.php.net/manual/en/ref.apcu.php
.. _`cachetool`: https://github.com/gordalina/cachetool
.. _`open_basedir`: https://www.php.net/manual/ini.core.php#ini.open-basedir
