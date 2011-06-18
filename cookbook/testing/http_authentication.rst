.. index::
   single: Tests; HTTP Authentication

How to simulate HTTP Authentication in a Functional Test
========================================================

If your application needs HTTP authentication, pass the username and password
as server variables to ``createClient()``::

    $client = static::createClient(array(), array(
        'PHP_AUTH_USER' => 'username',
        'PHP_AUTH_PW'   => 'pa$$word',
    ));

You can also override it on a per request basis::

    $client->request('DELETE', '/post/12', array(), array(
        'PHP_AUTH_USER' => 'username',
        'PHP_AUTH_PW'   => 'pa$$word',
    ));
