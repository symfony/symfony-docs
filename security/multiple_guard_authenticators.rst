How to Use Multiple Guard Authenticators
========================================

The Guard authentication component allows you to use many different
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

        # config/packages/security.yaml
        security:
             # ...
            firewalls:
                default:
                    anonymous: ~
                    guard:
                        authenticators:
                            - App\Security\LoginFormAuthenticator
                            - App\Security\FacebookConnectAuthenticator
                        entry_point: App\Security\LoginFormAuthenticator

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="default">
                    <anonymous/>
                    <guard entry-point="App\Security\LoginFormAuthenticator">
                        <authenticator>App\Security\LoginFormAuthenticator</authenticator>
                        <authenticator>App\Security\FacebookConnectAuthenticator</authenticator>
                    </guard>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\LoginFormAuthenticator;
        use App\Security\FacebookConnectAuthenticator;

        $container->loadFromExtension('security', [
            // ...
            'firewalls' => [
                'default' => [
                    'anonymous' => null,
                    'guard' => [
                        'entry_point' => '',
                        'authenticators' => [
                            LoginFormAuthenticator::class,
                            FacebookConnectAuthenticator::class,
                        ],
                    ],
                ],
            ],
        ]);

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

        # config/packages/security.yaml
        security:
            # ...
            firewalls:
                api:
                    pattern: ^/api/
                    guard:
                        authenticators:
                            - App\Security\ApiTokenAuthenticator
                default:
                    anonymous: ~
                    guard:
                        authenticators:
                            - App\Security\LoginFormAuthenticator
            access_control:
                - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
                - { path: ^/api, roles: ROLE_API_USER }
                - { path: ^/, roles: ROLE_USER }

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="api" pattern="^/api/">
                    <guard>
                        <authenticator>App\Security\ApiTokenAuthenticator</authenticator>
                    </guard>
                </firewall>
                <firewall name="default">
                    <anonymous/>
                    <guard>
                        <authenticator>App\Security\LoginFormAuthenticator</authenticator>
                    </guard>
                </firewall>
                <rule path="^/login" role="IS_AUTHENTICATED_ANONYMOUSLY"/>
                <rule path="^/api" role="ROLE_API_USER"/>
                <rule path="^/" role="ROLE_USER"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\ApiTokenAuthenticator;
        use App\Security\LoginFormAuthenticator;

        $container->loadFromExtension('security', [
            // ...
            'firewalls' => [
                'api' => [
                    'pattern' => '^/api',
                    'guard' => [
                        'authenticators' => [
                            ApiTokenAuthenticator::class,
                        ],
                    ],
                ],
                'default' => [
                    'anonymous' => null,
                    'guard' => [
                        'authenticators' => [
                            LoginFormAuthenticator::class,
                        ],
                    ],
                ],
            ],
            'access_control' => [
                ['path' => '^/login', 'role' => 'IS_AUTHENTICATED_ANONYMOUSLY'],
                ['path' => '^/api', 'role' => 'ROLE_API_USER'],
                ['path' => '^/', 'role' => 'ROLE_USER'],
            ],
        ]);
