.. index::
   single: Tests; HTTP authentication

How to simulate HTTP Authentication in a Functional Test
========================================================

If your application needs HTTP authentication, pass the username and password
as server variables to ``createClient()``::

    $client = static::createClient(array(), array(
        'PHP_AUTH_USER' => 'username',
        'PHP_AUTH_PW'   => 'pa$$word',
    ));

You can also override it on a per request basis::

    $client->request('DELETE', '/post/12', array(), array(), array(
        'PHP_AUTH_USER' => 'username',
        'PHP_AUTH_PW'   => 'pa$$word',
    ));

When your application is using a ``form_login``, you can simplify your tests
by allowing your test configuration to make use of HTTP authentication. This
way you can use the above to authenticate in tests, but still have your users
login via the normal ``form_login``. The trick is to include the ``http_basic``
key in your firewall, along with the ``form_login`` key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_test.yml
        security:
            firewalls:
                your_firewall_name:
                    http_basic:

    .. code-block:: xml

        <!-- app/config/config_test.xml -->
        <security:config>
            <security:firewall name="your_firewall_name">
              <security:http-basic />
           </security:firewall>
        </security:config>

    .. code-block:: php

        // app/config/config_test.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'your_firewall_name' => array(
                    'http_basic' => array(),
                ),
            ),
        ));
