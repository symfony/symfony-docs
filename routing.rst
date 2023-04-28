Routing
=======

When your application receives a request, it calls a
:doc:`controller action </controller>` to generate the response. The routing
configuration defines which action to run for each incoming URL. It also
provides other useful features, like generating SEO-friendly URLs (e.g.
``/read/intro-to-symfony`` instead of ``index.php?article_id=57``).

.. _routing-creating-routes:

Creating Routes
---------------

Routes can be configured in YAML, XML, PHP or using attributes.
All formats provide the same features and performance, so choose
your favorite.
:ref:`Symfony recommends attributes <best-practice-controller-annotations>`
because it's convenient to put the route and controller in the same place.

Creating Routes as Attributes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

PHP attributes allow to define routes next to the code of the
:doc:`controllers </controller>` associated to those routes. Attributes are
native in PHP 8 and higher versions, so you can use them right away.

You need to add a bit of configuration to your project before using them. If your
project uses :ref:`Symfony Flex <symfony-flex>`, this file is already created for you.
Otherwise, create the following file manually:

.. code-block:: yaml

    # config/routes/attributes.yaml
    controllers:
        resource:
            path: ../../src/Controller/
            namespace: App\Controller
        type: attribute

    kernel:
        resource: App\Kernel
        type: attribute

This configuration tells Symfony to look for routes defined as attributes on
classes declared in the ``App\Controller`` namespace and stored in the
``src/Controller/`` directory which follows the PSR-4 standard. The kernel can
act as a controller too, which is especially useful for small applications that
use Symfony as a microframework.

.. versionadded:: 6.2

    The feature to import routes from a PSR-4 namespace root was introduced in Symfony 6.2.

Suppose you want to define a route for the ``/blog`` URL in your application. To
do so, create a :doc:`controller class </controller>` like the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            #[Route('/blog', name: 'blog_list')]
            public function list(): Response
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

.. caution::

    If you define multiple PHP classes in the same file, Symfony only loads the
    routes of the first class, ignoring all the other routes.

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
                // controller class, you can skip the 'method_name' part:
                // ->controller(BlogController::class)
            ;
        };

.. note::

    By default Symfony only loads the routes defined in YAML format. If you
    define routes in XML and/or PHP formats, you need to
    :ref:`update the src/Kernel.php file <configuration-formats>`.

.. _routing-matching-http-methods:

Matching HTTP Methods
~~~~~~~~~~~~~~~~~~~~~

By default, routes match any HTTP verb (``GET``, ``POST``, ``PUT``, etc.)
Use the ``methods`` option to restrict the verbs each route should respond to:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogApiController.php
        namespace App\Controller;

        // ...

        class BlogApiController extends AbstractController
        {
            #[Route('/api/posts/{id}', methods: ['GET', 'HEAD'])]
            public function show(int $id): Response
            {
                // ... return a JSON response with the post
            }

            #[Route('/api/posts/{id}', methods: ['PUT'])]
            public function edit(int $id): Response
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
    ``_method`` with the method to use (e.g. ``<input type="hidden" name="_method" value="PUT">``).
    If you create your forms with :doc:`Symfony Forms </forms>` this is done
    automatically for you when the :ref:`framework.http_method_override <configuration-framework-http_method_override>`
    option is ``true``.

.. _routing-matching-expressions:

Matching Expressions
~~~~~~~~~~~~~~~~~~~~

Use the ``condition`` option if you need some route to match based on some
arbitrary matching logic:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/DefaultController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class DefaultController extends AbstractController
        {
            #[Route(
                '/contact',
                name: 'contact',
                condition: "context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'",
                // expressions can also include config parameters:
                // condition: "request.headers.get('User-Agent') matches '%app.allowed_browsers%'"
            )]
            public function contact(): Response
            {
                // ...
            }

            #[Route(
                '/posts/{id}',
                name: 'post_show',
                // expressions can retrieve route parameter values using the "params" variable
                condition: "params['id'] < 1000"
            )]
            public function showPost(int $id): Response
            {
                // ... return a JSON response with the post
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        contact:
            path:       /contact
            controller: 'App\Controller\DefaultController::contact'
            condition:  "context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'"
            # expressions can also include configuration parameters:
            # condition: "request.headers.get('User-Agent') matches '%app.allowed_browsers%'"
            # expressions can even use environment variables:
            # condition: "context.getHost() == env('APP_MAIN_HOST')"

        post_show:
            path:       /posts/{id}
            controller: 'App\Controller\DefaultController::showPost'
            # expressions can retrieve route parameter values using the "params" variable
            condition:  "params['id'] < 1000"

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/contact" controller="App\Controller\DefaultController::contact">
                <condition>context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'</condition>
                <!-- expressions can also include configuration parameters: -->
                <!-- <condition>request.headers.get('User-Agent') matches '%app.allowed_browsers%'</condition> -->
                <!-- expressions can even use environment variables: -->
                <!-- <condition>context.getHost() == env('APP_MAIN_HOST')</condition> -->
            </route>

            <route id="post_show" path="/posts/{id}" controller="App\Controller\DefaultController::showPost">
                <!-- expressions can retrieve route parameter values using the "params" variable -->
                <condition>params['id'] &lt; 1000</condition>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\DefaultController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('contact', '/contact')
                ->controller([DefaultController::class, 'contact'])
                ->condition('context.getMethod() in ["GET", "HEAD"] and request.headers.get("User-Agent") matches "/firefox/i"')
                // expressions can also include configuration parameters:
                // ->condition('request.headers.get("User-Agent") matches "%app.allowed_browsers%"')
                // expressions can even use environment variables:
                // ->condition('context.getHost() == env("APP_MAIN_HOST")')
            ;
            $routes->add('post_show', '/posts/{id}')
                ->controller([DefaultController::class, 'showPost'])
                // expressions can retrieve route parameter values using the "params" variable
                ->condition('params["id"] < 1000')
            ;
        };

The value of the ``condition`` option is an expression using any valid
:doc:`expression language syntax </reference/formats/expression_language>` and
can use any of these variables created by Symfony:

``context``
    An instance of :class:`Symfony\\Component\\Routing\\RequestContext`,
    which holds the most fundamental information about the route being matched.

``request``
    The :ref:`Symfony Request <component-http-foundation-request>` object that
    represents the current request.

``params``
    An array of matched :ref:`route parameters <routing-route-parameters>` for
    the current route.

.. versionadded:: 6.1

    The ``params`` variable was introduced in Symfony 6.1.

You can also use these functions:

``env(string $name)``
    Returns the value of a variable using :doc:`Environment Variable Processors <configuration/env_var_processors>`

``service(string $alias)``
    Returns a routing condition service.

    First, add the ``#[AsRoutingConditionService]`` attribute or ``routing.condition_service``
    tag to the services that you want to use in route conditions::

        use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;
        use Symfony\Component\HttpFoundation\Request;

        #[AsRoutingConditionService(alias: 'route_checker')]
        class RouteChecker
        {
            public function check(Request $request): bool
            {
                // ...
            }
        }

    Then, use the ``service()`` function to refer to that service inside conditions::

        // Controller (using an alias):
        #[Route(condition: "service('route_checker').check(request)")]
        // Or without alias:
        #[Route(condition: "service('App\\\Service\\\RouteChecker').check(request)")]

.. versionadded:: 6.1

    The ``service(string $alias)`` function and ``#[AsRoutingConditionService]``
    attribute were introduced in Symfony 6.1.

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

.. _routing-route-parameters:

Route Parameters
----------------

The previous examples defined routes where the URL never changes (e.g. ``/blog``).
However, it's common to define routes where some parts are variable. For example,
the URL to display some blog post will probably include the title or slug
(e.g. ``/blog/my-first-post`` or ``/blog/all-about-symfony``).

In Symfony routes, variable parts are wrapped in ``{ }``.
For example, the route to display the blog post contents is defined as ``/blog/{slug}``:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            // ...

            #[Route('/blog/{slug}', name: 'blog_show')]
            public function show(string $slug): Response
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

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            #[Route('/blog/{page}', name: 'blog_list', requirements: ['page' => '\d+'])]
            public function list(int $page): Response
            {
                // ...
            }

            #[Route('/blog/{slug}', name: 'blog_show')]
            public function show($slug): Response
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

    The :class:`Symfony\\Component\\Routing\\Requirement\\Requirement` enum
    contains a collection of commonly used regular-expression constants such as
    digits, dates and UUIDs which can be used as route parameter requirements.

    .. versionadded:: 6.1

        The ``Requirement`` enum was introduced in Symfony 6.1.

.. tip::

    Route requirements (and route paths too) can include
    :ref:`configuration parameters <configuration-parameters>`, which is useful to
    define complex regular expressions once and reuse them in multiple routes.

.. tip::

    Parameters also support `PCRE Unicode properties`_, which are escape
    sequences that match generic character types. For example, ``\p{Lu}``
    matches any uppercase character in any language, ``\p{Greek}`` matches any
    Greek characters, etc.

.. note::

    When using regular expressions in route parameters, you can set the ``utf8``
    route option to ``true`` to make any ``.`` character match any UTF-8
    characters instead of just a single byte.

If you prefer, requirements can be inlined in each parameter using the syntax
``{parameter_name<requirements>}``. This feature makes configuration more
concise, but it can decrease route readability when requirements are complex:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            #[Route('/blog/{page<\d+>}', name: 'blog_list')]
            public function list(int $page): Response
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
adding a default value for the ``{page}`` parameter. When using annotations or attributes,
default values are defined in the arguments of the controller action. In the
other configuration formats they are defined with the ``defaults`` option:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            #[Route('/blog/{page}', name: 'blog_list', requirements: ['page' => '\d+'])]
            public function list(int $page = 1): Response
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

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            #[Route('/blog/{page<\d+>?1}', name: 'blog_list')]
            public function list(int $page): Response
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

            <route id="blog_list" path="/blog/{page<\d+>?1}"
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
    ``?`` character (e.g. ``/blog/{page?}``). If you do this, don't forget to
    update the types of the related controller arguments to allow passing
    ``null`` values (e.g. replace ``int $page`` by ``?int $page``).

Priority Parameter
~~~~~~~~~~~~~~~~~~

Symfony evaluates routes in the order they are defined. If the path of a route
matches many different patterns, it might prevent other routes from being
matched. In YAML and XML you can move the route definitions up or down in the
configuration file to control their priority. In routes defined as PHP
annotations or attributes this is much harder to do, so you can set the
optional ``priority`` parameter in those routes to control their priority:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            /**
             * This route has a greedy pattern and is defined first.
             */
            #[Route('/blog/{slug}', name: 'blog_show')]
            public function show(string $slug): Response
            {
                // ...
            }

            /**
             * This route could not be matched without defining a higher priority than 0.
             */
            #[Route('/blog/list', name: 'blog_list', priority: 2)]
            public function list(): Response
            {
                // ...
            }
        }

The priority parameter expects an integer value. Routes with higher priority
are sorted before routes with lower priority. The default value when it is not
defined is ``0``.

Parameter Conversion
~~~~~~~~~~~~~~~~~~~~

A common routing need is to convert the value stored in some parameter (e.g. an
integer acting as the user ID) into another value (e.g. the object that
represents the user). This feature is called a "param converter".

.. versionadded:: 6.2

    Starting from Symfony 6.2, route param conversion is a built-in feature.
    In previous Symfony versions you had to install the package
    ``sensio/framework-extra-bundle`` before using this feature.

Now, keep the previous route configuration, but change the arguments of the
controller action. Instead of ``string $slug``, add ``BlogPost $post``::

    // src/Controller/BlogController.php
    namespace App\Controller;

    use App\Entity\BlogPost;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class BlogController extends AbstractController
    {
        // ...

        #[Route('/blog/{slug}', name: 'blog_show')]
        public function show(BlogPost $post): Response
        {
            // $post is the object whose slug matches the routing parameter

            // ...
        }
    }

If your controller arguments include type-hints for objects (``BlogPost`` in
this case), the "param converter" makes a database request to find the object
using the request parameters (``slug`` in this case). If no object is found,
Symfony generates a 404 response automatically.

Check out the :ref:`Doctrine param conversion documentation <doctrine-entity-value-resolver>`
to learn about the ``#[MapEntity]`` attribute that can be used to customize the
database queries used to fetch the object from the route parameter.

Backed Enum Parameters
~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 6.3

    The support of ``\BackedEnum`` as route parameters was introduced Symfony 6.3.

You can use PHP `backed enumerations`_ as route parameters because Symfony will
convert them automatically to their scalar values.

.. code-block:: php-attributes

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use App\Enum\OrderStatusEnum;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class OrderController extends AbstractController
    {
        #[Route('/orders/list/{status}', name: 'list_orders_by_status')]
        public function list(OrderStatusEnum $status = OrderStatusEnum::Paid): Response
        {
            // ...
        }
    }

Special Parameters
~~~~~~~~~~~~~~~~~~

In addition to your own parameters, routes can include any of the following
special parameters created by Symfony:

.. _routing-format-parameter:
.. _routing-locale-parameter:

``_controller``
    This parameter is used to determine which controller and action is executed
    when the route is matched.

``_format``
    The matched value is used to set the "request format" of the ``Request`` object.
    This is used for such things as setting the ``Content-Type`` of the response
    (e.g. a ``json`` format translates into a ``Content-Type`` of ``application/json``).

``_fragment``
    Used to set the fragment identifier, which is the optional last part of a URL that
    starts with a ``#`` character and is used to identify a portion of a document.

``_locale``
    Used to set the :ref:`locale <translation-locale-url>` on the request.

You can include these attributes (except ``_fragment``) both in individual routes
and in route imports. Symfony defines some special attributes with the same name
(except for the leading underscore) so you can define them easier:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/ArticleController.php
        namespace App\Controller;

        // ...
        class ArticleController extends AbstractController
        {
            #[Route(
                path: '/articles/{_locale}/search.{_format}',
                locale: 'en',
                format: 'html',
                requirements: [
                    '_locale' => 'en|fr',
                    '_format' => 'html|xml',
                ],
            )]
            public function search(): Response
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
                <requirement key="_format">html|xml</requirement>

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
                    '_format' => 'html|xml',
                ])
            ;
        };

Extra Parameters
~~~~~~~~~~~~~~~~

In the ``defaults`` option of a route you can optionally define parameters not
included in the route configuration. This is useful to pass extra arguments to
the controllers of the routes:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            #[Route('/blog/{page}', name: 'blog_index', defaults: ['page' => 1, 'title' => 'Hello world!'])]
            public function index(int $page, string $title): Response
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

    .. code-block:: php-attributes

        // src/Controller/DefaultController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class DefaultController extends AbstractController
        {
            #[Route('/share/{token}', name: 'share', requirements: ['token' => '.+'])]
            public function share($token): Response
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

    If the route defines several parameters and you apply this permissive
    regular expression to all of them, you might get unexpected results. For
    example, if the route definition is ``/share/{path}/{token}`` and both
    ``path`` and ``token`` accept ``/``, then ``token`` will only get the last part
    and the rest is matched by ``path``.

.. note::

    If the route includes the special ``{_format}`` parameter, you shouldn't
    use the ``.+`` requirement for the parameters that allow slashes. For example,
    if the pattern is ``/share/{token}.{_format}`` and ``{token}`` allows any
    character, the ``/share/foo/bar.json`` URL will consider ``foo/bar.json``
    as the token and the format will be empty. This can be solved by replacing
    the ``.+`` requirement by ``[^.]+`` to allow any character except dots.

.. _routing-alias:

Route Aliasing
--------------

Route alias allow you to have multiple name for the same route:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        new_route_name:
            alias: original_route_name

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="new_route_name" alias="original_route_name"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->alias('new_route_name', 'original_route_name');
        };

In this example, both ``original_route_name`` and ``new_route_name`` routes can
be used in the application and will produce the same result.

.. _routing-alias-deprecation:

Deprecating Route Aliases
~~~~~~~~~~~~~~~~~~~~~~~~~

If some route alias should no longer be used (because it is outdated or
you decided not to maintain it anymore), you can deprecate its definition:

.. configuration-block::

    .. code-block:: yaml

        new_route_name:
            alias: original_route_name

            # this outputs the following generic deprecation message:
            # Since acme/package 1.2: The "new_route_name" route alias is deprecated. You should stop using it, as it will be removed in the future.
            deprecated:
                package: 'acme/package'
                version: '1.2'

            # you can also define a custom deprecation message (%alias_id% placeholder is available)
            deprecated:
                package: 'acme/package'
                version: '1.2'
                message: 'The "%alias_id%" route alias is deprecated. Do not use it anymore.'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="new_route_name" alias="original_route_name">
                <!-- this outputs the following generic deprecation message:
                     Since acme/package 1.2: The "new_route_name" route alias is deprecated. You should stop using it, as it will be removed in the future. -->
                <deprecated package="acme/package" version="1.2"/>

                <!-- you can also define a custom deprecation message (%alias_id% placeholder is available) -->
                <deprecated package="acme/package" version="1.2">
                    The "%alias_id%" route alias is deprecated. Do not use it anymore.
                </deprecated>
            </route>
        </routes>

    .. code-block:: php

        $routes->alias('new_route_name', 'original_route_name')
            // this outputs the following generic deprecation message:
            // Since acme/package 1.2: The "new_route_name" route alias is deprecated. You should stop using it, as it will be removed in the future.
            ->deprecate('acme/package', '1.2', '')

            // you can also define a custom deprecation message (%alias_id% placeholder is available)
            ->deprecate(
                'acme/package',
                '1.2',
                'The "%alias_id%" route alias is deprecated. Do not use it anymore.'
            )
        ;

In this example, every time the ``new_route_name`` alias is used, a deprecation
warning is triggered, advising you to stop using that alias.

The message is actually a message template, which replaces occurrences of the
``%alias_id%`` placeholder by the route alias name. You **must** have
at least one occurrence of the ``%alias_id%`` placeholder in your template.

.. _routing-route-groups:

Route Groups and Prefixes
-------------------------

It's common for a group of routes to share some options (e.g. all routes related
to the blog start with ``/blog``) That's why Symfony includes a feature to share
route configuration.

When defining routes as attributes, put the common configuration
in the ``#[Route]`` attribute of the controller class.
In other routing formats, define the common configuration using options
when importing the routes.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        #[Route('/blog', requirements: ['_locale' => 'en|es|fr'], name: 'blog_')]
        class BlogController extends AbstractController
        {
            #[Route('/{_locale}', name: 'index')]
            public function index(): Response
            {
                // ...
            }

            #[Route('/{_locale}/posts/{slug}', name: 'show')]
            public function show(string $slug): Response
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes/annotations.yaml
        controllers:
            resource: '../../src/Controller/'
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

            # you can optionally exclude some files/subdirectories when loading annotations
            # exclude: '../../src/Controller/{DebugEmailController}.php'

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
                the 'exclude' option defines the files or subdirectories ignored when loading annotations
            -->
            <import resource="../../src/Controller/"
                type="annotation"
                prefix="/blog"
                name-prefix="blog_"
                exclude="../../src/Controller/{DebugEmailController}.php">
                <!-- these requirements are added to all imported routes -->
                <requirement key="_locale">en|es|fr</requirement>
            </import>

            <!-- An imported route with an empty URL will become "/blog/"
                 Uncomment this option to make that URL "/blog" instead -->
            <import resource="../../src/Controller/" type="annotation"
                    prefix="/blog"
                    trailing-slash-on-root="false">
                    <!-- ... -->
            </import>
        </routes>

    .. code-block:: php

        // config/routes/annotations.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->import(
                    '../../src/Controller/',
                    'annotation',
                    false,
                    // the optional fourth argument is used to exclude some files
                    // or subdirectories when loading annotations
                    '../../src/Controller/{DebugEmailController}.php'
                )
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
and its URL will be ``/blog/{_locale}``. The route of the ``show()`` action will be called
``blog_show`` and its URL will be ``/blog/{_locale}/posts/{slug}``. Both routes
will also validate that the ``_locale`` parameter matches the regular expression
defined in the class annotation.

.. note::

    If any of the prefixed routes defines an empty path, Symfony adds a trailing
    slash to it. In the previous example, an empty path prefixed with ``/blog``
    will result in the ``/blog/`` URL. If you want to avoid this behavior, set
    the ``trailing_slash_on_root`` option to ``false`` (this option is not
    available when using PHP attributes or annotations):

    .. configuration-block::

        .. code-block:: yaml

            # config/routes/annotations.yaml
            controllers:
                resource: '../../src/Controller/'
                type:     annotation
                prefix:   '/blog'
                trailing_slash_on_root: false
                # ...

        .. code-block:: xml

            <!-- config/routes/annotations.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                    https://symfony.com/schema/routing/routing-1.0.xsd">

                <import resource="../../src/Controller/"
                    type="annotation"
                    prefix="/blog"
                    name-prefix="blog_"
                    trailing-slash-on-root="false"
                    exclude="../../src/Controller/{DebugEmailController}.php">
                    <!-- ... -->
                </import>
            </routes>

        .. code-block:: php

            // config/routes/annotations.php
            use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

            return function (RoutingConfigurator $routes) {
                $routes->import('../../src/Controller/', 'annotation')
                    // the second argument is the $trailingSlashOnRoot option
                    ->prefix('/blog', false)

                    // ...
                ;
            };

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
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class BlogController extends AbstractController
    {
        #[Route('/blog', name: 'blog_list')]
        public function list(Request $request): Response
        {
            $routeName = $request->attributes->get('_route');
            $routeParameters = $request->attributes->get('_route_params');

            // use this to get all the available attributes (not only routing ones):
            $allAttributes = $request->attributes->all();

            // ...
        }
    }

You can get this information in services too injecting the ``request_stack``
service to :doc:`get the Request object in a service </service_container/request>`.
In templates, use the :ref:`Twig global app variable <twig-app-variable>` to get
the current route and its attributes:

.. code-block:: twig

    {% set route_name = app.current_route %}
    {% set route_parameters = app.current_route_parameters %}

.. versionadded:: 6.2

    The ``app.current_route`` and ``app.current_route_parameters`` variables
    were introduced in Symfony 6.2.
    Before you had to access ``_route`` and ``_route_params`` request
    attributes using ``app.request.attributes.get()``.

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
                # add this to remove the original route attributes when redirecting
                ignoreAttributes: true

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
                    // optionally you can define some arguments passed to the route
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

    .. code-block:: php-attributes

        // src/Controller/MainController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class MainController extends AbstractController
        {
            #[Route('/', name: 'mobile_homepage', host: 'm.example.com')]
            public function mobileHomepage(): Response
            {
                // ...
            }

            #[Route('/', name: 'homepage')]
            public function homepage(): Response
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


The value of the ``host`` option can include parameters (which is useful in
multi-tenant applications) and these parameters can be validated too with
``requirements``:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/MainController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class MainController extends AbstractController
        {
            #[Route(
                '/',
                name: 'mobile_homepage',
                host: '{subdomain}.example.com',
                defaults: ['subdomain' => 'm'],
                requirements: ['subdomain' => 'm|mobile'],
            )]
            public function mobileHomepage(): Response
            {
                // ...
            }

            #[Route('/', name: 'homepage')]
            public function homepage(): Response
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
otherwise you need to include a subdomain value each time you generate a URL using
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
            // or get the value from some configuration parameter:
            // ['HTTP_HOST' => 'm.'.$client->getContainer()->getParameter('domain')]
        );

.. tip::

    You can also use the inline defaults and requirements format in the
    ``host`` option: ``{subdomain<m|mobile>?m}.example.com``

.. _i18n-routing:

Localized Routes (i18n)
-----------------------

If your application is translated into multiple languages, each route can define
a different URL per each :ref:`translation locale <translation-locale>`. This
avoids the need for duplicating routes, which also reduces the potential bugs:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/CompanyController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class CompanyController extends AbstractController
        {
            #[Route(path: [
                'en' => '/about-us',
                'nl' => '/over-ons'
            ], name: 'about_us')]
            public function about(): Response
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

.. note::

    When using PHP attributes for localized routes, you have to use the ``path``
    named parameter to specify the array of paths.

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

            <import resource="../../src/Controller/" type="annotation">
                <!-- don't prefix URLs for English, the default locale -->
                <prefix locale="en"></prefix>
                <prefix locale="nl">/nl</prefix>
            </import>
        </routes>

    .. code-block:: php

        // config/routes/annotations.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('../../src/Controller/', 'annotation')
                ->prefix([
                    // don't prefix URLs for English, the default locale
                    'en' => '',
                    'nl' => '/nl',
                ])
            ;
        };

Another common requirement is to host the website on a different domain
according to the locale. This can be done by defining a different host for each
locale.

.. configuration-block::

    .. code-block:: yaml

        # config/routes/annotations.yaml
        controllers:
            resource: '../../src/Controller/'
            type: annotation
            host:
                en: 'https://www.example.com'
                nl: 'https://www.example.nl'

    .. code-block:: xml

        <!-- config/routes/annotations.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">
            <import resource="../../src/Controller/" type="annotation">
                <host locale="en">https://www.example.com</host>
                <host locale="nl">https://www.example.nl</host>
            </import>
        </routes>

    .. code-block:: php

        // config/routes/annotations.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
        return function (RoutingConfigurator $routes) {
            $routes->import('../../src/Controller/', 'annotation')
                ->host([
                    'en' => 'https://www.example.com',
                    'nl' => 'https://www.example.nl',
                ])
            ;
        };

.. _stateless-routing:

Stateless Routes
----------------

Sometimes, when an HTTP response should be cached, it is important to ensure
that can happen. However, whenever a session is started during a request,
Symfony turns the response into a private non-cacheable response.

For details, see :doc:`/http_cache`.

Routes can configure a ``stateless`` boolean option in order to declare that the
session shouldn't be used when matching a request:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/MainController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class MainController extends AbstractController
        {
            #[Route('/', name: 'homepage', stateless: true)]
            public function homepage(): Response
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        homepage:
            controller: App\Controller\MainController::homepage
            path: /
            stateless: true

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">
            <route id="homepage" controller="App\Controller\MainController::homepage" path="/" stateless="true"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\MainController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('homepage', '/')
                ->controller([MainController::class, 'homepage'])
                ->stateless()
            ;
        };

Now, if the session is used, the application will report it based on your
``kernel.debug`` parameter:

* ``enabled``: will throw an :class:`Symfony\\Component\\HttpKernel\\Exception\\UnexpectedSessionUsageException` exception
* ``disabled``: will log a warning

It will help you understand and hopefully fixing unexpected behavior in your application.

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
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

    class BlogController extends AbstractController
    {
        #[Route('/blog', name: 'blog_list')]
        public function list(): Response
        {
            // generate a URL with no route arguments
            $signUpPage = $this->generateUrl('sign_up');

            // generate a URL with route arguments
            $userProfilePage = $this->generateUrl('user_profile', [
                'username' => $user->getUserIdentifier(),
            ]);

            // generated URLs are "absolute paths" by default. Pass a third optional
            // argument to generate different URLs (e.g. an "absolute URL")
            $signUpPage = $this->generateUrl('sign_up', [], UrlGeneratorInterface::ABSOLUTE_URL);

            // when a route is localized, Symfony uses by default the current request locale
            // pass a different '_locale' value if you want to set the locale explicitly
            $signUpPageInDutch = $this->generateUrl('sign_up', ['_locale' => 'nl']);

            // ...
        }
    }

.. note::

    If you pass to the ``generateUrl()`` method some parameters that are not
    part of the route definition, they are included in the generated URL as a
    query string::

        $this->generateUrl('blog', ['page' => 2, 'category' => 'Symfony']);
        // the 'blog' route only defines the 'page' parameter; the generated URL is:
        // /blog/2?category=Symfony

.. caution::

    While objects are converted to string when used as placeholders, they are not
    converted when used as extra parameters. So, if you're passing an object (e.g. an Uuid)
    as value of an extra parameter, you need to explicitly convert it to a string::

        $this->generateUrl('blog', ['uuid' => (string) $entity->getUuid()]);

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
        public function __construct(
            private UrlGeneratorInterface $router,
        ) {
        }

        public function someMethod()
        {
            // ...

            // generate a URL with no route arguments
            $signUpPage = $this->router->generate('sign_up');

            // generate a URL with route arguments
            $userProfilePage = $this->router->generate('user_profile', [
                'username' => $user->getUserIdentifier(),
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
JavaScript variables. The ``escape()`` filter is needed to escape any
non-JavaScript-safe values:

.. code-block:: html+twig

    <script>
        const route = "{{ path('blog_show', {slug: 'my-blog-post'})|escape('js') }}";
    </script>

If you need to generate URLs dynamically or if you are using pure JavaScript
code, this solution doesn't work. In those cases, consider using the
`FOSJsRoutingBundle`_.

.. _router-generate-urls-commands:

Generating URLs in Commands
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Generating URLs in commands works the same as
:ref:`generating URLs in services <routing-generating-urls-in-services>`. The
only difference is that commands are not executed in the HTTP context. Therefore,
if you generate absolute URLs, you'll get ``http://localhost/`` as the host name
instead of your real host name.

The solution is to configure the ``default_uri`` option to define the
"request context" used by commands when they generate URLs:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/routing.yaml
        framework:
            router:
                # ...
                default_uri: 'https://example.org/my/path/'

    .. code-block:: xml

        <!-- config/packages/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:router default-uri="https://example.org/my/path/">
                    <!-- ... -->
                </framework:router>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/routing.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->router()->defaultUri('https://example.org/my/path/');
        };

Now you'll get the expected results when generating URLs in your commands::

    // src/Command/SomeCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    // ...

    class SomeCommand extends Command
    {
        public function __construct(private UrlGeneratorInterface $urlGenerator)
        {
            parent::__construct();
        }

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            // generate a URL with no route arguments
            $signUpPage = $this->urlGenerator->generate('sign_up');

            // generate a URL with route arguments
            $userProfilePage = $this->urlGenerator->generate('user_profile', [
                'username' => $user->getUserIdentifier(),
            ]);

            // by default, generated URLs are "absolute paths". Pass a third optional
            // argument to generate different URIs (e.g. an "absolute URL")
            $signUpPage = $this->urlGenerator->generate('sign_up', [], UrlGeneratorInterface::ABSOLUTE_URL);

            // when a route is localized, Symfony uses by default the current request locale
            // pass a different '_locale' value if you want to set the locale explicitly
            $signUpPageInDutch = $this->urlGenerator->generate('sign_up', ['_locale' => 'nl']);

            // ...
        }
    }

.. note::

    By default, the URLs generated for web assets use the same ``default_uri``
    value, but you can change it with the ``asset.request_context.base_path``
    and ``asset.request_context.secure`` container parameters.

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
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

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

    .. code-block:: php-attributes

        // src/Controller/SecurityController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class SecurityController extends AbstractController
        {
            #[Route('/login', name: 'login', schemes: ['https'])]
            public function login(): Response
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
            resource: '../../src/Controller/'
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

            <import resource="../../src/Controller/" type="annotation">
                <default key="schemes">HTTPS</default>
            </import>
        </routes>

    .. code-block:: php

        // config/routes/annotations.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('../../src/Controller/', 'annotation')
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

.. code-block:: text

    Controller "App\\Controller\\BlogController::show()" requires that you
    provide a value for the "$slug" argument.

This happens when your controller method has an argument (e.g. ``$slug``)::

    public function show(string $slug): Response
    {
        // ...
    }

But your route path does *not* have a ``{slug}`` parameter (e.g. it is
``/blog/show``). Add a ``{slug}`` to your route path: ``/blog/show/{slug}`` or
give the argument a default value (i.e. ``$slug = null``).

.. code-block:: text

    Some mandatory parameters are missing ("slug") to generate a URL for route
    "blog_show".

This means that you're trying to generate a URL to the ``blog_show`` route but
you are *not* passing a ``slug`` value (which is required, because it has a
``{slug}`` parameter in the route path). To fix this, pass a ``slug`` value when
generating the route::

    $this->generateUrl('blog_show', ['slug' => 'slug-value']);

or, in Twig:

.. code-block:: twig

    {{ path('blog_show', {slug: 'slug-value'}) }}

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
.. _`PCRE Unicode properties`: https://www.php.net/manual/en/regexp.reference.unicode.php
.. _`FOSJsRoutingBundle`: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
.. _`backed enumerations`: https://www.php.net/manual/en/language.enumerations.backed.php
