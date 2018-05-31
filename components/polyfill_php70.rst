.. index::
    single: Polyfill
    single: PHP
    single: Components; Polyfill

The Symfony Polyfill / PHP 7.0 Component
========================================

    This component provides some PHP 7.0 features to applications using earlier
    PHP versions.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-php70

Alternatively, you can clone the `<https://github.com/symfony/polyfill-php70>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
constants, classes and functions, no matter if your PHP version is earlier than
PHP 7.0.

Provided Constants
~~~~~~~~~~~~~~~~~~

* ``PHP_INT_MIN`` (value = ``~PHP_INT_MAX``)

Provided Classes
~~~~~~~~~~~~~~~~

* :phpclass:`ArithmeticError`
* :phpclass:`AssertionError`
* :phpclass:`DivisionByZeroError`
* :phpclass:`Error`
* :phpclass:`ParseError`
* :phpclass:`SessionUpdateTimestampHandlerInterface`
* :phpclass:`TypeError`

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`error_clear_last`
* :phpfunction:`intdiv`
* :phpfunction:`preg_replace_callback_array`
* :phpfunction:`random_bytes`
* :phpfunction:`random_int`
