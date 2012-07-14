.. index::
   single: Config; Define and process configuration values

Define and process configuration values
=======================================

Validate configuration values
-----------------------------

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

Define a hierarchy of configuration values using the TreeBuilder
----------------------------------------------------------------

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

Add node definitions to the tree
--------------------------------

Variable nodes
~~~~~~~~~~~~~~

A tree contains node definitions which can be layed out in a semantic way.
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

Array nodes
~~~~~~~~~~~

It is possible to add a deeper level to the hierarchy, by adding an array
node. The array node itself, may have a pre-defined set of variable nodes:

.. code-block:: php

    $rootNode
        ->arrayNode('connection')
            ->scalarNode('driver')->end()
            ->scalarNode('host')->end()
            ->scalarNode('username')->end()
            ->scalarNode('password')->end()
        ->end()
    ;

Or you may define a prototype for each node inside an array node:

.. code-block:: php

    $rootNode
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
    Provide the name of a child node, whose value should be used as the key in the resulting array
``requiresAtLeastOneElement()``
    There should be at least one element in the array (works only when ``isRequired()`` is also
    called).

An example of this:

.. code-block:: php

    $rootNode
        ->arrayNode('parameters')
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->useAttributeAsKey('name')
                ->children()
                    ->scalarNode('name')->isRequired()->end()
                    ->scalarNode('value')->isRequired()->end()
                ->end()
            ->end()
        ->end()
    ;

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
    ;

Merging options
---------------

Extra options concerning the merge process may be provided. For arrays:

``performNoDeepMerging()``
    When the value is also defined in a second configuration array, don’t
    try to merge an array, but overwrite it entirely

For all nodes:

``cannotBeOverwritten()``
    don’t let other configuration arrays overwrite an existing value for this node

Validation rules
----------------

More advanced validation rules can be provided using the
:class:`Symfony\\Component\\Config\\Definition\\Builder\\ExprBuilder`. This
builder implements a fluent interface for a well-known control structure.
The builder is used for adding advanced validation rules to node definitions, like::

    $rootNode
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
    $processedConfiguration = $processor->processConfiguration($configuration, $configs);
