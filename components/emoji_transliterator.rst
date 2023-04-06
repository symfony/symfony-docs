The EmojiTransliterator component
=================================

    The EmojiTransliterator provides an implementation of :phpclass:`Transliterator` that
    can convert emojis to ASCII representations.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/emoji-transliterator

.. include:: /components/require_autoload.rst.inc

Usage
-----

The ``EmojiTransliterator`` class provides a utility to translate emojis into
their textual representation in all languages based on the `Unicode CLDR dataset`_::

    use Symfony\Component\EmojiTransliterator\EmojiTransliterator;

    // describe emojis in English
    $transliterator = EmojiTransliterator::create('en');
    $transliterator->transliterate('Menus with 🍕 or 🍝');
    // => 'Menus with pizza or spaghetti'

    // describe emojis in Ukrainian
    $transliterator = EmojiTransliterator::create('uk');
    $transliterator->transliterate('Menus with 🍕 or 🍝');
    // => 'Menus with піца or спагеті'

The ``EmojiTransliterator`` class also provides two extra catalogues: ``github``
and ``slack`` that converts any emojis to the corresponding short code in those
platforms::

    use Symfony\Component\Intl\Transliterator\EmojiTransliterator;

    // describe emojis in Slack short code
    $transliterator = EmojiTransliterator::create('slack');
    $transliterator->transliterate('Menus with 🥗 or 🧆');
    // => 'Menus with :green_salad: or :falafel:'

    // describe emojis in Github short code
    $transliterator = EmojiTransliterator::create('github');
    $transliterator->transliterate('Menus with 🥗 or 🧆');
    // => 'Menus with :green_salad: or :falafel:'

The ``EmojiTransliterator`` class also provides an extra catalogues ``strip``
that removes any emojis::

    use Symfony\Component\Intl\Transliterator\EmojiTransliterator;

    // Strip emojis from the string
    $transliterator = EmojiTransliterator::create('strip');
    $transliterator->transliterate('Menus with 🥗 or 🧆');
    // => 'Menus with  or '

.. tip::

    Combine this emoji transliterator with the :ref:`Symfony String slugger <string-slugger-emoji>`
    to improve the slugs of contents that include emojis (e.g. for URLs).

.. _`Unicode CLDR dataset`: https://github.com/unicode-org/cldr
