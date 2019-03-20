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

A *route* is a map from a URL path to a controller. Suppose you want one route that
matches ``/blog`` exactly and another more dynamic route that can match *any* URL
like ``/blog/my-post`` or ``/blog/all-about-symfony``.

Routes can be configured in YAML, XML and PHP. All formats provide the same
features and performance, so choose the one you prefer. If you choose PHP
annotations, run this command once in your app to add support for them:

.. code-block:: terminal

    $ composer require annotations

Now you can configure the routes:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
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
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog" controller="App\Controller\BlogController::list">
                <!-- settings -->
            </route>

            <route id="blog_show" path="/blog/{slug}" controller="App\Controller\BlogController::show">
                <!-- settings -->
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\BlogController;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog')
                ->controller([BlogController::class, 'list'])
            ;
            $routes->add('blog_show', '/blog/{slug}')
                ->controller([BlogController::class, 'show'])
            ;
        };

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
    configure your routes in YAML, XML or PHP, that's no problem! Create a new
    routing file (e.g. ``routes.xml``) in the ``config/`` directory and Symfony
    will automatically use it.

.. _i18n-routing:

Localized Routing (i18n)
------------------------

Routes can be localized to provide unique paths per :doc:`locale </translation/locale>`.
Symfony provides a handy way to declare localized routes without duplication.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/CompanyController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class CompanyController extends AbstractController
        {
            /**
             * @Route({
             *     "nl": "/over-ons",
             *     "en": "/about-us"
             * }, name="about_us")
             */
            public function about()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        about_us:
            path:
                nl: /over-ons
                en: /about-us
            controller: App\Controller\CompanyController::about

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="about_us" controller="App\Controller\CompanyController::about">
                <path locale="nl">/over-ons</path>
                <path locale="en">/about-us</path>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\CompanyController;

        return function (RoutingConfigurator $routes) {
            $routes->add('about_us', ['nl' => '/over-ons', 'en' => '/about-us'])
                ->controller([CompanyController::class, 'about']);
        };

When a localized route is matched Symfony automatically knows which locale
should be used during the request. Defining routes this way also eliminated the
need for duplicate registration of routes which minimizes the risk for any bugs
caused by definition inconsistency.

.. tip::

    If the application uses full language + territory locales (e.g. ``fr_FR``,
    ``fr_BE``), you can use the language part only in your routes (e.g. ``fr``).
    This prevents having to define multiple paths when you want to use the same
    route path for locales that share the same language.

A common requirement for internationalized applications is to prefix all routes
with a locale. This can be done by defining a different prefix for each locale
(and setting an empty prefix for your default locale if you prefer it):

.. configuration-block::

    .. code-block:: yaml

        # config/routes/annotations.yaml
        controllers:
            resource: '../../src/Controller/'
            type: annotation
            prefix:
                en: '' # don't prefix URLs for English, the default locale
                nl: '/nl'

    .. code-block:: xml

        <!-- config/routes/annotations.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="../src/Controller/" type="annotation">
                <!-- don't prefix URLs for English, the default locale -->
                <prefix locale="en"></prefix>
                <prefix locale="nl">/nl</prefix>
            </import>
        </routes>

    .. code-block:: php

        // config/routes/annotations.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('../src/Controller/', 'annotation')
                ->prefix([
                    // don't prefix URLs for English, the default locale
                    'en' => '',
                    'nl' => '/nl'
                ])
            ;
        };

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

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
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
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog/{page}" controller="App\Controller\BlogController::list">
                <requirement key="page">\d+</requirement>
            </route>

            <!-- ... -->
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\BlogController;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog/{page}')
                ->controller([BlogController::class, 'list'])
                ->requirements(['page' => '\d+'])
            ;
            // ...
        };

The ``\d+`` is a regular expression that matches a *digit* of any length. Now:

========================  =============  ===============================
URL                       Route          Parameters
========================  =============  ===============================
``/blog/2``               ``blog_list``  ``$page`` = ``2``
``/blog/yay-routing``     ``blog_show``  ``$slug`` = ``yay-routing``
========================  =============  ===============================

If you prefer, requirements can be inlined in each placeholder using the syntax
``{placeholder_name<requirements>}``. This feature makes configuration more
concise, but it can decrease route readability when requirements are complex:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            /**
             * @Route("/blog/{page<\d+>}", name="blog_list")
             */
            public function list($page)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path:      /blog/{page<\d+>}
            controller: App\Controller\BlogController::list

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog/{page<\d+>}" controller="App\Controller\BlogController::list"/>

            <!-- ... -->
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\BlogController;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog/{page<\d+>}')
                ->controller([BlogController::class, 'list'])
            ;
            // ...
        };

To learn about other route requirements - like HTTP method, hostname and dynamic
expressions - see :doc:`/routing/requirements`.

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

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
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
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog/{page}" controller="App\Controller\BlogController::list">
                <default key="page">1</default>

                <requirement key="page">\d+</requirement>
            </route>

            <!-- ... -->
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\BlogController;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog/{page}')
                ->controller([BlogController::class, 'list'])
                ->defaults(['page' => 1])
                ->requirements(['page' => '\d+'])
            ;
        };

Now, when the user visits ``/blog``, the ``blog_list`` route will match and
``$page`` will default to a value of ``1``.

If you want to always include some default value in the generated URL (for
example to force the generation of ``/blog/1`` instead of ``/blog`` in the
previous example) add the ``!`` character before the placeholder name: ``/blog/{!page}``

.. versionadded:: 4.3
    The feature to force the inclusion of default values in generated URLs was
    introduced in Symfony 4.3.

As it happens with requirements, default values can also be inlined in each
placeholder using the syntax ``{placeholder_name?default_value}``. This feature
is compatible with inlined requirements, so you can inline both in a single
placeholder:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            /**
             * @Route("/blog/{page<\d+>?1}", name="blog_list")
             */
            public function list($page)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path:      /blog/{page<\d+>?1}
            controller: App\Controller\BlogController::list

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog/{page <\d+>?1}" controller="App\Controller\BlogController::list"/>

            <!-- ... -->
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\BlogController;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog/{page<\d+>?1}')
                ->controller([BlogController::class, 'list'])
            ;
        };

.. tip::

    To give a ``null`` default value to any placeholder, add nothing after the
    ``?`` character (e.g. ``/blog/{page?}``).

Listing all of your Routes
--------------------------

As your app grows, you'll eventually have a *lot* of routes! To see them all, run:

.. code-block:: terminal

    $ php bin/console debug:router

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
        class ArticleController extends AbstractController
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
                https://symfony.com/schema/routing/routing-1.0.xsd">

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
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\ArticleController;

        return function (RoutingConfigurator $routes) {
            $routes->add('article_show', '/articles/{_locale}/{year}/{slug}.{_format}')
                ->controller([ArticleController::class, 'show'])
                ->defaults([
                    '_format' => 'html',
                ])
                ->requirements([
                    '_locale' => 'en|fr',
                    '_format' => 'html|rss',
                    'year'    => '\d+',
                ])
            ;
        };

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

.. _routing-trailing-slash-redirection:

Redirecting URLs with Trailing Slashes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Historically, URLs have followed the UNIX convention of adding trailing slashes
for directories (e.g. ``https://example.com/foo/``) and removing them to refer
to files (``https://example.com/foo``). Although serving different contents for
both URLs is OK, nowadays it's common to treat both URLs as the same URL and
redirect between them.

Symfony follows this logic to redirect between URLs with and without trailing
slashes (but only for ``GET`` and ``HEAD`` requests):

==========  ========================================  ==========================================
Route path  If the requested URL is ``/foo``          If the requested URL is ``/foo/``
----------  ----------------------------------------  ------------------------------------------
``/foo``    It matches (``200`` status response)      It makes a ``301`` redirect to ``/foo``
``/foo/``   It makes a ``301`` redirect to ``/foo/``  It matches (``200`` status response)
==========  ========================================  ==========================================

.. note::

    If your application defines different routes for each path (``/foo`` and
    ``/foo/``) this automatic redirection doesn't take place and the right
    route is always matched.

.. index::
   single: Routing; Controllers
   single: Controller; String naming format

.. _controller-string-syntax:

Controller Naming Pattern
-------------------------

The ``controller`` value in your routes has the format ``CONTROLLER_CLASS::METHOD``.

.. tip::

    To refer to an action that is implemented as the ``__invoke()`` method of a controller class,
    you do not have to pass the method name, you can also use the fully qualified class name (e.g.
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
route. With this information, an URL can be generated in a controller::

    class BlogController extends AbstractController
    {
        public function show($slug)
        {
            // ...

            // /blog/my-blog-post
            $url = $this->generateUrl(
                'blog_show',
                ['slug' => 'my-blog-post']
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
                ['slug' => 'my-blog-post']
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

    $this->router->generate('blog', [
        'page' => 2,
        'category' => 'Symfony',
    ]);
    // /blog/2?category=Symfony

Generating Localized URLs
~~~~~~~~~~~~~~~~~~~~~~~~~

When a route is localized, Symfony uses by default the current request locale to
generate the URL. In order to generate the URL for a different locale you must
pass the ``_locale`` in the parameters array::

    $this->router->generate('about_us', [
        '_locale' => 'nl',
    ]);
    // generates: /over-ons

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

    $this->generateUrl('blog_show', ['slug' => 'my-blog-post'], UrlGeneratorInterface::ABSOLUTE_URL);
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
        // ...
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

    $this->generateUrl('blog_show', ['slug' => 'slug-value']);

    // or, in Twig
    // {{ path('blog_show', {'slug': 'slug-value'}) }}

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
