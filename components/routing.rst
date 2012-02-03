.. index::
   single: Routing
   single: Components; Routing

The Routing Component
=====================

   The Routing Component maps an HTTP request to a set of configuration 
   variables.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Routing);
* Install it via PEAR (`pear.symfony.com/Routing`);
* Install it via Composer (`symfony/routing` on Packagist)

Usage
-----

In order to set up a basic routing system you need three parts:

* A :class:`Symfony\\Component\\Routing\\RouteCollection`, which contains the route definitions (instances of the class :class:`Symfony\\Component\\Routing\\Route`)
* A :class:`Symfony\\Component\\Routing\\RequestContext`, which has information about the request
* A :class:`Symfony\\Component\\Routing\\Matcher\\UrlMatcher`, which performs the mapping of the request to a single route

Let's see a quick example. Notice that this assumes that you've already configured
your autoloader to load the Routing component::

    use Symfony\Component\Routing\Matcher\UrlMatcher;
    use Symfony\Component\Routing\RequestContext;
    use Symfony\Component\Routing\RouteCollection;
    use Symfony\Component\Routing\Route;

    $routes = new RouteCollection();
    $routes->add('route_name', new Route('/foo', array('controller' => 'MyController')));

    $context = new RequestContext($_SERVER['REQUEST_URI']);

    $matcher = new UrlMatcher($routes, $context);

    $parameters = $matcher->match( '/foo' ); 
    // array('controller' => 'MyController', '_route' => 'route_name')

.. note::

    Be careful when using ``$_SERVER['REQUEST_URI']``, as it may include
    any query parameters on the URL, which will cause problems with route
    matching. An easy way to solve this is to use the HTTPFoundation component
    as explained :ref:`below<components-routing-http-foundation>`.

You can add as many routes as you like to a 
:class:`Symfony\\Component\\Routing\\RouteCollection`.

The :method:`RouteCollection::add()<Symfony\\Component\\Routing\\RouteCollection::add>`
method takes two arguments. The first is the name of the route, The second
is a :class:`Symfony\\Component\\Routing\\Route` object, which expects a
URL path and some array of custom variables in its constructor. This array
of custom variables can be *anything* that's significant to your application,
and is returned when that route is matched.

If no matching route can be found a 
:class:`Symfony\\Component\\Routing\\Exception\\ResourceNotFoundException` will be thrown.

In addition to your array of custom variables, a ``_route`` key is added,
which holds the name of the matched route.

Defining routes
~~~~~~~~~~~~~~~

A full route definition can contain up to four parts:

1. The URL pattern route. This is matched against the URL passed to the `RequestContext`,
and can contain named wildcard placeholders (e.g. ``{placeholders}``)
to match dynamic parts in the URL.

2. An array of default values. This contains an array of arbitrary values
that will be returned when the request matches the route.

3. An array of requirements. These define constraints for the values of the
placeholders as regular expressions.

4. An array of options. These contain internal settings for the route and
are the least commonly needed.

Take the following route, which combines several of these ideas::

   $route = new Route(
       '/archive/{month}', // path
       array('controller' => 'showArchive'), // default values
       array('month' => '[0-9]{4}-[0-9]{2}'), // requirements
       array() // options
   );

   // ...

   $parameters = $matcher->match('/archive/2012-01');
   // array('controller' => 'showArchive', 'month' => 2012-01'', '_route' => '...')

   $parameters = $matcher->match('/archive/foo');
   // throws ResourceNotFoundException

In this case, the route is matched by ``/archive/2012/01``, because the ``{month}``
wildcard matches the regular expression wildcard given. However, ``/archive/foo``
does *not* match, because "foo" fails the month wildcard.

Besides the regular expression constraints there are two special requirements 
you can define:

* ``_method`` enforces a certain HTTP request method (``HEAD``, ``GET``, ``POST``, ...)
* ``_scheme`` enforces a certain HTTP scheme (``http``, ``https``) 

For example, the following route would only accept requests to /foo with
the POST method and a secure connection::

   $route = new Route('/foo', array('_method' => 'post', '_scheme' => 'https' ));

.. tip::
    
    If you want to match all urls which start with a certain path and end in an
    arbitrary suffix you can use the following route definition::
        
        $route = new Route('/start/{suffix}', array('suffix' => ''), array('suffix' => '.*'));
    

Using Prefixes
~~~~~~~~~~~~~~

You can add routes or other instances of 
:class:`Symfony\\Component\\Routing\\RouteCollection` to *another* collection.
This way you can build a tree of routes. Additionally you can define a prefix,
default requirements and default options to all routes of a subtree::

    $rootCollection = new RouteCollection();

    $subCollection = new RouteCollection();
    $subCollection->add( /*...*/ );
    $subCollection->add( /*...*/ );

    $rootCollection->addCollection($subCollection, '/prefix', array('_scheme' => 'https'));

Set the Request Parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Routing\\RequestContext` provides information 
about the current request. You can define all parameters of an HTTP request
with this class via its constructor::

    public function __construct($baseUrl = '', $method = 'GET', $host = 'localhost', $scheme = 'http', $httpPort = 80, $httpsPort = 443)

.. _components-routing-http-foundation:

Normally you can pass the values from the ``$_SERVER`` variable to populate the 
:class:`Symfony\\Component\\Routing\\RequestContext`. But If you use the
:doc:`HttpFoundation<http_foundation>` component, you can use its 
:class:`Symfony\\Component\\HttpFoundation\\Request` class to feed the 
:class:`Symfony\\Component\\Routing\\RequestContext` in a shortcut::

    use Symfony\Component\HttpFoundation\Request;

    $context = new RequestContext();
    $context->fromRequest(Request::createFromGlobals());

Generate a URL
~~~~~~~~~~~~~~

While the :class:`Symfony\\Component\\Routing\\Matcher\\UrlMatcher` tries
to find a route that fits the given request you can also build a URL from
a certain route::

    use Symfony\Component\Routing\Generator\UrlGenerator;

    $routes = new RouteCollection();
    $routes->add('show_post', new Route('/show/{slug}'));

    $context = new RequestContext($_SERVER['REQUEST_URI']);

    $generator = new UrlGenerator($routes, $context);

    $url = $generator->generate('show_post', array(
        'slug' => 'my-blog-post'
    ));
    // /show/my-blog-post

.. note::

    If you have defined the ``_scheme`` requirement, an absolute URL is generated
    if the scheme of the current :class:`Symfony\\Component\\Routing\\RequestContext`
    does not match the requirement.

Load Routes from a File
~~~~~~~~~~~~~~~~~~~~~~~

You've already seen how you can easily add routes to a collection right inside
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

    # routes.yml
    route1:
        pattern: /foo
        defaults: { controller: 'MyController::fooAction' }

    route2:
        pattern: /foo/bar
        defaults: { controller: 'MyController::foobarAction' }

To load this file, you can use the following code. This assumes that your
``routes.yml`` file is in the same directory as the below code::

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\Routing\Loader\YamlFileLoader;

    // look inside *this* directory
    $locator = new FileLocator(array(__DIR__));
    $loader = new YamlFileLoader($locator);
    $collection = $loader->load('routes.yml');

Besides :class:`Symfony\\Component\\Routing\\Loader\\YamlFileLoader` there are two
other loaders that work the same way:

* :class:`Symfony\\Component\\Routing\\Loader\\XmlFileLoader`
* :class:`Symfony\\Component\\Routing\\Loader\\PhpFileLoader`

If you use the :class:`Symfony\\Component\\Routing\\Loader\\PhpFileLoader` you
have to provide the name of a php file which returns a :class:`Symfony\\Component\\Routing\\RouteCollection`::

    // RouteProvider.php
    use Symfony\Component\Routing\RouteCollection;
    use Symfony\Component\Routing\Route;

    $collection = new RouteCollection();
    $collection->add('route_name', new Route('/foo', array('controller' => 'ExampleController')));
    // ...

    return $collection;

Routes as Closures
..................

There is also the :class:`Symfony\\Component\\Routing\\Loader\\ClosureLoader`, which 
calls a closure and uses the result as a :class:`Symfony\\Component\\Routing\\RouteCollection`::

    use Symfony\Component\Routing\Loader\ClosureLoader;

    $closure = function() {
        return new RouteCollection();
    };

    $loader = new ClosureLoader();
    $collection = $loader->load($closure);

Routes as Annotations
.....................

Last but not least there are
:class:`Symfony\\Component\\Routing\\Loader\\AnnotationDirectoryLoader` and
:class:`Symfony\\Component\\Routing\\Loader\\AnnotationFileLoader` to load
route definitions from class annotations. The specific details are left
out here.

The all-in-one Router
~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Routing\\Router` class is a all-in-one package
to quickly use the Routing component. The constructor expects a loader instance,
a path to the main route definition and some other settings::

    public function __construct(LoaderInterface $loader, $resource, array $options = array(), RequestContext $context = null, array $defaults = array());

With the ``cache_dir`` option you can enable route caching (if you provide a 
path) or disable caching (if it's set to ``null``). The caching is done 
automatically in the background if you want to use it. A basic example of the 
:class:`Symfony\\Component\\Routing\\Router` class would look like::

    $locator = new FileLocator(array(__DIR__));
    $requestContext = new RequestContext($_SERVER['REQUEST_URI']);

    $router = new Router(
        new YamlFileLoader($locator),
        "routes.yml",
        array('cache_dir' => __DIR__.'/cache'),
        $requestContext,
    );
    $router->match('/foo/bar');

.. note::

    If you use caching, the Routing component will compile new classes which 
    are saved in the ``cache_dir``. This means your script must have write 
    permissions for that location.
