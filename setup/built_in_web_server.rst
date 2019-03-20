.. index::
    single: Web Server; Built-in Web Server

How to Use PHP's built-in Web Server
====================================

Since PHP 5.4 the CLI SAPI comes with a `built-in web server`_. It can be used
to run your PHP applications locally during development, for testing or for
application demonstrations. This way, you don't have to bother configuring
a full-featured web server such as
:doc:`Apache or Nginx </setup/web_server_configuration>`.

.. caution::

    The built-in web server is meant to be run in a controlled environment.
    It is not designed to be used on public networks.

Symfony provides a web server built on top of this PHP server to simplify your
local setup. This server is distributed as a bundle, so you must first install
and enable the server bundle.

Installing the Web Server Bundle
--------------------------------

Move into your project directory and run this command:

.. code-block:: terminal

    $ cd your-project/
    $ composer require --dev symfony/web-server-bundle

Starting the Web Server
-----------------------

To run a Symfony application using PHP's built-in web server, execute the
``server:start`` command:

.. code-block:: terminal

    $ php bin/console server:start

This starts the web server at ``localhost:8000`` in the background that serves
your Symfony application.

By default, the web server listens on port 8000 on the loopback device. You
can change the socket passing an IP address and a port as a command-line argument:

.. code-block:: terminal

    # passing a specific IP and port
    $ php bin/console server:start 192.168.0.1:8080

    # passing '*' as the IP means to use 0.0.0.0 (i.e. any local IP address)
    $ php bin/console server:start *:8080

.. note::

    You can use the ``server:status`` command to check if a web server is
    listening:

    .. code-block:: terminal

        $ php bin/console server:status

.. tip::

    Some systems do not support the ``server:start`` command, in these cases
    you can execute the ``server:run`` command. This command behaves slightly
    different. Instead of starting the server in the background, it will block
    the current terminal until you terminate it (this is usually done by
    pressing Ctrl and C).

.. sidebar:: Using the built-in Web Server from inside a Virtual Machine

    If you want to use the built-in web server from inside a virtual machine
    and then load the site from a browser on your host machine, you'll need
    to listen on the ``0.0.0.0:8000`` address (i.e. on all IP addresses that
    are assigned to the virtual machine):

    .. code-block:: terminal

        $ php bin/console server:start 0.0.0.0:8000

    .. caution::

        You should **NEVER** listen to all interfaces on a computer that is
        directly accessible from the Internet. The built-in web server is
        not designed to be used on public networks.

Command Options
~~~~~~~~~~~~~~~

The built-in web server expects a "router" script (read about the "router"
script on `php.net`_) as an argument. Symfony already passes such a router
script when the command is executed in the ``prod`` or ``dev`` environment.
Use the ``--router`` option to use your own router script:

.. code-block:: terminal

    $ php bin/console server:start --router=config/my_router.php

If your application's document root differs from the standard directory layout,
you have to pass the correct location using the ``--docroot`` option:

.. code-block:: terminal

    $ php bin/console server:start --docroot=public_html

Stopping the Server
-------------------

When you finish your work, you can stop the web server with the following command:

.. code-block:: terminal

    $ php bin/console server:stop

.. _`built-in web server`: https://php.net/manual/en/features.commandline.webserver.php
.. _`php.net`: https://php.net/manual/en/features.commandline.webserver.php#example-411
