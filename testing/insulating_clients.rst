.. index::
   single: Tests; Insulating clients

How to Test the Interaction of several Clients
==============================================

If you need to simulate an interaction between different clients (think of a
chat for instance), create several clients::

    // ...
    use Symfony\Component\HttpFoundation\Response;

    $harry = static::createClient();
    $sally = static::createClient();

    $harry->request('POST', '/say/sally/Hello');
    $sally->request('GET', '/messages');

    $this->assertEquals(Response::HTTP_CREATED, $harry->getResponse()->getStatusCode());
    $this->assertRegExp('/Hello/', $sally->getResponse()->getContent());

This works except when your code maintains a global state or if it depends on
a third-party library that has some kind of global state. In such a case, you
can insulate your clients::

    // ...
    use Symfony\Component\HttpFoundation\Response;

    $harry = static::createClient();
    $sally = static::createClient();

    $harry->insulate();
    $sally->insulate();

    $harry->request('POST', '/say/sally/Hello');
    $sally->request('GET', '/messages');

    $this->assertEquals(Response::HTTP_CREATED, $harry->getResponse()->getStatusCode());
    $this->assertRegExp('/Hello/', $sally->getResponse()->getContent());

Insulated clients transparently run their requests in a dedicated and
clean PHP process, thus avoiding any side effects.

.. tip::

    As an insulated client is slower, you can keep one client in the main
    process, and insulate the other ones.

.. caution::

    Insulating tests requires some serializing and unserializing operations. If
    your test includes data that can't be serialized, such as file streams when
    using the ``UploadedFile`` class, you'll see an exception about
    *"serialization is not allowed"*. This is a technical limitation of PHP, so
    the only solution is to disable insulation for those tests.
