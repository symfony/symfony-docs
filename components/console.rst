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

.. _console-bundle:

Using the ConsoleBundle
-----------------------

.. versionadded:: 8.1

    The ``ConsoleBundle`` was introduced in Symfony 8.1.

Building the container by hand as shown above works, but the Console component
also provides a ``ConsoleBundle`` that brings service autodiscovery,
autoconfiguration and autowiring to console applications, still without
requiring HttpKernel or FrameworkBundle.

The bundle needs a kernel to build the container. Use the kernel provided by
the DependencyInjection component (read
:ref:`Building HTTP-less Applications <building-http-less-applications>` for
more details)::

    // src/Kernel.php
    namespace App;

    use Symfony\Component\DependencyInjection\Kernel\AbstractKernel;
    use Symfony\Component\DependencyInjection\Kernel\KernelTrait;

    class Kernel extends AbstractKernel
    {
        use KernelTrait;
    }

Then, enable the bundle::

    // config/bundles.php
    return [
        Symfony\Component\Console\ConsoleBundle::class => ['all' => true],
    ];

Finally, create the console executable. This example uses the
:doc:`Runtime component </components/runtime>`, but you can also boot the
kernel yourself::

    #!/usr/bin/env php
    <?php

    use App\Kernel;
    use Symfony\Component\Console\Application;

    require __DIR__.'/vendor/autoload_runtime.php';

    return function (array $context) {
        $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
        $kernel->boot();

        return new Application('my-cli', '1.0', $kernel->getContainer());
    };

The kernel follows the same conventions as full Symfony applications:
``config/bundles.php`` to enable bundles, ``config/packages/`` to configure
them and ``config/services.yaml`` (or ``config/services.php``) to register your
own services and commands.

On top of that, ``ConsoleBundle`` provides the following:

* autoconfiguration for commands: any service extending
  :class:`Symfony\\Component\\Console\\Command\\Command` or using the
  ``#[AsCommand]`` attribute is added to the application and loaded lazily
  through the command loader;
* autoconfiguration for the
  :doc:`argument value resolvers </console/value_resolver>`, together with all
  the built-in resolvers;
* a listener that logs command errors and non-zero exit codes, when the
  EventDispatcher component is installed;
* the ``dotenv:debug`` command, when the Dotenv component is installed.

``ConsoleBundle`` declares ``ServicesBundle`` as a
:ref:`required bundle <bundles-required-bundles>`, so you don't need to enable
it yourself. That bundle provides the core services used by the container, such
as ``parameter_bag``, ``event_dispatcher``, ``filesystem`` and ``clock``.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /console
    /components/console/*
    /console/*
