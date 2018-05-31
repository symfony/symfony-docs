.. index::
    single: Polyfill
    single: PHP
    single: Components; Polyfill

The Symfony Polyfill / PHP 5.6 Component
========================================

    This component provides some PHP 5.6 features to applications using earlier
    PHP versions.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-php56

Alternatively, you can clone the `<https://github.com/symfony/polyfill-php56>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
constants and functions, no matter if your PHP version is earlier than PHP 5.6.

Provided Constants
~~~~~~~~~~~~~~~~~~

* ``LDAP_ESCAPE_FILTER`` (value = ``1``)
* ``LDAP_ESCAPE_DN`` (value = ``2``)

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`hash_equals`
* :phpfunction:`ldap_escape`
