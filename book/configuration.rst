.. index::
   single: Configuration
   single: Environments

Configuring Symfony and Environments
====================================

.. index::
   single: Configuration; Formats

Configuration Formats
---------------------

Symfony supports four configuration formats: YAML, XML and PHP. Throughout
the chapters, all configuration examples are shown in all four formats. Each
has its own advantages and disadvantages. The choice of which to use is up
to you:

* *YAML*: Simple, clean and readable (learn more about YAML in the Yaml
  component documentation :doc:`/components/yaml/yaml_format`);

* *XML*: More powerful than YAML at times and supports IDE autocompletion;

* *PHP*: Very powerful but less readable than standard configuration formats.

* *Annoatations*: Code and configuration are in the same place, simple to
  learn and to use, concise to write.

The supported formats with their orders are:

* Configuration (including services): YAML, XML, PHP

* Routing: Annotations, YAML, XML, PHP

* Validation: Annotations, YAML, XML, PHP

* Doctrine Mapping: Annotations, YAML, XML, PHP

* Translation: XML, YAML, PHP

.. index::
   single: Configuration; Default configuration file

Default Configuration File
~~~~~~~~~~~~~~~~~~~~~~~~~~

An application consists of a collection of bundles representing all the
features and capabilities of your application. Each bundle can be customized
via configuration files written in YAML, XML or PHP. By default, the main
configuration file lives in the ``app/config/`` directory and is called
either ``config.yml``, ``config.xml`` or ``config.php`` depending on which
format you prefer::

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        imports:
            - { resource: parameters.yml }
            - { resource: security.yml }

        framework:
            secret:          '%secret%'
            router:          { resource: '%kernel.root_dir%/config/routing.yml' }
            # ...

        # Twig Configuration
        twig:
            debug:            '%kernel.debug%'
            strict_variables: '%kernel.debug%'

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

Each top-level entry like ``framework`` or ``twig`` defines the configuration
for a particular bundle. For example, the ``framework`` key defines the
configuration for the core Symfony FrameworkBundle and includes configuration
for the routing, templating, and other core systems.

For now, don't worry about the specific configuration options in each section.
The configuration file ships with sensible defaults. As you read more and
explore each part of Symfony, you'll learn about the specific configuration
options of each feature.

Default Configuration Dump for a Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can dump the default configuration for a bundle in YAML to the console
using the ``config:dump-reference`` command. Here is an example of dumping
the default FrameworkBundle configuration:

.. code-block:: bash

    $ php app/console config:dump-reference FrameworkBundle

The extension alias (configuration key) can also be used:

.. code-block:: bash

    $ php app/console config:dump-reference framework

.. note::

    See the cookbook article: :doc:`/cookbook/bundles/extension` for
    information on adding configuration for your own bundle.

.. index::
   single: Configuration; File structure of Symfony Standard Edition

Configuration Files of the Symfony Standard Edition
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The configuration files of the default Symfony Standard Edition are in
YAML and follow this structure:

.. code-block:: text

    your-project/
    ├─ app/
    │  ├─ ...
    │  └─ config/
    │     ├─ config.yml
    │     ├─ config_dev.yml
    │     ├─ config_prod.yml
    │     ├─ config_test.yml
    │     ├─ parameters.yml
    │     ├─ parameters.yml.dist
    │     ├─ routing.yml
    │     ├─ routing_dev.yml
    │     └─ security.yml
    ├─ ...

As we see, there is not just ``config.yml`` that we mentioned as default
configuration file but there are also three other configuration files with
different suffixes: ``config_dev.php``, ``config_prod.php`` and
``config_test.php``. To understand what we see we have to talk about
Environments.

.. index::
   single: Environments; Introduction

.. _environments-summary:
.. _page-creation-environments:
.. _book-page-creation-prod-cache-clear:

Environments
------------

An application can run in various environments. The different environments
share the same PHP code, but use different configuration. All environments
live together on the same machine and execute the same application. Different
environments use only different front controllers. So, to view application in
different environments we simply have to change the front controller.

By default Symfony comes with three environments, though creating new
environments is easy:

* ``dev`` represents development environment;
* ``prod`` represents production environment;
* ``test`` represents test environment.

In Symfony a ``dev`` environment will log warnings and errors, while a ``prod``
environment will only log errors. Some files like configuration, routing and
Twig templates, are rebuilt on each request in the ``dev`` environment (for the
developer's convenience), but compiled into flat PHP classes and cached in the
``prod`` environment (optimized for speed). The ``dev`` environment uses
``web/app_dev.php`` front controller while ``prod`` environment uses
``web/app.php`` front controller.

The test environment is used when writing automated tests and is not accessible
via a front controller directly in the browser . In other words, unlike the other
two environments, there is no ``web/app_test.php`` front controller file. See
the :doc:`Testing chapter </book/testing>` for more details.

.. index::
   single: Environments; Environment configuration files

Different Environments, Different Configuration Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Every application is the combination of code and a set of configuration that
dictates how that code should function. The configuration may define the
database being used, if something should be cached or how verbose logging
should be.

In Symfony, the idea of "environments" is the idea that the same codebase can
be run using multiple different configurations. Therefore, **an environment is
nothing more than a string that corresponds to a set of configuration.**

It should be no surprise then that each environment loads its own individual
configuration file. If you're using the YAML configuration format, the
following files are used:

* for the ``dev`` environment: app/config/config_dev.yml
* for the ``prod`` environment: app/config/config_prod.yml
* for the ``test`` environment: app/config/config_test.yml

This works via a simple standard that's used by default inside the ``AppKernel``
class::

    // app/AppKernel.php
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(
            __DIR__.'/config/config_'.$this->getEnvironment().'.yml'
        );
    }

As you can see, when Symfony is loaded, it uses the given environment to
determine which configuration file to load. This accomplishes the goal of
multiple environments in an elegant, powerful and transparent way.

.. note::

    You already know that the ``.yml`` extension can be changed to ``.xml`` or
    ``.php`` if you prefer to use either XML or PHP to write your configuration.

Of course, in reality, each environment differs only somewhat from others.
Generally, all environments will share a large base of common configuration.
Opening the ``config_dev.yml`` configuration file, you can see how this is
accomplished easily and transparently::

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        imports:
            - { resource: config.yml }

        framework:
            router:   { resource: '%kernel.root_dir%/config/routing_dev.yml' }
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

To share common configuration, each environment's configuration file simply
first imports from a central default configuration file ``config.yml``. The
``imports`` key is similar to a PHP ``include`` statement and guarantees that
the main configuration file (``config.yml``) is loaded first. The remainder
of the file can therefore deviate from the default configuration by
*overriding* individual parameters.

Both the ``prod`` and ``test`` environments follow the same model: each
environment imports the central default configuration file and then modifies
its configuration values to fit the needs of the specific environment. This
is just a convention, but one that allows you to reuse most of your
configuration and customize just pieces of it between environments.

.. index::
   single: Environments; Executing app in different environments

Executing an Application in different Environments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To see the application in the ``dev`` environment, access the application via
the development front controller::

.. code-block:: text

    http://localhost/app_dev.php/random/10

.. tip::

    When using the ``server:run`` command to start a server,
    ``http://localhost:8000/`` will use the development front controller.

If you'd like to see how your application will behave in the ``prod``
environment, call the production front controller instead::

.. code-block:: text

    http://localhost/app.php/random/10

When viewing changes in the ``prod`` environment, you'll need to clear these
cached files using ``cache:clear`` Console command and allow them to rebuild::

.. code-block:: bash

    $ php app/console cache:clear --env=prod --no-debug

.. index::
   single: Environments; Custom environments

Creating Custom Environments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Since an environment is nothing more than a string that corresponds to a set
of configuration, creating a new environment is a matter of creating a new
configuration file for that environment:

Suppose, for example, that before deployment, you need to benchmark your
application. One way to benchmark the application is to use near-production
settings, but with Symfony's ``web_profiler`` enabled. This allows Symfony to
record information about your application while benchmarking.

The best way to accomplish this is via a new environment called, for example,
``benchmark``. Start by creating a new configuration file ``config_benchmark.yml``::

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_benchmark.yml
        imports:
            - { resource: config_prod.yml }

        framework:
            profiler: { only_exceptions: false }

    .. code-block:: xml

        <!-- app/config/config_benchmark.xml -->
        <imports>
            <import resource="config_prod.xml" />
        </imports>

        <framework:config>
            <framework:profiler only-exceptions="false" />
        </framework:config>

    .. code-block:: php

        // app/config/config_benchmark.php
        $loader->import('config_prod.php')

        $container->loadFromExtension('framework', array(
            'profiler' => array('only-exceptions' => false),
        ));

And with this simple addition, the application now supports a new environment
called ``benchmark``.

This new configuration file imports the configuration from the ``prod``
environment and modifies it. This guarantees that the new environment is
identical to the ``prod`` environment, except for any changes explicitly
made here.

Because you'll want this environment to be accessible via a browser, you
should also create a front controller for it. Copy the ``web/app.php`` file
to ``web/app_benchmark.php`` and edit the environment to be ``benchmark``::

    // web/app_benchmark.php
    // change just this line

    $kernel = new AppKernel('benchmark', false);

The new environment is now accessible via::

    http://localhost/app_benchmark.php

.. sidebar:: Debug Mode

    Important, but unrelated to the topic of environments is the ``false``
    argument as the second argument to the ``AppKernel()`` constructor.
    This specifies if the application should run in "debug mode". Regardless
    of the environment, a Symfony application can be run with debug mode
    set to ``true`` or ``false``. This affects many things in the application,
    such as displaying stacktraces on error pages or if cache files are
    dynamically rebuilt on each request. Though not a requirement, debug mode
    is generally set to ``true`` for the ``dev`` and ``test`` environments and
    ``false`` for the ``prod`` environment.

    Internally, the value of the debug mode becomes the ``kernel.debug``
    parameter used inside the service container. If you look inside the
    default application configuration file, you'll see the parameter used,
    for example, to turn logging on or off when using the Doctrine DBAL::

    .. configuration-block::

        .. code-block:: yaml

            doctrine:
               dbal:
                   logging: '%kernel.debug%'
                   # ...

        .. code-block:: xml

            <doctrine:dbal logging="%kernel.debug%" />

        .. code-block:: php

            $container->loadFromExtension('doctrine', array(
                'dbal' => array(
                    'logging'  => '%kernel.debug%',
                    // ...
                ),
                // ...
            ));

Learn more from the Cookbook
----------------------------

To learn more about different environments, configuration organization,
relationship between front controllers, Kernel and environments and more you
can read several cookbook articles:

* :doc:`/cookbook/configuration/environments`
* :doc:`/cookbook/configuration/override_dir_structure`
* :doc:`/cookbook/configuration/front_controllers_and_kernel`
* :doc:`/cookbook/configuration/configuration_organization`
