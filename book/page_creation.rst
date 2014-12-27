.. index::
   single: Page creation

.. _creating-pages-in-symfony2:

Creating Pages in Symfony
=========================

Creating a new page in Symfony is a simple three-step process:

* *Create a bundle*: A bundle is a structured set of files within a directory 
  that implement a single feature. Each bundle is registered with the kernel;

* *Create a route*: A route defines the URL (e.g. ``/about``) to your page
  and specifies a controller (which is a PHP function) that Symfony should
  execute when the URL of an incoming request matches the route path;

* *Create a controller*: A controller is a PHP function that takes the incoming
  request and transforms it into the Symfony ``Response`` object that's
  returned to the user.

There is a beautiful simplicity in this approach based on routers and controllers. 
It matches the way that the Web works. Every interaction on the Web is initiated 
by an HTTP request. The job of your application is simply to interpret the request 
and return the appropriate HTTP response.

Symfony follows this philosophy and provides you with tools and conventions
to keep your application organized as it grows in users and complexity.

.. index::
   single: Page creation; Example

The "Random Number" Page
------------------------

In this chapter, you'll develop an application that can generate random numbers.
When you're finished, the user will be able to get a random number between ``1``
and the upper limit set by the URL:

.. code-block:: text

    http://localhost/app_dev.php/random/100

Actually, you'll be able to replace ``100`` with any other number to generate
numbers up to that upper limit. To create the page, follow the simple two-step
process.

.. note::

    The tutorial assumes that you've already downloaded Symfony and configured
    your webserver. The above URL assumes that ``localhost`` points to the
    ``web`` directory of your new Symfony project. For detailed information
    on this process, see the documentation on the web server you are using.
    Here are some relevant documentation pages for the web server you might be using:

    * For Apache HTTP Server, refer to `Apache's DirectoryIndex documentation`_
    * For Nginx, refer to `Nginx HttpCoreModule location documentation`_

Step 1: Create the Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Before you begin, you'll need to create a *bundle*. In Symfony, a :term:`bundle`
is like a plugin, except that all of the code in your application will live
inside a bundle.

A bundle is nothing more than a directory that houses everything related
to a specific feature, including PHP classes, configuration, and even stylesheets
and JavaScript files (see :ref:`page-creation-bundles`).

Depending on the way you installed Symfony, you may already have a bundle called
``AcmeDemoBundle``. Browse the ``src/`` directory of your project and check
if there is a ``DemoBundle/`` directory inside an ``Acme/`` directory. If those
directories already exist, skip the rest of this section and go directly to
create the route.

To create a bundle called ``AcmeDemoBundle`` (an example bundle that you'll
build in this chapter), run the following command and follow the on-screen
instructions (use all of the default options):

.. code-block:: bash

    $ php app/console generate:bundle --namespace=Acme/DemoBundle --format=yml

Behind the scenes, a directory is created for the bundle at ``src/Acme/DemoBundle``.
A line is also automatically added to the ``app/AppKernel.php`` file so that
the bundle is registered with the kernel::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            ...,
            new Acme\DemoBundle\AcmeDemoBundle(),
        );
        // ...

        return $bundles;
    }

Now that you have a bundle set up, you can begin building your application
inside the bundle.

Step 2: Create the Route
~~~~~~~~~~~~~~~~~~~~~~~~

By default, the routing configuration file in a Symfony application is
located at ``app/config/routing.yml``. Like all configuration in Symfony,
you can also choose to use XML or PHP out of the box to configure routes.

If you look at the main routing file, you'll see that Symfony already added
an entry when you generated the ``AcmeDemoBundle``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        acme_website:
            resource: "@AcmeDemoBundle/Resources/config/routing.yml"
            prefix:   /

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <import
                resource="@AcmeDemoBundle/Resources/config/routing.xml"
                prefix="/" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;

        $acmeDemo = $loader->import('@AcmeDemoBundle/Resources/config/routing.php');
        $acmeDemo->addPrefix('/');

        $collection = new RouteCollection();
        $collection->addCollection($acmeDemo);

        return $collection;

This entry is pretty basic: it tells Symfony to load routing configuration
from the ``Resources/config/routing.yml`` (``routing.xml`` or ``routing.php``
in the XML and PHP code example respectively) file that lives inside the ``AcmeDemoBundle``.
This means that you place routing configuration directly in ``app/config/routing.yml``
or organize your routes throughout your application, and import them from here.

.. note::

    You are not limited to load routing configurations that are of the same
    format. For example, you could also load a YAML file in an XML configuration
    and vice versa.

Now that the ``routing.yml`` file from the bundle is being imported, add
the new route that defines the URL of the page that you're about to create:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/routing.yml
        random:
            path:     /random/{limit}
            defaults: { _controller: AcmeDemoBundle:Random:index }

    .. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="random" path="/random/{limit}">
                <default key="_controller">AcmeDemoBundle:Random:index</default>
            </route>
        </routes>

    .. code-block:: php

        // src/Acme/DemoBundle/Resources/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('random', new Route('/random/{limit}', array(
            '_controller' => 'AcmeDemoBundle:Random:index',
        )));

        return $collection;

The routing consists of two basic pieces: the ``path``, which is the URL
that this route will match, and a ``defaults`` array, which specifies the
controller that should be executed. The placeholder syntax in the path
(``{limit}``) is a wildcard. It means that ``/random/10``, ``/random/327``
or any other similar URL will match this route. The ``{limit}`` placeholder
parameter will also be passed to the controller so that you can use its value
to generate the proper random number.

.. note::

  The routing system has many more great features for creating flexible
  and powerful URL structures in your application. For more details, see
  the chapter all about :doc:`Routing </book/routing>`.

Step 3: Create the Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a URL such as ``/random/10`` is handled by the application, the ``random``
route is matched and the ``AcmeDemoBundle:Random:index`` controller is executed
by the framework. The second step of the page-creation process is to create
that controller.

The controller - ``AcmeDemoBundle:Random:index`` is the *logical* name of
the controller, and it maps to the ``indexAction`` method of a PHP class
called ``Acme\DemoBundle\Controller\RandomController``. Start by creating this
file inside your ``AcmeDemoBundle``::

    // src/Acme/DemoBundle/Controller/RandomController.php
    namespace Acme\DemoBundle\Controller;

    class RandomController
    {
    }

In reality, the controller is nothing more than a PHP method that you create
and Symfony executes. This is where your code uses information from the request
to build and prepare the resource being requested. Except in some advanced
cases, the end product of a controller is always the same: a Symfony ``Response``
object.

Create the ``indexAction`` method that Symfony will execute when the ``random``
route is matched::

    // src/Acme/DemoBundle/Controller/RandomController.php
    namespace Acme\DemoBundle\Controller;

    use Symfony\Component\HttpFoundation\Response;

    class RandomController
    {
        public function indexAction($limit)
        {
            return new Response('<html><body>Number: '.rand(1, $limit).'</body></html>');
        }
    }

The controller is simple: it creates a new ``Response`` object, whose first
argument is the content that should be used in the response (a small HTML
page in this example).

Congratulations! After creating only a route and a controller, you already
have a fully-functional page! If you've setup everything correctly, your
application should generate a random number for you:

.. code-block:: text

    http://localhost/app_dev.php/random/10

.. note::

   Controllers are the main entry point for your code and a key ingredient
   when creating pages. Much more information can be found in the
   :doc:`Controller Chapter </book/controller>`.

Optional Step 4: Create the Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

An optional, but common, fourth step in the basic page-creation process is to 
build a template.

Templates allow you to move all of the presentation (e.g. HTML, XML, CSV,
LaTeX ...) into a separate file and reuse different portions of the page
layout. Rather than writing the HTML inside the controller, render a template:

.. code-block:: php
    :linenos:

    // src/Acme/DemoBundle/Controller/RandomController.php
    namespace Acme\DemoBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class RandomController extends Controller
    {
        public function indexAction($limit)
        {
            $number = rand(1, $limit);

            return $this->render(
                'AcmeDemoBundle:Random:index.html.twig',
                array('number' => $number)
            );

            // render a PHP template instead
            // return $this->render(
            //     'AcmeDemoBundle:Random:index.html.php',
            //     array('number' => $number)
            // );
        }
    }

.. note::

   In order to use the :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::render`
   method, your controller must extend the
   :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class,
   which adds shortcuts for tasks that are common inside controllers. This
   is done in the above example by adding the ``use`` statement on line 4
   and then extending ``Controller`` on line 6.

The ``render()`` method creates a ``Response`` object filled with the content
of the given, rendered template. Like any other controller, you will ultimately
return that ``Response`` object.

Notice that there are two different examples for rendering the template.
By default, Symfony supports two different templating languages: classic
PHP templates and the succinct but powerful `Twig`_ templates. Don't be
alarmed - you're free to choose either or even both in the same project.

The controller renders the ``AcmeDemoBundle:Random:index.html.twig`` template,
which uses the following naming convention:

    **BundleName**:**ControllerName**:**TemplateName**

This is the *logical* name of the template, which is mapped to a physical
location using the following convention.

    **/path/to/BundleName**/Resources/views/**ControllerName**/**TemplateName**

In this case, ``AcmeDemoBundle`` is the bundle name, ``Random`` is the
controller, and ``index.html.twig`` the template:

.. configuration-block::

    .. code-block:: jinja
       :linenos:

        {# src/Acme/DemoBundle/Resources/views/Random/index.html.twig #}
        {% extends '::base.html.twig' %}

        {% block body %}
            Number: {{ number }}
        {% endblock %}

    .. code-block:: html+php

        <!-- src/Acme/DemoBundle/Resources/views/Random/index.html.php -->
        <?php $view->extend('::base.html.php') ?>

        Number: <?php echo $view->escape($number) ?>

Step through the Twig template line-by-line:

* *line 2*: The ``extends`` token defines a parent template. The template
  explicitly defines a layout file inside of which it will be placed.

* *line 4*: The ``block`` token says that everything inside should be placed
  inside a block called ``body``. As you'll see, it's the responsibility
  of the parent template (``base.html.twig``) to ultimately render the
  block called ``body``.

The parent template, ``::base.html.twig``, is missing both the **BundleName**
and **ControllerName** portions of its name (hence the double colon (``::``)
at the beginning). This means that the template lives outside of the bundles
and in the ``app`` directory:

.. configuration-block::

    .. code-block:: html+jinja

        {# app/Resources/views/base.html.twig #}
        <!DOCTYPE html>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>{% block title %}Welcome!{% endblock %}</title>
                {% block stylesheets %}{% endblock %}
                <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
            </head>
            <body>
                {% block body %}{% endblock %}
                {% block javascripts %}{% endblock %}
            </body>
        </html>

    .. code-block:: html+php

        <!-- app/Resources/views/base.html.php -->
        <!DOCTYPE html>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title><?php $view['slots']->output('title', 'Welcome!') ?></title>
                <?php $view['slots']->output('stylesheets') ?>
                <link rel="shortcut icon" href="<?php echo $view['assets']->getUrl('favicon.ico') ?>" />
            </head>
            <body>
                <?php $view['slots']->output('_content') ?>
                <?php $view['slots']->output('javascripts') ?>
            </body>
        </html>

The base template file defines the HTML layout and renders the ``body`` block
that you defined in the ``index.html.twig`` template. It also renders a ``title``
block, which you could choose to define in the ``index.html.twig`` template.
Since you did not define the ``title`` block in the child template, it defaults
to "Welcome!".

Templates are a powerful way to render and organize the content for your
page. A template can render anything, from HTML markup, to CSS code, or anything
else that the controller may need to return.

In the lifecycle of handling a request, the templating engine is simply
an optional tool. Recall that the goal of each controller is to return a
``Response`` object. Templates are a powerful, but optional, tool for creating
the content for that ``Response`` object.
