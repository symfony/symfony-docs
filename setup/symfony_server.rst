Symfony Local Web Server
========================

You can run Symfony applications with any web server (Apache, nginx, the
internal PHP web server, etc.). However, Symfony provides its own web server to
make you more productive while developing your applications.

Although this server is not intended for production use, it supports HTTP/2,
TLS/SSL, automatic generation of security certificates, local domains, and many
other features that sooner or later you'll need when developing web projects.
Moreover, the server is not tied to Symfony and you can also use it with any
PHP application and even with HTML/SPA (single page applications).

Installation
------------

The Symfony server is distributed as a free installable binary without any
dependency and support for Linux, macOS and Windows. Go to `symfony.com/download`_
and follow the instructions for your operating system.

Getting Started
---------------

The Symfony server is started once per project, so you may end up with several
instances (each of them listening to a different port). This is the common
workflow to serve a Symfony project:

.. code-block:: terminal

    $ cd my-project/
    $ symfony server:start

      [OK] Web server listening on http://127.0.0.1:....
      ...

    # Now, browse the given URL, or run this command:
    $ symfony open:local

Running the server this way makes it display the log messages in the console, so
you won't be able to run other commands at the same time. If you prefer, you can
run the Symfony server in the background:

.. code-block:: terminal

    $ cd my-project/

    # start the server in the background
    $ symfony server:start -d

    # continue working and running other commands...

    # show the latest log messages
    $ symfony server:log

Enabling TLS
------------

Browsing the secure version of your apps locally is important to detect
problems with mixed content early, and to run libraries that only run in HTTPS.
Traditionally this has been painful and complicated to set up, but the Symfony
server automates everything. First, run this command:

.. code-block:: terminal

    $ symfony server:ca:install

This command creates a local certificate authority, registers it in your system
trust store, registers it in Firefox (this is required only for that browser)
and creates a default certificate for ``localhost`` and ``127.0.0.1``. In other
words, it does everything for you.

Before browsing your local application with HTTPS instead of HTTP, restart its
server stopping and starting it again.

Different PHP Settings Per Project
----------------------------------

Selecting a Different PHP Version
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you have multiple PHP versions installed on your computer, you can tell
Symfony which one to use creating a file called ``.php-version`` at the project
root directory:

.. code-block:: terminal

    $ cd my-project/

    # use a specific PHP version
    $ echo "7.2" > .php-version

    # use any PHP 7.x version available
    $ echo "7" > .php-version

.. tip::

    The Symfony server traverses the directory structure up to the root
    directory, so you can create a ``.php-version`` file in some parent
    directory to set the same PHP version for a group of projects under that
    directory.

Run command if you don't remember all the PHP versions installed on your
computer:

.. code-block:: terminal

    $ symfony local:php:list

      # You'll see all supported SAPIs (CGI, FastCGI, etc.) for each version.
      # FastCGI (php-fpm) is used when possible; then CGI (which acts as a FastCGI
      # server as well), and finally, the server falls back to plain CGI.

Overriding PHP Config Options Per Project
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can change the value of any PHP runtime config option per project by creating a
file called ``php.ini`` at the project root directory. Add only the options you want
to override:

.. code-block:: terminal

    $ cd my-project/

    # this project only overrides the default PHP timezone
    $ cat php.ini
    [Date]
    date.timezone = Asia/Tokyo

Running Commands with Different PHP Versions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When running different PHP versions it's useful to use the main ``symfony``
command as a wrapper for the ``php`` command. This allows you to always select
the most appropriate PHP version according to the project which is running the
commands. It also loads the env vars automatically, which is important when
running non-Symfony commands:

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

    # other PHP commands can be wrapped too using this trick
    $ cp symfony php-config
    $ cp symfony pear
    $ cp symfony pecl

Local Domain Names
------------------

By default, projects are accessible at some random port of the ``127.0.0.1``
local IP. However, sometimes it is preferable to associate a domain name to them:

* It's more convenient when you work continuously on the same project because
  port numbers can change but domains don't;
* The behavior of some applications depend on their domains/subdomains;
* To have stable endpoints, such as the local redirection URL for Oauth2.

Setting up the Local Proxy
~~~~~~~~~~~~~~~~~~~~~~~~~~

Local domains are possible thanks to a local proxy provided by the Symfony
server. First, start the proxy:

.. code-block:: terminal

    $ symfony proxy:start

If this is the first time you run the proxy, you must follow these additional steps:

* Open the **network configuration** of your operating system;
* Find the **proxy settings** and select the **"Automatic Proxy Configuration"**;
* Set the following URL as its value: ``http://127.0.0.1:7080/proxy.pac``

Defining the Local Domain
~~~~~~~~~~~~~~~~~~~~~~~~~

By default, Symfony proposes ``.wip`` (for *Work in Progress*) for the local
domains. You can define a local domain for your project as follows:

.. code-block:: terminal

    $ cd my-project/
    $ symfony proxy:domain:attach my-domain

If you have installed the local proxy as explained in the previous section, you
can now browse ``https://my-domain.wip`` to access your local project with the
new custom domain.

.. tip::

    Browse the http://127.0.0.1:7080 URL to get the full list of local project
    directories, their custom domains, and port numbers.

When running console commands, add the ``HTTPS_PROXY`` env var to make custom
domains work:

.. code-block:: terminal

    $ HTTPS_PROXY=http://127.0.0.1:7080 curl https://my-domain.wip

.. tip::

    If you prefer to use a different TLD, edit the ``~/.symfony/proxy.json``
    file (where ``~`` means the path to your user directory) and change the
    value of the ``tld`` option from ``wip`` to any other TLD.

Long-Running Commands
---------------------

Long-running commands, such as the ones that compile front-end web assets, block
the terminal and you can't run other commands at the same time. The Symfony
server provides a ``run`` command to wrap them as follows:

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

    # stop the web server (and all the associated commands) when you are finished
    $ symfony server:stop

Docker Integration
------------------

The local Symfony server provides full `Docker`_ integration for projects that
use it. First, make sure to expose the container ports:

.. code-block:: yaml

    # docker-compose.override.yaml
    services:
        database:
            ports:
                - "3306"

        redis:
            ports:
                - "6379"

        # ...

Then, check your service names and update them if needed (Symfony creates
environment variables following the name of the services so they can be
autoconfigured):

.. code-block:: yaml

    # docker-compose.yaml
    services:
        # DATABASE_URL
        database: ...
        # MONGODB_DATABASE, MONGODB_SERVER
        mongodb: ...
        # REDIS_URL
        redis: ...
        # ELASTISEARCH_HOST, ELASTICSEARCH_PORT
        elasticsearch: ...
        # RABBITMQ_DSN
        rabbitmq: ...

If you can't or don't want to update the service names, you must remap the env
vars so Symfony can find them. For example, if you want to keep a service called
``mysql`` instead of renaming it to ``database``, the env var will be called
``MYSQL_URL`` instead of the ``DATABASE_URL`` env var used in the Symfony
application, so you add the following to the ``.env.local`` file:

.. code-block:: bash

    # .env.local
    MYSQL_URL=${DATABASE_URL}
    # ...

Now you can start the containers and all their services will be exposed. Browse
any page of your application and check the "Symfony Server" section in the web
debug toolbar. You'll see that "Docker Compose" is "Up".

SymfonyCloud Integration
------------------------

The local Symfony server provides full, but optional, integration with
`SymfonyCloud`_, a service optimized to run your Symfony applications on the
cloud. It provides features such as creating environments, backups/snapshots,
and even access to a copy of the production data from your local machine to help
debug any issues.

`Read SymfonyCloud technical docs`_.

Bonus Features
--------------

In addition to being a local web server, the Symfony server provides other
useful features:

Looking for Security Vulnerabilities
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of installing the :doc:`Symfony Security Checker </security/security_checker>`
as a dependency of your projects, you can run the following command:

.. code-block:: terminal

    $ symfony security:check

This command uses the same vulnerability database as the Symfony Security
Checker but it does not make HTTP calls to the official API endpoint. Everything
(except cloning the public database) is done locally, which is the best for CI
(*continuous integration*) scenarios.

Creating Symfony Projects
~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to the `different ways of installing Symfony`_, you can use these
commands from the Symfony server:

.. code-block:: terminal

    # creates a new project based on symfony/skeleton
    $ symfony new my_project_name

    # creates a new project based on symfony/website-skeleton
    $ symfony new --full my_project_name

    # creates a new project based on the Symfony Demo application
    $ symfony new --demo my_project_name

.. _`symfony.com/download`: https://symfony.com/download
.. _`different ways of installing Symfony`: https://symfony.com/download
.. _`Docker`: https://en.wikipedia.org/wiki/Docker_(software)
.. _`SymfonyCloud`: https://symfony.com/cloud/
.. _`Read SymfonyCloud technical docs`: https://symfony.com/doc/master/cloud/intro.html
