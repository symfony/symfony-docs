.. index::
   single: Config

The Config Component
====================

Problem to solve
----------------

Projects are configurable in one way or another. We usually want to abstract
important data be it configuration data, fixtures data, or any other data
an application can use. We could have just leave these information written
within the code, however providing these data in a human or in a developer
or even in a client friendly way could make a project be maintainable. The
Symfony2 framework is a good example on how a framework can rely on configuration
data to be specified in a human readable way making the project very accessible
to be tuned to specific needs, improved or maintained. The data configuration
in the Symfony2 framework is in most part service specificaton data, but
there is other type of data such as security, routing, and asset. There
is no limit to what kind of data type you can work with when using the
Config component. If you can determine certain resources where to store
this information and you design your project so to keep in mind this incoming
configuration data from these sources then Config component can be used.

Therefore Config provides the infrastructure for loading configurations
from different data sources and optionally monitoring these data sources
for changes. There are additional tools for validating, normalizing and
handling of defaults that can optionally be used to convert from different
formats to arrays.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Config);
* Install it via PEAR ( `pear.symfony.com/Config`);
* Install it via Composer (`symfony/Config` on Packagist).

Architecture
------------

wip

Usage
-----

The main class is the configuration class:

.. code-block:: php

    namespace Project\Configuration;

    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    class Configuration implements ConfigurationInterface
    {
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('acme_demo');

            return $rootNode;
        }
    }

A boolean node
~~~~~~~~~~~~~~

There are many ways to specify validation rules for configuration values,
but first, let’s create a fresh node in the tree for our still invalid value
“enabled”. The value should always be of type “boolean”, so before we return
$rootNode, we add:

.. code-block:: php

    $rootNode->
        children()
            ->booleanNode('enabled')->end()
        end()
    ;

As you can see, the TreeBuilder and the nodes all implement a fluent interface,
so we can write the tree down in a semantic way: the way it looks reflects
the way it is.

The calls to end() mean that we want to move up again to the parent node
of the current node.

ScalarNode
~~~~~~~~~~

Let’s add a node for scalar values (like strings, numbers, etc.):

.. code-block:: php

    $rootNode->
        children()
            ->booleanNode('enabled')->end()
            ->scalarNode('default_user')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
        end()
    ;

The new node defines a config value “default_user” which is required and
may not be empty.

ArrayNode
~~~~~~~~~

Now, let’s add an array node, which allows for an array of users to be defined
in the config file:

.. code-block:: php

    $rootNode->
        children()
            ->booleanNode('enabled')->end()
            ->scalarNode('default_user')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->arrayNode('users')
                ->requiresAtLeastOneElement()
                ->prototype('array')
                    ->children()
                        ->scalarNode('full_name')
                            ->isRequired(true)
                        ->end()
                        ->booleanNode('is_active')
                            ->defaultValue(true)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ;

A few other things are shown here: the config value “user” contains multiple
subnodes, of which the prototype is an array node. The children of these
array nodes themselves will be a scalar node called “full_name” and a boolean
node called “is_active”, of which the default value is true.
An extra requirement is that at least one such user should be defined.

Before normalization – then what?
The final thing I want to show is how to change the overall structure of
the config values, before they are validated. This would allow you to add
shortcuts, or special ways of handling certain config structures (see the
DoctrineBundle Configuration class for a beautiful example of this: the
only connection defined will be moved to the “connections” section, before
processing). In my example, I remove all the user definitions, when the
value of “enabled” is false or is not set:

.. code-block:: php

    $rootNode->
        ->beforeNormalization()
            ->ifTrue(function($v) {
                // $v contains the raw configuration values
                return !isset($v['enabled']) || false === $v['enabled'];
            })
            ->then(function($v) {
                unset($v['users']);
                return $v;
            })
            ->end()
        ->children()
            // ...
        ->end()
    ;
