.. index::
    single: Polyfill
    single: Normalizer
    single: Components; Polyfill

The Symfony Polyfill / Intl Normalizer Component
================================================

    This component provides a fallback implementation for the ``Normalizer``
    class to users who run PHP versions without the ``intl`` extension.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/polyfill-intl-normalizer

Alternatively, you can clone the `<https://github.com/symfony/polyfill-intl-normalizer>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once this component is installed in your application, you can use the following
classes and functions, no matter if the `PHP intl extension`_ is installed or
not in your server.

Provided Classes
~~~~~~~~~~~~~~~~

* :phpclass:`Normalizer`

Provided Functions
~~~~~~~~~~~~~~~~~~

* :phpfunction:`normalizer_is_normalized`
* :phpfunction:`normalizer_normalize`

.. seealso::

    Symfony provides more polyfills for other classes and functions related to
    the Intl PHP extension:
    :doc:`polyfill-intl-grapheme </components/polyfill_intl_grapheme>`,
    :doc:`polyfill-intl-idn </components/polyfill_intl_idn>`,
    :doc:`polyfill-intl-icu </components/polyfill_intl_icu>`,
    and :doc:`polyfill-intl-messageformatter </components/polyfill_intl_messageformatter>`.

.. _`PHP intl extension`: https://secure.php.net/manual/en/book.intl.php
