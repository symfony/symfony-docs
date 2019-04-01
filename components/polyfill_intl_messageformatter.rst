.. index::
    single: Polyfill
    single: MessageFormatter
    single: Components; Polyfill

The Symfony Polyfill / Intl MessageFormatter Component
======================================================

    This component provides a fallback implementation for the ``MessageFormatter``
    class to users who run PHP versions without the ``intl`` extension.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-intl-messageformatter

Alternatively, you can clone the `<https://github.com/symfony/polyfill-intl-messageformatter>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
classes and functions, no matter if the `PHP intl extension`_ is installed or
not in your server.

Provided Classes
~~~~~~~~~~~~~~~~

* :phpclass:`IntlException`
* :phpclass:`MessageFormatter`

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`msgfmt_format_message`

.. seealso::

    Symfony provides more polyfills for other classes and functions related to
    the Intl PHP extension:
    :doc:`polyfill-intl-grapheme </components/polyfill_intl_grapheme>`,
    :doc:`polyfill-intl-idn </components/polyfill_intl_idn>`,
    :doc:`polyfill-intl-icu </components/polyfill_intl_icu>`,
    and :doc:`polyfill-intl-normalizer </components/polyfill_intl_normalizer>`.

.. _`PHP intl extension`: https://secure.php.net/manual/en/book.intl.php
