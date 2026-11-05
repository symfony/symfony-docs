The JsonSchema Component
========================

The JsonSchema component generates `JSON Schema`_ documents from PHP types.
Use it to describe your API in an OpenAPI specification, or to describe the
input and the output of tools exposed to AI agents.

The component turns a :doc:`TypeInfo </components/type_info>` type, or the
parameters of a function, into a schema. It reads the properties of classes
with the :doc:`PropertyInfo component </components/property_info>` and, when
they are available, takes into account the serializer groups, the name
converter, the discriminator maps and the validation constraints.

.. versionadded:: 8.2

    The JsonSchema component was introduced in Symfony 8.2.

.. warning::

    This component is experimental. Its API can change in minor versions
    without following the backward compatibility promise.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/json-schema

.. include:: /components/require_autoload.rst.inc

The component requires PHP 8.2 or higher. The TypeInfo and PropertyInfo
components are installed with it, while the Serializer and Validator
components are optional.

Usage
-----

Create a :class:`Symfony\\Component\\JsonSchema\\SchemaGenerator` with its
``create()`` method and call ``generate()`` with the type to describe. The
generator uses the Serializer and Validator components, and the PHPDoc
parsers, when they are installed::

    use Acme\Entity\Book;
    use Symfony\Component\JsonSchema\SchemaGenerator;
    use Symfony\Component\TypeInfo\Type;

    $generator = SchemaGenerator::create();
    $schema = $generator->generate(Type::object(Book::class));

    echo json_encode($schema, \JSON_PRETTY_PRINT);

The result is a :class:`Symfony\\Component\\JsonSchema\\Schema` object. It
holds a root schema and the named definitions that it references:

* ``getRoot()`` returns the root schema;
* ``getDefinitions()`` returns the definitions, indexed by name;
* ``toArray()`` (also used by ``json_encode()``) returns a complete document,
  with the definitions placed where the dialect expects them (``$defs`` for
  JSON Schema, ``components/schemas`` for OpenAPI) and, for JSON Schema, the
  ``$schema`` keyword;
* ``flatten()`` returns a self-contained schema where every reference is
  replaced by its definition. It only keeps the references that close a cycle
  in recursive types.

For example, given the following class::

    // src/Entity/Book.php
    namespace Acme\Entity;

    use Symfony\Component\Validator\Constraints\NotBlank;

    class Book
    {
        public function __construct(
            #[NotBlank]
            public string $title,
            public ?Author $author = null,
        ) {
        }
    }

This is the flattened root schema generated for it::

    $schema = $generator->generate(Type::object(Book::class));
    $rootSchema = $schema->flatten()->getRoot();

    // [
    //     'type' => 'object',
    //     'properties' => [
    //         // NotBlank adds "minLength" and makes the property required
    //         'title' => ['type' => 'string', 'minLength' => 1],
    //         'author' => [...],
    //     ],
    //     'required' => ['title'],
    // ]

Schemas for Functions and Tools
-------------------------------

Use ``generateForParameters()`` to describe the arguments of a function, for
example the input of a tool exposed to an AI agent::

    use Acme\Tool\SearchBooks;

    $method = new \ReflectionMethod(SearchBooks::class, '__invoke');
    $schema = $generator->generateForParameters($method->getParameters());

The root of the schema is an object with one property per parameter. Each
property is described by the type of the parameter, its default value, its
``@param`` description and its ``#[JsonSchemaConstraint]`` attribute (see
:ref:`json-schema-constraint-attribute`). Parameters without a default value
are required. The generator doesn't apply the constraints of the Validator
component to parameters. Reading the ``@param`` descriptions requires
``phpstan/phpdoc-parser`` 2.x.

Configuring the Schema
----------------------

Pass a :class:`Symfony\\Component\\JsonSchema\\Configuration` object as the
second argument of ``generate()`` to change the generated schema::

    use Symfony\Component\JsonSchema\Configuration;
    use Symfony\Component\JsonSchema\Dialect;

    // OpenAPI 3.1 schema restricted to the "book:write" serializer group
    $config = new Configuration(Dialect::openApi31(), ['book:write']);
    $schema = $generator->generate(Type::object(Book::class), $config);

The constructor accepts the following arguments, in this order:

``dialect``
    The :class:`Symfony\\Component\\JsonSchema\\Dialect` of the schema. It
    defaults to JSON Schema 2020-12 (``Dialect::jsonSchema202012()``). The
    other built-in dialects are OpenAPI 3.1 (``Dialect::openApi31()``) and
    OpenAPI 3.0 (``Dialect::openApi30()``). A dialect defines the path of the
    references, the syntax of ``null`` and the available keywords (``const``,
    ``examples``, numeric ``exclusiveMinimum`` and ``exclusiveMaximum`` and
    keywords next to ``$ref``).

``groups``
    The serializer groups used to select the properties.

``attributes``
    The serializer ``attributes`` option: the properties to include.

``ignoredAttributes``
    The properties to exclude, like the ``ignored_attributes`` option of the
    serializer.

``allowExtraAttributes``
    Set it to ``false`` to add ``additionalProperties: false`` to the
    object definitions. It defaults to ``true``.

``validationGroups``
    The validation groups (or a ``GroupSequence``) used to select the
    constraints reflected in the schema. By default, the generator uses the
    groups of the class.

``definitionName`` and ``definitionPrefix``
    Customize the definition names built by the default definition policy.
    ``definitionPrefix`` replaces the short name of the root class.
    ``definitionName`` replaces the suffix built from the other options
    (groups, direction, etc.) in every definition, including the nested ones,
    and is separated from the prefix by a hyphen; an empty name leaves only the
    prefix. Generic classes keep their arguments in the name (see
    :ref:`json-schema-definition-names`).

``format``
    The serializer format, such as ``json``. It's unrelated to the dialect.

``direction``
    A :class:`Symfony\\Component\\JsonSchema\\Direction` case:
    ``Direction::Input`` describes only the properties that can be written,
    and ``Direction::Output`` only the ones that can be read. Without a
    direction, the schema describes both and marks the properties with the
    ``readOnly`` and ``writeOnly`` keywords.

``context``
    Options passed to the extensions and to the PropertyInfo extractors.

.. _json-schema-constraint-attribute:

Describing Properties with Attributes
-------------------------------------

The generator converts the validation constraints of the Validator component,
such as ``NotBlank``, ``Length``, ``Range`` or ``Email``, to the matching
JSON Schema keywords. ``NotBlank`` (unless it allows ``null``) and ``NotNull``
also make the property required. When a property needs more information, add
the :class:`Symfony\\Component\\JsonSchema\\Attribute\\JsonSchemaConstraint`
attribute to a property or to a parameter::

    // src/Tool/SearchBooks.php
    namespace Acme\Tool;

    use Symfony\Component\JsonSchema\Attribute\JsonSchemaConstraint;

    class SearchBooks
    {
        /**
         * @param string $query The words to look for in the title
         */
        public function __invoke(
            // the first argument is the title of the property; its description
            // comes from the @param tag
            #[JsonSchemaConstraint('Search terms')]
            string $query,
            // optional, and its default value is added to the schema
            int $limit = 10,
        ): array {
            // ...
        }
    }

The attribute accepts a title, a description and the usual JSON Schema keywords
(``format``, ``enum``, ``pattern``, ``minimum``, ``maxItems``, ``default``,
``example``, etc.). Its ``required`` argument overrides whether the property is
required.

Sharing Definitions in a Document
---------------------------------

A document such as an OpenAPI specification contains many schemas that
reference the same definitions. Create a
:class:`Symfony\\Component\\JsonSchema\\SchemaDocument` and pass it to each
``generate()`` call. The generator builds each definition only once per
document, and the document gives access to all of them::

    use Acme\Entity\Author;
    use Symfony\Component\JsonSchema\SchemaDocument;

    $document = new SchemaDocument();
    $book = $generator->generate(Type::object(Book::class), $config, $document);
    $author = $generator->generate(Type::object(Author::class), $config, $document);

    // all the definitions of the document, indexed by name
    $definitions = $document->getDefinitions();

A *definition policy* names the definitions. The default one,
:class:`Symfony\\Component\\JsonSchema\\DefinitionPolicy\\ShortNameDefinitionPolicy`,
uses the short name of the class, its generic arguments (if any), the groups,
the direction and the other options that change its shape (e.g.
``Book-book.write_input`` for the ``book:write`` group and the input
direction).

Because the name depends on these arguments and options, the same class can
have several definitions in a document. Use ``getDefinitionClasses()`` to find
the class of a definition, for example to map a ``$ref`` back to its class::

    // indexed by name and in the same order as getDefinitions():
    // ['Book-book.write' => Book::class, 'Author-book.write' => Author::class]
    $classes = $document->getDefinitionClasses();

.. _json-schema-generic-classes:

Generic Classes
---------------

Use a generic class (declared with the ``@template`` PHPDoc tag) when several
properties share the same structure but hold values of different types. The
generator creates a separate definition for each specialization of the class.
It reads the templates and their arguments from PHPDoc, so install the PHPDoc
packages:

.. code-block:: terminal

    $ composer require phpstan/phpdoc-parser phpdocumentor/type-resolver

Without these packages, the generator ignores the type arguments of properties
and all the specializations share the definition of the raw class. The same
happens with TypeInfo versions that don't provide the ``Type::map()`` method.

For example, the following ``Inbox`` class contains an ``Envelope<string>``
and an ``Envelope<Customer>``::

    use Symfony\Component\JsonSchema\SchemaGenerator;
    use Symfony\Component\TypeInfo\Type;

    /** @template T */
    class Envelope
    {
        /** @var T */
        public mixed $data;
        public string $status;
    }

    class Customer
    {
        public string $name;
    }

    class Inbox
    {
        /** @var Envelope<string> */
        public Envelope $message;

        /** @var Envelope<Customer> */
        public Envelope $customer;
    }

    $generator = SchemaGenerator::create();
    $schema = $generator->generate(Type::object(Inbox::class));

The generated definitions include a separate schema for each
specialization::

    $schema->getDefinitions();
    // [
    //     'Inbox' => ['type' => 'object', 'properties' => [
    //         'message' => ['$ref' => '#/$defs/Envelope-of.string'],
    //         'customer' => ['$ref' => '#/$defs/Envelope-of.Customer'],
    //     ]],
    //     'Envelope-of.string' => ['type' => 'object', 'properties' => [
    //         'data' => ['type' => 'string'],
    //         'status' => ['type' => 'string'],
    //     ]],
    //     'Envelope-of.Customer' => ['type' => 'object', 'properties' => [
    //         'data' => ['$ref' => '#/$defs/Customer'],
    //         'status' => ['type' => 'string'],
    //     ]],
    //     'Customer' => ['type' => 'object', 'properties' => [
    //         'name' => ['type' => 'string'],
    //     ]],
    // ]

To describe a generic class as the root, pass its arguments to
``Type::generic()``::

    // without the PHPDoc packages, the definition is still named
    // "Envelope-of.Customer", but its "data" property accepts any value
    $schema = $generator->generate(Type::generic(
        Type::object(Envelope::class),
        Type::object(Customer::class),
    ));

The generator replaces the templates with their arguments everywhere in the
property types: nested generic classes, collections, nullable types, union
types, intersections and array or object shapes. Without arguments, ``Envelope``
uses the bounds of its templates, so ``data`` accepts any value. An explicit
``Envelope<mixed>`` creates a separate ``Envelope-of.mixed`` definition with
the same property schemas.

Template Declarations
~~~~~~~~~~~~~~~~~~~~~

Arguments follow the order of the template declarations of the class, and the
missing ones use the declared bounds::

    /**
     * @template T
     * @template U of int
     */
    class Pair
    {
        /** @var T */
        public mixed $first;

        /** @var U */
        public mixed $second;
    }

``Pair<string>`` describes ``first`` as a string and ``second`` as an integer.
``Pair<string, int>`` has the same property schemas, but a different definition
name: ``Pair-of.string.int`` instead of ``Pair-of.string``, because omitted
arguments aren't added to the name. Extra arguments don't change any property,
but they are added to the name because extensions can use them.

Each class uses its own arguments. In ``Envelope<Pair<string, int>>``, the
``data`` property of the envelope references ``Pair-of.string.int``, whose
``first`` and ``second`` properties are a string and an integer.

The generator supports the ``@template``, ``@template-covariant`` and
``@template-contravariant`` tags, including their ``@phpstan-`` and ``@psalm-``
prefixed forms. A template declared with several of these tags counts as a
single argument.

If you pass your own PropertyInfo extractor to the ``SchemaGenerator``
constructor or replace the PropertyInfo type extractors of your application,
they must return a TypeInfo ``TemplateType`` for the properties typed with a
template, as ``PhpStanExtractor`` does. The ``phpstan/phpdoc-parser`` package
is still required to read the template declarations of the classes.

Inherited Generic Properties
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

With the built-in PHPDoc extractor, TypeInfo resolves the inherited types using
the ``@extends`` and ``@implements`` tags, and the types of trait properties,
getters and setters using the ``@use`` tag. This happens before the generator
replaces the templates with the arguments of the class, so a child class can
name its templates differently from its parent::

    /**
     * @template T
     * @template U
     * @extends Envelope<U>
     */
    class ChildEnvelope extends Envelope
    {
        /** @var T */
        public mixed $extra;
    }

    // "extra" is an integer and "data" (inherited from Envelope) is a Customer
    $schema = $generator->generate(Type::generic(
        Type::object(ChildEnvelope::class),
        Type::int(),
        Type::object(Customer::class),
    ));

Concrete parent arguments, such as ``@extends Envelope<string>``, stay the
same whatever the arguments of the child class.

Without a matching ``@extends``, ``@implements`` or ``@use`` tag, the templates
of a parent class, interface or trait take the arguments of the templates with
the same name in the described class. The other templates keep their bounds.

Collections and Pagination
~~~~~~~~~~~~~~~~~~~~~~~~~~

PropertyInfo extracts the PHPDoc types of classes that implement
``Traversable`` or ``ArrayAccess`` as collections. For example, if ``Page``
implements ``IteratorAggregate``, a ``Page<Customer>`` property is described as
an array of ``Customer`` objects, without the other properties of ``Page``
(such as ``total``).

To describe pagination as an object with ``items`` and ``total`` properties,
use a class that doesn't implement these interfaces::

    /** @template T */
    class Page
    {
        /** @var list<T> */
        public array $items;
        public int $total;
    }

A ``Page<Customer>`` property then references ``Page-of.Customer``, with an
array of customers in ``items`` and an integer in ``total``.

The Serializer also normalizes ``Traversable`` objects as collections, but the
``ObjectNormalizer`` turns a class that only implements ``ArrayAccess`` into an
object (e.g. ``{"items": [...], "total": 1}``), while the schema describes it
as an array. To make the schema match that output, remove the interface or
replace the schema of the property with a property schema provider (see
:ref:`json-schema-extending-generator`).

Passing a ``Traversable`` class to ``generate()`` as an object type (e.g.
``Type::generic(Type::object(Page::class), ...)``) describes it as an object,
but the Serializer still normalizes it as a collection.

.. _json-schema-definition-names:

Definition Names
~~~~~~~~~~~~~~~~

The default definition policy adds readable labels for the built-in types and
the classes used as arguments (e.g. ``Envelope-of.Customer`` and
``Pair-of.string.int``). Nested generic classes and lists build these labels
recursively: ``Envelope<Envelope<Customer>>`` becomes
``Envelope-of.Envelope_Customer`` and ``Envelope<list<Customer>>`` becomes
``Envelope-of.list_Customer``.

Other arguments, such as nullable types, non-list collections
(``array<Customer>``, ``Customer[]``), unions, intersections, shapes and the
generic classes that contain them, use a hash instead (e.g.
``Envelope<int|string>`` becomes ``Envelope-of.generic.`` followed by a hash).
Labels longer than 128 bytes also use a hash. The hash only depends on the
argument types, so it's the same in every run.

The suffix built from the configuration comes after the arguments. When
``definitionName`` is ``'Custom'``, the definitions are named
``Envelope-of.Customer-Custom`` and ``Customer-Custom``; when it's empty, the
root definition is named ``Envelope-of.Customer``. When ``definitionPrefix``
is ``'Result'`` too, the root definition is named ``Result-of.Customer-Custom``.

When the short name of a class is already used by another class in the
document, the labels can include some namespace segments (e.g.
``Envelope-of.Billing.Customer``). If two specializations still get the same
name, the policy appends a hash. Names can therefore depend on the order in
which the classes are described, but a specialization keeps its name for a
given configuration in the same document, even after a failed generation. Use
a new document to start from scratch, or a custom definition policy when names
must not depend on that order (see :ref:`json-schema-extending-generator`).

Configuration labels and custom names can still collide. For example,
describing ``Customer`` with the ``book:write`` group and then with the
``book.write`` group in the same document creates the ``Customer-book.write``
name twice, so the second call fails. A custom policy must give different
names to different types and configurations.

When exporting the definitions to another schema format, use
``getDefinitionTypes()`` to keep their generic arguments. It returns the
``ObjectType`` or ``GenericType`` of each definition, indexed by name::

    use Symfony\Component\JsonSchema\Configuration;
    use Symfony\Component\JsonSchema\SchemaDocument;
    use Symfony\Component\TypeInfo\Type\GenericType;

    $document = new SchemaDocument();
    $generator->generate(Type::object(Inbox::class), new Configuration(), $document);

    foreach ($document->getDefinitionTypes() as $name => $type) {
        if ($type instanceof GenericType) {
            $class = $type->getWrappedType()->getClassName();
            $arguments = $type->getVariableTypes();
        } else {
            $class = $type->getClassName();
            $arguments = [];
        }

        // map $name to $class and $arguments in the target format
    }

With the default definition policy, types with the same string representation
share a definition for a given configuration, and ``getDefinitionTypes()``
returns the first one used to generate it. For example, when ``Status`` is an
enum, ``Envelope`` with a ``Type::enum(Status::class)`` argument and with a
``Type::object(Status::class)`` argument share the same definition.

Limitations
~~~~~~~~~~~

* The generic arguments of a class with a discriminator map aren't passed to
  its mapped subclasses;
* A template used in an intersection, such as ``T&Named``, can't receive a
  union argument, such as ``Foo|Bar``;
* The generator doesn't check the arguments against the bounds or the variance
  of the templates.

Recursive generic classes are supported: in a ``Node<T>`` class with a
``Node<T>|null`` property, ``Node<string>`` uses a single ``Node-of.string``
definition that references itself. When the arguments still contain a
template (e.g. ``Node`` used without arguments), the generator uses the
definition of the raw class (``Node``).

However, a ``GrowingNode<T>`` class with a ``GrowingNode<list<T>>|null``
property would create larger and larger specializations. To prevent this, the
generator uses the definition of the raw class when the arguments are nested
more than 64 levels deep or contain more than 1,024 TypeInfo types. Very deep
or large arguments can reach these limits even without recursion. Extensions
that describe other types must avoid infinite recursion themselves.

.. _json-schema-extending-generator:

Extending the Generator
-----------------------

``SchemaGenerator::create()`` accepts a definition policy and extensions that
run together with the built-in ones. Each extension receives the
``SchemaDocument``, whose ``describe()`` method generates the schema of
another type in the same document:

:class:`Symfony\\Component\\JsonSchema\\DefinitionPolicy\\DefinitionPolicyInterface`
    Names the definition of a class. The name identifies the definition, so
    it must include everything that changes its shape, including generic
    arguments and configuration.

:class:`Symfony\\Component\\JsonSchema\\ClassSchemaResolver\\ClassSchemaResolverInterface`
    Describes a class with an inline schema instead of a definition, for
    example value objects serialized as strings. The built-in resolver handles
    dates, UIDs, enums and a few other classes normalized to scalars.

:class:`Symfony\\Component\\JsonSchema\\PropertySchemaProvider\\PropertySchemaProviderInterface`
    Replaces the whole schema of a property, taking precedence over a schema
    generated from its type.

:class:`Symfony\\Component\\JsonSchema\\PropertySchemaEnricher\\PropertySchemaEnricherInterface`
    Refines the schema of a property, for example to add keywords or to
    change whether the property is required.

:class:`Symfony\\Component\\JsonSchema\\DefinitionProcessor\\DefinitionProcessorInterface`
    Post-processes each object definition once its properties are built.

Definition policies, class schema resolvers and definition processors receive
an ``ObjectType`` or, for a generic class, a ``GenericType`` that wraps the
``ObjectType`` and whose ``getVariableTypes()`` method returns the arguments
in order. Decorators must forward the complete type. When you call these
extensions yourself, pass ``Type::object($class)`` for a regular class and the
complete ``GenericType`` for a specialization.

For example, this processor adds a title with the class and its generic
arguments (if any) to each definition::

    // src/JsonSchema/TitleDefinitionProcessor.php
    namespace Acme\JsonSchema;

    use Symfony\Component\JsonSchema\Configuration;
    use Symfony\Component\JsonSchema\DefinitionParent;
    use Symfony\Component\JsonSchema\DefinitionProcessor\DefinitionProcessorInterface;
    use Symfony\Component\JsonSchema\SchemaDocument;
    use Symfony\Component\TypeInfo\Type\GenericType;
    use Symfony\Component\TypeInfo\Type\ObjectType;

    class TitleDefinitionProcessor implements DefinitionProcessorInterface
    {
        /**
         * @param array<string, mixed> $definition
         *
         * @return array<string, mixed>
         */
        public function process(
            array $definition,
            ObjectType|GenericType $type,
            Configuration $config,
            SchemaDocument $document,
            ?DefinitionParent $parent,
        ): array {
            $definition['title'] = (string) $type;

            return $definition;
        }
    }

When an extension only needs the class, get it from the wrapped type::

    $objectType = $type instanceof GenericType ? $type->getWrappedType() : $type;
    $class = $objectType->getClassName();

Class schema resolvers and definition processors can describe a type argument
in the same document by passing it to ``$document->describe()``. The
``DefinitionParent::$class`` property contains the class of the parent
definition, without its generic arguments.

Property schema providers identify a property by the name of its class and its
own name, so a provider for ``Envelope::$data`` replaces the schema of that
property in every specialization. To adapt the schema to the resolved type,
use an enricher instead: its ``PropertySchema::$type`` contains ``string`` in
``Envelope<string>`` and the ``T`` template when ``Envelope`` has no
arguments.

The class schema resolvers passed to ``create()`` run before the built-in one,
and the property schema enrichers run after the built-in ones (Validator
constraints and ``#[JsonSchemaConstraint]``). There are no built-in property
schema providers or definition processors::

    use Acme\JsonSchema\ExamplesPropertySchemaEnricher;
    use Acme\JsonSchema\MoneyClassSchemaResolver;
    use Acme\JsonSchema\TitleDefinitionProcessor;

    $generator = SchemaGenerator::create(
        // the definition policy (null uses ShortNameDefinitionPolicy)
        null,
        // the class schema resolvers
        [new MoneyClassSchemaResolver()],
        // the property schema providers
        [],
        // the property schema enrichers
        [new ExamplesPropertySchemaEnricher()],
        // the definition processors
        [new TitleDefinitionProcessor()],
    );

Using the Generator in Symfony Applications
-------------------------------------------

In a Symfony application, the FrameworkBundle registers the
:class:`Symfony\\Component\\JsonSchema\\JsonSchemaBundle` as soon as the
component is installed. It requires no configuration and lets you inject the
generator as a service::

    // src/Service/BookSchemaFactory.php
    namespace App\Service;

    use App\Entity\Book;
    use Symfony\Component\JsonSchema\SchemaGeneratorInterface;
    use Symfony\Component\TypeInfo\Type;

    class BookSchemaFactory
    {
        public function __construct(
            private SchemaGeneratorInterface $schemaGenerator,
        ) {
        }

        public function createSchema(): array
        {
            $schema = $this->schemaGenerator->generate(Type::object(Book::class));

            return $schema->toArray();
        }
    }

When the Serializer and Validator components are enabled, the generator
follows their mapping. It also describes dates and UIDs according to the
``datetime_format`` and ``uid_normalization_format`` options of the default
serializer context.

Symfony autoconfigures the services that implement one of the extension
interfaces described in the previous section with the following tags:

* ``json_schema.class_schema_resolver``
* ``json_schema.property_schema_provider``
* ``json_schema.property_schema_enricher``
* ``json_schema.definition_processor``

The built-in class schema resolver runs last, so your own resolvers (default
priority ``0``) can describe dates, UIDs and enums in a different way. The
Validator enricher runs first and the ``#[JsonSchemaConstraint]`` enricher
runs last, so your own enrichers run in between.

.. _`JSON Schema`: https://json-schema.org/
