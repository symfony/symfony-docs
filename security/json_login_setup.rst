How to Build a JSON Authentication Endpoint
===========================================

In this entry, you'll build a JSON endpoint to log in your users. Of course, when the
user logs in, you can load your users from anywhere - like the database.
See :ref:`security-user-providers` for details.

First, enable the JSON login under your firewall:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    anonymous: ~
                    json_login:
                        check_path: /login

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
                    <json-login check-path="/login" />
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

        // src/AppBundle/Controller/SecurityController.php

        // ...
        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\Routing\Annotation\Route;

        class SecurityController extends Controller
        {
            /**
             * @Route("/login", name="login")
             */
            public function loginAction(Request $request)
            {
              // Remember to create a route to a 'secure_location' where a user will be 
              // redirected to after a successful login
              return $this->redirectToRoute('secure_location');
            }
            
            
            /**
             * @Route("/secure", name="secure_location")
             */
            public function SecureAction(Request $request)
            {
              if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {              
                return new JsonResponse(array(
                  error' => "Login first",
                ));
              }
              
              return new JsonResponse(array(
                'message' => "This is a secure area",
              ));
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        login:
            path:     /login
            defaults: { _controller: AppBundle:Security:login }
            
        secure_location: 
            path:     /secure
            defaults: { _controller: AppBundle:Security:secure }
        

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
            
            <route id="secure_location" path="/secure">
                <default key="_controller">AppBundle:Security:secure</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('login', new Route('/login', array(
            '_controller' => 'AppBundle:Security:login',
        )));
        
        $collection->add('secure_location', new Route('/secure', array(
            '_controller' => 'AppBundle:Security:secure',
        )));

        return $collection;

When you submit a ``POST`` request to the ``/login`` URL with the 
following JSON document as the body, the security system intercepts the request and perform the authentication:

.. code-block:: json

    {
        "username": "dunglas",
        "password": "MyPassword"
    }
    
The security system takes care of authenticating the user with the submitted username and password and return a json response of whether authentication was successfully or not. 
If the authentication was successfully, the security system will redirect the response to ``secure_location`` route.
This ``secure_location`` can be defined anywhere in your controller. Just remember to guard it against accessing it without authentication.

If the JSON document has a different structure, you can specify the path to access the ``username`` and ``password`` properties using the ``username_path`` and ``password_path`` keys (they default respectively to ``username`` and ``password``). For example, if the JSON document has the following structure:

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
