.. index::
   single: Requirements

.. _requirements-for-running-symfony2:

Requirements for Running Symfony 4
==================================

Symfony 4 requires **PHP 7.1.3** or higher to run, in addition to other minor
requirements:

PHP Extensions
~~~~~~~~~~~~~~
* The `Ctype`_ extension must be available
* The `iconv`_ extension  must be available
* The `JSON`_ extension must be available
* The `PCRE`_ extension must be available (minimum version 8.0)
* The `Session`_ extension must be available
* The `SimpleXML`_ extension must be available
* The `Tokenizer`_ extension must be available

Please note that all these extensions are installed and enabled by default 
in PHP 7+.

Other Requirements
~~~~~~~~~~~~~~~~~~
* The cache directory must me writable by the web server
* The logs directory must be writable by the web server

Checking Requirements with Symfony Requirements Checker
-------------------------------------------------------
To make things simple, Symfony provides a tool to quickly check if
your system meets these requirements. In addition, the tool will
also provide recommendations if applicable.

Run this command to install the tool:

.. code-block:: terminal

    $ cd your-project/
    $ composer require symfony/requirements-checker

Beware that PHP may use different configurations for the command console and
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
    $ composer remove symfony/requirements-checker

Checking Requirements for the Command Console
---------------------------------------------

The requirements checker tool adds a script to your Composer configuration to
check the requirements automatically. There's no need to execute any command; if
there are any issues, you'll see them in the console output.

.. _iconv: http://php.net/manual/en/book.iconv.php
.. _JSON: http://php.net/manual/en/book.json.php
.. _Session: http://php.net/manual/en/book.session.php
.. _Ctype: http://php.net/manual/en/book.ctype.php
.. _Tokenizer: http://php.net/manual/en/book.tokenizer.php
.. _SimpleXML: http://php.net/manual/en/book.simplexml.php
.. _PCRE: http://php.net/manual/en/book.pcre.php
