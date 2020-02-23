.. index::
   single: String
   single: Components; String

The String Component
====================

    The String component provides a single object-oriented API to work with
    three "unit systems" of strings: bytes, code points and grapheme clusters.

.. versionadded:: 5.0

    The String component was introduced in Symfony 5.0 as an
    :doc:`experimental feature </contributing/code/experimental>`.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/string

.. include:: /components/require_autoload.rst.inc

What is a String?
-----------------

You can skip this section if you already know what a *"code point"* or a
*"grapheme cluster"* are in the context of handling strings. Otherwise, read
this section to learn about the terminology used by this component.

Languages like English require a very limited set of characters and symbols to
display any content. Each string is a series of characters (letters or symbols)
and they can be encoded even with the most limited standards (e.g. `ASCII`_).

However, other languages require thousands of symbols to display their contents.
They need complex encoding standards such as `Unicode`_ and concepts like
"character" no longer make sense. Instead, you have to deal with these terms:

* `Code points`_: they are the atomic unit of information. A string is a series
  of code points. Each code point is a number whose meaning is given by the
  `Unicode`_ standard. For example, the English letter ``A`` is the ``U+0041``
  code point and the Japanese *kana* ``の`` is the ``U+306E`` code point.
* `Grapheme clusters`_: they are a sequence of one or more code points which are
  displayed as a single graphical unit. For example, the Spanish letter ``ñ`` is
  a grapheme cluster that contains two code points: ``U+006E`` = ``n`` (*"latin
  small letter N"*) + ``U+0303`` = ``◌̃`` (*"combining tilde"*).
* Bytes: they are the actual information stored for the string contents. Each
  code point can require one or more bytes of storage depending on the standard
  being used (UTF-8, UTF-16, etc.).

The following image displays the bytes, code points and grapheme clusters for
the same word written in English (``hello``) and Hindi (``नमस्ते``):

.. image:: /_images/components/string/bytes-points-graphemes.png
   :align: center

Usage
-----

Create a new object of type :class:`Symfony\\Component\\String\\ByteString`,
:class:`Symfony\\Component\\String\\CodePointString` or
:class:`Symfony\\Component\\String\\UnicodeString`, pass the string contents as
their arguments and then use the object-oriented API to work with those strings::

    use Symfony\Component\String\UnicodeString;

    $text = (new UnicodeString('This is a déjà-vu situation.'))
        ->trimEnd('.')
        ->replace('déjà-vu', 'jamais-vu')
        ->append('!');
    // $text = 'This is a jamais-vu situation!'

    $content = new UnicodeString('नमस्ते दुनिया');
    if ($content->ignoreCase()->startsWith('नमस्ते')) {
        // ...
    }

Method Reference
----------------

Methods to Create String Objects
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First, you can create objects prepared to store strings as bytes, code points
and grapheme clusters with the following classes::

    use Symfony\Component\String\ByteString;
    use Symfony\Component\String\CodePointString;
    use Symfony\Component\String\UnicodeString;

    $foo = new ByteString('hello');
    $bar = new CodePointString('hello');
    // UnicodeString is the most commonly used class
    $baz = new UnicodeString('hello');

Use the ``wrap()`` static method to instantiate more than one string object::

    $contents = ByteString::wrap(['hello', 'world']);        // $contents = ByteString[]
    $contents = UnicodeString::wrap(['I', '❤️', 'Symfony']); // $contents = UnicodeString[]

    // use the unwrap method to make the inverse conversion
    $contents = UnicodeString::unwrap([
        new UnicodeString('hello'), new UnicodeString('world'),
    ]); // $contents = ['hello', 'world']

There are two shortcut functions called ``b()`` and ``u()`` to create
``ByteString`` and ``UnicodeString`` objects::

    // ...
    use function Symfony\Component\String\b;
    use function Symfony\Component\String\u;

    // both are equivalent
    $foo = new ByteString('hello');
    $foo = b('hello');

    // both are equivalent
    $baz = new UnicodeString('hello');
    $baz = u('hello');

There are also some specialized constructors::

    // ByteString can create a random string of the given length
    $foo = ByteString::fromRandom(12);

    // CodePointString and UnicodeString can create a string from code points
    $foo = UnicodeString::fromCodePoints(0x928, 0x92E, 0x938, 0x94D, 0x924, 0x947);
    // equivalent to: $foo = new UnicodeString('नमस्ते');

Methods to Transform String Objects
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Each string object can be transformed into the other two types of objects::

    $foo = ByteString::fromRandom(12)->toCodePointString();
    $foo = (new CodePointString('hello'))->toUnicodeString();
    $foo = UnicodeString::fromCodePoints(0x68, 0x65, 0x6C, 0x6C, 0x6F)->toByteString();

    // the optional $toEncoding argument defines the encoding of the target string
    $foo = (new CodePointString('hello'))->toByteString('Windows-1252');
    // the optional $fromEncoding argument defines the encoding of the original string
    $foo = (new ByteString('さよなら'))->toCodePointString('ISO-2022-JP');

If the conversion is not possible for any reason, you'll get an
:class:`Symfony\\Component\\String\\Exception\\InvalidArgumentException`.

Methods Related to Length and White Spaces
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // returns the number of graphemes, code points or bytes of the given string
    $word = 'नमस्ते';
    (new ByteString($word))->length();      // 18 (bytes)
    (new CodePointString($word))->length(); // 6 (code points)
    (new UnicodeString($word))->length();   // 4 (graphemes)

    // some symbols require double the width of others to represent them when using
    // a monospaced font (e.g. in a console). This method returns the total width
    // needed to represent the entire word
    $word = 'नमस्ते';
    (new ByteString($word))->width();      // 18
    (new CodePointString($word))->width(); // 4
    (new UnicodeString($word))->width();   // 4
    // if the text contains multiple lines, it returns the max width of all lines
    $text = "<<<END
    This is a
    multiline text
    END";
    u($text)->width(); // 14

    // only returns TRUE if the string is exactly an empty string (not even white spaces)
    u('hello world')->isEmpty();  // false
    u('     ')->isEmpty();        // false
    u('')->isEmpty();             // true

    // removes all white spaces from the start and end of the string and replaces two
    // or more consecutive white spaces inside contents by a single white space
    u("  \n\n   hello        world \n    \n")->collapseWhitespace(); // 'hello world'

Methods to Change Case
~~~~~~~~~~~~~~~~~~~~~~

::

    // changes all graphemes/code points to lower case
    u('FOO Bar')->lower();  // 'foo bar'

    // when dealing with different languages, uppercase/lowercase is not enough
    // there are three cases (lower, upper, title), some characters have no case,
    // case is context-sensitive and locale-sensitive, etc.
    // this method returns a string that you can use in case-insensitive comparisons
    u('FOO Bar')->folded();             // 'foo bar'
    u('Die O\'Brian Straße')->folded(); // "die o'brian strasse"

    // changes all graphemes/code points to upper case
    u('foo BAR')->upper(); // 'FOO BAR'

    // changes all graphemes/code points to "title case"
    u('foo bar')->title();     // 'Foo bar'
    u('foo bar')->title(true); // 'Foo Bar'

    // changes all graphemes/code points to camelCase
    u('Foo: Bar-baz.')->camel(); // 'fooBarBaz'
    // changes all graphemes/code points to snake_case
    u('Foo: Bar-baz.')->snake(); // 'foo_bar_baz'
    // other cases can be achieved by chaining methods. E.g. PascalCase:
    u('Foo: Bar-baz.')->camel()->title(); // 'FooBarBaz'

The methods of all string classes are case-sensitive by default. You can perform
case-insensitive operations with the ``ignoreCase()`` method::

    u('abc')->indexOf('B');               // null
    u('abc')->ignoreCase()->indexOf('B'); // 1

Methods to Append and Prepend
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // adds the given content (one or more strings) at the beginning/end of the string
    u('world')->prepend('hello');      // 'helloworld'
    u('world')->prepend('hello', ' '); // 'hello world'

    u('hello')->append('world');      // 'helloworld'
    u('hello')->append(' ', 'world'); // 'hello world'

    // adds the given content at the beginning of the string (or removes it) to
    // make sure that the content starts exactly with that content
    u('Name')->ensureStart('get');       // 'getName'
    u('getName')->ensureStart('get');    // 'getName'
    u('getgetName')->ensureStart('get'); // 'getName'
    // this method is similar, but works on the end of the content instead of on the beginning
    u('User')->ensureEnd('Controller');           // 'UserController'
    u('UserController')->ensureEnd('Controller'); // 'UserController'
    u('UserControllerController')->ensureEnd('Controller'); // 'UserController'

    // returns the contents found before/after the first occurrence of the given string
    u('hello world')->before('world');   // 'hello '
    u('hello world')->before('o');       // 'hell'
    u('hello world')->before('o', true); // 'hello'

    u('hello world')->after('hello');   // ' world'
    u('hello world')->after('o');       // ' world'
    u('hello world')->after('o', true); // 'o world'

    // returns the contents found before/after the last occurrence of the given string
    u('hello world')->beforeLast('o');       // 'hello w'
    u('hello world')->beforeLast('o', true); // 'hello wo'

    u('hello world')->afterLast('o');       // 'rld'
    u('hello world')->afterLast('o', true); // 'orld'

Methods to Pad and Trim
~~~~~~~~~~~~~~~~~~~~~~~

::

    // makes a string as long as the first argument by adding the given
    // string at the beginning, end or both sides of the string
    u(' Lorem Ipsum ')->padBoth(20, '-'); // '--- Lorem Ipsum ----'
    u(' Lorem Ipsum')->padStart(20, '-'); // '-------- Lorem Ipsum'
    u('Lorem Ipsum ')->padEnd(20, '-');   // 'Lorem Ipsum --------'

    // repeats the given string the number of times passed as argument
    u('_.')->repeat(10); // '_._._._._._._._._._.'

    // removes the given characters (by default, white spaces) from the string
    u('   Lorem Ipsum   ')->trim(); // 'Lorem Ipsum'
    u('Lorem Ipsum   ')->trim('m'); // 'Lorem Ipsum   '
    u('Lorem Ipsum')->trim('m');    // 'Lorem Ipsu'

    u('   Lorem Ipsum   ')->trimStart(); // 'Lorem Ipsum   '
    u('   Lorem Ipsum   ')->trimEnd();   // '   Lorem Ipsum'

Methods to Search and Replace
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // checks if the string starts/ends with the given string
    u('https://symfony.com')->startsWith('https'); // true
    u('report-1234.pdf')->endsWith('.pdf');        // true

    // checks if the string contents are exactly the same as the given contents
    u('foo')->equalsTo('foo'); // true

    // checks if the string content match the given regular expression
    u('avatar-73647.png')->match('/avatar-(\d+)\.png/');
    // result = ['avatar-73647.png', '73647']

    // finds the position of the first occurrence of the given string
    // (the second argument is the position where the search starts and negative
    // values have the same meaning as in PHP functions)
    u('abcdeabcde')->indexOf('c');     // 2
    u('abcdeabcde')->indexOf('c', 2);  // 2
    u('abcdeabcde')->indexOf('c', -4); // 7
    u('abcdeabcde')->indexOf('eab');   // 4
    u('abcdeabcde')->indexOf('k');     // null

    // finds the position of the last occurrence of the given string
    // (the second argument is the position where the search starts and negative
    // values have the same meaning as in PHP functions)
    u('abcdeabcde')->indexOfLast('c');     // 7
    u('abcdeabcde')->indexOfLast('c', 2);  // 7
    u('abcdeabcde')->indexOfLast('c', -4); // 2
    u('abcdeabcde')->indexOfLast('eab');   // 4
    u('abcdeabcde')->indexOfLast('k');     // null

    // replaces all occurrences of the given string
    u('http://symfony.com')->replace('http://', 'https://'); // 'https://symfony.com'
    // replaces all occurrences of the given regular expression
    u('(+1) 206-555-0100')->replaceMatches('/[^A-Za-z0-9]++/', ''); // '12065550100'
    // you can pass a callable as the second argument to perform advanced replacements
    u('123')->replaceMatches('/\d/', function ($match) {
        return '['.$match[0].']';
    }); // result = '[1][2][3]'

Methods to Join, Split and Truncate
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // uses the string as the "glue" to merge all the given strings
    u(', ')->join(['foo', 'bar']); // 'foo, bar'

    // breaks the string into pieces using the given delimiter
    u('template_name.html.twig')->split('.');    // ['template_name', 'html', 'twig']
    // you can set the maximum number of pieces as the second argument
    u('template_name.html.twig')->split('.', 2); // ['template_name', 'html.twig']

    // returns a substring which starts at the first argument and has the length of the
    // second optional argument (negative values have the same meaning as in PHP functions)
    u('Symfony is great')->slice(0, 7);  // 'Symfony'
    u('Symfony is great')->slice(0, -6); // 'Symfony is'
    u('Symfony is great')->slice(11);    // 'great'
    u('Symfony is great')->slice(-5);    // 'great'

    // reduces the string to the length given as argument (if it's longer)
    u('Lorem Ipsum')->truncate(80);     // 'Lorem Ipsum'
    u('Lorem Ipsum')->truncate(3);      // 'Lor'
    u('Lorem Ipsum')->truncate(8, '…'); // 'Lorem I…'

    // breaks the string into lines of the given length
    u('Lorem Ipsum')->wordwrap(4);             // 'Lorem\nIpsum'
    // by default it breaks by white space; pass TRUE to break unconditionally
    u('Lorem Ipsum')->wordwrap(4, "\n", true); // 'Lore\nm\nIpsu\nm'

    // replaces a portion of the string with the given contents:
    // the second argument is the position where the replacement starts;
    // the third argument is the number of graphemes/code points removed from the string
    u('0123456789')->splice('xxx');       // 'xxx'
    u('0123456789')->splice('xxx', 0, 2); // 'xxx23456789'
    u('0123456789')->splice('xxx', 0, 6); // 'xxx6789'
    u('0123456789')->splice('xxx', 6);    // '012345xxx'

    // breaks the string into pieces of the length given as argument
    u('0123456789')->chunk(3);  // ['012', '345', '678', '9']

Methods Added by ByteString
~~~~~~~~~~~~~~~~~~~~~~~~~~~

These methods are only available for ``ByteString`` objects::

    // returns TRUE if the string contents are valid UTF-8 contents
    b('Lorem Ipsum')->isUtf8(); // true
    b("\xc3\x28")->isUtf8();    // false

    // returns the value of the byte stored at the given position
    // ('नमस्ते' bytes = [224, 164, 168, 224, 164, 174, 224, 164, 184,
    //                  224, 165, 141, 224, 164, 164, 224, 165, 135])
    b('नमस्ते')->byteCode(0);  // 224
    b('नमस्ते')->byteCode(17); // 135

Methods Added by CodePointString and UnicodeString
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

These methods are only available for ``CodePointString`` and ``UnicodeString``
objects::

    // transliterates any string into the latin alphabet defined by the ASCII encoding
    // (don't use this method to build a slugger because this component already provides
    // a slugger, as explained later in this article)
    u('नमस्ते')->ascii();    // 'namaste'
    u('さよなら')->ascii(); // 'sayonara'
    u('спасибо')->ascii(); // 'spasibo'

    // returns an array with the code point or points stored at the given position
    // (code points of 'नमस्ते' graphemes = [2344, 2350, 2360, 2340]
    u('नमस्ते')->codePointsAt(0); // [2344]
    u('नमस्ते')->codePointsAt(2); // [2360]

`Unicode equivalence`_ is the specification by the Unicode standard that
different sequences of code points represent the same character. For example,
the Swedish letter ``å`` can be a single code point (``U+00E5`` = *"latin small
letter A with ring above"*) or a sequence of two code points (``U+0061`` =
*"latin small letter A"* + ``U+030A`` = *"combining ring above"*). The
``normalize()`` method allows to pick the normalization mode::

    // these encode the letter as a single code point: U+00E5
    u('å')->normalize(UnicodeString::NFC);
    u('å')->normalize(UnicodeString::NFKC);
    // these encode the letter as two code points: U+0061 + U+030A
    u('å')->normalize(UnicodeString::NFD);
    u('å')->normalize(UnicodeString::NFKD);

Slugger
-------

In some contexts, such as URLs and file/directory names, it's not safe to use
any Unicode character. A *slugger* transforms a given string into another string
that only includes safe ASCII characters::

    use Symfony\Component\String\Slugger\AsciiSlugger;

    $slugger = new AsciiSlugger();
    $slug = (string) $slugger->slug('Wôrķšƥáçè ~~sèťtïñğš~~');
    // $slug = 'Workspace-settings'

The separator between words is a dash (``-``) by default, but you can define
another separator as the second argument::

    $slug = (string) $slugger->slug('Wôrķšƥáçè ~~sèťtïñğš~~', '/');
    // $slug = 'Workspace/settings'

The slugger transliterates the original string into the Latin script before
applying the other transformations. The locale of the original string is
detected automatically, but you can define it explicitly::

    // this tells the slugger to transliterate from Korean language
    $slugger = new AsciiSlugger('ko');

    // you can override the locale as the third optional parameter of slug()
    $slug = $slugger->slug('...', '-', 'fa');

In a Symfony application, you don't need to create the slugger yourself. Thanks
to :doc:`service autowiring </service_container/autowiring>`, you can inject a
slugger by type-hinting a service constructor argument with the
:class:`Symfony\\Component\\String\\Slugger\\SluggerInterface`. The locale of
the injected slugger is the same as the request locale::

    use Symfony\Component\String\Slugger\SluggerInterface;

    class MyService
    {
        private $slugger;

        public function __construct(SluggerInterface $slugger)
        {
            $this->slugger = $slugger;
        }

        public function someMethod()
        {
            $slug = $this->slugger->slug('...');
        }
    }

.. _`ASCII`: https://en.wikipedia.org/wiki/ASCII
.. _`Unicode`: https://en.wikipedia.org/wiki/Unicode
.. _`Code points`: https://en.wikipedia.org/wiki/Code_point
.. _`Grapheme clusters`: https://en.wikipedia.org/wiki/Grapheme
.. _`Unicode equivalence`: https://en.wikipedia.org/wiki/Unicode_equivalence
