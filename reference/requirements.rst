.. index::
   single: Requirements

Requirements for Running Symfony2
=================================

To run Symfony2, your system needs to adhere to a list of requirements. You can
easily see if your system passes all requirements by running the ``web/config.php``
in your Symfony distribution. Since the CLI often uses a different ``php.ini``
configuration file, it's also a good idea to check your requirements from
the command line via:

.. code-block:: bash

    $ php app/check.php

Below is the list of required and optional requirements.

Required
--------

* PHP needs to be a minimum version of PHP 5.3.3
* JSON needs to be enabled
* ctype needs to be enabled
* Your ``php.ini`` needs to have the ``date.timezone`` setting

.. caution::

    Be aware that Symfony2 has some known limitations when using a PHP version
    less than 5.3.8 or equal to 5.3.16. For more information see the
    `Requirements section of the README`_.

Optional
--------

* You need to have the PHP-XML module installed
* You need to have at least version 2.6.21 of libxml
* PHP tokenizer needs to be enabled
* mbstring functions need to be enabled
* iconv needs to be enabled
* POSIX needs to be enabled (only on \*nix)
* Intl needs to be installed with ICU 4+
* APC 3.0.17+ (or another opcode cache needs to be installed)
* ``php.ini`` recommended settings

  * ``short_open_tag = Off``
  * ``magic_quotes_gpc = Off``
  * ``register_globals = Off``
  * ``session.auto_start = Off``

Doctrine
--------

If you want to use Doctrine, you will need to have PDO installed. Additionally,
you need to have the PDO driver installed for the database server you want
to use.

.. _`Requirements section of the README`: https://github.com/symfony/symfony#requirements
