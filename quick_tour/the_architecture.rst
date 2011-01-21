The Architecture
================

You are my hero! Who would have thought that you would still be here after the
first three parts? Your efforts will be well-rewarded soon. The first three
parts didn't look too deeply at the architecture of the framework. As it makes
Symfony2 stand apart from the framework crowd, let's dive into it now.

.. index::
   single: Directory Structure

The Directory Structure
-----------------------

The directory structure of a Symfony2 :term:`application` is rather flexible
but the directory structure of the sandbox reflects the typical and recommended
structure of a Symfony2 application:

* ``app/``: This directory contains the application configuration;

* ``src/``: All the PHP code is stored under this directory;

* ``web/``: This should be the web root directory.

The Web Directory
~~~~~~~~~~~~~~~~~

The web root directory is the home of all public and static files like images,
stylesheets, and JavaScript files. It is also where each :term:`front controller`
lives::

    // web/app.php
    require_once __DIR__.'/../app/AppKernel.php';

    use Symfony\Component\HttpFoundation\Request;

    $kernel = new AppKernel('prod', false);
    $kernel->handle(new Request())->send();

Like any front controller, ``app.php`` uses a Kernel Class, ``AppKernel``, to
bootstrap the application.

.. index::
   single: Kernel

The Application Directory
~~~~~~~~~~~~~~~~~~~~~~~~~

The ``AppKernel`` class is the main entry point of the application
configuration and as such, it is stored in the ``app/`` directory.

This class must implement four methods:

* ``registerRootDir()``: Returns the configuration root directory;

* ``registerBundles()``: Returns an array of all bundles needed to run the
  application (notice the reference to
  ``Application\HelloBundle\HelloBundle``);

* ``registerContainerConfiguration()``: Loads the configuration (more on this
  later);

Have a look at the default implementation of these methods to better
understand the flexibility of the framework.

To make things work together, the kernel requires one file from the ``src/``
directory::

    // app/AppKernel.php
    require_once __DIR__.'/../src/autoload.php';

The Source Directory
~~~~~~~~~~~~~~~~~~~~

The ``src/autoload.php`` file is responsible for autoloading all the PHP
classes used by the application::

    // src/autoload.php
    $vendorDir = __DIR__.'/vendor';

    require_once $vendorDir.'/symfony/src/Symfony/Component/HttpFoundation/UniversalClassLoader.php';

    use Symfony\Component\HttpFoundation\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
        'Symfony'                        => $vendorDir.'/symfony/src',
        'Application'                    => __DIR__,
        'Bundle'                         => __DIR__,
        'Doctrine\\Common\\DataFixtures' => $vendorDir.'/doctrine-data-fixtures/lib',
        'Doctrine\\Common'               => $vendorDir.'/doctrine-common/lib',
        'Doctrine\\DBAL\\Migrations'     => $vendorDir.'/doctrine-migrations/lib',
        'Doctrine\\MongoDB'              => $vendorDir.'/doctrine-mongodb/lib',
        'Doctrine\\ODM\\MongoDB'         => $vendorDir.'/doctrine-mongodb-odm/lib',
        'Doctrine\\DBAL'                 => $vendorDir.'/doctrine-dbal/lib',
        'Doctrine'                       => $vendorDir.'/doctrine/lib',
        'Zend'                           => $vendorDir.'/zend/library',
    ));
    $loader->registerPrefixes(array(
        'Swift_'           => $vendorDir.'/swiftmailer/lib/classes',
        'Twig_Extensions_' => $vendorDir.'/twig-extensions/lib',
        'Twig_'            => $vendorDir.'/twig/lib',
    ));
    $loader->register();

The ``UniversalClassLoader`` from Symfony2 is used to autoload files that
respect either the technical interoperability `standards`_ for PHP 5.3
namespaces or the PEAR naming `convention`_ for classes. As you can see
here, all dependencies are stored under the ``vendor/`` directory, but this is
just a convention. You can store them wherever you want, globally on your
server or locally in your projects.

.. index::
   single: Bundles

The Bundle System
-----------------

This section introduces one of the greatest and most powerful features of
Symfony2, the :term:`bundle` system.

A bundle is kind of like a plugin in other software. So why is it called
*bundle* and not *plugin*? Because *everything* is a bundle in Symfony2, from
the core framework features to the code you write for your application.
Bundles are first-class citizens in Symfony2. This gives you the flexibility
to use pre-built features packaged in third-party bundles or to distribute
your own bundles. It makes it easy to pick and choose which features to enable
in your application and optimize them the way you want.

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

In addition to the ``HelloBundle`` that we have already talked about, notice
that the kernel also enables ``FrameworkBundle``, ``DoctrineBundle``,
``SwiftmailerBundle``, and ``ZendBundle``. They are all part of the core
framework.

Each bundle can be customized via configuration files written in YAML, XML, or
PHP. Have a look at the default configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        app.config:
            charset:       UTF-8
            error_handler: null
            csrf_secret:   xxxxxxxxxx
            router:        { resource: "%kernel.root_dir%/config/routing.yml" }
            validation:    { enabled: true, annotations: true }
            templating:
                #assets_version: SomeVersionScheme
            session:
                default_locale: en
                lifetime: 3600

        ## Twig Configuration
        #twig.config:
        #    auto_reload: true

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
            <app:session default-locale="en" lifetime="3600" />
        </app:config>

        <!-- Twig Configuration -->
        <!--
        <twig:config auto_reload="true" />
        -->

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

        // app/config/config.php
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
                'lifetime' => "3600",
            ),
        ));

        // Twig Configuration
        /*
        $container->loadFromExtension('twig', 'config', array('auto_reload' => true));
        */

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

Each entry like ``app.config`` defines the configuration for a bundle.

Each :term:`environment` can override the default configuration by providing a
specific configuration file:

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

Do you understand now why Symfony2 is so flexible? Share your bundles between
applications, store them locally or globally, your choice.

.. index::
   single: Vendors

Using Vendors
-------------

Odds are that your application will depend on third-party libraries. Those
should be stored in the ``src/vendor/`` directory. This directory already
contains the Symfony2 libraries, the SwiftMailer library, the Doctrine ORM,
the Twig templating system, and a selection of the Zend Framework classes.

.. index::
   single: Configuration Cache
   single: Logs

Cache and Logs
--------------

Symfony2 is probably one of the fastest full-stack frameworks around. But how
can it be so fast if it parses and interprets tens of YAML and XML files for
each request? This is partly due to its cache system. The application
configuration is only parsed for the very first request and then compiled down
to plain PHP code stored in the ``cache/`` application directory. In the
development environment, Symfony2 is smart enough to flush the cache when you
change a file. But in the production environment, it is your responsibility
to clear the cache when you update your code or change its configuration.

When developing a web application, things can go wrong in many ways. The log
files in the ``logs/`` application directory tell you everything about the
requests and help you fix the problem quickly.

.. index::
   single: CLI
   single: Command Line

The Command Line Interface
--------------------------

Each application comes with a command line interface tool (``console``) that
helps you maintain your application. It provides commands that boost your
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
moving things around and making Symfony2 work for you. Everything is done in
Symfony2 to get out of your way. So, feel free to rename and move directories
around as you see fit.

And that's all for the quick tour. From testing to sending emails, you still
need to learn a lot to become a Symfony2 master. Ready to dig into these topics
now? Look no further - go to the official `guides`_ page and pick any topic you
want.

.. _standards:  http://groups.google.com/group/php-standards/web/psr-0-final-proposal
.. _convention: http://pear.php.net/
.. _guides:     http://www.symfony-reloaded.org/learn
