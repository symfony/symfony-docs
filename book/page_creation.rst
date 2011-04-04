.. index::
   single: Page creation

Creating Pages in Symfony2
==========================

Creating a new page in Symfony2 is a simple two-step process:

* *Create a route*: A route defines the URI (e.g. ``/about``) for your
  page and specifies a controller (a PHP function) that Symfony2 should
  execute when the URI of an incoming request matches the route pattern;

* *Create a controller*: A controller is a PHP function that takes the incoming
  request and transforms it into a Symfony2 ``Response`` object.

We love this simple approach because it matches the way that the Web works.
Every interaction on the Web is initiated by an HTTP request. The job of
your application is simply to interpret the request and return the appropriate
HTTP response. Symfony2 follows this philosophy and provides you with tools
and conventions to keep your application organized as it grows in users and
complexity.

.. index::
   single: Page creation; Example

The "Hello Symfony!" Page
-------------------------

Let's start with a spin off of the classic "Hello World!" application. When
we're finished, the user will be able to get a personal greeting by going
to the following URL:

.. code-block:: text

    http://localhost/app_dev.php/hello/Symfony

In reality, you'll be able to replace ``Symfony`` with any other name to be
greeted. To create the page, we'll go through the simple two-step process.

.. note::

    The tutorial assumes that you've already downloaded Symfony2 and configured
    your webserver. The above URL assumes that ``localhost`` points to the
    ``web`` directory of your new Symfony2 project. For detailed information
    on this process, see the :doc:`Installing Symfony2</book/installation>`.
    
    If you've downloaded the `Symfony Standard Edition`_, delete the
    ``src/Acme/DemoBundle`` directory, as you'll recreate it in this chapter.

Create the Bundle
~~~~~~~~~~~~~~~~~

Before you begin, you'll need to create a *bundle*. In Symfony2, a bundle
is like a plugin, except that all of the code in your application will live
inside a bundle.

A bundle is nothing more than a directory (with a PHP namespace) that houses
everything related to a specific feature (see :ref:`page-creation-bundles`).
To create a bundle called ``AcmeDemoBundle``, run the following command:

.. code-block:: text

    php app/console init:bundle "Acme\DemoBundle" src

Next, be sure that the ``Acme`` namespace is loaded by adding the following
to the ``app/autoload.php`` file (see the :ref:`Autoloading sidebar<autoloading-introduction-sidebar>`):

.. code-block:: php

        $loader->registerNamespaces(array(
            'Acme'                         => __DIR__.'/../src',
            // ...
        ));

Finally, initialize the bundle by adding it to the ``registerBundles`` method
of the ``AppKernel`` class:

.. code-block:: php

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

Create the Route
~~~~~~~~~~~~~~~~

By default, the routing configuration file in a Symfony2 application is
located at ``app/config/routing.yml``. Like all configuration in Symfony2,
you can also choose to use XML or PHP out of the box to configure routes:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        homepage:
            pattern:  /
            defaults: { _controller: FrameworkBundle:Default:index }

        hello:
            resource: "@AcmeDemoBundle/Resources/config/routing.yml"

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="homepage" pattern="/">
                <default key="_controller">FrameworkBundle:Default:index</default>
            </route>

            <import resource="@AcmeDemoBundle/Resources/config/routing.xml" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('homepage', new Route('/', array(
            '_controller' => 'FrameworkBundle:Default:index',
        )));
        $collection->addCollection($loader->import("@AcmeDemoBundle/Resources/config/routing.php"));

        return $collection;

The first few lines of the routing configuration file define which code to
call when the user requests the "``/``" resource (the homepage) and serves
as an example of routing configuration you may see in this file. More interesting
is the last part, which imports another routing configuration file located
inside the ``AcmeDemoBundle``:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/routing.yml
        hello:
            pattern:  /hello/{name}
            defaults: { _controller: AcmeDemoBundle:Hello:index }

    .. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="hello" pattern="/hello/{name}">
                <default key="_controller">AcmeDemoBundle:Hello:index</default>
            </route>
        </routes>

    .. code-block:: php

        // src/Acme/DemoBundle/Resources/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('hello', new Route('/hello/{name}', array(
            '_controller' => 'AcmeDemoBundle:Hello:index',
        )));

        return $collection;

The routing consists of two basic pieces: the ``pattern``, which is the URI
that will match this route, and a ``defaults`` array, which specifies the
controller that should be executed. The placeholder syntax in the pattern
(``{name}``) is a wildcard. It means that ``/hello/Ryan``, ``/hello/Fabien``
or any other similar URI will match this route. The ``{name}`` placeholder
parameter will also be passed to our controller so that we can use its value
to personally greet the user.

.. note::

  The routing system has many more great features for creating flexible
  and powerful URI structures in your application. For more details, see
  the chapter all about :doc:`Routing </book/routing>`.

Create the Controller
~~~~~~~~~~~~~~~~~~~~~

When a URI such as ``/hello/Ryan`` is handled by the application, the ``hello``
route is matched and the ``AcmeDemoBundle:Hello:index`` controller is executed
by the framework. The second step of the page-creation process is to create
this controller.

In reality, a controller is nothing more than a PHP method that you create
and Symfony executes. This is where the custom application code uses information
from the request to build and prepare the resource being requested. Except
in some advanced cases, the end product of a controller is always the same:
a Symfony2 ``Response`` object::

    // src/Acme/DemoBundle/Controller/HelloController.php

    namespace Acme\DemoBundle\Controller;
    use Symfony\Component\HttpFoundation\Response;

    class HelloController
    {
        public function indexAction($name)
        {
            return new Response('<html><body>Hello '.$name.'!</body></html>');
        }
    }

The controller is simple: it creates a new ``Response`` object, whose first
argument is the content that should be used for the response (a small HTML
page in this case).

Congratulations! After creating only a route and a controller, you already
have a fully-functional page! If you've setup everything correctly, your
application should greet you::

    http://localhost/app_dev.php/hello/Ryan

An optional, but common, third step in the process is to create a template.

.. note::

   Controllers are the main entry point for your code and a key ingredient
   when creating pages. Much more information can be found in the
   :doc:`Controller Chapter </book/controller>`.

Create the Template
~~~~~~~~~~~~~~~~~~~

Templates allows us to move all of the presentation (e.g. HTML code) into
a separate file and reuse different portions of the page layout. Instead
of writing the HTML inside the controller, use a template instead::

    // src/Acme/DemoBundle/Controller/HelloController.php

    namespace Acme\DemoBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class HelloController extends Controller
    {
        public function indexAction($name)
        {
            return $this->render('AcmeDemoBundle:Hello:index.html.twig', array('name' => $name));

            // render a PHP template instead
            // return $this->render('AcmeDemoBundle:Hello:index.html.php', array('name' => $name));
        }
    }

.. note::

   In order to use the ``render()`` method, you must extend the
   :class:`Symfony\Bundle\FrameworkBundle\Controller\Controller` class, which
   adds shortcuts for tasks that are common inside controllers.

The ``render()`` method creates a ``Response`` object filled with the content
of the given, rendered template. Like any other controller, you will ultimately
return that ``Response`` object.

Notice that there are two different examples for rendering the template.
By default, Symfony2 supports two different templating languages: classic
PHP templates and the succinct but powerful `Twig`_ templates. Don't be alarmed
- you're free to choose either or even both in the same project.

The controller renders the ``AcmeDemoBundle:Hello:index.html.twig`` template,
which uses the following naming convention:

*BundleName*:*ControllerName*:*TemplateName*

In this case, ``AcmeDemoBundle`` is the bundle name, ``Hello`` is the
controller, and ``index.html.twig`` the template:

.. configuration-block::

    .. code-block:: jinja
       :linenos:

        {# src/Acme/DemoBundle/Resources/views/Hello/index.html.twig #}
        {% extends '::layout.html.twig' %}

        {% block body %}
            Hello {{ name }}!
        {% endblock %}

    .. code-block:: php

        <!-- src/Acme/DemoBundle/Resources/views/Hello/index.html.php -->
        <?php $view->extend('::layout.html.php') ?>

        Hello <?php echo $view->escape($name) ?>!

Let's step through the Twig template line-by-line:

* *line 2*: The ``extends`` token defines a parent template. The template
  explicitly defines a layout file inside of which it will be placed.

* *line 4*: The ``block`` token says that everything inside should be placed
  inside a block called ``body``. As we'll see, it's the responsibility
  of the parent template (``layout.html.twig``) to ultimately render the
  block called ``body``.

The parent template, ``::layout.html.twig``, is missing both the bundle and controller
portions of its name (hence the double colon (``::``) at the beginning). This
means that the template lives outside of the bundles and in the ``app`` directory:

.. configuration-block::

    .. code-block:: html+jinja

        {% app/Resources/views/layout.html.twig %}
        <!DOCTYPE html>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>{% block title %}Hello Application{% endblock %}</title>
            </head>
            <body>
                {% block body %}{% endblock %}
            </body>
        </html>

    .. code-block:: php

        <!-- app/Resources/views/layout.html.php -->
        <!DOCTYPE html>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title><?php $view['slots']->output('title', 'Hello Application') ?></title>
            </head>
            <body>
                <?php $view['slots']->output('_content') ?>
            </body>
        </html>

The base template file defines the HTML layout and renders the ``body`` block
that we defined in the ``index.html.twig`` template. It also renders a ``title``
block, which we could choose to define in the ``index.html.twig`` template.
Since we did not define the ``title`` block in the child template, it defaults
to "Hello Application".

Templates are a powerful way to render and organize the content for your
page and can be HTML markup, CSS code, or anything else that the controller
may need to return. But the templating engine is simply a means to an end.
The goal is that each controller returns a ``Response`` object. Templates
are a powerful, but optional, tool for creating the content of a ``Response``
object.

.. index::
   single: Directory Structure

The Directory Structure
-----------------------

After just a few short sections, you already understand the philosophy behind
creating and rendering pages in Symfony2. You've also already begun to see
how Symfony2 projects are structured and organized. By the end of this section,
you'll know where to find and put different types of files and why.

Though perfectly flexible, by default, each Symfony :term:`application` has
the same basic and recommended directory structure:

* ``app/``: This directory contains the application configuration;

* ``src/``: All the project PHP code is stored under this directory;

* ``vendor/``: Any vendor libraries are placed here by convention;

* ``web/``: This is the web root directory and contains any publicly accessible files;

The Web Directory
~~~~~~~~~~~~~~~~~

The web root directory is the home of all public and static files such as
images, stylesheets, and JavaScript files. It is also where each
:term:`front controller` lives::

    // web/app.php
    require_once __DIR__.'/../app/bootstrap.php';
    require_once __DIR__.'/../app/AppKernel.php';

    use Symfony\Component\HttpFoundation\Request;

    $kernel = new AppKernel('prod', false);
    $kernel->handle(Request::createFromGlobals())->send();

The front controller file (``app.php`` in this example) is the actual PHP
file that's executed when using a Symfony2 application and its job is to
use a Kernel class, ``AppKernel``, to bootstrap the application.

.. tip::

   Having a front controller means different and more flexible URLs than
   are used in a typical flat PHP application. When using a front controller,
   URLs are formatted in the following way:

       http://localhost/app.php/hello/Ryan

   The front controller, ``app.php``, is executed and the URI ``/hello/Ryan``
   is routed internally using the routing configuration. By using Apache
   ``mod_rewrite`` rules, you can force the ``app.php`` file to be executed without
   needing to specify it in the URL::

    http://localhost/hello/Ryan

Though front controllers are essential in handling every request, you'll
rarely need to modify or even think about them. We'll mention them again
briefly in the `Environments`_ section.

The Application (``app``) Directory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As you saw in the front controller, the ``AppKernel`` class is the main entry
point of the application and is responsible for all configuration. As such,
it is stored in the ``app/`` directory.

This class must implement three methods that define everything that Symfony
needs to know about your application. You don't even need to worry about
these methods when starting - Symfony fills them in for you with sensible
defaults.

* ``registerBundles()``: Returns an array of all bundles needed to run the
  application (see `The Bundle System`_);

* ``registerContainerConfiguration()``: Loads the main application configuration
  resource file (see the `Application Configuration`_ section);

* ``registerRootDir()``: Returns the root app directory (defaults to ``app/``).

In day-to-day development, you'll mostly use the ``app/`` directory to modify
configuration and routing files in the ``app/config/`` directory (see
`Application Configuration`_). It also contains the application cache
directory (``app/cache``), a logging directory (``app/logs``) and a directory
for application-level resource files (``app/Resources``). You'll learn more
about each of these directories in later chapters.

.. _autoloading-introduction-sidebar:

.. sidebar:: Autoloading

    When bootstrapping, a special file - ``app/autoload.php`` - is included.
    This file is responsible for autoloading all the files stored in the
    ``src/`` and ``vendor/`` directories.

    Because of the autoloader, you never need to worry about using ``include``
    or ``require`` statements. Instead, Symfony2 uses the namespace of a class
    to determine its location and automatically includes the file on your
    behalf the instance you need a class::
    
        $loader->registerNamespaces(array(
            'Acme'                         => __DIR__.'/../src',
            // ...
        ));
    
    With this configuration, Symfony2 will look inside the ``src`` directory
    for any class in the ``Acme`` namespace (your pretend company's namespace).
    For autoloading to work, the class name and path to the file must follow
    the same pattern:

    .. code-block:: text

        Class Name:
            Acme\DemoBundle\Controller\HelloController
        Path:
            src/Acme/DemoBundle/Controller/HelloController.php

    The ``app/autoload.php`` configures the autoloader to look for different
    PHP namespaces in different directories and can be customized as necessary.
    For more information on autoloading, see :doc:`How to autoload Classes</cookbook/tools/autoloader>`.

The Source (``src``) Directory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Put simply, the ``src/`` directory contains all of the actual PHP code that
runs the application. In fact, when developing, the vast majority of work
will likely be done inside this directory. By default, the ``src/`` directory
is empty. When you begin development, you'll being to populate the directory
with *bundles* that contain your application code.

But what exactly is a :term:`bundle`?

.. _page-creation-bundles:

The Bundle System
-----------------

A bundle is similar to a plugin in other software, but even better. The key
difference is that *everything* is a bundle in Symfony2, including both the
core framework functionality and the code written for your application.
Bundles are first-class citizens in Symfony2. This gives you the flexibility
to use pre-built features packaged in `third-party bundles`_ or to distribute
your own bundles. It makes it easy to pick and choose which features to enable
in your application and to optimize them the way you want.

.. note::

   While we'll cover the basics here, an entire chapter is devoted to the topic
   of :doc:`/book/bundles`.

A bundle is simply a structured set of files within a directory that
implement a single feature. You might create a BlogBundle, a ForumBundle
or a bundle for user management (many of these exist already as open source
bundles). Each directory contains everything related to that feature, including
PHP files, templates, stylesheets, Javascripts, tests and anything else.
Every aspect of a feature exists in a bundle and every feature lives in a
bundle.

An application is made up of bundles as defined in the ``registerBundles()``
method of the ``AppKernel`` class::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),

            // enable third-party bundles
            new Symfony\Bundle\ZendBundle\ZendBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            //new Symfony\Bundle\DoctrineMigrationsBundle\DoctrineMigrationsBundle(),
            //new Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle(),

            // register your bundles
            new Acme\DemoBundle\AcmeDemoBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

With the ``registerBundles()`` method, you have total control over which bundles
are used by your application (including the core Symfony bundles).

.. tip::

   A bundle can live *anywhere* as long as it can be autoloaded by Symfony2.
   For example, if ``AcmeDemoBundle`` lives inside the ``src/Acme``
   directory, be sure that the ``Acme`` namespace has been added to the
   ``app/autoload.php`` file and mapped to the ``src`` directory.

Creating a Bundle
~~~~~~~~~~~~~~~~~

To show you how simple the bundle system is, let's create a new bundle called
``AcmeTestBundle`` and enable it.

First, create a ``src/Acme/TestBundle/`` directory and add a new file
called ``AcmeTestBundle.php``::

    // src/Acme/TestBundle/AcmeTestBundle.php
    namespace Acme\TestBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class AcmeTestBundle extends Bundle
    {
    }

.. tip::

   The name ``AcmeTestBundle`` follows the :ref:`Bundle naming conventions<bundles-naming-conventions>`.

This empty class is the only piece we need to create our new bundle. Though
commonly empty, this class is powerful and can be used to customize the behavior
of the bundle.

Now that we've created our bundle, we need to enable it via the ``AppKernel``
class::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            // ...

            // register your bundles
            new Acme\TestBundle\AcmeTestBundle(),
        );

        // ...

        return $bundles;
    }

And while it doesn't do anything yet, ``AcmeTestBundle`` is now ready to
be used.

And as easy as this is, Symfony also provides a command-line interface for
generating a basic bundle skeleton::

    php app/console init:bundle "Acme\TestBundle" src

The bundle skeleton generates with a basic controller, template and routing
resource that can be customized. We'll talk more about Symfony2's command-line
tools later.

.. tip::

   Whenever creating a new bundle or using a third-party bundle, be sure
   to always make sure that the bundle has been enabled in ``registerBundles()``.

Bundle Directory Structure
~~~~~~~~~~~~~~~~~~~~~~~~~~

The directory structure of a bundle is simple and flexible. By default, the
bundle system follows a set of conventions that help to keep code consistent
between all Symfony2 bundles. Let's take a look at ``AcmeDemoBundle``, as it
contains some of the most common elements of a bundle:

* *Controller/* contains the controllers of the bundle (e.g. ``HelloController.php``);

* *Resources/config/* houses configuration, including routing configuration
  (e.g. ``routing.yml``);

* *Resources/views/* templates organized by controller name (e.g. ``Hello/index.html.twig``);

* *Resources/public/* contains web assets (images, stylesheets, etc) and is
  copied or symbolically linked into the project ``web/`` directory;

* *Tests/* holds all tests for the bundle.

A bundle can be as small or large as the feature it implements. It contains
only the files you need and nothing else.

As you move through the book, you'll learn how to persist objects to a database,
create and validate forms, create translations for your application, write
tests and much more. Each of these has their own place and role within the
bundle.

Application Configuration
-------------------------

An application consists of a collection of bundles representing all of the
features and capabilities of your application. Each bundle can be customized
via configuration files written in YAML, XML or PHP. By default, the main
configuration file lives in the ``app/config/`` directory and is called
either ``config.yml``, ``config.xml`` or ``config.php`` depending on which
format you prefer:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            charset:       UTF-8
            error_handler: null
            csrf_protection:
                enabled: true
                secret: xxxxxxxxxx
            router:        { resource: "%kernel.root_dir%/config/routing.yml" }
            validation:    { enabled: true, annotations: true }
            templating:    { engines: ['twig'] } #assets_version: SomeVersionScheme
            session:
                default_locale: en
                lifetime:       3600
                auto_start:     true

        # Twig Configuration
        twig:
            debug:            %kernel.debug%
            strict_variables: %kernel.debug%

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config charset="UTF-8" error-handler="null" cache-warmer="false">
            <framework:router resource="%kernel.root_dir%/config/routing.xml" cache-warmer="true" />
            <framework:validation enabled="true" annotations="true" />
            <framework:session default-locale="en" lifetime="3600" auto-start="true" />
            <framework:templating assets-version="SomeVersionScheme" cache-warmer="true">
                <framework:engine id="twig" />
            </framework:templating>
            <framework:csrf-protection enabled="true" secret="xxxxxxxxxx" />
        </framework:config>

        <!-- Twig Configuration -->
        <twig:config debug="%kernel.debug%" strict-variables="%kernel.debug%" cache-warmer="true" />

    .. code-block:: php

        $container->loadFromExtension('framework', array(
            'charset'         => 'UTF-8',
            'error_handler'   => null,
            'csrf-protection' => array('enabled' => true, 'secret' => 'xxxxxxxxxx'),
            'router'          => array('resource' => '%kernel.root_dir%/config/routing.php'),
            'validation'      => array('enabled' => true, 'annotations' => true),
            'templating'      => array(
                'engines' => array('twig'),
                #'assets_version' => "SomeVersionScheme",
            ),
            'session' => array(
                'default_locale' => "en",
                'lifetime'       => "3600",
                'auto_start'     => true,
            ),
        ));

        // Twig Configuration
        $container->loadFromExtension('twig', array(
            'debug'            => '%kernel.debug%',
            'strict_variables' => '%kernel.debug%',
        ));

.. note::

   We'll show you how to choose exactly which file/format to load in the
   next section `Environments`_.

Each top-level entry like ``framework`` or ``twig`` defines the configuration
for a particular bundle. For example, the ``framework`` key defines the configuration
for the core Symfony ``FrameworkBundle`` and includes configuration for the
routing, templating, and other core systems.

For now, don't worry about the specific configuration options in each section.
The configuration file ships with sensible defaults. As you read more and
explore each part of Symfony2, you'll learn about the specific configuration
options of each feature.

.. sidebar:: Configuration Formats

    Throughout the chapters, all configuration examples will be shown in all
    three formats (YAML, XML and PHP). Each has its own advantages and
    disadvantages. The choice of which to use is up to you:

    * *YAML*: Simple, clean and readable;

    * *XML*: More powerful than YAML at times and supports IDE autocompletion;

    * *PHP*: Very powerful but less readable than standard configuration formats.

.. index::
   single: Environments; Introduction

.. _environments-summary:

Environments
------------

An application can run in various environments. The different environments
share the same PHP code (apart from the front controller), but can have completely
different configurations. For instance, a ``dev`` environment will log warnings
and errors, while a ``prod`` environment will only log errors. Some files
are rebuilt on each request in the ``dev`` environment, but cached in the
``prod`` environment. All environments live together on the same machine.

A Symfony2 project generally begins with three environments (``dev``, ``test``
and ``prod``), though creating new environments is easy. You can view your
application in different environments simply by changing the front controller
in your browser. To see the application in the ``dev`` environment, access
the application via the development front controller::

    http://localhost/app_dev.php/hello/Ryan

If you'd like to see how your application will behave in the production environment,
call the ``prod`` front controller instead::

    http://localhost/app.php/hello/Ryan

.. note::

   If you open the ``web/app.php`` file, you'll find that it's configured explicitly
   to use the ``prod`` environment::
   
       $kernel = new AppCache(new AppKernel('prod', false));
   
   You can create a new front controller for a new environment by copying
   this file and changing ``prod`` to some other value.

Since the ``prod`` environment is optimized for speed; the configuration,
routing and Twig templates are compiled into flat PHP classes and cached.
When viewing changes in the ``prod`` environment, you'll need to clear these
cached files and allow them to rebuild::

    rm -rf app/cache/*

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
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

We already know that the ``.yml`` extension can be changed to ``.xml`` or
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

        web_profiler:
            toolbar: true
            intercept_redirects: true

        zend:
            logger:
                priority: debug
                path:     %kernel.logs_dir%/%kernel.environment%.log

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->
        <imports>
            <import resource="config.xml" />
        </imports>

        <framework:config>
            <framework:router resource="%kernel.root_dir%/config/routing_dev.xml" />
            <framework:profiler only-exceptions="false" />
        </framework:config>

        <webprofiler:config
            toolbar="true"
            intercept-redirects="true"
        />

        <zend:config>
            <zend:logger priority="info" path="%kernel.logs_dir%/%kernel.environment%.log" />
        </zend:config>

    .. code-block:: php

        // app/config/config_dev.php
        $loader->import('config.php');

        $container->loadFromExtension('framework', array(
            'router'   => array('resource' => '%kernel.root_dir%/config/routing_dev.php'),
            'profiler' => array('only-exceptions' => false),
        ));

        $container->loadFromExtension('web_profiler', array(
            'toolbar' => true,
            'intercept-redirects' => true,
        ));

        $container->loadFromExtension('zend', array(
            'logger' => array(
                'priority' => 'info',
                'path'     => '%kernel.logs_dir%/%kernel.environment%.log',
            ),
        ));

The ``imports`` key is similar to a PHP ``include`` statement and guarantees
that the main configuration file (``config.yml``) is loaded first. The rest
of the file tweaks the default configuration for increased logging and other
settings conducive to a development environment.

Both the ``prod`` and ``test`` environments follow the same model: each environment
imports the base configuration file and then modifies its configuration values
to fit the needs of the specific environment.

Summary
-------

Congratulations! You've now seen every fundamental aspect of Symfony2 and have
hopefully discovered how easy and flexible it can be. And while there are
*a lot* of features still to come, be sure to keep the following basic points
in mind:

* creating a page is a three-step process involving a **route**, a **controller**
  and (optionally) a **template**.

* each application should contain only four directories: **web/** (web assets and
  the front controllers), **app/** (configuration), **src/** (your bundles),
  and **vendor/** (third-party code);

* each feature in Symfony2 (including the Symfony2 framework core) is organized
  into a *bundle*, which is a structured set of files for that feature;

* the **configuration** for each bundle lives in the ``app/config`` directory
  and can be specified in YAML, XML or PHP;

* each **environment** is accessible via a different front controller (e.g.
  ``app.php`` and ``app_dev.php``) and loads a different configuration file.

From here, each chapter will introduce you to more and more powerful tools
and advanced concepts. The more you know about Symfony2, the more you'll
appreciate the flexibility of its architecture and the power it gives you
to rapidly develop applications.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/controller/service`
* :doc:`/cookbook/templating/PHP`
* :doc:`/cookbook/tools/autoloader`
* :doc:`/cookbook/symfony1`

.. _`Twig`: http://www.twig-project.org
.. _`third-party bundles`: http://symfony2bundles.org/
.. _`Symfony Standard Edition`: http://symfony.com/download
