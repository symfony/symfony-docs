.. index::
   single: Installing and Setting up Symfony

Installing & Setting up the Symfony Framework
=============================================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Stellar Development with Symfony`_
    screencast series.

.. _symfony-tech-requirements:

Technical Requirements
----------------------

Before creating your first Symfony application you must:

* Install PHP 7.1 or higher and these PHP extensions (which are installed and
  enabled by default in most PHP 7 installations): `Ctype`_, `iconv`_, `JSON`_,
  `PCRE`_, `Session`_, `SimpleXML`_, and `Tokenizer`_;
* `Install Composer`_, which is used to install PHP packages;
* `Install Symfony`_, which creates in your computer a binary called ``symfony``
  that provides all the tools you need to develop your application locally.

The ``symfony`` binary provides a tool to check if your computer meets these
requirements. Open your console terminal and run this command:

.. code-block:: terminal

    $ symfony check:requirements

.. _creating-symfony-applications:

Creating Symfony Applications
-----------------------------

Open your console terminal and run any of these commands to create a new Symfony
application:

.. code-block:: terminal

    # run this if you are building a traditional web application
    $ symfony new --full my_project_name

    # run this if you are building a microservice, console application or API
    $ symfony new my_project_name

The only difference between these two commands is the number of packages
installed by default. The ``--full`` option installs all the packages that you
usually need to build web applications, so the installation size will be bigger.

If you can't or don't want to `install Symfony`_ for any reason, run these
commands to create the new Symfony application using Composer:

.. code-block:: terminal

    # run this if you are building a traditional web application
    $ composer create-project symfony/website-skeleton my_project_name

    # run this if you are building a microservice, console application or API
    $ composer create-project symfony/skeleton my_project_name

No matter which command you run to create the Symfony application. All of them
will create a new ``my_project_name/`` directory, download some dependencies
into it and even generate the basic directories and files you'll need to get
started. In other words, your new application is ready!

.. note::

    The project's cache and logs directory (by default, ``<project>/var/cache/``
    and ``<project>/var/log/``) must be writable by the web server. If you have
    any issue, read how to :doc:`set up permissions for Symfony applications </setup/file_permissions>`.

Running Symfony Applications
----------------------------

On production, you should use a web server like Nginx or Apache (see
:doc:`configuring a web server to run Symfony </setup/web_server_configuration>`).
But for development, it's more convenient to use the
:doc:`local web server </setup/symfony_server>` provided by Symfony.

This local server provides support for HTTP/2, TLS/SSL, automatic generation of
security certificates and many other features. It works with any PHP application,
not only Symfony projects, so it's a very useful development tool.

Open your console terminal, move into your new project directory and start the
local web server as follows:

.. code-block:: terminal

    $ cd my-project/
    $ symfony server:start

Open your browser and navigate to ``http://localhost:8000/``. If everything is
working, you'll see a welcome page. Later, when you are finished working, stop
the server by pressing ``Ctrl+C`` from your terminal.

.. _install-existing-app:

Setting up an Existing Symfony Project
--------------------------------------

In addition to creating new Symfony projects, you will also work on projects
already created by other developers. In that case, you only need to get the
project code and install the dependencies with Composer. Assuming your team uses
Git, setup your project with the following commands:

.. code-block:: terminal

    # clone the project to download its contents
    $ cd projects/
    $ git clone ...

    # make Composer install the project's dependencies into vendor/
    $ cd my-project/
    $ composer install

You'll probably also need to customize your :ref:`.env file <config-dot-env>`
and do a few other project-specific tasks (e.g. creating a database). When
working on a existing Symfony application for the first time, it may be useful
to run this command which displays information about the project:

.. code-block:: terminal

    $ php bin/console about

.. _symfony-flex:

Installing Packages
-------------------

A common practice when developing Symfony applications is to install packages
(Symfony calls them :doc:`bundles </bundles>`) that provide ready-to-use
features. Packages usually require some setup before using them (editing some
file to enable the bundle, creating some file to add some initial config, etc.)

Most of the time this setup can be automated and that's why Symfony includes
`Symfony Flex`_, a tool to simplify the installation/removal of packages in
Symfony applications. Technically speaking, Symfony Flex is a Composer plugin
that is installed by default when creating a new Symfony application and which
**automates the most common tasks of Symfony applications**.

.. tip::

    You can also :doc:`add Symfony Flex to an existing project </setup/flex>`.

Symfony Flex modifies the behavior of the ``require``, ``update``, and
``remove`` Composer commands to provide advanced features. Consider the
following example:

.. code-block:: terminal

    $ cd my-project/
    $ composer require logger

If you execute that command in a Symfony application which doesn't use Flex,
you'll see a Composer error explaining that ``logger`` is not a valid package
name. However, if the application has Symfony Flex installed, that command
installs and enables all the packages needed to use the official Symfony logger.

This is possible because lots of Symfony packages/bundles define **"recipes"**,
which are a set of automated instructions to install and enable packages into
Symfony applications. Flex keeps tracks of the recipes it installed in a
``symfony.lock`` file, which must be committed to your code repository.

Symfony Flex recipes are contributed by the community and they are stored in
two public repositories:

* `Main recipe repository`_, is a curated list of recipes for high quality and
  maintained packages. Symfony Flex only looks in this repository by default.

* `Contrib recipe repository`_, contains all the recipes created by the
  community. All of them are guaranteed to work, but their associated packages
  could be unmaintained. Symfony Flex will ask your permission before installing
  any of these recipes.

Read the `Symfony Recipes documentation`_ to learn everything about how to
create recipes for your own packages.

.. _security-checker:

Checking Security Vulnerabilities
---------------------------------

The ``symfony`` binary created when you `install Symfony`_ provides a command to
check whether your project's dependencies contain any known security
vulnerability:

.. code-block:: terminal

    $ symfony security:check

A good security practice is to execute this command regularly to be able to
update or replace compromised dependencies as soon as possible. The security
check is done locally by cloning the public `PHP security advisories database`_,
so your ``composer.lock`` file is not sent on the network.

.. tip::

    The ``check:security`` command terminates with a non-zero exit code if
    any of your dependencies is affected by a known security vulnerability.
    This way you can add it to your project build process and your continuous
    integration workflows to make them fail when there are vulnerabilities.

Symfony LTS Versions
--------------------

According to the :doc:`Symfony release process </contributing/community/releases>`,
"long-term support" (or LTS for short) versions are published every two years.
Check out the `Symfony roadmap`_ to know which is the latest LTS version.

By default, the command that creates new Symfony applications uses the latest
stable version. If you want to use an LTS version, add the ``--version`` option:

.. code-block:: terminal

    # find the latest LTS version at https://symfony.com/roadmap
    $ symfony new --version=3.4 my_project_name_name

    # you can also base your project on development versions
    $ symfony new --version=4.4.x-dev my_project_name
    $ symfony new --version=dev-master my_project_name

The Symfony Demo application
----------------------------

`The Symfony Demo Application`_ is a fully-functional application that shows the
recommended way to develop Symfony applications. It's a great learning tool for
Symfony newcomers and its code contains tons of comments and helpful notes.

Run this command to create a new project based on the Symfony Demo application:

.. code-block:: terminal

    $ symfony new --demo my_project_name

Start Coding!
-------------

With setup behind you, it's time to :doc:`Create your first page in Symfony </page_creation>`.

Learn More
----------

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
.. _`Install Composer`: https://getcomposer.org/download/
.. _`Install Symfony`: https://symfony.com/download
.. _`install Symfony`: https://symfony.com/download
.. _`The Symfony Demo Application`: https://github.com/symfony/demo
.. _`Symfony Flex`: https://github.com/symfony/flex
.. _`PHP security advisories database`: https://github.com/FriendsOfPHP/security-advisories
.. _`Symfony roadmap`: https://symfony.com/roadmap
.. _`Main recipe repository`: https://github.com/symfony/recipes
.. _`Contrib recipe repository`: https://github.com/symfony/recipes-contrib
.. _`Symfony Recipes documentation`: https://github.com/symfony/recipes/blob/master/README.rst
.. _`iconv`: https://php.net/book.iconv
.. _`JSON`: https://php.net/book.json
.. _`Session`: https://php.net/book.session
.. _`Ctype`: https://php.net/book.ctype
.. _`Tokenizer`: https://php.net/book.tokenizer
.. _`SimpleXML`: https://php.net/book.simplexml
.. _`PCRE`: https://php.net/book.pcre
