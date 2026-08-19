Performance
===========

Symfony is fast. However, you can make it faster if you
optimize your servers and your applications as explained in the following
performance checklists.

Performance Checklists
----------------------

Use these checklists to verify that your application and server are configured
for maximum performance:

* **Symfony Application Checklist**:

  #. :ref:`Restrict the number of locales enabled in the application <performance-enabled-locales>`

* **Production Server Checklist**:

  #. :ref:`Dump the service container into a single file <performance-service-container-single-file>`
  #. :ref:`Use the OPcache bytecode cache <performance-use-opcache>`
  #. :ref:`Configure OPcache for maximum performance <performance-configure-opcache>`
  #. :ref:`Don't check PHP files timestamps <performance-dont-check-timestamps>`
  #. :ref:`Configure the PHP realpath Cache <performance-configure-realpath-cache>`
  #. :ref:`Optimize Composer Autoloader <performance-optimize-composer-autoloader>`

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
PHP `class preloading`_:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            # ...
            .container.dumper.inline_factories: true

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'parameters' => [
                '.container.dumper.inline_factories' => true,
            ],
        ]);

.. tip::

    The ``.`` prefix denotes a parameter that is only used during compilation of the container.
    See :ref:`Configuration Parameters <configuration-parameters>` for more details.

.. _performance-use-opcache:

Use the OPcache Bytecode Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

OPcache caches the compiled bytecode of PHP scripts to avoid recompiling them on
each request. PHP ships with `OPcache`_, but depending on your setup, you may
need to enable it explicitly.

.. _performance-use-preloading:

Use the OPcache class preloading
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

OPcache can compile and load classes at start-up and make them available to all
requests until the server is restarted, improving performance significantly.

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

The preloader also follows the types found in the public properties, the
arguments and the return types of the preloaded classes, so referencing a class
of a large third-party SDK can pull thousands of classes into the preloading.
Call the ``ignore()`` method before requiring the generated file to exclude
some of them::

    // config/preload.php
    use Symfony\Component\DependencyInjection\Dumper\Preloader;

    // excludes a single class...
    Preloader::ignore(Microsoft\Graph\GraphServiceClient::class);
    // ...or a whole namespace, using a trailing backslash
    Preloader::ignore('Microsoft\\Graph\\');

    require dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php';

.. versionadded:: 8.2

    The ``Preloader::ignore()`` method was introduced in Symfony 8.2.

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
    opcache.max_accelerated_files=32531

    ; memory (in MB) for interned strings; the default value (8 MB) is too low
    ; for Symfony applications, which use many fully-qualified class names
    opcache.interned_strings_buffer=32

.. _performance-dont-check-timestamps:

Don't Check PHP File Timestamps
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In production servers, PHP files should never change, unless a new application
version is deployed. However, by default OPcache checks if cached files have
changed their contents since they were cached. This check introduces some
overhead that can be avoided as follows:

.. code-block:: ini

    ; php.ini
    opcache.validate_timestamps=0

After each deployment, you must empty and regenerate the cache of OPcache. Otherwise
you won't see the updates made in the application. Given that in PHP, the CLI
and the web processes don't share the same OPcache, you cannot clear the web
server OPcache by executing some command in your terminal. These are some of the
possible solutions:

1. Restart the web server;
2. Call the ``opcache_reset()`` function via the web server (i.e. by having these
   in a script that you execute over the web);
3. Use the `cachetool`_ utility to control OPcache from the CLI.

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
and it's stored in ``vendor/composer/autoload_classmap.php``.

Execute this command to generate the new class map (and make it part of your
deployment process too):

.. code-block:: terminal

    $ composer dump-autoload --no-dev --classmap-authoritative

* ``--no-dev`` excludes the classes that are only needed in the development
  environment (i.e. ``require-dev`` dependencies and ``autoload-dev`` rules);
* ``--classmap-authoritative`` creates a class map for PSR-0 and PSR-4 compatible classes
  used in your application and prevents Composer from scanning the file system for
  classes that are not found in the class map. (see: `Composer's autoloader optimization`_).

Disable Dumping the Container as XML in Debug Mode
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In :ref:`debug mode <debug-mode>`, Symfony generates an XML file with all the
:doc:`service container </service_container>` information (services, arguments, etc.)
This XML file is used by various debugging commands such as ``debug:container``
and ``debug:autowiring``.

When the container grows larger and larger, so does the size of the file and the
time to generate it. If the benefit of this XML file does not outweigh the decrease
in performance, you can stop generating the file as follows:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            # ...
            debug.container.dump: false

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'parameters' => [
                'debug.container.dump' => false,
            ],
        ]);

.. _profiling-applications:

Profiling Symfony Applications
------------------------------

Profiling with Blackfire
~~~~~~~~~~~~~~~~~~~~~~~~

`Blackfire`_ is the best tool to profile and optimize performance of Symfony
applications during development, test and production. It's a commercial service,
but provides a `full-featured demo`_.

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
        public function __construct(
            private Stopwatch $stopwatch,
        ) {
        }

        public function export(): void
        {
            // the argument is the name of the "profiling event"
            $this->stopwatch->start('export-data');

            // ...do things to export data...

            // reset the stopwatch to delete all the data measured so far
            // $this->stopwatch->reset();

            $this->stopwatch->stop('export-data');
        }
    }

If the request calls this service during its execution, you'll see a new
event called ``export-data`` in the Symfony profiler.

The ``start()``, ``stop()`` and ``getEvent()`` methods return a
:class:`Symfony\\Component\\Stopwatch\\StopwatchEvent` object that provides
information about the current event, even while it's still running. This
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

    // Gets the last event period:
    // $event->getLastPeriod();

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

    use Symfony\Component\Stopwatch\Stopwatch;

    foreach($this->stopwatch->getSectionEvents(Stopwatch::ROOT) as $event) {
        echo (string) $event;
    }

Learn more
----------

* :doc:`/http_cache/varnish`

.. _`OPcache`: https://php.net/book.opcache.php
.. _`Composer's autoloader optimization`: https://getcomposer.org/doc/articles/autoloader-optimization.md
.. _`cachetool`: https://github.com/gordalina/cachetool
.. _`open_basedir`: https://www.php.net/manual/ini.core.php#ini.open-basedir
.. _`Blackfire`: https://blackfire.io/docs/introduction?utm_source=symfony&utm_medium=symfonycom_docs&utm_campaign=performance
.. _`full-featured demo`: https://demo.blackfire.io?utm_source=symfony&utm_medium=symfonycom_docs&utm_campaign=performance
.. _`Stopwatch component`: https://symfony.com/components/Stopwatch
.. _`real-world stopwatch`: https://en.wikipedia.org/wiki/Stopwatch
.. _`class preloading`: https://php.net/opcache.preloading.php
