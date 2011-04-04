The Big Picture
===============

Start using Symfony2 in 10 minutes! This chapter walks you through some of the
most important concepts behind Symfony2. It explains how to get started
quickly by showing you the structure of a simple project.

If you've used a web framework before, you should feel right at home with
Symfony2. If not, welcome to a whole new way of developing web applications!

.. tip:

    Want to learn why and when you need to use a framework? Read the "`Symfony
    in 5 minutes`_" document.

Downloading Symfony2
--------------------

First, check that you have installed and configured a Web server (such as
Apache) with PHP 5.3.2 or higher.

Ready? Let's start by downloading "`Symfony2 Standard Edition`_", a Symfony
:term:`distribution` that is preconfigured for the most common use cases and
also contains some code that demonstrates how to use Symfony2 (get an archive
with the *vendors* included to get started even faster).

After unpacking the archive under the web server root directory, you should
have a ``Symfony/`` directory that looks like this:

.. code-block:: text

    www/ <- the web root directory
        Symfony/ <- the unpacked archive
            app/
                cache/
                config/
                logs/
            src/
                Acme/
                    DemoBundle/
                        Controller/
                        Resources/
            vendor/
                symfony/
                doctrine/
                ...
            web/
                app.php

Checking the Configuration
--------------------------

Symfony2 comes with a visual server configuration tester to help avoid some
headaches that come from Web server or PHP misconfiguration. Use the following
URL to see the diagnostics for your machine:

.. code-block:: text

    http://localhost/Symfony/web/config.php

If there are any outstanding issues listed, correct them; you might also tweak
your configuration by following the given recommendations. When everything is
fine, click on "Go to the Welcome page" to request your first "real" Symfony2
webpage:

.. code-block:: text

    http://localhost/Symfony/web/app_dev.php/

Symfony2 should welcome and congratulate you for your hard work so far!

Understanding the Fundamentals
------------------------------

One of the main goal of a framework is to ensure the `Separation of Concerns`_.
It keeps your code organized and allows your application to evolve
easily over time by avoiding the mix of database calls, HTML tags, and
business logic in the same script. To achieve this goal, you must learn about
some fundamental concepts and terms.

.. tip::

    Want some proofs that using a framework is better than mixing everything
    in the same script? Read the "`From flat PHP to Symfony2`_" chapter of the
    book.

The distribution comes with some sample code that you will use to learn more
about the main Symfony2 concepts. Go to the following URL to be greeted by
Symfony2 (replace *Fabien* with your first name):

.. code-block:: text

    http://localhost/Symfony/web/app_dev.php/demo/hello/Fabien

What's going on here? Let's dissect the URL:

* ``app_dev.php``: This is a :term:`front controller`. It is the unique entry
  point of the application and it responds to all user requests;

* ``/demo/hello/Fabien``: This is the *virtual path* to the resource the user
  wants to access.

Your responsibility as a developer is to write the code that maps the user
*request* (``/demo/hello/Fabien``) to the *resource* associated with it
(``Hello Fabien!``).

Routing
~~~~~~~

Symfony2 routes the request to the code that handles it by trying to match the
requested URL according to some configured patterns. By default, they are
defined in ``app/config/routing.yml`` configuration file:

.. code-block:: yaml

    # app/config/routing.yml
    _welcome:
        pattern:  /
        defaults: { _controller: AcmeDemoBundle:Welcome:index }

    _demo:
        resource: "@AcmeDemoBundle/Controller/DemoController.php"
        type:     annotation
        prefix:   /demo

The first three lines of the routing configuration file define the code that
is executed when the user requests the "``/``" resource (i.e. the welcome
page). When requested, the ``AcmeDemoBundle:Welcome:index`` controller will be
executed.

.. tip::

    The Symfony2 Standard Edition uses `YAML`_ for its configuration files,
    but Symfony2 also supports XML, PHP, and annotations natively. The
    different formats are compatible and may be used interchangeably within an
    application. Also, the performance of your application does not depend on
    the configuration format you chose as everything is cached on the very
    first request.

Controllers
~~~~~~~~~~~

A controller handles incoming *requests* and return *responses* (often in
HTML). Instead of using the PHP global variables and functions to manage these
HTTP messages, Symfony uses objects:
:class:`Symfony\\Component\\HttpFoundation\\Request` and
:class:`Symfony\\Component\\HttpFoundation\\Response`. The simplest possible
controller creates the response by hand, based on the request::

    use Symfony\Component\HttpFoundation\Response;

    $name = $request->query->get('name');

    return new Response('Hello '.$name, 200, array('Content-Type' => 'text/plain'));

.. note::

    Don't be fooled by the simple concepts and the power that they hold. Read
    the "`The HTTP Spec and Symfony2`_" chapter of the book to learn more
    about how Symfony2 embraces HTTP and why it makes things simpler and more
    powerful at the same time.

Symfony2 chooses the controller based on the ``_controller`` value from the
routing configuration: ``AcmeDemoBundle:Welcome:index``. This string is the
controller *logical name*, and it references the ``indexAction`` method from
the ``Acme\DemoBundle\Controller\WelcomeController`` class::

    // src/Acme/DemoBundle/Controller/WelcomeController.php
    namespace Acme\DemoBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class WelcomeController extends Controller
    {
        public function indexAction()
        {
            return $this->render('AcmeDemoBundle:Welcome:index.html.twig');
        }
    }

.. tip::

    You could have used
    ``Acme\DemoBundle\Controller\WelcomeController::indexAction`` for the
    ``_controller`` value but if you follow some simple conventions, the
    logical name is more concise and allows for more flexibility.

The controller class extends the built-in ``Controller`` class, which provides
useful shortcut methods, like the
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::render`
method that loads and renders a template
(``AcmeDemoBundle:Welcome:index.html.twig``). The returned value is a Response
object populated with the rendered content. So, if the needs arise, the
Response can be tweaked before it is sent to the browser::

    public function indexAction()
    {
        $response = $this->render('AcmeDemoBundle:Welcome:index.txt.twig');
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }

.. tip::

    Extending the ``Controller`` base class is optional. As a matter of fact,
    a controller can be a plain PHP function or even a PHP closure. "`The Controller`_"
    chapter of the book tells you everything about Symfony2 controllers.

The template name, ``AcmeDemoBundle:Welcome:index.html.twig``, is the template
*logical name* and it references the
``src/Acme/DemoBundle/Resources/views/Welcome/index.html.twig`` file. Again,
the bundles section below will explain you why this is useful.

Now, take a look at the end of the routing configuration again:

.. code-block:: yaml

    # app/config/routing.yml
    _demo:
        resource: "@AcmeDemoBundle/Controller/DemoController.php"
        type:     annotation
        prefix:   /demo

Symfony2 can read the routing information from different resources written in
YAML, XML, PHP, or even embedded in PHP annotations. Here, the resource
*logical name* is ``@AcmeDemoBundle/Controller/DemoController.php`` and refers
to the ``src/Acme/DemoBundle/Controller/DemoController.php`` file. In this
file, routes are defined as annotations on action methods::

    // src/Acme/DemoBundle/Controller/DemoController.php
    class DemoController extends Controller
    {
        /**
         * @extra:Route("/hello/{name}", name="_demo_hello")
         * @extra:Template()
         */
        public function helloAction($name)
        {
            return array('name' => $name);
        }

        // ...
    }

The ``@extra:Route()`` annotation defines the route for the ``helloAction``
method and the pattern is ``/hello/{name}``. A string enclosed in curly
brackets like ``{name}`` is a placeholder. As you can see, its value can be
retrieved through the ``$name`` method argument.

.. note::

    Even if annotations are not natively supported by PHP, you use them
    extensively in Symfony2 as a convenient way to configure the framework
    behavior and keep the configuration next to the code.

If you have a closer look at the action code, you can see that instead of
rendering a template like before, it just returns an array of parameters. The
``@extra:Template()`` annotation takes care of rendering a template which name
is determined based on some simple conventions (it will render
``src/Acme/DemoBundle/Resources/views/Demo/hello.html.twig``).

.. tip::

    The ``@extra:Route()`` and ``@extra:Template()`` annotations are more
    powerful than the simple examples shown in this tutorial. Learn more about
    "`annotations in controllers`_" in the official documentation.

Templates
~~~~~~~~~

The controller renders the
``src/Acme/DemoBundle/Resources/views/Demo/hello.html.twig`` template (or
``AcmeDemoBundle:Demo:hello.html.twig`` if you use the logical name):

.. code-block:: jinja

    {# src/Acme/DemoBundle/Resources/views/Demo/hello.html.twig #}
    {% extends "AcmeDemoBundle::layout.html.twig" %}

    {% block title "Hello " ~ name %}

    {% block content %}
        <h1>Hello {{ name }}!</h1>
    {% endblock %}

By default, Symfony2 uses `Twig`_ as its template engine but you can also use
traditional PHP templates if you choose. The next chapter will introduce how
templates work in Symfony2.

Bundles
~~~~~~~

You might have wondered why the :term:`bundle` word is used in many names we
have seen so far. All the code you write for your application is organized in
bundles. In Symfony2 speak, a bundle is a structured set of files (PHP files,
stylesheets, JavaScripts, images, ...) that implements a single feature (a
blog, a forum, ...) and which can be easily shared with other developers. As
of now, we have manipulated one bundle, ``AcmeDemoBundle``. You will learn
more about bundles in the last chapter of this tutorial.

Working with Environments
-------------------------

Now that you have a better understanding of how Symfony2 works, have a closer
look at the bottom of the page; you will notice a small bar with the Symfony2
logo. This is called the "Web Debug Toolbar" and it is the developer's best
friend. But this is only the tip of the iceberg; click on the weird
hexadecimal number to reveal yet another very useful Symfony2 debugging tool:
the profiler.

Of course, these tools must not be available when you deploy your application
to production. That's why you will find another front controller in the
``web/`` directory (``app.php``), optimized for the production environment:

.. code-block:: text

    http://localhost/Symfony/web/app.php/demo/hello/Fabien

And if you use Apache with ``mod_rewrite`` enabled, you can even omit the
``app.php`` part of the URL:

.. code-block:: text

    http://localhost/Symfony/web/demo/hello/Fabien

Last but not least, on the production servers, you should point your web root
directory to the ``web/`` directory to secure your installation and have an
even better looking URL:

.. code-block:: text

    http://localhost/demo/hello/Fabien

To make you application respond faster, Symfony2 maintains a cache under the
``app/cache/`` directory. In the development environment (``app_dev.php``),
this cache is flushed automatically whenever you make changes to the code or
configuration. But that's not the case in the production environment
(``app.php``) to make it perform even better; that's why you should always use
the development environment when developing your application.

Different :term:`environments<environment>` of a given application differ only in their
configuration. In fact, a configuration can inherit from another one:

.. code-block:: yaml

    # app/config/config_dev.yml
    imports:
        - { resource: config.yml }

    web_profiler:
        toolbar: true
        intercept_redirects: false

The ``dev`` environment (defined in ``config_dev.yml``) inherits from the
global ``config.yml`` file and extends it by enabling the web debug toolbar.

Final Thoughts
--------------

Congratulations! You've had your first taste of Symfony2 code. That wasn't so
hard, was it? There's a lot more to explore, but you should already see how
Symfony2 makes it really easy to implement web sites better and faster. If you
are eager to learn more about Symfony2, dive into the next section: "The
View".

.. _Symfony2 Standard Edition:    http://symfony.com/download
.. _Symfony in 5 minutes:         http://symfony.com/symfony-in-five-minutes
.. _Separation of Concerns:       http://en.wikipedia.org/wiki/Separation_of_concerns
.. _From flat PHP to Symfony2:    http://symfony.com/doc/2.0/book/from_flat_php_to_symfony2.html
.. _YAML:                         http://www.yaml.org/
.. _The HTTP Spec and Symfony2:   http://symfony.com/doc/2.0/book/http_fundamentals.html
.. _Learn more about the Routing: http://symfony.com/doc/2.0/book/routing.html
.. _The Controller:               http://symfony.com/doc/2.0/book/controller.html
.. _annotations in controllers:   http://bundles.symfony-reloaded.org/frameworkextrabundle/
.. _Twig:                         http://www.twig-project.org/
