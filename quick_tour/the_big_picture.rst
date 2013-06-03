The Big Picture
===============

Start using Symfony2 in 10 minutes! This chapter will walk you through some
of the most important concepts behind Symfony2 and explain how you can get
started quickly by showing you a simple project in action.

If you've used a web framework before, you should feel right at home with
Symfony2. If not, welcome to a whole new way of developing web applications!

.. tip::

    Want to learn why and when you need to use a framework? Read the "`Symfony
    in 5 minutes`_" document.

Downloading Symfony2
--------------------

First, check that you have installed and configured a Web server (such as
Apache) with PHP 5.3.2 or higher.

.. tip::

    If you have PHP 5.4, you could use the built-in web server. The built-in
    server should be used only for development purpose, but it can help you
    to start your project quickly and easily.

    Just use this command to launch the server:

    .. code-block:: bash

        $ php -S localhost:80 -t /path/to/www

    where "/path/to/www" is the path to some directory on your machine that
    you'll extract Symfony into so that the eventual URL to your application
    is "http://localhost/Symfony/app_dev.php". You can also extract Symfony
    first and then start the web server in the Symfony "web" directory. If
    you do this, the URL to your application will be "http://localhost/app_dev.php".

Ready? Start by downloading the "`Symfony2 Standard Edition`_", a Symfony
:term:`distribution` that is preconfigured for the most common use cases and
also contains some code that demonstrates how to use Symfony2 (get the archive
with the *vendors* included to get started even faster).

After unpacking the archive under your web server root directory, you should
have a ``Symfony/`` directory that looks like this:

.. code-block:: text

    www/ <- your web root directory
        Symfony/ <- the unpacked archive
            app/
                cache/
                config/
                logs/
                Resources/
            bin/
            src/
                Acme/
                    DemoBundle/
                        Controller/
                        Resources/
                        ...
            vendor/
                symfony/
                doctrine/
                ...
            web/
                app.php
                ...

.. note::

    If you downloaded the Standard Edition *without vendors*, simply run the
    following command to download all of the vendor libraries:

    .. code-block:: bash

        $ php bin/vendors install

Checking the Configuration
--------------------------

Symfony2 comes with a visual server configuration tester to help avoid some
headaches that come from Web server or PHP misconfiguration. Use the following
URL to see the diagnostics for your machine:

.. code-block:: text

    http://localhost/Symfony/web/config.php

.. note::

    All of the example URLs assume that you've downloaded and unzipped ``Symfony``
    directly into the web server web root. If you've followed the directions
    above and done this, then add ``/Symfony/web`` after ``localhost`` for all
    the URLs you see:

    .. code-block:: text

        http://localhost/Symfony/web/config.php

    To get nice and short urls you should point the document root of your
    webserver or virtual host to the ``Symfony/web/`` directory. In that
    case, your URLs will look like ``http://localhost/config.php`` or
    ``http://site.local/config.php``, if you created a virtual host to a
    local domain called, for example, ``site.local``.

If there are any outstanding issues listed, correct them. You might also tweak
your configuration by following any given recommendations. When everything is
fine, click on "*Bypass configuration and go to the Welcome page*" to request
your first "real" Symfony2 webpage:

.. code-block:: text

    http://localhost/Symfony/web/app_dev.php/

Symfony2 should welcome and congratulate you for your hard work so far!

.. image:: /images/quick_tour/welcome.jpg
   :align: center

Understanding the Fundamentals
------------------------------

One of the main goals of a framework is to ensure `Separation of Concerns`_.
This keeps your code organized and allows your application to evolve easily
over time by avoiding the mixing of database calls, HTML tags, and business
logic in the same script. To achieve this goal with Symfony, you'll first
need to learn a few fundamental concepts and terms.

.. tip::

    Want proof that using a framework is better than mixing everything
    in the same script? Read the ":doc:`/book/from_flat_php_to_symfony2`"
    chapter of the book.

The distribution comes with some sample code that you can use to learn more
about the main Symfony2 concepts. Go to the following URL to be greeted by
Symfony2 (replace *Fabien* with your first name):

.. code-block:: text

    http://localhost/Symfony/web/app_dev.php/demo/hello/Fabien

.. image:: /images/quick_tour/hello_fabien.png
   :align: center

What's going on here? Let's dissect the URL:

* ``app_dev.php``: This is a :term:`front controller`. It is the unique entry
  point of the application and it responds to all user requests;

* ``/demo/hello/Fabien``: This is the *virtual path* to the resource the user
  wants to access.

Your responsibility as a developer is to write the code that maps the user's
*request* (``/demo/hello/Fabien``) to the *resource* associated with it
(the ``Hello Fabien!`` HTML page).

Routing
~~~~~~~

Symfony2 routes the request to the code that handles it by trying to match the
requested URL against some configured patterns. By default, these patterns
(called routes) are defined in the ``app/config/routing.yml`` configuration
file. When you're in the ``dev`` :ref:`environment<quick-tour-big-picture-environments>` -
indicated by the app_**dev**.php front controller - the ``app/config/routing_dev.yml``
configuration file is also loaded. In the Standard Edition, the routes to
these "demo" pages are placed in that file:

.. code-block:: yaml

    # app/config/routing_dev.yml
    _welcome:
        pattern:  /
        defaults: { _controller: AcmeDemoBundle:Welcome:index }

    _demo:
        resource: "@AcmeDemoBundle/Controller/DemoController.php"
        type:     annotation
        prefix:   /demo

    # ...

The first three lines (after the comment) define the code that is executed
when the user requests the "``/``" resource (i.e. the welcome page you saw
earlier). When requested, the ``AcmeDemoBundle:Welcome:index`` controller
will be executed. In the next section, you'll learn exactly what that means.

.. tip::

    The Symfony2 Standard Edition uses `YAML`_ for its configuration files,
    but Symfony2 also supports XML, PHP, and annotations natively. The
    different formats are compatible and may be used interchangeably within an
    application. Also, the performance of your application does not depend on
    the configuration format you choose as everything is cached on the very
    first request.

Controllers
~~~~~~~~~~~

A controller is a fancy name for a PHP function or method that handles incoming
*requests* and returns *responses* (often HTML code). Instead of using the
PHP global variables and functions (like ``$_GET`` or ``header()``) to manage
these HTTP messages, Symfony uses objects: :class:`Symfony\\Component\\HttpFoundation\\Request`
and :class:`Symfony\\Component\\HttpFoundation\\Response`. The simplest possible
controller might create the response by hand, based on the request::

    use Symfony\Component\HttpFoundation\Response;

    $name = $request->query->get('name');

    return new Response('Hello '.$name, 200, array('Content-Type' => 'text/plain'));

.. note::

    Symfony2 embraces the HTTP Specification, which are the rules that govern
    all communication on the Web. Read the ":doc:`/book/http_fundamentals`"
    chapter of the book to learn more about this and the added power that
    this brings.

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

    You could have used the full class and method name -
    ``Acme\DemoBundle\Controller\WelcomeController::indexAction`` - for the
    ``_controller`` value. But if you follow some simple conventions, the
    logical name is shorter and allows for more flexibility.

The ``WelcomeController`` class extends the built-in ``Controller`` class,
which provides useful shortcut methods, like the
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

No matter how you do it, the end goal of your controller is always to return
the ``Response`` object that should be delivered back to the user. This ``Response``
object can be populated with HTML code, represent a client redirect, or even
return the contents of a JPG image with a ``Content-Type`` header of ``image/jpg``.

.. tip::

    Extending the ``Controller`` base class is optional. As a matter of fact,
    a controller can be a plain PHP function or even a PHP closure.
    ":doc:`The Controller</book/controller>`" chapter of the book tells you
    everything about Symfony2 controllers.

The template name, ``AcmeDemoBundle:Welcome:index.html.twig``, is the template
*logical name* and it references the
``Resources/views/Welcome/index.html.twig`` file inside the ``AcmeDemoBundle``
(located at ``src/Acme/DemoBundle``). The bundles section below will explain
why this is useful.

Now, take a look at the routing configuration again and find the ``_demo``
key:

.. code-block:: yaml

    # app/config/routing_dev.yml
    _demo:
        resource: "@AcmeDemoBundle/Controller/DemoController.php"
        type:     annotation
        prefix:   /demo

Symfony2 can read/import the routing information from different files written
in YAML, XML, PHP, or even embedded in PHP annotations. Here, the file's
*logical name* is ``@AcmeDemoBundle/Controller/DemoController.php`` and refers
to the ``src/Acme/DemoBundle/Controller/DemoController.php`` file. In this
file, routes are defined as annotations on action methods::

    // src/Acme/DemoBundle/Controller/DemoController.php
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    class DemoController extends Controller
    {
        /**
         * @Route("/hello/{name}", name="_demo_hello")
         * @Template()
         */
        public function helloAction($name)
        {
            return array('name' => $name);
        }

        // ...
    }

The ``@Route()`` annotation defines a new route with a pattern of
``/hello/{name}`` that executes the ``helloAction`` method when matched. A
string enclosed in curly brackets like ``{name}`` is called a placeholder. As
you can see, its value can be retrieved through the ``$name`` method argument.

.. note::

    Even if annotations are not natively supported by PHP, you use them
    extensively in Symfony2 as a convenient way to configure the framework
    behavior and keep the configuration next to the code.

If you take a closer look at the controller code, you can see that instead of
rendering a template and returning a ``Response`` object like before, it
just returns an array of parameters. The ``@Template()`` annotation tells
Symfony to render the template for you, passing in each variable of the array
to the template. The name of the template that's rendered follows the name
of the controller. So, in this example, the ``AcmeDemoBundle:Demo:hello.html.twig``
template is rendered (located at ``src/Acme/DemoBundle/Resources/views/Demo/hello.html.twig``).

.. tip::

    The ``@Route()`` and ``@Template()`` annotations are more powerful than
    the simple examples shown in this tutorial. Learn more about "`annotations
    in controllers`_" in the official documentation.

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

You might have wondered why the :term:`bundle` word is used in many names you
have seen so far. All the code you write for your application is organized in
bundles. In Symfony2 speak, a bundle is a structured set of files (PHP files,
stylesheets, JavaScripts, images, ...) that implements a single feature (a
blog, a forum, ...) and which can be easily shared with other developers. As
of now, you have manipulated one bundle, AcmeDemoBundle. You will learn
more about bundles in the last chapter of this tutorial.

Environments
~~~~~~~~~~~~

Every Symfony application runs within an :term:`environment`. An environment
is a specific set of configuration and loaded bundles, represented by a string.
The same application can be run with different configurations by running the
application in different environments. Symfony2 comes with three environments
defined — ``dev``, ``test`` and ``prod`` — but you can create your own as well.

Environments are useful by allowing a single application to have a dev environment
built for debugging and a production environment optimized for speed. You might
also load specific bundles based on the selected environment. For example,
Symfony2 comes with the WebProfilerBundle (described below), enabled only
in the ``dev`` and ``test`` environments.

.. _quick-tour-big-picture-environments:

Working with Environments
-------------------------

Symfony2 loads configuration based on the name of the environment. Typically,
you put your common configuration in ``config.yml`` and override where necessary
in the configuration for each environment. For example:

.. code-block:: yaml

    # app/config/config_dev.yml
    imports:
        - { resource: config.yml }

    web_profiler:
        toolbar: true
        intercept_redirects: false

In this example, the ``dev`` environment loads the ``config_dev.yml`` configuration
file, which itself imports the global ``config.yml`` file and then modifies it by
enabling the web debug toolbar.

To make your application respond faster, Symfony2 maintains a cache under the
``app/cache/`` directory. In the ``dev`` environment, this cache is flushed
automatically whenever you make changes to any code or configuration. But that's
not the case in the ``prod`` environment, where performance is key. That's why you
should always use the development environment when developing your application.

Symfony2 comes with two web-accessible front controllers: ``app_dev.php`` 
provides the ``dev`` environment, and ``app.php`` provides the ``prod`` environment.
All web accesses to Symfony2 normally go through one of these front controllers.
(The ``test`` environment is normally only used when running unit tests, and so 
doesn't have a dedicated front controller. The console tool also provides a
front controller that can be used with any environment.)

The AcmeDemoBundle is normally only available in the dev environment, but
if you were to add it (and its routes) to the production environment, you could
go here:

.. code-block:: text

    http://localhost/Symfony/web/app.php/demo/hello/Fabien

If instead of using php's built-in webserver, you use Apache with ``mod_rewrite``
enabled and take advantage of the ``.htaccess`` file Symfony2 provides
in ``web/``, you can even omit the ``app.php`` part of the URL. The default
``.htaccess`` points all requests to the ``app.php`` front controller:

.. code-block:: text

    http://localhost/Symfony/web/demo/hello/Fabien

Finally, on production servers, you should point your web root directory
to the ``web/`` directory to better secure your installation and have an
even better looking URL:

.. code-block:: text

    http://localhost/demo/hello/Fabien

.. note::

    Note that the three URLs above are provided here only as **examples** of
    how a URL looks like when the production front controller is used (with or
    without mod_rewrite). If you actually try them in an out-of-the-box
    installation of *Symfony Standard Edition*, you will get a 404 error since
    *AcmeDemoBundle* is enabled only in the dev environment and its routes imported
    from *app/config/routing_dev.yml*.

.. _quick-tour-big-picture-web-debug-toolbar:

The Web Debug Toolbar and Profiler
----------------------------------


Now that you have a better understanding of how Symfony2 works, take a closer
look at the bottom of any Symfony2 rendered page. You should notice a small
bar with the Symfony2 logo. This is the "Web Debug Toolbar", and it is a
Symfony2 developer's best friend.

.. image:: /images/quick_tour/web_debug_toolbar.png
   :align: center

What you see initially is only the tip of the iceberg; click on the long
hexadecimal number (the session token) to reveal yet another very useful
Symfony2 debugging tool: the profiler.

.. image:: /images/quick_tour/profiler.png
   :align: center

When enabled (by default in the dev and test environments), the Profiler
records a great deal of information on each request made to your application.
It allows you to view details of each request, including, but not limited to,
GET or POST parameters and the request headers; logs; an execution timeline;
information on the currently logged in user; Doctrine queries; and more.

Of course, it would be unwise to have these tools enabled when you deploy
your application, so by default, the profiler is not enabled in the ``prod``
environment. (In fact, its bundle is not even loaded).


Final Thoughts
--------------

Congratulations! You've had your first taste of Symfony2 code. That wasn't so
hard, was it? There's a lot more to explore, but you should already see how
Symfony2 makes it really easy to implement web sites better and faster. If you
are eager to learn more about Symfony2, dive into the next section:
":doc:`The View<the_view>`".

.. _Symfony2 Standard Edition:      http://symfony.com/download
.. _Symfony in 5 minutes:           http://symfony.com/symfony-in-five-minutes
.. _Separation of Concerns:         http://en.wikipedia.org/wiki/Separation_of_concerns
.. _YAML:                           http://www.yaml.org/
.. _annotations in controllers:     http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html#annotations-for-controllers
.. _Twig:                           http://twig.sensiolabs.org/
