The Architecture
================

You are my hero! Who would have thought that you would still be here after the
first three parts? Your efforts will be well rewarded soon. The first three
parts don't have a deep look at the architecture of the framework. As it makes
Symfony2 stand apart from the framework crowd, let's dive into it now.

.. index::
   single: Directory Structure

The Directory Structure
-----------------------

The directory structure of a Symfony :term:`application` is rather flexible
but the directory structure of a sandbox reflects the typical and recommended
structure of a Symfony application:

* ``hello/``: This directory, named after your application, contains the
  configuration files;

* ``src/``: All the PHP code is stored under this directory;

* ``web/``: This should be the web root directory.

The Web Directory
~~~~~~~~~~~~~~~~~

The web root directory is the home of all public and static files like images,
stylesheets, and JavaScript files. It is also where the front controllers
live:

.. code-block:: html+php

    <!-- web/index.php -->
    <?php

    require_once __DIR__.'/../hello/HelloKernel.php';

    $kernel = new HelloKernel('prod', false);
    $kernel->handle()->send();

Like any front controller, ``index.php`` uses a Kernel Class, ``HelloKernel``, to
bootstrap the application.

.. index::
   single: Kernel

The Application Directory
~~~~~~~~~~~~~~~~~~~~~~~~~

The ``HelloKernel`` class is the main entry point of the application
configuration and as such, it is stored in the ``hello/`` directory.

This class must implement four methods:

* ``registerRootDir()``: Returns the configuration root directory;

* ``registerBundles()``: Returns an array of all bundles needed to run the
  application (notice the reference to
  ``Application\HelloBundle\HelloBundle``);

* ``registerBundleDirs()``: Returns an array associating namespaces and their
  home directories;

* ``registerContainerConfiguration()``: Returns the main configuration object
  (more on this later);

Have a look at the default implementation of these methods to better
understand the flexibility of the framework.

To make things work together, the kernel requires one file from the ``src/``
directory::

    // hello/HelloKernel.php
    require_once __DIR__.'/../src/autoload.php';

The Source Directory
~~~~~~~~~~~~~~~~~~~~

The ``src/autoload.php`` file is responsible for autoloading all the files
stored in the ``src/`` directory::

    // src/autoload.php
    require_once __DIR__.'/vendor/symfony/src/Symfony/Framework/UniversalClassLoader.php';

    use Symfony\Framework\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
        'Symfony'                    => __DIR__.'/vendor/symfony/src',
        'Application'                => __DIR__,
        'Bundle'                     => __DIR__,
        'Doctrine\\Common'           => __DIR__.'/vendor/doctrine/lib/vendor/doctrine-common/lib',
        'Doctrine\\DBAL\\Migrations' => __DIR__.'/vendor/doctrine-migrations/lib',
        'Doctrine\\DBAL'             => __DIR__.'/vendor/doctrine/lib/vendor/doctrine-dbal/lib',
        'Doctrine'                   => __DIR__.'/vendor/doctrine/lib',
        'Zend'                       => __DIR__.'/vendor/zend/library',
    ));
    $loader->registerPrefixes(array(
        'Swift_' => __DIR__.'/vendor/swiftmailer/lib/classes',
        'Twig_'  => __DIR__.'/vendor/twig/lib',
    ));
    $loader->register();

The ``UniversalClassLoader`` from Symfony is used to autoload files that
respect either the technical interoperability `standards`_ for PHP 5.3
namespaces or the PEAR naming `convention`_ for classes. As you can see
here, all dependencies are stored under the ``vendor/`` directory, but this is
just a convention. You can store them wherever you want, globally on your
server or locally in your projects.

.. index::
   single: Bundles

The Bundle System
-----------------

This section starts to scratch the surface of one of the greatest and more
powerful features of Symfony, its :term:`bundle` system.

A bundle is kind of like a plugin in other software. But why is it called
bundle and not plugin then? Because everything is a bundle in Symfony, from
the core framework features to the code you write for your application.
Bundles are first-class citizens in Symfony. This gives you the flexibility to
use pre-built features packaged in third-party bundles or to distribute your
own bundles. It makes it so easy to pick and choose which features to enable
in your application and optimize them the way you want.

An application is made up of bundles as defined in the ``registerBundles()``
method of the ``HelloKernel`` class::

    // hello/HelloKernel.php

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Framework\KernelBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\ZendBundle\ZendBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            //new Symfony\Bundle\DoctrineMigrationsBundle\DoctrineMigrationsBundle(),
            //new Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle(),
            //new Symfony\Bundle\PropelBundle\PropelBundle(),
            //new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Application\HelloBundle\HelloBundle(),
        );

        if ($this->isDebug()) {
        }

        return $bundles;
    }

Along side the ``HelloBundle`` we have already talked about, notice that the
kernel also enables ``KernelBundle``, ``FrameworkBundle``, ``DoctrineBundle``,
``SwiftmailerBundle``, and ``ZendBundle``. They are all part of the core
framework.

Each bundle can be customized via configuration files written in YAML, XML, or
PHP. Have a look at the default configuration:

.. configuration-block::

    .. code-block:: yaml

        # hello/config/config.yml
        kernel.config:
            charset:       UTF-8
            error_handler: null

        web.config:
            router:     { resource: "%kernel.root_dir%/config/routing.yml" }
            validation: { enabled: true, annotations: true }

        web.templating:
            escaping:       htmlspecialchars

    .. code-block:: xml

        <!-- hello/config/config.xml -->
        <kernel:config
            charset="UTF-8"
            error_handler="null"
        />

        <web:config>
            <web:router resource="%kernel.root_dir%/config/routing.xml" />
            <web:validation enabled="true" annotations="true" />
        </web:config>

        <web:templating
            escaping="htmlspecialchars"
        />

    .. code-block:: php

        // hello/config/config.php
        $container->loadFromExtension('kernel', 'config', array(
            'charset'       => 'UTF-8',
            'error_handler' => null,
        ));

        $container->loadFromExtension('web', 'config', array(
            'router'     => array('resource' => '%kernel.root_dir%/config/routing.php'),
            'validation' => array('enabled' => true, 'annotations' => true),
        ));

        $container->loadFromExtension('web', 'templating', array(
            'escaping'       => "htmlspecialchars",
        ));

Each entry like ``kernel.config`` defines the configuration of a bundle. Some
bundles can have several entries if they provide many features like
``FrameworkBundle``, which has two entries: ``web.config`` and
``web.templating``.

Each :term:`environment` can override the default configuration by providing a
specific configuration file:

.. configuration-block::

    .. code-block:: yaml

        # hello/config/config_dev.yml
        imports:
            - { resource: config.yml }

        web.config:
            toolbar: true

        zend.logger:
            priority: debug
            path:     %kernel.root_dir%/logs/%kernel.environment%.log

    .. code-block:: xml

        <!-- hello/config/config_dev.xml -->
        <imports>
            <import resource="config.xml" />
        </imports>

        <web:config
            toolbar="true"
        />

        <zend:logger
            priority="info"
            path="%kernel.logs_dir%/%kernel.environment%.log"
        />

    .. code-block:: php

        // hello/config/config.php
        $loader->import('config.php');

        $container->loadFromExtension('web', 'config', array(
            'toolbar' => true,
        ));

        $container->loadFromExtension('zend', 'logger', array(
            'priority' => 'info',
            'path'     => '%kernel.logs_dir%/%kernel.environment%.log',
        ));

As we have seen in the previous part, an application is made of bundles as
defined in the ``registerBundles()`` method but how does Symfony know where to
look for bundles? Symfony is quite flexible in this regard. The
``registerBundleDirs()`` method must return an associative array that maps
namespaces to any valid directory (local or global ones)::

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

Do you understand now why Symfony is so flexible? Share your bundles between
applications, store them locally or globally, your choice.

.. index::
   single: Vendors

Vendors
-------

Odds are your application will depend on third-party libraries. Those should
be stored in the ``src/vendor/`` directory. It already contains the Symfony
libraries, the SwiftMailer library, the Doctrine ORM, the Propel ORM, the Twig
templating system, and a selection of the Zend Framework classes.

.. index::
   single: Cache
   single: Logs

Cache and Logs
--------------

Symfony is probably one of the fastest full-stack frameworks around. But how
can it be so fast if it parses and interprets tens of YAML and XML files for
each request? This is partly due to its cache system. The application
configuration is only parsed for the very first request and then compiled down
to plain PHP code stored in the ``cache/`` application directory. In the
development environment, Symfony is smart enough to flush the cache when you
change a file. But in the production one, it is your responsibility to clear
the cache when you update your code or change its configuration.

When developing a web application, things can go wrong in many ways. The log
files in the ``logs/`` application directory tell you everything about the
requests and helps you fix the problem in no time.

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

    $ php hello/console

The ``--help`` option helps you discover the usage of a command:

.. code-block:: bash

    $ php hello/console router:debug --help

Final Thoughts
--------------

Call me crazy, but after reading this part, you should be comfortable with
moving things around and making Symfony work for you. Everything is done in
Symfony to stand out of your way. So, feel free to rename and move directories
around as you see fit.

And that's all for the quick tour. From testing to sending emails, you still
need to learn of lot to become a Symfony master. Ready to dig into these
topics now? Look no further, go to the official `guides`_ page and pick any
topic you want.

.. _standards:  http://groups.google.com/group/php-standards/web/psr-0-final-proposal
.. _convention: http://pear.php.net/
.. _guides:     http://www.symfony-reloaded.org/learn
