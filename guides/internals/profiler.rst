Profiler
========

When enabled, the Symfony2 profiler collects useful information about each
request made to your application and store them for later analysis. Use the
profiler in the development environment to help you to debug your code and
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
            if ($profiler = $client->getProfiler()) {
                $this->assertTrue($profiler->get('db')->getQueryCount() < 30);
                $this->assertTrue($profiler->get('timer')->getTime() < 50);
            }
        }
    }

.. tip::

    Read the API for built-in `data collectors`_ to learn more about their
    interfaces.

If a test fails because of profiling data (too many DB queries for instance),
you might want to use the Web Profiler to analyze the request after the tests
finish. It's easy to achieve if you embed the token in the error message::

    $this->assertTrue(
        $profiler->get('db')->getQueryCount() < 30,
        sprintf('checks that query count is less than 30 (token %s)', $profiler->getToken())
    );

.. caution::

     The profiler store can be different depending on the environment
     (especially if you use the SQLite store, which is the default configured
     one).

Accessing the Profiling information
-----------------------------------

You don't need to use the default visualizer to access the profiling
information. But how can you retrieve profiling information for a specific
request after the fact? When the profiler stores data about a Request, it also
associates a token with it; this token is available in the ``X-Debug-Token``
HTTP header of the Response::

    $profiler = $container->get('profiler')->getFromResponse($response);

    $profiler = $container->get('profiler')->getFromToken($token);

.. tip::

    When the profiler is enabled but not the web debug toolbar, or when you
    want to get the token for an Ajax request, use a tool like Firebug to get
    the value of the ``X-Debug-Token`` HTTP header.

You can also set the token from your code, based for instance on the username
or any other information, to ease the retrieval later on::

    $profiler->setToken('abcd');

You can also use the ``find()`` method to access tokens based on some
criteria::

    // get the latest 10 tokens
    $tokens = $container->get('profiler')->find('', '', 10);

    // get the latest 10 tokens for all URL containing /admin/
    $tokens = $container->get('profiler')->find('', '/admin/', 10);

    // get the latest 10 tokens for local requests
    $tokens = $container->get('profiler')->find('127.0.0.1', '', 10);

If you want to manipulate profiling data on a different machine than the one
where the information were generated, use the ``export()`` and ``import()``
methods::

    // on the production machine
    $profiler = $container->get('profiler')->getFromToken($token);
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
        app.config:
            profiler: { only_exceptions: false }

        # enable the web profiler
        webprofiler.config:
            toolbar: true
            intercept_redirects: true

    .. code-block:: xml

        <!-- xmlns:webprofiler="http://www.symfony-project.org/schema/dic/webprofiler" -->
        <!-- xsi:schemaLocation="http://www.symfony-project.org/schema/dic/webprofiler http://www.symfony-project.org/schema/dic/webprofiler/webprofiler-1.0.xsd"> -->

        <!-- load the profiler -->
        <app:config>
            <app:profiler only-exceptions="false" />
        </app:config>

        <!-- enable the web profiler -->
        <webprofiler:config
            toolbar="true"
            intercept-redirects="true"
        />

    .. code-block:: php

        // load the profiler
        $container->loadFromExtension('app', 'config', array(
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
            resource: @WebProfilerBundle/Resources/config/routing/profiler.xml
            prefix:   /_profiler

    .. code-block:: xml

        <import resource="WebProfilerBundle/Resources/config/routing/profiler.xml" prefix="/_profiler" />

    .. code-block:: php

        $collection->addCollection($loader->import("WebProfilerBundle/Resources/config/routing/profiler.xml"), '/_profiler');

As the profiler adds some overhead, you might want to enable it only under
certain circumstances in the production environment. The ``only-exceptions``
settings limits profiling to 500 pages, but what if you want to get
information when the client IP comes from a specific address, or for a limited
portion of the website? You can use a request matcher:

.. configuration-block::

    .. code-block:: yaml

        # enables the profiler only for request coming for the 192.168.0.0 network
        app.config:
            profiler:
                matcher: { ip: 192.168.0.0/24 }

        # enables the profiler only for the /admin URLs
        app.config:
            profiler:
                matcher: { path: "#^/admin/#i" }

        # combine rules
        app.config:
            profiler:
                matcher: { ip: 192.168.0.0/24, path: "#^/admin/#i" }

        # use a custom matcher instance defined in the "custom_matcher" service
        app.config:
            profiler:
                matcher: { service: custom_matcher }

    .. code-block:: xml

        <!-- enables the profiler only for request coming for the 192.168.0.0 network -->
        <app:config>
            <app:profiler>
                <app:matcher ip="192.168.0.0/24" />
            </app:profiler>
        </app:config>

        <!-- enables the profiler only for the /admin URLs -->
        <app:config>
            <app:profiler>
                <app:matcher path="#^/admin/#i" />
            </app:profiler>
        </app:config>

        <!-- combine rules -->
        <app:config>
            <app:profiler>
                <app:matcher ip="192.168.0.0/24" path="#^/admin/#i" />
            </app:profiler>
        </app:config>

        <!-- use a custom matcher instance defined in the "custom_matcher" service -->
        <app:config>
            <appp:rofiler>
                <app:matcher service="custom_matcher" />
            </app:profiler>
        </app:config>

        <!-- define an anonymous service for the matcher -->
        <web:config>
            <profiler>
                <matcher>
                    <service class="CustomMatcher" />
                </matcher>
            </profiler>
        </web:config>

    .. code-block:: php

        // enables the profiler only for request coming for the 192.168.0.0 network
        $container->loadFromExtension('app', 'config', array(
            'profiler' => array(
                'matcher' => array('ip' => '192.168.0.0/24'),
            ),
        ));

        // enables the profiler only for the /admin URLs
        $container->loadFromExtension('app', 'config', array(
            'profiler' => array(
                'matcher' => array('path' => '#^/admin/#i'),
            ),
        ));

        // combine rules
        $container->loadFromExtension('app', 'config', array(
            'profiler' => array(
                'matcher' => array('ip' => '192.168.0.0/24', 'path' => '#^/admin/#i'),
            ),
        ));

        # use a custom matcher instance defined in the "custom_matcher" service
        $container->loadFromExtension('app', 'config', array(
            'profiler' => array(
                'matcher' => array('service' => new Reference('custom_matcher')),
            ),
        ));

        // define an anonymous service for the matcher
        $container->loadFromExtension('app', 'config', array(
            'profiler' => array(
                'matcher' => array('services' => array($container->register('custom_matcher', 'CustomMatcher'))),
            ),
        ));

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

    As the profiler serializes data collector instances, you should not
    store objects that cannot be serialized (like PDO objects), or you need
    to provide your own ``serialize()`` method.

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
                tags:
                    - { name: data_collector }

    .. code-block:: xml

        <service id="data_collector.your_collector_name" class="Fully\Qualified\Collector\Class\Name">
            <tag name="data_collector" />
        </service>

    .. code-block:: php

        $container
            ->register('data_collector.your_collector_name', 'Fully\Qualified\Collector\Class\Name')
            ->addTag('data_collector')
        ;

.. _data collectors: http://api.symfony-reloaded.org/PR4/index.html?q=DataCollector

Adding Web Profiler Templates
-----------------------------

When you want to display the data collected by your Data Collector in the web
debug toolbar or the web profiler, create a Twig template following this
skeleton:

.. code-block:: jinja

    {% extends 'WebProfilerBundle:Profiler:layout.html.twig' %}

    {% block toolbar %}
        {# the web debug toolbar content #}
    {% endblock %}

    {% block head %}
        {# if the web profiler panel needs some specific JS or CSS files #}
    {% endblock %}

    {% block menu %}
        {# the menu content #}
    {% endblock %}

    {% block panel %}
        {# the panel content #}
    {% endblock %}

Each block is optional. The ``toolbar`` block is used for the web debug
toolbar and ``menu`` and ``panel`` are used to add a panel to the web
profiler.

All blocks have access to the ``collector`` object.

.. tip::

    Built-in templates use a base64 encoded image for the toolbar (``<img
    src="src="data:image/png;base64,..."``). You can easily calculate the
    base64 value for an image with this little script: ``echo
    base64_encode(file_get_contents($_SERVER['argv'][1]));``.

To enable the template, add a ``template`` attribute to the ``data_collector``
tag in your configuration:

.. configuration-block::

    .. code-block:: yaml

        services:
            data_collector.your_collector_name:
                class: Fully\Qualified\Collector\Class\Name
                tags:
                    - { name: data_collector, template: "YourBundle:Collector:templatename" }

    .. code-block:: xml

        <service id="data_collector.your_collector_name" class="Fully\Qualified\Collector\Class\Name">
            <tag name="data_collector" template="YourBundle:Collector:templatename" />
        </service>

    .. code-block:: php

        $container
            ->register('data_collector.your_collector_name', 'Fully\Qualified\Collector\Class\Name')
            ->addTag('data_collector', array('template' => 'YourBundle:Collector:templatename'))
        ;
