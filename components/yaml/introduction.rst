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

.. tip::

    Learn more about the Yaml component in the
    :doc:`/components/yaml/yaml_format` article.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Yaml);
* :doc:`Install it via Composer</components/using_components>` (``symfony/yaml`` on `Packagist`_).

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

It may also be convenient to use the
:method:`Symfony\\Component\\Yaml\\Yaml::parse` wrapper method:

.. code-block:: php

    use Symfony\Component\Yaml\Yaml;

    $yaml = Yaml::parse(file_get_contents('/path/to/file.yml'));

The :method:`Symfony\\Component\\Yaml\\Yaml::parse` static method takes a YAML
string or a file containing YAML. Internally, it calls the
:method:`Symfony\\Component\\Yaml\\Parser::parse` method, but enhances the
error if something goes wrong by adding the filename to the message.

.. note::

    Although it is currently possible to pass the
    :method:`Symfony\\Component\\Yaml\\Yaml::parse` static method a
    filename, this functionality is deprecated in Symfony 2.3, and will be
    removed in Symfony 3.0. Because it is possible to pass a filename, if
    you use this method, you must validate the input first.

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

    $array = array(
        'foo' => 'bar',
        'bar' => array('foo' => 'bar', 'bar' => 'baz'),
    );

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

.. _YAML: http://yaml.org/
.. _Packagist: https://packagist.org/packages/symfony/yaml
