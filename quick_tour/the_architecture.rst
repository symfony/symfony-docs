The Architecture
================

You are my hero! Who would have thought that you would still be here after the
first three parts? Your efforts will be well rewarded soon. The first three
parts didn't look too deeply at the architecture of the framework. Because it
makes Symfony2 stand apart from the framework crowd, let's dive into the
architecture now.

Understanding the Directory Structure
-------------------------------------

The directory structure of a Symfony2 :term:`application` is rather flexible,
but the directory structure of the *Standard Edition* distribution reflects
the typical and recommended structure of a Symfony2 application:

* ``app/``:    The application configuration;
* ``src/``:    The project's PHP code;
* ``vendor/``: The third-party dependencies;
* ``web/``:    The web root directory.

The ``web/`` Directory
~~~~~~~~~~~~~~~~~~~~~~

The web root directory is the home of all public and static files like images,
stylesheets, and JavaScript files. It is also where each :term:`front controller`
lives::

    // web/app.php
    require_once __DIR__.'/../app/bootstrap.php.cache';
    require_once __DIR__.'/../app/AppKernel.php';

    use Symfony\Component\HttpFoundation\Request;

    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();
    $kernel->handle(Request::createFromGlobals())->send();

The kernel first requires the ``bootstrap.php.cache`` file, which bootstraps
the framework and registers the autoloader (see below).

Like any front controller, ``app.php`` uses a Kernel Class, ``AppKernel``, to
bootstrap the application.

.. _the-app-dir:

The ``app/`` Directory
~~~~~~~~~~~~~~~~~~~~~~

The ``AppKernel`` class is the main entry point of the application
configuration and as such, it is stored in the ``app/`` directory.

This class must implement two methods:

* ``registerBundles()`` must return an array of all bundles needed to run the
  application;

* ``registerContainerConfiguration()`` loads the application configuration
  (more on this later).

PHP autoloading can be configured via ``app/autoload.php``::

    // app/autoload.php
    use Symfony\Component\ClassLoader\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
        'Symfony'          => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
        'Sensio'           => __DIR__.'/../vendor/bundles',
        'JMS'              => __DIR__.'/../vendor/bundles',
        'Doctrine\\Common' => __DIR__.'/../vendor/doctrine-common/lib',
        'Doctrine\\DBAL'   => __DIR__.'/../vendor/doctrine-dbal/lib',
        'Doctrine'         => __DIR__.'/../vendor/doctrine/lib',
        'Monolog'          => __DIR__.'/../vendor/monolog/src',
        'Assetic'          => __DIR__.'/../vendor/assetic/src',
        'Metadata'         => __DIR__.'/../vendor/metadata/src',
    ));
    $loader->registerPrefixes(array(
        'Twig_Extensions_' => __DIR__.'/../vendor/twig-extensions/lib',
        'Twig_'            => __DIR__.'/../vendor/twig/lib',
    ));

    // ...

    $loader->registerNamespaceFallbacks(array(
        __DIR__.'/../src',
    ));
    $loader->register();

The :class:`Symfony\\Component\\ClassLoader\\UniversalClassLoader` is used to
autoload files that respect either the technical interoperability `standards`_
for PHP 5.3 namespaces or the PEAR naming `convention`_ for classes. As you
can see here, all dependencies are stored under the ``vendor/`` directory, but
this is just a convention. You can store them wherever you want, globally on
your server or locally in your projects.

.. note::

    If you want to learn more about the flexibility of the Symfony2
    autoloader, read the ":doc:`/components/class_loader`" chapter.

Understanding the Bundle System
-------------------------------

This section introduces one of the greatest and most powerful features of
Symfony2, the :term:`bundle` system.

A bundle is kind of like a plugin in other software. So why is it called a
*bundle* and not a *plugin*? This is because *everything* is a bundle in
Symfony2, from the core framework features to the code you write for your
application. Bundles are first-class citizens in Symfony2. This gives you
the flexibility to use pre-built features packaged in third-party bundles
or to distribute your own bundles. It makes it easy to pick and choose which
features to enable in your application and optimize them the way you want.
And at the end of the day, your application code is just as *important* as
the core framework itself.

Registering a Bundle
~~~~~~~~~~~~~~~~~~~~

An application is made up of bundles as defined in the ``registerBundles()``
method of the ``AppKernel`` class. Each bundle is a directory that contains
a single ``Bundle`` class that describes it::

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
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Acme\DemoBundle\AcmeDemoBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

In addition to the ``AcmeDemoBundle`` that we have already talked about, notice
that the kernel also enables other bundles such as the ``FrameworkBundle``,
``DoctrineBundle``, ``SwiftmailerBundle``, and ``AsseticBundle`` bundle.
They are all part of the core framework.

Configuring a Bundle
~~~~~~~~~~~~~~~~~~~~

Each bundle can be customized via configuration files written in YAML, XML, or
PHP. Have a look at the default configuration:

.. code-block:: yaml

    # app/config/config.yml
    imports:
        - { resource: parameters.ini }
        - { resource: security.yml }

    framework:
        secret:          "%secret%"
        charset:         UTF-8
        router:          { resource: "%kernel.root_dir%/config/routing.yml" }
        form:            true
        csrf_protection: true
        validation:      { enable_annotations: true }
        templating:      { engines: ['twig'] } #assets_version: SomeVersionScheme
        session:
            default_locale: "%locale%"
            auto_start:     true

    # Twig Configuration
    twig:
        debug:            "%kernel.debug%"
        strict_variables: "%kernel.debug%"

    # Assetic Configuration
    assetic:
        debug:          "%kernel.debug%"
        use_controller: false
        filters:
            cssrewrite: ~
            # closure:
            #     jar: "%kernel.root_dir%/java/compiler.jar"
            # yui_css:
            #     jar: "%kernel.root_dir%/java/yuicompressor-2.4.2.jar"

    # Doctrine Configuration
    doctrine:
        dbal:
            driver:   "%database_driver%"
            host:     "%database_host%"
            dbname:   "%database_name%"
            user:     "%database_user%"
            password: "%database_password%"
            charset:  UTF8

        orm:
            auto_generate_proxy_classes: "%kernel.debug%"
            auto_mapping: true

    # Swiftmailer Configuration
    swiftmailer:
        transport: "%mailer_transport%"
        host:      "%mailer_host%"
        username:  "%mailer_user%"
        password:  "%mailer_password%"

    jms_security_extra:
        secure_controllers:  true
        secure_all_services: false

Each entry like ``framework`` defines the configuration for a specific bundle.
For example, ``framework`` configures the ``FrameworkBundle`` while ``swiftmailer``
configures the ``SwiftmailerBundle``.

Each :term:`environment` can override the default configuration by providing a
specific configuration file. For example, the ``dev`` environment loads the
``config_dev.yml`` file, which loads the main configuration (i.e. ``config.yml``)
and then modifies it to add some debugging tools:

.. code-block:: yaml

    # app/config/config_dev.yml
    imports:
        - { resource: config.yml }

    framework:
        router:   { resource: "%kernel.root_dir%/config/routing_dev.yml" }
        profiler: { only_exceptions: false }

    web_profiler:
        toolbar: true
        intercept_redirects: false

    monolog:
        handlers:
            main:
                type:  stream
                path:  "%kernel.logs_dir%/%kernel.environment%.log"
                level: debug
            firephp:
                type:  firephp
                level: info

    assetic:
        use_controller: true

Extending a Bundle
~~~~~~~~~~~~~~~~~~

In addition to being a nice way to organize and configure your code, a bundle
can extend another bundle. Bundle inheritance allows you to override any existing
bundle in order to customize its controllers, templates, or any of its files.
This is where the logical names (e.g. ``@AcmeDemoBundle/Controller/SecuredController.php``)
come in handy: they abstract where the resource is actually stored.

Logical File Names
..................

When you want to reference a file from a bundle, use this notation:
``@BUNDLE_NAME/path/to/file``; Symfony2 will resolve ``@BUNDLE_NAME``
to the real path to the bundle. For instance, the logical path
``@AcmeDemoBundle/Controller/DemoController.php`` would be converted to
``src/Acme/DemoBundle/Controller/DemoController.php``, because Symfony knows
the location of the ``AcmeDemoBundle``.

Logical Controller Names
........................

For controllers, you need to reference method names using the format
``BUNDLE_NAME:CONTROLLER_NAME:ACTION_NAME``. For instance,
``AcmeDemoBundle:Welcome:index`` maps to the ``indexAction`` method from the
``Acme\DemoBundle\Controller\WelcomeController`` class.

Logical Template Names
......................

For templates, the logical name ``AcmeDemoBundle:Welcome:index.html.twig`` is
converted to the file path ``src/Acme/DemoBundle/Resources/views/Welcome/index.html.twig``.
Templates become even more interesting when you realize they don't need to be
stored on the filesystem. You can easily store them in a database table for
instance.

Extending Bundles
.................

If you follow these conventions, then you can use :doc:`bundle inheritance</cookbook/bundles/inheritance>`
to "override" files, controllers or templates. For example, you can create
a bundle - ``AcmeNewBundle`` - and specify that it overrides ``AcmeDemoBundle``.
When Symfony loads the ``AcmeDemoBundle:Welcome:index`` controller, it will
first look for the ``WelcomeController`` class in ``AcmeNewBundle`` and, if 
it doesn't exist, then look inside ``AcmeDemoBundle``. This means that one bundle 
can override almost any part of another bundle!

Do you understand now why Symfony2 is so flexible? Share your bundles between
applications, store them locally or globally, your choice.

.. _using-vendors:

Using Vendors
-------------

Odds are that your application will depend on third-party libraries. Those
should be stored in the ``vendor/`` directory. This directory already contains
the Symfony2 libraries, the SwiftMailer library, the Doctrine ORM, the Twig
templating system, and some other third party libraries and bundles.

Understanding the Cache and Logs
--------------------------------

Symfony2 is probably one of the fastest full-stack frameworks around. But how
can it be so fast if it parses and interprets tens of YAML and XML files for
each request? The speed is partly due to its cache system. The application
configuration is only parsed for the very first request and then compiled down
to plain PHP code stored in the ``app/cache/`` directory. In the development
environment, Symfony2 is smart enough to flush the cache when you change a
file. But in the production environment, it is your responsibility to clear
the cache when you update your code or change its configuration.

When developing a web application, things can go wrong in many ways. The log
files in the ``app/logs/`` directory tell you everything about the requests
and help you fix the problem quickly.

Using the Command Line Interface
--------------------------------

Each application comes with a command line interface tool (``app/console``)
that helps you maintain your application. It provides commands that boost your
productivity by automating tedious and repetitive tasks.

Run it without any arguments to learn more about its capabilities:

.. code-block:: bash

    $ php app/console

The ``--help`` option helps you discover the usage of a command:

.. code-block:: bash

    $ php app/console router:debug --help

Final Thoughts
--------------

Call me crazy, but after reading this part, you should be comfortable with
moving things around and making Symfony2 work for you. Everything in Symfony2
is designed to get out of your way. So, feel free to rename and move directories
around as you see fit.

And that's all for the quick tour. From testing to sending emails, you still
need to learn a lot to become a Symfony2 master. Ready to dig into these
topics now? Look no further - go to the official :doc:`/book/index` and pick
any topic you want.

.. _standards:  http://symfony.com/PSR0
.. _convention: http://pear.php.net/
