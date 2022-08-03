.. index::
    single: Console; CLI
    single: Components; Console

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

Then, you can create and register the commands using
:method:`Symfony\\Component\\Console\\Application::add`::

    // ...
    // command file created in src/Command/CreateUserCommand.php
    use App\Command\CreateUserCommand;
    // ...
    $application->add(new CreateUserCommand());

See the :doc:`/console` article for information about how to create commands.

.. note::

    Do not forget to leverage Composer's `autoload`_ feature
    so that your Command can be used.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /console
    /components/console/*
    /console/*

.. _`autoload`: https://getcomposer.org/doc/04-schema.md#autoload
