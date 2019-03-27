.. index::
   single: Routing
   single: Components; Routing

The Routing Component
=====================

    The Routing component maps an HTTP request to a set of configuration
    variables.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/routing

Alternatively, you can clone the `<https://github.com/symfony/routing>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

.. seealso::

    This article explains how to use the Routing features as an independent
    component in any PHP application. Read the :doc:`/routing` article to learn
    about how to use it in Symfony applications.

In order to set up a basic routing system you need three parts:

* A :class:`Symfony\\Component\\Routing\\RouteCollection`, which contains the route definitions (instances of the class :class:`Symfony\\Component\\Routing\\Route`)
* A :class:`Symfony\\Component\\Routing\\RequestContext`, which has information about the request
* A :class:`Symfony\\Component\\Routing\\Matcher\\UrlMatcher`, which performs the mapping of the request to a single route

Here is a quick example. Notice that this assumes that you've already configured
your autoloader to load the Routing component::

    use Symfony\Component\Routing\Matcher\UrlMatcher;
    use Symfony\Component\Routing\RequestContext;
    use Symfony\Component\Routing\Route;
    use Symfony\Component\Routing\RouteCollection;

    $route = new Route('/foo', ['_controller' => 'MyController']);
    $routes = new RouteCollection();
    $routes->add('route_name', $route);

    $context = new RequestContext('/');

    $matcher = new UrlMatcher($routes, $context);

    $parameters = $matcher->match('/foo');
    // ['_controller' => 'MyController', '_route' => 'route_name']

.. note::

    The :class:`Symfony\\Component\\Routing\\RequestContext` parameters can be populated
    with the values stored in ``$_SERVER``, but it's easier to use the HttpFoundation
    component as explained :ref:`below <components-routing-http-foundation>`.

You can add as many routes as you like to a
:class:`Symfony\\Component\\Routing\\RouteCollection`.

The :method:`RouteCollection::add() <Symfony\\Component\\Routing\\RouteCollection::add>`
method takes two arguments. The first is the name of the route. The second
is a :class:`Symfony\\Component\\Routing\\Route` object, which expects a
URL path and some array of custom variables in its constructor. This array
of custom variables can be *anything* that's significant to your application,
and is returned when that route is matched.

The :method:`UrlMatcher::match() <Symfony\\Component\\Routing\\Matcher\\UrlMatcher::match>`
returns the variables you set on the route as well as the wildcard placeholders
(see below). Your application can now use this information to continue
processing the request. In addition to the configured variables, a ``_route``
key is added, which holds the name of the matched route.

If no matching route can be found, a
:class:`Symfony\\Component\\Routing\\Exception\\ResourceNotFoundException` will
be thrown.

Defining Routes
~~~~~~~~~~~~~~~

A full route definition can contain up to eight parts:

#. The URL pattern. This is matched against the URL passed to the
   ``RequestContext``. It is not a regular expression, but can contain named
   wildcard placeholders (e.g. ``{slug}``) to match dynamic parts in the URL.
   The component will create the regular expression from it.

#. An array of default parameters. This contains an array of arbitrary values
   that will be returned when the request matches the route. It is used by
   convention to map a controller to the route.

#. An array of requirements. These define constraints for the values of the
   placeholders in the pattern as regular expressions.

#. An array of options. These contain advanced settings for the route and
   can be used to control encoding or customize compilation.
   See :ref:`routing-unicode-support` below. You can learn more about them by
   reading :method:`Symfony\\Component\\Routing\\Route::setOptions` implementation.

#. A host. This is matched against the host of the request. See
   :doc:`/routing/hostname_pattern` for more details.

#. An array of schemes. These enforce a certain HTTP scheme (``http``, ``https``).

#. An array of methods. These enforce a certain HTTP request method (``HEAD``,
   ``GET``, ``POST``, ...).

#. A condition, using the :doc:`/components/expression_language/syntax`.
   A string that must evaluate to ``true`` so the route matches. See
   :doc:`/routing/conditions` for more details.

Take the following route, which combines several of these ideas::

    $route = new Route(
        '/archive/{month}', // path
        ['_controller' => 'showArchive'], // default values
        ['month' => '[0-9]{4}-[0-9]{2}', 'subdomain' => 'www|m'], // requirements
        [], // options
        '{subdomain}.example.com', // host
        [], // schemes
        [], // methods
        'context.getHost() matches "/(secure|admin).example.com/"' // condition
    );

    // ...

    $parameters = $matcher->match('/archive/2012-01');
    // [
    //     '_controller' => 'showArchive',
    //     'month' => '2012-01',
    //     'subdomain' => 'www',
    //     '_route' => ...
    // ]

    $parameters = $matcher->match('/archive/foo');
    // throws ResourceNotFoundException

In this case, the route is matched by ``/archive/2012-01``, because the ``{month}``
wildcard matches the regular expression wildcard given. However, ``/archive/foo``
does *not* match, because "foo" fails the month wildcard.

When using wildcards, these are returned in the array result when calling
``match``. The part of the path that the wildcard matched (e.g. ``2012-01``) is used
as value.

A placeholder matches any character except slashes ``/`` by default, unless you define
a specific requirement for it.
The reason is that they are used by convention to separate different placeholders.

If you want a placeholder to match anything, it must be the last of the route::

    $route = new Route(
        '/start/{required}/{anything}',
        ['required' => 'default'], // should always be defined
        ['anything' => '.*'] // explicit requirement to allow "/"
    );

Learn more about it by reading :ref:`routing/slash_in_parameter`.

Using Prefixes and Collection Settings
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can add routes or other instances of
:class:`Symfony\\Component\\Routing\\RouteCollection` to *another* collection.
This way you can build a tree of routes. Additionally you can define a prefix
and default values for the parameters, requirements, options, schemes and the
host to all routes of a subtree using methods provided by the
``RouteCollection`` class::

    $rootCollection = new RouteCollection();

    $subCollection = new RouteCollection();
    $subCollection->add(...);
    $subCollection->add(...);
    $subCollection->addPrefix('/prefix');
    $subCollection->addDefaults([...]);
    $subCollection->addRequirements([...]);
    $subCollection->addOptions([...]);
    $subCollection->setHost('{subdomain}.example.com');
    $subCollection->setMethods(['POST']);
    $subCollection->setSchemes(['https']);
    $subCollection->setCondition('context.getHost() matches "/(secure|admin).example.com/"');

    $rootCollection->addCollection($subCollection);

Set the Request Parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Routing\\RequestContext` provides information
about the current request. You can define all parameters of an HTTP request
with this class via its constructor::

    public function __construct(
        $baseUrl = '',
        $method = 'GET',
        $host = 'localhost',
        $scheme = 'http',
        $httpPort = 80,
        $httpsPort = 443,
        $path = '/',
        $queryString = ''
    )

.. _components-routing-http-foundation:

Normally you can pass the values from the ``$_SERVER`` variable to populate the
:class:`Symfony\\Component\\Routing\\RequestContext`. But if you use the
:doc:`HttpFoundation </components/http_foundation>` component, you can use its
:class:`Symfony\\Component\\HttpFoundation\\Request` class to feed the
:class:`Symfony\\Component\\Routing\\RequestContext` in a shortcut::

    use Symfony\Component\HttpFoundation\Request;

    $context = new RequestContext();
    $context->fromRequest(Request::createFromGlobals());

Generate a URL
~~~~~~~~~~~~~~

While the :class:`Symfony\\Component\\Routing\\Matcher\\UrlMatcher` tries
to find a route that fits the given request you can also build a URL from
a certain route with the :class:`Symfony\\Component\\Routing\\Generator\\UrlGenerator`::

    use Symfony\Component\Routing\Generator\UrlGenerator;
    use Symfony\Component\Routing\RequestContext;
    use Symfony\Component\Routing\Route;
    use Symfony\Component\Routing\RouteCollection;

    $routes = new RouteCollection();
    $routes->add('show_post', new Route('/show/{slug}'));

    $context = new RequestContext('/');

    $generator = new UrlGenerator($routes, $context);

    $url = $generator->generate('show_post', [
        'slug' => 'my-blog-post',
    ]);
    // /show/my-blog-post

.. note::

    If you have defined a scheme, an absolute URL is generated if the scheme
    of the current :class:`Symfony\\Component\\Routing\\RequestContext` does
    not match the requirement.

Check if a Route Exists
~~~~~~~~~~~~~~~~~~~~~~~

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
        $url = $generator->generate($dynamicRouteName, $parameters);
    } catch (RouteNotFoundException $e) {
        // the route is not defined...
    }

Load Routes from a File
~~~~~~~~~~~~~~~~~~~~~~~

You've already seen how you can add routes to a collection right inside
PHP. But you can also load routes from a number of different files.

The Routing component comes with a number of loader classes, each giving
you the ability to load a collection of route definitions from an external
file of some format.
Each loader expects a :class:`Symfony\\Component\\Config\\FileLocator` instance
as the constructor argument. You can use the :class:`Symfony\\Component\\Config\\FileLocator`
to define an array of paths in which the loader will look for the requested files.
If the file is found, the loader returns a :class:`Symfony\\Component\\Routing\\RouteCollection`.

If you're using the ``YamlFileLoader``, then route definitions look like this:

.. code-block:: yaml

    # routes.yaml
    route1:
        path:       /foo
        controller: MyController::fooAction
        methods:    GET|HEAD

    route2:
        path:       /foo/bar
        controller: FooBarInvokableController
        methods:    PUT

To load this file, you can use the following code. This assumes that your
``routes.yaml`` file is in the same directory as the below code::

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\Routing\Loader\YamlFileLoader;

    // looks inside *this* directory
    $fileLocator = new FileLocator([__DIR__]);
    $loader = new YamlFileLoader($fileLocator);
    $routes = $loader->load('routes.yaml');

Besides :class:`Symfony\\Component\\Routing\\Loader\\YamlFileLoader` there are two
other loaders that work the same way:

* :class:`Symfony\\Component\\Routing\\Loader\\XmlFileLoader`
* :class:`Symfony\\Component\\Routing\\Loader\\PhpFileLoader`

If you use the :class:`Symfony\\Component\\Routing\\Loader\\PhpFileLoader` you
have to provide the name of a PHP file which returns a callable handling a :class:`Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator`.
This class allows to chain imports, collections or simple route definition calls::

    // RouteProvider.php
    namespace Symfony\Component\Routing\Loader\Configurator;

    return function (RoutingConfigurator $routes) {
        $routes->add('route_name', '/foo')
            ->controller('ExampleController')
            // ...
        ;
    };

Routes as Closures
..................

There is also the :class:`Symfony\\Component\\Routing\\Loader\\ClosureLoader`, which
calls a closure and uses the result as a :class:`Symfony\\Component\\Routing\\RouteCollection`::

    use Symfony\Component\Routing\Loader\ClosureLoader;

    $closure = function () {
        return new RouteCollection();
    };

    $loader = new ClosureLoader();
    $routes = $loader->load($closure);

Routes as Annotations
.....................

Last but not least there are
:class:`Symfony\\Component\\Routing\\Loader\\AnnotationDirectoryLoader` and
:class:`Symfony\\Component\\Routing\\Loader\\AnnotationFileLoader` to load
route definitions from class annotations. The specific details are left
out here.

.. include:: /_includes/_annotation_loader_tip.rst.inc

The all-in-one Router
~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Routing\\Router` class is an all-in-one package
to use the Routing component. The constructor expects a loader instance,
a path to the main route definition and some other settings::

    public function __construct(
        LoaderInterface $loader,
        $resource,
        array $options = [],
        RequestContext $context = null,
        LoggerInterface $logger = null
    );

With the ``cache_dir`` option you can enable route caching (if you provide a
path) or disable caching (if it's set to ``null``). The caching is done
automatically in the background if you want to use it. A basic example of the
:class:`Symfony\\Component\\Routing\\Router` class would look like::

    $fileLocator = new FileLocator([__DIR__]);
    $requestContext = new RequestContext('/');

    $router = new Router(
        new YamlFileLoader($fileLocator),
        'routes.yaml',
        ['cache_dir' => __DIR__.'/cache'],
        $requestContext
    );
    $parameters = $router->match('/foo/bar');
    $url = $router->generate('some_route', ['parameter' => 'value']);

.. note::

    If you use caching, the Routing component will compile new classes which
    are saved in the ``cache_dir``. This means your script must have write
    permissions for that location.

.. _routing-unicode-support:

Unicode Routing Support
~~~~~~~~~~~~~~~~~~~~~~~

The Routing component supports UTF-8 characters in route paths and requirements.
Thanks to the ``utf8`` route option, you can make Symfony match and generate
routes with UTF-8 characters:

.. configuration-block::

    .. code-block:: php-annotations

        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class DefaultController extends AbstractController
        {
            /**
             * @Route("/category/{name}", name="route1", options={"utf8": true})
             */
            public function category()
            {
                // ...
            }

    .. code-block:: yaml

        route1:
            path:     /category/{name}
            controller: App\Controller\DefaultController::category
            options:
                utf8: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="route1" path="/category/{name}" controller="App\Controller\DefaultController::category">
                <option key="utf8">true</option>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\DefaultController;

        return function (RoutingConfigurator $routes) {
            $routes->add('route1', '/category/{name}')
                ->controller([DefaultController::class, 'category'])
                ->options([
                    'utf8' => true,
                ])
            ;
        };

In this route, the ``utf8`` option set to ``true`` makes Symfony consider the
``.`` requirement to match any UTF-8 characters instead of just a single
byte character. This means that so the following URLs would match:
``/category/日本語``, ``/category/فارسی``, ``/category/한국어``, etc. In case you
are wondering, this option also allows to include and match emojis in URLs.

You can also include UTF-8 strings as routing requirements:

.. configuration-block::

    .. code-block:: php-annotations

        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class DefaultController extends AbstractController
        {
            /**
             * @Route(
             *     "/category/{name}",
             *     name="route2",
             *     defaults={"name": "한국어"},
             *     options={"utf8": true}
             * )
             */
            public function category()
            {
                // ...
            }

    .. code-block:: yaml

        route2:
            path:     /category/{name}
            controller: 'App\Controller\DefaultController::category'
            defaults:
                name: "한국어"
            options:
                utf8: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="route2" path="/category/{name}" controller="App\Controller\DefaultController::category">
                <default key="name">한국어</default>
                <option key="utf8">true</option>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\DefaultController;

        return function (RoutingConfigurator $routes) {
            $routes->add('route2', '/category/{name}')
                ->controller([DefaultController::class, 'category'])
                ->defaults([
                    'name' => '한국어',
                ])
                ->options([
                    'utf8' => true,
                ])
            ;
        };

.. tip::

    In addition to UTF-8 characters, the Routing component also supports all
    the `PCRE Unicode properties`_, which are escape sequences that match
    generic character types. For example, ``\p{Lu}`` matches any uppercase
    character in any language, ``\p{Greek}`` matches any Greek character,
    ``\P{Han}`` matches any character not included in the Chinese Han script.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /routing
    /routing/*
    /controller
    /controller/*

.. _Packagist: https://packagist.org/packages/symfony/routing
.. _PCRE Unicode properties: http://php.net/manual/en/regexp.reference.unicode.php
