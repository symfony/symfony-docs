.. index::
   single: Requirements

.. _requirements-for-running-symfony2:

Requirements for Running Symfony
================================

These are the technical requirements to run Symfony 4 applications:

* **PHP version**: 7.1.3 or higher
* **PHP extensions**: (all of them are installed and enabled by default in PHP 7+)

  * `Ctype`_
  * `iconv`_
  * `JSON`_
  * `PCRE`_
  * `Session`_
  * `SimpleXML`_
  * `Tokenizer`_

* **Writable directories**: (must be writable by the web server)

  * The project's cache directory (``var/cache/`` by default, but the app can
    :ref:`override the cache dir <override-cache-dir>`)
  * The project's log directory (``var/log/`` by default, but the app can
    :ref:`override the logs dir <override-logs-dir>`)

Checking Requirements Automatically
-----------------------------------

To make things simple, Symfony provides a tool to quickly check if your system
meets these requirements. In addition, the tool provides recommendations if
applicable.

Run this command to install the tool:

.. code-block:: terminal

    $ cd your-project/
    $ composer require symfony/requirements-checker

Beware that PHP may use different configurations for the command console and
the web server, so you need to check the requirements in both environments.

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

.. _`iconv`: https://php.net/book.iconv
.. _`JSON`: https://php.net/book.json
.. _`Session`: https://php.net/book.session
.. _`Ctype`: https://php.net/book.ctype
.. _`Tokenizer`: https://php.net/book.tokenizer
.. _`SimpleXML`: https://php.net/book.simplexml
.. _`PCRE`: https://php.net/book.pcre
