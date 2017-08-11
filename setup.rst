.. index::
   single: Installing and Setting up Symfony

Installing & Setting up the Symfony Framework
=============================================

This article explains how to install Symfony and solve the most common issues
that may appear during the installation process.

.. seealso::

    Do you prefer video tutorials? Check out the `Joyful Development with Symfony`_
    screencast series from KnpUniversity.

.. _installation-creating-the-app:

Creating Symfony Applications
-----------------------------

Symfony applications are created with `Composer`_, the package manager used by
modern PHP applications. If you don't have Composer installed in your computer,
start by :doc:`installing Composer globally </setup/composer>`. Then, execute
this command to create a new empty Symfony application based on its latest
stable version:

.. code-block:: terminal

    $ composer create-project symfony/skeleton my-project

.. tip::

    If your Internet connection is slow, you may think that Composer is not
    doing anything. If that's your case, add the ``-vvv`` flag to the previous
    command to display a detailed output of everything that Composer is doing.

If your project needs to be based on a specific Symfony version, use the
optional third argument of the ``create-project`` command:

.. code-block:: terminal

    # use the most recent version in any Symfony branch
    $ composer create-project symfony/skeleton my-project "3.3.*"

    # use a specific Symfony version
    $ composer create-project symfony/skeleton my-project "3.3.5"

    # use a beta or RC version (useful for testing new Symfony versions)
    $ composer create-project symfony/skeleton my-project 3.3.0-BETA1

.. note::

    Read the :doc:`Symfony Release process </contributing/community/releases>`
    to better understand why there are several Symfony versions and which one
    to use for your projects.

Running the Symfony Application
-------------------------------

Symfony provides a utility called ``server`` that leverages the internal PHP web
server to run applications while developing them. First, install that utility
in your application:

.. code-block:: terminal

    $ cd my-project/
    $ composer require server

Then, whenever you want to run the application, execute this command:

.. code-block:: terminal

    $ php bin/console server:run

Open your browser, access the ``http://localhost:8000/`` URL and you'll see the
application running. When you are finished working on your Symfony application,
stop the server by pressing ``Ctrl+C`` from the terminal or command console.

.. tip::

    PHP's internal web server is great for developing, but should **not** be
    used on production. Instead, use Apache or Nginx.
    See :doc:`/setup/web_server_configuration`.

Checking Symfony Requirements
-----------------------------

In addition to PHP 7.1, Symfony has other `technical requirements`_ that your
server must meet. Symfony provides a tool called "Requirements Checker" (or
``req-checker``) to check those requirements:

.. code-block:: terminal

    $ cd my-project/
    $ composer require req-checker

The ``req-checker`` utility adds two PHP scripts in your application:
``bin/check.php`` and ``public/check.php``. Run the first one in the command
console and the second one in the browser. This is needed because PHP can define
a different configuration for both the command console and the web server, so
you need to check both.

Once you've fixed all the reported issues, uninstall the requirements checker:

.. code-block:: terminal

    $ composer remove req-checker

.. _installation-updating-vendors:

Updating Symfony Applications
-----------------------------

At this point, you've created a fully-functional Symfony application! Every
Symfony app depends on a number of third-party libraries stored in the
``vendor/`` directory and managed by Composer.

Updating those libraries frequently is a good practice to fix bugs and prevent
security vulnerabilities. Execute the ``update`` Composer command to update them
all at once (this can take up to several minutes to complete depending on the
complexity of your project):

.. code-block:: terminal

    $ cd my_project_name/
    $ composer update

.. _install-existing-app:

Installing an Existing Symfony Application
------------------------------------------

When working collaboratively in a Symfony application, it's uncommon to create
a new Symfony application as explained in the previous sections. Instead,
someone else has already created and submitted it to a shared repository.

It's recommended to not submit some files (``.env``) and directories (``vendor/``,
cache, logs) to the repository, so you'll have to do the following when
installing an existing Symfony application:

.. code-block:: terminal

    # clone the project to download its contents
    $ cd projects/
    $ git clone ...

    # make Composer install the project's dependencies into vendor/
    $ cd my-project/
    $ composer install

Checking for Security Vulnerabilities
-------------------------------------

Symfony provides a utility called "Security Checker" (or ``sec-checker``) to
check whether your project's dependencies contain any known security
vulnerability. Run this command to install it in your application:

.. code-block:: terminal

    $ cd my-project/
    $ composer require sec-checker

From now on, this command will be run automatically whenever you install or
update any dependency in the application.

Installing the Symfony Demo application
---------------------------------------

`The Symfony Demo Application`_ is a fully-functional application that shows the
recommended way to develop Symfony applications. It's a great learning tool for
Symfony newcomers and its code contains tons of comments and helpful notes.

Run the following command to download and install the Symfony Demo application:

.. code-block:: terminal

    $ composer create-project symfony/symfony-demo my-project

Now, enter the ``my-project/`` directory, run the internal web server and
browse ``http://127.0.0.1:8000``:

.. code-block:: terminal

    $ cd my-project
    $ php bin/console server:start

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

.. _`Joyful Development with Symfony`: http://knpuniversity.com/screencast/symfony
.. _`Composer`: https://getcomposer.org/
.. _`technical requirements`: https://symfony.com/doc/current/reference/requirements.html
.. _`The Symfony Demo application`: https://github.com/symfony/symfony-demo
