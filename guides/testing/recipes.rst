Testing Recipes
===============

Insulating Clients
------------------

If you need to simulate an interaction between different Clients (think of a
chat for instance), create several Clients::

    $harry = $this->createClient();
    $sally = $this->createClient();

    $harry->request('POST', '/say/sally/Hello');
    $sally->request('GET', '/messages');

    $this->assertEquals(201, $harry->getResponse()->getStatusCode());
    $this->assertRegExp('/Hello/', $sally->getResponse()->getContent());

This works except when your code maintains a global state or if it depends on
third-party libraries that has some kind of global state. In such a case, you
can insulate your clients::

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

.. tip::
   As an insulated client is slower, you can keep one client in the main process,
   and insulate the other ones.

Testing Redirection
-------------------

By default, the Client follows HTTP redirects. But if you want to get the
Response before the redirection and redirect yourself, calls the
``followRedirects()`` method::

    $client->followRedirects(false);

    $crawler = $this->request('GET', '/');

    // do something with the redirect response

    // follow the redirection manually
    $crawler = $this->followRedirect();

    $client->followRedirects(true);

HTTP Authorization
------------------

If your application needs HTTP authentication, pass the username and password
as server variables to ``createClient()``::

    $client = $this->createClient(array(), array(
        'PHP_AUTH_USER' => 'username',
        'PHP_AUTH_PW'   => 'pa$$word',
    ));

You can also override it on a per request basis::

    $client->request('DELETE', '/post/12', array(), array(
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
available (it is enabled by default in the ``test`` environment)::

    if ($profiler = $client->getProfiler()) {
        // check the number of requests
        $this->assertTrue($profiler['db']->getQueryCount() < 10);

        // check the time spent in the framework
        $this->assertTrue( $profiler['timer']->getTime() < 0.5);

        // check the matching route
        $this->assertEquals('blog_post', $profiler['app']->getRoute());
    }

.. note::
   The profiler information are available even if you insulate the client or if
   you use an HTTP layer for your tests.

Container
---------

It's highly recommended that a functional test only tests the Response. But
under certain very rare circumstances, you might want to access some internal
objects to write assertions. In such cases, you can access the dependency
injection container::

    $container = $client->getContainer();

Be warned that this does not work if you insulate the client or if you use an
HTTP layer.

.. tip::
   If the information you need to check are available from the profiler, use them
   instead.

Useful Assertions
-----------------

After some time, you will notice that you always write the same kind of
assertions. To get you started faster, here is a list of the most common and
useful assertions::

    // Assert that the response matches a given CSS selector.
    $this->assertTrue(count($crawler->filter($selector)) > 0);

    // Assert that the response matches a given CSS selector n times.
    $this->assertEquals($count, $crawler->filter($selector)->count());

    // Assert the a response header has the given value.
    $this->assertTrue($client->getResponse()->headers->contains($key, $value));

    // Assert that the response content matches a regexp.
    $this->assertRegExp($regexp, $client->getResponse()->getContent());

    // Assert the response status code.
    $this->assertTrue($client->getResponse()->isSuccessful());
    $this->assertTrue($client->getResponse()->isNotFound());
    $this->assertEquals(200, $client->getResponse()->getStatusCode());

    // Assert that the response status code is a redirect.
    $this->assertTrue($client->getResponse()->isRedirected('google.com'));
