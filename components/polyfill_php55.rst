.. index::
    single: Polyfill
    single: PHP
    single: Components; Polyfill

The Symfony Polyfill / PHP 5.5 Component
========================================

    This component provides some PHP 5.5 features to applications using earlier
    PHP versions.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-php55

Alternatively, you can clone the `<https://github.com/symfony/polyfill-php55>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
functions, no matter if your PHP version is earlier than PHP 5.5.

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`array_column`
* :phpfunction:`boolval`
* :phpfunction:`hash_pbkdf2`
* :phpfunction:`json_last_error_msg`
* :phpfunction:`password_get_info`
* :phpfunction:`password_hash`
* :phpfunction:`password_needs_rehash`
* :phpfunction:`password_verify`
