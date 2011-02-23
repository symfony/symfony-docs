.. index::
   single: Page creation

Creating Pages in Symfony2
==========================

Creating a new page in Symfony2 is a simple two-step process:

* *Create a route*: A route defines the URI (e.g. ``/about``) for your
  page and specifies a controller (a PHP function) that Symfony2 should
  execute when the URI of an incoming request matches the route pattern;

* *Create a controller* A controller is a PHP function that takes the incoming
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
we're finished, the user will be able to get a personal gretting by going
to the following URL:

.. code-block:: text

    http://localhost/app_dev.php/hello/Symfony

.. note::

    The tutorial assumes that you've already downloaded Symfony2 and configured
    your webserver. The above URL assumes that ``localhost`` points to the
    ``web`` directory of your new Symfony2 project. For detailed information
    on this process, see the 

In reality, you'll be able to replace ``Ryan`` with any other name to be
greeted. To create the page, we'll go through the three-step process.

Create the Route
~~~~~~~~~~~~~~~~

By default, the routing configuration file in your Symfony application is
located at ``app/config/config.yml``. Like with all configuration in Symfony,
you can also choose to use XML or PHP out of the box to configure your routes:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        homepage:
            pattern:  /
            defaults: { _controller: FrameworkBundle:Default:index }

        hello:
            resource: HelloBundle/Resources/config/routing.yml

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://www.symfony-project.org/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">

            <route id="homepage" pattern="/">
                <default key="_controller">FrameworkBundle:Default:index</default>
            </route>

            <import resource="HelloBundle/Resources/config/routing.xml" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('homepage', new Route('/', array(
            '_controller' => 'FrameworkBundle:Default:index',
        )));
        $collection->addCollection($loader->import("HelloBundle/Resources/config/routing.php"));

        return $collection;

The first few lines of the routing configuration file define which code to
call when the user requests the "``/``" resource and serves just as an example
of routing configuration you may see in this file. More interesting is the last
part, which imports another routing configuration:

.. configuration-block::

    .. code-block:: yaml

        # src/Application/HelloBundle/Resources/config/routing.yml
        hello:
            pattern:  /hello/{name}
            defaults: { _controller: HelloBundle:Hello:index }

    .. code-block:: xml

        <!-- src/Application/HelloBundle/Resources/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://www.symfony-project.org/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">

            <route id="hello" pattern="/hello/{name}">
                <default key="_controller">HelloBundle:Hello:index</default>
            </route>
        </routes>

    .. code-block:: php

        // src/Application/HelloBundle/Resources/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('hello', new Route('/hello/{name}', array(
            '_controller' => 'HelloBundle:Hello:index',
        )));

        return $collection;

The routing consists of two basic pieces: the ``pattern`` it should match and
a ``defaults`` array that specifies the controller that should be executed.
The placeholder syntax in the pattern (``{name}``) is a wildcard. It means
that ``/hello/Ryan``, ``/hello/Fabien`` or any other similar URI will match this
route. Beyond being a flexible, the ``:name`` placeholder parameter will also
be passed to our controller so that we can personally greet the user.

.. note::

  The routing system has many more great features for creating flexible
  URI schemes in your application. For all the details, see the guide
  all about :doc:`Routing </guides/routing>`.

Create the Controller
~~~~~~~~~~~~~~~~~~~~~

When a URI such as ``/hello/Ryan`` is handled by our app, the ``hello``
route is matched and the ``HelloBundle:Hello:index`` controller is
executed by the framework. In reality, controllers are nothing more than
a PHP method that you create and Symfony executes. This is where your custom
application code uses information from the request to build and prepare the
resource being requested. The end product of a controller is always the same: 
a Symfony ``Response`` object::

    // src/Application/HelloBundle/Controller/HelloController.php

    namespace Application\HelloBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class HelloController extends Controller
    {
        public function indexAction($name)
        {
            return $this->createResponse('<html><body>Hello '.$name.'!</body></html>');
        }
    }

The ``createResponse()`` method is a shortcut to creating a ``Response``
object and its first argument is the content for that response (a small
HTML page in this case).

So, we lied a little. After creating only a route and a controller, we already
have a fully-functional page. If you've setup everything correctly, your
application should greet you::

    http://localhost/app_dev.php/hello/Ryan

The third step in the process - creating a template - is totally optional
but commonly used in practice.

.. note::

   Controllers are the main entry point for your code and a key ingredient
   when creating pages. Much more information can be found in the :doc:`Controller Chapter <controller>`.

Create the Template
~~~~~~~~~~~~~~~~~~~

Templates allows us to move all of the presentation (e.g. HTML code) into
a separate file and reuse different portions of the page layout. So, instead
of writing the HTML inside our controller, let's use a template::

    public function indexAction($name)
    {
        return $this->render('HelloBundle:Hello:index.html.twig', array('name' => $name));

        // render a PHP template instead
        // return $this->render('HelloBundle:Hello:index.html.php', array('name' => $name));
    }

The ``render()`` method creates a ``Response`` object filled with the content
of the given, rendered template. Like any other controller, we then return
the ``Response`` object.

Notice that we've included two different examples for rendering the same
template. By default, Symfony support two different templating languages:
the classic PHP templates and the succinct but powerful `Twig`_ templates.
Don't be alarmed - you're free to choose either or even both in the same
project.

The controller renders the ``HelloBundle:Hello:index.html.twig`` template,
which uses the following naming convention:

*BundleName*:*ControllerName*:*TemplateName*

In this case, ``HelloBundle`` is the bundle name, ``Hello`` is the
controller, and ``index.html.twig`` the template:

.. code-block::jinja
   :linenos:

    {# src/Application/HelloBundle/Resources/views/Hello/index.html.twig #}
    {% extends '::layout.html.twig' %}

    {% block body %}
        Hello {{ name }}!
    {% endblock %}

Let's step through the Twig template line-by-line:

* *line 2*: The ``extends`` token defines a parent template. The template
  explicitly defines a layout file inside of which it will be placed.

* *line 4*: The ``block`` token allows an area of content to be assigned
  to a block variable called ``content``. It's the responsibility of the
  parent template (``layout.html.twig``) to render the ``content`` block.

The parent template, ``::layout.html.twig``, is missing both the bundle and controller
portions of its name (hence the double colon (``::``) at the beginning). This
means that the template lives outside of the bundles and in the ``app`` directory:

.. code-block::jinja

    {% app/views/layout.html.twig %}
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>{% block title %}Hello Application{% endblock %}</title>
        </head>
        <body>
            {% block body %}{% endblock %}
        </body>
    </html> 

The base template file defines the HTML layout and renders the ``body`` block
that we defined in the ``index.html.twig`` template. It also renders a ``title``
block (which we could choose to define in the ``index.html.twig`` template) that
defaults to "Hello Application" if it's not defined.

Templates are a powerful way to render and organize the content for your
page and can be HTML markup, CSS code, or anything else that your controller
may need to return. But the templating engine is simply a means to an ends.
The goal is that your controller returns a ``Response`` object. Templates
are a powerful tool for creating the content of the ``Response``.

.. index::
   single: Directory Structure

The Directory Structure
-----------------------

After just a few short sections, you already understand the philosophy behind
creating and rendering pages in Symfony. You've also already begun to see
how Symfony projects are structured and organized. By the end of this section,
you'll know where to find and put different types of files and why.

Though perfectly flexible, by default, each Symfony :term:`application` has
the same basic and recommended directory structure:

* ``app/``: This directory contains the application configuration;

* ``src/``: All the PHP code is stored under this directory;

* ``web/``: This is the web root directory and contains any publicly accessible files.

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
file that's executed when using a Symfony application and its job is to
use a Kernel class, ``AppKernel``, to bootstrap the application.

.. tip::

   Having a front controller means different and more flexible URLs than
   are used in a typical flat PHP application. When using a front controller,
   URLs are formatted in the following way:

       http://localhost/app.php/hello/Ryan

   The front controller, ``app.php``, is executed and the URI ``/hello/Ryan``
   is routed internally using your routing configuration. By using Apache
   ``mod_rewrite`` rules, you can force the ``app.php`` file to be executed without
   needing to specify it in the URL::

    http://localhost/hello/Ryan

Though front controllers are essential to handling every request, you'll
rarely need to modify or even think about them. We'll mention them again
briefly in the `Environments`_ section.

The Application (``app``) Directory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As you saw in the front controller, the ``AppKernel`` class is the main entry
point of the application and is responsible for all configuration. As such,
it is stored in the ``app/`` directory.

This class must implement four methods that define everything that Symfony
needs to know about your application. You don't even need to worry about
these methods when starting - Symfony fills them in for you with sensible
defaults.

* ``registerRootDir()``: Returns the configuration root directory;

* ``registerBundles()``: Returns an array of all bundles needed to run the
  application (see `The Bundle System`_);

* ``registerBundleDirs()``: Maps bundle namespaces to directories;

* ``registerContainerConfiguration()``: Returns the main configuration object
  (See the `Application Configuration`_ section);

In day-to-day development, you'll mostly use the ``app/`` directory to modify
configuration and routing files in the ``app/config/`` directory (See
`Application Configuration`_). It also contains the application cache directory
(``app/cache``), a logging directory (``app/logging``) and a directory for
application-level template files (``app/views``). You'll learn more about
each of these directories in later guides.

.. sidebar:: Autoloading

    The ``AppKernel.php`` file also requires the ``src/autoload.php`` file,
    which is responsible for autoloading all the files stored in the ``src/``
    directory.

    Because of the autoloader, you won't need to worry about using the ``include``
    or ``require`` statements. Instead, Symfony uses the namespace of a class
    to determine its location and automatically include it. For example::

    *class*: Application\HelloBundle\Controller\HelloController
    *path*:  src/Application/HelloBundle/Controller/HelloController.php

    The ``src/autoload.php`` configures the autoloader to look for different
    PHP namespaces in different directories and also supports autoloading
    based off of the PEAR naming `convention`_.

The Source (``src``) Directory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If the ``app`` directory contains the application configuration, the ``src``
directory contains all of the actual PHP code that runs your application.
In fact, when developing, the vast majority of your work will likely be done
inside this directory. By default, the ``src`` directory is broken down into
three subdirectories:

* ``src/Application/`` Contains *your* bundles;

* ``src/Bundle/`` Contains third-party bundles;

* ``src/vendor/`` Contains all vendor libraries, including the Symfony framework
  core (a bundle called ``FrameworkBundle``).

But what exactly is a :term:`bundle`?

The Bundle System
-----------------

A bundle is kind of like a plugin in other software, but even better. The
key difference is that *everything* is a bundle in Symfony, from the core
framework features to the code you write for your application. Bundles are
first-class citizens in Symfony. This gives you the flexibility to use pre-built
features packaged in `third-party bundles`_ or to distribute your own bundles.
It makes it easy to pick and choose which features to enable in your application
and to optimize them the way you want.

.. note::

   While we'll cover the basics here, an entire guide is devoted to the topic
   of :doc:`/guides/bundles`.

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
            new Application\HelloBundle\HelloBundle(),
        );

        if ($this->isDebug()) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

With the ``registerBundles()`` method, you have total control over each bundle
used by your application (including the core Symfony bundles).

Where to Bundles Live?
~~~~~~~~~~~~~~~~~~~~~~

As we have seen in the previous part, an application is made up of bundles
defined in the ``registerBundles()`` method. The ``registerBundleDirs()``
method returns an associative array that maps each bundle namespace to any
valid directory (local or global ones)::

    // app/AppKernel.php
    public function registerBundleDirs()
    {
        return array(
            'Application'     => __DIR__.'/../src/Application',
            'Bundle'          => __DIR__.'/../src/Bundle',
            'Symfony\\Bundle' => __DIR__.'/../src/vendor/symfony/src/Symfony/Bundle',
        );
    }

So, when you reference the ``HelloBundle`` in a controller name or in a template
name, Symfony will look for it under the given directories.

As you develop, you'll create new bundles inside the ``src/Application/``
directory and place `third-party bundles`_ in the ``src/Bundle/`` directory.

Creating a Bundle
~~~~~~~~~~~~~~~~~

To show you how simple the bundle system is, let's create a new bundle called
``MyBundle`` and enable it.

First, create a ``src/Application/MyBundle/`` directory and add a new file
called ``MyBundle.php``::

    // src/Application/MyBundle/MyBundle.php
    namespace Application\MyBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class MyBundle extends Bundle
    {
    }

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
            new Application\MyBundle\MyBundle(),
        );

        // ...

        return $bundles;
    }

And while it doesn't do anything yet, ``MyBundle`` is now ready to be used.

And as easy as this is, Symfony also provides a command-line interface for
generating a basic bundle skeleton::

    ./app/console init:bundle "Application\MyBundle" src

The bundle skeleton generates with a basic controller, template and routing
resource that can be customized. We'll talk more about Symfony's command-line
tools later.

.. tip::

   Whenever creating a new bundle or using a third-party bundle, be sure
   to always make sure that the bundle has been enabled in ``registerBundles()``.

Bundle Directory Structure
~~~~~~~~~~~~~~~~~~~~~~~~~~

The directory structure of a bundle is simple and flexible. By default, the
bundle system follows a set of conventions that help to keep code consistent
between all Symfony bundles. Let's take a look at ``HelloBundle``, as it
contains some of the most common elements of a bundle:

* *Controller/* Contains the controllers of the bundle (e.g. ``HelloController.php``);

* *Resources/config/* Houses configuration, including routing configuration
  (e.g. ``routing.yml``);

* *Resources/views/* Templates organized by controller name (e.g. ``Hello/index.html.twig``);

* *Resources/public/* Contains web assets (images, stylesheets, etc) and is
  copied or symbolically linked into the project ``web/`` directory;

* *Tests/* Holds all tests for the bundle.

A bundle can be as small or large as the feature it implements. It contains
only the files you need and nothing else.

As you move through the book, you'll learn how to persist objects to a database,
create and validate forms, internationalize your application, write tests
and much more. Each of these has their own place and role within the bundle.

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
        app.config:
            charset:       UTF-8
            error_handler: null
            csrf_secret:   xxxxxxxxxx
            router:        { resource: "%kernel.root_dir%/config/routing.yml" }
            validation:    { enabled: true, annotations: true }
            templating:    {} #assets_version: SomeVersionScheme
            session:
                default_locale: en
                lifetime:       3600
                auto_start:     true

        # Twig Configuration
        twig.config:
            debug:            %kernel.debug%
            strict_variables: %kernel.debug%

        ## Doctrine Configuration
        #doctrine.dbal:
        #    dbname:   xxxxxxxx
        #    user:     xxxxxxxx
        #    password: ~
        #doctrine.orm: ~

        ## Swiftmailer Configuration
        #swiftmailer.config:
        #    transport:  smtp
        #    encryption: ssl
        #    auth_mode:  login
        #    host:       smtp.gmail.com
        #    username:   xxxxxxxx
        #    password:   xxxxxxxx

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <app:config csrf-secret="xxxxxxxxxx" charset="UTF-8" error-handler="null">
            <app:router resource="%kernel.root_dir%/config/routing.xml" />
            <app:validation enabled="true" annotations="true" />
            <app:templating assets_version="SomeVersionScheme" />
            <app:session default-locale="en" lifetime="3600" auto-start="true" />
        </app:config>

        <!-- Twig Configuration -->
        <twig:config debug="%kernel.debug%" strict-variables="%kernel.debug%" />

        <!-- Doctrine Configuration -->
        <!--
        <doctrine:dbal dbname="xxxxxxxx" user="xxxxxxxx" password="" />
        <doctrine:orm />
        -->

        <!-- Swiftmailer Configuration -->
        <!--
        <swiftmailer:config
            transport="smtp"
            encryption="ssl"
            auth_mode="login"
            host="smtp.gmail.com"
            username="xxxxxxxx"
            password="xxxxxxxx" />
        -->

    .. code-block:: php

        $container->loadFromExtension('app', 'config', array(
            'charset'       => 'UTF-8',
            'error_handler' => null,
            'csrf-secret'   => 'xxxxxxxxxx',
            'router'        => array('resource' => '%kernel.root_dir%/config/routing.php'),
            'validation'    => array('enabled' => true, 'annotations' => true),
            'templating'    => array(
                #'assets_version' => "SomeVersionScheme",
            ),
            'session' => array(
                'default_locale' => "en",
                'lifetime'       => "3600",
                'auto_start'     => true,
            ),
        ));

        // Twig Configuration
        $container->loadFromExtension('twig', 'config', array(
            'debug'            => '%kernel.debug%',
            'strict_variables' => '%kernel.debug%',
        ));

        // Doctrine Configuration
        /*
        $container->loadFromExtension('doctrine', 'dbal', array(
            'dbname'   => 'xxxxxxxx',
            'user'     => 'xxxxxxxx',
            'password' => '',
        ));
        $container->loadFromExtension('doctrine', 'orm');
        */

        // Swiftmailer Configuration
        /*
        $container->loadFromExtension('swiftmailer', 'config', array(
            'transport'  => "smtp",
            'encryption' => "ssl",
            'auth_mode'  => "login",
            'host'       => "smtp.gmail.com",
            'username'   => "xxxxxxxx",
            'password'   => "xxxxxxxx",
        ));
        */

.. note::

   We'll show you how to choose exactly which file/format to load in the
   next section `Environments`_.

Each top-level entry like ``app.config`` or ``doctrine.orm`` defines the
configuration for a particular bundle. For example, the ``app.config`` key
defines the configuration for the core Symfony ``FrameworkBundle`` and includes
configuration for the routing, templating, and other core systems.

For now, don't worry about the specific configuration options in each section.
The configuration file ships with sensible defaults. As we explore each part
of Symfony, we'll cover its specific configuration options in detail.

.. sidebar:: Configuration Formats

    Throughout the book, we'll continue to show all configuration examples in
    all three formats. Each has their own advantages and disadvantages - the
    choice is up to you:

    * *YAML*: Simple, clean and readable;

    * *XML*: More powerful than YAML at times and supports validation and IDE
      autocompletion;

    * *PHP*: Very powerful but less readable than standard configuration formats.

.. index::
   single: Environments

.. _environments-summary:

Environments
------------

An application can run in various environments. The different environments
share the same PHP code (apart from the front controller), but can have completely
different configurations. For instance, a ``dev`` environment will log alerts
and errors, while a ``prod`` environment will only log errors. Some files
are rebuilt on each request in the ``dev`` environment, but cached in the
``prod`` environment. All environments can live together on the same machine.

A Symfony project generally begins with three environments (``dev``, ``test``
and ``prod``), though creating new environments is easy. You can view your
application in different environments simply by changing the front controller
in your browser. To see the application in the ``dev`` environment, access
the application via the development front controller::

    http://localhost/app_dev.php/hello/Ryan

If you'd like to see how your application will behave in the production environment,
call the ``prod`` front controller instead::

    http;//localhost/app.php/hello/Ryan

Since the ``prod`` environment is optimized for speed, the configuration,
routing and Twig templates are compiled into flat PHP classes and cached.
When viewing changes in the ``prod`` environment, you'll need to clear these
cached files and allow them to rebuild::

    rm -rf app/cache/*

.. note::

    The ``test`` environment is used when running automated tests and cannot
    be accessed directly through the browser. See the :doc:`Testing Chapter <testing>`
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
Notice also that each environment loads its own configuration file. Let's
look at the configuration file for the ``dev`` environment.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        imports:
            - { resource: config.yml }

        app.config:
            router:   { resource: "%kernel.root_dir%/config/routing_dev.yml" }
            profiler: { only_exceptions: false }

        webprofiler.config:
            toolbar: true
            intercept_redirects: true

        zend.config:
            logger:
                priority: debug
                path:     %kernel.logs_dir%/%kernel.environment%.log

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->
        <imports>
            <import resource="config.xml" />
        </imports>

        <app:config>
            <app:router resource="%kernel.root_dir%/config/routing_dev.xml" />
            <app:profiler only-exceptions="false" />
        </app:config>

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

        $container->loadFromExtension('app', 'config', array(
            'router'   => array('resource' => '%kernel.root_dir%/config/routing_dev.php'),
            'profiler' => array('only-exceptions' => false),
        ));

        $container->loadFromExtension('webprofiler', 'config', array(
            'toolbar' => true,
            'intercept-redirects' => true,
        ));

        $container->loadFromExtension('zend', 'config', array(
            'logger' => array(
                'priority' => 'info',
                'path'     => '%kernel.logs_dir%/%kernel.environment%.log',
            ),
        ));

The ``imports`` key is similar to a PHP ``include`` statement and guarantees
that the main configuration file (``config.yml``) is loaded first. The rest
of the file tweaks the default configuration for increased logging and other
settings condusive to a development environment.

Both the ``prod`` and ``test`` environments follow the same model. Each environment
imports the base configuration file and then modifies its defaults to fit
the needs of the environment.

In the ``dev`` environment, the logging and debugging settings are all enabled,
since maintenance is more important than performance. On the contrary, the
``prod`` environment has settings optimized for performance by default, so
the production configuration turns off many features. A good rule of thumb
is to navigate in the ``dev`` environment until you are satisfied with the
feature you are working on, and then switch to the ``prod`` environment to
check its speed.

Summary
-------

Congratulations! You've now seen every fundamental aspect of Symfony and have
hopefully discovered how easy and flexible it can be. And while there are
*a lot* of features still to come, be sure to refer back to the following
basic points:

* Creating a page is a three-step process involving a **route**, a **controller**
  and (optionally) a **template**.

* Each application contain only three directories: **web/** (web assets and
  the front controllers), **app/** (configuration) and **src/** (PHP code
  and bundles). 

* Each feature in Symfony (including the Symfony framework core) is organized
  into a *bundle*, which is a structured set of files for that feature.

* The **configuration** for each bundle lives in the ``app/config`` directory
  and can be specified in YAML, XML or PHP.

* Each **environment** is accessible via a different front controller (e.g.
  ``app.php`` and ``app_dev.php``) and loads a different configuration file.

Now that you understand the fundamental concepts of Symfony, each guide
will introduce you to more and more powerful tools and advanced concepts.
The more you know about Symfony, the more you'll appreciate the flexibility
of its architecture.

.. _`Twig`: http://www.twig-project.org
.. _convention: http://pear.php.net/
.. _`third-party bundles`: http://symfony2bundles.org/
