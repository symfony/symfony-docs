YAML
====

[YAML][1] website is "a human friendly data serialization standard for all
programming languages". YAML is a simple language that describes data. As PHP,
it has a syntax for simple types like strings, booleans, floats, or integers.
But unlike PHP, it makes a difference between arrays (sequences) and hashes
(mappings).

The Symfony2 YAML Component knows how to parse YAML and dump a PHP array to
YAML.

>**NOTE**
>Even if the YAML format can describe complex nested data structure, this guide
>only describes the minimum set of features needed to use YAML as a
>configuration file format.

Reading YAML Files
------------------

The `Parser::parse()` method parses a YAML string and converts it to a PHP
array:

    [php]
    use Symfony\Components\Yaml\Parser;

    $yaml = new Parser();
    $value = $yaml->parse(file_get_contents('/path/to/file.yaml'));

If an error occurs during parsing, the parser throws an exception indicating
the error type and the line in the original YAML string where the error
occurred:

    [php]
    try
    {
      $value = $yaml->parse(file_get_contents('/path/to/file.yaml'));
    }
    catch (\InvalidArgumentException $e)
    {
      // an error occurred during parsing
      echo "Unable to parse the YAML string: ".$e->getMessage();
    }

>**TIP**
>As the parser is reentrant, you can use the same parser object to load
>different YAML strings.

When loading a YAML file, it is sometimes better to use the `Yaml::load()`
wrapper method:

    [php]
    use Symfony\Components\Yaml\Yaml;

    $loader = Yaml::load('/path/to/file.yml');

The `Yaml::load()` static method takes a YAML string or a file containing
YAML. Internally, it calls the `Parser::parse()` method, but with some added
bonuses:

  * It executes the YAML file as if it was a PHP file, so that you can embed
    PHP commands in YAML files;

  * When a file cannot be parsed, it automatically adds the file name to the
    error message, simplifying debugging when your application is loading
    several YAML files.

Writing YAML Files
------------------

The `Dumper::dump()` method dumps any PHP array to its YAML representation:

    [php]
    use Symfony\Components\Yaml\Dumper;

    $array = array('foo' => 'bar', 'bar' => array('foo' => 'bar', 'bar' => 'baz'));

    $dumper = new Dumper();
    $yaml = $dumper->dump($array);
    file_put_contents('/path/to/file.yaml', $yaml);

>**NOTE**
>There are some limitations: the dumper is not able to dump resources and
>dumping PHP objects is considered an alpha feature.

If you only need to dump one array, you can use the `Yaml::dump()` static
method shortcut:

    [php]
    $yaml = Yaml::dump($array, $inline);

The YAML format supports the two YAML array representations. By default, the
dumper uses the inline representation:

    [yml]
    { foo: bar, bar: { foo: bar, bar: baz } }

But the second argument of the `dump()` method customizes the level at which
the output switches from the expanded representation to the inline one:

    [php]
    echo $dumper->dump($array, 1);

-

    [yml]
    foo: bar
    bar: { foo: bar, bar: baz }

-

    [php]
    echo $dumper->dump($array, 2);

-

    [yml]
    foo: bar
    bar:
      foo: bar
      bar: baz

The YAML Syntax
---------------

### Strings

    [yml]
    A string in YAML

-

    [yml]
    'A singled-quoted string in YAML'

>**TIP**
>In a single quoted string, a single quote `'` must be doubled:
>
>     [yml]
>     'A single quote '' in a single-quoted string'

    [yml]
    "A double-quoted string in YAML\n"

Quoted styles are useful when a string starts or ends with one or more
relevant spaces.

>**TIP**
>The double-quoted style provides a way to express arbitrary strings, by
>using `\` escape sequences. It is very useful when you need to embed a
>`\n` or a unicode character in a string.

When a string contains line breaks, you can use the literal style, indicated
by the pipe (`|`), to indicate that the string will span several lines. In
literals, newlines are preserved:

    [yml]
    |
      \/ /| |\/| |
      / / | |  | |__

Alternatively, strings can be written with the folded style, denoted by `>`,
where each line break is replaced by a space:

    [yml]
    >
      This is a very long sentence
      that spans several lines in the YAML
      but which will be rendered as a string
      without carriage returns.

>**NOTE**
>Notice the two spaces before each line in the previous examples. They
>won't appear in the resulting PHP strings.

### Numbers

    [yml]
    # an integer
    12

-

    [yml]
    # an octal
    014

-

    [yml]
    # an hexadecimal
    0xC

-

    [yml]
    # a float
    13.4

-

    [yml]
    # an exponential number
    1.2e+34

-

    [yml]
    # infinity
    .inf

### Nulls

Nulls in YAML can be expressed with `null` or `~`.

### Booleans

Booleans in YAML are expressed with `true` and `false`.

### Dates

YAML uses the ISO-8601 standard to express dates:

    [yml]
    2001-12-14t21:59:43.10-05:00

-

    [yml]
    # simple date
    2002-12-14

### Collections

A YAML file is rarely used to describe a simple scalar. Most of the time, it
describes a collection. A collection can be a sequence or a mapping of
elements. Both sequences and mappings are converted to PHP arrays.

Sequences use a dash followed by a space (`- `):

    [yml]
    - PHP
    - Perl
    - Python

The previous YAML file is equivalent to the following PHP code:

    [php]
    array('PHP', 'Perl', 'Python');

Mappings use a colon followed by a space (`: `) to mark each key/value pair:

    [yml]
    PHP: 5.2
    MySQL: 5.1
    Apache: 2.2.20

which is equivalent to this PHP code:

    [php]
    array('PHP' => 5.2, 'MySQL' => 5.1, 'Apache' => '2.2.20');

>**NOTE**
>In a mapping, a key can be any valid scalar.

The number of spaces between the colon and the value does not matter:

    [yml]
    PHP:    5.2
    MySQL:  5.1
    Apache: 2.2.20

YAML uses indentation with one or more spaces to describe nested collections:

    [yml]
    "symfony 1.4":
      PHP:      5.2
      Doctrine: 1.2
    "Symfony2":
      PHP:      5.3
      Doctrine: 2.0

The following YAML is equivalent to the following PHP code:

    [php]
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

    [yml]
    'Chapter 1':
      - Introduction
      - Event Types
    'Chapter 2':
      - Introduction
      - Helpers

YAML can also use flow styles for collections, using explicit indicators
rather than indentation to denote scope.

A sequence can be written as a comma separated list within square brackets
(`[]`):

    [yml]
    [PHP, Perl, Python]

A mapping can be written as a comma separated list of key/values within curly
braces (`{}`):

    [yml]
    { PHP: 5.2, MySQL: 5.1, Apache: 2.2.20 }

You can mix and match styles to achieve a better readability:

    [yml]
    'Chapter 1': [Introduction, Event Types]
    'Chapter 2': [Introduction, Helpers]

-

    [yml]
    "symfony 1.4": { PHP: 5.2, Doctrine: 1.2 }
    "Symfony2":    { PHP: 5.3, Doctrine: 2.0 }

### Comments

Comments can be added in YAML by prefixing them with a hash mark (`#`):

    [yml]
    # Comment on a line
    "Symfony2": { PHP: 5.3, Doctrine: 2.0 } # Comment at the end of a line

>**NOTE**
>Comments are simply ignored by the YAML parser and do not need to be
>indented according to the current level of nesting in a collection.

### Dynamic YAML files

In Symfony, a YAML file can contain PHP code that is evaluated just before the
parsing occurs:

    [php]
    1.0:
      version: <?php echo file_get_contents('1.0/VERSION')."\n" ?>
    1.1:
      version: "<?php echo file_get_contents('1.1/VERSION') ?>"

Be careful to not mess up with the indentation. Keep in mind the following
simple tips when adding PHP code to a YAML file:

 * The `<?php ?>` statements must always start the line or be embedded in a
   value.

 * If a `<?php ?>` statement ends a line, you need to explicitly output a new
   line ("\n").

[1]: http://yaml.org/
