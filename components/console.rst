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

Alternatively, you can clone the `<https://github.com/symfony/console>`_ repository.

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

See the :doc:`/console` article for information about how to create commands.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /console
    /components/console/*
    /components/console/helpers/index
    /console/*

.. _Packagist: https://packagist.org/packages/symfony/console
