.. index::
   single: Routing; Matching on Hostname

How to Match a Route Based on the Host
======================================

You can also match any route with the HTTP *host* of the incoming request.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/MainController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class MainController extends AbstractController
        {
            /**
             * @Route("/", name="mobile_homepage", host="m.example.com")
             */
            public function mobileHomepage()
            {
                // ...
            }

            /**
             * @Route("/", name="homepage")
             */
            public function homepage()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        mobile_homepage:
            path:       /
            host:       m.example.com
            controller: App\Controller\MainController::mobileHomepage

        homepage:
            path:       /
            controller: App\Controller\MainController::homepage

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="mobile_homepage"
                path="/"
                host="m.example.com"
                controller="App\Controller\MainController::mobileHomepage"/>

            <route id="homepage" path="/" controller="App\Controller\MainController::homepage"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\MainController;

        return function (RoutingConfigurator $routes) {
            $routes->add('mobile_homepage', '/')
                ->controller([MainController::class, 'mobileHomepage'])
                ->host('m.example.com')
            ;
            $routes->add('homepage', '/')
                ->controller([MainController::class, 'homepage'])
            ;
        };

        return $routes;

Both routes match the same path ``/``, however the first one will match
only if the host is ``m.example.com``.

Using Placeholders
------------------

The host option uses the same syntax as the path matching system. This means
you can use placeholders in your hostname:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/MainController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class MainController extends AbstractController
        {
            /**
             * @Route("/", name="projects_homepage", host="{project}.example.com")
             */
            public function projectsHomepage(string $project)
            {
                // ...
            }

            /**
             * @Route("/", name="homepage")
             */
            public function homepage()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        projects_homepage:
            path:       /
            host:       "{project}.example.com"
            controller: App\Controller\MainController::projectsHomepage

        homepage:
            path:       /
            controller: App\Controller\MainController::homepage

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="projects_homepage"
                path="/"
                host="{project}.example.com"
                controller="App\Controller\MainController::projectsHomepage"/>

            <route id="homepage" path="/" controller="App\Controller\MainController::homepage"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\MainController;

        return function (RoutingConfigurator $routes) {
            $routes->add('project_homepage', '/')
                ->controller([MainController::class, 'projectHomepage'])
                ->host('{project}.example.com')
            ;
            $routes->add('homepage', '/')
                ->controller([MainController::class, 'homepage'])
            ;
        };

Also, any requirement or default can be set for these placeholders. For
instance, if you want to match both ``m.example.com`` and
``mobile.example.com``, you can use this:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/MainController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class MainController extends AbstractController
        {
            /**
             * @Route(
             *     "/",
             *     name="mobile_homepage",
             *     host="{subdomain}.example.com",
             *     defaults={"subdomain"="m"},
             *     requirements={"subdomain"="m|mobile"}
             * )
             */
            public function mobileHomepage()
            {
                // ...
            }

            /**
             * @Route("/", name="homepage")
             */
            public function homepage()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        mobile_homepage:
            path:       /
            host:       "{subdomain}.example.com"
            controller: App\Controller\MainController::mobileHomepage
            defaults:
                subdomain: m
            requirements:
                subdomain: m|mobile

        homepage:
            path:       /
            controller: App\Controller\MainController::homepage

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="mobile_homepage"
                path="/"
                host="{subdomain}.example.com"
                controller="App\Controller\MainController::mobileHomepage">
                <default key="subdomain">m</default>
                <requirement key="subdomain">m|mobile</requirement>
            </route>

            <route id="homepage" path="/" controller="App\Controller\MainController::homepage"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\MainController;

        return function (RoutingConfigurator $routes) {
            $routes->add('mobile_homepage', '/')
                ->controller([MainController::class, 'mobileHomepage'])
                ->host('{subdomain}.example.com')
                ->defaults([
                    'subdomain' => 'm',
                ])
                ->requirements([
                    'subdomain' => 'm|mobile',
                ])
            ;
            $routes->add('homepage', '/')
                ->controller([MainController::class, 'homepage'])
            ;
        };

.. tip::

    You can also use service parameters if you do not want to hardcode the
    hostname:

    .. configuration-block::

        .. code-block:: php-annotations

            // src/Controller/MainController.php
            namespace App\Controller;

            use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
            use Symfony\Component\Routing\Annotation\Route;

            class MainController extends AbstractController
            {
                /**
                 * @Route(
                 *     "/",
                 *     name="mobile_homepage",
                 *     host="m.{domain}",
                 *     defaults={"domain"="%domain%"},
                 *     requirements={"domain"="%domain%"}
                 * )
                 */
                public function mobileHomepage()
                {
                    // ...
                }

                /**
                 * @Route("/", name="homepage")
                 */
                public function homepage()
                {
                    // ...
                }
            }

        .. code-block:: yaml

            # config/routes.yaml
            mobile_homepage:
                path:       /
                host:       "m.{domain}"
                controller: App\Controller\MainController::mobileHomepage
                defaults:
                    domain: '%domain%'
                requirements:
                    domain: '%domain%'

            homepage:
                path:       /
                controller: App\Controller\MainController::homepage

        .. code-block:: xml

            <!-- config/routes.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                    https://symfony.com/schema/routing/routing-1.0.xsd">

                <route id="mobile_homepage"
                    path="/"
                    host="m.{domain}"
                    controller="App\Controller\MainController::mobileHomepage">
                    <default key="domain">%domain%</default>
                    <requirement key="domain">%domain%</requirement>
                </route>

                <route id="homepage" path="/" controller="App\Controller\MainController::homepage"/>
            </routes>

        .. code-block:: php

            // config/routes.php
            namespace Symfony\Component\Routing\Loader\Configurator;

            use App\Controller\MainController;

            return function (RoutingConfigurator $routes) {
                $routes->add('mobile_homepage', '/')
                    ->controller([MainController::class, 'mobileHomepage'])
                    ->host('m.{domain}')
                    ->defaults([
                        'domain' => '%domain%',
                    ])
                    ->requirements([
                        'domain' => '%domain%',
                    ])
                ;
                $routes->add('homepage', '/')
                    ->controller([MainController::class, 'homepage'])
                ;
            };

.. tip::

    Make sure you also include a default option for the ``domain`` placeholder,
    otherwise you need to include a domain value each time you generate
    a URL using the route.

.. _component-routing-host-imported:

Using Host Matching of Imported Routes
--------------------------------------

You can also set the host option on imported routes:

.. configuration-block::

    .. code-block:: php-annotations

        // vendor/acme/acme-hello-bundle/src/Controller/MainController.php
        namespace Acme\AcmeHelloBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        /**
         * @Route(host="hello.example.com")
         */
        class MainController extends AbstractController
        {
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        app_hello:
            resource: '@AcmeHelloBundle/Resources/config/routing.yaml'
            host:     "hello.example.com"

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="@AcmeHelloBundle/Resources/config/routing.xml" host="hello.example.com"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return function (RoutingConfigurator $routes) {
            $routes->import("@AcmeHelloBundle/Resources/config/routing.php")
                ->host('hello.example.com')
            ;
        };

The host ``hello.example.com`` will be set on each route loaded from the new
routing resource.

Testing your Controllers
------------------------

You need to set the Host HTTP header on your request objects if you want to get
past url matching in your functional tests::

    $crawler = $client->request(
        'GET',
        '/',
        [],
        [],
        ['HTTP_HOST' => 'm.' . $client->getContainer()->getParameter('domain')]
    );
