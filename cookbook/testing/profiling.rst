.. index::
   single: Tests; Profiling

How to use the Profiler in a Functional Test
============================================

It's highly recommended that a functional test only tests the Response. But if
you write functional tests that monitor your production servers, you might
want to write tests on the profiling data as it gives you a great way to check
various things and enforce some metrics.

The Symfony2 :doc:`Profiler </book/internals/profiler>` gathers a lot of
data for each request. Use this data to check the number of database calls,
the time spent in the framework, ... But before writing assertions, always
check that the profiler is indeed available (it is enabled by default in the
``test`` environment)::

    class HelloControllerTest extends WebTestCase
    {
        public function testIndex()
        {
            $client = static::createClient();
            $crawler = $client->request('GET', '/hello/Fabien');

            // Write some assertions about the Response
            // ...

            // Check that the profiler is enabled
            if ($profile = $client->getProfile()) {
                // check the number of requests
                $this->assertTrue($profile->get('db')->getQueryCount() < 10);

                // check the time spent in the framework
                $this->assertTrue( $profile->get('timer')->getTime() < 0.5);
            }
        }
    }

If a test fails because of profiling data (too many DB queries for instance),
you might want to use the Web Profiler to analyze the request after the tests
finish. It's easy to achieve if you embed the token in the error message::

    $this->assertTrue(
        $profile->get('db')->getQueryCount() < 30,
        sprintf('Checks that query count is less than 30 (token %s)', $profile->getToken())
    );

.. caution::

     The profiler store can be different depending on the environment
     (especially if you use the SQLite store, which is the default configured
     one).

.. note::

    The profiler information is available even if you insulate the client or
    if you use an HTTP layer for your tests.

.. tip::

    Read the API for built-in :doc:`data collectors</cookbook/profiler/data_collector>`
    to learn more about their interfaces.
