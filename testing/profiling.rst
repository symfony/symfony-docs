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
tests significantly. For this reason, Symfony keeps the profiler enabled
but **disables data collection** by default in the test environment:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/web_profiler.yaml
        when@test:
            framework:
                profiler: { enabled: true, collect: false }

    .. code-block:: php

        // config/packages/web_profiler.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'when@test' => [
                'framework' => [
                    'profiler' => [
                        'enabled' => true,
                        'collect' => false,
                    ],
                ],
            ],
        ]);

Setting ``collect`` to ``true`` enables profiler data collection for all tests.
However, if you only need profiler data in a few specific tests, you can keep
collection disabled globally and enable it selectively by calling
``$client->enableProfiler()`` in those tests.

Note that calling ``enableProfiler()`` does not enable the profiler itself, which
must already be enabled via configuration. It only enables data collection for
the current test client.

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
        public function testRandomNumber(): void
        {
            $client = static::createClient();

            // enable profiler data collection only for the next request. If you
            // make additional requests, you must call this method again. This
            // method only works if the profiler is enabled in the configuration
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
