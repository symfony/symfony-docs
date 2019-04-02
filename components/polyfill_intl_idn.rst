.. index::
    single: Polyfill
    single: IDN
    single: Components; Polyfill

The Symfony Polyfill / Intl IDN Component
=========================================

    This component provides a collection of functions related to IDN when the
    Intl extension is not installed.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-intl-idn

Alternatively, you can clone the `<https://github.com/symfony/polyfill-intl-idn>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
functions, no matter if the `PHP intl extension`_ is installed or not in your
server.

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`idn_to_ascii`
* :phpfunction:`idn_to_utf8`

.. seealso::

    Symfony provides more polyfills for other classes and functions related to
    the Intl PHP extension:
    :doc:`polyfill-intl-grapheme </components/polyfill_intl_grapheme>`,
    :doc:`polyfill-intl-icu </components/polyfill_intl_icu>`,
    :doc:`polyfill-intl-messageformatter </components/polyfill_intl_messageformatter>`,
    and :doc:`polyfill-intl-normalizer </components/polyfill_intl_normalizer>`.

.. _`PHP intl extension`: https://secure.php.net/manual/en/book.intl.php
