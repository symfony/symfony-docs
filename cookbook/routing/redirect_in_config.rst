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

        # app/config/routing.yml

        # load some routes - one should ultimately have the path "/app"
        AppBundle:
            resource: '@AppBundle/Controller/'
            type:     annotation
            prefix:   /app

        # redirecting the root
        root:
            path: /
            defaults:
                _controller: FrameworkBundle:Redirect:urlRedirect
                path: /app
                permanent: true

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <!-- load some routes - one should ultimately have the path "/app" -->
            <import resource="@AppBundle/Controller/"
                type="annotation"
                prefix="/app"
            />

            <!-- redirecting the root -->
            <route id="root" path="/">
                <default key="_controller">FrameworkBundle:Redirect:urlRedirect</default>
                <default key="path">/app</default>
                <default key="permanent">true</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();

        // load some routes - one should ultimately have the path "/app"
        $appRoutes = $loader->import("@AppBundle/Controller/", "annotation");
        $appRoutes->setPrefix('/app');

        $collection->addCollection($appRoutes);

        // redirecting the root
        $collection->add('root', new Route('/', array(
            '_controller' => 'FrameworkBundle:Redirect:urlRedirect',
            'path'        => '/app',
            'permanent'   => true,
        )));

        return $collection;

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

        # app/config/routing.yml

        # ...

        # redirecting the admin home
        root:
            path: /wp-admin
            defaults:
                _controller: FrameworkBundle:Redirect:redirect
                route: sonata_admin_dashboard
                permanent: true

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <!-- ... -->

            <!-- redirecting the admin home -->
            <route id="root" path="/wp-admin">
                <default key="_controller">FrameworkBundle:Redirect:redirect</default>
                <default key="route">sonata_admin_dashboard</default>
                <default key="permanent">true</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        // ...

        // redirecting the root
        $collection->add('root', new Route('/wp-admin', array(
            '_controller' => 'FrameworkBundle:Redirect:redirect',
            'route'       => 'sonata_admin_dashboard',
            'permanent'   => true,
        )));

        return $collection;

.. caution::

    Because you are redirecting to a route instead of a path, the required
    option is called ``route`` in the ``redirect`` action, instead of ``path``
    in the ``urlRedirect`` action.
