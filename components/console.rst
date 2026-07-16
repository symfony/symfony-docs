The Console Component
=====================

    The Console component eases the creation of beautiful and testable command
    line interfaces.

The Console component allows you to create command-line commands. Your console
commands can be used for any recurring task, such as cronjobs, imports, or
other batch jobs.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/console

.. include:: /components/require_autoload.rst.inc

Creating a Console Application
------------------------------

.. seealso::

    This article explains how to use the Console features as an independent
    component in any PHP application. Read the :doc:`/console` article to
    learn about how to use it in Symfony applications.

First, you need to create a PHP script to define the console application::

    #!/usr/bin/env php
    <?php
    // application.php

    require __DIR__.'/vendor/autoload.php';

    use Symfony\Component\Console\Application;

    $application = new Application();

    // ... register commands

    $application->run();

Then, you can register the commands using
:method:`Symfony\\Component\\Console\\Application::addCommand`::

    // ...
    $application->addCommand(new GenerateAdminCommand());

You can also register inline commands and define their behavior thanks to the
``Command::setCode()`` method::

    // ...
    $application->register('generate-admin')
        ->addArgument('username', InputArgument::REQUIRED)
        ->setCode(function (InputInterface $input, OutputInterface $output): int {
            // ...

            return Command::SUCCESS;
        });

This is useful when creating a :doc:`single-command application </components/console/single_command_tool>`.

See the :doc:`/console` article for information about how to create commands.

Using a PSR Container
---------------------

.. versionadded:: 8.1

    The ``$container`` parameter of the ``Application`` class was introduced
    in Symfony 8.1.

The ``Application`` class accepts an optional third argument, a PSR-11
``ContainerInterface``. When provided, the application automatically wires
several services from the container:

* ``event_dispatcher``: sets the event dispatcher via ``setDispatcher()``
* ``console.argument_resolver``: sets the argument resolver via ``setArgumentResolver()``
* ``console.command_loader``: sets the command loader via ``setCommandLoader()``
* ``console.command.ids``: eagerly loads commands registered in the container
* ``services_resetter``: resets services after ``run()`` completes

When using Symfony's :class:`Symfony\\Component\\DependencyInjection\\ContainerInterface`,
the ``kernel.environment`` and ``kernel.debug`` parameters are also displayed
in the application's long version output.

This makes it possible to build console applications with dependency injection
without requiring HttpKernel or FrameworkBundle. Passing no container preserves
all existing behavior::

    #!/usr/bin/env php
    <?php

    use Symfony\Component\Console\Application;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    require __DIR__.'/vendor/autoload.php';

    $container = new ContainerBuilder();
    // ... register your services, commands, etc.
    $container->compile();

    $application = new Application('my-cli', '1.0', $container);
    $application->run();

Bootstrapping an Application with the ConsoleBundle
---------------------------------------------------

.. versionadded:: 8.1

    The ``ConsoleBundle`` was introduced in Symfony 8.1.

Registering services and commands by hand works, but the Console component
also ships a ``ConsoleBundle`` that brings service autodiscovery,
autoconfiguration and autowiring to console applications, still without
requiring HttpKernel or FrameworkBundle.

Combined with the kernel infrastructure provided by the DependencyInjection
component, a DI-enabled console application boils down to a
``config/bundles.php`` file::

    // config/bundles.php
    return [
        Symfony\Component\Console\ConsoleBundle::class => ['all' => true],
    ];

and a small executable script (this example also uses the
:doc:`Runtime component </components/runtime>`)::

    #!/usr/bin/env php
    <?php

    require __DIR__.'/vendor/autoload_runtime.php';

    use Symfony\Component\Console\Application;
    use Symfony\Component\DependencyInjection\Kernel\AbstractKernel;
    use Symfony\Component\DependencyInjection\Kernel\KernelTrait;

    return static function (array $context) {
        $env = $context['APP_ENV'] ?? 'dev';
        $debug = (bool) ($context['APP_DEBUG'] ?? true);

        $kernel = new class($env, $debug) extends AbstractKernel {
            use KernelTrait;
        };

        $kernel->boot();

        return new Application('demo', '1.0', $kernel->getContainer());
    };

The kernel follows the same conventions as full Symfony applications:
``config/bundles.php`` to register bundles, ``config/packages/`` for package
configuration and ``config/services.yaml`` (or ``.php``) to register your own
services and commands.

The ConsoleBundle provides:

* autoconfiguration for commands: any service extending ``Command`` or using
  the ``#[AsCommand]`` attribute is registered in the application and loaded
  lazily through the command loader;
* the built-in :doc:`argument value resolvers </console/value_resolver>`;
* an ``event_dispatcher`` service wired to the console events (if the
  EventDispatcher component is installed);
* the ``dotenv:debug`` command (if the Dotenv component is installed).

The ConsoleBundle itself declares the ``ServicesBundle`` of the
DependencyInjection component as a :ref:`required bundle
<bundles-required-bundles>`. That bundle supplies the core infrastructure
services (``parameter_bag``, ``event_dispatcher``, ``filesystem``, ``clock``,
the config cache, etc.), so you don't need to register it explicitly.

.. tip::

    Anything HTTP-related in your app? Use FrameworkBundle. An app that is
    unrelated to server-side HTTP? The ConsoleBundle might be a fit.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /console
    /components/console/*
    /console/*
