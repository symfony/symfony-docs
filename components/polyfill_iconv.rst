.. index::
    single: Polyfill
    single: Iconv
    single: Components; Polyfill

The Symfony Polyfill / Iconv Component
======================================

    This component provides a native PHP implementation of the ``iconv_*``
    functions to users who run PHP versions without the ``iconv`` extension.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-iconv

Alternatively, you can clone the `<https://github.com/symfony/polyfill-iconv>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
constants and functions, no matter if the `PHP iconv extension`_ is installed or
not in your server. The only function not implemented in this component is
:phpfunction:`ob_iconv_handler`.

Provided Constants
~~~~~~~~~~~~~~~~~~

* ``ICONV_IMPL`` (value = ``'Symfony'``)
* ``ICONV_VERSION`` (value = ``'1.0'``)
* ``ICONV_MIME_DECODE_STRICT`` (value = ``1``)
* ``ICONV_MIME_DECODE_CONTINUE_ON_ERROR`` (value = ``2``)

Provided Functions
~~~~~~~~~~~~~~~~~~

These functions are always available:

* :phpfunction:`iconv`
* :phpfunction:`iconv_get_encoding`
* :phpfunction:`iconv_set_encoding`
* :phpfunction:`iconv_mime_encode`
* :phpfunction:`iconv_mime_decode_headers`

These functions are only available when the ``mbstring`` or the ``xml``
extension are installed:

* :phpfunction:`iconv_strlen`
* :phpfunction:`iconv_strpos`
* :phpfunction:`iconv_strrpos`
* :phpfunction:`iconv_substr`
* :phpfunction:`iconv_mime_decode`

.. _`PHP iconv extension`: https://secure.php.net/manual/en/book.iconv.php
.. _`mbstring`: https://secure.php.net/manual/en/book.mbstring.php
.. _`xml`: https://secure.php.net/manual/en/book.xml.php
