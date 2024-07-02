The TypeInfo Component
======================

The TypeInfo component extracts type information from PHP elements like properties,
arguments and return types.

This component provides:

* A powerful ``Type`` definition that can handle unions, intersections, and generics
  (and can be extended to support more types in the future);
* A way to get types from PHP elements such as properties, method arguments,
  return types, and raw strings.

.. caution::

    This component is :doc:`experimental </contributing/code/experimental>` and
    could be changed at any time without prior notice.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/type-info

.. include:: /components/require_autoload.rst.inc

Usage
-----

This component gives you a :class:`Symfony\\Component\\TypeInfo\\Type` object that
represents the PHP type of anything you built or asked to resolve.

There are two ways to use this component. First one is to create a type manually thanks
to the :class:`Symfony\\Component\\TypeInfo\\Type` static methods as following::

    use Symfony\Component\TypeInfo\Type;

    Type::int();
    Type::nullable(Type::string());
    Type::generic(Type::object(Collection::class), Type::int());
    Type::list(Type::bool());
    Type::intersection(Type::object(\Stringable::class), Type::object(\Iterator::class));

    // Many others are available and can be
    // found in Symfony\Component\TypeInfo\TypeFactoryTrait

The second way of using the component is to use ``TypeInfo`` to resolve a type
based on reflection or a simple string::

    use Symfony\Component\TypeInfo\Type;
    use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

    // Instantiate a new resolver
    $typeResolver = TypeResolver::create();

    // Then resolve types for any subject
    $typeResolver->resolve(new \ReflectionProperty(Dummy::class, 'id')); // returns an "int" Type instance
    $typeResolver->resolve('bool'); // returns a "bool" Type instance

    // Types can be instantiated thanks to static factories
    $type = Type::list(Type::nullable(Type::bool()));

    // Type instances have several helper methods
    $type->getBaseType() // returns an "array" Type instance
    $type->getCollectionKeyType(); // returns an "int" Type instance
    $type->getCollectionValueType()->isNullable(); // returns true

Each of these calls will return you a ``Type`` instance that corresponds to the
static method used. You can also resolve types from a string (as shown in the
``bool`` parameter of the previous example)

.. note::

    To support raw string resolving, you need to install ``phpstan/phpdoc-parser`` package.
