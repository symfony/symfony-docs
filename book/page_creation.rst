.. index::
   single: Page creation

.. _creating-pages-in-symfony2:

Creating Pages in Symfony
=========================

Creating a new page in Symfony is a simple two-step process:

* *Create a route*: A route defines the URL (e.g. ``/about``) to your page
  and specifies a controller (which is a PHP function) that Symfony should
  execute when the URL of an incoming request matches the route path;

* *Create a controller*: A controller is a PHP function that takes the incoming
  request and transforms it into the Symfony ``Response`` object that's
  returned to the user.

This simple approach is beautiful because it matches the way that the Web works.
Every interaction on the Web is initiated by an HTTP request. The job of
your application is simply to interpret the request and return the appropriate
HTTP response.

Symfony follows this philosophy and provides you with tools and conventions
to keep your application organized as it grows in users and complexity.

.. index::
   single: Page creation; Environments & Front Controllers

.. _page-creation-environments:

Environments & Front Controllers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Every Symfony application runs within an :term:`environment`. An environment
is a specific set of configuration and loaded bundles, represented by a string.
The same application can be run with different configurations by running the
application in different environments. Symfony comes with three environments
defined — ``dev``, ``test`` and ``prod`` — but you can create your own as well.

Environments are useful by allowing a single application to have a dev environment
built for debugging and a production environment optimized for speed. You might
also load specific bundles based on the selected environment. For example,
Symfony comes with the WebProfilerBundle (described below), enabled only
in the ``dev`` and ``test`` environments.

Symfony comes with two web-accessible front controllers: ``app_dev.php``
provides the ``dev`` environment, and ``app.php`` provides the ``prod`` environment.
All web accesses to Symfony normally go through one of these front controllers.
(The ``test`` environment is normally only used when running unit tests, and so
doesn't have a dedicated front controller. The console tool also provides a
front controller that can be used with any environment.)

When the front controller initializes the kernel, it provides two parameters:
the environment, and also whether the kernel should run in debug mode.
To make your application respond faster, Symfony maintains a cache under the
``app/cache/`` directory. When debug mode is enabled (such as ``app_dev.php``
does by default), this cache is flushed automatically whenever you make changes
to any code or configuration. When running in debug mode, Symfony runs
slower, but your changes are reflected without having to manually clear the
cache.

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

Before you begin: Create the Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Before you begin, you'll need to create a *bundle*. In Symfony, a :term:`bundle`
is like a plugin, except that all the code in your application will live
inside a bundle.

A bundle is nothing more than a directory that houses everything related
to a specific feature, including PHP classes, configuration, and even stylesheets
and JavaScript files (see :ref:`page-creation-bundles`).

Depending on the way you installed Symfony, you may already have a bundle called
AcmeDemoBundle. Browse the ``src/`` directory of your project and check
if there is a ``DemoBundle/`` directory inside an ``Acme/`` directory. If those
directories already exist, skip the rest of this section and go directly to
create the route.

To create a bundle called AcmeDemoBundle (a play bundle that you'll
build in this chapter), run the following command and follow the on-screen
instructions (use all the default options):

.. code-block:: bash

    $ php app/console generate:bundle --namespace=Acme/DemoBundle --format=yml

Behind the scenes, a directory is created for the bundle at ``src/Acme/DemoBundle``.
A line is also automatically added to the ``app/AppKernel.php`` file so that
the bundle is registered with the kernel::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Acme\DemoBundle\AcmeDemoBundle(),
        );
        // ...

        return $bundles;
    }

Now that you have a bundle setup, you can begin building your application
inside the bundle.

Step 1: Create the Route
~~~~~~~~~~~~~~~~~~~~~~~~

By default, the routing configuration file in a Symfony application is
located at ``app/config/routing.yml``. Like all configuration in Symfony,
you can also choose to use XML or PHP out of the box to configure routes.

If you look at the main routing file, you'll see that Symfony already added an
entry when you generated the AcmeDemoBundle:

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
in the XML and PHP code example respectively) file that lives inside the
AcmeDemoBundle. This means that you place routing configuration directly in
``app/config/routing.yml`` or organize your routes throughout your application,
and import them from here.

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

Step 2: Create the Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a URL such as ``/random/10`` is handled by the application, the ``random``
route is matched and the ``AcmeDemoBundle:Random:index`` controller is executed
by the framework. The second step of the page-creation process is to create
that controller.

The controller - ``AcmeDemoBundle:Random:index`` is the *logical* name of
the controller, and it maps to the ``indexAction`` method of a PHP class
called ``Acme\DemoBundle\Controller\RandomController``. Start by creating this
file inside your AcmeDemoBundle::

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
            return new Response(
                '<html><body>Number: '.rand(1, $limit).'</body></html>'
            );
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

.. _book-page-creation-prod-cache-clear:

.. tip::

    You can also view your app in the "prod" :ref:`environment <environments-summary>`
    by visiting:

    .. code-block:: text

        http://localhost/app.php/random/10

    If you get an error, it's likely because you need to clear your cache
    by running:

    .. code-block:: bash

        $ php app/console cache:clear --env=prod --no-debug

An optional, but common, third step in the process is to create a template.

.. note::

   Controllers are the main entry point for your code and a key ingredient
   when creating pages. Much more information can be found in the
   :doc:`Controller Chapter </book/controller>`.

Optional Step 3: Create the Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Templates allow you to move all the presentation code (e.g. HTML) into
a separate file and reuse different portions of the page layout. Instead
of writing the HTML inside the controller, render a template instead:

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

In this case, AcmeDemoBundle is the bundle name, ``Random`` is the
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
at the beginning). This means that the template lives outside of the bundle
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
                <link rel="shortcut icon"
                    href="<?php echo $view['assets']->getUrl('favicon.ico') ?>" />
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

.. index::
   single: Directory Structure

The Directory Structure
-----------------------

After just a few short sections, you already understand the philosophy behind
creating and rendering pages in Symfony. You've also already begun to see
how Symfony projects are structured and organized. By the end of this section,
you'll know where to find and put different types of files and why.

Though entirely flexible, by default, each Symfony :term:`application` has
the same basic and recommended directory structure:

``app/``
    This directory contains the application configuration.

``src/``
    All the project PHP code is stored under this directory.

``vendor/``
    Any vendor libraries are placed here by convention.

``web/``
    This is the web root directory and contains any publicly accessible files.

.. seealso::

    You can easily override the default directory structure. See
    :doc:`/cookbook/configuration/override_dir_structure` for more
    information.

.. _the-web-directory:

The Web Directory
~~~~~~~~~~~~~~~~~

The web root directory is the home of all public and static files including
images, stylesheets, and JavaScript files. It is also where each
:term:`front controller` lives::

    // web/app.php
    require_once __DIR__.'/../app/bootstrap.php.cache';
    require_once __DIR__.'/../app/AppKernel.php';

    use Symfony\Component\HttpFoundation\Request;

    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();
    $kernel->handle(Request::createFromGlobals())->send();

The front controller file (``app.php`` in this example) is the actual PHP
file that's executed when using a Symfony application and its job is to
use a Kernel class, ``AppKernel``, to bootstrap the application.

.. tip::

    Having a front controller means different and more flexible URLs than
    are used in a typical flat PHP application. When using a front controller,
    URLs are formatted in the following way:

    .. code-block:: text

        http://localhost/app.php/random/10

    The front controller, ``app.php``, is executed and the "internal:" URL
    ``/random/10`` is routed internally using the routing configuration.
    By using Apache ``mod_rewrite`` rules, you can force the ``app.php`` file
    to be executed without needing to specify it in the URL:

    .. code-block:: text

        http://localhost/random/10

Though front controllers are essential in handling every request, you'll
rarely need to modify or even think about them. They'll be mentioned again
briefly in the `Environments`_ section.

The Application (``app``) Directory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As you saw in the front controller, the ``AppKernel`` class is the main entry
point of the application and is responsible for all configuration. As such,
it is stored in the ``app/`` directory.

This class must implement two methods that define everything that Symfony
needs to know about your application. You don't even need to worry about
these methods when starting - Symfony fills them in for you with sensible
defaults.

``registerBundles()``
    Returns an array of all bundles needed to run the application (see
    :ref:`page-creation-bundles`).

``registerContainerConfiguration()``
    Loads the main application configuration resource file (see the
    `Application Configuration`_ section).

In day-to-day development, you'll mostly use the ``app/`` directory to modify
configuration and routing files in the ``app/config/`` directory (see
`Application Configuration`_). It also contains the application cache
directory (``app/cache``), a log directory (``app/logs``) and a directory
for application-level resource files, such as templates (``app/Resources``).
You'll learn more about each of these directories in later chapters.

.. _autoloading-introduction-sidebar:

.. sidebar:: Autoloading

    When Symfony is loading, a special file - ``vendor/autoload.php`` - is
    included. This file is created by Composer and will autoload all
    application files living in the ``src/`` folder as well as all
    third-party libraries mentioned in the ``composer.json`` file.

    Because of the autoloader, you never need to worry about using ``include``
    or ``require`` statements. Instead, Composer uses the namespace of a class
    to determine its location and automatically includes the file on your
    behalf the instant you need a class.

    The autoloader is already configured to look in the ``src/`` directory
    for any of your PHP classes. For autoloading to work, the class name and
    path to the file have to follow the same pattern:

    .. code-block:: text

        Class Name:
            Acme\DemoBundle\Controller\RandomController
        Path:
            src/Acme/DemoBundle/Controller/RandomController.php

The Source (``src``) Directory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Put simply, the ``src/`` directory contains all the actual code (PHP code,
templates, configuration files, stylesheets, etc) that drives *your* application.
When developing, the vast majority of your work will be done inside one or
more bundles that you create in this directory.

But what exactly is a :term:`bundle`?

.. _page-creation-bundles:

The Bundle System
-----------------

A bundle is similar to a plugin in other software, but even better. The key
difference is that *everything* is a bundle in Symfony, including both the
core framework functionality and the code written for your application.
Bundles are first-class citizens in Symfony. This gives you the flexibility
to use pre-built features packaged in `third-party bundles`_ or to distribute
your own bundles. It makes it easy to pick and choose which features to enable
in your application and to optimize them the way you want.

.. note::

   While you'll learn the basics here, an entire cookbook entry is devoted
   to the organization and best practices of :doc:`bundles </cookbook/bundles/best_practices>`.

A bundle is simply a structured set of files within a directory that implement
a single feature. You might create a BlogBundle, a ForumBundle or
a bundle for user management (many of these exist already as open source
bundles). Each directory contains everything related to that feature, including
PHP files, templates, stylesheets, JavaScripts, tests and anything else.
Every aspect of a feature exists in a bundle and every feature lives in a
bundle.

An application is made up of bundles as defined in the ``registerBundles()``
method of the ``AppKernel`` class::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Acme\DemoBundle\AcmeDemoBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

With the ``registerBundles()`` method, you have total control over which bundles
are used by your application (including the core Symfony bundles).

.. tip::

   A bundle can live *anywhere* as long as it can be autoloaded (via the
   autoloader configured at ``app/autoload.php``).

Creating a Bundle
~~~~~~~~~~~~~~~~~

The Symfony Standard Edition comes with a handy task that creates a fully-functional
bundle for you. Of course, creating a bundle by hand is pretty easy as well.

To show you how simple the bundle system is, create a new bundle called
AcmeTestBundle and enable it.

.. tip::

    The ``Acme`` portion is just a dummy name that should be replaced by
    some "vendor" name that represents you or your organization (e.g.
    ABCTestBundle for some company named ``ABC``).

Start by creating a ``src/Acme/TestBundle/`` directory and adding a new file
called ``AcmeTestBundle.php``::

    // src/Acme/TestBundle/AcmeTestBundle.php
    namespace Acme\TestBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class AcmeTestBundle extends Bundle
    {
    }

.. tip::

   The name AcmeTestBundle follows the standard
   :ref:`Bundle naming conventions <bundles-naming-conventions>`. You could
   also choose to shorten the name of the bundle to simply TestBundle by naming
   this class TestBundle (and naming the file ``TestBundle.php``).

This empty class is the only piece you need to create the new bundle. Though
commonly empty, this class is powerful and can be used to customize the behavior
of the bundle.

Now that you've created the bundle, enable it via the ``AppKernel`` class::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            // register your bundle
            new Acme\TestBundle\AcmeTestBundle(),
        );
        // ...

        return $bundles;
    }

And while it doesn't do anything yet, AcmeTestBundle is now ready to be used.

And as easy as this is, Symfony also provides a command-line interface for
generating a basic bundle skeleton:

.. code-block:: bash

    $ php app/console generate:bundle --namespace=Acme/TestBundle

The bundle skeleton generates with a basic controller, template and routing
resource that can be customized. You'll learn more about Symfony's command-line
tools later.

.. tip::

   Whenever creating a new bundle or using a third-party bundle, always make
   sure the bundle has been enabled in ``registerBundles()``. When using
   the ``generate:bundle`` command, this is done for you.

Bundle Directory Structure
~~~~~~~~~~~~~~~~~~~~~~~~~~

The directory structure of a bundle is simple and flexible. By default, the
bundle system follows a set of conventions that help to keep code consistent
between all Symfony bundles. Take a look at AcmeDemoBundle, as it contains some
of the most common elements of a bundle:

``Controller/``
    Contains the controllers of the bundle (e.g. ``RandomController.php``).

``DependencyInjection/``
    Holds certain dependency injection extension classes, which may import service
    configuration, register compiler passes or more (this directory is not
    necessary).

``Resources/config/``
    Houses configuration, including routing configuration (e.g. ``routing.yml``).

``Resources/views/``
    Holds templates organized by controller name (e.g. ``Hello/index.html.twig``).

``Resources/public/``
    Contains web assets (images, stylesheets, etc) and is copied or symbolically
    linked into the project ``web/`` directory via the ``assets:install`` console
    command.

``Tests/``
    Holds all tests for the bundle.

A bundle can be as small or large as the feature it implements. It contains
only the files you need and nothing else.

As you move through the book, you'll learn how to persist objects to a database,
create and validate forms, create translations for your application, write
tests and much more. Each of these has their own place and role within the
bundle.

Application Configuration
-------------------------

An application consists of a collection of bundles representing all the
features and capabilities of your application. Each bundle can be customized
via configuration files written in YAML, XML or PHP. By default, the main
configuration file lives in the ``app/config/`` directory and is called
either ``config.yml``, ``config.xml`` or ``config.php`` depending on which
format you prefer:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        imports:
            - { resource: parameters.yml }
            - { resource: security.yml }

        framework:
            secret:          "%secret%"
            router:          { resource: "%kernel.root_dir%/config/routing.yml" }
            # ...

        # Twig Configuration
        twig:
            debug:            "%kernel.debug%"
            strict_variables: "%kernel.debug%"

        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd
                http://symfony.com/schema/dic/twig
                http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <imports>
                <import resource="parameters.yml" />
                <import resource="security.yml" />
            </imports>

            <framework:config secret="%secret%">
                <framework:router resource="%kernel.root_dir%/config/routing.xml" />
                <!-- ... -->
            </framework:config>

            <!-- Twig Configuration -->
            <twig:config debug="%kernel.debug%" strict-variables="%kernel.debug%" />

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        $this->import('parameters.yml');
        $this->import('security.yml');

        $container->loadFromExtension('framework', array(
            'secret' => '%secret%',
            'router' => array(
                'resource' => '%kernel.root_dir%/config/routing.php',
            ),
            // ...
        ));

        // Twig Configuration
        $container->loadFromExtension('twig', array(
            'debug'            => '%kernel.debug%',
            'strict_variables' => '%kernel.debug%',
        ));

        // ...

.. note::

   You'll learn exactly how to load each file/format in the next section
   `Environments`_.

Each top-level entry like ``framework`` or ``twig`` defines the configuration
for a particular bundle. For example, the ``framework`` key defines the configuration
for the core Symfony FrameworkBundle and includes configuration for the
routing, templating, and other core systems.

For now, don't worry about the specific configuration options in each section.
The configuration file ships with sensible defaults. As you read more and
explore each part of Symfony, you'll learn about the specific configuration
options of each feature.

.. sidebar:: Configuration Formats

    Throughout the chapters, all configuration examples will be shown in all
    three formats (YAML, XML and PHP). Each has its own advantages and
    disadvantages. The choice of which to use is up to you:

    * *YAML*: Simple, clean and readable (learn more about YAML in
      ":doc:`/components/yaml/yaml_format`");

    * *XML*: More powerful than YAML at times and supports IDE autocompletion;

    * *PHP*: Very powerful but less readable than standard configuration formats.

Default Configuration Dump
~~~~~~~~~~~~~~~~~~~~~~~~~~

You can dump the default configuration for a bundle in YAML to the console using
the ``config:dump-reference`` command. Here is an example of dumping the default
FrameworkBundle configuration:

.. code-block:: bash

    $ app/console config:dump-reference FrameworkBundle

The extension alias (configuration key) can also be used:

.. code-block:: bash

    $ app/console config:dump-reference framework

.. note::

    See the cookbook article: :doc:`/cookbook/bundles/extension` for
    information on adding configuration for your own bundle.

.. index::
   single: Environments; Introduction

.. _environments-summary:

Environments
------------

An application can run in various environments. The different environments
share the same PHP code (apart from the front controller), but use different
configuration. For instance, a ``dev`` environment will log warnings and
errors, while a ``prod`` environment will only log errors. Some files are
rebuilt on each request in the ``dev`` environment (for the developer's convenience),
but cached in the ``prod`` environment. All environments live together on
the same machine and execute the same application.

A Symfony project generally begins with three environments (``dev``, ``test``
and ``prod``), though creating new environments is easy. You can view your
application in different environments simply by changing the front controller
in your browser. To see the application in the ``dev`` environment, access
the application via the development front controller:

.. code-block:: text

    http://localhost/app_dev.php/random/10

If you'd like to see how your application will behave in the production environment,
call the ``prod`` front controller instead:

.. code-block:: text

    http://localhost/app.php/random/10

Since the ``prod`` environment is optimized for speed; the configuration,
routing and Twig templates are compiled into flat PHP classes and cached.
When viewing changes in the ``prod`` environment, you'll need to clear these
cached files and allow them to rebuild:

.. code-block:: bash

    $ php app/console cache:clear --env=prod --no-debug

.. note::

   If you open the ``web/app.php`` file, you'll find that it's configured explicitly
   to use the ``prod`` environment::

       $kernel = new AppKernel('prod', false);

   You can create a new front controller for a new environment by copying
   this file and changing ``prod`` to some other value.

.. note::

    The ``test`` environment is used when running automated tests and cannot
    be accessed directly through the browser. See the :doc:`testing chapter </book/testing>`
    for more details.

.. index::
   single: Environments; Configuration

Environment Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~

The ``AppKernel`` class is responsible for actually loading the configuration
file of your choice::

    // app/AppKernel.php
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(
            __DIR__.'/config/config_'.$this->getEnvironment().'.yml'
        );
    }

You already know that the ``.yml`` extension can be changed to ``.xml`` or
``.php`` if you prefer to use either XML or PHP to write your configuration.
Notice also that each environment loads its own configuration file. Consider
the configuration file for the ``dev`` environment.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        imports:
            - { resource: config.yml }

        framework:
            router:   { resource: "%kernel.root_dir%/config/routing_dev.yml" }
            profiler: { only_exceptions: false }

        # ...

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <imports>
                <import resource="config.xml" />
            </imports>

            <framework:config>
                <framework:router resource="%kernel.root_dir%/config/routing_dev.xml" />
                <framework:profiler only-exceptions="false" />
            </framework:config>

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config_dev.php
        $loader->import('config.php');

        $container->loadFromExtension('framework', array(
            'router' => array(
                'resource' => '%kernel.root_dir%/config/routing_dev.php',
            ),
            'profiler' => array('only-exceptions' => false),
        ));

        // ...

The ``imports`` key is similar to a PHP ``include`` statement and guarantees
that the main configuration file (``config.yml``) is loaded first. The rest
of the file tweaks the default configuration for increased logging and other
settings conducive to a development environment.

Both the ``prod`` and ``test`` environments follow the same model: each environment
imports the base configuration file and then modifies its configuration values
to fit the needs of the specific environment. This is just a convention,
but one that allows you to reuse most of your configuration and customize
just pieces of it between environments.

Summary
-------

Congratulations! You've now seen every fundamental aspect of Symfony and have
hopefully discovered how easy and flexible it can be. And while there are
*a lot* of features still to come, be sure to keep the following basic points
in mind:

* Creating a page is a three-step process involving a **route**, a **controller**
  and (optionally) a **template**;

* Each project contains just a few main directories: ``web/`` (web assets and
  the front controllers), ``app/`` (configuration), ``src/`` (your bundles),
  and ``vendor/`` (third-party code) (there's also a ``bin/`` directory that's
  used to help updated vendor libraries);

* Each feature in Symfony (including the Symfony framework core) is organized
  into a *bundle*, which is a structured set of files for that feature;

* The **configuration** for each bundle lives in the ``Resources/config``
  directory of the bundle and can be specified in YAML, XML or PHP;

* The global **application configuration** lives in the ``app/config``
  directory;

* Each **environment** is accessible via a different front controller (e.g.
  ``app.php`` and ``app_dev.php``) and loads a different configuration file.

From here, each chapter will introduce you to more and more powerful tools
and advanced concepts. The more you know about Symfony, the more you'll
appreciate the flexibility of its architecture and the power it gives you
to rapidly develop applications.

.. _`Twig`: http://twig.sensiolabs.org
.. _`third-party bundles`: http://knpbundles.com
.. _`Symfony Standard Edition`: http://symfony.com/download
.. _`Apache's DirectoryIndex documentation`: http://httpd.apache.org/docs/current/mod/mod_dir.html
.. _`Nginx HttpCoreModule location documentation`: http://wiki.nginx.org/HttpCoreModule#location
