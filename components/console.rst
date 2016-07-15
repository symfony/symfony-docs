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

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/console`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/console).

.. include:: /components/require_autoload.rst.inc

Creating an Application File
----------------------------

At first, you need to create a file to run commands from the console::

    #!/usr/bin/env php
    <?php
    // application.php

    require __DIR__.'/vendor/autoload.php';

    use Symfony\Component\Console\Application;

    $application = new Application();

    // ... register commands

    $application->run();

See the :doc:`/console` article for information about how to create commands.
You can register the commands using
:method:`Symfony\\Component\\Console\\Application::add`::

    // ...
    $application->add(new GenerateAdminCommand());

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /console
    /components/console/*
    /components/console/helpers/index

.. _Packagist: https://packagist.org/packages/symfony/console
