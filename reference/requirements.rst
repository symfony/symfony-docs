.. index::
   single: Requirements

.. _requirements-for-running-symfony2:

Requirements for Running Symfony
================================

Symfony 2.7 requires **PHP 5.3.9** or higher to run, in addition to other minor
requirements. To make things simple, Symfony provides a tool to quickly check if
your system meets all those requirements.

Beware that PHP can define a different configuration for the command console and
the web server, so you need to check requirements in both environments.

Checking Requirements for the Web Server
----------------------------------------

Symfony includes a ``config.php`` file in the ``web/`` directory of your project.
Open that file with your browser to check the requirements.

Once you've fixed all the reported issues, delete the ``web/config.php`` file
to avoid leaking internal information about your application to visitors.

Checking Requirements for the Command Console
---------------------------------------------

Open your console or terminal, enter in your project directory, execute this
command and fix the reported issues:

.. code-block:: terminal

    $ cd my-project/
    $ php app/check.php
