.. index::
    single: Polyfill
    single: PHP
    single: Components; Polyfill

The Symfony Polyfill / PHP 7.3 Component
========================================

    This component provides some PHP 7.3 features to applications using earlier
    PHP versions.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-php73

Alternatively, you can clone the `<https://github.com/symfony/polyfill-php73>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
functions, no matter if your PHP version is earlier than PHP 7.3.

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`is_countable`
