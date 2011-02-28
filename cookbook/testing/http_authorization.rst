.. index::
   single: Tests; HTTP Authorization

How to simulate HTTP Authorization in a Functional Test
=======================================================

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
