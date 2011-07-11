.. index::
   single: YAML
   single: Configuration; YAML

YAML
====

`YAML`_ website is "a human friendly data serialization standard for all
programming languages". YAML is a simple language that describes data. As PHP,
it has a syntax for simple types like strings, booleans, floats, or integers.
But unlike PHP, it makes a difference between arrays (sequences) and hashes
(mappings).

The Symfony2 :namespace:`Symfony\\Component\\Yaml` Component knows how to
parse YAML and dump a PHP array to YAML.

.. note::

    Even if the YAML format can describe complex nested data structure, this
    chapter only describes the minimum set of features needed to use YAML as a
    configuration file format.

Reading YAML Files
------------------

The :method:`Symfony\\Component\\Yaml\\Parser::parse` method parses a YAML
string and converts it to a PHP array::

    use Symfony\Component\Yaml\Parser;

    $yaml = new Parser();
    $value = $yaml->parse(file_get_contents('/path/to/file.yaml'));

If an error occurs during parsing, the parser throws an exception indicating
the error type and the line in the original YAML string where the error
occurred::

    try {
        $value = $yaml->parse(file_get_contents('/path/to/file.yaml'));
    } catch (\InvalidArgumentException $e) {
        // an error occurred during parsing
        echo "Unable to parse the YAML string: ".$e->getMessage();
    }

.. tip::

    As the parser is reentrant, you can use the same parser object to load
    different YAML strings.

When loading a YAML file, it is sometimes better to use the
:method:`Symfony\\Component\\Yaml\\Yaml::parse` wrapper method::

    use Symfony\Component\Yaml\Yaml;

    $loader = Yaml::parse('/path/to/file.yml');

The ``Yaml::parse()`` static method takes a YAML string or a file containing
YAML. Internally, it calls the ``Parser::parse()`` method, but with some added
bonuses:

* It executes the YAML file as if it was a PHP file, so that you can embed
  PHP commands in YAML files;

* When a file cannot be parsed, it automatically adds the file name to the
  error message, simplifying debugging when your application is loading
  several YAML files.

Writing YAML Files
------------------

The :method:`Symfony\\Component\\Yaml\\Dumper::dump` method dumps any PHP array
to its YAML representation::

    use Symfony\Component\Yaml\Dumper;

    $array = array('foo' => 'bar', 'bar' => array('foo' => 'bar', 'bar' => 'baz'));

    $dumper = new Dumper();
    $yaml = $dumper->dump($array);
    file_put_contents('/path/to/file.yaml', $yaml);

.. note::

    There are some limitations: the dumper is not able to dump resources and
    dumping PHP objects is considered an alpha feature.

If you only need to dump one array, you can use the
:method:`Symfony\\Component\\Yaml\\Yaml::dump` static method shortcut::

    $yaml = Yaml::dump($array, $inline);

The YAML format supports the two YAML array representations. By default, the
dumper uses the inline representation:

.. code-block:: yaml

    { foo: bar, bar: { foo: bar, bar: baz } }

But the second argument of the ``dump()`` method customizes the level at which
the output switches from the expanded representation to the inline one::

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

The YAML Syntax
---------------

Strings
~~~~~~~

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

Quoted styles are useful when a string starts or ends with one or more relevant
spaces.

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

    Notice the two spaces before each line in the previous examples. They won't
    appear in the resulting PHP strings.

Numbers
~~~~~~~

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
~~~~~

Nulls in YAML can be expressed with ``null`` or ``~``.

Booleans
~~~~~~~~

Booleans in YAML are expressed with ``true`` and ``false``.

Dates
~~~~~

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

Sequences use a dash followed by a space (``-`` ):

.. code-block:: yaml

    - PHP
    - Perl
    - Python

The previous YAML file is equivalent to the following PHP code::

    array('PHP', 'Perl', 'Python');

Mappings use a colon followed by a space (``:`` ) to mark each key/value pair:

.. code-block:: yaml

    PHP: 5.2
    MySQL: 5.1
    Apache: 2.2.20

which is equivalent to this PHP code::

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

    "symfony 1.4":
        PHP:      5.2
        Doctrine: 1.2
    "Symfony2":
        PHP:      5.3
        Doctrine: 2.0

The following YAML is equivalent to the following PHP code::

    array(
        'symfony 1.4' => array(
            'PHP'      => 5.2,
            'Doctrine' => 1.2,
        ),
        'Symfony2' => array(
            'PHP'      => 5.3,
            'Doctrine' => 2.0,
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
braces (``{}``):

.. code-block:: yaml

    { PHP: 5.2, MySQL: 5.1, Apache: 2.2.20 }

You can mix and match styles to achieve a better readability:

.. code-block:: yaml

    'Chapter 1': [Introduction, Event Types]
    'Chapter 2': [Introduction, Helpers]

.. code-block:: yaml

    "symfony 1.4": { PHP: 5.2, Doctrine: 1.2 }
    "Symfony2":    { PHP: 5.3, Doctrine: 2.0 }

Comments
~~~~~~~~

Comments can be added in YAML by prefixing them with a hash mark (``#``):

.. code-block:: yaml

    # Comment on a line
    "Symfony2": { PHP: 5.3, Doctrine: 2.0 } # Comment at the end of a line

.. note::

    Comments are simply ignored by the YAML parser and do not need to be
    indented according to the current level of nesting in a collection.

Dynamic YAML files
~~~~~~~~~~~~~~~~~~

In Symfony2, a YAML file can contain PHP code that is evaluated just before the
parsing occurs:

.. code-block:: yaml

    1.0:
        version: <?php echo file_get_contents('1.0/VERSION')."\n" ?>
    1.1:
        version: "<?php echo file_get_contents('1.1/VERSION') ?>"

Be careful to not mess up with the indentation. Keep in mind the following
simple tips when adding PHP code to a YAML file:

* The ``<?php ?>`` statements must always start the line or be embedded in a
  value.

* If a ``<?php ?>`` statement ends a line, you need to explicitly output a new
  line ("\n").

.. _YAML: http://yaml.org/
