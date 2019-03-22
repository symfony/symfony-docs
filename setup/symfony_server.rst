Symfony Local Web Server
========================

You can run Symfony applications with any web server (Apache, nginx, the
internal PHP web server, etc.) However, Symfony provides its own web server to
make you more productive while developing your apps.

This server is not intended for production use and it supports HTTP/2, TLS/SSL,
automatic generation of security certificates, local domains, and many other
features that sooner or later you'll need when developing web projects.

Installation
------------

The Symfony server is distributed as a free installable binary without any
dependency and support for Linux, macOS and Windows. Go to `symfony.com/download`_
and follow the instructions for your operating system.

Getting Started
---------------

The Symfony web server is started once per project, so you may end up with
several instances (each of them listening to a different port). This is the
common workflow to serve a Symfony project:

.. code-block:: terminal

    $ cd my-project/
    $ symfony server:start

      [OK] Web server listening on http://127.0.0.1:....
      ...

    # Now, browse the given URL in your browser, or run this command:
    $ symfony open:local

Running the server this way makes it display the log messages in the console, so
you can't run other commands. If you prefer, you can run the Symfony server in
the background:

.. code-block:: terminal

    $ cd my-project/

    # start the server in the background
    $ symfony server:start -d

    # continue working and running other commands...

    # show the latest log messages
    $ symfony server:log

Enabling TLS
------------

Browsing the secure version of your apps locally is important to early detect
problems with mixed content and to run libraries that only run in HTTPS.
Traditionally this has been painful and complicated to set up, but the Symfony
server automates everything. First, run this command:

.. code-block:: terminal

    $ symfony server:ca:install

This command creates a local certificate authority, registers it in your system
trust store, registers it in Firefox (this is required only for that browser)
and creates a default certificate for ``localhost`` and ``127.0.0.1``. In other
words, it does everything for you. You can now browse your local app using
HTTPS instead of HTTP.

Different PHP Settings Per Project
----------------------------------

Selecting a Different PHP Version
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you have multiple PHP versions installed in your computer, you can tell
Symfony which one to use creating a file called ``.php-version`` at the project
root dir:

.. code-block:: terminal

    $ cd my-project/

    # use a specific PHP version
    $ echo "7.2" > .php-version

    # use any PHP 7.x version available
    $ echo "7" > .php-version

This other command is useful if you don't remember all the PHP versions
installed in your computer:

.. code-block:: terminal

    $ symfony local:php:list

Overriding PHP Config Options Per Project
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can change the value of any PHP runtime config option per project creating a
file called ``php.ini`` at the project root dir. Add only the options you want
to override:

.. code-block:: terminal

    $ cd my-project/

    # this project only overrides the default PHP timezone
    $ cat php.ini
    [Date]
    date.timezone = Asia/Tokyo

Running Commands with Different PHP Versions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When running different PHP versions, it's useful to use the main ``symfony``
command as a wrapper of the ``php`` command to always select the most
appropriate PHP version according to the project which is running the commands:

.. code-block:: terminal

    # runs the command with the default PHP version
    $ php -r "..."

    # runs the command with the PHP version selected by the project
    # (or the default PHP version if the project didn't select one)
    $ symfony php -r "..."

If you are using this wrapper frequently, consider aliasing the ``php`` command
to it:

.. code-block:: terminal

    $ cd ~/.symfony/bin
    $ cp symfony php
    # now you can run "php ..." and the "symfony" command will be executed instead

Local Domain Names
------------------

By default, projects are accessible at some random port of the ``12.7.0.0.1``
local IP. However, sometimes is preferable to associate a domain name to them:

* It's more convenient when you work continuously on the same project because
  port numbers can change but domains don't;
* The behavior of some apps depend on their domains/subdomains;
* To have stable endpoints, such as the local redirection URL of Oauth2.

Setting up the Local Proxy
~~~~~~~~~~~~~~~~~~~~~~~~~~

Local domains are possible thanks to a local proxy provided by the Symfony
server. First, start the proxy:

.. code-block:: terminal

    $ symfony proxy:start

If this is the first time you run the proxy, you must follow these additional steps:

* Open the **network configuration** of your operating system;
* Find the **proxy settings** and select the **"Automatic Proxy Configuration"**;
* Set the following URL as its value: ``https://127.0.0.1:7080/proxy.pac``

Defining the Local Domain
~~~~~~~~~~~~~~~~~~~~~~~~~

By default, Symfony proposes ``.wip`` (for *Work in Progress*) as the local
domains (but you can choose any other domain and TLD you like). Define a local
domain for a project as follows:

.. code-block:: terminal

    $ cd my-project/
    $ symfony proxy:domain:attach my-domain.wip

If you have installed the local proxy as explained in the previous section, you
can now browse ``https://my-domain.wip`` to access to your local project with
the new custom domain.

.. tip::

    Browse the https://127.0.0.1:7080 URL to get the full list of local project
    directories, their custom domains, and port numbers.

When running console commands, add the ``HTTPS_PROXY`` env var to make custom
domains work:

.. code-block:: terminal

    $ HTTPS_PROXY=https://127.0.0.1:7080 curl https://my-domain.wip

Long-Running Commands
---------------------

Long-running commands, such as the ones related to compiling front-end web
assets, block the terminal and you can't run other commands. The Symfony server
provides a ``run`` command to wrap them as follows:

.. code-block:: terminal

    # compile Webpack assets using Symfony Encore ... but do that in the
    # background to not block the terminal
    $ symfony run -d yarn encore dev --watch

    # continue working and running other commands...

    # from time to time, check the command logs if you want
    $ symfony server:log

    # and you can also check if the command is still running
    $ symfony server:status
    Web server listening on ...
    Command "yarn ..." running with PID ...

    # stop the command (and the whole server) when you are finished
    $ symfony server:stop

Bonus Features
--------------

The Symfony server is much more than a local web server and it includes other
useful features.

Looking for Security Vulnerabilities
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of installing the :doc:`Symfony Security Checker </security/security_checker>`
as a dependency of your projects, you can use this command from the Symfony server:

.. code-block:: terminal

    $ symfony security:check

This command uses the same vulnerability database as the Symfony Security
Checker but it also caches that information to keep checking security when
it's not possible to access to that public database.

Creating Symfony Projects
~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to the `different ways to install Symfony`_, you can use this
command from the Symfony server:

.. code-block:: terminal

    # creates a new project based on the Symfony Skeleton
    $ symfony new my_project_name

    # creates a new project based on the Symfony Website Skeleton
    $ symfony new --full my_project_name

    # creates a new project based on the Symfony Demo application
    $ symfony new --demo my_project_name

SymfonyCloud Integration
------------------------

The local Symfony server provides full, but optional, integration with
`SymfonyCloud`_, a service optimized to run your Symfony apps on the cloud.
It provides features such as creating environments, backups/snapshots, and
even access to a copy of the production data in your local machine to help
you debug any issues.

`Read SymfonyCloud technical docs`_.

.. _`symfony.com/download`: https://symfony.com/download
.. _`different ways to install Symfony`: https://symfony.com/download
.. _`SymfonyCloud`: https://symfony.com/cloud/
.. _`Read SymfonyCloud technical docs`: https://symfony.com/doc/master/cloud/intro.html
