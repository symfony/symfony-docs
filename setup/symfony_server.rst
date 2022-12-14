Symfony Local Web Server
========================

You can run Symfony applications with any web server (Apache, nginx, the
internal PHP web server, etc.). However, Symfony provides its own web server to
make you more productive while developing your applications.

Although this server is not intended for production use, it supports HTTP/2,
TLS/SSL, automatic generation of security certificates, local domains, and many
other features that sooner or later you'll need when developing web projects.
Moreover, the server is not tied to Symfony and you can also use it with any
PHP application and even with HTML or single page applications.

Installation
------------

The Symfony server is part of the ``symfony`` binary created when you
`install Symfony`_ and has support for Linux, macOS and Windows.

.. note::

   You can view and contribute to the Symfony CLI source in the
   `symfony-cli/symfony-cli GitHub repository`_.

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

Enabling PHP-FPM
----------------

.. note::

    PHP-FPM must be installed locally for the Symfony server to utilize.

When the server starts, it checks for ``web/index_dev.php``, ``web/index.php``,
``public/app_dev.php``, ``public/app.php`` in that order. If one is found, the
server will automatically start with PHP-FPM enabled. Otherwise the server will
start without PHP-FPM and will show a ``Page not found`` page when trying to
access a ``.php`` file in the browser.

.. tip::

    When an ``index.html`` and a front controller like e.g. ``index.php`` are
    both present the server will still start with PHP-FPM enabled but the
    ``index.html`` will take precedence over the front controller. This means
    when an ``index.html`` file is present in ``public`` or ``web``, it will be
    displayed instead of the ``index.php`` which would show e.g. the Symfony
    application.

Enabling TLS
------------

Browsing the secure version of your applications locally is important to detect
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
    $ echo 7.4 > .php-version

    # use any PHP 8.x version available
    $ echo 8 > .php-version

.. tip::

    The Symfony server traverses the directory structure up to the root
    directory, so you can create a ``.php-version`` file in some parent
    directory to set the same PHP version for a group of projects under that
    directory.

Run the command below if you don't remember all the PHP versions installed on your
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

When running different PHP versions, it is useful to use the main ``symfony``
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

Local Domain Names
------------------

By default, projects are accessible at some random port of the ``127.0.0.1``
local IP. However, sometimes it is preferable to associate a domain name to them:

* It's more convenient when you work continuously on the same project because
  port numbers can change but domains don't;
* The behavior of some applications depend on their domains/subdomains;
* To have stable endpoints, such as the local redirection URL for OAuth2.

Setting up the Local Proxy
~~~~~~~~~~~~~~~~~~~~~~~~~~

Local domains are possible thanks to a local proxy provided by the Symfony server.
If this is the first time you run the proxy, you must configure it as follows:

#. Open the **proxy settings** of your operating system:

   * `Proxy settings in Windows`_;
   * `Proxy settings in macOS`_;
   * `Proxy settings in Ubuntu`_.

#. Set the following URL as the value of the **Automatic Proxy Configuration**:
   ``http://127.0.0.1:7080/proxy.pac``

Now run this command to start the proxy:

.. code-block:: terminal

    $ symfony proxy:start

If the proxy doesn't work as explained in the following sections, check these:

* Some browsers (e.g. Chrome) require to re-apply proxy settings (clicking on
  ``Re-apply settings`` button on the ``chrome://net-internals/#proxy`` page)
  or a full restart after starting the proxy. Otherwise, you'll see a
  *"This webpage is not available"* error (``ERR_NAME_NOT_RESOLVED``);
* Some Operating Systems (e.g. macOS) don't apply by default the proxy settings
  to local hosts and domains. You may need to remove ``*.local`` and/or other
  IP addresses from that list.

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

You can also add a wildcard domain:

.. code-block:: terminal

    $ symfony proxy:domain:attach "*.my-domain"

So it will match all subdomains like ``https://admin.my-domain.wip``, ``https://other.my-domain.wip``...

When running console commands, add the ``https_proxy`` env var to make custom
domains work:

.. code-block:: terminal

    # Example with curl
    $ https_proxy=$(symfony proxy:url) curl https://my-domain.wip

    # Example with Blackfire and curl
    $ https_proxy=$(symfony proxy:url) blackfire curl https://my-domain.wip

    # Example with Cypress
    $ https_proxy=$(symfony proxy:url) ./node_modules/bin/cypress open

.. note::

    Although env var names are always defined in uppercase, the ``https_proxy``
    env var `is treated differently`_ than other env vars and its name must be
    spelled in lowercase.

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

Configuring Workers
-------------------

.. caution::

    This feature is experimental and could change or be removed at any time
    without prior notice.

If you like some processes to start automatically, along with the webserver
(``symfony server:start``), add a configuration file to your project:

.. code-block:: yaml

    # .symfony.local.yaml
    workers:
        # built-in command that builds and watches front-end assets
        # yarn_encore_watch:
        #     cmd: ['yarn', 'encore', 'dev', '--watch']
        yarn_encore_watch: ~

        # built-in command that starts messenger consumer
        # messenger_consume_async:
        #     cmd: ['symfony', 'console', 'messenger:consume', 'async']
        #     watch: ['config', 'src', 'templates', 'vendor']
        messenger_consume_async: ~

        # you can also add your own custom commands
        build_spa:
            cmd: ['yarn', '--cwd', './spa/', 'dev']

.. _symfony-server-docker:

Docker Integration
------------------

The local Symfony server provides full `Docker`_ integration for projects that
use it. To learn more about Docker & Symfony, see :doc:`docker`.

When the web server detects that Docker Compose is running for the project, it
automatically exposes some environment variables.

Via the ``docker-compose`` API, it looks for exposed ports used for common
services. When it detects one it knows about, it uses the service name to
expose environment variables.

Consider the following configuration:

.. code-block:: yaml

    # docker-compose.yaml
    services:
        database:
            ports: [3306]

The web server detects that a service exposing port ``3306`` is running for the
project. It understands that this is a MySQL service and creates environment
variables accordingly with the service name (``database``) as a prefix:
``DATABASE_URL``, ``DATABASE_HOST``, ...

If the service is not in the supported list below, generic environment
variables are set: ``PORT``, ``IP``, and ``HOST``.

If the ``docker-compose.yaml`` names do not match Symfony's conventions, add a
label to override the environment variables prefix:

.. code-block:: yaml

    # docker-compose.yaml
    services:
        db:
            ports: [3306]
            labels:
                com.symfony.server.service-prefix: 'DATABASE'

In this example, the service is named ``db``, so environment variables would be
prefixed with ``DB_``, but as the ``com.symfony.server.service-prefix`` is set
to ``DATABASE``, the web server creates environment variables starting with
``DATABASE_`` instead as expected by the default Symfony configuration.

Here is the list of supported services with their ports and default Symfony
prefixes:

============= ========= ======================
Service       Port      Symfony default prefix
============= ========= ======================
MySQL         3306      ``DATABASE_``
PostgreSQL    5432      ``DATABASE_``
Redis         6379      ``REDIS_``
Memcached     11211     ``MEMCACHED_``
RabbitMQ      5672      ``RABBITMQ_`` (set user and pass via Docker ``RABBITMQ_DEFAULT_USER`` and ``RABBITMQ_DEFAULT_PASS`` env var)
Elasticsearch 9200      ``ELASTICSEARCH_``
MongoDB       27017     ``MONGODB_`` (set the database via a Docker ``MONGO_DATABASE`` env var)
Kafka         9092      ``KAFKA_``
MailCatcher   1025/1080 ``MAILER_``
              or 25/80
Blackfire     8707      ``BLACKFIRE_``
Mercure       80        Always exposes ``MERCURE_PUBLIC_URL`` and ``MERCURE_URL`` (only works with the ``dunglas/mercure`` Docker image)
============= ========= ======================

You can open web management interfaces for the services that expose them:

.. code-block:: bash

    $ symfony open:local:webmail
    $ symfony open:local:rabbitmq

Or click on the links in the "Server" section of the web debug toolbar.

.. tip::

    To debug and list all exported environment variables, run ``symfony
    var:export --debug``.

.. tip::

    For some services, the web server also exposes environment variables
    understood by CLI tools related to the service. For instance, running
    ``symfony run psql`` will connect you automatically to the PostgreSQL server
    running in a container without having to specify the username, password, or
    database name.

When Docker services are running, browse a page of your Symfony application and
check the "Symfony Server" section in the web debug toolbar; you'll see that
"Docker Compose" is "Up".

.. note::

    If you don't want environment variables to be exposed for a service, set
    the ``com.symfony.server.service-ignore`` label to ``true``:

    .. code-block:: yaml

        # docker-compose.yaml
        services:
            db:
                ports: [3306]
                labels:
                    com.symfony.server.service-ignore: true

If your Docker Compose file is not at the root of the project, use the
``COMPOSE_FILE`` and ``COMPOSE_PROJECT_NAME`` environment variables to define
its location, same as for ``docker-compose``:

.. code-block:: bash

    # start your containers:
    COMPOSE_FILE=docker/docker-compose.yaml COMPOSE_PROJECT_NAME=project_name docker-compose up -d

    # run any Symfony CLI command:
    COMPOSE_FILE=docker/docker-compose.yaml COMPOSE_PROJECT_NAME=project_name symfony var:export

.. note::

    If you have more than one Docker Compose file, you can provide them all
    separated by ``:`` as explained in the `Docker compose CLI env var reference`_.

.. caution::

    When using the Symfony binary with ``php bin/console`` (``symfony console ...``),
    the binary will **always** use environment variables detected via Docker and will
    ignore local environment variables.
    For example if you set up a different database name in your ``.env.test`` file
    (``DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/test``) and if you run
    ``symfony console doctrine:database:drop --force --env=test``, the command will drop the database
    defined in your Docker configuration and not the "test" one.

SymfonyCloud Integration
------------------------

The local Symfony server provides full, but optional, integration with
`SymfonyCloud`_, a service optimized to run your Symfony applications on the
cloud. It provides features such as creating environments, backups/snapshots,
and even access to a copy of the production data from your local machine to help
debug any issues.

`Read SymfonyCloud technical docs`_.

.. _`install Symfony`: https://symfony.com/download
.. _`symfony-cli/symfony-cli GitHub repository`: https://github.com/symfony-cli/symfony-cli
.. _`Docker`: https://en.wikipedia.org/wiki/Docker_(software)
.. _`SymfonyCloud`: https://symfony.com/cloud/
.. _`Read SymfonyCloud technical docs`: https://symfony.com/doc/master/cloud/intro.html
.. _`Proxy settings in Windows`: https://www.dummies.com/computers/operating-systems/windows-10/how-to-set-up-a-proxy-in-windows-10/
.. _`Proxy settings in macOS`: https://support.apple.com/guide/mac-help/enter-proxy-server-settings-on-mac-mchlp2591/mac
.. _`Proxy settings in Ubuntu`: https://help.ubuntu.com/stable/ubuntu-help/net-proxy.html.en
.. _`is treated differently`: https://ec.haxx.se/usingcurl/usingcurl-proxies#http_proxy-in-lower-case-only
.. _`Docker compose CLI env var reference`: https://docs.docker.com/compose/reference/envvars/
