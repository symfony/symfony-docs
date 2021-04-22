How to Build a JSON Authentication Endpoint
===========================================

In this entry, you'll build a JSON endpoint to log in your users. When the
user logs in, you can load your users from anywhere - like the database.
See :ref:`security-user-providers` for details.

First, enable the JSON login under your firewall:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    anonymous: true
                    lazy: true
                    json_login:
                        check_path: /login

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="main" anonymous="true" lazy="true">
                    <json-login check-path="/login"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            'firewalls' => [
                'main' => [
                    'anonymous' => true,
                    'lazy' => true,
                    'json_login' => [
                        'check_path' => '/login',
                    ],
                ],
            ],
        ]);

.. tip::

    The ``check_path`` can also be a route name (but cannot have mandatory
    wildcards - e.g. ``/login/{foo}`` where ``foo`` has no default value).

The next step is to configure a route in your app matching this path:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/SecurityController.php
        namespace App\Controller;

        // ...
        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\Routing\Annotation\Route;

        class SecurityController extends AbstractController
        {
            /**
             * @Route("/login", name="login", methods={"POST"})
             */
            public function login(Request $request): Response
            {
                $user = $this->getUser();

                return $this->json([
                    // The getUserIdentifier() method was introduced in Symfony 5.3.
                    // In previous versions it was called getUsername()
                    'username' => $user->getUserIdentifier(),
                    'roles' => $user->getRoles(),
                ]);
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        login:
            path:       /login
            controller: App\Controller\SecurityController::login
            methods: POST

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="login" path="/login" controller="App\Controller\SecurityController::login" methods="POST"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\SecurityController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('login', '/login')
                ->controller([SecurityController::class, 'login'])
                ->methods(['POST'])
            ;
        };

Now, when you make a ``POST`` request, with the header ``Content-Type: application/json``,
to the ``/login`` URL with the following JSON document as the body, the security
system intercepts the request and initiates the authentication process:

.. code-block:: json

    {
        "username": "dunglas",
        "password": "MyPassword"
    }

Symfony takes care of authenticating the user with the submitted username and
password or triggers an error in case the authentication process fails. If the
authentication is successful, the controller defined earlier will be called.

If the JSON document has a different structure, you can specify the path to
access the ``username`` and ``password`` properties using the ``username_path``
and ``password_path`` keys (they default respectively to ``username`` and
``password``). For example, if the JSON document has the following structure:

.. code-block:: json

    {
        "security": {
            "credentials": {
                "login": "dunglas",
                "password": "MyPassword"
            }
        }
    }

The security configuration should be:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    anonymous: true
                    lazy: true
                    json_login:
                        check_path:    login
                        username_path: security.credentials.login
                        password_path: security.credentials.password

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="main" anonymous="true" lazy="true">
                    <json-login check-path="login"
                        username-path="security.credentials.login"
                        password-path="security.credentials.password"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            'firewalls' => [
                'main' => [
                    'anonymous' => true,
                    'lazy' => true,
                    'json_login' => [
                        'check_path' => 'login',
                        'username_path' => 'security.credentials.login',
                        'password_path' => 'security.credentials.password',
                    ],
                ],
            ],
        ]);
