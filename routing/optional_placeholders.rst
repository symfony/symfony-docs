.. index::
    single: Routing; Optional Placeholders

How to Define Optional Placeholders
===================================

To make things more exciting, add a new route that displays a list of all
the available blog posts for this imaginary blog application:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php

        // ...
        class BlogController extends Controller
        {
            // ...

            /**
             * @Route("/blog")
             */
            public function indexAction()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog:
            path:      /blog
            defaults:  { _controller: AppBundle:Blog:index }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" path="/blog">
                <default key="_controller">AppBundle:Blog:index</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog', new Route('/blog', array(
            '_controller' => 'AppBundle:Blog:index',
        )));

        return $collection;

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
        public function indexAction($page)
        {
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog:
            path:      /blog/{page}
            defaults:  { _controller: AppBundle:Blog:index }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" path="/blog/{page}">
                <default key="_controller">AppBundle:Blog:index</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog', new Route('/blog/{page}', array(
            '_controller' => 'AppBundle:Blog:index',
        )));

        return $collection;

Like the ``{slug}`` placeholder before, the value matching ``{page}`` will
be available inside your controller. Its value can be used to determine which
set of blog posts to display for the given page.

But hold on! Since placeholders are required by default, this route will
no longer match on simply ``/blog``. Instead, to see page 1 of the blog,
you'd need to use the URL ``/blog/1``! Since that's no way for a rich web
app to behave, modify the route to make the ``{page}`` parameter optional.
This is done by including it in the ``defaults`` collection:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php

        // ...

        /**
         * @Route("/blog/{page}", defaults={"page" = 1})
         */
        public function indexAction($page)
        {
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog:
            path:      /blog/{page}
            defaults:  { _controller: AppBundle:Blog:index, page: 1 }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" path="/blog/{page}">
                <default key="_controller">AppBundle:Blog:index</default>
                <default key="page">1</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog', new Route('/blog/{page}', array(
            '_controller' => 'AppBundle:Blog:index',
            'page'        => 1,
        )));

        return $collection;

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

    Of course, you can have more than one optional placeholder (e.g. ``/blog/{slug}/{page}``),
    but everything after an optional placeholder must be optional. For example,
    ``/{page}/blog`` is a valid path, but ``page`` will always be required
    (i.e. simply ``/blog`` will not match this route).

.. tip::

    Routes with optional parameters at the end will not match on requests
    with a trailing slash (i.e. ``/blog/`` will not match, ``/blog`` will match).
