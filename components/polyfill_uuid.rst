.. index::
    single: Polyfill
    single: PHP
    single: Components; Polyfill

The Symfony Polyfill / UUID Component
=====================================

    This component provides ``uuid_*`` functions to users who run PHP versions
    without the UUID extension.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-uuid

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
functions, no matter if the `PHP UUID extension`_ is installed or not in your
server.

Provided Constants
~~~~~~~~~~~~~~~~~~

* ``UUID_VARIANT_NCS`` (value = 0)
* ``UUID_VARIANT_DCE`` (value = 1)
* ``UUID_VARIANT_MICROSOFT`` (value = 2)
* ``UUID_VARIANT_OTHER`` (value = 3)
* ``UUID_TYPE_DEFAULT`` (value = 0)
* ``UUID_TYPE_TIME`` (value = 1)
* ``UUID_TYPE_DCE`` (value = 4)
* ``UUID_TYPE_NAME`` (value = 1)
* ``UUID_TYPE_RANDOM`` (value = 4)
* ``UUID_TYPE_NULL`` (value = -1)
* ``UUID_TYPE_INVALID`` (value = -42)

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`uuid_create`
* :phpfunction:`uuid_is_valid`
* :phpfunction:`uuid_compare`
* :phpfunction:`uuid_is_null`
* :phpfunction:`uuid_type`
* :phpfunction:`uuid_variant`
* :phpfunction:`uuid_time`
* :phpfunction:`uuid_mac`
* :phpfunction:`uuid_parse`
* :phpfunction:`uuid_unparse`

.. _`PHP UUID extension`: https://pecl.php.net/package/uuid
