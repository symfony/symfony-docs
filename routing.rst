.. index::
   single: Routing

Routing
=======

Beautiful URLs are a must for any serious web application. This means leaving behind
ugly URLs like ``index.php?article_id=57`` in favor of something like ``/read/intro-to-symfony``.

Having flexibility is even more important. What if you need to change the
URL of a page from ``/blog`` to ``/news``? How many links would you need to
hunt down and update to make the change? If you're using Symfony's router,
the change is simple.

.. index::
   single: Routing; Basics

.. _routing-creating-routes:

Creating Routes
---------------

First, install the annotations package:

.. code-block:: terminal

    $ composer require annotations

A *route* is a map from a URL path to a controller. Suppose you want one route that
matches ``/blog`` exactly and another more dynamic route that can match *any* URL
like ``/blog/my-post`` or ``/blog/all-about-symfony``:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends Controller
        {
            /**
             * Matches /blog exactly
             *
             * @Route("/blog", name="blog_list")
             */
            public function list()
            {
                // ...
            }

            /**
             * Matches /blog/*
             *
             * @Route("/blog/{slug}", name="blog_show")
             */
            public function show($slug)
            {
                // $slug will equal the dynamic part of the URL
                // e.g. at /blog/yay-routing, then $slug='yay-routing'

                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path:     /blog
            controller: App\Controller\BlogController::list

        blog_show:
            path:     /blog/{slug}
            controller: App\Controller\BlogController::show

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" controller="App\Controller\BlogController::list" path="/blog" >
                <!-- settings -->
            </route>

            <route id="blog_show" controller="App\Controller\BlogController::show" path="/blog/{slug}">
                <!-- settings -->
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;
        use App\Controller\BlogController;

        $collection = new RouteCollection();
        $collection->add('blog_list', new Route('/blog', array(
            '_controller' => [BlogController::class, 'list']
        )));
        $collection->add('blog_show', new Route('/blog/{slug}', array(
            '_controller' => [BlogController::class, 'show']
        )));

        return $collection;

Thanks to these two routes:

* If the user goes to ``/blog``, the first route is matched and ``list()``
  is executed;

* If the user goes to ``/blog/*``, the second route is matched and ``show()``
  is executed. Because the route path is ``/blog/{slug}``, a ``$slug`` variable is
  passed to ``show()`` matching that value. For example, if the user goes to
  ``/blog/yay-routing``, then ``$slug`` will equal ``yay-routing``.

Whenever you have a ``{placeholder}`` in your route path, that portion becomes a
wildcard: it matches *any* value. Your controller can now *also* have an argument
called ``$placeholder`` (the wildcard and argument names *must* match).



Each route also has an internal name: ``blog_list`` and ``blog_show``. These can
be anything (as long as each is unique) and don't have any meaning yet. You'll
use them later to :ref:`generate URLs <routing-generate>`.

.. sidebar:: Routing in Other Formats

    The ``@Route`` above each method is called an *annotation*. If you'd rather
    configure your routes in YAML, XML or PHP, that's no problem! Just create a
    new routing file (e.g. ``routes.xml``) and Symfony will automatically use it.

.. _routing-requirements:

Adding {wildcard} Requirements
------------------------------

Imagine the ``blog_list`` route will contain a paginated list of blog posts, with
URLs like ``/blog/2`` and ``/blog/3`` for pages 2 and 3. If you change the route's
path to ``/blog/{page}``, you'll have a problem:

* blog_list: ``/blog/{page}`` will match ``/blog/*``;
* blog_show: ``/blog/{slug}`` will *also* match ``/blog/*``.

When two routes match the same URL, the *first* route that's loaded wins. Unfortunately,
that means that ``/blog/yay-routing`` will match the ``blog_list``. No good!

To fix this, add a *requirement* that the ``{page}`` wildcard can *only* match numbers
(digits):

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends Controller
        {
            /**
             * @Route("/blog/{page}", name="blog_list", requirements={"page"="\d+"})
             */
            public function list($page)
            {
                // ...
            }

            /**
             * @Route("/blog/{slug}", name="blog_show")
             */
            public function show($slug)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path:      /blog/{page}
            controller: App\Controller\BlogController::list
            requirements:
                page: '\d+'

        blog_show:
            # ...

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog/{page}" controller="App\Controller\BlogController::list">
                <requirement key="page">\d+</requirement>
            </route>

            <!-- ... -->
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;
        use App\Controller\BlogController;

        $collection = new RouteCollection();
        $collection->add('blog_list', new Route('/blog/{page}', array(
            '_controller' => [BlogController::class, 'list'],
        ), array(
            'page' => '\d+'
        )));

        // ...

        return $collection;

The ``\d+`` is a regular expression that matches a *digit* of any length. Now:

========================  =============  ===============================
URL                       Route          Parameters
========================  =============  ===============================
``/blog/2``               ``blog_list``  ``$page`` = ``2``
``/blog/yay-routing``     ``blog_show``  ``$slug`` = ``yay-routing``
========================  =============  ===============================

To learn about other route requirements - like HTTP method, hostname and dynamic
expressions - see :doc:`/routing/requirements`.

Prefix all controller route names and URLs
------------------------------------------

You have the possibility to prefix all routes names and URLs used by the action methods of a controller with the ``@Route`` annotation.

Add a name property to the ``@Route`` annotation of the controller class and that will be considered the prefix of all route names

.. code-block:: php

      use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
      //prefix all URLs of this controller's action method with /blog and routes name with blog_
      /**
       * @Route("/blog", name="blog_")
       */
      class BlogController extends Controller
      {
          /**
           * @Route("/", defaults={"page": "1"}, name="index")
           */
          public function indexAction($page, $_format) { ... }

          /**
           * @Route("/posts/{slug}", name="post")
           */
          public function showAction(Post $post) { ... }
      }

In this example, the URL of the index action will be ``/blog/`` and ``/blog/posts/``... and the URL of the show action will be /blog/posts/... It's the same logic for the routes name that will be ``blog_index`` and ``blog_post``. 

Giving {placeholders} a Default Value
-------------------------------------

In the previous example, the ``blog_list`` has a path of ``/blog/{page}``. If
the user visits ``/blog/1``, it will match. But if they visit ``/blog``, it
will **not** match. As soon as you add a ``{placeholder}`` to a route, it
*must* have a value.

So how can you make ``blog_list`` once again match when the user visits
``/blog``? By adding a *default* value:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends Controller
        {
            /**
             * @Route("/blog/{page}", name="blog_list", requirements={"page"="\d+"})
             */
            public function list($page = 1)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path:      /blog/{page}
            controller: App\Controller\BlogController::list
            defaults:
                page: 1
            requirements:
                page: '\d+'

        blog_show:
            # ...

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog/{page}" controller="App\Controller\BlogController::list">
                <default key="page">1</default>

                <requirement key="page">\d+</requirement>
            </route>

            <!-- ... -->
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;
        use App\Controller\BlogController;

        $collection = new RouteCollection();
        $collection->add('blog_list', new Route(
            '/blog/{page}',
            array(
                '_controller' => [BlogController::class, 'list'],
                'page'        => 1,
            ),
            array(
                'page' => '\d+'
            )
        ));

        // ...

        return $collection;

Now, when the user visits ``/blog``, the ``blog_list`` route will match and
``$page`` will default to a value of ``1``.

Listing all of your Routes
--------------------------

As your app grows, you'll eventually have a *lot* of routes! To see them all, run:

.. code-block:: terminal

    $ php bin/console debug:router

.. code-block:: text

    ------------------------------ -------- -------------------------------------
     Name                           Method   Path
    ------------------------------ -------- -------------------------------------
     app_lucky_number              ANY    /lucky/number/{max}
     ...
    ------------------------------ -------- -------------------------------------

.. index::
   single: Routing; Advanced example
   single: Routing; _format parameter

.. _advanced-routing-example:

Advanced Routing Example
------------------------

With all of this in mind, check out this advanced example:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/ArticleController.php

        // ...
        class ArticleController extends Controller
        {
            /**
             * @Route(
             *     "/articles/{_locale}/{year}/{slug}.{_format}",
             *     defaults={"_format": "html"},
             *     requirements={
             *         "_locale": "en|fr",
             *         "_format": "html|rss",
             *         "year": "\d+"
             *     }
             * )
             */
            public function show($_locale, $year, $slug)
            {
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        article_show:
          path:     /articles/{_locale}/{year}/{slug}.{_format}
          controller: App\Controller\ArticleController::show
          defaults:
              _format: html
          requirements:
              _locale:  en|fr
              _format:  html|rss
              year:     \d+

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="article_show"
                path="/articles/{_locale}/{year}/{slug}.{_format}"
                controller="App\Controller\ArticleController::show">

                <default key="_format">html</default>
                <requirement key="_locale">en|fr</requirement>
                <requirement key="_format">html|rss</requirement>
                <requirement key="year">\d+</requirement>

            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;
        use App\Controller\ArticleController;

        $collection = new RouteCollection();
        $collection->add(
            'article_show',
            new Route('/articles/{_locale}/{year}/{slug}.{_format}', array(
                '_controller' => [ArticleController::class, 'show'],
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

.. _routing-format-param:

.. sidebar:: The Special ``_format`` Routing Parameter

    This example also highlights the special ``_format`` routing parameter.
    When using this parameter, the matched value becomes the "request format"
    of the ``Request`` object.

    Ultimately, the request format is used for such things as setting the
    ``Content-Type`` of the response (e.g. a ``json`` request format translates
    into a ``Content-Type`` of ``application/json``).

.. note::

    Sometimes you want to make certain parts of your routes globally configurable.
    Symfony provides you with a way to do this by leveraging service container
    parameters. Read more about this in ":doc:`/routing/service_container_parameters`".

.. caution::

    A route placeholder name cannot start with a digit and cannot be longer than 32 characters.

Special Routing Parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~

As you've seen, each routing parameter or default value is eventually available
as an argument in the controller method. Additionally, there are four parameters
that are special: each adds a unique piece of functionality inside your application:

``_controller``
    As you've seen, this parameter is used to determine which controller is
    executed when the route is matched.

``_format``
    Used to set the request format (:ref:`read more <routing-format-param>`).

``_fragment``
    Used to set the fragment identifier, the optional last part of a URL that
    starts with a ``#`` character and is used to identify a portion of a document.

``_locale``
    Used to set the locale on the request (:ref:`read more <translation-locale-url>`).

.. index::
   single: Routing; Controllers
   single: Controller; String naming format

.. _controller-string-syntax:

Controller Naming Pattern
-------------------------

The ``controller`` value in your routes has a very simple format ``CONTROLLER_CLASS::METHOD``.
If your controller is registered as a service, you can also use just one colon separator
(e.g. ``service_name:index``).

.. tip::

    To refer to an action that is implemented as the ``__invoke()`` method of a controller class,
    you do not have to pass the method name, but can just use the fully qualified class name (e.g.
    ``App\Controller\BlogController``).

.. index::
   single: Routing; Generating URLs

.. _routing-generate:

Generating URLs
---------------

The routing system can also generate URLs. In reality, routing is a bidirectional
system: mapping the URL to a controller and also a route back to a URL.

To generate a URL, you need to specify the name of the route (e.g. ``blog_show``)
and any wildcards (e.g. ``slug = my-blog-post``) used in the path for that
route. With this information, any URL can easily be generated::

    class MainController extends Controller
    {
        public function show($slug)
        {
            // ...

            // /blog/my-blog-post
            $url = $this->generateUrl(
                'blog_show',
                array('slug' => 'my-blog-post')
            );
        }
    }

If you need to generate a URL from a service, type-hint the :class:`Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface`
service::

    // src/Service/SomeService.php

    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

    class SomeService
    {
        private $router;

        public function __construct(UrlGeneratorInterface $router)
        {
            $this->router = $router;
        }

        public function someMethod()
        {
            $url = $this->router->generate(
                'blog_show',
                array('slug' => 'my-blog-post')
            );
            // ...
        }
    }

.. index::
   single: Routing; Generating URLs in a template

Generating URLs with Query Strings
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``generate()`` method takes an array of wildcard values to generate the URI.
But if you pass extra ones, they will be added to the URI as a query string::

    $this->router->generate('blog', array(
        'page' => 2,
        'category' => 'Symfony',
    ));
    // /blog/2?category=Symfony

Generating URLs from a Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To generate URLs inside Twig, see the templating article: :ref:`templating-pages`.
If you also need to generate URLs in JavaScript, see :doc:`/routing/generate_url_javascript`.

.. index::
   single: Routing; Absolute URLs

Generating Absolute URLs
~~~~~~~~~~~~~~~~~~~~~~~~

By default, the router will generate relative URLs (e.g. ``/blog``). From
a controller, pass ``UrlGeneratorInterface::ABSOLUTE_URL`` to the third argument of the ``generateUrl()``
method::

    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

    $this->generateUrl('blog_show', array('slug' => 'my-blog-post'), UrlGeneratorInterface::ABSOLUTE_URL);
    // http://www.example.com/blog/my-blog-post

.. note::

    The host that's used when generating an absolute URL is automatically
    detected using the current ``Request`` object. When generating absolute
    URLs from outside the web context (for instance in a console command) this
    doesn't work. See :doc:`/console/request_context` to learn how to
    solve this problem.

Troubleshooting
---------------

Here are some common errors you might see while working with routing:

    Controller "App\\Controller\\BlogController::show()" requires that you
    provide a value for the "$slug" argument.

This happens when your controller method has an argument (e.g. ``$slug``)::

    public function show($slug)
    {
        // ..
    }

But your route path does *not* have a ``{slug}`` wildcard (e.g. it is ``/blog/show``).
Add a ``{slug}`` to your route path: ``/blog/show/{slug}`` or give the argument
a default value (i.e. ``$slug = null``).

    Some mandatory parameters are missing ("slug") to generate a URL for route
    "blog_show".

This means that you're trying to generate a URL to the ``blog_show`` route but you
are *not* passing a ``slug`` value (which is required, because it has a ``{slug}``)
wildcard in the route path. To fix this, pass a ``slug`` value when generating the
route::

    $this->generateUrl('blog_show', array('slug' => 'slug-value'));

    // or, in Twig
    // {{ path('blog_show', {'slug': 'slug-value'}) }}

Translating Routes
------------------

Symfony doesn't support defining routes with different contents depending on the
user language. In those cases, you can define multiple routes per controller,
one for each supported language; or use any of the bundles created by the
community to implement this feature, such as `JMSI18nRoutingBundle`_ and
`BeSimpleI18nRoutingBundle`_.

Keep Going!
-----------

Routing, check! Now, uncover the power of :doc:`controllers </controller>`.

Learn more about Routing
------------------------

.. toctree::
    :hidden:

    controller

.. toctree::
    :maxdepth: 1
    :glob:

    routing/*

.. _`JMSI18nRoutingBundle`: https://github.com/schmittjoh/JMSI18nRoutingBundle
.. _`BeSimpleI18nRoutingBundle`: https://github.com/BeSimple/BeSimpleI18nRoutingBundle
