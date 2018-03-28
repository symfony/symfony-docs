How to Use Multiple Guard Authenticators
========================================

The Guard authentication component allows you to easily use many different
authenticators at a time.

An entry point is a service id (of one of your authenticators) whose
``start()`` method is called to start the authentication process.

Multiple Authenticators with Shared Entry Point
-----------------------------------------------

Sometimes you want to offer your users different authentication mechanisms like
a form login and a Facebook login while both entry points redirect the user to
the same login page.
However, in your configuration you have to explicitly say which entry point
you want to use.

This is how your security configuration can look in action:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
             # ...
            firewalls:
                default:
                    anonymous: ~
                    guard:
                        authenticators:
                            - AppBundle\Security\LoginFormAuthenticator
                            - AppBundle\Security\FacebookConnectAuthenticator
                        entry_point: AppBundle\Security\LoginFormAuthenticator

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="default">
                    <anonymous />
                    <guard entry-point="AppBundle\Security\LoginFormAuthenticator">
                        <authenticator>AppBundle\Security\LoginFormAuthenticator</authenticator>
                        <authenticator>AppBundle\Security\FacebookConnectAuthenticator</authenticator>
                    </guard>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        use AppBundle\Security\LoginFormAuthenticator;
        use AppBundle\Security\FacebookConnectAuthenticator;

        $container->loadFromExtension('security', array(
            // ...
            'firewalls' => array(
                'default' => array(
                    'anonymous' => null,
                    'guard' => array(
                        'entry_point' => '',
                        'authenticators' => array(
                            LoginFormAuthenticator::class,
                            FacebookConnectAuthenticator::class'
                        ),
                    ),
                ),
            ),
        ));

There is one limitation with this approach - you have to use exactly one entry point.

Multiple Authenticators with Separate Entry Points
--------------------------------------------------

However, there are use cases where you have authenticators that protect different
parts of your application. For example, you have a login form that protects
the secured area of your application front-end and API end points that are
protected with API tokens. As you can only configure one entry point per firewall,
the solution is to split the configuration into two separate firewalls:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...
            firewalls:
                api:
                    pattern: ^/api/
                    guard:
                        authenticators:
                            - AppBundle\Security\ApiTokenAuthenticator
                default:
                    anonymous: ~
                    guard:
                        authenticators:
                            - AppBundle\Security\LoginFormAuthenticator
            access_control:
                - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
                - { path: ^/api, roles: ROLE_API_USER }
                - { path: ^/, roles: ROLE_USER }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="api" pattern="^/api/">
                    <guard>
                        <authenticator>AppBundle\Security\ApiTokenAuthenticator</authenticator>
                    </guard>
                </firewall>
                <firewall name="default">
                    <anonymous />
                    <guard>
                        <authenticator>AppBundle\Security\LoginFormAuthenticator</authenticator>
                    </guard>
                </firewall>
                <rule path="^/login" role="IS_AUTHENTICATED_ANONYMOUSLY" />
                <rule path="^/api" role="ROLE_API_USER" />
                <rule path="^/" role="ROLE_USER" />
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        use AppBundle\Security\ApiTokenAuthenticator;
        use AppBundle\Security\LoginFormAuthenticator;

        $container->loadFromExtension('security', array(
            // ...
            'firewalls' => array(
                'api' => array(
                    'pattern' => '^/api',
                    'guard' => array(
                        'authenticators' => array(
                            ApiTokenAuthenticator::class,
                        ),
                    ),
                ),
                'default' => array(
                    'anonymous' => null,
                    'guard' => array(
                        'authenticators' => array(
                            LoginFormAuthenticator::class,
                        ),
                    ),
                ),
            ),
            'access_control' => array(
                array('path' => '^/login', 'role' => 'IS_AUTHENTICATED_ANONYMOUSLY'),
                array('path' => '^/api', 'role' => 'ROLE_API_USER'),
                array('path' => '^/', 'role' => 'ROLE_USER'),
            ),
        ));
