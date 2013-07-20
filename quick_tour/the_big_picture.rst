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
Apache) with PHP 5.3.3 or higher.

.. tip::

    If you have PHP 5.4, you could use the built-in web server to start trying
    things out. We'll tell you how to start the built-in web server
    :ref:`after downloading Symfony<quick-tour-big-picture-built-in-server>`.

Ready? Start by downloading the "`Symfony2 Standard Edition`_": a Symfony
:term:`distribution` that is preconfigured for the most common use cases and
also contains some code that demonstrates how to use Symfony2 (get the archive
with the *vendors* included to get started even faster).

After unpacking the archive under your web server root directory (if you'll
use the built-in PHP web server, you can unpack it anywhere), you should
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

    If you are familiar with `Composer`_, you can download Composer and then
    run the following command instead of downloading the archive (replacing
    ``2.3.0`` with the latest Symfony release like ``2.3.1``):

    .. code-block:: bash

        $ php composer.phar create-project symfony/framework-standard-edition Symfony 2.3.0

.. _`quick-tour-big-picture-built-in-server`:

If you have PHP 5.4, you can use the built-in web server:

.. code-block:: bash

    # check your PHP CLI configuration
    $ php php app/check.php

    # run the built-in web server
    $ php php app/console server:run

Then the URL to your application will be "http://localhost:8000/app_dev.php"

The built-in server should be used only for development purpose, but it
can help you to start your project quickly and easily.

If you're using a traditional web server like Apache, your URL depends on
your configuration. If you've unzipped Symfony under your web root into a
``Symfony`` directory, then the URL to your application will be:
"http://localhost/Symfony/web/app_dev.php".

.. note::

    Read more in our :doc:`/cookbook/configuration/web_server_configuration`
    section.

Checking the Configuration
--------------------------

Symfony2 comes with a visual server configuration tester to help avoid some
headaches that come from Web server or PHP misconfiguration. Use the following
URL to see the diagnostics for your machine:

.. code-block:: text

    http://localhost/config.php

.. note::

    All of the example URLs assume you've setup your web server to point
    directly to the ``web/`` directory of your new project, which is different
    and a bit more advanced than the process shown above. So, the URL on your
    machine will vary - e.g. ``http://localhost:8000/config.php``
    or ``http://localhost/Symfony/web/config.php``. See the
    :ref:`above section<quick-tour-big-picture-built-in-server>` for details
    on what your URL should be and use it below in all of the examples.

If there are any outstanding issues listed, correct them. You might also tweak
your configuration by following any given recommendations. When everything is
fine, click on "*Bypass configuration and go to the Welcome page*" to request
your first "real" Symfony2 webpage:

.. code-block:: text

    http://localhost/app_dev.php/

Symfony2 should welcome and congratulate you for your hard work so far!

.. image:: /images/quick_tour/welcome.png
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

    http://localhost/app_dev.php/demo/hello/Fabien

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
requested URL (i.e. the virtual path) against some configured paths. By default,
these paths (called routes) are defined in the ``app/config/routing.yml`` configuration
file. When you're in the ``dev`` :ref:`environment<quick-tour-big-picture-environments>` -
indicated by the app_**dev**.php front controller - the ``app/config/routing_dev.yml``
configuration file is also loaded. In the Standard Edition, the routes to
these "demo" pages are imported from this file:

.. code-block:: yaml

    # app/config/routing_dev.yml
    # ...

    # AcmeDemoBundle routes (to be removed)
    _acme_demo:
        resource: "@AcmeDemoBundle/Resources/config/routing.yml"

This imports a ``routing.yml`` file that lives inside the AcmeDemoBundle:

.. code-block:: yaml

    # src/Acme/DemoBundle/Resources/config/routing.yml
    _welcome:
        path:  /
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

    The Symfony2 Standard Edition uses :doc:`YAML</components/yaml/yaml_format>`
    for its configuration files, but Symfony2 also supports XML, PHP, and
    annotations natively. The different formats are compatible and may be
    used interchangeably within an application. Also, the performance of
    your application does not depend on the configuration format you choose
    as everything is cached on the very first request.

Controllers
~~~~~~~~~~~

A controller is a fancy name for a PHP function or method that handles incoming
*requests* and returns *responses* (often HTML code). Instead of using the
PHP global variables and functions (like ``$_GET`` or ``header()``) to manage
these HTTP messages, Symfony uses objects: :ref:`Request<component-http-foundation-request>`
and :ref:`Response<component-http-foundation-response>`. The simplest possible
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
:ref:`render()<controller-rendering-templates>` method that loads and renders
a template (``AcmeDemoBundle:Welcome:index.html.twig``). The returned value
is a Response object populated with the rendered content. So, if the need
arises, the Response can be tweaked before it is sent to the browser::

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
*logical name* and it references the ``Resources/views/Welcome/index.html.twig``
file inside the ``AcmeDemoBundle`` (located at ``src/Acme/DemoBundle``).
The `Bundles`_ section below will explain why this is useful.

Now, take a look at the routing configuration again and find the ``_demo``
key:

.. code-block:: yaml

    # src/Acme/DemoBundle/Resources/config/routing.yml
    # ...
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

The ``@Route()`` annotation defines a new route with a path of
``/hello/{name}`` that executes the ``helloAction`` method when matched. A
string enclosed in curly brackets like ``{name}`` is called a placeholder. As
you can see, its value can be retrieved through the ``$name`` method argument.

.. note::

    Even if annotations are not natively supported by PHP, you can use them
    in Symfony2 as a convenient way to configure the framework behavior and
    keep the configuration next to the code.

If you take a closer look at the controller code, you can see that instead of
rendering a template and returning a ``Response`` object like before, it
just returns an array of parameters. The ``@Template()`` annotation tells
Symfony to render the template for you, passing in each variable of the array
to the template. The name of the template that's rendered follows the name
of the controller. So, in this example, the ``AcmeDemoBundle:Demo:hello.html.twig``
template is rendered (located at ``src/Acme/DemoBundle/Resources/views/Demo/hello.html.twig``).

.. tip::

    The ``@Route()`` and ``@Template()`` annotations are more powerful than
    the simple examples shown in this tutorial. Learn more about "`annotations in controllers`_"
    in the official documentation.

Templates
~~~~~~~~~

The controller renders the ``src/Acme/DemoBundle/Resources/views/Demo/hello.html.twig``
template (or ``AcmeDemoBundle:Demo:hello.html.twig`` if you use the logical name):

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

You might have wondered why the :term:`Bundle` word is used in many names you
have seen so far. All the code you write for your application is organized in
bundles. In Symfony2 speak, a bundle is a structured set of files (PHP files,
stylesheets, JavaScripts, images, ...) that implements a single feature (a
blog, a forum, ...) and which can be easily shared with other developers. As
of now, you have manipulated one bundle, AcmeDemoBundle. You will learn
more about bundles in the :doc:`last chapter of this tutorial</quick_tour/the_architecture>`.

.. _quick-tour-big-picture-environments:

Working with Environments
-------------------------

Now that you have a better understanding of how Symfony2 works, take a closer
look at the bottom of any Symfony2 rendered page. You should notice a small
bar with the Symfony2 logo. This is the "Web Debug Toolbar", and it is a
Symfony2 developer's best friend!

.. image:: /images/quick_tour/web_debug_toolbar.png
   :align: center

But what you see initially is only the tip of the iceberg; click on the
hexadecimal number (the session token) to reveal yet another very useful
Symfony2 debugging tool: the profiler.

.. image:: /images/quick_tour/profiler.png
   :align: center

.. note::

    You can also get more information quickly by hovering over the items
    on the Web Debug Toolbar, or clicking them to go to their respective
    pages in the profiler.

When loaded and enabled (by default in the ``dev`` :ref:`environment<quick-tour-big-picture-environments-intro>`),
the Profiler provides a web interface for a *huge* amount of information recorded
on each request, including logs, a timeline of the request, GET or POST parameters,
security details, database queries and more!
 
Of course, it would be unwise to have these tools enabled when you deploy
your application, so by default, the profiler is not enabled in the ``prod``
environment.

.. _quick-tour-big-picture-environments-intro:

So what *is* an environment? An :term:`Environment` is a simple string (e.g.
``dev`` or ``prod``) that represents a group of configuration that's used
to run your application.

Typically, you put your common configuration in ``config.yml`` and override
where necessary in the configuration for each environment. For example:

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

When you visit the ``app_dev.php`` file in your browser, you're executing
your Symfony application in the ``dev`` environment. To visit your application
in the ``prod`` environment, visit the ``app.php`` file instead. The demo
routes in our application are only available in the ``dev`` environment, but
if those routes were available in the ``prod`` environment, you would be able
to visit them in the ``prod`` environment by going to:

.. code-block:: text

    http://localhost/app.php/demo/hello/Fabien

If instead of using php's built-in webserver, you use Apache with ``mod_rewrite``
enabled and take advantage of the ``.htaccess`` file Symfony2 provides
in ``web/``, you can even omit the ``app.php`` part of the URL. The default
``.htaccess`` points all requests to the ``app.php`` front controller:

.. code-block:: text

    http://localhost/demo/hello/Fabien

.. note::

    Note that the two URLs above are provided here only as **examples** of
    how a URL looks like when the ``prod`` front controller is used. If you
    actually try them in an out-of-the-box installation of *Symfony Standard Edition*,
    you will get a 404 error since the *AcmeDemoBundle* is enabled only in
    the ``dev`` environment and its routes imported from ``app/config/routing_dev.yml``.

For more details on environments, see ":ref:`Environments & Front Controllers<page-creation-environments>`".

Final Thoughts
--------------

Congratulations! You've had your first taste of Symfony2 code. That wasn't so
hard, was it? There's a lot more to explore, but you should already see how
Symfony2 makes it really easy to implement web sites better and faster. If you
are eager to learn more about Symfony2, dive into the next section:
":doc:`The View<the_view>`".

.. _Symfony2 Standard Edition:      http://symfony.com/download
.. _Symfony in 5 minutes:           http://symfony.com/symfony-in-five-minutes
.. _`Composer`:                     http://getcomposer.org/
.. _Separation of Concerns:         http://en.wikipedia.org/wiki/Separation_of_concerns
.. _annotations in controllers:     http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html#annotations-for-controllers
.. _Twig:                           http://twig.sensiolabs.org/
.. _`Symfony Installation Page`:    http://symfony.com/download
