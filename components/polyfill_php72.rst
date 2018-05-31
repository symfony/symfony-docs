.. index::
    single: Polyfill
    single: PHP
    single: Components; Polyfill

The Symfony Polyfill / PHP 7.2 Component
========================================

    This component provides some PHP 7.2 features to applications using earlier
    PHP versions.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-php72

Alternatively, you can clone the `<https://github.com/symfony/polyfill-php72>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
constants and functions, no matter if your PHP version is earlier than PHP 7.2.

Provided Constants
~~~~~~~~~~~~~~~~~~

* ``PHP_OS_FAMILY`` (value = depends on your operating system)

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`sapi_windows_vt100_support` (only on Windows systems)
* :phpfunction:`spl_object_id`
* :phpfunction:`stream_isatty`
* :phpfunction:`utf8_decode`
* :phpfunction:`utf8_encode`
