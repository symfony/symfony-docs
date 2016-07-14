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

If you don't want to use annotations, you can also use YAML, XML or PHP. In these
formats, the ``_controller`` parameter is a special
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

.. figure:: /_images/http/request-flow.png
   :align: center
   :alt: Symfony request flow

   The routing layer is a tool that translates the incoming URL into a specific
   controller to execute.

.. index::
   single: Routing; Creating routes

.. _routing-creating-routes:

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
    See the :doc:`/routing/external_resources` section for more
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

Dynamic Routing with Placeholders
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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
    parameters. Read more about this in ":doc:`/routing/service_container_parameters`".

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

=============  ==================  ==============
Bundle         Controller Class    Method Name
=============  ==================  ==============
``AppBundle``  ``BlogController``  ``showAction``
=============  ==================  ==============

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
   refers to the controller as a service (see :doc:`/controller/service`).

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
see :doc:`/routing/extra_information`.

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
            {'slug': 'my-blog-post'}
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
the ``path()`` function to generate a relative URL:

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

.. tip::

    If you are generating the route inside a ``<script>`` element, it's a good
    practice to escape it for JavaScript:

    .. configuration-block::

        .. code-block:: html+twig

            <script>
            var route = "{{ path('blog_show', {'slug': 'my-blog-post'})|escape('js') }}";
            </script>

        .. code-block:: html+php
        
            <script>
            var route = "<?php echo $view->escape(
                $view['router']->path('blow_show', array(
                    'slug' => 'my-blog-post',
                )),
                'js'
            ) ?>";
            </script>

.. index::
   single: Routing; Absolute URLs

Generating Absolute URLs
~~~~~~~~~~~~~~~~~~~~~~~~

By default, the router will generate relative URLs (e.g. ``/blog``). From
a controller, simply pass ``UrlGeneratorInterface::ABSOLUTE_URL`` to the third argument of the ``generateUrl()``
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

.. note::

    The host that's used when generating an absolute URL is automatically
    detected using the current ``Request`` object. When generating absolute
    URLs from outside the web context (for instance in a console command) this
    doesn't work. See :doc:`/console/request_context` to learn how to
    solve this problem.

Summary
-------

Routing is a system for mapping the URL of incoming requests to the controller
function that should be called to process the request. It both allows you
to specify beautiful URLs and keeps the functionality of your application
decoupled from those URLs. Routing is a bidirectional mechanism, meaning that it
should also be used to generate URLs.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    routing/*

.. _`FOSJsRoutingBundle`: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
