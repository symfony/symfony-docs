.. index::
   single: Security; Pre authenticated providers

Using pre authenticated security firewalls
==========================================

A lot of authentication modules are already provided by some webservers,
including Apache. These modules generally set some environment variables
that can be used to know which user is accessing your application. Out of the 
box, Symfony supports most authentication mecanisms.
These are called *pre authenticated* requests because the user is already
authenticated when reaching your application.

.. note::

    An authentication provider will only inform the user provider of the username
    that made the request. You will need to either use an available
    :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`
    or implement your own:

    * :doc:`/cookbook/security/entity_provider`
    * :doc:`/cookbook/security/custom_provider`

X.509 Client certificate authentication
---------------------------------------

When using client certificate, your webserver is doing all the authentication
process itself. For Apache, on your VirtualHost, you may use the 
``SSLVerifyClient Require`` directive.

On your Symfony2 application security configuration, you can enable the x509
authentication firewall:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                secured_area:
                    pattern: ^/
                    x509:
                        provider: your_user_provider

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall name="secured_area" pattern="^/">
                <x509 provider="your_user_provider"/>
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area' => array(
                    'pattern' => '^/'
                    'x509'    => array(
                        'provider' => 'your_user_provider',
                    ),
                ),
            ),
        ));

By default, the firewall will provide the ``SSL_CLIENT_S_DN_Email`` variable to
your user provider, and set the ``SSL_CLIENT_S_DN`` as credentials in the 
:class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken`.
You can override these by setting respectively the ``user`` and the ``credentials`` keys
in the x509 firewall configuration.
