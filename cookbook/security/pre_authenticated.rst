.. index::
   single: Security; Pre authenticated providers

Using pre Authenticated Security Firewalls
==========================================

A lot of authentication modules are already provided by some web servers,
including Apache. These modules generally set some environment variables
that can be used to determine which user is accessing your application. Out of the
box, Symfony supports most authentication mechanisms.
These requests are called *pre authenticated* requests because the user is already
authenticated when reaching your application.

X.509 Client Certificate Authentication
---------------------------------------

When using client certificates, your webserver is doing all the authentication
process itself. With Apache, for example, you would use the
``SSLVerifyClient Require`` directive.

Enable the x509 authentication for a particular firewall in the security configuration:

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

        <?xml version="1.0" ?>
        <!-- app/config/security.xml -->
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services">

            <config>
                <firewall name="secured_area" pattern="^/">
                    <x509 provider="your_user_provider"/>
                </firewall>
            </config>
        </srv:container>

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

By default, the firewall provides the ``SSL_CLIENT_S_DN_Email`` variable to
the user provider, and sets the ``SSL_CLIENT_S_DN`` as credentials in the
:class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken`.
You can override these by setting the ``user`` and the ``credentials`` keys
in the x509 firewall configuration respectively.

.. note::

    An authentication provider will only inform the user provider of the username
    that made the request. You will need to create (or use) a "user provider" that
    is referenced by the ``provider`` configuration parameter (``your_user_provider``
    in the configuration example). This provider will turn the username into a User 
    object of your choice. For more information on creating or configuring a user 
    provider, see:

    * :doc:`/cookbook/security/custom_provider`
    * :doc:`/cookbook/security/entity_provider`