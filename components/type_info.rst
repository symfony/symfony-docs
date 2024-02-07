The TypeInfo Component
======================

    The TypeInfo component extracts PHP types information. It aims to:

    - Have a powerful Type definition that can handle union, intersections, and generics (and could be even more extended)

    - Being able to get types from anything, such as properties, method arguments, return types, and raw strings (and can also be extended).


Installation
------------

.. code-block:: terminal

    $ composer require symfony/type-info

.. include:: /components/require_autoload.rst.inc

Usage
-----

There is two ways to use this component. First one is to create a manually thanks
to :class:`Symfony\\Component\\TypeInfo\\Type` static methods as following::

    use Symfony\Component\TypeInfo\Type;

    Type::int();
    Type::nullable(Type::string());
    Type::generic(Type::object(Collection::class), Type::int());
    Type::list(Type::bool());
    Type::intersection(Type::object(\Stringable::class), Type::object(\Iterator::class));

    // Many others are available and can be
    // found in Symfony\Component\TypeInfo\TypeFactoryTrait

Second way to use TypeInfo is to resolve a type based on reflection or a simple string::

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

.. note::

    To support raw string resolving, you need to install ``phpstan/phpdoc-parser`` package.
