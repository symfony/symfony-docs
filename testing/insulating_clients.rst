How to Test the Interaction of several Clients
==============================================

If you need to simulate an interaction between different clients (think of a
chat for instance), create several clients::

    // ...
    use Symfony\Component\HttpFoundation\Response;

    $harry = static::createClient();

    // before creating another client, shut down the current kernel first;
    // this does NOT invalidate the $harry client, which can still be used after this call
    self::ensureKernelShutdown();

    $sally = static::createClient();

    $harry->request('POST', '/say/sally/Hello');
    $sally->request('GET', '/messages');

    $this->assertEquals(Response::HTTP_CREATED, $harry->getResponse()->getStatusCode());
    $this->assertMatchesRegularExpression('/Hello/', $sally->getResponse()->getContent());

Each call to ``createClient()`` boots a kernel and stores it as the current
kernel for the test case. Creating another client while a kernel is still
running triggers a ``LogicException``. Call ``self::ensureKernelShutdown()``
before creating the next client.

Shutting down the kernel does not make previously created clients unusable.
Existing clients (like ``$harry`` in the above example) can still perform requests
after the call, but the next ``createClient()`` call will boot a fresh kernel instance.

This works except when your code maintains a global state or if it depends on
a third-party library that has some kind of global state. In such a case, you
can insulate your clients::

    // ...
    use Symfony\Component\HttpFoundation\Response;

    $harry = static::createClient();
    self::ensureKernelShutdown();
    $sally = static::createClient();

    $harry->insulate();
    $sally->insulate();

    $harry->request('POST', '/say/sally/Hello');
    $sally->request('GET', '/messages');

    $this->assertEquals(Response::HTTP_CREATED, $harry->getResponse()->getStatusCode());
    $this->assertMatchesRegularExpression('/Hello/', $sally->getResponse()->getContent());

Insulated clients transparently run their requests in a dedicated and
clean PHP process, thus avoiding any side effects.

.. tip::

    As an insulated client is slower, you can keep one client in the main
    process, and insulate the other ones.

.. warning::

    Insulating tests requires some serializing and unserializing operations. If
    your test includes data that can't be serialized, such as file streams when
    using the ``UploadedFile`` class, you'll see an exception about
    *"serialization is not allowed"*. This is a technical limitation of PHP, so
    the only solution is to disable insulation for those tests.
