.. index::
   single: Installing and Setting up Symfony

Installing & Setting up the Symfony Framework
=============================================

This article explains how to install Symfony in different ways and how to solve
the most common issues that may appear during the installation process.

Creating Symfony Applications
-----------------------------

Symfony provides a dedicated application called the **Symfony Installer** to ease
the creation of Symfony applications. This installer is a PHP 5.4 compatible
executable that needs to be installed on your system only once:

.. code-block:: terminal

    # Linux and macOS systems
    $ sudo mkdir -p /usr/local/bin
    some
    output

    stuff
    $ sudo curl -LsS https://symfony.com/installer -o /usr/local/bin/symfony
    $ sudo chmod a+x /usr/local/bin/symfony

    # Windows systems
    c:\> php -r "readfile('https://symfony.com/installer');" > symfony

.. note::

    In Linux and macOS, a global ``symfony`` command is created. In Windows,
    move the ``symfony`` file to a directory that's included in the ``PATH``
    environment variable to create the global command or move it to any other
    directory convenient for you:

    .. code-block:: terminal

        # for example, if WAMP is used ...
        c:\> move symfony c:\wamp\bin\php
        # ... then, execute the command as:
        c:\> symfony

        # moving it to your projects folder ...
        c:\> move symfony c:\projects
        # ... then, execute the command as
        c:\> cd projects
        c:\projects\> php symfony

.. _installation-creating-the-app:

Once the Symfony Installer is installed, create your first Symfony application
with the ``new`` command:

.. code-block:: terminal

    $ symfony new my_project_name

This command creates a new directory called ``my_project_name/`` that contains
an empty project based on the most recent stable Symfony version available. In
addition, the installer checks if your system meets the technical requirements
to execute Symfony applications. If not, you'll see the list of changes needed
to meet those requirements.

.. note::

    If the installer doesn't work for you or doesn't output anything, make sure
    that the PHP `Phar extension`_ is installed and enabled on your computer.

Basing your Project on a Specific Symfony Version
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In case your project needs to be based on a specific Symfony version, use the
optional second argument of the ``new`` command:

.. code-block:: terminal

    # use the most recent version in any Symfony branch
    $ symfony new my_project_name 2.8
    $ symfony new my_project_name 3.1

    # use a specific Symfony version
    $ symfony new my_project_name 2.8.3
    $ symfony new my_project_name 3.1.5

    # use a beta or RC version (useful for testing new Symfony versions)
    $ symfony new my_project 2.7.0-BETA1
    $ symfony new my_project 2.7.0-RC1

    # use the most recent 'lts' version (Long Term Support version)
    $ symfony new my_project_name lts

Each version has its *own* documentation, which you can select on any documentation
page.

.. note::

    Read the :doc:`Symfony Release process </contributing/community/releases>`
    to better understand why there are several Symfony versions and which one
    to use for your projects.

Creating Symfony Applications with Composer
-------------------------------------------

If you still use PHP 5.3 or can't use the Symfony installer for any reason, you
can create Symfony applications with `Composer`_, the dependency manager used by
modern PHP applications.

If you don't have Composer installed in your computer, start by
:doc:`installing Composer globally </setup/composer>`. Then, execute the
``create-project`` command to create a new Symfony application based on its
latest stable version:

.. code-block:: terminal

    $ composer create-project symfony/framework-standard-edition my_project_name

You can also install any other Symfony version by passing a second argument to
the ``create-project`` command:

.. code-block:: terminal

    $ composer create-project symfony/framework-standard-edition my_project_name "2.8.*"

.. tip::

    If your Internet connection is slow, you may think that Composer is not
    doing anything. If that's your case, add the ``-vvv`` flag to the previous
    command to display a detailed output of everything that Composer is doing.

Running the Symfony Application
-------------------------------

Symfony leverages the internal PHP web server (available since PHP 5.4) to run
applications while developing them. Therefore, running a Symfony application is
a matter of browsing to the project directory and executing this command:

.. code-block:: terminal

    $ cd my_project_name/
    $ php app/console server:run

Then, open your browser and access the ``http://localhost:8000/`` URL to see the
Welcome Page of Symfony:

.. image:: /_images/quick_tour/welcome.png
   :align: center
   :alt:   Symfony Welcome Page
   :class: with-browser

If you see a blank page or an error page instead of the Welcome Page, there is
a directory permission misconfiguration. The solution to this problem is
explained in the :doc:`/setup/file_permissions`.

When you are finished working on your Symfony application, stop the server by
pressing ``Ctrl+C`` from the terminal or command console.

.. tip::

    PHP's internal web server is great for developing, but should **not** be
    used on production. Instead, use Apache or Nginx.
    See :doc:`/setup/web_server_configuration`.

Checking Symfony Application Configuration and Setup
----------------------------------------------------

The Symfony Installer checks if your system is ready to run Symfony applications.
However, the PHP configuration for the command console can be different from the
PHP web configuration. For that reason, Symfony provides a visual configuration
checker. Access the following URL to check your configuration and fix any issue
before moving on:

.. code-block:: text

    http://localhost:8000/config.php

Fixing Permissions Problems
---------------------------

If you have any file permission errors or see a white screen, then read
:doc:`/setup/file_permissions` for more information.

.. _installation-updating-vendors:

Updating Symfony Applications
-----------------------------

At this point, you've created a fully-functional Symfony application! Every Symfony
app depends on a number of third-party libraries stored in the ``vendor/`` directory
and managed by Composer.

Updating those libraries frequently is a good practice to prevent bugs and
security vulnerabilities. Execute the ``update`` Composer command to update them
all at once (this can take up to several minutes to complete depending on the
complexity of your project):

.. code-block:: terminal

    $ cd my_project_name/
    $ composer update

.. tip::

    Symfony provides a command to check whether your project's dependencies
    contain any known security vulnerability:

    .. code-block:: terminal

        $ php app/console security:check

    A good security practice is to execute this command regularly to be able to
    update or replace compromised dependencies as soon as possible.

.. _installing-a-symfony2-distribution:

Installing the Symfony Demo or Other Distributions
--------------------------------------------------

You've already downloaded the `Symfony Standard Edition`_: the default starting project
for all Symfony apps. You'll use this project throughout the documentation to build
your app!

Symfony also provides some other projects and starting skeletons that you can use:

`The Symfony Demo Application`_
    This is a fully-functional application that shows the recommended way to develop
    Symfony applications. The app has been conceived as a learning tool for Symfony
    newcomers and its source code contains tons of comments and helpful notes.

`The Symfony CMF Standard Edition`_
    The `Symfony CMF`_ is a project that helps make it easier for developers to add
    CMS functionality to their Symfony applications. This is a starting project
    containing the Symfony CMF.

`The Symfony REST Edition`_
    Shows how to build an application that provides a RESTful API using the
    `FOSRestBundle`_ and several other related Bundles.

.. _install-existing-app:

Installing an Existing Symfony Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When working collaboratively in a Symfony application, it's uncommon to create
a new Symfony application as explained in the previous sections. Instead,
someone else has already created and submitted it to a shared repository.

It's recommended to not submit some files (:ref:`parameters.yml <config-parameters-yml>`)
and directories (``vendor/``, cache, logs) to the repository, so you'll have to do
the following when installing an existing Symfony application:

.. code-block:: terminal

    # clone the project to download its contents
    $ cd projects/
    $ git clone ...

    # make Composer install the project's dependencies into vendor/
    $ cd my_project_name/
    $ composer install

    # now Composer will ask you for the values of any undefined parameter
    $ ...

Keep Going!
-----------

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

.. _`Composer`: https://getcomposer.org/
.. _`Phar extension`: http://php.net/manual/en/intro.phar.php
.. _`Symfony Standard Edition`: https://github.com/symfony/symfony-standard
.. _`The Symfony Demo application`: https://github.com/symfony/symfony-demo
.. _`The Symfony CMF Standard Edition`: https://github.com/symfony-cmf/standard-edition
.. _`Symfony CMF`: http://cmf.symfony.com/
.. _`The Symfony REST Edition`: https://github.com/gimler/symfony-rest-edition
.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle
