.. index::
    single: Polyfill
    single: Mbstring
    single: Components; Polyfill

The Symfony Polyfill / Intl Mbstring Component
==============================================

    This component provides a partial, native PHP implementation for the
    ``mbstring`` PHP extension.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-mbstring

Alternatively, you can clone the `<https://github.com/symfony/polyfill-mbstring>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
constants and functions, no matter if the `PHP mbstring extension`_ is installed
or not in your server.

Provided Constants
~~~~~~~~~~~~~~~~~~

* ``MB_CASE_UPPER`` (value = ``0``)
* ``MB_CASE_LOWER`` (value = ``1``)
* ``MB_CASE_TITLE`` (value = ``2``)

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`mb_check_encoding`
* :phpfunction:`mb_chr`
* :phpfunction:`mb_convert_case`
* :phpfunction:`mb_convert_encoding`
* :phpfunction:`mb_convert_variables`
* :phpfunction:`mb_decode_mimeheader`
* :phpfunction:`mb_decode_numericentity`
* :phpfunction:`mb_detect_encoding`
* :phpfunction:`mb_detect_order`
* :phpfunction:`mb_encode_mimeheader`
* :phpfunction:`mb_encode_numericentity`
* :phpfunction:`mb_encoding_aliases`
* :phpfunction:`mb_get_info`
* :phpfunction:`mb_http_input`
* :phpfunction:`mb_http_output`
* :phpfunction:`mb_internal_encoding`
* :phpfunction:`mb_language`
* :phpfunction:`mb_list_encodings`
* :phpfunction:`mb_ord`
* :phpfunction:`mb_output_handler`
* :phpfunction:`mb_parse_str`
* :phpfunction:`mb_scrub`
* :phpfunction:`mb_stripos`
* :phpfunction:`mb_stristr`
* :phpfunction:`mb_strlen`
* :phpfunction:`mb_strpos`
* :phpfunction:`mb_strrchr`
* :phpfunction:`mb_strrichr`
* :phpfunction:`mb_strripos`
* :phpfunction:`mb_strrpos`
* :phpfunction:`mb_strstr`
* :phpfunction:`mb_strtolower`
* :phpfunction:`mb_strtoupper`
* :phpfunction:`mb_strwidth`
* :phpfunction:`mb_substitute_character`
* :phpfunction:`mb_substr_count`
* :phpfunction:`mb_substr`

.. _`PHP mbstring extension`: https://secure.php.net/manual/en/book.mbstring.php
