How to Force HTTPS or HTTP for Different URLs
=============================================

.. tip::

    The *best* policy is to force ``https`` on all URLs, which can be done via
    your web server configuration or ``access_control``.

You can force areas of your site to use the HTTPS protocol in the security
config. This is done through the ``access_control`` rules using the ``requires_channel``
option. To enforce HTTPS on all URLs, add the ``requires_channel`` config to every
access control:

.. configuration-block::

        .. code-block:: yaml

            # config/packages/security.yaml
            security:
                # ...

                access_control:
                    - { path: '^/secure', roles: ROLE_ADMIN, requires_channel: https }
                    - { path: '^/login', roles: PUBLIC_ACCESS, requires_channel: https }
                    # catch all other URLs
                    - { path: '^/', roles: PUBLIC_ACCESS, requires_channel: https }

        .. code-block:: php

            // config/packages/security.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'security' => [
                    // ....
                    'access_control' => [
                        ['path' => '^/secure', 'roles' => ['ROLE_ADMIN'], 'requires_channel' => 'https'],
                        ['path' => '^/login', 'roles' => ['PUBLIC_ACCESS'], 'requires_channel' => 'https'],
                        // catch all other URLs
                        ['path' => '^/', 'roles' => ['PUBLIC_ACCESS'], 'requires_channel' => 'https'],
                    ],
                ],
            ]);

To make life easier while developing, you can also use an environment variable,
like ``requires_channel: '%env(REQUIRED_SCHEME)%'``. In your ``.env`` file, set
``REQUIRED_SCHEME`` to ``http`` by default, but override it to ``https`` on production.

See :doc:`/security/access_control` for more details about ``access_control``
in general.

.. note::

    An alternative way to enforce HTTP or HTTPS is to use
    :ref:`the scheme option <routing-force-https>` of a route or group of routes.

.. note::

    Forcing HTTPS while using a reverse proxy or load balancer requires a proper
    configuration to avoid infinite redirect loops; see :doc:`/deployment/proxies`
    for more details.
