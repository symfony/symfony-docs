.. index::
   single: Routing

The Routing Component
====================

   The Routing Component maps a HTTP request to a set of configuration 
   variables.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Routing.git);
* Install it via PEAR (`pear.symfony.com/Routing`);
* Install it via Composer (`symfony/routing` on Packagist)

Usage
-----

In order to set up a basic routing system you need three parts:

* A `:class:Symfony\\Component\\Routing\\RouteCollection`, which contains the route definitions (instances of the class `:class:Symfony\\Component\\Routing\\Route`)
* A `:class:Symfony\\Component\\Routing\\RequestContext`, which has information about the request
* A `:class:Symfony\\Component\\Routing\\Matcher\\UrlMatcher`, which performs the mapping of the request to a single route

.. code-block:: php
    
    use Symfony\Component\Routing\Matcher\UrlMatcher;
    use Symfony\Component\Routing\RequestContext;
    use Symfony\Component\Routing\RouteCollection;
    use Symfony\Component\Routing\Route;

    $routes = new RouteCollection();
    $routes->add('route_name', new Route('/foo', array('controller' => 'MyController')));

    $context = new RequestContext();

    $matcher = new UrlMatcher($routes, $context);

    $parameters = $matcher->match( '/foo' ); 
    // array('controller' => 'MyController', '_route' => 'route_name')

You can add as many routes as you like to a 
`:class:Symfony\\Component\\Routing\\RouteCollection`. The first argument you have to
provide to the `:method:Symfony\\Component\\Routing\\RouteCollection::add` method is 
the name of the Route. The constructor of the `:class:Symfony\\Component\\Routing\\Route` 
class expects an url path and an array of custom variables. These are returned by the 
Matcher if the route matches the request.

If no matching route can be found a 
`:class:Symfony\\Component\\Routing\\Exception\\ResourceNotFoundException` will be thrown.

Additionally to the custom variables the name of the route is added with the 
key "_route".

Defining routes
~~~~~~~~

A route contains:

* The path the of route, which shall be recognized by the matcher. You can use ``{placeholders}`` to match dynamic parts in the url.
* The default values. These contain the values that will be returned when the request matches the route.
* The requirements. These define constraints for the values of the placeholders as regular expressions.
* The options. These contain internal settings for the route

.. code-block:: php
    
   $route = new Route(
       '/archive/{month}', // path
       array('controller' => 'showArchive'), // default values
       array('month' => '[0-9]{4}-[0-9]{2}'), // requirements
       array() // options
   );
   
   // ...
   
   $parameters = $matcher->match('/archive/2012-01');
   // array('controller' => 'showArchive', 'month' => 2012-01'', '_route' => '...')
   
   $parameters = $matcher->match( '/archive/foo' );
   // throws ResourceNotFoundException

Besides the regular expression constraints there are two special requirements 
you can define:

* ``_method`` enforces a certain HTTP request method (``HEAD``, ``GET``, ``POST``, ...)
* ``_scheme`` enforces a certian HTTP scheme (``http``, ``https``) 

.. code-block:: php
    
   // Only accepts requests to /foo with the POST method and a secure connection.
   $route = new Route('/foo', array('_method' => 'post', '_scheme' => 'https' ));

Using prefixes
~~~~~~~~

You can add routes or other instances of 
`:class:Symfony\\Component\\Routing\\RouteCollection` to a collection. This way 
you can build a tree of routes. Additionally you can define a prefix, default 
requirements and default options to all routes of a subtree:

.. code-block:: php
    
    $rootCollection = new RouteCollection();
    
    $subCollection = new RouteCollection();
    $subCollection->add( /*...*/ );
    $subCollection->add( /*...*/ );
    
    $rootCollection->addCollection($subCollection, '/prefix', array('_scheme' => 'https'));

Set the request parameters
~~~~~~~~

The `:class:Symfony\\Component\\Routing\\RequestContext` provides information 
about a request. You can define all parameters of a HTTP request with this class:

.. code-block:: php
    
    public function __construct($baseUrl = '', $method = 'GET', $host = 'localhost', $scheme = 'http', $httpPort = 80, $httpsPort = 443)

Normally you can pass the values from the ``$_SERVER`` variable variable to the 
`:class:Symfony\\Component\\Routing\\RequestContext`. If you use Symfony you can 
use it's `:class:Symfony\\Component\\HttpFoundation\\Request` object to feed the 
`:class:Symfony\\Component\\Routing\\RequestContext` in a shortcut:
  
.. code-block:: php
    
    use Symfony\Component\HttpFoundation\Request;
    
    $context = new RequestContext();
    $context->fromRequest(Request::createFromGlobals());

Generate a URL
~~~~~~~~

While the `:class:Symfony\\Component\\Routing\\Matcher\\UrlMatcher` tries to find 
a route that fits the given request you can also build an URL from a certain route:

.. code-block:: php
    
    use Symfony\Component\Routing\Generator\UrlGenerator;

    $routes = new RouteCollection();
    $routes->add('show_post', new Route('/show/{slug}'));

    $context = new RequestContext();

    $generator = new UrlGenerator($routes, $context);

    $url = $generator->generate('show_post', 'My_Blog_Post');
    // /show/My_Blog_Post

.. note::
    
    An absolute URL is generated if you have defined a ``_scheme`` requirement for 
    the matched route.

Load routes from a file
~~~~~~~~

There is a number of loader classes. They give you the abbility to load a collection
of route definitions from external files. There are:

* `:class:Symfony\\Component\\Routing\\Loader\\XmlFileLoader`
* `:class:Symfony\\Component\\Routing\\Loader\\YamlFileLoader`
* `:class:Symfony\\Component\\Routing\\Loader\\PhpFileLoader`
* and others

Here is an example with `:class:Symfony\\Component\\Routing\\Loader\\YamlFileLoader`:

.. code-block:: yaml

    # routes.yml
    route1:
        pattern: /foo
        defaults: { controller: 'MyController::fooAction' }

    route2:
        pattern: /foo/bar
        defaults: { controller: 'MyController::foobarAction' }

.. code-block:: php
    
    $loader = new YamlFileLoader();
    $collection = $loader->load('routes.yml');

The all-in-one Router
~~~~~~~~

The `:class:Symfony\\Component\\Routing\\Router` class is a all-in-one package of 
the routing algorithm. The constructor expects a loader instance, a path to the 
main route definition and some other settings:

.. code-block:: php
    
    public function __construct(LoaderInterface $loader, $resource, array $options = array(), RequestContext $context = null, array $defaults = array());

With the ``cache_dir`` option you can enable route caching (if you provide a 
path) or disable caching (if it's set to ``null``). The caching is done 
automatically in the background if you want to use it. A basic example of the 
`:class:Symfony\\Component\\Routing\\Router` class would look like:

.. code-block:: php
    
    $router = new Router(new YamlFileLoader(), "routes.yml");
    $router->match('/foo/bar');

.. note::
    
    If you use caching the Routing component will compile new classes which 
    are saved in the ``cache_dir``. This means your script must have write 
    permissions for that location.