.. index::
   single: Installing and Setting up Symfony

Installing & Setting up the Symfony Framework
=============================================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Stellar Development with Symfony`_
    screencast series.

To create your new Symfony application, first make sure you're using PHP 7.1 or higher
and have `Composer`_ installed. If you don't, start by :doc:`installing Composer globally </setup/composer>`
on your system. If you want to use a virtual machine (VM), check out :doc:`Homestead </setup/homestead>`.

Create your new project by running:

.. code-block:: terminal

    $ composer create-project symfony/website-skeleton my-project

This will create a new ``my-project`` directory, download some dependencies into
it and even generate the basic directories and files you'll need to get started.
In other words, your new app is ready!

.. tip::

    The ``website-skeleton`` is optimized for traditional web applications. If
    you are building microservices, console applications or APIs, consider
    using the much simpler ``skeleton`` project:

    .. code-block:: terminal

        $ composer create-project symfony/skeleton my-project

        # optional: install the web server bundle (explained next)
        $ cd my-project
        $ composer require --dev symfony/web-server-bundle

Running your Symfony Application
--------------------------------

On production, you should use a web server like Nginx or Apache
(see :doc:`configuring a web server to run Symfony </setup/web_server_configuration>`).
But for development, it's convenient to use the :doc:`Symfony PHP web server <setup/built_in_web_server>`.

Move into your new project and start the server:

.. code-block:: terminal

    $ cd my-project
    $ php bin/console server:run

Open your browser and navigate to ``http://localhost:8000/``. If everything is working,
you'll see a welcome page. Later, when you are finished working, stop the server
by pressing ``Ctrl+C`` from your terminal.

.. tip::

    If you're having any problems running Symfony, your system may be missing
    some technical requirements. Use the :doc:`Symfony Requirements Checker </reference/requirements>`
    tool to make sure your system is set up.

.. tip::

    If you're using a VM, you may need to tell the server to bind to all IP addresses:

    .. code-block:: terminal

        $ php bin/console server:start 0.0.0.0:8000

    You should **NEVER** listen to all interfaces on a computer that is
    directly accessible from the Internet.

Storing your Project in git
---------------------------

Storing your project in services like GitHub, GitLab and Bitbucket works like with
any other code project! Init a new repository with ``Git`` and you are ready to push
to your remote:

.. code-block:: terminal

    $ git init
    $ git add .
    $ git commit -m "Initial commit"

Your project already has a sensible ``.gitignore`` file. And as you install more
packages, a system called :ref:`Flex <flex-quick-intro>` will add more lines to
that file when needed.

.. _install-existing-app:

Setting up an Existing Symfony Project
--------------------------------------

If you're working on an existing Symfony application, you only need to get the
project code and install the dependencies with Composer. Assuming your team uses Git,
setup your project with the following commands:

.. code-block:: terminal

    # clone the project to download its contents
    $ cd projects/
    $ git clone ...

    # make Composer install the project's dependencies into vendor/
    $ cd my-project/
    $ composer install

You'll probably also need to customize your :ref:`.env <config-dot-env>` and do a
few other project-specific tasks (e.g. creating database schema). When working
on a existing Symfony app for the first time, it may be useful to run this
command which displays information about the app:

.. code-block:: terminal

    $ php bin/console about

Checking for Security Vulnerabilities
-------------------------------------

Symfony provides a utility called the "Security Checker" to check whether your
project's dependencies contain any known security vulnerability. Check out
the integration instructions for `the Security Checker`_ to set it up.

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
    setup/built_in_web_server
    setup/web_server_configuration
    setup/composer
    setup/*

.. _`Stellar Development with Symfony`: http://symfonycasts.com/screencast/symfony
.. _`Composer`: https://getcomposer.org/
.. _`the Security Checker`: https://github.com/sensiolabs/security-checker#integration
.. _`The Symfony Demo application`: https://github.com/symfony/demo
.. _`symfony/symfony-demo`: https://github.com/symfony/demo
