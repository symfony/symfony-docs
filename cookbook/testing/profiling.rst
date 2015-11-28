.. index::
   single: Tests; Profiling

How to Use the Profiler in a Functional Test
============================================

It's highly recommended that a functional test only tests the Response. But if
you write functional tests that monitor your production servers, you might
want to write tests on the profiling data as it gives you a great way to check
various things and enforce some metrics.

:doc:`The Symfony Profiler </cookbook/profiler/index>` gathers a lot of data for
each request. Use this data to check the number of database calls, the time
spent in the framework, etc. But before writing assertions, enable the profiler
and check that the profiler is indeed available (it is enabled by default in
the ``test`` environment)::

    class LuckyControllerTest extends WebTestCase
    {
        public function testNumberAction()
        {
            $client = static::createClient();

            // Enable the profiler for the next request
            // (it does nothing if the profiler is not available)
            $client->enableProfiler();

            $crawler = $client->request('GET', '/lucky/number');

            // ... write some assertions about the Response

            // Check that the profiler is enabled
            if ($profile = $client->getProfile()) {
                // check the number of requests
                $this->assertLessThan(
                    10,
                    $profile->getCollector('db')->getQueryCount()
                );

                // check the time spent in the framework
                $this->assertLessThan(
                    500,
                    $profile->getCollector('time')->getDuration()
                );
            }
        }
    }

If a test fails because of profiling data (too many DB queries for instance),
you might want to use the Web Profiler to analyze the request after the tests
finish. It's easy to achieve if you embed the token in the error message::

    $this->assertLessThan(
        30,
        $profile->getCollector('db')->getQueryCount(),
        sprintf(
            'Checks that query count is less than 30 (token %s)',
            $profile->getToken()
        )
    );

.. caution::

     The profiler store can be different depending on the environment
     (especially if you use the SQLite store, which is the default configured
     one).

.. note::

    The profiler information is available even if you insulate the client or
    if you use an HTTP layer for your tests.

.. tip::

    Read the API for built-in :doc:`data collectors </cookbook/profiler/data_collector>`
    to learn more about their interfaces.

Speeding up Tests by not Collecting Profiler Data
-------------------------------------------------

To avoid collecting data in each test you can set the ``collect`` parameter
to false:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_test.yml

        # ...
        framework:
            profiler:
                enabled: true
                collect: false

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                        http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->

            <framework:config>
                <framework:profiler enabled="true" collect="false" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php

        // ...
        $container->loadFromExtension('framework', array(
            'profiler' => array(
                'enabled' => true,
                'collect' => false,
            ),
        ));

In this way only tests that call ``$client->enableProfiler()`` will collect data.
