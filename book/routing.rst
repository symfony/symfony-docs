.. index::
   single: Routing

Routing
=======

Beautiful URLs are an absolute must for any serious web application. This
means leaving behind ugly URLs like ``index.php?article_id=57`` in favor
of something like ``/read/intro-to-symfony``.

Having flexibility is even more important. What if you need to change the
URL of a page from ``/blog`` to ``/news``? How many links should you need to
hunt down and update to make the change? If you're using Symfony's router,
the change is simple.

The Symfony router lets you define creative URLs that you map to different
areas of your application. By the end of this chapter, you'll be able to:

* Create complex routes that map to controllers
* Generate URLs inside templates and controllers
* Load routing resources from bundles (or anywhere else)
* Debug your routes

.. index::
   single: Routing; Basics

Routing in Action
-----------------

A *route* is a map from a URL path to a controller. For example, suppose
you want to match any URL like ``/blog/my-post`` or ``/blog/all-about-symfony``
and send it to a controller that can look up and render that blog entry.
The route is simple:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/BlogController.php
        namespace AppBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class BlogController extends Controller
        {
            /**
             * @Route("/blog/{slug}", name="blog_show")
             */
            public function showAction($slug)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
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

            <route id="blog_show" path="/blog/{slug}">
                <default key="_controller">AppBundle:Blog:show</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog_show', new Route('/blog/{slug}', array(
            '_controller' => 'AppBundle:Blog:show',
        )));

        return $collection;

The path defined by the ``blog_show`` route acts like ``/blog/*`` where
the wildcard is given the name ``slug``. For the URL ``/blog/my-blog-post``,
the ``slug`` variable gets a value of ``my-blog-post``, which is available
for you to use in your controller (keep reading). The ``blog_show`` is the
internal name of the route, which doesn't have any meaning yet and just needs
to be unique. Later, you'll use it to generate URLs.

If you don't want to use annotations, because you don't like them or because
you don't want to depend on the SensioFrameworkExtraBundle, you can also use
Yaml, XML or PHP. In these formats, the ``_controller`` parameter is a special
key that tells Symfony which controller should be executed when a URL matches
this route. The ``_controller`` string is called the
:ref:`logical name <controller-string-syntax>`. It follows a pattern that
points to a specific PHP class and method, in this case the
``AppBundle\Controller\BlogController::showAction`` method.

Congratulations! You've just created your first route and connected it to
a controller. Now, when you visit ``/blog/my-post``, the ``showAction`` controller
will be executed and the ``$slug`` variable will be equal to ``my-post``.

This is the goal of the Symfony router: to map the URL of a request to a
controller. Along the way, you'll learn all sorts of tricks that make mapping
even the most complex URLs easy.

.. index::
   single: Routing; Under the hood

Routing: Under the Hood
-----------------------

When a request is made to your application, it contains an address to the
exact "resource" that the client is requesting. This address is called the
URL, (or URI), and could be ``/contact``, ``/blog/read-me``, or anything
else. Take the following HTTP request for example:

.. code-block:: text

    GET /blog/my-blog-post

The goal of the Symfony routing system is to parse this URL and determine
which controller should be executed. The whole process looks like this:

#. The request is handled by the Symfony front controller (e.g. ``app.php``);

#. The Symfony core (i.e. Kernel) asks the router to inspect the request;

#. The router matches the incoming URL to a specific route and returns information
   about the route, including the controller that should be executed;

#. The Symfony Kernel executes the controller, which ultimately returns
   a ``Response`` object.

.. figure:: /images/request-flow.png
   :align: center
   :alt: Symfony request flow

   The routing layer is a tool that translates the incoming URL into a specific
   controller to execute.

.. index::
   single: Routing; Creating routes

Creating Routes
---------------

Symfony loads all the routes for your application from a single routing configuration
file. The file is usually ``app/config/routing.yml``, but can be configured
to be anything (including an XML or PHP file) via the application configuration
file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            router: { resource: '%kernel.root_dir%/config/routing.yml' }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- ... -->
                <framework:router resource="%kernel.root_dir%/config/routing.xml" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'router' => array(
                'resource' => '%kernel.root_dir%/config/routing.php',
            ),
        ));

.. tip::

    Even though all routes are loaded from a single file, it's common practice
    to include additional routing resources. To do so, just point out in the
    main routing configuration file which external files should be included.
    See the :ref:`routing-include-external-resources` section for more
    information.

Basic Route Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~

Defining a route is easy, and a typical application will have lots of routes.
A basic route consists of just two parts: the ``path`` to match and a
``defaults`` array:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/MainController.php

        // ...
        class MainController extends Controller
        {
            /**
             * @Route("/")
             */
            public function homepageAction()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        _welcome:
            path:      /
            defaults:  { _controller: AppBundle:Main:homepage }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="_welcome" path="/">
                <default key="_controller">AppBundle:Main:homepage</default>
            </route>

        </routes>

    ..  code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('_welcome', new Route('/', array(
            '_controller' => 'AppBundle:Main:homepage',
        )));

        return $collection;

This route matches the homepage (``/``) and maps it to the
``AppBundle:Main:homepage`` controller. The ``_controller`` string is
translated by Symfony into an actual PHP function and executed. That process
will be explained shortly in the :ref:`controller-string-syntax` section.

.. index::
   single: Routing; Placeholders

Routing with Placeholders
~~~~~~~~~~~~~~~~~~~~~~~~~

Of course the routing system supports much more interesting routes. Many
routes will contain one or more named "wildcard" placeholders:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/BlogController.php

        // ...
        class BlogController extends Controller
        {
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

            <route id="blog_show" path="/blog/{slug}">
                <default key="_controller">AppBundle:Blog:show</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog_show', new Route('/blog/{slug}', array(
            '_controller' => 'AppBundle:Blog:show',
        )));

        return $collection;

The path will match anything that looks like ``/blog/*``. Even better,
the value matching the ``{slug}`` placeholder will be available inside your
controller. In other words, if the URL is ``/blog/hello-world``, a ``$slug``
variable, with a value of ``hello-world``, will be available in the controller.
This can be used, for example, to load the blog post matching that string.

The path will *not*, however, match simply ``/blog``. That's because,
by default, all placeholders are required. This can be changed by adding
a placeholder value to the ``defaults`` array.

Required and Optional Placeholders
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To make things more exciting, add a new route that displays a list of all
the available blog posts for this imaginary blog application:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/BlogController.php

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

        # app/config/routing.yml
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

        // src/AppBundle/Controller/BlogController.php

        // ...

        /**
         * @Route("/blog/{page}")
         */
        public function indexAction($page)
        {
            // ...
        }

    .. code-block:: yaml

        # app/config/routing.yml
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

        // src/AppBundle/Controller/BlogController.php

        // ...

        /**
         * @Route("/blog/{page}", defaults={"page" = 1})
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

By adding ``page`` to the ``defaults`` key, the ``{page}`` placeholder is no
longer required. The URL ``/blog`` will match this route and the value of
the ``page`` parameter will be set to ``1``. The URL ``/blog/2`` will also
match, giving the ``page`` parameter a value of ``2``. Perfect.

===========  ========  ==================
URL          Route     Parameters
===========  ========  ==================
``/blog``    ``blog``  ``{page}`` = ``1``
``/blog/1``  ``blog``  ``{page}`` = ``1``
``/blog/2``  ``blog``  ``{page}`` = ``2``
===========  ========  ==================

.. caution::

    Of course, you can have more than one optional placeholder (e.g.
    ``/blog/{slug}/{page}``), but everything after an optional placeholder must
    be optional. For example, ``/{page}/blog`` is a valid path, but ``page``
    will always be required (i.e. simply ``/blog`` will not match this route).

.. tip::

    Routes with optional parameters at the end will not match on requests
    with a trailing slash (i.e. ``/blog/`` will not match, ``/blog`` will match).

.. index::
   single: Routing; Requirements

.. _book-routing-requirements:

Adding Requirements
~~~~~~~~~~~~~~~~~~~

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

.. index::
   single: Routing; Method requirement

Adding HTTP Method Requirements
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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
~~~~~~~~~~~~~~~~~~~~~~~~~

You can also match on the HTTP *host* of the incoming request. For more
information, see :doc:`/components/routing/hostname_pattern` in the Routing
component documentation.

.. _book-routing-conditions:

Completely Customized Route Matching with Conditions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As you've seen, a route can be made to match only certain routing wildcards
(via regular expressions), HTTP methods, or host names. But the routing system
can be extended to have an almost infinite flexibility using ``conditions``:

.. configuration-block::

    .. code-block:: yaml

        contact:
            path:     /contact
            defaults: { _controller: AcmeDemoBundle:Main:contact }
            condition: "context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'"

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/contact">
                <default key="_controller">AcmeDemoBundle:Main:contact</default>
                <condition>context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'</condition>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('contact', new Route(
            '/contact', array(
                '_controller' => 'AcmeDemoBundle:Main:contact',
            ),
            array(),
            array(),
            '',
            array(),
            array(),
            'context.getMethod() in ["GET", "HEAD"] and request.headers.get("User-Agent") matches "/firefox/i"'
        ));

        return $collection;

The ``condition`` is an expression, and you can learn more about its syntax
here: :doc:`/components/expression_language/syntax`. With this, the route
won't match unless the HTTP method is either GET or HEAD *and* if the ``User-Agent``
header matches ``firefox``.

You can do any complex logic you need in the expression by leveraging two
variables that are passed into the expression:

``context``
    An instance of :class:`Symfony\\Component\\Routing\\RequestContext`, which
    holds the most fundamental information about the route being matched.
``request``
    The Symfony :class:`Symfony\\Component\\HttpFoundation\\Request` object
    (see :ref:`component-http-foundation-request`).

.. caution::

    Conditions are *not* taken into account when generating a URL.

.. sidebar:: Expressions are Compiled to PHP

    Behind the scenes, expressions are compiled down to raw PHP. Our example
    would generate the following PHP in the cache directory::

        if (rtrim($pathinfo, '/contact') === '' && (
            in_array($context->getMethod(), array(0 => "GET", 1 => "HEAD"))
            && preg_match("/firefox/i", $request->headers->get("User-Agent"))
        )) {
            // ...
        }

    Because of this, using the ``condition`` key causes no extra overhead
    beyond the time it takes for the underlying PHP to execute.

.. index::
   single: Routing; Advanced example
   single: Routing; _format parameter

.. _advanced-routing-example:

Advanced Routing Example
~~~~~~~~~~~~~~~~~~~~~~~~

At this point, you have everything you need to create a powerful routing
structure in Symfony. The following is an example of just how flexible the
routing system can be:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/ArticleController.php

        // ...
        class ArticleController extends Controller
        {
            /**
             * @Route(
             *     "/articles/{_locale}/{year}/{title}.{_format}",
             *     defaults={"_format": "html"},
             *     requirements={
             *         "_locale": "en|fr",
             *         "_format": "html|rss",
             *         "year": "\d+"
             *     }
             * )
             */
            public function showAction($_locale, $year, $title)
            {
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        article_show:
          path:     /articles/{_locale}/{year}/{title}.{_format}
          defaults: { _controller: AppBundle:Article:show, _format: html }
          requirements:
              _locale:  en|fr
              _format:  html|rss
              year:     \d+

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="article_show"
                path="/articles/{_locale}/{year}/{title}.{_format}">

                <default key="_controller">AppBundle:Article:show</default>
                <default key="_format">html</default>
                <requirement key="_locale">en|fr</requirement>
                <requirement key="_format">html|rss</requirement>
                <requirement key="year">\d+</requirement>

            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add(
            'article_show',
            new Route('/articles/{_locale}/{year}/{title}.{_format}', array(
                '_controller' => 'AppBundle:Article:show',
                '_format'     => 'html',
            ), array(
                '_locale' => 'en|fr',
                '_format' => 'html|rss',
                'year'    => '\d+',
            ))
        );

        return $collection;

As you've seen, this route will only match if the ``{_locale}`` portion of
the URL is either ``en`` or ``fr`` and if the ``{year}`` is a number. This
route also shows how you can use a dot between placeholders instead of
a slash. URLs matching this route might look like:

* ``/articles/en/2010/my-post``
* ``/articles/fr/2010/my-post.rss``
* ``/articles/en/2013/my-latest-post.html``

.. _book-routing-format-param:

.. sidebar:: The Special ``_format`` Routing Parameter

    This example also highlights the special ``_format`` routing parameter.
    When using this parameter, the matched value becomes the "request format"
    of the ``Request`` object.

    Ultimately, the request format is used for such things as setting the
    ``Content-Type`` of the response (e.g. a ``json`` request format translates
    into a ``Content-Type`` of ``application/json``). It can also be used in the
    controller to render a different template for each value of ``_format``.
    The ``_format`` parameter is a very powerful way to render the same content
    in different formats.

    In Symfony versions previous to 3.0, it is possible to override the request
    format by adding a query parameter named ``_format`` (for example:
    ``/foo/bar?_format=json``). Relying on this behavior not only is considered
    a bad practice but it will complicate the upgrade of your applications to
    Symfony 3.

.. note::

    Sometimes you want to make certain parts of your routes globally configurable.
    Symfony provides you with a way to do this by leveraging service container
    parameters. Read more about this in ":doc:`/cookbook/routing/service_container_parameters`".

Special Routing Parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~

As you've seen, each routing parameter or default value is eventually available
as an argument in the controller method. Additionally, there are three parameters
that are special: each adds a unique piece of functionality inside your application:

``_controller``
    As you've seen, this parameter is used to determine which controller is
    executed when the route is matched.

``_format``
    Used to set the request format (:ref:`read more <book-routing-format-param>`).

``_locale``
    Used to set the locale on the request (:ref:`read more <book-translation-locale-url>`).

.. index::
   single: Routing; Controllers
   single: Controller; String naming format

.. _controller-string-syntax:

Controller Naming Pattern
-------------------------

Every route must have a ``_controller`` parameter, which dictates which
controller should be executed when that route is matched. This parameter
uses a simple string pattern called the *logical controller name*, which
Symfony maps to a specific PHP method and class. The pattern has three parts,
each separated by a colon:

    **bundle**:**controller**:**action**

For example, a ``_controller`` value of ``AppBundle:Blog:show`` means:

=========  ==================  ==============
Bundle     Controller Class    Method Name
=========  ==================  ==============
AppBundle  ``BlogController``  ``showAction``
=========  ==================  ==============

The controller might look like this::

    // src/AppBundle/Controller/BlogController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class BlogController extends Controller
    {
        public function showAction($slug)
        {
            // ...
        }
    }

Notice that Symfony adds the string ``Controller`` to the class name (``Blog``
=> ``BlogController``) and ``Action`` to the method name (``show`` => ``showAction``).

You could also refer to this controller using its fully-qualified class name
and method: ``AppBundle\Controller\BlogController::showAction``. But if you
follow some simple conventions, the logical name is more concise and allows
more flexibility.

.. note::

   In addition to using the logical name or the fully-qualified class name,
   Symfony supports a third way of referring to a controller. This method
   uses just one colon separator (e.g. ``service_name:indexAction``) and
   refers to the controller as a service (see :doc:`/cookbook/controller/service`).

Route Parameters and Controller Arguments
-----------------------------------------

The route parameters (e.g. ``{slug}``) are especially important because
each is made available as an argument to the controller method::

    public function showAction($slug)
    {
        // ...
    }

In reality, the entire ``defaults`` collection is merged with the parameter
values to form a single array. Each key of that array is available as an
argument on the controller.

In other words, for each argument of your controller method, Symfony looks
for a route parameter of that name and assigns its value to that argument.
In the advanced example above, any combination (in any order) of the following
variables could be used as arguments to the ``showAction()`` method:

* ``$_locale``
* ``$year``
* ``$title``
* ``$_format``
* ``$_controller``
* ``$_route``

Since the placeholders and ``defaults`` collection are merged together, even
the ``$_controller`` variable is available. For a more detailed discussion,
see :ref:`route-parameters-controller-arguments`.

.. tip::

    The special ``$_route`` variable is set to the name of the route that was
    matched.

You can even add extra information to your route definition and access it
within your controller. For more information on this topic,
see :doc:`/cookbook/routing/extra_information`.

.. index::
   single: Routing; Importing routing resources

.. _routing-include-external-resources:

Including External Routing Resources
------------------------------------

All routes are loaded via a single configuration file - usually
``app/config/routing.yml`` (see `Creating Routes`_ above). However, if you use
routing annotations, you'll need to point the router to the controllers with
the annotations. This can be done by "importing" directories into the routing
configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        app:
            resource: '@AppBundle/Controller/'
            type:     annotation # required to enable the Annotation reader for this resource

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <!-- the type is required to enable the annotation reader for this resource -->
            <import resource="@AppBundle/Controller/" type="annotation"/>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->addCollection(
            // second argument is the type, which is required to enable
            // the annotation reader for this resource
            $loader->import("@AppBundle/Controller/", "annotation")
        );

        return $collection;

.. note::

   When importing resources from YAML, the key (e.g. ``app``) is meaningless.
   Just be sure that it's unique so no other lines override it.

The ``resource`` key loads the given routing resource. In this example the
resource is a directory, where the ``@AppBundle`` shortcut syntax resolves to
the full path of the AppBundle. When pointing to a directory, all files in that
directory are parsed and put into the routing.

.. note::

    You can also include other routing configuration files, this is often used
    to import the routing of third party bundles:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/routing.yml
            app:
                resource: '@AcmeOtherBundle/Resources/config/routing.yml'

        .. code-block:: xml

            <!-- app/config/routing.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                    http://symfony.com/schema/routing/routing-1.0.xsd">

                <import resource="@AcmeOtherBundle/Resources/config/routing.xml" />
            </routes>

        .. code-block:: php

            // app/config/routing.php
            use Symfony\Component\Routing\RouteCollection;

            $collection = new RouteCollection();
            $collection->addCollection(
                $loader->import("@AcmeOtherBundle/Resources/config/routing.php")
            );

            return $collection;

Prefixing Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~

You can also choose to provide a "prefix" for the imported routes. For example,
suppose you want to prefix all routes in the AppBundle with ``/site`` (e.g.
``/site/blog/{slug}`` instead of ``/blog/{slug}``):

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        app:
            resource: '@AppBundle/Controller/'
            type:     annotation
            prefix:   /site

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <import
                resource="@AppBundle/Controller/"
                type="annotation"
                prefix="/site" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;

        $app = $loader->import('@AppBundle/Controller/', 'annotation');
        $app->addPrefix('/site');

        $collection = new RouteCollection();
        $collection->addCollection($app);

        return $collection;

The path of each route being loaded from the new routing resource will now
be prefixed with the string ``/site``.

Adding a Host Requirement to Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can set the host regex on imported routes. For more information, see
:ref:`component-routing-host-imported`.

.. index::
   single: Routing; Debugging

Visualizing & Debugging Routes
------------------------------

While adding and customizing routes, it's helpful to be able to visualize
and get detailed information about your routes. A great way to see every route
in your application is via the ``debug:router`` console command. Execute
the command by running the following from the root of your project.

.. code-block:: bash

    $ php bin/console debug:router

This command will print a helpful list of *all* the configured routes in
your application:

.. code-block:: text

    homepage              ANY       /
    contact               GET       /contact
    contact_process       POST      /contact
    article_show          ANY       /articles/{_locale}/{year}/{title}.{_format}
    blog                  ANY       /blog/{page}
    blog_show             ANY       /blog/{slug}

You can also get very specific information on a single route by including
the route name after the command:

.. code-block:: bash

    $ php bin/console debug:router article_show

Likewise, if you want to test whether a URL matches a given route, you can
use the ``router:match`` console command:

.. code-block:: bash

    $ php bin/console router:match /blog/my-latest-post

This command will print which route the URL matches.

.. code-block:: text

    Route "blog_show" matches

.. index::
   single: Routing; Generating URLs

Generating URLs
---------------

The routing system should also be used to generate URLs. In reality, routing
is a bidirectional system: mapping the URL to a controller+parameters and
a route+parameters back to a URL. The
:method:`Symfony\\Component\\Routing\\Router::match` and
:method:`Symfony\\Component\\Routing\\Router::generate` methods form this bidirectional
system. Take the ``blog_show`` example route from earlier::

    $params = $this->get('router')->match('/blog/my-blog-post');
    // array(
    //     'slug'        => 'my-blog-post',
    //     '_controller' => 'AppBundle:Blog:show',
    // )

    $uri = $this->get('router')->generate('blog_show', array(
        'slug' => 'my-blog-post'
    ));
    // /blog/my-blog-post

To generate a URL, you need to specify the name of the route (e.g. ``blog_show``)
and any wildcards (e.g. ``slug = my-blog-post``) used in the path for that
route. With this information, any URL can easily be generated::

    class MainController extends Controller
    {
        public function showAction($slug)
        {
            // ...

            $url = $this->generateUrl(
                'blog_show',
                array('slug' => 'my-blog-post')
            );
        }
    }

.. note::

    The ``generateUrl()`` method defined in the base
    :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class is
    just a shortcut for this code::

        $url = $this->container->get('router')->generate(
            'blog_show',
            array('slug' => 'my-blog-post')
        );

In an upcoming section, you'll learn how to generate URLs from inside templates.

.. tip::

    If the front-end of your application uses Ajax requests, you might want
    to be able to generate URLs in JavaScript based on your routing configuration.
    By using the `FOSJsRoutingBundle`_, you can do exactly that:

    .. code-block:: javascript

        var url = Routing.generate(
            'blog_show',
            {"slug": 'my-blog-post'}
        );

    For more information, see the documentation for that bundle.

.. index::
   single: Routing; Generating URLs in a template

Generating URLs with Query Strings
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``generate`` method takes an array of wildcard values to generate the URI.
But if you pass extra ones, they will be added to the URI as a query string::

    $this->get('router')->generate('blog', array(
        'page' => 2,
        'category' => 'Symfony'
    ));
    // /blog/2?category=Symfony

Generating URLs from a Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The most common place to generate a URL is from within a template when linking
between pages in your application. This is done just as before, but using
a template helper function:

.. configuration-block::

    .. code-block:: html+twig

        <a href="{{ path('blog_show', {'slug': 'my-blog-post'}) }}">
          Read this blog post.
        </a>

    .. code-block:: html+php

        <a href="<?php echo $view['router']->path('blog_show', array(
            'slug' => 'my-blog-post',
        )) ?>">
            Read this blog post.
        </a>

.. versionadded:: 2.8
    The ``path()`` PHP templating helper was introduced in Symfony 2.8. Prior
    to 2.8, you had to use the ``generate()`` helper method.

.. index::
   single: Routing; Absolute URLs

Generating Absolute URLs
~~~~~~~~~~~~~~~~~~~~~~~~

By default, the router will generate relative URLs (e.g. ``/blog``). From
a controller, simply pass ``true`` to the third argument of the ``generateUrl()``
method::

    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

    $this->generateUrl('blog_show', array('slug' => 'my-blog-post'), UrlGeneratorInterface::ABSOLUTE_URL);
    // http://www.example.com/blog/my-blog-post

From a template, simply use the ``url()`` function (which generates an absolute
URL) rather than the ``path()`` function (which generates a relative URL):

.. configuration-block::

    .. code-block:: html+twig

        <a href="{{ url('blog_show', {'slug': 'my-blog-post'}) }}">
          Read this blog post.
        </a>

    .. code-block:: html+php

        <a href="<?php echo $view['router']->url('blog_show', array(
            'slug' => 'my-blog-post',
        )) ?>">
            Read this blog post.
        </a>

.. versionadded:: 2.8
    The ``url()`` PHP templating helper was introduced in Symfony 2.8. Prior
    to 2.8, you had to use the ``generate()`` helper method with
    ``Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL``
    passed as the third argument.

.. note::

    The host that's used when generating an absolute URL is automatically
    detected using the current ``Request`` object. When generating absolute
    URLs from outside the web context (for instance in a console command) this
    doesn't work. See :doc:`/cookbook/console/sending_emails` to learn how to
    solve this problem.

Summary
-------

Routing is a system for mapping the URL of incoming requests to the controller
function that should be called to process the request. It both allows you
to specify beautiful URLs and keeps the functionality of your application
decoupled from those URLs. Routing is a bidirectional mechanism, meaning that it
should also be used to generate URLs.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/routing/scheme`

.. _`FOSJsRoutingBundle`: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
