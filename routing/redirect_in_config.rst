.. index::
   single: Routing; Redirect using Framework:RedirectController

How to Configure a Redirect without a custom Controller
=======================================================

Sometimes, a URL needs to redirect to another URL. You can do that by creating
a new controller action whose only task is to redirect, but using the
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController` of
the FrameworkBundle is even easier.

You can redirect to a specific path (e.g. ``/about``) or to a specific route
using its name (e.g. ``homepage``).

Redirecting Using a Path
------------------------

Assume there is no default controller for the ``/`` path of your application
and you want to redirect these requests to ``/app``. You will need to use the
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction`
action to redirect to this new url:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml

        # load some routes - one should ultimately have the path "/app"
        controllers:
            resource: ../src/Controller/
            type:     annotation
            prefix:   /app

        # redirecting the homepage
        homepage:
            path: /
            controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction
            defaults:
                path: /app
                permanent: true

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <!-- load some routes - one should ultimately have the path "/app" -->
            <import resource="../src/Controller/"
                type="annotation"
                prefix="/app"
            />

            <!-- redirecting the homepage -->
            <route id="homepage" path="/">
                <default key="_controller">Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction</default>
                <default key="path">/app</default>
                <default key="permanent">true</default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $routes = new RouteCollection();

        // load some routes - one should ultimately have the path "/app"
        $appRoutes = $loader->import("../src/Controller/", "annotation");
        $appRoutes->setPrefix('/app');

        $routes->addCollection($appRoutes);

        // redirecting the homepage
        $routes->add('homepage', new Route('/', array(
            '_controller' => 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction',
            'path'        => '/app',
            'permanent'   => true,
        )));

        return $routes;

In this example, you configured a route for the ``/`` path and let the
``RedirectController`` redirect it to ``/app``. The ``permanent`` switch
tells the action to issue a ``301`` HTTP status code instead of the default
``302`` HTTP status code.

Redirecting Using a Route
-------------------------

Assume you are migrating your website from WordPress to Symfony, you want to
redirect ``/wp-admin`` to the route ``sonata_admin_dashboard``. You don't know
the path, only the route name. This can be achieved using the
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::redirectAction`
action:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml

        # ...

        admin:
            path: /wp-admin
            controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction
            defaults:
                route: sonata_admin_dashboard
                permanent: true

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <!-- ... -->

            <route id="admin" path="/wp-admin">
                <default key="_controller">Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction</default>
                <default key="route">sonata_admin_dashboard</default>
                <default key="permanent">true</default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $routes = new RouteCollection();
        // ...

        $routes->add('admin', new Route('/wp-admin', array(
            '_controller' => 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction',
            'route'       => 'sonata_admin_dashboard',
            'permanent'   => true,
        )));

        return $routes;

.. caution::

    Because you are redirecting to a route instead of a path, the required
    option is called ``route`` in the ``redirect()`` action, instead of ``path``
    in the ``urlRedirect()`` action.

Keeping the Request Method when Redirecting
-------------------------------------------

The redirections performed in the previous examples use the ``301`` and ``302``
HTTP status codes. For legacy reasons, these HTTP redirections change the method
of ``POST`` requests to ``GET`` (because redirecting a ``POST`` request didn't
work well in old browsers).

However, in some scenarios it's either expected or required that the redirection
request uses the same HTTP method. That's why the HTTP standard defines two
additional status codes (``307`` and ``308``) to perform temporary/permanent
redirects that maintain the original request method.

The :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction`
and :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::redirectAction`
methods accept an additional argument called ``keepRequestMethod``. When it's
set to ``true``, temporary redirects use ``307`` code instead of ``302`` and
permanent redirects use ``308`` code instead of ``301``::

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml

        # redirects with the 308 status code
        route_foo:
            # ...
            controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction
            defaults:
                # ...
                permanent: true
                keepRequestMethod: true

        # redirects with the 307 status code
        route_bar:
            # ...
            controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction
            defaults:
                # ...
                permanent: false
                keepRequestMethod: true

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <!-- redirects with the 308 status code -->
            <route id="route_foo" path="...">
                <!-- ... -->
                <default key="_controller">Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction</default>
                <default key="permanent">true</default>
                <default key="keepRequestMethod">true</default>
            </route>

            <!-- redirects with the 307 status code -->
            <route id="route_bar" path="...">
                <!-- ... -->
                <default key="_controller">Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction</default>
                <default key="permanent">false</default>
                <default key="keepRequestMethod">true</default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();

        // redirects with the 308 status code
        $collection->add('route_foo', new Route('...', array(
            // ...
            '_controller'       => 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction',
            'permanent'         => true,
            'keepRequestMethod' => true,
        )));

        // redirects with the 307 status code
        $collection->add('route_bar', new Route('...', array(
            // ...
            '_controller'       => 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction',
            'permanent'         => false,
            'keepRequestMethod' => true,
        )));

        return $collection;
