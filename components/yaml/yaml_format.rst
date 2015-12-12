.. index::
    single: Yaml; YAML Format

The YAML Format
===============

According to the official `YAML`_ website, YAML is "a human friendly data
serialization standard for all programming languages".

Even if the YAML format can describe complex nested data structure, this
chapter only describes the minimum set of features needed to use YAML as a
configuration file format.

YAML is a simple language that describes data. As PHP, it has a syntax for
simple types like strings, booleans, floats, or integers. But unlike PHP, it
makes a difference between arrays (sequences) and hashes (mappings).

Scalars
-------

The syntax for scalars is similar to the PHP syntax.

Strings
~~~~~~~

Strings in YAML can be wrapped both in single and double quotes. In some cases,
they can also be unquoted:

.. code-block:: yaml

    A string in YAML

    'A singled-quoted string in YAML'

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
  ``-``, ``<``, ``>``, ``=``, ``!``, ``%``, ``@``, ``\```

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
you're using single or double quotes:

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
      that spans several lines in the YAML
      but which will be rendered as a string
      without carriage returns.

.. note::

    Notice the two spaces before each line in the previous examples. They
    won't appear in the resulting PHP strings.

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
-----------

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

Mappings use a colon followed by a space (``:`` ) to mark each key/value pair:

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

    'symfony 1.0':
      PHP:    5.0
      Propel: 1.2
    'symfony 1.2':
      PHP:    5.2
      Propel: 1.3

The above YAML is equivalent to the following PHP code:

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
--------

Comments can be added in YAML by prefixing them with a hash mark (``#``):

.. code-block:: yaml

    # Comment on a line
    "symfony 1.0": { PHP: 5.0, Propel: 1.2 } # Comment at the end of a line
    "symfony 1.2": { PHP: 5.2, Propel: 1.3 }

.. note::

    Comments are simply ignored by the YAML parser and do not need to be
    indented according to the current level of nesting in a collection.

.. _YAML: http://yaml.org/
