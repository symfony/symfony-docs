.. index::
   single: Tests; Insulating clients

How to test the Interaction of several Clients
==============================================

If you need to simulate an interaction between different Clients (think of a
chat for instance), create several Clients::

    $harry = static::createClient();
    $sally = static::createClient();

    $harry->request('POST', '/say/sally/Hello');
    $sally->request('GET', '/messages');

    $this->assertEquals(201, $harry->getResponse()->getStatusCode());
    $this->assertRegExp('/Hello/', $sally->getResponse()->getContent());

This works except when your code maintains a global state or if it depends on
a third-party library that has some kind of global state. In such a case, you
can insulate your clients::

    $harry = static::createClient();
    $sally = static::createClient();

    $harry->insulate();
    $sally->insulate();

    $harry->request('POST', '/say/sally/Hello');
    $sally->request('GET', '/messages');

    $this->assertEquals(201, $harry->getResponse()->getStatusCode());
    $this->assertRegExp('/Hello/', $sally->getResponse()->getContent());

Insulated clients transparently execute their requests in a dedicated and
clean PHP process, thus avoiding any side-effects.

.. tip::

    As an insulated client is slower, you can keep one client in the main
    process, and insulate the other ones.
