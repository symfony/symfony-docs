.. index::
   single: Config; Defining and processing configuration values

Defining and processing configuration values
============================================

Validating configuration values
-------------------------------

After loading configuration values from all kinds of resources, the values
and their structure can be validated using the "Definition" part of the Config
Component. Configuration values are usually expected to show some kind of
hierarchy. Also, values should be of a certain type, be restricted in number
or be one of a given set of values. For example, the following configuration
(in Yaml) shows a clear hierarchy and some validation rules that should be
applied to it (like: "the value for ``auto_connect`` must be a boolean value"):

.. code-block:: yaml

    auto_connect: true
    default_connection: mysql
    connections:
        mysql:
            host: localhost
            driver: mysql
            username: user
            password: pass
        sqlite:
            host: localhost
            driver: sqlite
            memory: true
            username: user
            password: pass

When loading multiple configuration files, it should be possible to merge
and overwrite some values. Other values should not be merged and stay as
they are when first encountered. Also, some keys are only available when
another key has a specific value (in the sample configuration above: the
``memory`` key only makes sense when the ``driver`` is ``sqlite``).

Defining a hierarchy of configuration values using the TreeBuilder
------------------------------------------------------------------

All the rules concerning configuration values can be defined using the
:class:`Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder`.

A :class:`Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder` instance
should be returned from a custom ``Configuration`` class which implements the
:class:`Symfony\\Component\\Config\\Definition\\ConfigurationInterface`::

    namespace Acme\DatabaseConfiguration;

    use Symfony\Component\Config\Definition\ConfigurationInterface;
    use Symfony\Component\Config\Definition\Builder\TreeBuilder;

    class DatabaseConfiguration implements ConfigurationInterface
    {
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('database');

            // ... add node definitions to the root of the tree

            return $treeBuilder;
        }
    }

Adding node definitions to the tree
-----------------------------------

Variable nodes
~~~~~~~~~~~~~~

A tree contains node definitions which can be laid out in a semantic way.
This means, using indentation and the fluent notation, it is possible to
reflect the real structure of the configuration values::

    $rootNode
        ->children()
            ->booleanNode('auto_connect')
                ->defaultTrue()
            ->end()
            ->scalarNode('default_connection')
                ->defaultValue('default')
            ->end()
        ->end()
    ;

The root node itself is an array node, and has children, like the boolean
node ``auto_connect`` and the scalar node ``default_connection``. In general:
after defining a node, a call to ``end()`` takes you one step up in the hierarchy.

Node type
~~~~~~~~~

It is possible to validate the type of a provided value by using the appropriate
node definition. Node type are available for:

* scalar
* boolean
* integer (new in 2.2)
* float (new in 2.2)
* enum (new in 2.1)
* array
* variable (no validation)

and are created with ``node($name, $type)`` or their associated shortcut
``xxxxNode($name)`` method.

Numeric node constraints
~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.2
    The numeric (float and integer) nodes are new in 2.2

Numeric nodes (float and integer) provide two extra constraints -
:method:`Symfony\\Component\\Config\\Definition\\Builder::min` and
:method:`Symfony\\Component\\Config\\Definition\\Builder::max` -
allowing to validate the value::

    $rootNode
        ->children()
            ->integerNode('positive_value')
                ->min(0)
            ->end()
            ->floatNode('big_value')
                ->max(5E45)
            ->end()
            ->integerNode('value_inside_a_range')
                ->min(-50)->max(50)
            ->end()
        ->end()
    ;

Enum nodes
~~~~~~~~~~

.. versionadded:: 2.1
    The enum node is new in Symfony 2.1

Enum nodes provide a constraint to match the given input against a set of
values::

    $rootNode
        ->children()
            ->enumNode('gender')
                ->values(array('male', 'female'))
            ->end()
        ->end()
    ;

This will restrict the ``gender`` option to be either ``male`` or ``female``.

Array nodes
~~~~~~~~~~~

It is possible to add a deeper level to the hierarchy, by adding an array
node. The array node itself, may have a pre-defined set of variable nodes::

    $rootNode
        ->children()
            ->arrayNode('connection')
                ->children()
                    ->scalarNode('driver')->end()
                    ->scalarNode('host')->end()
                    ->scalarNode('username')->end()
                    ->scalarNode('password')->end()
                ->end()
            ->end()
        ->end()
    ;

Or you may define a prototype for each node inside an array node::

    $rootNode
        ->children()
            ->arrayNode('connections')
                ->prototype('array')
                ->children()
                    ->scalarNode('driver')->end()
                    ->scalarNode('host')->end()
                    ->scalarNode('username')->end()
                    ->scalarNode('password')->end()
                ->end()
            ->end()
        ->end()
    ;

A prototype can be used to add a definition which may be repeated many times
inside the current node. According to the prototype definition in the example
above, it is possible to have multiple connection arrays (containing a ``driver``,
``host``, etc.).

Array node options
~~~~~~~~~~~~~~~~~~

Before defining the children of an array node, you can provide options like:

``useAttributeAsKey()``
    Provide the name of a child node, whose value should be used as the key in the resulting array.
``requiresAtLeastOneElement()``
    There should be at least one element in the array (works only when ``isRequired()`` is also
    called).
``addDefaultsIfNotSet()``
    If any child nodes have default values, use them if explicit values haven't been provided.

An example of this::

    $rootNode
        ->children()
            ->arrayNode('parameters')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('value')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ;

In YAML, the configuration might look like this:

.. code-block:: yaml

    database:
        parameters:
            param1: { value: param1val }

In XML, each ``parameters`` node would have a ``name`` attribute (along with
``value``), which would be removed and used as the key for that element in
the final array. The ``useAttributeAsKey`` is useful for normalizing how
arrays are specified between different formats like XML and YAML.

Default and required values
---------------------------

For all node types, it is possible to define default values and replacement
values in case a node
has a certain value:

``defaultValue()``
    Set a default value
``isRequired()``
    Must be defined (but may be empty)
``cannotBeEmpty()``
    May not contain an empty value
``default*()``
    (``null``, ``true``, ``false``), shortcut for ``defaultValue()``
``treat*Like()``
    (``null``, ``true``, ``false``), provide a replacement value in case the value is ``*.``

.. code-block:: php

    $rootNode
        ->children()
            ->arrayNode('connection')
                ->children()
                    ->scalarNode('driver')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('host')
                        ->defaultValue('localhost')
                    ->end()
                    ->scalarNode('username')->end()
                    ->scalarNode('password')->end()
                    ->booleanNode('memory')
                        ->defaultFalse()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('settings')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('name')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->defaultValue('value')
                    ->end()
                ->end()
            ->end()
        ->end()
    ;

Optional Sections
-----------------

.. versionadded:: 2.2
    The ``canBeEnabled`` and ``canBeDisabled`` methods are new in Symfony 2.2

If you have entire sections which are optional and can be enabled/disabled,
you can take advantage of the shortcut
:method:`Symfony\\Component\\Config\\Definition\\Builder\\ArrayNodeDefinition::canBeEnabled` and
:method:`Symfony\\Component\\Config\\Definition\\Builder\\ArrayNodeDefinition::canBeDisabled` methods::

    $arrayNode
        ->canBeEnabled()
    ;

    // is equivalent to

    $arrayNode
        ->treatFalseLike(array('enabled' => false))
        ->treatTrueLike(array('enabled' => true))
        ->treatNullLike(array('enabled' => true))
        ->children()
            ->booleanNode('enabled')
                ->defaultFalse()
    ;

The ``canBeDisabled`` method looks about the same except that the section
would be enabled by default.

Merging options
---------------

Extra options concerning the merge process may be provided. For arrays:

``performNoDeepMerging()``
    When the value is also defined in a second configuration array, don’t
    try to merge an array, but overwrite it entirely

For all nodes:

``cannotBeOverwritten()``
    don’t let other configuration arrays overwrite an existing value for this node

Appending sections
------------------

If you have a complex configuration to validate then the tree can grow to
be large and you may want to split it up into sections. You can do this by
making a section a separate node and then appending it into the main tree
with ``append()``::

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('database');

        $rootNode
            ->children()
                ->arrayNode('connection')
                    ->children()
                        ->scalarNode('driver')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('host')
                            ->defaultValue('localhost')
                        ->end()
                        ->scalarNode('username')->end()
                        ->scalarNode('password')->end()
                        ->booleanNode('memory')
                            ->defaultFalse()
                        ->end()
                    ->end()
                    ->append($this->addParametersNode())
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    public function addParametersNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('parameters');

        $node
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('value')->isRequired()->end()
                ->end()
            ->end()
        ;

        return $node;
    }

This is also useful to help you avoid repeating yourself if you have sections
of the config that are repeated in different places.

Normalization
-------------

When the config files are processed they are first normalized, then merged
and finally the tree is used to validate the resulting array. The normalization
process is used to remove some of the differences that result from different
configuration formats, mainly the differences between Yaml and XML.

The separator used in keys is typically ``_`` in Yaml and ``-`` in XML. For
example, ``auto_connect`` in Yaml and ``auto-connect``. The normalization would
make both of these ``auto_connect``.

.. caution::

    The target key will not be altered if it's mixed like
    ``foo-bar_moo`` or if it already exists.

Another difference between Yaml and XML is in the way arrays of values may
be represented. In Yaml you may have:

.. code-block:: yaml

    twig:
        extensions: ['twig.extension.foo', 'twig.extension.bar']

and in XML:

.. code-block:: xml

    <twig:config>
        <twig:extension>twig.extension.foo</twig:extension>
        <twig:extension>twig.extension.bar</twig:extension>
    </twig:config>

This difference can be removed in normalization by pluralizing the key used
in XML. You can specify that you want a key to be pluralized in this way with
``fixXmlConfig()``::

    $rootNode
        ->fixXmlConfig('extension')
        ->children()
            ->arrayNode('extensions')
                ->prototype('scalar')->end()
            ->end()
        ->end()
    ;

If it is an irregular pluralization you can specify the plural to use as
a second argument::

    $rootNode
        ->fixXmlConfig('child', 'children')
        ->children()
            ->arrayNode('children')
        ->end()
    ;

As well as fixing this, ``fixXmlConfig`` ensures that single xml elements
are still turned into an array. So you may have:

.. code-block:: xml

    <connection>default</connection>
    <connection>extra</connection>

and sometimes only:

.. code-block:: xml

    <connection>default</connection>

By default ``connection`` would be an array in the first case and a string
in the second making it difficult to validate. You can ensure it is always
an array with ``fixXmlConfig``.

You can further control the normalization process if you need to. For example,
you may want to allow a string to be set and used as a particular key or several
keys to be set explicitly. So that, if everything apart from ``name`` is optional
in this config:

.. code-block:: yaml

    connection:
        name: my_mysql_connection
        host: localhost
        driver: mysql
        username: user
        password: pass

you can allow the following as well:

.. code-block:: yaml

    connection: my_mysql_connection

By changing a string value into an associative array with ``name`` as the key::

    $rootNode
        ->children()
            ->arrayNode('connection')
                ->beforeNormalization()
                ->ifString()
                    ->then(function($v) { return array('name'=> $v); })
                ->end()
                ->children()
                    ->scalarNode('name')->isRequired()
                    // ...
                ->end()
            ->end()
        ->end()
    ;

Validation rules
----------------

More advanced validation rules can be provided using the
:class:`Symfony\\Component\\Config\\Definition\\Builder\\ExprBuilder`. This
builder implements a fluent interface for a well-known control structure.
The builder is used for adding advanced validation rules to node definitions, like::

    $rootNode
        ->children()
            ->arrayNode('connection')
                ->children()
                    ->scalarNode('driver')
                        ->isRequired()
                        ->validate()
                        ->ifNotInArray(array('mysql', 'sqlite', 'mssql'))
                            ->thenInvalid('Invalid database driver "%s"')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ;

A validation rule always has an "if" part. You can specify this part in the
following ways:

- ``ifTrue()``
- ``ifString()``
- ``ifNull()``
- ``ifArray()``
- ``ifInArray()``
- ``ifNotInArray()``
- ``always()``

A validation rule also requires a "then" part:

- ``then()``
- ``thenEmptyArray()``
- ``thenInvalid()``
- ``thenUnset()``

Usually, "then" is a closure. Its return value will be used as a new value
for the node, instead
of the node's original value.

Processing configuration values
-------------------------------

The :class:`Symfony\\Component\\Config\\Definition\\Processor` uses the tree
as it was built using the :class:`Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder`
to process multiple arrays of configuration values that should be merged.
If any value is not of the expected type, is mandatory and yet undefined,
or could not be validated in some other way, an exception will be thrown.
Otherwise the result is a clean array of configuration values::

    use Symfony\Component\Yaml\Yaml;
    use Symfony\Component\Config\Definition\Processor;
    use Acme\DatabaseConfiguration;

    $config1 = Yaml::parse(__DIR__.'/src/Matthias/config/config.yml');
    $config2 = Yaml::parse(__DIR__.'/src/Matthias/config/config_extra.yml');

    $configs = array($config1, $config2);

    $processor = new Processor();
    $configuration = new DatabaseConfiguration;
    $processedConfiguration = $processor->processConfiguration(
        $configuration,
        $configs)
    ;
