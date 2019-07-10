.. index::
   single: Installing and Setting up Symfony

Installing & Setting up the Symfony Framework
=============================================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Stellar Development with Symfony`_
    screencast series.

Installing Symfony
------------------

Before creating your first Symfony application, make sure to meet the following
requirements:

* Your server has PHP 7.1 or higher installed (and :doc:`these PHP extensions </reference/requirements>`
  which are installed and enabled by default by PHP);
* You have `installed Composer`_, which is used to install PHP packages;
* You have installed the :doc:`Symfony local web server </setup/symfony_server>`,
  which provides all the tools you need to develop your application locally.

Once these requirements are installed, open your terminal and run any of these
commands to create the Symfony application:

.. code-block:: terminal

    # run this if you are building a traditional web application
    $ symfony new --full my_project

    # run this if you are building a microservice, console application or API
    $ symfony new my-project

The only difference between these two commands is the number of packages
installed. The ``--full`` option installs all the packages that you usually
need to build web apps. Therefore, the installation size will be much bigger.

Both commands will create a new ``my-project/`` directory, download some
dependencies into it and even generate the basic directories and files you'll
need to get started. In other words, your new app is ready!

.. seealso::

    If you can't use the ``symfony`` command provided by the Symfony local web
    server, use the alternative installation commands based on Composer and
    displayed on the `Symfony download page`_.

Running your Symfony Application
--------------------------------

On production, you should use a web server like Nginx or Apache (see
:doc:`configuring a web server to run Symfony </setup/web_server_configuration>`).
But for development, it's more convenient to use the
:doc:`Symfony Local Web Server </setup/symfony_server>` installed earlier.

This local server provides support for HTTP/2, TLS/SSL, automatic generation of
security certificates and many other features. It works with any PHP application,
not only Symfony projects, so it's a very useful development tool.

Open your terminal, move into your new project directory and start the local web
server as follows:

.. code-block:: terminal

    $ cd my-project/
    $ symfony server:start

Open your browser and navigate to ``http://localhost:8000/``. If everything is
working, you'll see a welcome page. Later, when you are finished working, stop
the server by pressing ``Ctrl+C`` from your terminal.

.. tip::

    If you're having any problems running Symfony, your system may be missing
    some technical requirements. Use the :doc:`Symfony Requirements Checker </reference/requirements>`
    tool to make sure your system is set up.

.. note::

    If you want to use a virtual machine (VM) with Vagrant, check out
    :doc:`Homestead </setup/homestead>`.

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
    setup/web_server_configuration
    setup/*

.. _`Stellar Development with Symfony`: http://symfonycasts.com/screencast/symfony
.. _`Composer`: https://getcomposer.org/
.. _`installed Composer`: https://getcomposer.org/download/
.. _`Download the Symfony local web server`: https://symfony.com/download
.. _`Symfony download page`: https://symfony.com/download
.. _`the Security Checker`: https://github.com/sensiolabs/security-checker#integration
.. _`The Symfony Demo application`: https://github.com/symfony/demo
.. _`symfony/symfony-demo`: https://github.com/symfony/demo
