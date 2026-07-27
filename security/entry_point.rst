The Entry Point: Helping Users Start Authentication
===================================================

When an unauthenticated user tries to access a protected page, Symfony
gives them a suitable response to let them start authentication (e.g.
redirect to a login form or show a 401 Unauthorized HTTP response for
APIs).

However, sometimes one firewall has multiple ways to authenticate (e.g.
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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\SocialConnectAuthenticator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        // allow authentication using a form or a custom authenticator
                        'form_login' => null,
                        'custom_authenticators' => [
                            SocialConnectAuthenticator::class,
                        ],
                        // configure the form authentication as the entry point for unauthenticated users
                        'entry_point' => 'form_login',
                    ],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\ApiTokenAuthenticator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'api' => [
                        'pattern' => '^/api',
                        'custom_authenticators' => [
                            ApiTokenAuthenticator::class,
                        ],
                    ],
                    'main' => [
                        'lazy' => true,
                        'form_login' => null,
                    ],
                ],
                'access_control' => [
                    ['path' => '^/login', 'roles' => 'PUBLIC_ACCESS'],
                    ['path' => '^/api', 'roles' => 'ROLE_API_USER'],
                    ['path' => '^/', 'roles' => 'ROLE_USER'],
                ],
            ],
        ]);
