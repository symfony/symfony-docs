Working with Emojis
===================

.. versionadded:: 7.1

    The emoji component was introduced in Symfony 7.1.

Symfony provides several utilities to work with emoji characters and sequences
from the `Unicode CLDR dataset`_. They are available via the Emoji component,
which you must first install in your application:

.. _installation:

.. code-block:: terminal

    $ composer require symfony/emoji

.. include:: /components/require_autoload.rst.inc

The data needed to store the transliteration of all emojis (~5,000) into all
languages take a considerable disk space.

If you need to save disk space (e.g. because you deploy to some service with tight
size constraints), run this command (e.g. as an automated script after ``composer install``)
to compress the internal Symfony emoji data files using the PHP ``zlib`` extension:

.. code-block:: terminal

    # adjust the path to the 'compress' binary based on your application installation
    $ php ./vendor/symfony/emoji/Resources/bin/compress

.. _emoji-transliteration:

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

.. tip::

    When using the :ref:`slugger <string-slugger>` from the String component,
    you can combine it with the ``EmojiTransliterator`` to :ref:`slugify emojis <string-slugger-emoji>`.

Transliterating Emoji Text Short Codes
--------------------------------------

Services like GitHub and Slack allows to include emojis in your messages using
text short codes (e.g. you can add the ``:+1:`` code to render the 👍 emoji).

Symfony also provides a feature to transliterate emojis into short codes and vice
versa. The short codes are slightly different on each service, so you must pass
the name of the service as an argument when creating the transliterator.

GitHub Emoji Short Codes Transliteration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Convert emojis to GitHub short codes with the ``emoji-github`` locale::

    $transliterator = EmojiTransliterator::create('emoji-github');
    $transliterator->transliterate('Teenage 🐢 really love 🍕');
    // => 'Teenage :turtle: really love :pizza:'

Convert GitHub short codes to emojis with the ``github-emoji`` locale::

    $transliterator = EmojiTransliterator::create('github-emoji');
    $transliterator->transliterate('Teenage :turtle: really love :pizza:');
    // => 'Teenage 🐢 really love 🍕'

Gitlab Emoji Short Codes Transliteration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Convert emojis to Gitlab short codes with the ``emoji-gitlab`` locale::

    $transliterator = EmojiTransliterator::create('emoji-gitlab');
    $transliterator->transliterate('Breakfast with 🥝 or 🥛');
    // => 'Breakfast with :kiwi: or :milk:'

Convert Gitlab short codes to emojis with the ``gitlab-emoji`` locale::

    $transliterator = EmojiTransliterator::create('gitlab-emoji');
    $transliterator->transliterate('Breakfast with :kiwi: or :milk:');
    // => 'Breakfast with 🥝 or 🥛'

Slack Emoji Short Codes Transliteration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Convert emojis to Slack short codes with the ``emoji-slack`` locale::

    $transliterator = EmojiTransliterator::create('emoji-slack');
    $transliterator->transliterate('Menus with 🥗 or 🧆');
    // => 'Menus with :green_salad: or :falafel:'

Convert Slack short codes to emojis with the ``slack-emoji`` locale::

    $transliterator = EmojiTransliterator::create('slack-emoji');
    $transliterator->transliterate('Menus with :green_salad: or :falafel:');
    // => 'Menus with 🥗 or 🧆'

.. _text-emoji:

Universal Emoji Short Codes Transliteration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you don't know which service was used to generate the short codes, you can use
the ``text-emoji`` locale, which combines all codes from all services::

    $transliterator = EmojiTransliterator::create('text-emoji');

    // Github short codes
    $transliterator->transliterate('Breakfast with :kiwi-fruit: or :milk-glass:');
    // Gitlab short codes
    $transliterator->transliterate('Breakfast with :kiwi: or :milk:');
    // Slack short codes
    $transliterator->transliterate('Breakfast with :kiwifruit: or :glass-of-milk:');

    // all the above examples produce the same result:
    // => 'Breakfast with 🥝 or 🥛'

You can convert emojis to short codes with the ``emoji-text`` locale::

    $transliterator = EmojiTransliterator::create('emoji-text');
    $transliterator->transliterate('Breakfast with 🥝 or 🥛');
    // => 'Breakfast with :kiwifruit: or :milk-glass:

Inverse Emoji Transliteration
-----------------------------

Given the textual representation of an emoji, you can reverse it back to get the
actual emoji thanks to the :ref:`emojify filter <reference-twig-filter-emojify>`:

.. code-block:: twig

    {{ 'I like :kiwi-fruit:'|emojify }} {# renders: I like 🥝 #}
    {{ 'I like :kiwi:'|emojify }}       {# renders: I like 🥝 #}
    {{ 'I like :kiwifruit:'|emojify }}  {# renders: I like 🥝 #}

By default, ``emojify`` uses the :ref:`text catalog <text-emoji>`, which
merges the emoji text codes of all services. If you prefer, you can select a
specific catalog to use:

.. code-block:: twig

    {{ 'I :green-heart: this'|emojify }}                  {# renders: I 💚 this #}
    {{ ':green_salad: is nice'|emojify('slack') }}        {# renders: 🥗 is nice #}
    {{ 'My :turtle: has no name yet'|emojify('github') }} {# renders: My 🐢 has no name yet #}
    {{ ':kiwi: is a great fruit'|emojify('gitlab') }}     {# renders: 🥝 is a great fruit #}

Removing Emojis
---------------

The ``EmojiTransliterator`` can also be used to remove all emojis from a string,
via the special ``strip`` locale::

    use Symfony\Component\Emoji\EmojiTransliterator;

    $transliterator = EmojiTransliterator::create('strip');
    $transliterator->transliterate('🎉Hey!🥳 🎁Happy Birthday!🎁');
    // => 'Hey! Happy Birthday!'

.. _`Unicode CLDR dataset`: https://github.com/unicode-org/cldr
