The Entry Point: Helping Users Start Authentication
===================================================

When an unauthenticated user tries to access a protected page, Symfony
gives them a suitable response to let them start authentication (e.g.
redirect to a login form or show a 401 Unauthorized HTTP response for
APIs).

However sometimes, one firewall has multiple ways to authenticate (e.g.
both a form login and a social login). In these cases, it is required to
configure the *authentication entry point*.

You can configure this using the ``entry_point`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:

            # ...
            firewalls:
                main:
                    # allow authentication using a form or a custom authenticator
                    form_login: ~
                    custom_authenticators:
                        - App\Security\SocialConnectAuthenticator

                    # configure the form authentication as the entry point for unauthenticated users
                    entry_point: form_login

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config enable-authenticator-manager="true">
                <!-- ... -->

                <!-- entry-point: configure the form authentication as the entry
                                  point for unauthenticated users -->
                <firewall name="main"
                    entry-point="form_login"
                >
                    <!-- allow authentication using a form or a custom authenticator -->
                    <form-login/>
                    <custom-authenticator>App\Security\SocialConnectAuthenticator</custom-authenticator>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\SocialConnectAuthenticator;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->enableAuthenticatorManager(true);
            // ....


            // allow authentication using a form or HTTP basic
            $mainFirewall = $security->firewall('main');
            $mainFirewall
                ->formLogin()
                ->customAuthenticators([SocialConnectAuthenticator::class])

                // configure the form authentication as the entry point for unauthenticated users
                ->entryPoint('form_login');
            ;
        };

.. note::

    You can also create your own authentication entry point by creating a
    class that implements
    :class:`Symfony\\Component\\Security\\Http\\EntryPoint\\AuthenticationEntryPointInterface`.
    You can then set ``entry_point`` to the service id (e.g.
    ``entry_point: App\Security\CustomEntryPoint``)

Multiple Authenticators with Separate Entry Points
--------------------------------------------------

However, there are use cases where you have authenticators that protect
different parts of your application. For example, you have a login form
that protects the main website and API end-points used by external parties
protected by API keys.

As you can only configure one entry point per firewall, the solution is to
split the configuration into two separate firewalls:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            firewalls:
                api:
                    pattern: ^/api/
                    custom_authenticators:
                        - App\Security\ApiTokenAuthenticator
                main:
                    lazy: true
                    form_login: ~

            access_control:
                - { path: '^/login', roles: PUBLIC_ACCESS }
                - { path: '^/api', roles: ROLE_API_USER }
                - { path: '^/', roles: ROLE_USER }

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="api" pattern="^/api/">
                    <custom-authenticator>App\Security\ApiTokenAuthenticator</custom-authenticator>
                </firewall>

                <firewall name="main" anonymous="true" lazy="true">
                    <form-login/>
                </firewall>

                <rule path="^/login" role="PUBLIC_ACCESS"/>
                <rule path="^/api" role="ROLE_API_USER"/>
                <rule path="^/" role="ROLE_USER"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\ApiTokenAuthenticator;
        use App\Security\LoginFormAuthenticator;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $apiFirewall = $security->firewall('api');
            $apiFirewall
                ->pattern('^/api')
                ->customAuthenticators([ApiTokenAuthenticator::class])
            ;

            $mainFirewall = $security->firewall('main');
            $mainFirewall
                ->lazy(true)
                ->formLogin();

            $accessControl = $security->accessControl();
            $accessControl->path('^/login')->roles(['PUBLIC_ACCESS']);
            $accessControl->path('^/api')->roles(['ROLE_API_USER']);
            $accessControl->path('^/')->roles(['ROLE_USER']);
        };
