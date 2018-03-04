How to Build a JSON Authentication Endpoint
===========================================

In this entry, you'll build a JSON endpoint to log in your users. Of course, when the
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
                    anonymous: ~
                    json_login:
                        check_path: /login

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="main">
                    <anonymous />
                    <json-login check-path="/login" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array(
                    'anonymous'  => null,
                    'json_login' => array(
                        'check_path' => '/login',
                    ),
                ),
            ),
        ));

.. tip::

    The ``check_path`` can also be a route name (but cannot have mandatory wildcards - e.g.
    ``/login/{foo}`` where ``foo`` has no default value).

Now, when a request is made to the ``/login`` URL, the security system initiates
the authentication process. You just need to configure a route matching this
path:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/SecurityController.php

        // ...
        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\Routing\Annotation\Route;

        class SecurityController extends Controller
        {
            /**
             * @Route("/login", name="login")
             */
            public function login(Request $request)
            {
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        login:
            path:       /login
            controller: App\Controller\SecurityController::login

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="login" path="/login">
                <default key="_controller">App\Controller\SecurityController::login</default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('login', new Route('/login', array(
            '_controller' => 'App\Controller\SecurityController::login',
        )));

        return $collection;

Don't let this empty controller confuse you. When you submit a ``POST`` request
to the ``/login`` URL with the following JSON document as the body, the security
system intercepts the requests. It takes care of authenticating the user with
the submitted username and password or triggers an error in case the authentication
process fails:

.. code-block:: json

    {
        "username": "dunglas",
        "password": "MyPassword"
    }

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
                    anonymous: ~
                    json_login:
                        check_path:    login
                        username_path: security.credentials.login
                        password_path: security.credentials.password

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="main">
                    <anonymous />
                    <json-login check-path="login"
                                username-path="security.credentials.login"
                                password-path="security.credentials.password" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array(
                    'anonymous'  => null,
                    'json_login' => array(
                        'check_path' => 'login',
                        'username_path' => 'security.credentials.login',
                        'password_path' => 'security.credentials.password',
                    ),
                ),
            ),
        ));
