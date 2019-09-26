.. index::
   single: Routing
   single: Components; Routing

The Routing Component
=====================

    The Routing component maps an HTTP request to a set of configuration
    variables. It's used to build routing systems for web applications where
    each URL is associated with some code to execute.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/routing

.. include:: /components/require_autoload.rst.inc

Usage
-----

The main :doc:`Symfony routing </routing>` article explains all the features of
this component when used inside a Symfony application. This article only
explains the things you need to do to use it in a non-Symfony PHP application.

Routing System Setup
--------------------

A routing system has three parts:

* A :class:`Symfony\\Component\\Routing\\RouteCollection`, which contains the
  route definitions (instances of the class :class:`Symfony\\Component\\Routing\\Route`);
* A :class:`Symfony\\Component\\Routing\\RequestContext`, which has information
  about the request;
* A :class:`Symfony\\Component\\Routing\\Matcher\\UrlMatcher`, which performs
  the mapping of the path to a single route.

Here is a quick example::

    use App\Controller\BlogController;
    use Symfony\Component\Routing\Generator\UrlGenerator;
    use Symfony\Component\Routing\Matcher\UrlMatcher;
    use Symfony\Component\Routing\RequestContext;
    use Symfony\Component\Routing\Route;
    use Symfony\Component\Routing\RouteCollection;

    $route = new Route('/blog/{slug}', ['_controller' => BlogController::class]);
    $routes = new RouteCollection();
    $routes->add('blog_show', $route);

    $context = new RequestContext('/');

    // Routing can match routes with incoming requests
    $matcher = new UrlMatcher($routes, $context);
    $parameters = $matcher->match('/blog/lorem-ipsum');
    // $parameters = [
    //     '_controller' => 'App\Controller\BlogController',
    //     'slug' => 'lorem-ipsum',
    //     '_route' => 'blog_show'
    // ]

    // Routing can also generate URLs for a given route
    $generator = new UrlGenerator($routes, $context);
    $url = $generator->generate('blog_show', [
        'slug' => 'my-blog-post',
    ]);
    // $url = '/blog/my-blog-post'

The :method:`RouteCollection::add() <Symfony\\Component\\Routing\\RouteCollection::add>`
method takes two arguments. The first is the name of the route. The second
is a :class:`Symfony\\Component\\Routing\\Route` object, which expects a
URL path and some array of custom variables in its constructor. This array
of custom variables can be *anything* that's significant to your application,
and is returned when that route is matched.

The :method:`UrlMatcher::match() <Symfony\\Component\\Routing\\Matcher\\UrlMatcher::match>`
returns the variables you set on the route as well as the route parameters.
Your application can now use this information to continue processing the request.
In addition to the configured variables, a ``_route`` key is added, which holds
the name of the matched route.

If no matching route can be found, a
:class:`Symfony\\Component\\Routing\\Exception\\ResourceNotFoundException` will
be thrown.

Defining Routes
---------------

A full route definition can contain up to eight parts::

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

Route Collections
-----------------

You can add routes or other instances of
:class:`Symfony\\Component\\Routing\\RouteCollection` to *another* collection.
This way you can build a tree of routes. Additionally you can define common
options for all routes of a subtree using methods provided by the
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

Setting the Request Parameters
------------------------------

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

Loading Routes
--------------

The Routing component comes with a number of loader classes, each giving you the
ability to load a collection of route definitions from external resources.

File Routing Loaders
~~~~~~~~~~~~~~~~~~~~

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
have to provide the name of a PHP file which returns a callable handling a
:class:`Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator`.
This class allows to chain imports, collections or simple route definition calls::

    // RouteProvider.php
    use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

    return function (RoutingConfigurator $routes) {
        $routes->add('route_name', '/foo')
            ->controller('ExampleController')
            // ...
        ;
    };

Closure Routing Loaders
~~~~~~~~~~~~~~~~~~~~~~~

There is also the :class:`Symfony\\Component\\Routing\\Loader\\ClosureLoader`, which
calls a closure and uses the result as a :class:`Symfony\\Component\\Routing\\RouteCollection`::

    use Symfony\Component\Routing\Loader\ClosureLoader;

    $closure = function () {
        return new RouteCollection();
    };

    $loader = new ClosureLoader();
    $routes = $loader->load($closure);

Annotation Routing Loaders
~~~~~~~~~~~~~~~~~~~~~~~~~~

Last but not least there are
:class:`Symfony\\Component\\Routing\\Loader\\AnnotationDirectoryLoader` and
:class:`Symfony\\Component\\Routing\\Loader\\AnnotationFileLoader` to load
route definitions from class annotations::

    use Doctrine\Common\Annotations\AnnotationReader;
    use Symfony\Bundle\FrameworkBundle\Routing\AnnotatedRouteControllerLoader;
    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;

    $loader = new AnnotationDirectoryLoader(
        new FileLocator(__DIR__.'/app/controllers/'),
        new AnnotatedRouteControllerLoader(
            new AnnotationReader()
        )
    );

    $routes = $loader->load(__DIR__.'/app/controllers/');
    // ...

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

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /routing
    /routing/*
    /controller
    /controller/*
