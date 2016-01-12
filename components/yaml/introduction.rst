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

The :method:`Symfony\\Component\\Yaml\\Yaml::parse` static method takes a YAML string.
Internally, it constructs a :class:`Symfony\\Component\\Yaml\\Parser` object and calls
the :method:`Symfony\\Component\\Yaml\\Parser::parse` method.

.. _components-yaml-dump:

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

    Of course, the Symfony Yaml dumper is not able to dump resources. Also,
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
.. _`YAML 1.2 version specification`: http://yaml.org/spec/1.2/spec.html
