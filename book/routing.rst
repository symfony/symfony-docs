.. index::
   single: Routing

Routing
=======

Having beautiful and flexible URLs is an absolute must for all high-quality
web applications. This means leaving behind ugly URLs such as ``index.php?article_id=57``
in favor of something like ``/read/intro-to-symfony``. More importantly,
developers should be able to refactor their application without changing
the outward-facing URLs. In other words, the URLs shown to the world
should be decoupled and free from the logic of your application. Symfony's
``Router`` makes clean, flexible and decoupled URLs possible and easy.

.. index::
   single: Routing; Basics

Routing Basics
--------------

When an HTTP request is made to your application, it contains directions
to the exact "resource" that the client is requesting. The "address" to
the resource is the URI (uniform resource identifier). Take the following
HTTP request as our example:

.. code-block:: text

    GET /blog/my-blog-post

Internally, Symfony needs to parse the URI and determine the correct controller
that should be executed to return that resource. This is exactly what Symfony's
router does:

#. The HTTP request enters the Symfony2 application;

#. The Symfony2 kernel asks the ``Router`` to inspect the request;

#. The router matches the URI of the request to a specific route and returns
   information about that route, including the controller to be executed;

#. The Symfony2 kernel executes the controller, which ultimately returns
   a ``Response`` object.

.. code-block:: text

    Request -> Kernel::handle() -> Controller -> Response
                        |    ^
                        | controller
                        |    |
                        v    |
                        Routing

Mapping "Path Info" (URI) to a Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony's ``Router`` has one simple goal: to map the path info of a request
(a sanitized version of the URI) to a controller. It does this by interpreting
a routing map built by the developer. For example, the URI ``/blog/my-blog-post``
would be matched by the following route:

.. code-block:: yaml

    blog_show:
        pattern:   /blog/{slug}
        defaults:  { _controller: MyBlogBundle:Blog:show }

.. tip::
    A "slug" is just a name for the url-friendly version of a string. For example,
    a page titled "All About Symfony2" might have a slug of "all-about-symfony2".

The pattern defined looks like ``/blog/*`` where the single wildcard is
given the name ``slug``. In our example, the ``slug`` parameter maps to a value
of ``my-blog-post``. Later, we'll show you how you can use multiple wildcards,
optional wildcards, and make your wildcards more powerful by matching only
on a given regular expression.

Overview of Symfony Routing
~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a Symfony application handles a request for the URI ``/blog/my-blog-post``,
the following events take place:

#. Symfony asks the ``Router`` for to match a route for the url ``/blog/my-blog-post``.

#. The ``Router`` attempts to find a matching route by matching
   ``/blog/my-blog-post`` against each route's pattern until one is found. In our
   example, the ``blog_show`` route is matched.

#. Symfony parses the ``_controller`` parameter from the route. This follows a
   convention that translates the string into a specific PHP callable (See
   `Routes and Controllers`_).

#. Symfony executes the controller, passing in the correct arguments.

.. index::
   single: Routing; Creating

Creating Routes
---------------

The ``Router`` is seeded by a single routing configuration resource (typically
a file) that defines all the routes of your application. This main resource
is defined in your application's main configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            router:        { resource: "%kernel.root_dir%/config/routing.yml" }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config ...>
            <!-- ... -->
            <framework:router resource="%kernel.root_dir%/config/routing.xml" />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'router'        => array('resource' => '%kernel.root_dir%/config/routing.php'),
        ));

As you can see, the default routing configuration resource is simply a file
that lives in the ``app/config`` directory of your project. In the next section,
we'll start creating and customizing routes inside this file.

.. tip::

    Even though all routes are seeded by a single file, it's common practice
    to include additional routing resources. See the
    :ref:`Including External Routing Resources <routing-include-external-resources>`
    section for more information.

Basic Routing Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Defining a route is simple and flexible. A basic route consists of just two
parts: the ``pattern`` to match and the ``defaults``:

.. code-block:: yaml

    homepage:
        pattern:   /
        defaults:  { _controller: MyBundle:Main:homepage }

This route matches the homepage (``/``) and specifies a ``_controller``
default of ``MyBundle:Main:homepage``. The ``_controller`` string is translated
by Symfony into an actual PHP callable and executed. That part of the routing
process will be explained in the `Routes and Controllers`_ section.

.. index::
   single: Routing; Placeholders

Routing with Placeholders
~~~~~~~~~~~~~~~~~~~~~~~~~

Of course the routing system supports much more interesting routes. Many
routes will contain one or more named "wildcards" placeholders:

.. code-block:: yaml

    blog_show:
        pattern:   /blog/{slug}
        defaults:  { _controller: MyBlogBundle:Blog:show }

The pattern being matched looks like ``/blog/*``, where the portion coming
after ``/blog/`` is mapped to a parameter ``slug``. As we'll find out later,
the ``slug`` parameter will eventually be available in your controller.

The pattern ``/blog/{slug}`` will match ``/blog/my-blog-post``, but will *not*
match simply ``/blog``. That's because, by default, all placeholders are
required. This can be changed by adding a placeholder value to the ``defaults``
routing key.

Required and Optional Placeholders
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Let's consider a new route that will simply display a list of all the
available blog posts in some imaginary blog application:

.. code-block:: yaml

    blog:
        pattern:   /blog
        defaults:  { _controller: MyBlogBundle:Blog:index }

At this point, this route should be easy for us - it contains no placeholders
and will only match the exact url ``/blog``. Suppose now that this page needs
to support pagination:

.. code-block:: yaml

    blog:
        pattern:   /blog/{page}
        defaults:  { _controller: MyBlogBundle:Blog:index }

Like the ``:slug`` placeholder in the previous example, the value matching
``{page}`` will be available in our controller so that we can determine which
set of blog posts to display.

Unfortunately, as we mentioned before, wildcards are required by default.
In other words, the above route will no longer match ``/blog`` - the url
for page one must be ``/blog/1``! Since that's no way for a rich web application
to behave, let's make it so that the url ``/blog`` matches the ``blog`` route
and make the ``{page}`` placeholder default to a value of ``1``:

.. code-block:: yaml

    blog:
        pattern:   /blog/{page}
        defaults:  { _controller: MyBlogBundle:Blog:index, page: 1 }

By adding ``page`` to the ``defaults`` key, the ``:page`` placeholder is no
longer required. The url ``/blog`` will match and the value of the ``page``
parameter will be ``1``. The url ``/blog/2`` will also match, giving the
``page`` parameter a value of ``2``.

.. index::
   single: Routing; Requirements

Adding Requirements
~~~~~~~~~~~~~~~~~~~

Let's take a look at the routes that we've created so far. As you'll see,
we've introduced a major problem:

.. code-block:: yaml

    blog:
        pattern:   /blog/{page}
        defaults:  { _controller: MyBlogBundle:Blog:index, page: 1 }

    blog_show:
        pattern:   /blog/{slug}
        defaults:  { _controller: MyBlogBundle:Blog:show }

Notice that both routes have a pattern that looks like ``/blog/*``. The
Symfony ``Router`` will always return the *first* route that's matched. In
other words, the ``blog_show`` route will *never* be matched. Instead, URLs
like ``/blog/my-blog-post`` will match the first route (``blog``) and pass a
value of ``my-blog-post`` as the ``page`` argument in the ``indexAction``
controller.

The answer to the problem is routing *requirements*. Our routing setup would
work perfectly if the ``/blog/{page}`` pattern *only* matched URLs where the
``:page`` portion were an integer. Fortunately, regular expression requirements
can easily be added for each parameter. For example:

.. code-block:: yaml

    blog:
        pattern:   /blog/{page}
        defaults:  { _controller: MyBlogBundle:Blog:index, page: 1 }
        requirements:
            page:  \d+

The ``blog`` route will still match URLs such as ``/blog/2``, but it will
no longer match routes like ``/blog/my-blog-post``. Instead, that url will
be allowed to properly match the ``blog_show`` route.

.. note::
    Keep in mind that the order of the routes is very important. If the ``blog_show``
    route were placed above the ``blog`` route, the ``/blog/2`` url would
    would match ``blog_show`` instead of ``blog`` since the ``:slug`` parameter
    of ``blog_show`` has no requirements. By using proper ordering and clever
    requirements, you can create a rich routing schema.

Since the parameter requirements are regular expressions, the complexity
and flexibility of each requirement is entirely up to you. Suppose that
the homepage of your application is available in two different languages,
based on the url::

    homepage:
        pattern:   /{culture}
        defaults:  { _controller: MyBundle:Main:homepage, culture: en }
        requirements:
            culture:  en|fr

When matching against this route, the ``:culture`` portion of the url is matched
against the regular expression ``(en|fr)``. The following URLs would match::

    /       (culture = en)
    /en     (culture = en)
    /fr     (culture = fr)

.. index::
   single: Routing; Method requirement

Method Routing
~~~~~~~~~~~~~~

In addition to the url, you can also match on the *method* of the incoming
request (i.e. GET, HEAD, POST, PUT, DELETE). Suppose we have a contact form
with two controllers - one for displaying the form (on a GET request) and one
for processing the form when it's submitted (on a POST request). We can
accomplish this with the following routing configuration:

.. configuration-block::

    .. code-block:: yaml

        contact:
            pattern:  /contact
            defaults: { _controller: MyBundle:Main:contact }
            requirements:
                _method:  GET

        contact_process:
            pattern:  /contact
            defaults: { _controller: MyBundle:Main:contactProcess }
            requirements:
                _method:  POST

Despite the fact that these two routes have identical patterns (``/contact``),
the first route will be matched only on GET requests while the second route
will be matched only on POST requests. This means that you can display the
form and submit the form via the same url but using distinct controllers
for the two actions.

.. note::
    If no ``_method`` requirement is specified, the route will match on
    *all* methods.

.. tip::

    Like all other requirements, the ``_method`` requirement is parsed as
    a regular expression. This means that to restrict a route to only ``GET``
    or ``POST`` requests, use ``GET|POST``.

.. index::
   single: Routing; Advanced example

Advanced Routing Example
~~~~~~~~~~~~~~~~~~~~~~~~

At this point, you've got everything you need to create a powerful routing
schema in Symfony. The following is an example of just how flexible the
routing system can be:

.. configuration-block::

    .. code-block:: yaml

        article_show:
          pattern:  /articles/{culture}/{year}/{title}.{_format}
          defaults  { _controller: MyBundle:Article:show, _format: html }
          requirements:
              culture:  en|fr
              _format:  html|rss
              year:     \d+

As we've seen, this route will only match if the ``{culture}`` portion of
the url is either ``en`` or ``fr`` and if the ``{year}`` is a number.

This example also highlights the special ``_format`` routing parameter.
When using this parameter, the matched value becomes the "request format"
of the ``Request`` object. Ultimately, the request format is used for such
things such as setting the ``Content-Type`` of the response (e.g. a ``json``
request format translates into a ``Content-Type`` of ``application/json``)
and determining the filename of a template to render. The ``_format`` parameter
is a very powerful way to render the same content in different formats.

.. note::

    You may have also noticed that a period (.) is used between the ``{title}``
    and ``{_format}`` parameters. This is because, by default, Symfony is configured
    to allow both a forward slash (/) or a period (.) to be a valid "separator"
    between the routing parameters.

.. _routing-include-external-resources:

.. index::
   single: Routing; Importing routing resources

Including External Routing Resources
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As we know, the routing configuration is seeded by a single resource (usually
a file) that's defined in your application's main configuration file (see
`Creating Routes`_ above). Commonly, however, we may want to include routing
configuration from other places, such as from a bundle. This can be easily done:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            resource: "@SensioHelloBundle/Resources/config/routing.yml"

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://www.symfony-project.org/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">

            <import resource="@SensioHelloBundle/Resources/config/routing.xml" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->addCollection($loader->import("@SensioHelloBundle/Resources/config/routing.php"));

        return $collection;


The ``resource`` key loads the routing resource from the ``SensioHelloBundle``:

.. configuration-block::

    .. code-block:: yaml

        # src/Sensio/HelloBundle/Resources/config/routing.yml
        hello:
            pattern:  /hello/{name}
            defaults: { _controller: SensioHelloBundle:Hello:index }

    .. code-block:: xml

        <!-- src/Sensio/HelloBundle/Resources/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://www.symfony-project.org/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">

            <route id="hello" pattern="/hello/{name}">
                <default key="_controller">SensioHelloBundle:Hello:index</default>
            </route>
        </routes>

    .. code-block:: php

        // src/Sensio/HelloBundle/Resources/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('hello', new Route('/hello/{name}', array(
            '_controller' => 'SensioHelloBundle:Hello:index',
        )));

        return $collection;

The routes from the external resource are parsed and loaded in the same way
as the main routing resource. You can also choose to provide a "prefix" option.
For example, suppose that we want the "hello" route to have a pattern of
``/admin/hello/{name}`` instead of simply ``/hello/{name}``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            resource: "@SensioHelloBundle/Resources/config/routing.yml"
            prefix:   /admin

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://www.symfony-project.org/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">

            <import resource="@SensioHelloBundle/Resources/config/routing.xml" prefix="/admin" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->addCollection($loader->import("@SensioHelloBundle/Resources/config/routing.php"), '/admin');

        return $collection;

The string ``/admin`` will be prepended to the pattern of each route loaded
from the new routing resource.

.. index::
   single: Routing; Debugging

Visualizing & Debugging Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

While adding and customizing routes, it's helpful to be able to visualize
your routes and see if each is configured correctly. Any easy way to see
every route in your application is via the ``router:debug`` cli command. Initiate
the command by running the following from the root of your project.

.. code-block:: text

    ./app/console router:debug

The command should print a helpful list of all of your application's routes:

.. code-block:: text

    homepage              ANY       /
    contact               GET       /contact
    contact_process       POST      /contact
    article_show          ANY       /articles/{culture}/{year}/{title}.{_format}

You can also get very specific information on a single route by including
the route name after the command:

.. code-block:: text

    ./app/console router:debug article_show

.. index::
   single: Routing; Controllers
   single: Controller; String naming format

Routes and Controllers
----------------------

Now that you've mastered the creation of routes and learned how matching
takes place, the only missing piece is connecting each route to a controller.

.. controller-string-syntax:

The ``_controller`` Parameter
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Every route *must* contain a ``_controller`` parameter, which is a special
string syntax that Symfony translates into a PHP callable. There are two
different syntax for the ``_controller`` parameter:

The ``bundle:controller:action`` syntax
.......................................

This syntax is the one used in our example. Specifically, the ``_controller``
string ``MyBlogBundle:Blog:show`` means the following:

* Look for a controller class whose name is the concatenation of the second
  part of the ``_controller`` string (`` Blog``) and ``Controller`` (e.g. ``BlogController``).

* Look for the controller class in the ``Controller`` namespace of any bundle
  named ``MyBlogBundle`` (e.g. ``Sensio\MyBlogBundle\Controller\BlogController``
  or ``Bundle\VendorName\MyBlogBundle\Controller\BlogController``).

* Execute a method called ``showAction`` - a concatenation of the third
  portion of the ``_controller`` string (``show``) and ``Action``.

The basic ``class::method`` syntax
..................................

A less common but simple way to specify a controller is via the basic
``class::method`` syntax. This method could be used to call the example
controller via the string
``Sensio\MyBlogBundle\Controller\BlogController::showAction``, though
the ``showAction`` must now be a static method. This is not a recommended
syntax.

Route Parameters as Controller Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The route parameters (e.g. ``{slug}``} are especially important because
each is made available to the controller being executed as method arguments::

    public function showAction($slug)
    {
      // ...
    }

In reality, the ``defaults`` collection is merged with the parameter values
to form a single array. Each key of that array is available as an argument
on the controller. For a more detailed discussion, see :ref:`route-parameters-controller-arguments`.

.. index::
   single: Routing; Generating URLs

Generating URLs
---------------

The routing system also generates URLs. In fact, routing is really a bi-directional
system that maps a given path info (i.e. URL) to an array of routing parameters
and vice-versa. The ``Router::match()`` and ``Router::generate()`` methods
form this bi-directional system. Take the ``blog_show`` example route from
earlier::

    $params = $router->match('/blog/my-blog-post');
    // array('slug' => 'my-blog-post', '_controller' => 'MyBlogBundle:Blog:show')

    $uri = $router->generate('blog_show', array('slug' => 'my-blog-post'));
    // /blog/my-blog-post

To generate a URL, you need to specify the name of the route (e.g. ``blog_show``)
and any parameters/wildcards (e.g. ``slug=my-blog-post``) used in the pattern
for that route.

The key to generating a URL is to get access to the ``Router`` object. From
a traditional controller, this is easy::

    class MyController extends Controller
    {
        public function showAction($slug)
        {
          // ...

          $url = $this->get('router')->generate('blog_show', array('slug' => 'my-blog-post'));
        }
    }

.. index::
   single: Routing; Absolute URLs

Absolute URLs
~~~~~~~~~~~~~

By default, the ``Router`` will generate relative URLs (e.g. ``/blog``). In
certain cases, it makes sense to generate an absolute URL. To generate an
absolute URL, pass ``true`` to the third argument of ``Router::generate()``::

    $router->generate('blog_show', array('slug' => 'my-blog-post'), true);
    // http://www.example.com/blog/my-blog-post

.. note::

    The host of that's used when generating an absolute URL is the host of
    the current ``Request`` object.

.. index::
   single: Routing; URLs in a template

Generating URLs from a template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The most common place to generate a URL is from within a template when linking
between pages in your application:

.. configuration-block::

    .. code-block:: jinja

        <a href="{{ path('blog_show', { 'slug': 'my-blog-post' }) }}">
          Read this blog post.
        </a>

    .. code-block:: php

        <a href="<?php echo $view['router']->generate('blog_show', array('slug' => 'my-blog-post')) ?>">
            Read this blog post.
        </a>

Absolute URLs can also be generated.

.. configuration-block::

    .. code-block:: jinja

        <a href="{{ url('blog_show', { 'slug': 'my-blog-post' }) }}">
          Read this blog post.
        </a>

    .. code-block:: php

        <a href="<?php echo $view['router']->generate('blog_show', array('slug' => 'my-blog-post'), true) ?>">
            Read this blog post.
        </a>

Summary
-------

Routing is a two-way mechanism designed to allow formatting of external URLs
so that they are more user-friendly and decoupled from your application.
The main routing resource file (e.g. ``routing.yml``) configures the rules
of the routing system and can include other external routing resources. The
goal of matching a route is ultimately to determine a controller and a set
of arguments to execute for a given path info (i.e. URI). The ``Router``
should also be used each time you need to output a URL.
