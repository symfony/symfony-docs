.. index::
   single: Config; Defining and processing configuration values

Defining and Processing Configuration Values
============================================

Validating Configuration Values
-------------------------------

After loading configuration values from all kinds of resources, the values
and their structure can be validated using the "Definition" part of the
Config Component. Configuration values are usually expected to show some
kind of hierarchy. Also, values should be of a certain type, be restricted
in number or be one of a given set of values. For example, the following
configuration (in YAML) shows a clear hierarchy and some validation rules
that should be applied to it (like: "the value for ``auto_connect`` must
be a boolean value"):

.. code-block:: yaml

    database:
        auto_connect: true
        default_connection: mysql
        connections:
            mysql:
                host:     localhost
                driver:   mysql
                username: user
                password: pass
            sqlite:
                host:     localhost
                driver:   sqlite
                memory:   true
                username: user
                password: pass

When loading multiple configuration files, it should be possible to merge
and overwrite some values. Other values should not be merged and stay as
they are when first encountered. Also, some keys are only available when
another key has a specific value (in the sample configuration above: the
``memory`` key only makes sense when the ``driver`` is ``sqlite``).

Defining a Hierarchy of Configuration Values Using the TreeBuilder
------------------------------------------------------------------

All the rules concerning configuration values can be defined using the
:class:`Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder`.

A :class:`Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder`
instance should be returned from a custom ``Configuration`` class which
implements the :class:`Symfony\\Component\\Config\\Definition\\ConfigurationInterface`::

    namespace Acme\DatabaseConfiguration;

    use Symfony\Component\Config\Definition\ConfigurationInterface;
    use Symfony\Component\Config\Definition\Builder\TreeBuilder;

    class DatabaseConfiguration implements ConfigurationInterface
    {
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder('database');

            // ... add node definitions to the root of the tree
            // $treeBuilder->getRootNode()->...

            return $treeBuilder;
        }
    }

.. deprecated:: 4.2

    Not passing the root node name to ``TreeBuilder`` was deprecated in Symfony 4.2.

Adding Node Definitions to the Tree
-----------------------------------

Variable Nodes
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
after defining a node, a call to ``end()`` takes you one step up in the
hierarchy.

Node Type
~~~~~~~~~

It is possible to validate the type of a provided value by using the appropriate
node definition. Node types are available for:

* scalar (generic type that includes booleans, strings, integers, floats
  and ``null``)
* boolean
* integer
* float
* enum (similar to scalar, but it only allows a finite set of values)
* array
* variable (no validation)

and are created with ``node($name, $type)`` or their associated shortcut
``xxxxNode($name)`` method.

Numeric Node Constraints
~~~~~~~~~~~~~~~~~~~~~~~~

Numeric nodes (float and integer) provide two extra constraints -
:method:`Symfony\\Component\\Config\\Definition\\Builder\\IntegerNodeDefinition::min`
and :method:`Symfony\\Component\\Config\\Definition\\Builder\\IntegerNodeDefinition::max`
- allowing to validate the value::

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

Enum Nodes
~~~~~~~~~~

Enum nodes provide a constraint to match the given input against a set of
values::

    $rootNode
        ->children()
            ->enumNode('delivery')
                ->values(['standard', 'expedited', 'priority'])
            ->end()
        ->end()
    ;

This will restrict the ``delivery`` options to be either ``standard``,
``expedited``  or ``priority``.

Array Nodes
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
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('driver')->end()
                        ->scalarNode('host')->end()
                        ->scalarNode('username')->end()
                        ->scalarNode('password')->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ;

A prototype can be used to add a definition which may be repeated many times
inside the current node. According to the prototype definition in the example
above, it is possible to have multiple connection arrays (containing a ``driver``,
``host``, etc.).

Sometimes, to improve the user experience of your application or bundle, you may
allow to use a simple string or numeric value where an array value is required.
Use the ``castToArray()`` helper to turn those variables into arrays::

    ->arrayNode('hosts')
        ->beforeNormalization()->castToArray()->end()
        // ...
    ->end()

Array Node Options
~~~~~~~~~~~~~~~~~~

Before defining the children of an array node, you can provide options like:

``useAttributeAsKey()``
    Provide the name of a child node, whose value should be used as the key in
    the resulting array. This method also defines the way config array keys are
    treated, as explained in the following example.
``requiresAtLeastOneElement()``
    There should be at least one element in the array (works only when
    ``isRequired()`` is also called).
``addDefaultsIfNotSet()``
    If any child nodes have default values, use them if explicit values
    haven't been provided.
``normalizeKeys(false)``
    If called (with ``false``), keys with dashes are *not* normalized to underscores.
    It is recommended to use this with prototype nodes where the user will define
    a key-value map, to avoid an unnecessary transformation.
``ignoreExtraKeys()``
    Allows extra config keys to be specified under an array without
    throwing an exception.

A basic prototyped array configuration can be defined as follows::

    $node
        ->fixXmlConfig('driver')
        ->children()
            ->arrayNode('drivers')
                ->scalarPrototype()->end()
            ->end()
        ->end()
    ;

When using the following YAML configuration:

.. code-block:: yaml

    drivers: ['mysql', 'sqlite']

Or the following XML configuration:

.. code-block:: xml

    <driver>mysql</driver>
    <driver>sqlite</driver>

The processed configuration is::

    Array(
        [0] => 'mysql'
        [1] => 'sqlite'
    )

A more complex example would be to define a prototyped array with children::

    $node
        ->fixXmlConfig('connection')
        ->children()
            ->arrayNode('connections')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('table')->end()
                        ->scalarNode('user')->end()
                        ->scalarNode('password')->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ;

When using the following YAML configuration:

.. code-block:: yaml

    connections:
        - { table: symfony, user: root, password: ~ }
        - { table: foo, user: root, password: pa$$ }

Or the following XML configuration:

.. code-block:: xml

    <connection table="symfony" user="root" password="null"/>
    <connection table="foo" user="root" password="pa$$"/>

The processed configuration is::

    Array(
        [0] => Array(
            [table] => 'symfony'
            [user] => 'root'
            [password] => null
        )
        [1] => Array(
            [table] => 'foo'
            [user] => 'root'
            [password] => 'pa$$'
        )
    )

The previous output matches the expected result. However, given the configuration
tree, when using the following YAML configuration:

.. code-block:: yaml

    connections:
        sf_connection:
            table: symfony
            user: root
            password: ~
        default:
            table: foo
            user: root
            password: pa$$

The output configuration will be exactly the same as before. In other words, the
``sf_connection`` and ``default`` configuration keys are lost. The reason is that
the Symfony Config component treats arrays as lists by default.

.. note::

    As of writing this, there is an inconsistency: if only one file provides the
    configuration in question, the keys (i.e. ``sf_connection`` and ``default``)
    are *not* lost. But if more than one file provides the configuration, the keys
    are lost as described above.

In order to maintain the array keys use the ``useAttributeAsKey()`` method::

    $node
        ->fixXmlConfig('connection')
        ->children()
            ->arrayNode('connections')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('table')->end()
                        ->scalarNode('user')->end()
                        ->scalarNode('password')->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ;

The argument of this method (``name`` in the example above) defines the name of
the attribute added to each XML node to differentiate them. Now you can use the
same YAML configuration shown before or the following XML configuration:

.. code-block:: xml

    <connection name="sf_connection"
        table="symfony" user="root" password="null"/>
    <connection name="default"
        table="foo" user="root" password="pa$$"/>

In both cases, the processed configuration maintains the ``sf_connection`` and
``default`` keys::

    Array(
        [sf_connection] => Array(
            [table] => 'symfony'
            [user] => 'root'
            [password] => null
        )
        [default] => Array(
            [table] => 'foo'
            [user] => 'root'
            [password] => 'pa$$'
        )
    )

Default and Required Values
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
    (``null``, ``true``, ``false``), provide a replacement value in case
    the value is ``*.``

The following example shows these methods in practice::

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

Deprecating the Option
----------------------

You can deprecate options using the
:method:`Symfony\\Component\\Config\\Definition\\Builder\\NodeDefinition::setDeprecated`
method::

    $rootNode
        ->children()
            ->integerNode('old_option')
                // this outputs the following generic deprecation message:
                // The child node "old_option" at path "..." is deprecated.
                ->setDeprecated()

                // you can also pass a custom deprecation message (%node% and %path% placeholders are available):
                ->setDeprecated('The "%node%" option is deprecated. Use "new_config_option" instead.')
            ->end()
        ->end()
    ;

If you use the Web Debug Toolbar, these deprecation notices are shown when the
configuration is rebuilt.

Documenting the Option
----------------------

All options can be documented using the
:method:`Symfony\\Component\\Config\\Definition\\Builder\\NodeDefinition::info`
method::

    $rootNode
        ->children()
            ->integerNode('entries_per_page')
                ->info('This value is only used for the search results page.')
                ->defaultValue(25)
            ->end()
        ->end()
    ;

The info will be printed as a comment when dumping the configuration tree
with the ``config:dump-reference`` command.

In YAML you may have:

.. code-block:: yaml

    # This value is only used for the search results page.
    entries_per_page: 25

and in XML:

.. code-block:: xml

    <!-- entries-per-page: This value is only used for the search results page. -->
    <config entries-per-page="25"/>

Optional Sections
-----------------

If you have entire sections which are optional and can be enabled/disabled,
you can take advantage of the shortcut
:method:`Symfony\\Component\\Config\\Definition\\Builder\\ArrayNodeDefinition::canBeEnabled`
and
:method:`Symfony\\Component\\Config\\Definition\\Builder\\ArrayNodeDefinition::canBeDisabled`
methods::

    $arrayNode
        ->canBeEnabled()
    ;

    // is equivalent to

    $arrayNode
        ->treatFalseLike(['enabled' => false])
        ->treatTrueLike(['enabled' => true])
        ->treatNullLike(['enabled' => true])
        ->children()
            ->booleanNode('enabled')
                ->defaultFalse()
    ;

The ``canBeDisabled()`` method looks about the same except that the section
would be enabled by default.

Merging Options
---------------

Extra options concerning the merge process may be provided. For arrays:

``performNoDeepMerging()``
    When the value is also defined in a second configuration array, don't
    try to merge an array, but overwrite it entirely

For all nodes:

``cannotBeOverwritten()``
    don't let other configuration arrays overwrite an existing value for
    this node

Appending Sections
------------------

If you have a complex configuration to validate then the tree can grow to
be large and you may want to split it up into sections. You can do this
by making a section a separate node and then appending it into the main
tree with ``append()``::

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('database');

        $treeBuilder->getRootNode()
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
        $treeBuilder = new TreeBuilder('parameters');

        $node = $treeBuilder->getRootNode()
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('value')->isRequired()->end()
                ->end()
            ->end()
        ;

        return $node;
    }

This is also useful to help you avoid repeating yourself if you have sections
of the config that are repeated in different places.

The example results in the following:

.. configuration-block::

    .. code-block:: yaml

        database:
            connection:
                driver:               ~ # Required
                host:                 localhost
                username:             ~
                password:             ~
                memory:               false
                parameters:           # Required

                    # Prototype
                    name:
                        value:                ~ # Required

    .. code-block:: xml

        <database>
            <!-- driver: Required -->
            <connection
                driver=""
                host="localhost"
                username=""
                password=""
                memory="false"
            >

                <!-- prototype -->
                <!-- value: Required -->
                <parameters
                    name="parameters name"
                    value=""
                />

            </connection>
        </database>

.. _component-config-normalization:

Normalization
-------------

When the config files are processed they are first normalized, then merged
and finally the tree is used to validate the resulting array. The normalization
process is used to remove some of the differences that result from different
configuration formats, mainly the differences between YAML and XML.

The separator used in keys is typically ``_`` in YAML and ``-`` in XML.
For example, ``auto_connect`` in YAML and ``auto-connect`` in XML. The
normalization would make both of these ``auto_connect``.

.. caution::

    The target key will not be altered if it's mixed like
    ``foo-bar_moo`` or if it already exists.

Another difference between YAML and XML is in the way arrays of values may
be represented. In YAML you may have:

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
in XML. You can specify that you want a key to be pluralized in this way
with ``fixXmlConfig()``::

    $rootNode
        ->fixXmlConfig('extension')
        ->children()
            ->arrayNode('extensions')
                ->scalarPrototype()->end()
            ->end()
        ->end()
    ;

If it is an irregular pluralization you can specify the plural to use as
a second argument::

    $rootNode
        ->fixXmlConfig('child', 'children')
        ->children()
            ->arrayNode('children')
                // ...
            ->end()
        ->end()
    ;

As well as fixing this, ``fixXmlConfig()`` ensures that single XML elements
are still turned into an array. So you may have:

.. code-block:: xml

    <connection>default</connection>
    <connection>extra</connection>

and sometimes only:

.. code-block:: xml

    <connection>default</connection>

By default ``connection`` would be an array in the first case and a string
in the second making it difficult to validate. You can ensure it is always
an array with ``fixXmlConfig()``.

You can further control the normalization process if you need to. For example,
you may want to allow a string to be set and used as a particular key or
several keys to be set explicitly. So that, if everything apart from ``name``
is optional in this config:

.. code-block:: yaml

    connection:
        name:     my_mysql_connection
        host:     localhost
        driver:   mysql
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
                    ->then(function ($v) { return ['name' => $v]; })
                ->end()
                ->children()
                    ->scalarNode('name')->isRequired()
                    // ...
                ->end()
            ->end()
        ->end()
    ;

Validation Rules
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
                            ->ifNotInArray(['mysql', 'sqlite', 'mssql'])
                            ->thenInvalid('Invalid database driver %s')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ;

A validation rule always has an "if" part. You can specify this part in
the following ways:

- ``ifTrue()``
- ``ifString()``
- ``ifNull()``
- ``ifEmpty()`` (since Symfony 3.2)
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
for the node, instead of the node's original value.

Configuring the Node Path Separator
-----------------------------------

Consider the following config builder example::

    $treeBuilder = new TreeBuilder('database');

    $treeBuilder->getRootNode()
        ->children()
            ->arrayNode('connection')
                ->children()
                    ->scalarNode('driver')->end()
                ->end()
            ->end()
        ->end()
    ;

By default, the hierarchy of nodes in a config path is defined with a dot
character (``.``)::

    // ...

    $node = $treeBuilder->buildTree();
    $children = $node->getChildren();
    $path = $children['driver']->getPath();
    // $path = 'database.connection.driver'

Use the ``setPathSeparator()`` method on the config builder to change the path
separator::

    // ...

    $treeBuilder->setPathSeparator('/');
    $node = $treeBuilder->buildTree();
    $children = $node->getChildren();
    $path = $children['driver']->getPath();
    // $path = 'database/connection/driver'

Processing Configuration Values
-------------------------------

The :class:`Symfony\\Component\\Config\\Definition\\Processor` uses the
tree as it was built using the
:class:`Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder` to
process multiple arrays of configuration values that should be merged. If
any value is not of the expected type, is mandatory and yet undefined, or
could not be validated in some other way, an exception will be thrown.
Otherwise the result is a clean array of configuration values::

    use Symfony\Component\Yaml\Yaml;
    use Symfony\Component\Config\Definition\Processor;
    use Acme\DatabaseConfiguration;

    $config = Yaml::parse(
        file_get_contents(__DIR__.'/src/Matthias/config/config.yaml')
    );
    $extraConfig = Yaml::parse(
        file_get_contents(__DIR__.'/src/Matthias/config/config_extra.yaml')
    );

    $configs = [$config, $extraConfig];

    $processor = new Processor();
    $databaseConfiguration = new DatabaseConfiguration();
    $processedConfiguration = $processor->processConfiguration(
        $databaseConfiguration,
        $configs
    );
