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

    Learn more about the Yaml component in the
    :doc:`/components/yaml/yaml_format` article.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/yaml`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/yaml).

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

It supports most of the YAML built-in types like dates, integers, octals,
booleans, and much more...

Full Merge Key Support
~~~~~~~~~~~~~~~~~~~~~~

Full support for references, aliases, and full merge key. Don't repeat
yourself by referencing common configuration bits.

.. _using-the-symfony2-yaml-component:

Using the Symfony YAML Component
--------------------------------

The Symfony Yaml component is very simple and consists of two main classes:
one parses YAML strings (:class:`Symfony\\Component\\Yaml\\Parser`), and the
other dumps a PHP array to a YAML string
(:class:`Symfony\\Component\\Yaml\\Dumper`).

On top of these two classes, the :class:`Symfony\\Component\\Yaml\\Yaml` class
acts as a thin wrapper that simplifies common uses.

Reading YAML Files
~~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Component\\Yaml\\Yaml::parse` method parses a YAML
string and converts it to a PHP array:

.. code-block:: php

    use Symfony\Component\Yaml\Yaml;

    $value = Yaml::parse(file_get_contents('/path/to/file.yml'));

.. caution::

    Because it is currently possible to pass a filename to this method, you
    must validate the input first. Passing a filename is deprecated in
    Symfony 2.2, and was removed in Symfony 3.0.

If an error occurs during parsing, the parser throws a
:class:`Symfony\\Component\\Yaml\\Exception\\ParseException` exception
indicating the error type and the line in the original YAML string where the
error occurred:

.. code-block:: php

    use Symfony\Component\Yaml\Exception\ParseException;

    try {
        $value = Yaml::parse(file_get_contents('/path/to/file.yml'));
    } catch (ParseException $e) {
        printf("Unable to parse the YAML string: %s", $e->getMessage());
    }

.. _components-yaml-dump:

Writing YAML Files
~~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Component\\Yaml\\Yaml::dump` method dumps any PHP
array to its YAML representation:

.. code-block:: php

    use Symfony\Component\Yaml\Yaml;

    $array = array(
        'foo' => 'bar',
        'bar' => array('foo' => 'bar', 'bar' => 'baz'),
    );

    $yaml = Yaml::dump($array);

    file_put_contents('/path/to/file.yml', $yaml);

If an error occurs during the dump, the parser throws a
:class:`Symfony\\Component\\Yaml\\Exception\\DumpException` exception.

Array Expansion and Inlining
............................

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
representation to the inline one:

.. code-block:: php

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

By default the YAML component will use 4 spaces for indentation. This can be
changed using the third argument as follows::

    // use 8 spaces for indentation
    echo Yaml::dump($array, 2, 8);

.. code-block:: yaml

    foo: bar
    bar:
            foo: bar
            bar: baz

Advanced Usage: Flags
---------------------

.. versionadded:: 3.1
    Flags were introduced in Symfony 3.1 and replaced the earlier boolean
    arguments.

.. _objects-for-mappings:

Object Parsing and Dumping
~~~~~~~~~~~~~~~~~~~~~~~~~~

You can dump objects by using the ``DUMP_OBJECT`` flag::

    $object = new \stdClass();
    $object->foo = 'bar';

    $dumped = Yaml::dump($object, 2, 4, Yaml::DUMP_OBJECT);
    // !php/object:O:8:"stdClass":1:{s:5:"foo";s:7:"bar";}

And parse them by using the ``PARSE_OBJECT`` flag::

    $parsed = Yaml::parse($dumped, Yaml::PARSE_OBJECT);
    var_dump(is_object($parsed)); // true
    echo $parsed->foo; // bar

The YAML component uses PHP's ``serialize()`` method to generate a string
representation of the object.

.. caution::

    Object serialization is specific to this implementation, other PHP YAML
    parsers will likely not recognize the ``php/object`` tag and non-PHP
    implementations certainly won't - use with discretion!

.. _invalid-types-and-object-serialization:

Handling Invalid Types
~~~~~~~~~~~~~~~~~~~~~~

By default the parser will encode invalid types as ``null``. You can make the
parser throw exceptions by using the ``PARSE_EXCEPTION_ON_INVALID_TYPE``
flag::

    $yaml = '!php/object:O:8:"stdClass":1:{s:5:"foo";s:7:"bar";}';
    Yaml::parse($yaml, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE); // throws an exception

Similarly you can use ``DUMP_EXCEPTION_ON_INVALID_TYPE`` when dumping::

    $data = new \stdClass(); // by default objects are invalid.
    Yaml::dump($data, Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE); // throws an exception

    echo $yaml; // { foo: bar }

Date Handling
~~~~~~~~~~~~~

By default the YAML parser will convert unquoted strings which look like a
date or a date-time into a Unix timestamp; for example ``2016-05-27`` or
``2016-05-27T02:59:43.1Z`` (ISO-8601_)::

    Yaml::parse('2016-05-27'); // 1464307200

You can make it convert to a ``DateTime`` instance by using the ``PARSE_DATETIME``
flag::

    $date = Yaml::parse('2016-05-27', Yaml::PARSE_DATETIME);
    var_dump(get_class($date)); // DateTime

Dumping Multi-line Literal Blocks
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In YAML multiple lines can be represented as literal blocks, by default the
dumper will encode multiple lines as an inline string::

    $string = array("string" => "Multiple\nLine\nString");
    $yaml = Yaml::dump($string);
    echo $yaml; // string: "Multiple\nLine\nString"

You can make it use a literal block with the ``DUMP_MULTI_LINE_LITERAL_BLOCK``
flag::

    $string = array("string" => "Multiple\nLine\nString");
    $yaml = Yaml::dump($string, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    echo $yaml;
    //  string: |
    //       Multiple
    //       Line
    //       String

Syntax Validation
~~~~~~~~~~~~~~~~~

The syntax of YAML contents can be validated through the CLI using the
:class:`Symfony\\Component\\Yaml\\Command\\LintCommand` command.

First, install the Console component:

.. code-block:: bash

    $ composer require symfony/console

Create a console application with ``lint:yaml`` as its only command::

.. code-block:: php

    // lint.php

    use Symfony\Component\Console\Application;
    use Symfony\Component\Yaml\Command\LintCommand;

    (new Application('yaml/lint'))
        ->add(new LintCommand())
        ->getApplication()
        ->setDefaultCommand('lint:yaml', true)
        ->run();

Then, execute the script for validating contents:

.. code-block:: bash

    # validates a single file
    $ php lint.php path/to/file.yml

    # or all the files in a directory
    $ php lint.php path/to/directory

    # or contents passed to STDIN
    $ cat path/to/file.yml | php lint.php

The result is written to STDOUT and uses a plain text format by default.
Add the ``--format`` option to get the output in JSON format:

.. code-block:: bash

    $ php lint.php path/to/file.yml --format json

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    yaml/*

.. _YAML: http://yaml.org/
.. _Packagist: https://packagist.org/packages/symfony/yaml
.. _`YAML 1.2 version specification`: http://yaml.org/spec/1.2/spec.html
.. _ISO-8601: http://www.iso.org/iso/iso8601
