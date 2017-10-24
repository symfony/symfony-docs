.. index::
   single: Installing and Setting up Symfony

Installing & Setting up the Symfony Framework
=============================================

.. seealso::

    Do you prefer video tutorials? Check out the `Joyful Development with Symfony`_
    screencast series from KnpUniversity.

To create your new Symfony application, first make sure you're using PHP 7.1 or higher
and have `Composer`_ installed. If you don't, start by :doc:`installing Composer globally </setup/composer>`
on your system. If you want to use a virtual machine (VM), check out :doc:`Homestead </setup/homestead>`.

Create your new project by running:

.. code-block:: terminal

    $ composer create-project symfony/skeleton my-project

This will create a new ``my-project`` directory, download some dependencies into
it and even generate the basic directories and files you'll need to get started.
In other words, your new app is ready!

.. tip::

    You can also download a specific version of Symfony:

    .. code-block:: terminal

        # use the most recent version in any Symfony branch
        $ composer create-project symfony/skeleton my-project "3.3.*"

        # use a beta or RC version (useful for testing new Symfony versions)
        $ composer create-project symfony/skeleton my-project 3.3.0-BETA1

    Some version are long-term support (LTS) versions. Read the :doc:`Symfony Release process </contributing/community/releases>`
    to learn more.

Running your Symfony Application
--------------------------------

On production, you should use a web server like Nginx or Apache
(see :doc:`configuring a web server to run Symfony </setup/web_server_configuration>`).
But for development, it's even easier to use the :doc:`Symfony PHP web server <setup/built_in_web_server>`.

First, move into your new project and install the server:

.. code-block:: terminal

    cd my-project
    composer require server

To start the server, run:

.. code-block:: terminal

    $ php bin/console server:run

Open your browser and navigate to ``http://localhost:8000/``. If everything is working,
you'll see a welcome page. Later, when you are finished working, stop the server
by pressing ``Ctrl+C`` from your terminal.

.. tip::

    If you're using a VM, you may need to tell the server to bind to all IP addresses:

    .. code-block:: terminal

        $ php bin/console server:start 0.0.0.0:8000

    You should **NEVER** listen to all interfaces on a computer that is
    directly accessible from the Internet.

Troubleshooting: The Requirements Checker
-----------------------------------------

If you're having any problems running Symfony, your system may be missing some
`technical requirements`_.  Symfony has a "Requirements Checker" tool that you
can use to easily make sure your system is set up. First, move into your project
directory and install it:

.. code-block:: terminal

    $ composer require req-checker

The ``req-checker`` utility adds two PHP scripts to your application:
``bin/check.php`` and ``public/check.php``. Run the first one from your terminal:

.. code-block:: terminal

    php bin/check.php

This will check your CLI environment. Run the second one from a browser (e.g.
``http://localhost:8000/check.php``) to check your web server environment.

Once you've fixed any issues, uninstall the requirements checker:

.. code-block:: terminal

    $ composer remove req-checker

.. _install-existing-app:

Setting up an Existing Symfony Project
--------------------------------------

If you're working on an existing Symfony application, you'll just need to do a few
things to get your project setup. Assuming your team uses git, you can setup your
project with the following commands:

.. code-block:: terminal

    # clone the project to download its contents
    $ cd projects/
    $ git clone ...

    # make Composer install the project's dependencies into vendor/
    $ cd my-project/
    $ composer install

You'll probably also need to customize your :ref:`.env <config-dot-env>` and do a
few other project-specific tasks (e.g. creating database schema).

Checking for Security Vulnerabilities
-------------------------------------

Symfony provides a utility called the "Security Checker" (or ``sec-checker``) to
check whether your project's dependencies contain any known security
vulnerability. Run this command to install it in your application:

.. code-block:: terminal

    $ cd my-project/
    $ composer require sec-checker

From now on, this utility will be run automatically whenever you install or
update any dependency in the application. If a dependency contains a vulnerability,
you'll see a clear message.

The Symfony Demo application
----------------------------

`The Symfony Demo Application`_ is a fully-functional application that shows the
recommended way to develop Symfony applications. It's a great learning tool for
Symfony newcomers and its code contains tons of comments and helpful notes.

To check out its code and install it locally, see `symfony/symfony-demo`_.

Start Coding!
-------------

With setup behind you, it's time to :doc:`Create your first page in Symfony </page_creation>`.

Go Deeper with Setup
--------------------

.. toctree::
    :hidden:

    page_creation

.. toctree::
    :maxdepth: 1
    :glob:

    setup/homestead
    setup/new_project_git
    setup/built_in_web_server
    setup/web_server_configuration
    setup/composer
    setup/*

.. _`Joyful Development with Symfony`: http://knpuniversity.com/screencast/symfony
.. _`Composer`: https://getcomposer.org/
.. _`technical requirements`: https://symfony.com/doc/current/reference/requirements.html
.. _`The Symfony Demo application`: https://github.com/symfony/symfony-demo
.. _`symfony/symfony-demo`: https://github.com/symfony/demo
