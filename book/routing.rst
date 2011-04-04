.. index::
   single: Routing

Routing
=======

Having beautiful and flexible URLs is an absolute must for all high-quality
web applications. This means leaving behind ugly URLs like ``index.php?article_id=57``
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
HTTP request as an example:

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

This basic pattern means that a "page" in Symfony2 is nothing more than a
route and a controller (a PHP function). The job of the route is to match
the URI pattern of an incoming request and tell Symfony2 which controller
should be executed.

Mapping "Path Info" (URI) to a Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony's ``Router`` has one simple goal: to map the path info of a request
(a sanitized version of the URI) to a controller. It does this by interpreting
a routing map built by the developer. For example, if an incoming request
has the URI ``/blog/my-blog-post``, the following route would be matched:

.. configuration-block::

    .. code-block:: yaml

        blog_show:
            pattern:   /blog/{slug}
            defaults:  { _controller: AcmeBlogBundle:Blog:show }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_show" pattern="/blog/{slug}">
                <default key="_controller">AcmeBlogBundle:Blog:show</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog_show', new Route('/blog/{slug}', array(
            '_controller' => 'AcmeBlogBundle:Blog:show',
        )));

        return $collection;

.. tip::
    A "slug" is just a name for the url-friendly version of a string. For example,
    a page titled "All About Symfony2" might have a slug of "all-about-symfony2".

The pattern defined by the ``blog_show`` route looks like ``/blog/*`` where
the single wildcard is given the name ``slug``. For the URI ``/blog/my-blog-post``,
the ``slug`` parameter receives a value of ``my-blog-post``. Later, you'll
learn how you can use multiple wildcards, optional wildcards, and make your
wildcards more powerful by matching only on a given regular expression.

.. index::
   single: Routing; Creating routes

Creating Routes
---------------

The :class:`Symfony/Component/Routing/Router` service is seeded by a single
routing configuration resource (typically a file) that defines all the routes
for the application. This single resource is configured in the application
configuration file:

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

In other words, all the routing configuration of the application lives in
a single routing file inside the ``app/config`` directory. This file can
be written in YAML, XML or PHP, but YAML is used by default. In the next
section, you'll start creating and customizing routes inside this file.

.. tip::

    Even though all routes are seeded by a single file, it's common practice
    to include additional routing resources from inside this file. See the
    :ref:`Including External Routing Resources <routing-include-external-resources>`
    section for more information.

Basic Routing Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Defining a route is simple and flexible. A basic route consists of just two
parts: the ``pattern`` to match and the ``defaults`` collection:

.. configuration-block::

    .. code-block:: yaml

        homepage:
            pattern:   /
            defaults:  { _controller: AcmeDemoBundle:Main:homepage }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="homepage" pattern="/">
                <default key="_controller">AcmeDemoBundle:Main:homepage</default>
            </route>

        </routes>

    ..  code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('homepage', new Route('/', array(
            '_controller' => 'AcmeDemoBundle:Main:homepage',
        )));

        return $collection;

This route matches the homepage (``/``) and specifies the ``_controller``
``AcmeDemoBundle:Main:homepage``. The ``_controller`` string is translated by
Symfony2 into an actual PHP callable and executed. That part of the routing
process will be explained in the `Routes and Controllers`_ section.

.. index::
   single: Routing; Placeholders

Routing with Placeholders
~~~~~~~~~~~~~~~~~~~~~~~~~

Of course the routing system supports much more interesting routes. Many
routes will contain one or more named "wildcards" placeholders:

.. configuration-block::

    .. code-block:: yaml

        blog_show:
            pattern:   /blog/{slug}
            defaults:  { _controller: AcmeBlogBundle:Blog:show }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_show" pattern="/blog/{slug}">
                <default key="_controller">AcmeBlogBundle:Blog:show</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog_show', new Route('/blog/{slug}', array(
            '_controller' => 'AcmeBlogBundle:Blog:show',
        )));

        return $collection;

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

.. configuration-block::

    .. code-block:: yaml

        blog:
            pattern:   /blog
            defaults:  { _controller: AcmeBlogBundle:Blog:index }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" pattern="/blog">
                <default key="_controller">AcmeBlogBundle:Blog:index</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog', new Route('/blog', array(
            '_controller' => 'AcmeBlogBundle:Blog:index',
        )));

        return $collection;

At this point, this route should be easy - it contains no placeholders and
will only match the exact url ``/blog``. However, suppose now that this page
needs to support pagination:

.. configuration-block::

    .. code-block:: yaml

        blog:
            pattern:   /blog/{page}
            defaults:  { _controller: AcmeBlogBundle:Blog:index }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" pattern="/blog/{page}">
                <default key="_controller">AcmeBlogBundle:Blog:index</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog', new Route('/blog/{page}', array(
            '_controller' => 'AcmeBlogBundle:Blog:index',
        )));

        return $collection;

Like the ``{slug}`` placeholder in the previous example, the value matching
``{page}`` will be available in the controller so that we can determine which
set of blog posts to display based on the value of ``page`` in the URI.

Unfortunately, wildcards are required by default. In other words, the above
route will no longer match ``/blog`` - the url for page one must be ``/blog/1``!
Since that's no way for a rich web application to behave, modify the routing
configuration to make the ``{page}`` parameter optional. This is done by
including it in the ``defaults`` collection:

.. configuration-block::

    .. code-block:: yaml

        blog:
            pattern:   /blog/{page}
            defaults:  { _controller: AcmeBlogBundle:Blog:index, page: 1 }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" pattern="/blog/{page}">
                <default key="_controller">AcmeBlogBundle:Blog:index</default>
                <default key="page">1</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog', new Route('/blog/{page}', array(
            '_controller' => 'AcmeBlogBundle:Blog:index',
            'page' => 1,
        )));

        return $collection;

By adding ``page`` to the ``defaults`` key, the ``{page}`` placeholder is no
longer required. The URI ``/blog`` will match this route and the value of
the ``page`` parameter will be ``1``. The url ``/blog/2`` will also match,
giving the ``page`` parameter a value of ``2``.

.. index::
   single: Routing; Requirements

Adding Requirements
~~~~~~~~~~~~~~~~~~~

Take a look at the routes that have been created so far:

.. configuration-block::

    .. code-block:: yaml

        blog:
            pattern:   /blog/{page}
            defaults:  { _controller: AcmeBlogBundle:Blog:index, page: 1 }

        blog_show:
            pattern:   /blog/{slug}
            defaults:  { _controller: AcmeBlogBundle:Blog:show }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" pattern="/blog/{page}">
                <default key="_controller">AcmeBlogBundle:Blog:index</default>
                <default key="page">1</default>
            </route>

            <route id="blog_show" pattern="/blog/{slug}">
                <default key="_controller">AcmeBlogBundle:Blog:show</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog', new Route('/blog/{page}', array(
            '_controller' => 'AcmeBlogBundle:Blog:index',
            'page' => 1,
        )));

        $collection->add('blog_show', new Route('/blog/{show}', array(
            '_controller' => 'AcmeBlogBundle:Blog:show',
        )));

        return $collection;

But there's a problem. Notice that both routes have a pattern that matches
URI patterns like ``/blog/*``. The Symfony ``Router`` will always return
the *first* route that's matched. In other words, the ``blog_show`` route
will *never* be matched. Instead, URIs like ``/blog/my-blog-post`` will match
the first route (``blog``) and give a nonsense value of ``my-blog-post``
to the ``{page}`` parameter.

The answer to the problem is to add routing *requirements*. The routing setup
would work perfectly if the ``/blog/{page}`` pattern *only* matched URIs
where the ``{page}`` portion were an integer. Fortunately, regular expression
requirements can easily be added for each parameter. For example:

.. configuration-block::

    .. code-block:: yaml

        blog:
            pattern:   /blog/{page}
            defaults:  { _controller: AcmeBlogBundle:Blog:index, page: 1 }
            requirements:
                page:  \d+

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog" pattern="/blog/{page}">
                <default key="_controller">AcmeBlogBundle:Blog:index</default>
                <default key="page">1</default>
                <requirement key="page">\d+</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog', new Route('/blog/{page}', array(
            '_controller' => 'AcmeBlogBundle:Blog:index',
            'page' => 1,
        ), array(
            'page' => '\d+',
        )));

        return $collection;

The ``\d+`` requirement is a regular expression that says that the value of
the ``{page}`` parameter must be a digit (i.e. a number). The ``blog`` route
will still be matched for URIs such as ``/blog/2``, but it will no longer
be matched for URIs containing a non-number value for the ``{page}`` wildcard.
Instead, a URI like ``/blog/my-blog-post`` will now properly be allowed to
match against the ``blog_show`` route.

.. note::

    Keep in mind that the order of the routes is very important. If the ``blog_show``
    route were placed above the ``blog`` route, the ``/blog/2`` url would match
    ``blog_show`` instead of ``blog`` since the ``{slug}`` parameter
    of ``blog_show`` has no requirements. By using proper ordering and clever
    requirements, you can create a rich routing schema.

Since the parameter requirements are regular expressions, the complexity
and flexibility of each requirement is entirely up to you. Suppose the homepage
of your application is available in two different languages, based on the url:

.. configuration-block::

    .. code-block:: yaml

        homepage:
            pattern:   /{culture}
            defaults:  { _controller: AcmeDemoBundle:Main:homepage, culture: en }
            requirements:
                culture:  en|fr

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="homepage" pattern="/{culture}">
                <default key="_controller">AcmeDemoBundle:Main:homepage</default>
                <default key="culture">en</default>
                <requirement key="culture">en|fr</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('homepage', new Route('/{culture}', array(
            '_controller' => 'AcmeDemoBundle:Main:homepage',
            'culture' => 'en',
        ), array(
            'culture' => 'en|fr',
        )));

        return $collection;

When matching against this route, the ``{culture}`` portion of the URI is matched
against the regular expression ``(en|fr)``. The following URIs would match::

    /       (culture = en)
    /en     (culture = en)
    /fr     (culture = fr)

.. index::
   single: Routing; Method requirement

Method Routing
~~~~~~~~~~~~~~

In addition to the URI, you can also match on the *method* of the incoming
request (i.e. GET, HEAD, POST, PUT, DELETE). Suppose you have a contact form
with two controllers - one for displaying the form (on a GET request) and one
for processing the form when it's submitted (on a POST request). This can
be accomplished with the following routing configuration:

.. configuration-block::

    .. code-block:: yaml

        contact:
            pattern:  /contact
            defaults: { _controller: AcmeDemoBundle:Main:contact }
            requirements:
                _method:  GET

        contact_process:
            pattern:  /contact
            defaults: { _controller: AcmeDemoBundle:Main:contactProcess }
            requirements:
                _method:  POST

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" pattern="/contact">
                <default key="_controller">AcmeDemoBundle:Main:contact</default>
                <requirement key="_method">GET</requirement>
            </route>

            <route id="contact_process" pattern="/contact">
                <default key="_controller">AcmeDemoBundle:Main:contactProcess</default>
                <requirement key="_method">POST</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('contact', new Route('/contact', array(
            '_controller' => 'AcmeDemoBundle:Main:contact',
        ), array(
            '_method' => 'GET',
        )));

        $collection->add('contact_process', new Route('/contact', array(
            '_controller' => 'AcmeDemoBundle:Main:contactProcess',
        ), array(
            '_method' => 'POST',
        )));

        return $collection;

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
   single: Routing; _format parameter

.. _advanced-routing-example:

Advanced Routing Example
~~~~~~~~~~~~~~~~~~~~~~~~

At this point, you have everything you need to create a powerful routing
structure in Symfony. The following is an example of just how flexible the
routing system can be:

.. configuration-block::

    .. code-block:: yaml

        article_show:
          pattern:  /articles/{culture}/{year}/{title}.{_format}
          defaults  { _controller: AcmeDemoBundle:Article:show, _format: html }
          requirements:
              culture:  en|fr
              _format:  html|rss
              year:     \d+

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="article_show" pattern="/articles/{culture}/{year}/{title}.{_format}">
                <default key="_controller">AcmeDemoBundle:Article:show</default>
                <default key="_format">html</default>
                <requirement key="culture">en|fr</requirement>
                <requirement key="_format">html|rss</requirement>
                <requirement key="year">\d+</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('homepage', new Route('/articles/{culture}/{year}/{title}.{_format}', array(
            '_controller' => 'AcmeDemoBundle:Article:show',
            '_format' => 'html',
        ), array(
            'culture' => 'en|fr',
            '_format' => 'html|rss',
            'year' => '\d+',
        )));

        return $collection;

As we've seen, this route will only match if the ``{culture}`` portion of
the URI is either ``en`` or ``fr`` and if the ``{year}`` is a number.

This example also highlights the special ``_format`` routing parameter.
When using this parameter, the matched value becomes the "request format"
of the ``Request`` object. Ultimately, the request format is used for such
things such as setting the ``Content-Type`` of the response (e.g. a ``json``
request format translates into a ``Content-Type`` of ``application/json``).
It can also be used in the controller to render a different template for
each value of ``_format``. The ``_format`` parameter is a very powerful way
to render the same content in different formats.

.. note::

    You may have also noticed that a period (.) is used between the ``{title}``
    and ``{_format}`` parameters. This is because, by default, Symfony is configured
    to allow both a forward slash (/) or a period (.) to be a valid "separator"
    between the routing parameters.

.. index::
   single: Routing; Importing routing resources

.. _routing-include-external-resources:

Including External Routing Resources
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As you know, the routing configuration is seeded by a single resource (usually
a file) that's defined in the application's main configuration file (see
`Creating Routes`_ above). Commonly, however, you'll want to include routing
configuration from other places, such as from a bundle. Fortunately, this
can be easily accomplished:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            resource: "@AcmeHelloBundle/Resources/config/routing.yml"

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="@AcmeHelloBundle/Resources/config/routing.xml" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->addCollection($loader->import("@AcmeHelloBundle/Resources/config/routing.php"));

        return $collection;


The ``resource`` key loads the routing resource from the ``AcmeHelloBundle``:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/routing.yml
        hello:
            pattern:  /hello/{name}
            defaults: { _controller: AcmeHelloBundle:Hello:index }

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="hello" pattern="/hello/{name}">
                <default key="_controller">AcmeHelloBundle:Hello:index</default>
            </route>
        </routes>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('hello', new Route('/hello/{name}', array(
            '_controller' => 'AcmeHelloBundle:Hello:index',
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
            resource: "@AcmeHelloBundle/Resources/config/routing.yml"
            prefix:   /admin

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="@AcmeHelloBundle/Resources/config/routing.xml" prefix="/admin" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->addCollection($loader->import("@AcmeHelloBundle/Resources/config/routing.php"), '/admin');

        return $collection;

The string ``/admin`` will be prepended to the pattern of each route loaded
from the new routing resource.

.. index::
   single: Routing; Debugging

Visualizing & Debugging Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

While adding and customizing routes, it's helpful to be able to visualize
your routes and see if each is configured correctly. An easy way to see
every route in your application is via the ``router:debug`` console command. Execute
the command by running the following from the root of your project.

.. code-block:: text

    ./app/console router:debug

The command should print a helpful list of all of the routes registered with
the application:

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

.. _controller-string-syntax:

Controller Naming Format
~~~~~~~~~~~~~~~~~~~~~~~~

Every route *must* contain a ``_controller`` parameter, which is a special
string syntax that Symfony2 translates into a PHP callable. There are two
different syntax for the ``_controller`` parameter.

.. note::

   There is also a third, more advanced syntax that is discussed further
   in :doc:`/cookbook/controller/service`.

The ``bundle:controller:action`` syntax
.......................................

This syntax is the most common syntax, and the one used in the examples
in this chapter. Specifically, the ``_controller`` string ``AcmeBlogBundle:Blog:show``
translates to the following:

* ``AcmeBlog`` - indicates that the controller lives inside the ``AcmeBlogBundle``;

* ``Blog`` - indicates that the class name of the controller is ``BlogController``;

* ``show`` - means that the name of the method that will be executed is
  called ``showAction``.

Every controller that follows this syntax will live inside the ``Controller``
directory of the given bundle. In other words::

    ``AcmeBlogBundle:Blog:show``

means that the following PHP method will be executed::

    ``Acme\BlogBundle\Controller\BlogController::showAction()``

Since the fully-qualified class name of the controller is
``Acme\BlogBundle\Controller\BlogController``, the controller class itself
will live at ``src/Acme/BlogBundle/Controller/BlogBundle.php`` (assuming
that the ``Acme`` namespace lives in the ``src/Acme`` directory.

The basic ``class::method`` syntax
..................................

A less common but simple way to specify a controller is via the basic
``class::method`` syntax. This method could be used to call the example
controller via the string
``Acme\BlogBundle\Controller\BlogController::showAction``, though
the ``showAction`` must now be a static method. This is not a recommended
syntax.

Route Parameters as Controller Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The route parameters (e.g. ``{slug}``} are especially important because
each is made available as arguments to the controller method that's ultimately
executed::

    public function showAction($slug)
    {
      // ...
    }

In reality, the entire ``defaults`` collection is merged with the parameter
values to form a single array. Each key of that array is available as an
argument on the controller. For a more detailed discussion, see
:ref:`route-parameters-controller-arguments`.

.. index::
   single: Routing; Generating URLs

Generating URLs
---------------

The routing system should also be used to generate URLs. In fact, routing
is really a bi-directional system that maps path info (i.e. URI) to an
array of routing parameters, and parameters back to a URI. The
:method:`Symfony\\Component\\Routing\\Router::match` and
:method:`Symfony\\Component\\Routing\\Router::generate` methods form this bi-directional
system. Take the ``blog_show`` example route from earlier::

    $params = $router->match('/blog/my-blog-post');
    // array('slug' => 'my-blog-post', '_controller' => 'AcmeBlogBundle:Blog:show')

    $uri = $router->generate('blog_show', array('slug' => 'my-blog-post'));
    // /blog/my-blog-post

To generate a URL, you need to specify the name of the route (e.g. ``blog_show``)
and any parameters/wildcards (e.g. ``slug = my-blog-post``) used in the pattern
for that route.

The key to generating a URL is to get access to the ``router`` service. From
a traditional controller, this is easy::

    class MainController extends Controller
    {
        public function showAction($slug)
        {
          // ...

          $url = $this->get('router')->generate('blog_show', array('slug' => 'my-blog-post'));
        }
    }

In an upcoming section, you'll learn how to generate URLs from inside templates.

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

    The host that's used when generating an absolute URL is the host of
    the current ``Request`` object. This is detected automatically based
    on server information supplied by PHP.

.. index::
   single: Routing; Generating URLs in a template

Generating URLs from a template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The most common place to generate a URL is from within a template when linking
between pages in your application:

.. configuration-block::

    .. code-block:: html+jinja

        <a href="{{ path('blog_show', { 'slug': 'my-blog-post' }) }}">
          Read this blog post.
        </a>

    .. code-block:: php

        <a href="<?php echo $view['router']->generate('blog_show', array('slug' => 'my-blog-post')) ?>">
            Read this blog post.
        </a>

Absolute URLs can also be generated.

.. configuration-block::

    .. code-block:: html+jinja

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
The main routing resource file (``app/config/routing.yml`` by default) configures
the rules of the routing system and can include other external routing resources.
The goal of matching a route is ultimately to determine a controller and
a set of parameter values for a given path info (i.e. URI). The ``Router``
should also be used each time you need to render a URL.