.. index::
    single: Polyfill
    single: Ctype
    single: Components; Polyfill

The Symfony Polyfill / Ctype Component
======================================

    This component provides ``ctype_*`` functions to users who run PHP versions
    without the ctype extension.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-ctype

Alternatively, you can clone the `<https://github.com/symfony/polyfill-ctype>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
functions, no matter if the `PHP Ctype extension`_ is installed or not in your
server.

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`ctype_alnum`
* :phpfunction:`ctype_alpha`
* :phpfunction:`ctype_cntrl`
* :phpfunction:`ctype_digit`
* :phpfunction:`ctype_graph`
* :phpfunction:`ctype_lower`
* :phpfunction:`ctype_print`
* :phpfunction:`ctype_punct`
* :phpfunction:`ctype_space`
* :phpfunction:`ctype_upper`
* :phpfunction:`ctype_xdigit`

.. _`PHP Ctype extension`: https://secure.php.net/manual/en/book.ctype.php
