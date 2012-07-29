.. index::
   single: Yaml
   single: Components; Yaml

The YAML Component
==================

    The YAML Component loads and dumps YAML files.

What is it?
-----------

The Symfony2 YAML Component parses YAML strings to convert them to PHP arrays.
It is also able to convert PHP arrays to YAML strings.

`YAML`_, *YAML Ain't Markup Language*, is a human friendly data serialization
standard for all programming languages. YAML is a great format for your
configuration files. YAML files are as expressive as XML files and as readable
as INI files.

The Symfony2 YAML Component implements the YAML 1.2 version of the
specification.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Yaml);
* Install it via PEAR ( `pear.symfony.com/Yaml`);
* Install it via Composer (`symfony/yaml` on Packagist).

Why?
----

Fast
~~~~

One of the goal of Symfony YAML is to find the right balance between speed and
features. It supports just the needed feature to handle configuration files.

Real Parser
~~~~~~~~~~~

It sports a real parser and is able to parse a large subset of the YAML
specification, for all your configuration needs. It also means that the parser
is pretty robust, easy to understand, and simple enough to extend.

Clear error messages
~~~~~~~~~~~~~~~~~~~~

Whenever you have a syntax problem with your YAML files, the library outputs a
helpful message with the filename and the line number where the problem
occurred. It eases the debugging a lot.

Dump support
~~~~~~~~~~~~

It is also able to dump PHP arrays to YAML with object support, and inline
level configuration for pretty outputs.

Types Support
~~~~~~~~~~~~~

It supports most of the YAML built-in types like dates, integers, octals,
booleans, and much more...

Full merge key support
~~~~~~~~~~~~~~~~~~~~~~

Full support for references, aliases, and full merge key. Don't repeat
yourself by referencing common configuration bits.

Using the Symfony2 YAML Component
---------------------------------

The Symfony2 YAML Component is very simple and consists of two main classes:
one parses YAML strings (:class:`Symfony\\Component\\Yaml\\Parser`), and the
other dumps a PHP array to a YAML string
(:class:`Symfony\\Component\\Yaml\\Dumper`).

On top of these two classes, the :class:`Symfony\\Component\\Yaml\\Yaml` class
acts as a thin wrapper that simplifies common uses.

Reading YAML Files
~~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Component\\Yaml\\Parser::parse` method parses a YAML
string and converts it to a PHP array:

.. code-block:: php

    use Symfony\Component\Yaml\Parser;

    $yaml = new Parser();

    $value = $yaml->parse(file_get_contents('/path/to/file.yml'));

If an error occurs during parsing, the parser throws a
:class:`Symfony\\Component\\Yaml\\Exception\\ParseException` exception
indicating the error type and the line in the original YAML string where the
error occurred:

.. code-block:: php

    use Symfony\Component\Yaml\Exception\ParseException;

    try {
        $value = $yaml->parse(file_get_contents('/path/to/file.yml'));
    } catch (ParseException $e) {
        printf("Unable to parse the YAML string: %s", $e->getMessage());
    }

.. tip::

    As the parser is re-entrant, you can use the same parser object to load
    different YAML strings.

When loading a YAML file, it is sometimes better to use the
:method:`Symfony\\Component\\Yaml\\Yaml::parse` wrapper method:

.. code-block:: php

    use Symfony\Component\Yaml\Yaml;

    $loader = Yaml::parse('/path/to/file.yml');

The :method:`Symfony\\Component\\Yaml\\Yaml::parse` static method takes a YAML
string or a file containing YAML. Internally, it calls the
:method:`Symfony\\Component\\Yaml\\Parser::parse` method, but enhances the
error if something goes wrong by adding the filename to the message.

Executing PHP Inside YAML Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.1
    The ``Yaml::enablePhpParsing()`` method is new to Symfony 2.1. Prior to 2.1,
    PHP was *always* executed when calling the ``parse()`` function.

By default, if you include PHP inside a YAML file, it will not be parsed.
If you do want PHP to be parsed, you must call ``Yaml::enablePhpParsing()``
before parsing the file to activate this mode. If you only want to allow
PHP code for a single YAML file, be sure to disable PHP parsing after parsing
the single file by calling ``Yaml::$enablePhpParsing = false;`` (``$enablePhpParsing``
is a public property).

Writing YAML Files
~~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Component\\Yaml\\Dumper::dump` method dumps any PHP
array to its YAML representation:

.. code-block:: php

    use Symfony\Component\Yaml\Dumper;

    $array = array('foo' => 'bar', 'bar' => array('foo' => 'bar', 'bar' => 'baz'));

    $dumper = new Dumper();

    $yaml = $dumper->dump($array);

    file_put_contents('/path/to/file.yml', $yaml);

.. note::

    Of course, the Symfony2 YAML dumper is not able to dump resources. Also,
    even if the dumper is able to dump PHP objects, it is considered to be a
    not supported feature.

If an error occurs during the dump, the parser throws a
:class:`Symfony\\Component\\Yaml\\Exception\\DumpException` exception.

If you only need to dump one array, you can use the
:method:`Symfony\\Component\\Yaml\\Yaml::dump` static method shortcut:

.. code-block:: php

    use Symfony\Component\Yaml\Yaml;

    $yaml = Yaml::dump($array, $inline);

The YAML format supports two kind of representation for arrays, the expanded
one, and the inline one. By default, the dumper uses the inline
representation:

.. code-block:: yaml

    { foo: bar, bar: { foo: bar, bar: baz } }

The second argument of the :method:`Symfony\\Component\\Yaml\\Dumper::dump`
method customizes the level at which the output switches from the expanded
representation to the inline one:

.. code-block:: php

    echo $dumper->dump($array, 1);

.. code-block:: yaml

    foo: bar
    bar: { foo: bar, bar: baz }

.. code-block:: php

    echo $dumper->dump($array, 2);

.. code-block:: yaml

    foo: bar
    bar:
        foo: bar
        bar: baz

The YAML Format
---------------

According to the official `YAML`_ website, YAML is "a human friendly data
serialization standard for all programming languages".

Even if the YAML format can describe complex nested data structure, this
chapter only describes the minimum set of features needed to use YAML as a
configuration file format.

YAML is a simple language that describes data. As PHP, it has a syntax for
simple types like strings, booleans, floats, or integers. But unlike PHP, it
makes a difference between arrays (sequences) and hashes (mappings).

Scalars
~~~~~~~

The syntax for scalars is similar to the PHP syntax.

Strings
.......

.. code-block:: yaml

    A string in YAML

.. code-block:: yaml

    'A singled-quoted string in YAML'

.. tip::

    In a single quoted string, a single quote ``'`` must be doubled:

    .. code-block:: yaml

        'A single quote '' in a single-quoted string'

.. code-block:: yaml

    "A double-quoted string in YAML\n"

Quoted styles are useful when a string starts or ends with one or more
relevant spaces.

.. tip::

    The double-quoted style provides a way to express arbitrary strings, by
    using ``\`` escape sequences. It is very useful when you need to embed a
    ``\n`` or a unicode character in a string.

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
      that spans several lines in the YAML
      but which will be rendered as a string
      without carriage returns.

.. note::

    Notice the two spaces before each line in the previous examples. They
    won't appear in the resulting PHP strings.

Numbers
.......

.. code-block:: yaml

    # an integer
    12

.. code-block:: yaml

    # an octal
    014

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

YAML uses the ISO-8601 standard to express dates:

.. code-block:: yaml

    2001-12-14t21:59:43.10-05:00

.. code-block:: yaml

    # simple date
    2002-12-14

Collections
~~~~~~~~~~~

A YAML file is rarely used to describe a simple scalar. Most of the time, it
describes a collection. A collection can be a sequence or a mapping of
elements. Both sequences and mappings are converted to PHP arrays.

Sequences use a dash followed by a space:

.. code-block:: yaml

    - PHP
    - Perl
    - Python

The previous YAML file is equivalent to the following PHP code:

.. code-block:: php

    array('PHP', 'Perl', 'Python');

Mappings use a colon followed by a space (``: ``) to mark each key/value pair:

.. code-block:: yaml

    PHP: 5.2
    MySQL: 5.1
    Apache: 2.2.20

which is equivalent to this PHP code:

.. code-block:: php

    array('PHP' => 5.2, 'MySQL' => 5.1, 'Apache' => '2.2.20');

.. note::

    In a mapping, a key can be any valid scalar.

The number of spaces between the colon and the value does not matter:

.. code-block:: yaml

    PHP:    5.2
    MySQL:  5.1
    Apache: 2.2.20

YAML uses indentation with one or more spaces to describe nested collections:

.. code-block:: yaml

    "symfony 1.0":
      PHP:    5.0
      Propel: 1.2
    "symfony 1.2":
      PHP:    5.2
      Propel: 1.3

The following YAML is equivalent to the following PHP code:

.. code-block:: php

    array(
      'symfony 1.0' => array(
        'PHP'    => 5.0,
        'Propel' => 1.2,
      ),
      'symfony 1.2' => array(
        'PHP'    => 5.2,
        'Propel' => 1.3,
      ),
    );

There is one important thing you need to remember when using indentation in a
YAML file: *Indentation must be done with one or more spaces, but never with
tabulations*.

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
braces (`{}`):

.. code-block:: yaml

    { PHP: 5.2, MySQL: 5.1, Apache: 2.2.20 }

You can mix and match styles to achieve a better readability:

.. code-block:: yaml

    'Chapter 1': [Introduction, Event Types]
    'Chapter 2': [Introduction, Helpers]

.. code-block:: yaml

    "symfony 1.0": { PHP: 5.0, Propel: 1.2 }
    "symfony 1.2": { PHP: 5.2, Propel: 1.3 }

Comments
~~~~~~~~

Comments can be added in YAML by prefixing them with a hash mark (``#``):

.. code-block:: yaml

    # Comment on a line
    "symfony 1.0": { PHP: 5.0, Propel: 1.2 } # Comment at the end of a line
    "symfony 1.2": { PHP: 5.2, Propel: 1.3 }

.. note::

    Comments are simply ignored by the YAML parser and do not need to be
    indented according to the current level of nesting in a collection.

.. _YAML: http://yaml.org/
