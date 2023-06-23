The Console Component
=====================

.. caution::

    This article explains how to create console applications and commands in
    any PHP application using the Console component. If you are working on a
    Symfony application, you don't have to do most of the things explained here.
    Instead, read the explanation of :doc:`this other article </console>`.

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
:method:`Symfony\\Component\\Console\\Application::add`::

    // ...
    $application->add(new GenerateAdminCommand());

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

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /console
    /components/console/*
    /console/*
