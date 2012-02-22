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

When your application is using a form_login with an entity provider, you can
simplify your tests by allowing your test configuration to make use of HTTP
authentication. This way you can use the above to authenticate and still use
the entity provider::

.. configuration-block::

    .. code-block:: yaml

	# app/config/config_test.yml
        security:
            firewalls:
                secured_area:
                    http_basic:

You do have to know the password for your users as this stored encrypted in
the database. You can use fixtures to load test users into your database.
