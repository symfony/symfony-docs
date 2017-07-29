.. index::
   single: Requirements

.. _requirements-for-running-symfony2:

Requirements for Running Symfony
================================

Symfony 4.0 requires **PHP 7.1.3** or higher to run, in addition to other minor
requirements. To make things simple, Symfony provides a tool to quickly check if
your system meets all those requirements. Run this command to install the tool:

.. code-block:: terminal

    $ cd your-project/
    $ composer require requirements-checker

Beware that PHP can define a different configuration for the command console and
the web server, so you need to check requirements in both environments.

Checking Requirements for the Web Server
----------------------------------------

The requirements checker tool creates a file called ``check.php`` in the
``public/`` directory of your project. Open that file with your browser to check
the requirements.

Once you've fixed all the reported issues, uninstall the requirements checker
to avoid leaking internal information about your application to visitors:

.. code-block:: terminal

    $ cd your-project/
    $ composer remove requirements-checker

Checking Requirements for the Command Console
---------------------------------------------

The requirements checker tool adds a script to your Composer configuration to
check the requirements automatically. There's no need to execute any command; if
there is any issue, you'll see them in the console output.
