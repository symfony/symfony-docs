.. index::
   single: Performance; Byte code cache; OPcache; APC

Performance
===========

Symfony is fast, right out of the box. However, you can make it faster if you
optimize your servers and your applications as explained in the following
performance checklists.

Performance Checklists
----------------------

Use these checklists to verify that your application and server are configured
for maximum performance:

* **Symfony Application Checklist**:

  #. :ref:`Install APCu Polyfill if your server uses APC <performance-install-apcu-polyfill>`
  #. :ref:`Restrict the number of locales enabled in the application <performance-enabled-locales>`

* **Production Server Checklist**:

  #. :ref:`Dump the service container into a single file <performance-service-container-single-file>`
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

.. _performance-enabled-locales:

Restrict the Number of Locales Enabled in the Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use the :ref:`framework.enabled_locales <reference-enabled-locales>`
option to only generate the translation files actually used in your application.

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
        $container->parameters()->set('container.dumper.inline_factories', true);

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

During container compilation (e.g. when running the ``cache:clear`` command),
Symfony generates a file with the list of classes to preload in the
``var/cache/`` directory. Rather than use this file directly, use the
``config/preload.php`` file that is created when
:doc:`using Symfony Flex in your project </setup/flex>`:

.. code-block:: ini

    ; php.ini
    opcache.preload=/path/to/project/config/preload.php

    ; required for opcache.preload:
    opcache.preload_user=www-data

If this file is missing, run this command to update the Symfony Flex recipe:
``composer recipes:update symfony/framework-bundle``.

Use the :ref:`container.preload <dic-tags-container-preload>` and
:ref:`container.no_preload <dic-tags-container-nopreload>` service tags to define
which classes should or should not be preloaded by PHP.

.. _performance-configure-opcache:

Configure OPcache for Maximum Performance
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default OPcache configuration is not suited for Symfony applications, so
it is recommended to change these settings as follows:

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

After each deployment, you must empty and regenerate the cache of OPcache. Otherwise
you will not see the updates made in the application. Given that in PHP, the CLI
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

The class loader used while developing the application is optimized to find new
and changed classes. In production servers, PHP files should never change,
unless a new application version is deployed. That's why you can optimize
Composer's autoloader to scan the entire application once and build an
optimized "class map", which is a big array of the locations of all the classes
and it is stored in ``vendor/composer/autoload_classmap.php``.

Execute this command to generate the new class map (and make it part of your
deployment process too):

.. code-block:: terminal

    $ composer dump-autoload --no-dev --classmap-authoritative

* ``--no-dev`` excludes the classes that are only needed in the development
  environment (i.e. ``require-dev`` dependencies and ``autoload-dev`` rules);
* ``--classmap-authoritative`` creates a class map for PSR-0 and PSR-4 compatible classes
  used in your application and prevents Composer from scanning the file system for
  classes that are not found in the class map. (see: `Composer's autoloader optimization`_).

.. _profiling-applications:

Profiling Symfony Applications
------------------------------

Profiling with Blackfire
~~~~~~~~~~~~~~~~~~~~~~~~

`Blackfire`_ is the best tool to profile and optimize performance of Symfony
applications during development, test and production. It is a commercial service,
but provides free features that you can use to find bottlenecks in your projects.

Profiling with Symfony Stopwatch
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony provides a basic performance profiler in the development
:ref:`config environment <configuration-environments>`. Click on the "time panel"
of the :ref:`web debug toolbar <web-debug-toolbar>` to see how much time Symfony
spent on tasks such as making database queries and rendering templates.

You can measure the execution time and memory consumption of your own code and
display the result in the Symfony profiler thanks to the `Stopwatch component`_.

When using :ref:`autowiring <services-autowire>`, type-hint any controller or
service argument with the :class:`Symfony\\Component\\Stopwatch\\Stopwatch` class
and Symfony will inject the ``debug.stopwatch`` service::

    use Symfony\Component\Stopwatch\Stopwatch;

    class DataExporter
    {
        private $stopwatch;

        public function __construct(Stopwatch $stopwatch)
        {
            $this->stopwatch = $stopwatch;
        }

        public function export()
        {
            // the argument is the name of the "profiling event"
            $this->stopwatch->start('export-data');

            // ...do things to export data...

            // reset the stopwatch to delete all the data measured so far
            // $this->stopwatch->reset();

            $this->stopwatch->stop('export-data');
        }
    }

If the request calls this service during its execution, you will see a new
event called ``export-data`` in the Symfony profiler.

The ``start()``, ``stop()`` and ``getEvent()`` methods return a
:class:`Symfony\\Component\\Stopwatch\\StopwatchEvent` object that provides
information about the current event, even while it is still running. This
object can be converted to a string for a quick summary::

    // ...
    dump((string) $this->stopwatch->getEvent('export-data')); // dumps e.g. '4.50 MiB - 26 ms'

You can also profile your template code with the :ref:`stopwatch Twig tag <reference-twig-tag-stopwatch>`:

.. code-block:: twig

    {% stopwatch 'render-blog-posts' %}
        {% for post in blog_posts %}
            {# ... #}
        {% endfor %}
    {% endstopwatch %}

Profiling Categories
....................

Use the second optional argument of the ``start()`` method to define the
category or tag of the event. This helps keep events organized by type::

    $this->stopwatch->start('export-data', 'export');

Profiling Periods
.................

A `real-world stopwatch`_ not only includes the start/stop button but also a
"lap button" to measure each partial lap. This is exactly what the ``lap()``
method does, which stops an event and then restarts it immediately::

    $this->stopwatch->start('process-data-records', 'export');

    foreach ($records as $record) {
        // ... some code goes here
        $this->stopwatch->lap('process-data-records');
    }

    $event = $this->stopwatch->stop('process-data-records');
    // $event->getDuration(), $event->getMemory(), etc.

    // Lap information is stored as "periods" within the event:
    // $event->getPeriods();

Profiling Sections
..................

Sections are a way to split the profile timeline into groups. Example::

    $this->stopwatch->openSection();
    $this->stopwatch->start('validating-file', 'validation');
    $this->stopwatch->stopSection('parsing');

    $events = $this->stopwatch->getSectionEvents('parsing');

    // later you can reopen a section passing its name to the openSection() method
    $this->stopwatch->openSection('parsing');
    $this->stopwatch->start('processing-file');
    $this->stopwatch->stopSection('parsing');

All events that don't belong to any named section are added to the special section
called ``__root__``. This way you can get all stopwatch events, even if you don't
know their names, as follows::

    foreach($this->stopwatch->getSectionEvents('__root__') as $event) {
        echo (string) $event;
    }

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
.. _`Blackfire`: https://blackfire.io/docs/introduction?utm_source=symfony&utm_medium=symfonycom_docs&utm_campaign=performance
.. _`Stopwatch component`: https://symfony.com/components/Stopwatch
.. _`real-world stopwatch`: https://en.wikipedia.org/wiki/Stopwatch
