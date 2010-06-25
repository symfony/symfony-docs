Testing Recipes
===============

Insulating Clients
------------------

If you need to simulate an interaction between different Clients (think of a
chat for instance), create several Clients:

    [php]
    $harry = $this->createClient();
    $sally = $this->createClient();

    $harry->request('POST', '/say/sally/Hello');
    $sally->request('GET', '/messages');

    $this->assertEquals(201, $harry->getResponse()->getStatusCode());
    $this->assertRegExp('/Hello/', $sally->getResponse()->getContent());

This works except when your code maintains a global state or if it depends on
third-party libraries that has some kind of global state. In such a case, you
can insulate your clients:

    [php]
    $harry = $this->createClient();
    $sally = $this->createClient();

    $harry->insulate();
    $sally->insulate();

    $harry->request('POST', '/say/sally/Hello');
    $sally->request('GET', '/messages');

    $this->assertEquals(201, $harry->getResponse()->getStatusCode());
    $this->assertRegExp('/Hello/', $sally->getResponse()->getContent());

Insulated clients transparently execute their requests in a dedicated and
clean PHP process, thus avoiding any side-effects.

>**TIP**
>As an insulated client is slower, you can keep one client in the main process,
>and insulate the other ones.

HTTP Authorization
------------------

If your application needs HTTP authentication, pass the username and password
as HTTP headers to `createClient()`:

    [php]
    $client = $this->createClient(array(), array(
        'PHP_AUTH_USER' => 'username',
        'PHP_AUTH_PW'   => 'pa$$word',
    ));

Profiling
---------

It's highly recommended that a functional test only tests the Response. But if
you write functional tests that monitor your production servers, you might
want to write tests on the profiling data.

The Symfony2 profiler gathers a lot of data for each request. Use these data
to check the number of database calls, the time spent in the framework, ...
But before writing assertions, always check that the profiler is indeed
available (it is enabled by default in the `test` environment):

    [php]
    $profiler = $this->getProfiler($client->getResponse());
    if ($profiler) {
        // check the number of requests
        $this->assertTrue($profiler['db']->getQueryCount() < 10);

        // check the time spent in the framework
        $this->assertTrue( $profiler['timer']->getTime() < 0.5);

        // check the matching route
        $this->assertEquals('blog_post', $profiler['app']->getRoute());
    }

>**NOTE**
>The profiler information are available even if you insulate the client or if
>you use an HTTP layer for your tests.

Container
---------

It's highly recommended that a functional test only tests the Response. But
under certain very rare circumstances, you might want to access some internal
objects to write assertions. In such cases, you can access the dependency
injection container:

    [php]
    $container = $client->getContainer();

Be warned that this does not work if you insulate the client or if you use an
HTTP layer.

>**TIP**
>If the information you need to check are available from the profiler, use them
>instead.
