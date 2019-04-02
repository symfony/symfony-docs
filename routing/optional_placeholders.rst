.. index::
    single: Routing; Optional Placeholders

How to Define Optional Placeholders
===================================

To make things more exciting, add a new route that displays a list of all
the available blog posts for this imaginary blog application:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        use Symfony\Component\Routing\Annotation\Route;

        // ...
        class BlogController extends AbstractController
        {
            /**
             * @Route("/blog")
             */
            public function index()
            {
                // ...
            }
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog:
            path:       /blog
            controller: App\Controller\BlogController::index

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" path="/blog" controller="App\Controller\BlogController::index"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\BlogController;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog', '/blog')
                ->controller([BlogController::class, 'index'])
            ;
        };

So far, this route is as simple as possible - it contains no placeholders
and will only match the exact URL ``/blog``. But what if you need this route
to support pagination, where ``/blog/2`` displays the second page of blog
entries? Update the route to have a new ``{page}`` placeholder:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php

        // ...

        /**
         * @Route("/blog/{page}")
         */
        public function index($page)
        {
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog:
            path:       /blog/{page}
            controller: App\Controller\BlogController::index

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" path="/blog/{page}" controller="App\Controller\BlogController::index"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\BlogController;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog', '/blog/{page}')
                ->controller([BlogController::class, 'index'])
            ;
        };

Like the ``{slug}`` placeholder before, the value matching ``{page}`` will
be available inside your controller. Its value can be used to determine which
set of blog posts to display for the given page.

But hold on! Since placeholders are required by default, this route will
no longer match on ``/blog`` alone. Instead, to see page 1 of the blog,
you'd need to use the URL ``/blog/1``! Since that's no way for a rich web
app to behave, modify the route to make the ``{page}`` parameter optional.
This is done by including it in the ``defaults`` collection:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php

        // ...

        /**
         * @Route("/blog/{page}", defaults={"page"=1})
         */
        public function index($page)
        {
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog:
            path:       /blog/{page}
            controller: App\Controller\BlogController::index
            defaults:   { page: 1 }

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" path="/blog/{page}" controller="App\Controller\BlogController::index">
                <default key="page">1</default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\BlogController;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog', '/blog/{page}')
                ->controller([BlogController::class, 'index'])
                ->defaults([
                    'page' => 1,
                ])
            ;
        };

By adding ``page`` to the ``defaults`` key, the ``{page}`` placeholder is
no longer required. The URL ``/blog`` will match this route and the value
of the ``page`` parameter will be set to ``1``. The URL ``/blog/2`` will
also match, giving the ``page`` parameter a value of ``2``. Perfect.

===========  ========  ==================
URL          Route     Parameters
===========  ========  ==================
``/blog``    ``blog``  ``{page}`` = ``1``
``/blog/1``  ``blog``  ``{page}`` = ``1``
``/blog/2``  ``blog``  ``{page}`` = ``2``
===========  ========  ==================

.. caution::

    You can have more than one optional placeholder (e.g. ``/blog/{slug}/{page}``),
    but everything after an optional placeholder must be optional. For example,
    ``/{page}/blog`` is a valid path, but ``page`` will always be required
    (i.e. ``/blog`` will not match this route).

.. tip::

    Routes with optional parameters at the end will not match on requests
    with a trailing slash (i.e. ``/blog/`` will not match, ``/blog`` will match).
