.. index::
    single: Polyfill
    single: Intl
    single: Components; Polyfill

The Symfony Polyfill / Intl Grapheme Component
==============================================

    This component provides a partial, native PHP implementation of the
    ``grapheme_*`` functions to users who run PHP versions without the ``intl``
    extension.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-intl-grapheme

Alternatively, you can clone the `<https://github.com/symfony/polyfill-intl-grapheme>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
constants and functions, no matter if the `PHP intl extension`_ is installed or
not in your server.

Provided Constants
~~~~~~~~~~~~~~~~~~

* ``GRAPHEME_EXTR_COUNT`` (value = ``0``)
* ``GRAPHEME_EXTR_MAXBYTES`` (value = ``1``)
* ``GRAPHEME_EXTR_MAXCHARS`` (value = ``2``)

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`grapheme_extract`
* :phpfunction:`grapheme_stripos`
* :phpfunction:`grapheme_stristr`
* :phpfunction:`grapheme_strlen`
* :phpfunction:`grapheme_strpos`
* :phpfunction:`grapheme_strripos`
* :phpfunction:`grapheme_strrpos`
* :phpfunction:`grapheme_strstr`
* :phpfunction:`grapheme_substr`

.. seealso::

    Symfony provides more polyfills for other classes and functions related to
    the Intl PHP extension:
    :doc:`polyfill-intl-icu </components/polyfill_intl_icu>`,
    :doc:`polyfill-intl-idn </components/polyfill_intl_idn>`,
    :doc:`polyfill-intl-messageformatter </components/polyfill_intl_messageformatter>`,
    and :doc:`polyfill-intl-normalizer </components/polyfill_intl_normalizer>`.

.. _`PHP intl extension`: https://secure.php.net/manual/en/book.intl.php
