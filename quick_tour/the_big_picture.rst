The Big Picture
===============

Start using Symfony2 in 10 minutes! This tutorial walks you through some
of the most important concepts behind Symfony2. It explains how to get started
quickly by showing you the structure of a sample project.

If you've used a web framework before, you should feel right at home with
Symfony2. If not, welcome to a whole new way of developing web applications!

.. index::
   pair: Standard Distribution; Download

Downloading and Installing Symfony2
-----------------------------------

First, check that you have installed and configured a webserver (such as
Apache) with PHP 5.3.2 or higher.

Ready? Let's start by downloading Symfony2. To get started even faster, we are
going to use the "Symfony2 Standard Distribution". This is a preconfigured Symfony2 project
that includes some simple controllers and their required libraries. The great
advantage of the standard distribution over other methods of installation is you can start
experimenting with Symfony2 immediately.

Download the `standard distribution`_sandbox, and unpack it in your root web directory. You
should now have a ``symfony-standard/`` directory::

    www/ <- your web root directory
        symfony-standard/ <- the unpacked archive
            app/
                cache/
                config/
                logs/
            src/
                Acme/
                    DemoBundle/
                        Controller/
                        DependencyInjection/
                        Form/
                        Resources/
                        Tests/
                        Twig/
            vendor/
                symfony/
                doctrine/
                ...
            web/

.. index::
   single: Installation; Check

Checking the Configuration
--------------------------

Symfony2 comes with a visual configuration wizard to help you to configure your application
and avoid some headaches that come from web server or PHP misconfiguration. Use the following
url to start installation:

    http://localhost/symfony-standard/web/config.php

The wizard will help you to fix the eventual issues : missing PHP extensions, write permissions
on cache and logs directories, and so on. So, read carefully the several messages and correct
any outstanding issues.

Once all the issues are fixed, you can just click on `Bypass configuration and go to the Welcome
page`, or request your first "real" Symfony2 webpage:

    http://localhost/symfony-standard/web/app_dev.php/

Symfony2 should congratulate you for your hard work so far!

Creating your first Application
-------------------------------

The "Standard distribution" comes with a simple Hello World ":term:`application`" that we'll
use to learn more about Symfony2. Go to the following URL to be greeted by
Symfony2 (replace Fabien with your first name):

    http://localhost/symfony-standard/web/app_dev.php/demo/hello/Fabien

What's going on here? Let's dissect the URL:

.. index:: Front Controller

* ``app_dev.php``: This is a "front controller". It is the unique entry point
  of the application and it responds to all user requests;

* ``/demo/hello/Fabien``: This is the virtual path to the resource the user wants
  to access.

Your responsibility as a developer is to write the code that maps the user
request (``/hello/Fabien``) to the resource associated with it (``Hello
Fabien!``).

.. index::
   single: Configuration

Configuration
~~~~~~~~~~~~~

Symfony2 configuration files can be written in PHP, XML or `YAML`_. The
different types are compatible and may be used interchangeably within an
application.

.. tip::

    The standard distribution defaults to YAML, but you can easily switch to XML 
    or PHP by opening the ``app/AppKernel.php`` file and modifying the
    ``registerContainerConfiguration`` method.

.. index::
   single: Routing
   pair: Configuration; Routing

Routing
~~~~~~~

Symfony2 routes the request to your code by using a configuration file. Here
are a few examples of the routing configuration file for our application:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        _welcome:
            pattern:  /
            defaults: { _controller: AcmeDemoBundle:Welcome:index }

        _demo_secured:
            resource: "@AcmeDemoBundle/Controller/SecuredController.php"
            type:     annotation

        _demo:
            resource: "@AcmeDemoBundle/Controller/DemoController.php"
            type:     annotation
            prefix:   /demo


The first few lines of the routing configuration file define the code that
is executed when the user requests the resource specified by the pattern
"``/``" (i.e. the homepage). Here, it executes the ``index`` method of
the ``Welcome`` controller inside the ``AcmeDemoBundle``.

Take a look at the two latest directives of the configuration file: Symfony2 can
include routing information from other routing configuration files by using
the ``import`` directive. In this case, we want to import the routing configuration
from ``AcmeDemoBundle``. A bundle is like a plugin that has added power and
we'll talk more about them later. For now, let's look at the routing configuration
that we've imported:

.. configuration-block::

    .. code-block:: php-annotations
               
        // src/Acme/DemoBundle/Resources/Controller/DemoController.php
        namespace Acme\DemoBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        /* ... */

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
        }


As you can see, the "``/hello/{name}``" resource pattern is mapped to a controller,
referenced by the ``@extra:Route`` annotation. The string enclosed in curly brackets
(``{name}``) is a placeholder and defines an argument that will be available
in the controller.

.. index::
   single: Controller
   single: MVC; Controller

Controllers
~~~~~~~~~~~

The controller defines actions to handle users requests and prepares responses
(often in HTML).

.. code-block:: php-annotations
   :linenos:

    // src/Acme/DemoBundle/Resources/Controller/DemoController.php

    namespace Acme\DemoBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    
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
    }

The code is pretty straightforward but let's explain it line by line:

* *line 3*: Symfony2 takes advantage of new PHP 5.3 namespacing features,
  and all controllers should be properly namespaced. As you can see, the
  namespace has a correlation to the actual file location. In this example,
  the controller lives in the bundle named ``AcmeDemoBundle``, which forms the
  first part of the imported routing resource.

* *line 7*: The controller name is the latest part of the imported routing 
  resource. It extends the built-in ``Controller`` class, which provides 
  useful shortcuts (as we will see later in this tutorial). The ``Controller`` 
  resides in ``Symfony\Bundle\FrameworkBundle\Controller\Controller`` which 
  we defined on line 5.

* *line 10*: The standard distribution uses the ``FrameworkExtraBundle`` that
  allows to define the routing rules as annotations. In this case, we configure
  a routing rule named ``_demo_hello`` that map the pattern ``/hello/{name}`` 
  to this action.

* *line 11*: Thanks to the ``@extra:Template()`` annotation, the framework
  will render automatically the file
  ``src\Sensio\HelloBundle\Resources\views\Hello\index.html.twig`` as the
  template for this action.

* *line 12*: Each controller consists of several actions. As per the routing
  configuration, the hello page is handled by the ``index`` action (thanks to 
  the @extra:Route annotation). This method receives the placeholder values 
  as arguments (``$name`` in our case).

* *line 14*: The action return the variables used in the template file
  ``src\Sensio\AcmdeBundle\Resources\views\Demo\index.html.twig``.

.. tip::

    The `@extra:Route` and `@extra:Template` annotations are provided by the 
    `FrameworkExtraBundle` and are not a part of the core framework.


Bundles
~~~~~~~

But what is a :term:`bundle`? All the code you write in a Symfony2 project is
organized in bundles. In Symfony2 speak, a bundle is a structured set of files
(PHP files, stylesheets, JavaScripts, images, ...) that implements a single
feature (a blog, a forum, ...) and which can be easily shared with other
developers. In our example, we only have one bundle, ``AcmeDemoBundle``.

Templates
~~~~~~~~~

The controller renders the ``AcmeDemoBundle:Demo:index.html.twig`` template. By
default, the standard distribution uses Twig as its template engine but you can also use
traditional PHP templates if you choose.

.. code-block:: jinja

    {# src/Sensio/AcmeBundle/Resources/views/Demo/index.html.twig #}
    {% extends "AcmeDemoBundle::layout.html.twig" %}

    {% block title "Hello " ~ name %}

    {% block content %}
        <h1>Hello {{ name }}!</h1>
    {% endblock %}


Congratulations! You've had your first taste of Symfony2 code and created
your first page. That wasn't so hard, was it? There's a lot more to explore,
but you should already see how Symfony2 makes it really easy to implement
web sites better and faster.

.. index::
   single: Environment
   single: Configuration; Environment

Working with Environments
-------------------------

Now that you have a better understanding of how Symfony2 works, have a closer
look at the bottom of the page; you will notice a small bar with the Symfony2
and PHP logos. This is called the "Web Debug Toolbar" and it is the developer's
best friend. Of course, such a tool must not be displayed when you deploy your
application to production. That's why you will find another front controller in
the ``web/`` directory (``app.php``), optimized for the production environment:

    http://localhost/symfony-standard/web/app.php/demo/hello/Fabien

And if you use Apache with ``mod_rewrite`` enabled, you can even omit the
``app.php`` part of the URL:

    http://localhost/symfony-standard/web/demo/hello/Fabien

Last but not least, on the production servers, you should point your web root
directory to the ``web/`` directory to secure your installation and have an even
better looking URL:

    http://localhost/demo/hello/Fabien

To make the production environment as fast as possible, Symfony2 maintains a
cache under the ``app/cache/`` directory. When you make changes to the code or
configuration, you need to manually remove the cached files. When developing
your application, you should use the development front controller (``app_dev.php``),
which does not use the cache. When using the development front controller,
your changes will appear immediately.

Final Thoughts
--------------

Thanks for trying out Symfony2! By now, you should be able to create your own
simple routes, controllers and templates. As an exercise, try to build
something more useful than the Hello application! If you are eager to
learn more about Symfony2, dive into the next section: "The View".

.. _sandbox: http://symfony.com/download
.. _YAML:    http://www.yaml.org/
