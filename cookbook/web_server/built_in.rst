.. index::
    single: Web Server; Built-in Web Server

How to Use PHP's built-in Web Server
====================================

Since PHP 5.4 the CLI SAPI comes with a `built-in web server`_. It can be used
to run your PHP applications locally during development, for testing or for
application demonstrations. This way, you don't have to bother configuring
a full-featured web server such as
:doc:`Apache or Nginx </cookbook/configuration/web_server_configuration>`.

.. caution::

    The built-in web server is meant to be run in a controlled environment.
    It is not designed to be used on public networks.

Starting the Web Server
-----------------------

Running a Symfony application using PHP's built-in web server is as easy as
executing the ``server:run`` command:

.. code-block:: bash

    $ php app/console server:run

This starts a server at ``localhost:8000`` that executes your Symfony application.
The command will wait and will respond to incoming HTTP requests until you
terminate it (this is usually done by pressing Ctrl and C).

By default, the web server listens on port 8000 on the loopback device. You
can change the socket passing an IP address and a port as a command-line argument:

.. code-block:: bash

    $ php app/console server:run 192.168.0.1:8080

.. sidebar:: Using the built-in Web Server from inside a Virtual Machine

    If you want to use the built-in web server from inside a virtual machine
    and then load the site from a browser on your host machine, you'll need
    to listen on the ``0.0.0.0:8000`` address (i.e. on all IP addresses that
    are assigned to the virtual machine):

    .. code-block:: bash

        $ php app/console server:run 0.0.0.0:8000

    .. caution::

        You should **NEVER** listen to all interfaces on a computer that is
        directly accessible from the Internet. The built-in web server is
        not designed to be used on public networks.

Command Options
---------------

The built-in web server expects a "router" script (read about the "router"
script on `php.net`_) as an argument. Symfony already passes such a router
script when the command is executed in the ``prod`` or in the ``dev`` environment.
Use the ``--router`` option in any other environment or to use another router
script:

.. code-block:: bash

    $ php app/console server:run --env=test --router=app/config/router_test.php

If your application's document root differs from the standard directory layout,
you have to pass the correct location using the ``--docroot`` option:

.. code-block:: bash

    $ php app/console server:run --docroot=public_html

.. _`built-in web server`: http://www.php.net/manual/en/features.commandline.webserver.php
.. _`php.net`: http://php.net/manual/en/features.commandline.webserver.php#example-411
