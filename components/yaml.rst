.. index::
   single: Yaml
   single: Components; Yaml

The Yaml Component
==================

    The Yaml component loads and dumps YAML files.

What is It?
-----------

The Symfony Yaml component parses YAML strings to convert them to PHP arrays.
It is also able to convert PHP arrays to YAML strings.

`YAML`_, *YAML Ain't Markup Language*, is a human friendly data serialization
standard for all programming languages. YAML is a great format for your
configuration files. YAML files are as expressive as XML files and as readable
as INI files.

The Symfony Yaml Component implements a selected subset of features defined in
the `YAML 1.2 version specification`_.

.. tip::

    Learn more about :ref:`YAML specifications <yaml-format>`.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/yaml

.. include:: /components/require_autoload.rst.inc

Why?
----

Fast
~~~~

One of the goals of Symfony Yaml is to find the right balance between speed and
features. It supports just the needed features to handle configuration files.
Notable lacking features are: document directives, multi-line quoted messages,
compact block collections and multi-document files.

Real Parser
~~~~~~~~~~~

It sports a real parser and is able to parse a large subset of the YAML
specification, for all your configuration needs. It also means that the parser
is pretty robust, easy to understand, and simple enough to extend.

Clear Error Messages
~~~~~~~~~~~~~~~~~~~~

Whenever you have a syntax problem with your YAML files, the library outputs a
helpful message with the filename and the line number where the problem
occurred. It eases the debugging a lot.

Dump Support
~~~~~~~~~~~~

It is also able to dump PHP arrays to YAML with object support, and inline
level configuration for pretty outputs.

Types Support
~~~~~~~~~~~~~

It supports most of the YAML built-in types like dates, integers, octal numbers,
booleans, and much more...

Full Merge Key Support
~~~~~~~~~~~~~~~~~~~~~~

Full support for references, aliases, and full merge key. Don't repeat
yourself by referencing common configuration bits.

.. _using-the-symfony2-yaml-component:

Using the Symfony YAML Component
--------------------------------

The Symfony Yaml component consists of two main classes:
one parses YAML strings (:class:`Symfony\\Component\\Yaml\\Parser`), and the
other dumps a PHP array to a YAML string
(:class:`Symfony\\Component\\Yaml\\Dumper`).

On top of these two classes, the :class:`Symfony\\Component\\Yaml\\Yaml` class
acts as a thin wrapper that simplifies common uses.

Reading YAML Contents
~~~~~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Component\\Yaml\\Yaml::parse` method parses a YAML
string and converts it to a PHP array::

    use Symfony\Component\Yaml\Yaml;

    $value = Yaml::parse("foo: bar");
    // $value = ['foo' => 'bar']

If an error occurs during parsing, the parser throws a
:class:`Symfony\\Component\\Yaml\\Exception\\ParseException` exception
indicating the error type and the line in the original YAML string where the
error occurred::

    use Symfony\Component\Yaml\Exception\ParseException;

    try {
        $value = Yaml::parse('...');
    } catch (ParseException $exception) {
        printf('Unable to parse the YAML string: %s', $exception->getMessage());
    }

Reading YAML Files
~~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Component\\Yaml\\Yaml::parseFile` method parses the YAML
contents of the given file path and converts them to a PHP value::

    use Symfony\Component\Yaml\Yaml;

    $value = Yaml::parseFile('/path/to/file.yaml');

If an error occurs during parsing, the parser throws a ``ParseException`` exception.

.. _components-yaml-dump:

Writing YAML Files
~~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Component\\Yaml\\Yaml::dump` method dumps any PHP
array to its YAML representation::

    use Symfony\Component\Yaml\Yaml;

    $array = [
        'foo' => 'bar',
        'bar' => ['foo' => 'bar', 'bar' => 'baz'],
    ];

    $yaml = Yaml::dump($array);

    file_put_contents('/path/to/file.yaml', $yaml);

If an error occurs during the dump, the parser throws a
:class:`Symfony\\Component\\Yaml\\Exception\\DumpException` exception.

.. _array-expansion-and-inlining:

Expanded and Inlined Arrays
...........................

The YAML format supports two kind of representation for arrays, the expanded
one, and the inline one. By default, the dumper uses the expanded
representation:

.. code-block:: yaml

    foo: bar
    bar:
        foo: bar
        bar: baz

The second argument of the :method:`Symfony\\Component\\Yaml\\Yaml::dump`
method customizes the level at which the output switches from the expanded
representation to the inline one::

    echo Yaml::dump($array, 1);

.. code-block:: yaml

    foo: bar
    bar: { foo: bar, bar: baz }

.. code-block:: php

    echo Yaml::dump($array, 2);

.. code-block:: yaml

    foo: bar
    bar:
        foo: bar
        bar: baz

Indentation
...........

By default, the YAML component will use 4 spaces for indentation. This can be
changed using the third argument as follows::

    // uses 8 spaces for indentation
    echo Yaml::dump($array, 2, 8);

.. code-block:: yaml

    foo: bar
    bar:
            foo: bar
            bar: baz

Numeric Literals
................

Long numeric literals, being integer, float or hexadecimal, are known for their
poor readability in code and configuration files. That's why YAML files allow to
add underscores to improve their readability:

.. code-block:: yaml

    parameters:
        credit_card_number: 1234_5678_9012_3456
        long_number: 10_000_000_000
        pi: 3.14159_26535_89793
        hex_words: 0x_CAFE_F00D

During the parsing of the YAML contents, all the ``_`` characters are removed
from the numeric literal contents, so there is not a limit in the number of
underscores you can include or the way you group contents.

Advanced Usage: Flags
---------------------

.. _objects-for-mappings:

Object Parsing and Dumping
~~~~~~~~~~~~~~~~~~~~~~~~~~

You can dump objects by using the ``DUMP_OBJECT`` flag::

    $object = new \stdClass();
    $object->foo = 'bar';

    $dumped = Yaml::dump($object, 2, 4, Yaml::DUMP_OBJECT);
    // !php/object 'O:8:"stdClass":1:{s:5:"foo";s:7:"bar";}'

And parse them by using the ``PARSE_OBJECT`` flag::

    $parsed = Yaml::parse($dumped, Yaml::PARSE_OBJECT);
    var_dump(is_object($parsed)); // true
    echo $parsed->foo; // bar

The YAML component uses PHP's ``serialize()`` method to generate a string
representation of the object.

.. caution::

    Object serialization is specific to this implementation, other PHP YAML
    parsers will likely not recognize the ``php/object`` tag and non-PHP
    implementations certainly will not - use with discretion!

Parsing and Dumping Objects as Maps
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can dump objects as Yaml maps by using the ``DUMP_OBJECT_AS_MAP`` flag::

    $object = new \stdClass();
    $object->foo = 'bar';

    $dumped = Yaml::dump(['data' => $object], 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
    // $dumped = "data:\n    foo: bar"

And parse them by using the ``PARSE_OBJECT_FOR_MAP`` flag::

    $parsed = Yaml::parse($dumped, Yaml::PARSE_OBJECT_FOR_MAP);
    var_dump(is_object($parsed)); // true
    var_dump(is_object($parsed->data)); // true
    echo $parsed->data->foo; // bar

The YAML component uses PHP's ``(array)`` casting to generate a string
representation of the object as a map.

.. _invalid-types-and-object-serialization:

Handling Invalid Types
~~~~~~~~~~~~~~~~~~~~~~

By default, the parser will encode invalid types as ``null``. You can make the
parser throw exceptions by using the ``PARSE_EXCEPTION_ON_INVALID_TYPE``
flag::

    $yaml = '!php/object \'O:8:"stdClass":1:{s:5:"foo";s:7:"bar";}\'';
    Yaml::parse($yaml, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE); // throws an exception

Similarly you can use ``DUMP_EXCEPTION_ON_INVALID_TYPE`` when dumping::

    $data = new \stdClass(); // by default objects are invalid.
    Yaml::dump($data, 2, 4, Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE); // throws an exception

Date Handling
~~~~~~~~~~~~~

By default, the YAML parser will convert unquoted strings which look like a
date or a date-time into a Unix timestamp; for example ``2016-05-27`` or
``2016-05-27T02:59:43.1Z`` (`ISO-8601`_)::

    Yaml::parse('2016-05-27'); // 1464307200

You can make it convert to a ``DateTime`` instance by using the ``PARSE_DATETIME``
flag::

    $date = Yaml::parse('2016-05-27', Yaml::PARSE_DATETIME);
    var_dump(get_class($date)); // DateTime

Dumping Multi-line Literal Blocks
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In YAML, multiple lines can be represented as literal blocks. By default, the
dumper will encode multiple lines as an inline string::

    $string = ["string" => "Multiple\nLine\nString"];
    $yaml = Yaml::dump($string);
    echo $yaml; // string: "Multiple\nLine\nString"

You can make it use a literal block with the ``DUMP_MULTI_LINE_LITERAL_BLOCK``
flag::

    $string = ["string" => "Multiple\nLine\nString"];
    $yaml = Yaml::dump($string, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    echo $yaml;
    //  string: |
    //       Multiple
    //       Line
    //       String

Parsing PHP Constants
~~~~~~~~~~~~~~~~~~~~~

By default, the YAML parser treats the PHP constants included in the contents as
regular strings. Use the ``PARSE_CONSTANT`` flag and the special ``!php/const``
syntax to parse them as proper PHP constants::

    $yaml = '{ foo: PHP_INT_SIZE, bar: !php/const PHP_INT_SIZE }';
    $parameters = Yaml::parse($yaml, Yaml::PARSE_CONSTANT);
    // $parameters = ['foo' => 'PHP_INT_SIZE', 'bar' => 8];

Parsing and Dumping of Binary Data
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Non UTF-8 encoded strings are dumped as base64 encoded data::

    $imageContents = file_get_contents(__DIR__.'/images/logo.png');

    $dumped = Yaml::dump(['logo' => $imageContents]);
    // logo: !!binary iVBORw0KGgoAAAANSUhEUgAAA6oAAADqCAY...

Binary data is automatically parsed if they include the ``!!binary`` YAML tag::

    $dumped = 'logo: !!binary iVBORw0KGgoAAAANSUhEUgAAA6oAAADqCAY...';
    $parsed = Yaml::parse($dumped);
    $imageContents = $parsed['logo'];

Parsing and Dumping Custom Tags
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to the built-in support of tags like ``!php/const`` and
``!!binary``, you can define your own custom YAML tags and parse them with the
``PARSE_CUSTOM_TAGS`` flag::

    $data = "!my_tag { foo: bar }";
    $parsed = Yaml::parse($data, Yaml::PARSE_CUSTOM_TAGS);
    // $parsed = Symfony\Component\Yaml\Tag\TaggedValue('my_tag', ['foo' => 'bar']);
    $tagName = $parsed->getTag();    // $tagName = 'my_tag'
    $tagValue = $parsed->getValue(); // $tagValue = ['foo' => 'bar']

If the contents to dump contain :class:`Symfony\\Component\\Yaml\\Tag\\TaggedValue`
objects, they are automatically transformed into YAML tags::

    use Symfony\Component\Yaml\Tag\TaggedValue;

    $data = new TaggedValue('my_tag', ['foo' => 'bar']);
    $dumped = Yaml::dump($data);
    // $dumped = '!my_tag { foo: bar }'

Dumping Null Values
~~~~~~~~~~~~~~~~~~~

The official YAML specification uses both ``null`` and ``~`` to represent null
values. This component uses ``null`` by default when dumping null values but
you can dump them as ``~`` with the ``DUMP_NULL_AS_TILDE`` flag::

    $dumped = Yaml::dump(['foo' => null]);
    // foo: null

    $dumped = Yaml::dump(['foo' => null], 2, 4, Yaml::DUMP_NULL_AS_TILDE);
    // foo: ~

Syntax Validation
~~~~~~~~~~~~~~~~~

The syntax of YAML contents can be validated through the CLI using the
:class:`Symfony\\Component\\Yaml\\Command\\LintCommand` command.

First, install the Console component:

.. code-block:: terminal

    $ composer require symfony/console

Create a console application with ``lint:yaml`` as its only command::

    // lint.php
    use Symfony\Component\Console\Application;
    use Symfony\Component\Yaml\Command\LintCommand;

    (new Application('yaml/lint'))
        ->add(new LintCommand())
        ->getApplication()
        ->setDefaultCommand('lint:yaml', true)
        ->run();

Then, execute the script for validating contents:

.. code-block:: terminal

    # validates a single file
    $ php lint.php path/to/file.yaml

    # or validates multiple files
    $ php lint.php path/to/file1.yaml path/to/file2.yaml

    # or all the files in a directory
    $ php lint.php path/to/directory

    # or all the files in multiple directories
    $ php lint.php path/to/directory1 path/to/directory2

    # or contents passed to STDIN
    $ cat path/to/file.yaml | php lint.php

    # you can also exclude one or more files from linting
    $ php lint.php path/to/directory --exclude=path/to/directory/foo.yaml --exclude=path/to/directory/bar.yaml

.. versionadded:: 5.4

    The ``--exclude`` option was introduced in Symfony 5.4.

The result is written to STDOUT and uses a plain text format by default.
Add the ``--format`` option to get the output in JSON format:

.. code-block:: terminal

    $ php lint.php path/to/file.yaml --format json

.. tip::

    The linting command will also report any deprecations in the checked
    YAML files. This may for example be useful for recognizing deprecations of
    contents of YAML files during automated tests.

.. _yaml-format:

.. index::
    single: Yaml; YAML Format

The YAML Format
---------------

Scalars
~~~~~~~

The syntax for scalars is similar to the PHP syntax.

Strings
.......

Strings in YAML can be wrapped both in single and double quotes. In some cases,
they can also be unquoted:

.. code-block:: yaml

    A string in YAML

    'A single-quoted string in YAML'

    "A double-quoted string in YAML"

Quoted styles are useful when a string starts or end with one or more relevant
spaces, because unquoted strings are trimmed on both end when parsing their
contents. Quotes are required when the string contains special or reserved characters.

When using single-quoted strings, any single quote ``'`` inside its contents
must be doubled to escape it:

.. code-block:: yaml

    'A single quote '' inside a single-quoted string'

Strings containing any of the following characters must be quoted. Although you
can use double quotes, for these characters it is more convenient to use single
quotes, which avoids having to escape any backslash ``\``:

* ``:``, ``{``, ``}``, ``[``, ``]``, ``,``, ``&``, ``*``, ``#``, ``?``, ``|``,
  ``-``, ``<``, ``>``, ``=``, ``!``, ``%``, ``@``, `````

The double-quoted style provides a way to express arbitrary strings, by
using ``\`` to escape characters and sequences. For instance, it is very useful
when you need to embed a ``\n`` or a Unicode character in a string.

.. code-block:: yaml

    "A double-quoted string in YAML\n"

If the string contains any of the following control characters, it must be
escaped with double quotes:

* ``\0``, ``\x01``, ``\x02``, ``\x03``, ``\x04``, ``\x05``, ``\x06``, ``\a``,
  ``\b``, ``\t``, ``\n``, ``\v``, ``\f``, ``\r``, ``\x0e``, ``\x0f``, ``\x10``,
  ``\x11``, ``\x12``, ``\x13``, ``\x14``, ``\x15``, ``\x16``, ``\x17``, ``\x18``,
  ``\x19``, ``\x1a``, ``\e``, ``\x1c``, ``\x1d``, ``\x1e``, ``\x1f``, ``\N``,
  ``\_``, ``\L``, ``\P``

Finally, there are other cases when the strings must be quoted, no matter if
you are using single or double quotes:

* When the string is ``true`` or ``false`` (otherwise, it would be treated as a
  boolean value);
* When the string is ``null`` or ``~`` (otherwise, it would be considered as a
  ``null`` value);
* When the string looks like a number, such as integers (e.g. ``2``, ``14``, etc.),
  floats (e.g. ``2.6``, ``14.9``) and exponential numbers (e.g. ``12e7``, etc.)
  (otherwise, it would be treated as a numeric value);
* When the string looks like a date (e.g. ``2014-12-31``) (otherwise it would be
  automatically converted into a Unix timestamp).

When a string contains line breaks, you can use the literal style, indicated
by the pipe (``|``), to indicate that the string will span several lines. In
literals, newlines are preserved:

.. code-block:: yaml

    |
      \/ /| |\/| |
      / / | |  | |__

Alternatively, strings can be written with the folded style, denoted by ``>``,
where each line break is replaced by a space:

.. code-block:: yaml

    >
      This is a very long sentence
      that spans several lines in the YAML.

    # This will be parsed as follows: (notice the trailing \n)
    # "This is a very long sentence that spans several lines in the YAML.\n"

    >-
      This is a very long sentence
      that spans several lines in the YAML.

    # This will be parsed as follows: (without a trailing \n)
    # "This is a very long sentence that spans several lines in the YAML."

.. note::

    Notice the two spaces before each line in the previous examples. They
    will not appear in the resulting PHP strings.

Numbers
.......

.. code-block:: yaml

    # an integer
    12

.. code-block:: yaml

    # an octal
    0o14

.. deprecated:: 5.1

    In YAML 1.1, octal numbers use the notation ``0...``, whereas in YAML 1.2
    the notation changes to ``0o...``. Symfony 5.1 added support for YAML 1.2
    notation and deprecated support for YAML 1.1 notation.

.. code-block:: yaml

    # an hexadecimal
    0xC

.. code-block:: yaml

    # a float
    13.4

.. code-block:: yaml

    # an exponential number
    1.2e+34

.. code-block:: yaml

    # infinity
    .inf

Nulls
.....

Nulls in YAML can be expressed with ``null`` or ``~``.

Booleans
........

Booleans in YAML are expressed with ``true`` and ``false``.

Dates
.....

YAML uses the `ISO-8601`_ standard to express dates:

.. code-block:: yaml

    2001-12-14T21:59:43.10-05:00

.. code-block:: yaml

    # simple date
    2002-12-14

.. _yaml-format-collections:

Collections
~~~~~~~~~~~

A YAML file is rarely used to describe a simple scalar. Most of the time, it
describes a collection. YAML collections can be a sequence (indexed arrays in PHP)
or a mapping of elements (associative arrays in PHP).

Sequences use a dash followed by a space:

.. code-block:: yaml

    - PHP
    - Perl
    - Python

The previous YAML file is equivalent to the following PHP code::

    ['PHP', 'Perl', 'Python'];

Mappings use a colon followed by a space (``:`` ) to mark each key/value pair:

.. code-block:: yaml

    PHP: 5.2
    MySQL: 5.1
    Apache: 2.2.20

which is equivalent to this PHP code::

    ['PHP' => 5.2, 'MySQL' => 5.1, 'Apache' => '2.2.20'];

.. note::

    In a mapping, a key can be any valid scalar.

The number of spaces between the colon and the value does not matter:

.. code-block:: yaml

    PHP:    5.2
    MySQL:  5.1
    Apache: 2.2.20

YAML uses indentation with one or more spaces to describe nested collections:

.. code-block:: yaml

    'symfony 1.0':
      PHP:    5.0
      Propel: 1.2
    'symfony 1.2':
      PHP:    5.2
      Propel: 1.3

The above YAML is equivalent to the following PHP code::

    [
        'symfony 1.0' => [
            'PHP'    => 5.0,
            'Propel' => 1.2,
        ],
        'symfony 1.2' => [
            'PHP'    => 5.2,
            'Propel' => 1.3,
        ],
    ];

There is one important thing you need to remember when using indentation in a
YAML file: *Indentation must be done with one or more spaces, but never with
tabulators*.

You can nest sequences and mappings as you like:

.. code-block:: yaml

    'Chapter 1':
      - Introduction
      - Event Types
    'Chapter 2':
      - Introduction
      - Helpers

YAML can also use flow styles for collections, using explicit indicators
rather than indentation to denote scope.

A sequence can be written as a comma separated list within square brackets
(``[]``):

.. code-block:: yaml

    [PHP, Perl, Python]

A mapping can be written as a comma separated list of key/values within curly
braces (``{}``):

.. code-block:: yaml

    { PHP: 5.2, MySQL: 5.1, Apache: 2.2.20 }

You can mix and match styles to achieve a better readability:

.. code-block:: yaml

    'Chapter 1': [Introduction, Event Types]
    'Chapter 2': [Introduction, Helpers]

.. code-block:: yaml

    'symfony 1.0': { PHP: 5.0, Propel: 1.2 }
    'symfony 1.2': { PHP: 5.2, Propel: 1.3 }

Comments
~~~~~~~~

Comments can be added in YAML by prefixing them with a hash mark (``#``):

.. code-block:: yaml

    # Comment on a line
    "symfony 1.0": { PHP: 5.0, Propel: 1.2 } # Comment at the end of a line
    "symfony 1.2": { PHP: 5.2, Propel: 1.3 }

.. note::

    Comments are ignored by the YAML parser and do not need to be indented
    according to the current level of nesting in a collection.

Explicit Typing
~~~~~~~~~~~~~~~

The YAML specification defines some tags to set the type of any data explicitly:

.. code-block:: yaml

    data:
        # this value is parsed as a string (it is not transformed into a DateTime)
        start_date: !!str 2002-12-14

        # this value is parsed as a float number (it will be 3.0 instead of 3)
        price: !!float 3

        # this value is parsed as binary data encoded in base64
        picture: !!binary |
            R0lGODlhDAAMAIQAAP//9/X
            17unp5WZmZgAAAOfn515eXv
            Pz7Y6OjuDg4J+fn5OTk6enp
            56enmleECcgggoBADs=

Unsupported YAML Features
~~~~~~~~~~~~~~~~~~~~~~~~~

The following YAML features are not supported by the Symfony Yaml component:

* Multi-documents (``---`` and ``...`` markers);
* Complex mapping keys and complex values starting with ``?``;
* Tagged values as keys;
* The following tags and types: ``!!set``, ``!!omap``, ``!!pairs``, ``!!seq``,
  ``!!bool``, ``!!int``, ``!!merge``, ``!!null``, ``!!timestamp``, ``!!value``, ``!!yaml``;
* Tags (``TAG`` directive; example: ``%TAG ! tag:example.com,2000:app/``)
  and tag references (example: ``!<tag:example.com,2000:app/foo>``);
* Using sequence-like syntax for mapping elements (example: ``{foo, bar}``; use
  ``{foo: ~, bar: ~}`` instead).

.. _`YAML`: https://yaml.org/
.. _`YAML 1.2 version specification`: https://yaml.org/spec/1.2/spec.html
.. _`ISO-8601`: https://www.iso.org/iso-8601-date-and-time-format.html
