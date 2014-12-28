.. index::
   single: Structuring Apps in Symfony

.. _structuring-apps-in-symfony2:

Structuring Apps in Symfony
=========================

The Directory Structure
-----------------------

The short sections above illustrate several important principles behind
creating and rendering pages in Symfony. They also give an initial insight into
how Symfony projects are structured and organized by default. Each Symfony
:term:`application` has the same basic and recommended directory structure:

``app/``
    This directory contains the application configuration, templates and stylesheets.

``src/``
    All the project PHP code is stored under this directory.

``vendor/``
    Any vendor libraries are placed here by convention.

``web/``
    This is the web root directory and contains any publicly accessible files.

.. seealso::

    For further important information on the directory structure, see 
    :doc:`/best_practices/creating-the-project.html#structuring-the-application`. 


The Bundle System
-----------------

Before you begin building apps, you'll need to create a *bundle*. In Symfony, a :term:`bundle`
is like a plugin, except that all of the code in your application will live
inside a bundle.

A bundle is nothing more than a directory that houses everything related
to a specific feature, including PHP classes, configuration, and even stylesheets
and JavaScript files (see :ref:`page-creation-bundles`).


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

You might create a ``BlogBundle``, a ``ForumBundle`` or a bundle for user management 
(many of these exist already as open source bundles). Each directory contains all the
business logic related to that feature, including tests. Every feature lives in its
own bundle, along with every fundamental piece that should ship with that feature.

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
``AcmeTestBundle`` and enable it.

.. tip::

    The ``Acme`` portion is just a dummy name that should be replaced by
    some "vendor" name that represents you or your organization (e.g. ``ABCTestBundle``
    for some company named ``ABC``).

Start by creating a ``src/Acme/TestBundle/`` directory and adding a new file
called ``AcmeTestBundle.php``::

    // src/Acme/TestBundle/AcmeTestBundle.php
    namespace Acme\TestBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class AcmeTestBundle extends Bundle
    {
    }

.. tip::

   The name ``AcmeTestBundle`` follows the standard :ref:`Bundle naming conventions <bundles-naming-conventions>`.
   You could also choose to shorten the name of the bundle to simply ``TestBundle``
   by naming this class ``TestBundle`` (and naming the file ``TestBundle.php``).

This empty class is the only piece you need to create the new bundle. Though
commonly empty, this class is powerful and can be used to customize the behavior
of the bundle.

Now that you've created the bundle, enable it via the ``AppKernel`` class::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            ...,
            // register your bundles
            new Acme\TestBundle\AcmeTestBundle(),
        );
        // ...

        return $bundles;
    }

And while it doesn't do anything yet, ``AcmeTestBundle`` is now ready to
be used.

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
between all Symfony bundles. Take a look at ``AcmeDemoBundle``, as it contains
some of the most common elements of a bundle:

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

An application consists of a collection of bundles representing all of the
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
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd
                http://symfony.com/schema/dic/twig http://symfony.com/schema/dic/twig/twig-1.0.xsd">

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
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

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
            'router'   => array(
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

* Creating a page is a four-step process involving a **bundle**, a **route**, 
  a **controller** and (optionally) a **template**;

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
