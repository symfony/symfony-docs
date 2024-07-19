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

    // returns the main type (e.g. in this example it returns an "array" Type instance);
    // for nullable types (e.g. string|null) it returns the non-null type (e.g. string)
    // and for compound types (e.g. int|string) it throws an exception because both types
    // can be considered the main one, so there's no way to pick one
    $baseType = $type->getBaseType();

    // for collections, it returns the type of the item used as the key;
    // in this example, the collection is a list, so it returns an "int" Type instance
    $keyType = $type->getCollectionKeyType();

    // you can chain the utility methods (e.g. to introspect the values of the collection)
    // the following code will return true
    $isValueNullable = $type->getCollectionValueType()->isNullable();

Each of these calls will return you a ``Type`` instance that corresponds to the
static method used. You can also resolve types from a string (as shown in the
``bool`` parameter of the previous example)

.. note::

    To support raw string resolving, you need to install ``phpstan/phpdoc-parser`` package.
