How to Build a JSON Authentication Endpoint
===========================================

In this entry, you'll build a JSON endpoint to log in your users. Of course, when the
user logs in, you can load your users from anywhere - like the database.
See :ref:`security-user-providers` for details.

First, enable form login under your firewall:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    anonymous: ~
                    json_login:
                        check_path: login

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="main">
                    <anonymous />
                    <json-login check-path="login" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array(
                    'anonymous'  => null,
                    'json_login' => array(
                        'check_path' => 'login',
                    ),
                ),
            ),
        ));

.. tip::

    The ``check_path`` can also be route names (but cannot have mandatory wildcards - e.g.
    ``/login/{foo}`` where ``foo`` has no default value).

Create a new ``SecurityController`` inside a bundle::

    // src/AppBundle/Controller/SecurityController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class SecurityController extends Controller
    {
    }

Next, configure the route that you earlier used under your ``json_login``
configuration (``login``):

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/SecurityController.php

        // ...
        use Symfony\Component\HttpFoundation\Request;
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class SecurityController extends Controller
        {
            /**
             * @Route("/login", name="login")
             */
            public function loginAction(Request $request)
            {
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        login:
            path:     /login
            defaults: { _controller: AppBundle:Security:login }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="login" path="/login">
                <default key="_controller">AppBundle:Security:login</default>
            </route>
        </routes>

    ..  code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('login', new Route('/login', array(
            '_controller' => 'AppBundle:Security:login',
        )));

        return $collection;

Great!

Don't let this controller confuse you. As you'll see in a moment, when the
user submits the form, the security system automatically handles the form
submission for you. If the user submits an invalid username or password,
this controller reads the form submission error from the security system,
so that it can be displayed back to the user.

In other words the security system itself takes care of checking the submitted
username and password and authenticating the user.

And that's it! When you submit a ``POST`` request to the ``/login`` URL with
the following JSON document as body, the security system will automatically
check the user's credentials and either authenticate the user or throw an error::

.. code-block:: json

    {
        "login": "dunglas",
        "password": "MyPassword"
    }

If the JSON document has a different structure, you can specify the path to
access to the user and password properties using the ``username_path`` and
``password_path`` keys (they default respectively to ``username`` and ``password``).

For example, if the JSON document has the following structure:

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

        # app/config/security.yml
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

        <!-- app/config/security.xml -->
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

        // app/config/security.php
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

.. _`FOSUserBundle`: https://github.com/FriendsOfSymfony/FOSUserBundle
