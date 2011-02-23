.. index::
   single: Tests; Profiling

How to use the Profiling Data in a Test
=======================================

It's highly recommended that a functional test only tests the Response. But if
you write functional tests that monitor your production servers, you might
want to write tests on the profiling data.

The Symfony2 :doc:`Profiler </guides/internals/profiler>` gathers a lot of
data for each request. Use these data to check the number of database calls,
the time spent in the framework, ... But before writing assertions, always
check that the profiler is indeed available (it is enabled by default in the
``test`` environment)::

    if ($profiler = $client->getProfiler()) {
        // check the number of requests
        $this->assertTrue($profiler->get('db')->getQueryCount() < 10);

        // check the time spent in the framework
        $this->assertTrue( $profiler->get('timer')->getTime() < 0.5);
    }

.. note::

    The profiler information are available even if you insulate the client or
    if you use an HTTP layer for your tests.
