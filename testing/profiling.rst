How to Use the Profiler in a Functional Test
============================================

It's highly recommended that a functional test only tests the Response. But if
you write functional tests that monitor your production servers, you might
want to write tests on the profiling data as it gives you a great way to check
various things and enforce some metrics.

.. _speeding-up-tests-by-not-collecting-profiler-data:

Enabling the Profiler in Tests
------------------------------

Collecting data with :doc:`the Symfony Profiler </profiler>` can slow down your
tests significantly. That's why Symfony disables it by default:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/test/web_profiler.yaml

        # ...
        framework:
            profiler: { enabled: true, collect: false }

    .. code-block:: xml

        <!-- config/packages/test/web_profiler.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                        http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->

            <framework:config>
                <framework:profiler enabled="true" collect="false"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/test/web_profiler.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->profiler()
                ->enabled(true)
                ->collect(false)
            ;
        };

Setting ``collect`` to ``true`` enables the profiler for all tests. However, if
you need the profiler only in a few tests, you can keep it disabled globally and
enable the profiler individually on each test by calling
``$client->enableProfiler()``.

Testing the Profiler Information
--------------------------------

The data collected by the Symfony Profiler can be used to check the number of
database calls, the time spent in the framework, etc. All this information is
provided by the collectors obtained through the ``$client->getProfile()`` call::

    // tests/Controller/LuckyControllerTest.php
    namespace App\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class LuckyControllerTest extends WebTestCase
    {
        public function testRandomNumber()
        {
            $client = static::createClient();

            // enable the profiler only for the next request (if you make
            // new requests, you must call this method again)
            // (it does nothing if the profiler is not available)
            $client->enableProfiler();

            $crawler = $client->request('GET', '/lucky/number');

            // ... write some assertions about the Response

            // check that the profiler is enabled
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
finish. It can be achieved by embedding the token in the error message::

    $this->assertLessThan(
        30,
        $profile->getCollector('db')->getQueryCount(),
        sprintf(
            'Checks that query count is less than 30 (token %s)',
            $profile->getToken()
        )
    );

.. note::

    The profiler information is available even if you :doc:`insulate the client </testing/insulating_clients>`
    or if you use an HTTP layer for your tests.

.. tip::

    Read the API for built-in :ref:`data collectors <profiler-data-collector>`
    to learn more about their interfaces.
