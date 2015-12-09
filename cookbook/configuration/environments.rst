.. index::
    single: Environments

How to Master and Create new Environments
=========================================

Every application is the combination of code and a set of configuration that
dictates how that code should function. The configuration may define the database
being used, if something should be cached or how verbose logging should be.

In Symfony, the idea of "environments" is the idea that the same codebase can be
run using multiple different configurations. For example, the ``dev`` environment
should use configuration that makes development easy and friendly, while the
``prod`` environment should use a set of configuration optimized for speed.

.. index::
   single: Environments; Configuration files

Different Environments, different Configuration Files
-----------------------------------------------------

A typical Symfony application begins with three environments: ``dev``,
``prod``, and ``test``. As mentioned, each environment simply represents
a way to execute the same codebase with different configuration. It should
be no surprise then that each environment loads its own individual configuration
file. If you're using the YAML configuration format, the following files
are used:

* for the ``dev`` environment: ``app/config/config_dev.yml``
* for the ``prod`` environment: ``app/config/config_prod.yml``
* for the ``test`` environment: ``app/config/config_test.yml``

This works via a simple standard that's used by default inside the ``AppKernel``
class:

.. code-block:: php

    // app/AppKernel.php

    // ...

    class AppKernel extends Kernel
    {
        // ...

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
        }
    }

As you can see, when Symfony is loaded, it uses the given environment to
determine which configuration file to load. This accomplishes the goal of
multiple environments in an elegant, powerful and transparent way.

Of course, in reality, each environment differs only somewhat from others.
Generally, all environments will share a large base of common configuration.
Opening the ``config_dev.yml`` configuration file, you can see how this is
accomplished easily and transparently:

.. configuration-block::

    .. code-block:: yaml

        imports:
            - { resource: config.yml }

        # ...

    .. code-block:: xml

        <imports>
            <import resource="config.xml" />
        </imports>

        <!-- ... -->

    .. code-block:: php

        $loader->import('config.php');

        // ...

To share common configuration, each environment's configuration file
simply first imports from a central configuration file (``config.yml``).
The remainder of the file can then deviate from the default configuration
by overriding individual parameters. For example, by default, the ``web_profiler``
toolbar is disabled. However, in the ``dev`` environment, the toolbar is
activated by modifying the value of the ``toolbar`` option in the ``config_dev.yml``
configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        imports:
            - { resource: config.yml }

        web_profiler:
            toolbar: true
            # ...

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->
        <imports>
            <import resource="config.xml" />
        </imports>

        <webprofiler:config toolbar="true" />

    .. code-block:: php

        // app/config/config_dev.php
        $loader->import('config.php');

        $container->loadFromExtension('web_profiler', array(
            'toolbar' => true,

            // ...
        ));

.. index::
   single: Environments; Executing different environments

Executing an Application in different Environments
--------------------------------------------------

To execute the application in each environment, load up the application using
either the ``app.php`` (for the ``prod`` environment) or the ``app_dev.php``
(for the ``dev`` environment) front controller:

.. code-block:: text

    http://localhost/app.php      -> *prod* environment
    http://localhost/app_dev.php  -> *dev* environment

.. note::

   The given URLs assume that your web server is configured to use the ``web/``
   directory of the application as its root. Read more in
   :doc:`Installing Symfony </book/installation>`.

If you open up one of these files, you'll quickly see that the environment
used by each is explicitly set::

    // web/app.php
    // ...

    $kernel = new AppKernel('prod', false);

    // ...

The ``prod`` key specifies that this application will run in the ``prod``
environment. A Symfony application can be executed in any environment by using
this code and changing the environment string.

.. note::

   The ``test`` environment is used when writing functional tests and is
   not accessible in the browser directly via a front controller. In other
   words, unlike the other environments, there is no ``app_test.php`` front
   controller file.

.. index::
   single: Configuration; Debug mode

.. sidebar:: *Debug* Mode

    Important, but unrelated to the topic of *environments* is the ``false``
    argument as the second argument to the ``AppKernel`` constructor. This
    specifies if the application should run in "debug mode". Regardless
    of the environment, a Symfony application can be run with debug mode
    set to ``true`` or ``false``. This affects many things in the application,
    such as if errors should be displayed or if cache files are
    dynamically rebuilt on each request. Though not a requirement, debug mode
    is generally set to ``true`` for the ``dev`` and ``test`` environments
    and ``false`` for the ``prod`` environment.

    Internally, the value of the debug mode becomes the ``kernel.debug``
    parameter used inside the :doc:`service container </book/service_container>`.
    If you look inside the application configuration file, you'll see the
    parameter used, for example, to turn logging on or off when using the
    Doctrine DBAL:

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

    As of Symfony 2.3, showing errors or not no longer depends on the debug
    mode. You'll need to enable that in your front controller by calling
    :method:`Symfony\\Component\\Debug\\Debug::enable`.

Selecting the Environment for Console Commands
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, Symfony commands are executed in the ``dev`` environment and with the
debug mode enabled. Use the ``--env`` and ``--no-debug`` options to modify this
behavior:

.. code-block:: bash

    # 'dev' environment and debug enabled
    $ php bin/console command_name

    # 'prod' environment (debug is always disabled for 'prod')
    $ php bin/console command_name --env=prod

    # 'test' environment and debug disabled
    $ php bin/console command_name --env=test --no-debug

In addition to the ``--env`` and ``--debug`` options, the behavior of Symfony
commands can also be controlled with environment variables. The Symfony console
application checks the existence and value of these environment variables before
executing any command:

``SYMFONY_ENV``
    Sets the execution environment of the command to the value of this variable
    (``dev``, ``prod``, ``test``, etc.);
``SYMFONY_DEBUG``
    If ``0``, debug mode is disabled. Otherwise, debug mode is enabled.

These environment variables are very useful for production servers because they
allow you to ensure that commands always run in the ``prod`` environment without
having to add any command option.

.. index::
   single: Environments; Creating a new environment

Creating a new Environment
--------------------------

By default, a Symfony application has three environments that handle most
cases. Of course, since an environment is nothing more than a string that
corresponds to a set of configuration, creating a new environment is quite
easy.

Suppose, for example, that before deployment, you need to benchmark your
application. One way to benchmark the application is to use near-production
settings, but with Symfony's ``web_profiler`` enabled. This allows Symfony
to record information about your application while benchmarking.

The best way to accomplish this is via a new environment called, for example,
``benchmark``. Start by creating a new configuration file:

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

.. include:: /components/dependency_injection/_imports-parameters-note.rst.inc

And with this simple addition, the application now supports a new environment
called ``benchmark``.

This new configuration file imports the configuration from the ``prod`` environment
and modifies it. This guarantees that the new environment is identical to
the ``prod`` environment, except for any changes explicitly made here.

Because you'll want this environment to be accessible via a browser, you
should also create a front controller for it. Copy the ``web/app.php`` file
to ``web/app_benchmark.php`` and edit the environment to be ``benchmark``::

    // web/app_benchmark.php
    // ...

    // change just this line
    $kernel = new AppKernel('benchmark', false);

    // ...

The new environment is now accessible via::

    http://localhost/app_benchmark.php

.. note::

    Some environments, like the ``dev`` environment, are never meant to be
    accessed on any deployed server by the public. This is because
    certain environments, for debugging purposes, may give too much information
    about the application or underlying infrastructure. To be sure these environments
    aren't accessible, the front controller is usually protected from external
    IP addresses via the following code at the top of the controller::

        if (!in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1'))) {
            die('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
        }

.. index::
   single: Environments; Cache directory

Environments and the Cache Directory
------------------------------------

Symfony takes advantage of caching in many ways: the application configuration,
routing configuration, Twig templates and more are cached to PHP objects
stored in files on the filesystem.

By default, these cached files are largely stored in the ``var/cache`` directory.
However, each environment caches its own set of files:

.. code-block:: text

    your-project/
    ├─ var/
    │  ├─ cache/
    │  │  ├─ dev/   # cache directory for the *dev* environment
    │  │  └─ prod/  # cache directory for the *prod* environment
    │  ├─ ...

Sometimes, when debugging, it may be helpful to inspect a cached file to
understand how something is working. When doing so, remember to look in
the directory of the environment you're using (most commonly ``dev`` while
developing and debugging). While it can vary, the ``var/cache/dev`` directory
includes the following:

``appDevDebugProjectContainer.php``
    The cached "service container" that represents the cached application
    configuration.

``appDevUrlGenerator.php``
    The PHP class generated from the routing configuration and used when
    generating URLs.

``appDevUrlMatcher.php``
    The PHP class used for route matching - look here to see the compiled regular
    expression logic used to match incoming URLs to different routes.

``twig/``
    This directory contains all the cached Twig templates.

.. note::

    You can easily change the directory location and name. For more information
    read the article :doc:`/cookbook/configuration/override_dir_structure`.

Going further
-------------

Read the article on :doc:`/cookbook/configuration/external_parameters`.
