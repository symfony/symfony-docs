Profiler
========

When enabled, the Symfony2 profiler collects useful information about each
request made to your application and store them for later analysis. Use the
profiler in the development environment to help you debug your code and
enhance performance; use it in the production environment to explore problems
after the fact.

You rarely have to deal with the profiler directly as Symfony2 provides
visualizer tools like the Web Debug Toolbar and the Web Profiler. If you use
the Symfony2 sandbox, or create an application with the Symfony2 console, the
profiler, the web debug toolbar, and the web profiler are all already
configured with sensible settings.

.. note::
   The profiler collects information for all requests (simple requests,
   redirects, exceptions, Ajax requests, ESI requests; and for all HTTP
   methods and all formats). It means that for a single URL, you can have
   several associated profiling data (one per external request/response
   pair).

Visualizing Profiling Data
--------------------------

Using the Web Debug Toolbar
~~~~~~~~~~~~~~~~~~~~~~~~~~~

In the development environment, the web debug toolbar is available at the
bottom of all pages. It displays a good summary of the profiling data that
gives you instant access to a lot of useful information when something does
not work as expected.

If the summary provided by the Web Debug Toolbar is not enough, click on the
token link (a string made of 13 random characters) to access the Web Profiler.

.. note::
   If the token is not clickable, it means that the profiler routes are not
   registered (see below for configuration information).

Analyzing Profiling data with the Web Profiler
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The Web Profiler is a visualization tool for profiling data that you can use
in development to debug your code and enhance performance; but it can also be
used to explore problems that occur in production. It exposes all information
collected by the profiler in a web interface.

Using the Profiler in Functional Tests
--------------------------------------

Profiling data are available in functional tests. It gives you a great way to
check various things and enforce some metrics::

    class HelloControllerTest extends WebTestCase
    {
        public function testIndex()
        {
            $client = $this->createClient();
            $crawler = $client->request('GET', '/hello/Fabien');

            // Write some assertions about the Response
            // ...

            // Check that the profiler is enabled
            if ($profiler = $this->getProfiler($client->getResponse())) {
                $this->assertTrue($profiler->get('db')->getQueryCount() < 30);
                $this->assertTrue($profiler->get('timer')->getTime() < 50);
            }
        }
    }

.. tip::
   Read the API for built-in `data collectors`_ to learn more about their
   interfaces.

Accessing the Profiling information
-----------------------------------

You don't need to use the default visualizers to access the profiling
information. But how can you retrieve profiling information for a specific
request after the fact? When the profiler stores data about a Request, it also
associates a token with it; this token is available in the ``X-Debug-Token``
HTTP header of the Response::

    $profiler = $container->getProfiler()->getFromResponse($response);

    $profiler = $container->getProfiler()->getFromToken($token);

.. tip::
   When the profiler is enabled but not the web debug toolbar, or when you
   want to get the token for an Ajax request, use a tool like Firebug to get
   the value of the ``X-Debug-Token`` HTTP header.

You can also use the ``find()`` method to access tokens based on some
criteria::

    // get the latest 10 tokens
    $tokens = $container->getProfiler()->find('', '', 10);

    // get the latest 10 tokens for all URL containing /admin/
    $tokens = $container->getProfiler()->find('', '/admin/', 10);

    // get the latest 10 tokens for local requests
    $tokens = $container->getProfiler()->find('127.0.0.1', '', 10);

If you want to manipulate profiling data on a different machine than the one
where the information were generated, use the ``export()`` and ``import()``
methods::

    // on the production machine
    $profiler = $container->getProfiler()->getFromToken($token);
    $data = $profiler->export();

    // on the development machine
    $profiler->import($data);

Configuration
-------------

The default Symfony2 configuration comes with sensible settings for the
profiler, the web debug toolbar, and the web profiler. Here is for instance
the configuration for the development environment:

.. configuration-block::

    .. code-block:: yaml

        # load the profiler
        web.config:
            profiler: { only_exceptions: false }

        # enable the web profiler
        webprofiler.config:
            toolbar: true
            intercept_redirects: true

    .. code-block:: xml

        <!-- xmlns:webprofiler="http://www.symfony-project.org/schema/dic/webprofiler" -->
        <!-- xsi:schemaLocation="http://www.symfony-project.org/schema/dic/webprofiler http://www.symfony-project.org/schema/dic/webprofiler/webprofiler-1.0.xsd"> -->

        <!-- load the profiler -->
        <web:config>
            <profiler only-exceptions="false" />
        </web:config>

        <!-- enable the web profiler -->
        <webprofiler:config
            toolbar="true"
            intercept-redirects="true"
        />

    .. code-block:: php

        // load the profiler
        $container->loadFromExtension('web', 'config', array(
            'profiler' => array('only-exceptions' => false),
        ));

        // enable the web profiler
        $container->loadFromExtension('webprofiler', 'config', array(
            'toolbar' => true,
            'intercept-redirects' => true,
        ));

When ``only-exceptions`` is set to ``true``, the profiler only collects data
when an exception is thrown by the application.

When ``intercept-redirects`` is set to ``true``, the web profiler intercepts
the redirects and gives you the opportunity to look at the collected data
before following the redirect.

If you enable the web profiler, you also need to mount the profiler routes:

.. configuration-block::

    .. code-block:: yaml

        _profiler:
            resource: WebProfilerBundle/Resources/config/routing/profiler.xml
            prefix:   /_profiler

    .. code-block:: xml

        <import resource="WebProfilerBundle/Resources/config/routing/profiler.xml" prefix="/_profiler" />

    .. code-block:: php

        $collection->addCollection($loader->import("WebProfilerBundle/Resources/config/routing/profiler.xml"), '/_profiler');

.. _data collectors: http://api.symfony-reloaded.org/PR3/index.html?q=DataCollector


Creating a custom Data Collector
--------------------------------

Creating a custom data collector is as simple as implementing the
:class:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface`::

    interface DataCollectorInterface
    {
        /**
         * Collects data for the given Request and Response.
         *
         * @param Request    $request   A Request instance
         * @param Response   $response  A Response instance
         * @param \Exception $exception An Exception instance
         */
        function collect(Request $request, Response $response, \Exception $exception = null);

        /**
         * Returns the name of the collector.
         *
         * @return string The collector name
         */
        function getName();
    }

The ``getName()`` method must return a unique name. This is used to access the
information later on (see the section about functional tests above for
instance).

The ``collect()`` method is responsible for storing the data it wants to give
access to in local properties.

.. caution::
   As the profiler serializes data collector instances, you should not store
   objects that cannot be serialized (like PDO objects), or you need to
   provide your own ``serialize()`` method.

Most of the time, it is convenient to extend
:class:`Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector` and
populate the ``$this->data`` property (it takes care of serializing the
``$this->data`` property)::

    class MemoryDataCollector extends DataCollector
    {
        public function collect(Request $request, Response $response, \Exception $exception = null)
        {
            $this->data = array(
                'memory' => memory_get_peak_usage(true),
            );
        }

        public function getMemory()
        {
            return $this->data['memory'];
        }

        public function getName()
        {
            return 'memory';
        }
    }

.. _data_collector_tag:

Enabling Custom Data Collectors
-------------------------------

To enable a data collector, add it as a regular service in one of your
configuration, and tag it with ``data_collector``:

.. configuration-block::

    .. code-block:: yaml

        services:
            data_collector.your_collector_name:
                class: Fully\Qualified\Collector\Class\Name
                tag:   { name: data_collector }

    .. code-block:: xml

        <service id="data_collector.your_collector_name" class="Fully\Qualified\Collector\Class\Name">
            <tag name="data_collector" />
        </service>

    .. code-block:: php

        $container
            ->register('data_collector.your_collector_name', 'Fully\Qualified\Collector\Class\Name')
            ->addTag('data_collector')
        ;

.. _data collectors: http://api.symfony-reloaded.org/PR3/index.html?q=DataCollector
