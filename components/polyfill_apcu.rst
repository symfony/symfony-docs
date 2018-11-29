.. index::
    single: Polyfill
    single: APC
    single: Components; Polyfill

The Symfony Polyfill / APCu Component
=====================================

    This component provides ``apcu_*`` functions and the ``APCUIterator`` class
    to users of the legacy APC extension.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-apcu

Alternatively, you can clone the `<https://github.com/symfony/polyfill-apcu>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
classes and functions, no matter if the `PHP APCu extension`_ is installed or
not in your server. The only requirement is to have installed at least the
`legacy APC extension`_.

Provided Classes
~~~~~~~~~~~~~~~~

* :phpclass:`APCUIterator`

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`apcu_add`
* :phpfunction:`apcu_delete`
* :phpfunction:`apcu_exists`
* :phpfunction:`apcu_fetch`
* :phpfunction:`apcu_store`
* :phpfunction:`apcu_cache_info`
* :phpfunction:`apcu_cas`
* :phpfunction:`apcu_clear_cache`
* :phpfunction:`apcu_dec`
* :phpfunction:`apcu_inc`
* :phpfunction:`apcu_sma_info`

.. _`PHP APCu extension`: https://secure.php.net/manual/en/book.apcu.php
.. _`legacy APC extension`: https://secure.php.net/manual/en/book.apc.php
