The Emoji Component
===================

    The Emoji component provides utilities to work with emoji characters and
    sequences from the `Unicode CLDR dataset`_.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/emoji

.. include:: /components/require_autoload.rst.inc


Emoji Transliteration
---------------------

The ``EmojiTransliterator`` class offers a way to translate emojis into their
textual representation in all languages based on the `Unicode CLDR dataset`_::

    use Symfony\Component\Emoji\EmojiTransliterator;

    // Describe emojis in English
    $transliterator = EmojiTransliterator::create('en');
    $transliterator->transliterate('Menus with 🍕 or 🍝');
    // => 'Menus with pizza or spaghetti'

    // Describe emojis in Ukrainian
    $transliterator = EmojiTransliterator::create('uk');
    $transliterator->transliterate('Menus with 🍕 or 🍝');
    // => 'Menus with піца or спагеті'


The ``EmojiTransliterator`` also provides special locales that convert emojis to
short codes and vice versa in specific platforms, such as GitHub and Slack.

GitHub
~~~~~~

Convert GitHub emojis to short codes with the ``emoji-github`` locale::

    $transliterator = EmojiTransliterator::create('emoji-github');
    $transliterator->transliterate('Teenage 🐢 really love 🍕');
    // => 'Teenage :turtle: really love :pizza:'

Convert GitHub short codes to emojis with the ``github-emoji`` locale::

    $transliterator = EmojiTransliterator::create('github-emoji');
    $transliterator->transliterate('Teenage :turtle: really love :pizza:');
    // => 'Teenage 🐢 really love 🍕'

Slack
~~~~~

Convert Slack emojis to short codes with the ``emoji-slack`` locale::

    $transliterator = EmojiTransliterator::create('emoji-slack');
    $transliterator->transliterate('Menus with 🥗 or 🧆');
    // => 'Menus with :green_salad: or :falafel:'

Convert Slack short codes to emojis with the ``slack-emoji`` locale::

    $transliterator = EmojiTransliterator::create('slack-emoji');
    $transliterator->transliterate('Menus with :green_salad: or :falafel:');
    // => 'Menus with 🥗 or 🧆'


Emoji Slugger
-------------

Combine the emoji transliterator with the :doc:`/components/string`
to improve the slugs of contents that include emojis (e.g. for URLs).

Call the ``AsciiSlugger::withEmoji()`` method to enable the emoji transliterator in the Slugger::

    use Symfony\Component\String\Slugger\AsciiSlugger;

    $slugger = new AsciiSlugger();
    $slugger = $slugger->withEmoji();

    $slug = $slugger->slug('a 😺, 🐈‍⬛, and a 🦁 go to 🏞️', '-', 'en');
    // $slug = 'a-grinning-cat-black-cat-and-a-lion-go-to-national-park';

    $slug = $slugger->slug('un 😺, 🐈‍⬛, et un 🦁 vont au 🏞️', '-', 'fr');
    // $slug = 'un-chat-qui-sourit-chat-noir-et-un-tete-de-lion-vont-au-parc-national';

.. tip::

    Integrating the Emoji Component with the String component is straightforward and requires no additional
    configuration.string.

Removing Emojis
---------------

The ``EmojiTransliterator`` can also be used to remove all emojis from a string, via the
special ``strip`` locale::

    use Symfony\Component\Emoji\EmojiTransliterator;

    $transliterator = EmojiTransliterator::create('strip');
    $transliterator->transliterate('🎉Hey!🥳 🎁Happy Birthday!🎁');
    // => 'Hey! Happy Birthday!'

Disk space
----------

The data needed to store the transliteration of all emojis (~5,000) into all
languages take a considerable disk space.

If you need to save disk space (e.g. because you deploy to some service with tight
size constraints), run this command (e.g. as an automated script after ``composer install``)
to compress the internal Symfony emoji data files using the PHP ``zlib`` extension:

.. code-block:: terminal

    # adjust the path to the 'compress' binary based on your application installation
    $ php ./vendor/symfony/emoji/Resources/bin/compress


.. _`Unicode CLDR dataset`: https://github.com/unicode-org/cldr
