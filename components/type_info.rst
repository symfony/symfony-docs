The TypeInfo Component
======================

The TypeInfo component extracts type information from PHP elements like properties,
arguments and return types.

This component provides:

* A powerful ``Type`` definition that can handle unions, intersections, and generics
  (and can be extended to support more types in the future);
* A way to get types from PHP elements such as properties, method arguments,
  return types, and raw strings.

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
to the :class:`Symfony\\Component\\TypeInfo\\Type` static methods as follows::

    use Symfony\Component\TypeInfo\Type;

    Type::int();
    Type::nullable(Type::string());
    Type::generic(Type::object(Collection::class), Type::int());
    Type::list(Type::bool());
    Type::intersection(Type::object(\Stringable::class), Type::object(\Iterator::class));

Many other methods are available and can be found
in :class:`Symfony\\Component\\TypeInfo\\TypeFactoryTrait`.

You can also use a generic method that detects the type automatically::

    Type::fromValue(1.1);   // same as Type::float()
    Type::fromValue('...'); // same as Type::string()
    Type::fromValue(false); // same as Type::false()

Resolvers
~~~~~~~~~

The second way to use the component is by using ``TypeInfo`` to resolve a type
based on reflection or a simple string. This approach is designed for libraries
that need a simple way to describe a class or anything with a type::

    use Symfony\Component\TypeInfo\Type;
    use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

    class Dummy
    {
        public function __construct(
            public int $id,
        ) {
        }
    }

    // Instantiate a new resolver
    $typeResolver = TypeResolver::create();

    // Then resolve types for any subject
    $typeResolver->resolve(new \ReflectionProperty(Dummy::class, 'id')); // returns an "int" Type instance
    $typeResolver->resolve('bool'); // returns a "bool" Type instance
    $typeResolver->resolve('array{id: int, name?: string}'); // returns an array shape type instance where 'id' is required and 'name' is optional


    // Types can be instantiated thanks to static factories
    $type = Type::list(Type::nullable(Type::bool()));

    // Type instances have several helper methods

    // for collections, it returns the type of the item used as the key;
    // in this example, the collection is a list, so it returns an "int" Type instance
    $keyType = $type->getCollectionKeyType();

    // you can chain the utility methods (e.g. to introspect the values of the collection)
    // the following code will return true
    $isValueNullable = $type->getCollectionValueType()->isNullable();

Each of these calls will return you a ``Type`` instance that corresponds to the
static method used. You can also resolve types from a string (as shown in the
``bool`` parameter of the previous example).

PHPDoc Parsing
~~~~~~~~~~~~~~

In many cases, you may not have cleanly typed properties or may need more precise
type definitions provided by advanced PHPDoc. To achieve this, you can use a string
resolver based on the PHPDoc annotations.

First, run the command ``composer require phpstan/phpdoc-parser`` to install the
PHP package required for string resolving. Then, follow these steps::

    use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

    class Dummy
    {
        public function __construct(
            public int $id,
            /** @var string[] $tags */
            public array $tags,
        ) {
        }
    }

    $typeResolver = TypeResolver::create();
    $typeResolver->resolve(new \ReflectionProperty(Dummy::class, 'id')); // returns an "int" Type
    $typeResolver->resolve(new \ReflectionProperty(Dummy::class, 'tags')); // returns a collection with "int" as key and "string" as values Type

Generic Templates
~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.2

    Support for covariant templates was introduced in Symfony 8.2.

TypeInfo collects the generic templates declared through ``@template`` annotations,
including their covariant variant ``@template-covariant`` (the ``@psalm-`` and
``@phpstan-`` prefixed synonyms are also supported)::

    use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

    /**
     * @template-covariant T of int|string
     */
    class Collection
    {
        /** @return T */
        public function getFirst(): mixed
        {
            // ...
        }
    }

    $typeResolver = TypeResolver::create();
    // resolves the "getFirst()" return type using the covariant "T" template
    $typeResolver->resolve(new \ReflectionMethod(Collection::class, 'getFirst'));

Type Aliases
~~~~~~~~~~~~

The TypeInfo component supports type aliases defined via PHPDoc annotations. This
allows you to define complex types once and reuse them across your codebase::

    /**
     * @phpstan-type UserData = array{name: string, email: string, age: int}
     */
    class UserService
    {
        /**
         * @var UserData
         */
        public mixed $userData;

        /**
         * @param UserData $data
         */
        public function process(mixed $data): void
        {
            // ...
        }
    }

    $typeResolver = TypeResolver::create();
    $typeResolver->resolve(new \ReflectionProperty(UserService::class, 'userData'));
    // returns an array Type with the shape defined in UserData

The component supports both PHPStan and Psalm annotation formats:

* ``@phpstan-type`` and ``@psalm-type`` for defining type aliases
* ``@phpstan-import-type`` and ``@psalm-import-type`` for importing type aliases from other classes

You can also import type aliases defined in other classes::

    /**
     * @phpstan-type Address = array{street: string, city: string, zip: string}
     */
    class Location
    {
    }

    /**
     * @phpstan-import-type Address from Location
     */
    class Company
    {
        /**
         * @var Address
         */
        public mixed $headquarters;
    }

.. note::

    Both syntax variations are supported: with an equals sign
    (``@phpstan-type TypeAlias = Type``) or without (``@phpstan-type TypeAlias Type``).

You can also define type aliases globally through the framework configuration.
These aliases are available everywhere in the type resolver, without needing
``@phpstan-type`` annotations:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            type_info:
                aliases:
                    MoneyAmount: int
                    UserData: 'array{name: string, email: string, age: int}'

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'type_info' => [
                    'aliases' => [
                        'MoneyAmount' => 'int',
                        'UserData' => 'array{name: string, email: string, age: int}',
                    ],
                ],
            ],
        ]);

Once configured, these aliases can be used in PHPDoc annotations and will be
resolved by the type resolver::

    class Product
    {
        /** @var MoneyAmount */
        public mixed $price;
    }

    $typeResolver = TypeResolver::create();
    $typeResolver->resolve(new \ReflectionProperty(Product::class, 'price'));
    // returns an "int" Type instance

    // aliases can also be resolved directly from strings
    $typeResolver->resolve('MoneyAmount'); // returns an "int" Type instance

.. note::

    When a PHPDoc ``@phpstan-type`` annotation defines an alias with the same
    name as a configuration alias, the PHPDoc annotation takes precedence.

Array Shapes
~~~~~~~~~~~~

TypeInfo can resolve array shapes, which describe the structure of arrays with
specific key-value type relationships. Use the ``array{...}`` syntax in PHPDoc
annotations::

    use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

    class Dummy
    {
        /**
         * @var array{name: string, age: int, email?: string}
         */
        public array $person;
    }

    $typeResolver = TypeResolver::create();
    $type = $typeResolver->resolve(new \ReflectionProperty(Dummy::class, 'person'));
    // returns an ArrayShapeType with "name" (string), "age" (int), and optional "email" (string)

The ``?`` suffix marks a key as optional (e.g. ``email?``).

Array shapes are **sealed** by default, meaning they reject extra entries
beyond those explicitly defined. Use ``...`` to create an **unsealed** shape
that accepts additional entries::

Array shapes can be sealed or unsealed:

    // sealed: only accepts "id" key
    // @var array{id: int}

    // unsealed: accepts "id" and any extra entries
    // @var array{id: int, ...}

    // unsealed but extra entries must use strings as keys and booleans as values
    // @var array{id: int, ...<string, bool>}

You can also create array shapes manually using the ``Type::arrayShape()`` method::

    use Symfony\Component\TypeInfo\Type;

    // simple array shape (sealed by default)
    $type = Type::arrayShape([
        'name' => Type::string(),
        'age' => Type::int()
    ]);

    // with optional keys (denoted by "?" suffix)
    $type = Type::arrayShape([
        'required_id' => Type::int(),
        'optional_name' => ['type' => Type::string(), 'optional' => true],
    ]);

    // unsealed: allow extra entries (sealed = false)
    $type = Type::arrayShape([
        'id' => Type::int(),
    ], false);

    // unsealed with typed extra keys and values (extraKeyType=string, extraValueType=bool)
    // equivalent to: array{id: int, ...<string, bool>}
    $type = Type::arrayShape([
        'id' => Type::int(),
    ], false, Type::string(), Type::bool());

Object Shapes
.............

.. versionadded:: 8.1

    Support for object shapes was introduced in Symfony 8.1.

TypeInfo can resolve object shapes, which describe the structure of anonymous
objects with specific properties and their types. Use the ``object{...}`` syntax
in PHPDoc annotations::

    use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

    class Dummy
    {
        /**
         * @var object{name: string, age: int, email?: string}
         */
        public object $person;
    }

    $typeResolver = TypeResolver::create();
    $type = $typeResolver->resolve(new \ReflectionProperty(Dummy::class, 'person'));
    // returns an ObjectShapeType with "name" (string), "age" (int), and optional "email" (string)

The ``?`` suffix marks a property as optional (e.g. ``email?``).

You can also create object shapes manually using the ``Type::objectShape()`` method::

    use Symfony\Component\TypeInfo\Type;

    $type = Type::objectShape([
        'name' => Type::string(),
        'age' => Type::int(),
        'email' => Type::nullable(Type::string()),
    ]);

Advanced Usages
~~~~~~~~~~~~~~~

The TypeInfo component provides various methods to manipulate and check types,
depending on your needs.

**Identify** a type::

    // define a simple integer type
    $type = Type::int();
    // check if the type matches a specific identifier
    $type->isIdentifiedBy(TypeIdentifier::INT);    // true
    $type->isIdentifiedBy(TypeIdentifier::STRING); // false

    // define a union type (equivalent to PHP's int|string)
    $type = Type::union(Type::string(), Type::int());
    // now the second check is true because the union type contains the string type
    $type->isIdentifiedBy(TypeIdentifier::INT);    // true
    $type->isIdentifiedBy(TypeIdentifier::STRING); // true

    class DummyParent {}
    class Dummy extends DummyParent implements DummyInterface {}

    // define an object type
    $type = Type::object(Dummy::class);

    // check if the type is an object or matches a specific class
    $type->isIdentifiedBy(TypeIdentifier::OBJECT); // true
    $type->isIdentifiedBy(Dummy::class);           // true
    // check if it inherits/implements something
    $type->isIdentifiedBy(DummyParent::class);     // true
    $type->isIdentifiedBy(DummyInterface::class);  // true

Checking if a type **accepts a value**::

    $type = Type::int();
    // check if the type accepts a given value
    $type->accepts(123); // true
    $type->accepts('z'); // false

    $type = Type::union(Type::string(), Type::int());
    // now the second check is true because the union type accepts either an int or a string value
    $type->accepts(123); // true
    $type->accepts('z'); // true

Using callables for **complex checks**::

    class Foo
    {
        private int $integer;
        private string $string;
        private ?float $float;
    }

    $reflClass = new \ReflectionClass(Foo::class);

    $resolver = TypeResolver::create();
    $integerType = $resolver->resolve($reflClass->getProperty('integer'));
    $stringType = $resolver->resolve($reflClass->getProperty('string'));
    $floatType = $resolver->resolve($reflClass->getProperty('float'));

    // define a callable to validate non-nullable number types
    $isNonNullableNumber = function (Type $type): bool {
        if ($type->isNullable()) {
            return false;
        }

        if ($type->isIdentifiedBy(TypeIdentifier::INT) || $type->isIdentifiedBy(TypeIdentifier::FLOAT)) {
            return true;
        }

        return false;
    };

    $integerType->isSatisfiedBy($isNonNullableNumber); // true
    $stringType->isSatisfiedBy($isNonNullableNumber);  // false
    $floatType->isSatisfiedBy($isNonNullableNumber);   // false
