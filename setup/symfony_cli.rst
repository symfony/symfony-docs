.. _symfony-server:
.. _symfony-local-web-server:

Symfony CLI
===========

The **Symfony CLI** is a free and `open source`_ developer tool to help you build,
run, and manage your Symfony applications directly from your terminal. It's designed
to boost your productivity with smart features like:

* **Web server** optimized for development, with **HTTPS support**
* **Docker** integration and automatic environment variable management
* Management of multiple **PHP versions**
* Support for background **workers**
* Seamless integration with **Symfony Cloud**

Installation
------------

The Symfony CLI is available as a standalone executable that supports Linux,
macOS, and Windows. Download and install it following the instructions on
`symfony.com/download`_.

.. _symfony-cli-autocompletion:

Shell Autocompletion
~~~~~~~~~~~~~~~~~~~~

The Symfony CLI supports autocompletion for Bash, Zsh, and Fish shells. This
helps you type commands faster and discover available options:

.. code-block:: terminal

    # install autocompletion (do this only once)
    $ symfony completion bash | sudo tee /etc/bash_completion.d/symfony

    # for Zsh users
    $ symfony completion zsh > ~/.symfony_completion && echo "source ~/.symfony_completion" >> ~/.zshrc

    # for Fish users
    $ symfony completion fish | source

After installation, restart your terminal to enable autocompletion. The CLI will
also provide autocompletion for ``composer`` and ``console`` commands when it
detects a Symfony project.

Creating New Symfony Applications
---------------------------------

The Symfony CLI includes a project creation command that helps you start new
projects quickly:

.. code-block:: terminal

    # create a new Symfony project based on the latest stable version
    $ symfony new my_project

    # create a project with the latest LTS (Long Term Support) version
    $ symfony new my_project --version=lts

    # create a project based on a specific Symfony version
    $ symfony new my_project --version=6.4

    # create a project using the development version
    $ symfony new my_project --version=next

    # all the previous commands create minimal projects with the least
    # amount of dependencies possible; if you are building a website or
    # web application, add this option to install all the common dependencies
    $ symfony new my_project --webapp

    # Create a project based on the Symfony Demo application
    $ symfony new my_project --demo

.. tip::

    Pass the ``--cloud`` option to initialize a Symfony Cloud project at the same
    time the Symfony project is created.

.. _symfony-cli-server:

Running the Local Web Server
----------------------------

The Symfony CLI includes a **local web server** designed for development. It's
not intended for production use, but it provides features that improve the
developer experience:

* HTTPS support with automatic certificate generation
* HTTP/2 support
* Automatic PHP version selection
* Integration with Docker services
* Built-in proxy for custom domain names

.. _getting-started:

Serving Your Application
~~~~~~~~~~~~~~~~~~~~~~~~

To serve a Symfony project with the local server:

.. code-block:: terminal

    $ cd my-project/
    $ symfony server:start

      [OK] Web server listening on http://127.0.0.1:8000
      ...

Now browse the given URL or run the following command to open it in the browser:

.. code-block:: terminal

    $ symfony open:local

.. tip::

    If you work on more than one project, you can run multiple instances of the
    Symfony server on your development machine. Each instance will find a different
    available port.

The ``server:start`` command blocks the current terminal to output the server
logs. To run the server in the background:

.. code-block:: terminal

    $ symfony server:start -d

Now you can continue working in the terminal and run other commands:

.. code-block:: terminal

    # view the latest log messages
    $ symfony server:log

    # stop the background server
    $ symfony server:stop

.. tip::

    On macOS, when starting the Symfony server you might see a warning dialog asking
    *"Do you want the application to accept incoming network connections?"*.
    This happens when running unsigned applications that are not listed in the
    firewall list. The solution is to run this command to sign the Symfony CLI:

    .. code-block:: terminal

        $ sudo codesign --force --deep --sign - $(whereis -q symfony)

Enabling PHP-FPM
~~~~~~~~~~~~~~~~

.. note::

    PHP-FPM must be installed locally for the Symfony server to utilize.

When the server starts, it checks for ``web/index_dev.php``, ``web/index.php``,
``public/app_dev.php``, ``public/app.php``, ``public/index.php`` in that order. If one is found, the
server will automatically start with PHP-FPM enabled. Otherwise the server will
start without PHP-FPM and will show a ``Page not found`` page when trying to
access a ``.php`` file in the browser.

.. tip::

    When an ``index.html`` and a front controller (e.g. ``index.php``) are both
    present, the server will still start with PHP-FPM enabled, but the
    ``index.html`` will take precedence. This means that if an ``index.html``
    file is present in ``public/`` or ``web/``, it will be displayed instead of
    the ``index.php``, which would otherwise show, for example, the Symfony
    application.

Enabling HTTPS/TLS
~~~~~~~~~~~~~~~~~~

Running your application over HTTPS locally helps detect mixed content issues
early and allows using features that require secure connections. Traditionally,
this has been painful and complicated to set up, but the Symfony server automates
everything for you:

.. code-block:: terminal

    # install the certificate authority (run this only once on your machine)
    $ symfony server:ca:install

    # now start (or restart) your server; it will use HTTPS automatically
    $ symfony server:start

.. tip::

    For WSL (Windows Subsystem for Linux), the newly created local certificate
    authority needs to be imported manually:

    .. code-block:: terminal

        $ explorer.exe `wslpath -w $HOME/.symfony5/certs`

    In the file explorer window that just opened, double-click on the file
    called ``default.p12``.

PHP Management
--------------

The Symfony CLI provides PHP management features, allowing you to use different
PHP versions and/or settings for different projects.

Selecting PHP Version
~~~~~~~~~~~~~~~~~~~~~

If you have multiple PHP versions installed on your computer, you can tell
Symfony which one to use creating a file called ``.php-version`` at the project
root directory:

.. code-block:: terminal

    $ cd my-project/

    # use a specific PHP version
    $ echo 8.2 > .php-version

    # use any PHP 8.x version available
    $ echo 8 > .php-version

To see all available PHP versions:

.. code-block:: terminal

    $ symfony local:php:list

.. tip::

    You can create a ``.php-version`` file in a parent directory to set the same
    PHP version for multiple projects.

Custom PHP Configuration
~~~~~~~~~~~~~~~~~~~~~~~~

Override PHP settings per project by creating a ``php.ini`` file at the project
root:

.. code-block:: ini

    ; php.ini
    [Date]
    date.timezone = Asia/Tokyo

    [PHP]
    memory_limit = 256M

Using PHP Commands
~~~~~~~~~~~~~~~~~~

Use ``symfony php`` to ensure commands run with the correct PHP version:

.. code-block:: terminal

    # runs with the system's default PHP
    $ php -v

    # runs with the project's PHP version
    $ symfony php -v

    # this also works for Composer
    $ symfony composer install

Local Domain Names
------------------

By default, projects are accessible at a random port on the ``127.0.0.1``
local IP. However, sometimes it is preferable to associate a domain name
(e.g. ``my-app.wip``) with them:

* it's more convenient when working continuously on the same project because
  port numbers can change but domains don't;
* the behavior of some applications depends on their domains/subdomains;
* to have stable endpoints, such as the local redirection URL for OAuth2.

Setting up the Local Proxy
~~~~~~~~~~~~~~~~~~~~~~~~~~

The Symfony CLI includes a proxy that allows using custom local domains. The
first time you use it, you must configure it as follows:

#. Open the **proxy settings** of your operating system:

   * `Proxy settings in Windows`_;
   * `Proxy settings in macOS`_;
   * `Proxy settings in Ubuntu`_.

#. Set the following URL as the value of the **Automatic Proxy Configuration**:

   ``http://127.0.0.1:7080/proxy.pac``

Now run this command to start the proxy:

.. code-block:: terminal

    $ symfony proxy:start

If the proxy doesn't work as explained in the following sections, check the following:

* Some browsers (e.g. Chrome) require reapplying proxy settings (clicking on
  ``Re-apply settings`` button on the ``chrome://net-internals/#proxy`` page)
  or a full restart after starting the proxy. Otherwise, you'll see a
  *"This webpage is not available"* error (``ERR_NAME_NOT_RESOLVED``);
* Some Operating Systems (e.g. macOS) don't apply proxy settings to local hosts
  and domains by default. You may need to remove ``*.local`` and/or other
  IP addresses from that list.
* Windows **requires** using ``localhost`` instead of ``127.0.0.1`` when
  configuring the automatic proxy, otherwise you won't be able to access
  your local domain from your browser running in Windows.

Defining the Local Domain
~~~~~~~~~~~~~~~~~~~~~~~~~

By default, Symfony uses ``.wip`` (for *Work in Progress*) as the local TLD for
custom domains. You can define a local domain for your project as follows:

.. code-block:: terminal

   $ cd my-project/
   $ symfony proxy:domain:attach my-app

Your application is now available at ``https://my-app.wip``

.. tip::

    View all local domains and their configuration at http://127.0.0.1:7080

You can also use wildcards:

.. code-block:: terminal

    $ symfony proxy:domain:attach "*.my-app"

This allows accessing subdomains like ``https://api.my-app.wip`` or
``https://admin.my-app.wip``.

.. tip::

    If you prefer to use a different TLD, edit the ``~/.symfony5/proxy.json``
    file (where ``~`` means the path to your user directory) and change the
    value of the ``tld`` option from ``wip`` to any other TLD.

When running console commands, set the ``https_proxy`` environment variable
to make custom domains work:

.. code-block:: terminal

    # example with cURL
    $ https_proxy=$(symfony proxy:url) curl https://my-domain.wip

    # example with Blackfire and cURL
    $ https_proxy=$(symfony proxy:url) blackfire curl https://my-domain.wip

    # example with Cypress
    $ https_proxy=$(symfony proxy:url) ./node_modules/bin/cypress open

.. warning::

    Although environment variable names are typically uppercase, the ``https_proxy``
    variable `is treated differently`_ and must be written in lowercase.

.. _symfony-server-docker:

Docker Integration
------------------

The Symfony CLI provides full `Docker`_ integration for projects that
use it. To learn more about Docker and Symfony, see :doc:`docker`.
The local server automatically detects Docker services and exposes their
connection information as environment variables.

Automatic Service Detection
~~~~~~~~~~~~~~~~~~~~~~~~~~~

With this ``compose.yaml``:

.. code-block:: yaml

    services:
        database:
            image: mysql:8
            ports: [3306]

The web server detects that a service exposing port ``3306`` is running for the
project. It understands that this is a MySQL service and creates environment
variables accordingly, using the service name (``database``) as a prefix:

* ``DATABASE_URL``
* ``DATABASE_HOST``
* ``DATABASE_PORT``

Here is a list of supported services with their ports and default Symfony prefixes:

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

If the service is not supported, generic environment variables are set:
``PORT``, ``IP``, and ``HOST``.

You can open web management interfaces for the services that expose them
by clicking on the links in the "Server" section of the web debug toolbar
or by running these commands:

.. code-block:: bash

    $ symfony open:local:webmail
    $ symfony open:local:rabbitmq

.. tip::

    To debug and list all exported environment variables, run:
    ``symfony var:export --debug``.

.. tip::

    For some services, the local web server also exposes environment variables
    understood by CLI tools related to the service. For instance, running
    ``symfony run psql`` will connect you automatically to the PostgreSQL server
    running in a container without having to specify the username, password, or
    database name.

When Docker services are running, browse a page of your Symfony application and
check the "Symfony Server" section in the web debug toolbar. You'll see that
"Docker Compose" is marked as "Up".

.. note::

    If you don't want environment variables to be exposed for a service, set
    the ``com.symfony.server.service-ignore`` label to ``true``:

    .. code-block:: yaml

        # compose.yaml
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
    COMPOSE_FILE=docker/compose.yaml COMPOSE_PROJECT_NAME=project_name docker-compose up -d

    # run any Symfony CLI command:
    COMPOSE_FILE=docker/compose.yaml COMPOSE_PROJECT_NAME=project_name symfony var:export

.. note::

    If you have more than one Docker Compose file, you can provide them all,
    separated by ``:``, as explained in the `Docker Compose CLI env var reference`_.

.. warning::

    When using the Symfony CLI with ``php bin/console`` (``symfony console ...``),
    it will **always** use environment variables detected via Docker, ignoring
    any local environment variables. For example, if you set up a different database
    name in your ``.env.test`` file (``DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/test``)
    and run ``symfony console doctrine:database:drop --force --env=test``,
    the command will drop the database defined in your Docker configuration and not the "test" one.

.. warning::

    Similar to other web servers, this tool automatically exposes all environment
    variables available in the CLI context. Ensure that this local server is not
    accessible on your local network without your explicit consent, to avoid
    potential security issues.

Service Naming
~~~~~~~~~~~~~~

If your service names don't match Symfony conventions, use labels:

.. code-block:: yaml

    services:
        db:
            image: postgres:15
            ports: [5432]
            labels:
                com.symfony.server.service-prefix: 'DATABASE'

In this example, the service is named ``db``, so environment variables would be
prefixed with ``DB_``, but as the ``com.symfony.server.service-prefix`` is set
to ``DATABASE``, the web server creates environment variables starting with
``DATABASE_`` instead as expected by the default Symfony configuration.

Managing Long-Running Processes
-------------------------------

Use the ``run`` command provided by the Symfony CLI to manage long-running
processes like Webpack watchers:

.. code-block:: terminal

    # start webpack watcher in the background to not block the terminal
    $ symfony run -d npx encore dev --watch

    # continue working and running other commands...

    # view logs
    $ symfony server:log

    # check status
    $ symfony server:status

.. _symfony-server_configuring-workers:

Configuring Workers
~~~~~~~~~~~~~~~~~~~

Define processes that should start automatically with the server in
``.symfony.local.yaml``:

.. code-block:: yaml

    # .symfony.local.yaml
    workers:
        # Built-in Encore integration
        npm_encore_watch: ~

        # Messenger consumer with file watching
        messenger_consume_async:
            cmd: ['symfony', 'console', 'messenger:consume', 'async']
            watch: ['config', 'src', 'templates', 'vendor/composer/installed.json']

        # Custom commands
        build_spa:
            cmd: ['npm', 'run', 'watch']

        # Auto-start Docker Compose
        docker_compose: ~

Advanced Configuration
----------------------

The ``.symfony.local.yaml`` file provides advanced configuration options:

.. code-block:: yaml

    # sets app.wip and admin.app.wip for the current project
    proxy:
        domains:
            - app
            - admin.app

    # HTTP server settings
    http:
        document_root: public/
        passthru: index.php
        # forces the port that will be used to run the server
        port: 8000
        # sets the HTTP port you prefer for this project [default: 8000]
        # (only will be used if it's available; otherwise a random port is chosen)
        preferred_port: 8001
        # used to disable the default auto-redirection from HTTP to HTTPS
        allow_http: true
        # force the use of HTTP instead of HTTPS
        no_tls: false
        # path to the file containing the TLS certificate to use in p12 format
        p12: path/to/custom-cert.p12
        # toggle GZIP compression
        use_gzip: true
        # enable Cross-origin resource sharing (CORS) for all request
        allow_cors: true

    # run the server in the background
    daemon: true

.. warning::

    Setting domains in this configuration file will override any domains you set
    using the ``proxy:domain:attach`` command for the current project when you start
    the server.

.. _platform-sh-integration:

Symfony Cloud Integration
-------------------------

The Symfony CLI provides seamless integration with `Symfony Cloud`_ (powered by
`Upsun`_):

.. code-block:: terminal

    # open Upsun web UI
    $ symfony cloud:web

    # deploy your project to production
    $ symfony cloud:deploy

    # create a new environment
    $ symfony cloud:env:create feature-xyz

For more features, see the `Symfony Cloud documentation`_.

Troubleshooting
---------------

**Server doesn't start**: Check if the port is already in use:

.. code-block:: terminal

    $ symfony server:status
    $ symfony server:stop  # If a server is already running

**HTTPS not working**: Ensure the CA is installed:

.. code-block:: terminal

    $ symfony server:ca:install

**Docker services not detected**: Check that Docker is running and environment
variables are properly exposed:

.. code-block:: terminal

    $ docker compose ps
    $ symfony var:export --debug

**Proxy domains not working**:

* Clear your browser cache
* Check proxy settings in your system
* For Chrome, visit ``chrome://net-internals/#proxy`` and click "Re-apply settings"

.. _`open source`: https://github.com/symfony-cli/symfony-cli
.. _`symfony.com/download`: https://symfony.com/download
.. _`Docker`: https://en.wikipedia.org/wiki/Docker_(software)
.. _`Symfony Cloud`: https://symfony.com/cloud/
.. _`Upsun`: https://upsun.com/
.. _`Symfony Cloud documentation`: https://docs.upsun.com/get-started/stacks/symfony/integration.html
.. _`Proxy settings in Windows`: https://www.dummies.com/computers/operating-systems/windows-10/how-to-set-up-a-proxy-in-windows-10/
.. _`Proxy settings in macOS`: https://support.apple.com/guide/mac-help/enter-proxy-server-settings-on-mac-mchlp2591/mac
.. _`Proxy settings in Ubuntu`: https://help.ubuntu.com/stable/ubuntu-help/net-proxy.html.en
.. _`is treated differently`: https://superuser.com/a/1799209
.. _`Docker Compose CLI env var reference`: https://docs.docker.com/compose/reference/envvars/
