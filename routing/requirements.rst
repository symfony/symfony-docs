.. index::
    single: Routing; Requirements

How to Define Route Requirements
================================

Take a quick look at the routes that have been created so far:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/BlogController.php

        // ...
        class BlogController extends Controller
        {
            /**
             * @Route("/blog/{page}", defaults={"page" = 1})
             */
            public function indexAction($page)
            {
                // ...
            }

            /**
             * @Route("/blog/{slug}")
             */
            public function showAction($slug)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        blog:
            path:      /blog/{page}
            defaults:  { _controller: AppBundle:Blog:index, page: 1 }

        blog_show:
            path:      /blog/{slug}
            defaults:  { _controller: AppBundle:Blog:show }

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

            <route id="blog_show" path="/blog/{slug}">
                <default key="_controller">AppBundle:Blog:show</default>
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

        $collection->add('blog_show', new Route('/blog/{show}', array(
            '_controller' => 'AppBundle:Blog:show',
        )));

        return $collection;

Can you spot the problem? Notice that both routes have patterns that match
URLs that look like ``/blog/*``. The Symfony router will always choose the
**first** matching route it finds. In other words, the ``blog_show`` route
will *never* be matched. Instead, a URL like ``/blog/my-blog-post`` will match
the first route (``blog``) and return a nonsense value of ``my-blog-post``
to the ``{page}`` parameter.

======================  ========  ===============================
URL                     Route     Parameters
======================  ========  ===============================
``/blog/2``             ``blog``  ``{page}`` = ``2``
``/blog/my-blog-post``  ``blog``  ``{page}`` = ``"my-blog-post"``
======================  ========  ===============================

The answer to the problem is to add route *requirements* or route *conditions*
(see :ref:`book-routing-conditions`). The routes in this example would work
perfectly if the ``/blog/{page}`` path *only* matched URLs where the ``{page}``
portion is an integer. Fortunately, regular expression requirements can easily
be added for each parameter. For example:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/BlogController.php

        // ...

        /**
         * @Route("/blog/{page}", defaults={"page": 1}, requirements={
         *     "page": "\d+"
         * })
         */
        public function indexAction($page)
        {
            // ...
        }

    .. code-block:: yaml

        # app/config/routing.yml
        blog:
            path:      /blog/{page}
            defaults:  { _controller: AppBundle:Blog:index, page: 1 }
            requirements:
                page:  \d+

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
                <requirement key="page">\d+</requirement>
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
        ), array(
            'page' => '\d+',
        )));

        return $collection;

The ``\d+`` requirement is a regular expression that says that the value of
the ``{page}`` parameter must be a digit (i.e. a number). The ``blog`` route
will still match on a URL like ``/blog/2`` (because 2 is a number), but it
will no longer match a URL like ``/blog/my-blog-post`` (because ``my-blog-post``
is *not* a number).

As a result, a URL like ``/blog/my-blog-post`` will now properly match the
``blog_show`` route.

========================  =============  ===============================
URL                       Route          Parameters
========================  =============  ===============================
``/blog/2``               ``blog``       ``{page}`` = ``2``
``/blog/my-blog-post``    ``blog_show``  ``{slug}`` = ``my-blog-post``
``/blog/2-my-blog-post``  ``blog_show``  ``{slug}`` = ``2-my-blog-post``
========================  =============  ===============================

.. sidebar:: Earlier Routes always Win

    What this all means is that the order of the routes is very important.
    If the ``blog_show`` route were placed above the ``blog`` route, the
    URL ``/blog/2`` would match ``blog_show`` instead of ``blog`` since the
    ``{slug}`` parameter of ``blog_show`` has no requirements. By using proper
    ordering and clever requirements, you can accomplish just about anything.

Since the parameter requirements are regular expressions, the complexity
and flexibility of each requirement is entirely up to you. Suppose the homepage
of your application is available in two different languages, based on the
URL:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/MainController.php

        // ...
        class MainController extends Controller
        {
            /**
             * @Route("/{_locale}", defaults={"_locale": "en"}, requirements={
             *     "_locale": "en|fr"
             * })
             */
            public function homepageAction($_locale)
            {
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        homepage:
            path:      /{_locale}
            defaults:  { _controller: AppBundle:Main:homepage, _locale: en }
            requirements:
                _locale:  en|fr

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="homepage" path="/{_locale}">
                <default key="_controller">AppBundle:Main:homepage</default>
                <default key="_locale">en</default>
                <requirement key="_locale">en|fr</requirement>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('homepage', new Route('/{_locale}', array(
            '_controller' => 'AppBundle:Main:homepage',
            '_locale'     => 'en',
        ), array(
            '_locale' => 'en|fr',
        )));

        return $collection;

For incoming requests, the ``{_locale}`` portion of the URL is matched against
the regular expression ``(en|fr)``.

=======  ========================
Path     Parameters
=======  ========================
``/``    ``{_locale}`` = ``"en"``
``/en``  ``{_locale}`` = ``"en"``
``/fr``  ``{_locale}`` = ``"fr"``
``/es``  *won't match this route*
=======  ========================

.. tip::

    The route requirements can also include container parameters, as explained
    in :doc:`this article </routing/service_container_parameters>`.
    This comes in handy when the regular expression is very complex and used
    repeatedly in your application.

.. index::
    single: Routing; Method requirement

Adding HTTP Method Requirements
-------------------------------

In addition to the URL, you can also match on the *method* of the incoming
request (i.e. GET, HEAD, POST, PUT, DELETE). Suppose you create an API for
your blog and you have 2 routes: One for displaying a post (on a GET or HEAD
request) and one for updating a post (on a PUT request). This can be
accomplished with the following route configuration:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/MainController.php
        namespace AppBundle\Controller;

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
        // ...

        class BlogApiController extends Controller
        {
            /**
             * @Route("/api/posts/{id}")
             * @Method({"GET","HEAD"})
             */
            public function showAction($id)
            {
                // ... return a JSON response with the post
            }

            /**
             * @Route("/api/posts/{id}")
             * @Method("PUT")
             */
            public function editAction($id)
            {
                // ... edit a post
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        api_post_show:
            path:     /api/posts/{id}
            defaults: { _controller: AppBundle:BlogApi:show }
            methods:  [GET, HEAD]

        api_post_edit:
            path:     /api/posts/{id}
            defaults: { _controller: AppBundle:BlogApi:edit }
            methods:  [PUT]

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="api_post_show" path="/api/posts/{id}" methods="GET|HEAD">
                <default key="_controller">AppBundle:BlogApi:show</default>
            </route>

            <route id="api_post_edit" path="/api/posts/{id}" methods="PUT">
                <default key="_controller">AppBundle:BlogApi:edit</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('api_post_show', new Route('/api/posts/{id}', array(
            '_controller' => 'AppBundle:BlogApi:show',
        ), array(), array(), '', array(), array('GET', 'HEAD')));

        $collection->add('api_post_edit', new Route('/api/posts/{id}', array(
            '_controller' => 'AppBundle:BlogApi:edit',
        ), array(), array(), '', array(), array('PUT')));

        return $collection;

Despite the fact that these two routes have identical paths
(``/api/posts/{id}``), the first route will match only GET or HEAD requests and
the second route will match only PUT requests. This means that you can display
and edit the post with the same URL, while using distinct controllers for the
two actions.

.. note::

    If no ``methods`` are specified, the route will match on *all* methods.

Adding a Host Requirement
-------------------------

You can also match on the HTTP *host* of the incoming request. For more
information, see :doc:`/components/routing/hostname_pattern` in the Routing
component documentation.
