.. index::
   single: Routing

Routing
=======

When your application receives a request, it executes a
:doc:`controller action </controller>` to generate the response. The routing
configuration defines which action to run for each incoming URL. It also
provides other useful features, like generating SEO-friendly URLs (e.g.
``/read/intro-to-symfony`` instead of ``index.php?article_id=57``).

.. _routing-creating-routes:

Creating Routes
---------------

Routes can be configured in YAML, XML, PHP or using annotations. All formats
provide the same features and performance, so choose your favorite.
:ref:`Symfony recommends annotations <best-practice-controller-annotations>`
because it's convenient to put the route and controller in the same place.

Creating Routes as Annotations
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Run this command once in your application to add support for annotations:

.. code-block:: terminal

    $ composer require annotations

In addition to installing the needed dependencies, this command creates the
following configuration file:

.. code-block:: yaml

    # config/routes.yaml
    controllers:
        resource: '../src/Controller/'
        type:     annotation

This configuration tells Symfony to look for routes defined as annotations in
any PHP class stored in the ``src/Controller/`` directory.

Suppose you want to define a route for the ``/blog`` URL in your application. To
do so, create a :doc:`controller class </controller>` like the following::

    // src/Controller/BlogController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;

    class BlogController extends AbstractController
    {
        /**
         * @Route("/blog", name="blog_list")
         */
        public function list()
        {
            // ...
        }
    }

This configuration defines a route called ``blog_list`` that matches when the
user requests the ``/blog`` URL. When the match occurs, the application runs
the ``list()`` method of the ``BlogController`` class.

.. note::

    The query string of a URL is not considered when matching routes. In this
    example, URLs like ``/blog?foo=bar`` and ``/blog?foo=bar&bar=foo`` will
    also match the ``blog_list`` route.

The route name (``blog_list``) is not important for now, but it will be
essential later when :ref:`generating URLs <routing-generating-urls>`. You only
have to keep in mind that each route name must be unique in the application.

Creating Routes in YAML, XML or PHP Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of defining routes in the controller classes, you can define them in a
separate YAML, XML or PHP file. The main advantage is that they don't require
any extra dependency. The main drawback is that you have to work with multiple
files when checking the routing of some controller action.

The following example shows how to define in YAML/XML/PHP a route called
``blog_list`` that associates the ``/blog`` URL with the ``list()`` action of
the ``BlogController``:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path: /blog
            # the controller value has the format 'controller_class::method_name'
            controller: App\Controller\BlogController::list

            # if the action is implemented as the __invoke() method of the
            # controller class, you can skip the '::method_name' part:
            # controller: App\Controller\BlogController

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <!-- the controller value has the format 'controller_class::method_name' -->
            <route id="blog_list" path="/blog"
                   controller="App\Controller\BlogController::list"/>

            <!-- if the action is implemented as the __invoke() method of the
                 controller class, you can skip the '::method_name' part:
                 controller="App\Controller\BlogController"/> -->
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\BlogController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog')
                // the controller value has the format [controller_class, method_name]
                ->controller([BlogController::class, 'list'])

                // if the action is implemented as the __invoke() method of the
                // controller class, you can skip the ', method_name]' part:
                // ->controller([BlogController::class])
            ;
        };

.. _routing-matching-http-methods:

Matching HTTP Methods
~~~~~~~~~~~~~~~~~~~~~

By default, routes match any HTTP verb (``GET``, ``POST``, ``PUT``, etc.)
Use the ``methods`` option to restrict the verbs each route should respond to:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogApiController.php
        namespace App\Controller;

        // ...

        class BlogApiController extends AbstractController
        {
            /**
             * @Route("/api/posts/{id}", methods={"GET","HEAD"})
             */
            public function show(int $id)
            {
                // ... return a JSON response with the post
            }

            /**
             * @Route("/api/posts/{id}", methods={"PUT"})
             */
            public function edit(int $id)
            {
                // ... edit a post
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        api_post_show:
            path:       /api/posts/{id}
            controller: App\Controller\BlogApiController::show
            methods:    GET|HEAD

        api_post_edit:
            path:       /api/posts/{id}
            controller: App\Controller\BlogApiController::edit
            methods:    PUT

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="api_post_show" path="/api/posts/{id}"
                controller="App\Controller\BlogApiController::show"
                methods="GET|HEAD"/>

            <route id="api_post_edit" path="/api/posts/{id}"
                controller="App\Controller\BlogApiController::edit"
                methods="PUT"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\BlogApiController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('api_post_show', '/api/posts/{id}')
                ->controller([BlogApiController::class, 'show'])
                ->methods(['GET', 'HEAD'])
            ;
            $routes->add('api_post_edit', '/api/posts/{id}')
                ->controller([BlogApiController::class, 'edit'])
                ->methods(['PUT'])
            ;
        };

.. tip::

    HTML forms only support ``GET`` and ``POST`` methods. If you're calling a
    route with a different method from an HTML form, add a hidden field called
    ``_method`` with the method to use (e.g. ``<input type="hidden" name="_method" value="PUT"/>``).
    If you create your forms with :doc:`Symfony Forms </forms>` this is done
    automatically for you.

.. _routing-matching-expressions:

Matching Expressions
~~~~~~~~~~~~~~~~~~~~

Use the ``condition`` option if you need some route to match based on some
arbitrary matching logic:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/DefaultController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class DefaultController extends AbstractController
        {
            /**
             * @Route(
             *     "/contact",
             *     name="contact",
             *     condition="context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'"
             * )
             *
             * expressions can also include config parameters:
             * condition: "request.headers.get('User-Agent') matches '%app.allowed_browsers%'"
             */
            public function contact()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        contact:
            path:       /contact
            controller: 'App\Controller\DefaultController::contact'
            condition:  "context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'"
            # expressions can also include config parameters:
            # condition: "request.headers.get('User-Agent') matches '%app.allowed_browsers%'"

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/contact" controller="App\Controller\DefaultController::contact">
                <condition>context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'</condition>
                <!-- expressions can also include config parameters: -->
                <!-- <condition>request.headers.get('User-Agent') matches '%app.allowed_browsers%'</condition> -->
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\DefaultController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('contact', '')
                ->controller([DefaultController::class, 'contact'])
                ->condition('context.getMethod() in ["GET", "HEAD"] and request.headers.get("User-Agent") matches "/firefox/i"')
                // expressions can also include config parameters:
                // 'request.headers.get("User-Agent") matches "%app.allowed_browsers%"'
            ;
        };

The value of the ``condition`` option is any valid
:doc:`ExpressionLanguage expression </components/expression_language/syntax>`
and can use any of these variables created by Symfony:

``context``
    An instance of :class:`Symfony\\Component\\Routing\\RequestContext`,
    which holds the most fundamental information about the route being matched.

``request``
    The :ref:`Symfony Request <component-http-foundation-request>` object that
    represents the current request.

Behind the scenes, expressions are compiled down to raw PHP. Because of this,
using the ``condition`` key causes no extra overhead beyond the time it takes
for the underlying PHP to execute.

.. caution::

    Conditions are *not* taken into account when generating URLs (which is
    explained later in this article).

Debugging Routes
~~~~~~~~~~~~~~~~

As your application grows, you'll eventually have a *lot* of routes. Symfony
includes some commands to help you debug routing issues. First, the ``debug:router``
command lists all your application routes in the same order in which Symfony
evaluates them:

.. code-block:: terminal

    $ php bin/console debug:router

    ----------------  -------  -------  -----  --------------------------------------------
    Name              Method   Scheme   Host   Path
    ----------------  -------  -------  -----  --------------------------------------------
    homepage          ANY      ANY      ANY    /
    contact           GET      ANY      ANY    /contact
    contact_process   POST     ANY      ANY    /contact
    article_show      ANY      ANY      ANY    /articles/{_locale}/{year}/{title}.{_format}
    blog              ANY      ANY      ANY    /blog/{page}
    blog_show         ANY      ANY      ANY    /blog/{slug}
    ----------------  -------  -------  -----  --------------------------------------------

You can sort the routes by name or path using the ``--sort`` option

.. code-block:: terminal

    $ php bin/console debug:router --sort=name

    ----------------  -------  -------  -----  --------------------------------------------
    Name              Method   Scheme   Host   Path
    ----------------  -------  -------  -----  --------------------------------------------
    article_show      ANY      ANY      ANY    /articles/{_locale}/{year}/{title}.{_format}
    blog              ANY      ANY      ANY    /blog/{page}
    blog_show         ANY      ANY      ANY    /blog/{slug}
    contact           GET      ANY      ANY    /contact
    contact_process   POST     ANY      ANY    /contact
    homepage          ANY      ANY      ANY    /
    ----------------  -------  -------  -----  --------------------------------------------

Pass the name (or part of the name) of some route to this argument to print the
route details:

.. code-block:: terminal

    $ php bin/console debug:router app_lucky_number

    +-------------+---------------------------------------------------------+
    | Property    | Value                                                   |
    +-------------+---------------------------------------------------------+
    | Route Name  | app_lucky_number                                        |
    | Path        | /lucky/number/{max}                                     |
    | ...         | ...                                                     |
    | Options     | compiler_class: Symfony\Component\Routing\RouteCompiler |
    |             | utf8: true                                              |
    +-------------+---------------------------------------------------------+

The other command is called ``router:match`` and it shows which route will match
the given URL. It's useful to find out why some URL is not executing the
controller action that you expect:

.. code-block:: terminal

    $ php bin/console router:match /lucky/number/8

      [OK] Route "app_lucky_number" matches

Route Parameters
----------------

The previous examples defined routes where the URL never changes (e.g. ``/blog``).
However, it's common to define routes where some parts are variable. For example,
the URL to display some blog post will probably include the title or slug
(e.g. ``/blog/my-first-post`` or ``/blog/all-about-symfony``).

In Symfony routes, variable parts are wrapped in ``{ ... }`` and they must have
a unique name. For example, the route to display the blog post contents is
defined as ``/blog/{slug}``:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            // ...

            /**
             * @Route("/blog/{slug}", name="blog_show")
             */
            public function show(string $slug)
            {
                // $slug will equal the dynamic part of the URL
                // e.g. at /blog/yay-routing, then $slug='yay-routing'

                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_show:
            path:       /blog/{slug}
            controller: App\Controller\BlogController::show

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_show" path="/blog/{slug}"
                   controller="App\Controller\BlogController::show"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\BlogController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_show', '/blog/{slug}')
                ->controller([BlogController::class, 'show'])
            ;
        };

The name of the variable part (``{slug}`` in this example) is used to create a
PHP variable where that route content is stored and passed to the controller.
If a user visits the ``/blog/my-first-post`` URL, Symfony executes the ``show()``
method in the ``BlogController`` class and passes a ``$slug = 'my-first-post'``
argument to the ``show()`` method.

Routes can define any number of parameters, but each of them can only be used
once on each route (e.g. ``/blog/posts-about-{category}/page/{pageNumber}``).

.. _routing-requirements:

Parameters Validation
~~~~~~~~~~~~~~~~~~~~~

Imagine that your application has a ``blog_show`` route (URL: ``/blog/{slug}``)
and a ``blog_list`` route (URL: ``/blog/{page}``). Given that route parameters
accept any value, there's no way to differentiate both routes.

If the user requests ``/blog/my-first-post``, both routes will match and Symfony
will use the route which was defined first. To fix this, add some validation to
the ``{page}`` parameter using the ``requirements`` option:

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
            public function list(int $page)
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
            path:       /blog/{page}
            controller: App\Controller\BlogController::list
            requirements:
                page: '\d+'

        blog_show:
            path:       /blog/{slug}
            controller: App\Controller\BlogController::show

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

            <route id="blog_show" path="/blog/{slug}"
                   controller="App\Controller\BlogController::show"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\BlogController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog/{page}')
                ->controller([BlogController::class, 'list'])
                ->requirements(['page' => '\d+'])
            ;

            $routes->add('blog_show', '/blog/{slug}')
                ->controller([BlogController::class, 'show'])
            ;
            // ...
        };

The ``requirements`` option defines the `PHP regular expressions`_ that route
parameters must match for the entire route to match. In this example, ``\d+`` is
a regular expression that matches a *digit* of any length. Now:

========================  =============  ===============================
URL                       Route          Parameters
========================  =============  ===============================
``/blog/2``               ``blog_list``  ``$page`` = ``2``
``/blog/my-first-post``   ``blog_show``  ``$slug`` = ``my-first-post``
========================  =============  ===============================

.. tip::

    Route requirements (and route paths too) can include
    :ref:`container parameters <configuration-parameters>`, which is useful to
    define complex regular expressions once and reuse them in multiple routes.

.. tip::

    Parameters also support `PCRE Unicode properties`_, which are escape
    sequences that match generic character types. For example, ``\p{Lu}``
    matches any uppercase character in any language, ``\p{Greek}`` matches any
    Greek character, etc.

.. note::

    When using regular expressions in route parameters, you can set the ``utf8``
    route option to ``true`` to make any ``.`` character match any UTF-8
    characters instead of just a single byte.

If you prefer, requirements can be inlined in each parameter using the syntax
``{parameter_name<requirements>}``. This feature makes configuration more
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
            public function list(int $page)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path:       /blog/{page<\d+>}
            controller: App\Controller\BlogController::list

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog/{page<\d+>}"
                   controller="App\Controller\BlogController::list"/>

            <!-- ... -->
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\BlogController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog/{page<\d+>}')
                ->controller([BlogController::class, 'list'])
            ;
            // ...
        };

Optional Parameters
~~~~~~~~~~~~~~~~~~~

In the previous example, the URL of ``blog_list`` is ``/blog/{page}``. If users
visit ``/blog/1``, it will match. But if they visit ``/blog``, it will **not**
match. As soon as you add a parameter to a route, it must have a value.

You can make ``blog_list`` once again match when the user visits ``/blog`` by
adding a default value for the ``{page}`` parameter. When using annotations,
default values are defined in the arguments of the controller action. In the
other configuration formats they are defined with the ``defaults`` option:

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
            public function list(int $page = 1)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path:       /blog/{page}
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
        use App\Controller\BlogController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog/{page}')
                ->controller([BlogController::class, 'list'])
                ->defaults(['page' => 1])
                ->requirements(['page' => '\d+'])
            ;
        };

Now, when the user visits ``/blog``, the ``blog_list`` route will match and
``$page`` will default to a value of ``1``.

.. caution::

    You can have more than one optional parameter (e.g. ``/blog/{slug}/{page}``),
    but everything after an optional parameter must be optional. For example,
    ``/{page}/blog`` is a valid path, but ``page`` will always be required
    (i.e. ``/blog`` will not match this route).

If you want to always include some default value in the generated URL (for
example to force the generation of ``/blog/1`` instead of ``/blog`` in the
previous example) add the ``!`` character before the parameter name: ``/blog/{!page}``

As it happens with requirements, default values can also be inlined in each
parameter using the syntax ``{parameter_name?default_value}``. This feature
is compatible with inlined requirements, so you can inline both in a single
parameter:

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
            public function list(int $page)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path:       /blog/{page<\d+>?1}
            controller: App\Controller\BlogController::list

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog/{page <\d+>?1}"
                   controller="App\Controller\BlogController::list"/>

            <!-- ... -->
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\BlogController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog/{page<\d+>?1}')
                ->controller([BlogController::class, 'list'])
            ;
        };

.. tip::

    To give a ``null`` default value to any parameter, add nothing after the
    ``?`` character (e.g. ``/blog/{page?}``).

Parameter Conversion
~~~~~~~~~~~~~~~~~~~~

A common routing need is to convert the value stored in some parameter (e.g. an
integer acting as the user ID) into another value (e.g. the object that
represents the user). This feature is called "param converter" and is only
available when using annotations to define routes.

In case you didn't run this command before, run it now to add support for
annotations and "param converters":

.. code-block:: terminal

    $ composer require annotations

Now, keep the previous route configuration, but change the arguments of the
controller action. Instead of ``string $slug``, add ``BlogPost $post``::

    // src/Controller/BlogController.php
    namespace App\Controller;

    use App\Entity\BlogPost;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;

    class BlogController extends AbstractController
    {
        // ...

        /**
         * @Route("/blog/{slug}", name="blog_show")
         */
        public function show(BlogPost $post)
        {
            // $post is the object whose slug matches the routing parameter

            // ...
        }
    }

If your controller arguments include type-hints for objects (``BlogPost`` in
this case), the "param converter" makes a database request to find the object
using the request parameters (``slug`` in this case). If no object is found,
Symfony generates a 404 response automatically.

Read the `full param converter documentation`_ to learn about the converters
provided by Symfony and how to configure them.

Special Parameters
~~~~~~~~~~~~~~~~~~

In addition to your own parameters, routes can include any of the following
special parameters created by Symfony:

``_controller``
    This parameter is used to determine which controller and action is executed
    when the route is matched.

.. _routing-format-parameter:

``_format``
    The matched value is used to set the "request format" of the ``Request`` object.
    This is used for such things as setting the ``Content-Type`` of the response
    (e.g. a ``json`` format translates into a ``Content-Type`` of ``application/json``).

``_fragment``
    Used to set the fragment identifier, which is the optional last part of a URL that
    starts with a ``#`` character and is used to identify a portion of a document.

.. _routing-locale-parameter:

``_locale``
    Used to set the :ref:`locale <translation-locale-url>` on the request.

You can include these attributes (except ``_fragment``) both in individual routes
and in route imports. Symfony defines some special attributes with the same name
(except for the leading underscore) so you can define them easier:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/ArticleController.php
        namespace App\Controller;

        // ...
        class ArticleController extends AbstractController
        {
            /**
             * @Route(
             *     "/articles/{_locale}/search.{_format}",
             *     locale="en",
             *     format="html",
             *     requirements={
             *         "_locale": "en|fr",
             *         "_format": "html|xml",
             *     }
             * )
             */
            public function search()
            {
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        article_search:
          path:        /articles/{_locale}/search.{_format}
          controller:  App\Controller\ArticleController::search
          locale:      en
          format:      html
          requirements:
              _locale: en|fr
              _format: html|xml

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="article_search"
                path="/articles/{_locale}/search.{_format}"
                controller="App\Controller\ArticleController::search"
                locale="en"
                format="html">

                <requirement key="_locale">en|fr</requirement>
                <requirement key="_format">html|rss</requirement>

            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\ArticleController;

        return function (RoutingConfigurator $routes) {
            $routes->add('article_show', '/articles/{_locale}/search.{_format}')
                ->controller([ArticleController::class, 'search'])
                ->locale('en')
                ->format('html')
                ->requirements([
                    '_locale' => 'en|fr',
                    '_format' => 'html|rss',
                ])
            ;
        };

Extra Parameters
~~~~~~~~~~~~~~~~

In the ``defaults`` option of a route you can optionally define parameters not
included in the route configuration. This is useful to pass extra arguments to
the controllers of the routes:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Component\Routing\Annotation\Route;

        class BlogController
        {
            /**
             * @Route("/blog/{page}", name="blog_index", defaults={"page": 1, "title": "Hello world!"})
             */
            public function index(int $page, string $title)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_index:
            path:       /blog/{page}
            controller: App\Controller\BlogController::index
            defaults:
                page: 1
                title: "Hello world!"

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_index" path="/blog/{page}" controller="App\Controller\BlogController::index">
                <default key="page">1</default>
                <default key="title">Hello world!</default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\BlogController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_index', '/blog/{page}')
                ->controller([BlogController::class, 'index'])
                ->defaults([
                    'page'  => 1,
                    'title' => 'Hello world!',
                ])
            ;
        };

.. _routing-slash-in-parameters:

Slash Characters in Route Parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Route parameters can contain any values except the ``/`` slash character,
because that's the character used to separate the different parts of the URLs.
For example, if the ``token`` value in the ``/share/{token}`` route contains a
``/`` character, this route won't match.

A possible solution is to change the parameter requirements to be more permissive:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/DefaultController.php
        namespace App\Controller;

        use Symfony\Component\Routing\Annotation\Route;

        class DefaultController
        {
            /**
             * @Route("/share/{token}", name="share", requirements={"token"=".+"})
             */
            public function share($token)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        share:
            path:       /share/{token}
            controller: App\Controller\DefaultController::share
            requirements:
                token: .+

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="share" path="/share/{token}" controller="App\Controller\DefaultController::share">
                <requirement key="token">.+</requirement>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\DefaultController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('share', '/share/{token}')
                ->controller([DefaultController::class, 'share'])
                ->requirements([
                    'token' => '.+',
                ])
            ;
        };

.. note::

    If the route defines several parameter and you apply this permissive
    regular expression to all of them, you might get unexpected results. For
    example, if the route definition is ``/share/{path}/{token}`` and both
    ``path`` and ``token`` accept ``/``. The ``token`` only get the last path
    and the rest of the match is matched by the first argument (``path``).

.. note::

    If the route includes the special ``{_format}`` parameter, you shouldn't
    use the ``.+`` requirement for the parameters that allow slashes. For example,
    if the pattern is ``/share/{token}.{_format}`` and ``{token}`` allows any
    character, the ``/share/foo/bar.json`` URL will consider ``foo/bar.json``
    as the token and the format will be empty. This can be solved by replacing
    the ``.+`` requirement by ``[^.]+`` to allow any character except dots.

.. _routing-route-groups:

Route Groups and Prefixes
-------------------------

It's common for a group of routes to share some options (e.g. all routes related
to the blog start with ``/blog``) That's why Symfony includes a feature to share
route configuration.

When defining routes as annotations, put the common configuration in the
``@Route`` annotation of the controller class. In other routing formats, define
the common configuration using options when importing the routes.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Component\Routing\Annotation\Route;

        /**
         * @Route("/blog", requirements={"_locale": "en|es|fr"}, name="blog_")
         */
        class BlogController
        {
            /**
             * @Route("/{_locale}", name="index")
             */
            public function index()
            {
                // ...
            }

            /**
             * @Route("/{_locale}/posts/{slug}", name="show")
             */
            public function show(Post $post)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes/annotations.yaml
        controllers:
            resource: '../src/Controller/'
            type: annotation
            # this is added to the beginning of all imported route URLs
            prefix: '/blog'
            # this is added to the beginning of all imported route names
            name_prefix: 'blog_'
            # these requirements are added to all imported routes
            requirements:
                _locale: 'en|es|fr'
            # An imported route with an empty URL will become "/blog/"
            # Uncomment this option to make that URL "/blog" instead
            # trailing_slash_on_root: false

    .. code-block:: xml

        <!-- config/routes/annotations.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <!--
                the 'prefix' value is added to the beginning of all imported route URLs
                the 'name-prefix' value is added to the beginning of all imported route names
            -->
            <import resource="../src/Controller/"
                type="annotation"
                prefix="/blog"
                name-prefix="blog_">
                <!-- these requirements are added to all imported routes -->
                <requirement key="_locale">en|es|fr</requirement>
            </import>

            <!-- An imported route with an empty URL will become "/blog/"
                 Uncomment this option to make that URL "/blog" instead -->
            <import resource="../src/Controller/" type="annotation"
                    prefix="/blog"
                    trailing-slash-on-root="false">
                    <!-- ... -->
            </import>
        </routes>

    .. code-block:: php

        // config/routes/annotations.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('../src/Controller/', 'annotation')
                // this is added to the beginning of all imported route URLs
                ->prefix('/blog')
                // An imported route with an empty URL will become "/blog/"
                // Pass FALSE as the second argument to make that URL "/blog" instead
                // ->prefix('/blog', false)
                // this is added to the beginning of all imported route names
                ->namePrefix('blog_')
                // these requirements are added to all imported routes
                ->requirements(['_locale' => 'en|es|fr'])
            ;
        };

In this example, the route of the ``index()`` action will be called ``blog_index``
and its URL will be ``/blog/``. The route of the ``show()`` action will be called
``blog_show`` and its URL will be ``/blog/{_locale}/posts/{slug}``. Both routes
will also validate that the ``_locale`` parameter matches the regular expression
defined in the class annotation.

.. seealso::

    Symfony can :doc:`import routes from different sources </routing/custom_route_loader>`
    and you can even create your own route loader.

Getting the Route Name and Parameters
-------------------------------------

The ``Request`` object created by Symfony stores all the route configuration
(such as the name and parameters) in the "request attributes". You can get this
information in a controller via the ``Request`` object::

    // src/Controller/BlogController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Routing\Annotation\Route;

    class BlogController extends AbstractController
    {
        /**
         * @Route("/blog", name="blog_list")
         */
        public function list(Request $request)
        {
            // ...

            $routeName = $request->attributes->get('_route');
            $routeParameters = $request->attributes->get('_route_params');

            // use this to get all the available attributes (not only routing ones):
            $allAttributes = $request->attributes->all();
        }
    }

You can get this information in services too injecting the ``request_stack``
service to :doc:`get the Request object in a service </service_container/request>`.
In templates, use the :ref:`Twig global app variable <twig-app-variable>` to get
the request and its attributes:

.. code-block:: twig

    {% set route_name = app.request.attributes.get('_route') %}
    {% set route_parameters = app.request.attributes.get('_route_params') %}

    {# use this to get all the available attributes (not only routing ones) #}
    {% set all_attributes = app.request.attributes.all %}

Special Routes
--------------

Symfony defines some special controllers to render templates and redirect to
other routes from the route configuration so you don't have to create a
controller action.

Rendering a Template Directly from a Route
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Read the section about :ref:`rendering a template from a route <templates-render-from-route>`
in the main article about Symfony templates.

Redirecting to URLs and Routes Directly from a Route
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use the ``RedirectController`` to redirect to other routes and URLs:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        doc_shortcut:
            path: /doc
            controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController
            defaults:
                route: 'doc_page'
                # optionally you can define some arguments passed to the route
                page: 'index'
                version: 'current'
                # redirections are temporary by default (code 302) but you can make them permanent (code 301)
                permanent: true
                # add this to keep the original query string parameters when redirecting
                keepQueryParams: true
                # add this to keep the HTTP method when redirecting. The redirect status changes
                # * for temporary redirects, it uses the 307 status code instead of 302
                # * for permanent redirects, it uses the 308 status code instead of 301
                keepRequestMethod: true

        legacy_doc:
            path: /legacy/doc
            controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController
            defaults:
                # this value can be an absolute path or an absolute URL
                path: 'https://legacy.example.com/doc'
                permanent: true

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="doc_shortcut" path="/doc"
                   controller="Symfony\Bundle\FrameworkBundle\Controller\RedirectController">
                <default key="route">doc_page</default>
                <!-- optionally you can define some arguments passed to the route -->
                <default key="page">index</default>
                <default key="version">current</default>
                <!-- redirections are temporary by default (code 302) but you can make them permanent (code 301)-->
                <default key="permanent">true</default>
                <!-- add this to keep the original query string parameters when redirecting -->
                <default key="keepQueryParams">true</default>
                <!-- add this to keep the HTTP method when redirecting. The redirect status changes:
                     * for temporary redirects, it uses the 307 status code instead of 302
                     * for permanent redirects, it uses the 308 status code instead of 301 -->
                <default key="keepRequestMethod">true</default>
            </route>

            <route id="legacy_doc" path="/legacy/doc"
                   controller="Symfony\Bundle\FrameworkBundle\Controller\RedirectController">
                <!-- this value can be an absolute path or an absolute URL -->
                <default key="path">https://legacy.example.com/doc</default>
                <!-- redirections are temporary by default (code 302) but you can make them permanent (code 301)-->
                <default key="permanent">true</default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\DefaultController;
        use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('doc_shortcut', '/doc')
                ->controller(RedirectController::class)
                 ->defaults([
                    'route' => 'doc_page',
                    // optionally you can define some arguments passed to the template
                    'page' => 'index',
                    'version' => 'current',
                    // redirections are temporary by default (code 302) but you can make them permanent (code 301)
                    'permanent' => true,
                    // add this to keep the original query string parameters when redirecting
                    'keepQueryParams' => true,
                    // add this to keep the HTTP method when redirecting. The redirect status changes:
                    // * for temporary redirects, it uses the 307 status code instead of 302
                    // * for permanent redirects, it uses the 308 status code instead of 301
                    'keepRequestMethod' => true,
                ])
            ;

            $routes->add('legacy_doc', '/legacy/doc')
                ->controller(RedirectController::class)
                 ->defaults([
                    // this value can be an absolute path or an absolute URL
                    'path' => 'https://legacy.example.com/doc',
                    // redirections are temporary by default (code 302) but you can make them permanent (code 301)
                    'permanent' => true,
                ])
            ;
        };

.. tip::

    Symfony also provides some utilities to
    :ref:`redirect inside controllers <controller-redirect>`

.. _routing-trailing-slash-redirection:

Redirecting URLs with Trailing Slashes
......................................

Historically, URLs have followed the UNIX convention of adding trailing slashes
for directories (e.g. ``https://example.com/foo/``) and removing them to refer
to files (``https://example.com/foo``). Although serving different contents for
both URLs is OK, nowadays it's common to treat both URLs as the same URL and
redirect between them.

Symfony follows this logic to redirect between URLs with and without trailing
slashes (but only for ``GET`` and ``HEAD`` requests):

==========  ========================================  ==========================================
Route URL   If the requested URL is ``/foo``          If the requested URL is ``/foo/``
==========  ========================================  ==========================================
``/foo``    It matches (``200`` status response)      It makes a ``301`` redirect to ``/foo``
``/foo/``   It makes a ``301`` redirect to ``/foo/``  It matches (``200`` status response)
==========  ========================================  ==========================================

Sub-Domain Routing
------------------

Routes can configure a ``host`` option to require that the HTTP host of the
incoming requests matches some specific value. In the following example, both
routes match the same path (``/``) but one of them only responds to a specific
host name:

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
        use App\Controller\MainController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

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

The value of the ``host`` option can include parameters (which is useful in
multi-tenant applications) and these parameters can be validated too with
``requirements``:

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
        use App\Controller\MainController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

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

In the above example, the ``subdomain`` parameter defines a default value because
otherwise you need to include a domain value each time you generate a URL using
these routes.

.. tip::

    You can also set the ``host`` option when :ref:`importing routes <routing-route-groups>`
    to make all of them require that host name.

.. note::

    When using sub-domain routing, you must set the ``Host`` HTTP headers in
    :doc:`functional tests </testing>` or routes won't match::

        $crawler = $client->request(
            'GET',
            '/',
            [],
            [],
            ['HTTP_HOST' => 'm.example.com']
            // or get the value from some container parameter:
            // ['HTTP_HOST' => 'm.' . $client->getContainer()->getParameter('domain')]
        );

.. _i18n-routing:

Localized Routes (i18n)
-----------------------

If your application is translated into multiple languages, each route can define
a different URL per each :doc:`translation locale </translation/locale>`. This
avoids the need for duplicating routes, which also reduces the potential bugs:

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
             *     "en": "/about-us",
             *     "nl": "/over-ons"
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
                en: /about-us
                nl: /over-ons
            controller: App\Controller\CompanyController::about

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="about_us" controller="App\Controller\CompanyController::about">
                <path locale="en">/about-us</path>
                <path locale="nl">/over-ons</path>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\CompanyController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('about_us', [
                'en' => '/about-us',
                'nl' => '/over-ons',
            ])
                ->controller([CompanyController::class, 'about'])
            ;
        };

When a localized route is matched, Symfony uses the same locale automatically
during the entire request.

.. tip::

    When the application uses full "language + territory" locales (e.g. ``fr_FR``,
    ``fr_BE``), if the URLs are the same in all related locales, routes can use
    only the language part (e.g. ``fr``) to avoid repeating the same URLs.

A common requirement for internationalized applications is to prefix all routes
with a locale. This can be done by defining a different prefix for each locale
(and setting an empty prefix for your default locale if you prefer it):

.. configuration-block::

    .. code-block:: yaml

        # config/routes/annotations.yaml
        controllers:
            resource: '../src/Controller/'
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
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('../src/Controller/', 'annotation')
                ->prefix([
                    // don't prefix URLs for English, the default locale
                    'en' => '',
                    'nl' => '/nl'
                ])
            ;
        };

.. _routing-generating-urls:

Generating URLs
---------------

Routing systems are bidirectional: 1) they associate URLs with controllers (as
explained in the previous sections); 2) they generate URLs for a given route.
Generating URLs from routes allows you to not write the ``<a href="...">``
values manually in your HTML templates. Also, if the URL of some route changes,
you only have to update the route configuration and all links will be updated.

To generate a URL, you need to specify the name of the route (e.g.
``blog_show``) and the values of the parameters defined by the route (e.g.
``slug = my-blog-post``).

For that reason each route has an internal name that must be unique in the
application. If you don't set the route name explicitly with the ``name``
option, Symfony generates an automatic name based on the controller and action.

Generating URLs in Controllers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your controller extends from the :ref:`AbstractController <the-base-controller-class-services>`,
use the ``generateUrl()`` helper::

    // src/Controller/BlogController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

    class BlogController extends AbstractController
    {
        /**
         * @Route("/blog", name="blog_list")
         */
        public function list()
        {
            // ...

            // generate a URL with no route arguments
            $signUpPage = $this->generateUrl('sign_up');

            // generate a URL with route arguments
            $userProfilePage = $this->generateUrl('user_profile', [
                'username' => $user->getUsername(),
            ]);

            // generated URLs are "absolute paths" by default. Pass a third optional
            // argument to generate different URLs (e.g. an "absolute URL")
            $signUpPage = $this->generateUrl('sign_up', [], UrlGeneratorInterface::ABSOLUTE_URL);

            // when a route is localized, Symfony uses by default the current request locale
            // pass a different '_locale' value if you want to set the locale explicitly
            $signUpPageInDutch = $this->generateUrl('sign_up', ['_locale' => 'nl']);
        }
    }

.. note::

    If you pass to the ``generateUrl()`` method some parameters that are not
    part of the route definition, they are included in the generated URL as a
    query string:::

        $this->generateUrl('blog', ['page' => 2, 'category' => 'Symfony']);
        // the 'blog' route only defines the 'page' parameter; the generated URL is:
        // /blog/2?category=Symfony

If your controller does not extend from ``AbstractController``, you'll need to
:ref:`fetch services in your controller <controller-accessing-services>` and
follow the instructions of the next section.

.. _routing-generating-urls-in-services:

Generating URLs in Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Inject the ``router`` Symfony service into your own services and use its
``generate()`` method. When using :doc:`service autowiring </service_container/autowiring>`
you only need to add an argument in the service constructor and type-hint it with
the :class:`Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface` class::

    // src/Service/SomeService.php
    namespace App\Service;

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
            // ...

            // generate a URL with no route arguments
            $signUpPage = $this->router->generate('sign_up');

            // generate a URL with route arguments
            $userProfilePage = $this->router->generate('user_profile', [
                'username' => $user->getUsername(),
            ]);

            // generated URLs are "absolute paths" by default. Pass a third optional
            // argument to generate different URLs (e.g. an "absolute URL")
            $signUpPage = $this->router->generate('sign_up', [], UrlGeneratorInterface::ABSOLUTE_URL);

            // when a route is localized, Symfony uses by default the current request locale
            // pass a different '_locale' value if you want to set the locale explicitly
            $signUpPageInDutch = $this->router->generate('sign_up', ['_locale' => 'nl']);
        }
    }

Generating URLs in Templates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Read the section about :ref:`creating links between pages <templates-link-to-pages>`
in the main article about Symfony templates.

Generating URLs in JavaScript
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your JavaScript code is included in a Twig template, you can use the
``path()`` and ``url()`` Twig functions to generate the URLs and store them in
JavaScript variables. The ``escape()`` function is needed to escape any
non-JavaScript-safe values:

.. code-block:: html+twig

    <script>
        const route = "{{ path('blog_show', {slug: 'my-blog-post'})|escape('js') }}";
    </script>

If you need to generate URLs dynamically or if you are using pure JavaScript
code, this solution doesn't work. In those cases, consider using the
`FOSJsRoutingBundle`_.

Generating URLs in Commands
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Generating URLs in commands works the same as
:ref:`generating URLs in services <routing-generating-urls-in-services>`. The
only difference is that commands are not executed in the HTTP context, so they
don't have access to HTTP requests. In practice, this means that if you generate
absolute URLs, you'll get ``http://localhost/`` as the host name instead of your
real host name.

The solution is to configure the "request context" used by commands when they
generate URLs. This context can be configured globally for all commands:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            router.request_context.host: 'example.org'
            router.request_context.base_url: 'my/path'
            asset.request_context.base_path: '%router.request_context.base_url%'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

            <parameters>
                <parameter key="router.request_context.host">example.org</parameter>
                <parameter key="router.request_context.base_url">my/path</parameter>
                <parameter key="asset.request_context.base_path">%router.request_context.base_url%</parameter>
            </parameters>

        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('router.request_context.host', 'example.org');
        $container->setParameter('router.request_context.base_url', 'my/path');
        $container->setParameter('asset.request_context.base_path', $container->getParameter('router.request_context.base_url'));

This information can be configured per command too::

    // src/Command/SomeCommand.php
    namespace App\Command;

    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Routing\RouterInterface;
    // ...

    class SomeCommand extends Command
    {
        private $router;

        public function __construct(RouterInterface $router)
        {
            parent::__construct();

            $this->router = $router;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            // these values override any global configuration
            $context = $this->router->getContext();
            $context->setHost('example.com');
            $context->setBaseUrl('my/path');

            // generate a URL with no route arguments
            $signUpPage = $this->router->generate('sign_up');

            // generate a URL with route arguments
            $userProfilePage = $this->router->generate('user_profile', [
                'username' => $user->getUsername(),
            ]);

            // generated URLs are "absolute paths" by default. Pass a third optional
            // argument to generate different URLs (e.g. an "absolute URL")
            $signUpPage = $this->router->generate('sign_up', [], UrlGeneratorInterface::ABSOLUTE_URL);

            // when a route is localized, Symfony uses by default the current request locale
            // pass a different '_locale' value if you want to set the locale explicitly
            $signUpPageInDutch = $this->router->generate('sign_up', ['_locale' => 'nl']);

            // ...
        }
    }

Checking if a Route Exists
~~~~~~~~~~~~~~~~~~~~~~~~~~

In highly dynamic applications, it may be necessary to check whether a route
exists before using it to generate a URL. In those cases, don't use the
:method:`Symfony\\Component\\Routing\\Router::getRouteCollection` method because
that regenerates the routing cache and slows down the application.

Instead, try to generate the URL and catch the
:class:`Symfony\\Component\\Routing\\Exception\\RouteNotFoundException` thrown
when the route doesn't exist::

    use Symfony\Component\Routing\Exception\RouteNotFoundException;

    // ...

    try {
        $url = $this->router->generate($routeName, $routeParameters);
    } catch (RouteNotFoundException $e) {
        // the route is not defined...
    }

.. _routing-force-https:

Forcing HTTPS on Generated URLs
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, generated URLs use the same HTTP scheme as the current request.
In console commands, where there is no HTTP request, URLs use ``http`` by
default. You can change this per command (via the router's ``getContext()``
method) or globally with these configuration parameters:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            router.request_context.scheme: 'https'
            asset.request_context.secure: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

            <parameters>
                <parameter key="router.request_context.scheme">https</parameter>
                <parameter key="asset.request_context.secure">true</parameter>
            </parameters>

        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('router.request_context.scheme', 'https');
        $container->setParameter('asset.request_context.secure', true);

Outside of console commands, use the ``schemes`` option to define the scheme of
each route explicitly:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/MainController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class SecurityController extends AbstractController
        {
            /**
             * @Route("/login", name="login", schemes={"https"})
             */
            public function login()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        login:
            path:       /login
            controller: App\Controller\SecurityController::login
            schemes:    [https]

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="login" path="/login" schemes="https"
                   controller="App\Controller\SecurityController::login"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\SecurityController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('login', '/login')
                ->controller([SecurityController::class, 'login'])
                ->schemes(['https'])
            ;
        };

The URL generated for the ``login`` route will always use HTTPS. This means that
when using the ``path()`` Twig function to generate URLs, you may get an
absolute URL instead of a relative URL if the HTTP scheme of the original
request is different from the scheme used by the route:

.. code-block:: twig

    {# if the current scheme is HTTPS, generates a relative URL: /login #}
    {{ path('login') }}

    {# if the current scheme is HTTP, generates an absolute URL to change
       the scheme: https://example.com/login #}
    {{ path('login') }}

The scheme requirement is also enforced for incoming requests. If you try to
access the ``/login`` URL with HTTP, you will automatically be redirected to the
same URL, but with the HTTPS scheme.

If you want to force a group of routes to use HTTPS, you can define the default
scheme when importing them. The following example forces HTTPS on all routes
defined as annotations:

.. configuration-block::

    .. code-block:: yaml

        # config/routes/annotations.yaml
        controllers:
            resource: '../src/Controller/'
            type: annotation
            defaults:
                schemes: [https]

    .. code-block:: xml

        <!-- config/routes/annotations.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="../src/Controller/" type="annotation">
                <default locale="schemes">HTTPS</prefix>
            </import>
        </routes>

    .. code-block:: php

        // config/routes/annotations.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('../src/Controller/', 'annotation')
                ->schemes(['https'])
            ;
        };

.. note::

    The Security component provides
    :doc:`another way to enforce HTTP or HTTPS </security/force_https>`
    via the ``requires_channel`` setting.

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

But your route path does *not* have a ``{slug}`` parameter (e.g. it is
``/blog/show``). Add a ``{slug}`` to your route path: ``/blog/show/{slug}`` or
give the argument a default value (i.e. ``$slug = null``).

    Some mandatory parameters are missing ("slug") to generate a URL for route
    "blog_show".

This means that you're trying to generate a URL to the ``blog_show`` route but
you are *not* passing a ``slug`` value (which is required, because it has a
``{slug}`` parameter in the route path). To fix this, pass a ``slug`` value when
generating the route::

    $this->generateUrl('blog_show', ['slug' => 'slug-value']);

    // or, in Twig
    // {{ path('blog_show', {slug: 'slug-value'}) }}

Learn more about Routing
------------------------

.. toctree::
    :hidden:

    controller

.. toctree::
    :maxdepth: 1
    :glob:

    routing/*

.. _`PHP regular expressions`: https://www.php.net/manual/en/book.pcre.php
.. _`PCRE Unicode properties`: http://php.net/manual/en/regexp.reference.unicode.php
.. _`full param converter documentation`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`FOSJsRoutingBundle`: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
