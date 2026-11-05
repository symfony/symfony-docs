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
component to parameters.

PHPDoc parameter descriptions require ``phpstan/phpdoc-parser`` 2.x.

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
    With the default definition policy, ``definitionPrefix`` replaces the short
    class name used to prefix the root definition. ``definitionName`` replaces
    the configuration suffix of every definition, including nested ones.
    A custom suffix is separated from the class prefix by a hyphen; an empty
    name leaves only the prefix for ordinary classes. Generic specializations
    retain their argument suffix, giving ``Envelope-of.Customer-Custom`` with
    a custom name or ``Envelope-of.Customer`` with an empty name.

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
uses the short name of the class, any generic arguments, the groups, the
direction and the other options that change its shape (e.g.
``Book-book.write_input`` for the ``book:write`` group and the input direction).

Because the name depends on the type arguments and these options, the same class
can have several definitions in a document. Use ``getDefinitionClasses()`` to
find the class of a definition, for example to map a ``$ref`` back to its
class::

    // indexed by name and in the same order as getDefinitions():
    // ['Book-book.write' => Book::class, 'Author-book.write' => Author::class]
    $classes = $document->getDefinitionClasses();

.. _json-schema-generic-classes:

Generic Classes
---------------

.. versionadded:: 8.3

    Support for generic class arguments was introduced in Symfony 8.3.

To describe PHPDoc-typed properties with the default generator, install the
optional PHPDoc packages:

.. code-block:: terminal

    $ composer require phpstan/phpdoc-parser phpdocumentor/type-resolver

This applies both to ``SchemaGenerator::create()`` and to the generator service
in Symfony applications. Without PHPDoc support, type arguments declared on
properties are ignored and different specializations share the native class's
definition.

Use a generic DTO when several properties share the same structure but contain
values of different types. For example, the following ``Inbox`` contains an
``Envelope<string>`` and an ``Envelope<Customer>``::

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

The generated definitions include a separate schema for each specialization::

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

You can also describe a generic root by passing its arguments explicitly::

    $schema = $generator->generate(Type::generic(
        Type::object(Envelope::class),
        Type::object(Customer::class),
    ));

Without the optional PHPDoc packages, this explicit root keeps its
``Envelope-of.Customer`` name, but its properties are not specialized:
``data`` accepts any value.

Arguments are substituted in nested generic classes, collections, nullable and
union types, supported intersections, and array and object shapes. Without
arguments, ``Envelope`` uses its template bounds: ``data`` accepts any value.
An explicit ``Envelope<mixed>`` produces a separate ``Envelope-of.mixed``
definition with the same property schemas.

Template Declarations
~~~~~~~~~~~~~~~~~~~~~

Arguments follow the order of the class's template declarations. Missing
arguments use the declared bounds. For example::

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
``Pair<string, int>`` has the same property schemas, but the definition names
are different: ``Pair-of.string`` and ``Pair-of.string.int``. Omitted arguments
are not added to the name. Extra arguments affect names without assigning
additional property types, because extensions can use them.

Each class uses its own arguments. In ``Envelope<Pair<string, int>>``, the
envelope's ``data`` references ``Pair-of.string.int``, whose ``first`` and
``second`` properties use ``string`` and ``int`` respectively.

PHPStan- and Psalm-prefixed template tags also work. Repeated aliases of the
same template name occupy one argument position. Supported phpdoc-parser 1.x
and 2.x versions both work for generic properties.

Use plain ``@template`` declarations with the built-in extractor for
compatibility with all supported TypeInfo versions. Covariant declarations
(``@template-covariant`` and their vendor prefixes) require support from
TypeInfo. Contravariant declarations (``@template-contravariant``) are not
supported.

.. versionadded:: 8.2

    Support for ``@template-covariant`` declarations in the TypeInfo component was
    introduced in Symfony 8.2.

An unsupported declaration still occupies an argument position, so later
templates receive the correct arguments. Properties using it are described as
if the class had no arguments; its bound may not be extracted either.

If you pass your own PropertyInfo extractor to ``new SchemaGenerator()`` or
replace your application's PropertyInfo type extractors, they must return
TypeInfo ``TemplateType`` values for properties typed with a template, as
``PhpStanExtractor`` does. Keep ``phpstan/phpdoc-parser`` installed so JsonSchema
can read the class's template declarations.

Collections and Pagination
~~~~~~~~~~~~~~~~~~~~~~~~~~

PHPDoc types for classes implementing ``Traversable`` or ``ArrayAccess`` are
extracted as collections. For example, a ``Page<Customer>`` property whose
``Page`` implements ``IteratorAggregate`` is described as an array of
``Customer``. Extra properties such as ``total`` are not included.

To describe pagination as an object with ``items`` and ``total``, use a DTO
without these interfaces::

    /** @template T */
    class Page
    {
        /** @var list<T> */
        public array $items;
        public int $total;
    }

A ``Page<Customer>`` property then references ``Page-of.Customer``, with an
array of customers in ``items`` and an integer ``total``.

The Serializer also normalizes ``Traversable`` values as collections. For a
class that implements only ``ArrayAccess``, the default ``ObjectNormalizer``
produces an object such as ``{"items": [...], "total": 1}``, while its generic
PHPDoc type is described as an array. To match the schema to that output, use a
DTO without these interfaces or replace the schema of the property that uses
it with a property schema provider (see
:ref:`json-schema-extending-generator`).

Passing a pagination class as an explicit object type to ``generate()``
bypasses PHPDoc collection handling; it does not change how the Serializer
normalizes a ``Traversable`` value.

Definition Names
~~~~~~~~~~~~~~~~

Built-in type and class arguments use readable labels, such as
``Envelope-of.Customer`` and ``Pair-of.string.int``. Nullable types,
collections, unions, intersections, shapes and nested generic arguments use a
hash instead. For example, ``Envelope<int|string>`` produces
``Envelope-of.generic.95cee375039d6796``. Readable labels longer than 128 bytes
also use a hash. The hash is derived from the argument types, so the same types
produce the same argument hash between runs.

The configuration suffix follows the arguments. Setting ``definitionName`` to
``'Custom'`` gives ``Envelope-of.Customer-Custom`` and also names its nested
definition ``Customer-Custom``. An empty ``definitionName`` keeps
``Envelope-of.Customer``. Setting ``definitionPrefix`` to ``'Result'`` changes
only the root prefix, giving ``Result-of.Customer-Custom`` with that suffix.

When a class short name is already used by another class in the document,
argument labels can include namespace segments, as in
``Envelope-of.Billing.Customer``. If two specializations still receive the same
definition name, the default policy appends a hash. Names can therefore depend
on the order in which classes are described. Once a specialization has a name
for a given configuration, later calls reuse that name in the same document.

If generation fails, the document state is rolled back, but the default naming
policy keeps the names it selected. A retry reuses those names; a later type may
receive a suffix it would not receive in a fresh document. Use a new document
when you need fresh naming history, or a custom definition policy when names
must be independent of generation order (see
:ref:`json-schema-extending-generator`).

Configuration labels or custom names can still collide and prevent schema
generation. For example, describing ``Customer`` with the groups ``book:write``
and then ``book.write`` in the same document produces the name
``Customer-book.write`` twice, so the second call fails. A custom policy must
assign distinct names to distinct types and configurations.
``SchemaDocument::getDefinitionClasses()`` maps each definition name to its
base class; several definitions can map to the same class.

When exporting definitions to another schema format, use
``SchemaDocument::getDefinitionTypes()`` to keep their generic arguments. It
returns each definition's ``ObjectType`` or ``GenericType``, indexed by name::

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

Different groups or directions can produce several definitions for the same
type. With the default naming policy, types with the same string representation
share a definition for the same configuration. The getter keeps the type used
to generate it first. For example, ``Type::enum(Status::class)`` and
``Type::object(Status::class)`` arguments for ``Envelope`` share one definition
when ``Status`` is an enum.

Limitations
~~~~~~~~~~~

JsonSchema does not resolve generic ``@extends``, ``@implements`` or trait
``@use`` declarations, or infer discriminator subclass arguments. Inherited
properties can use the child's argument when the child reuses the parent's
template name, as in ``Child<T> extends Base<T>``. A fixed parent argument can
still be interpreted incorrectly::

    /**
     * @template T
     * @extends Envelope<string>
     */
    class ChildEnvelope extends Envelope
    {
    }

``ChildEnvelope<int>`` can describe inherited ``data`` as an integer, even
though ``Envelope<string>`` requires a string. Use concrete property types or
an extractor that resolves these relationships when needed.

A template used in an intersection, such as ``T&Named``, cannot receive a union
argument such as ``Foo|Bar``. Type arguments are not validated against their
declared bounds or variance.

Some TypeInfo versions deduplicate union members by their string representation.
Distinct types with the same string representation, such as a ``GenericType``
and a ``CollectionType`` wrapping it, can therefore lose an alternative during
substitution. Intersection substitution can also discard distinct members with
the same string representation.

Normal recursion works: a ``Node<T>`` with a ``Node<T>|null`` property references
its own definition. For example, ``Node<string>`` uses one ``Node-of.string``
definition. A ``GrowingNode<T>`` with a ``GrowingNode<list<T>>|null`` property
keeps creating larger types instead, and is stopped by the expansion limits.

The generator limits each specialization's argument list and each property
type that needs template substitution to 1,024 type nodes, 64 nesting levels
and 64 KiB of estimated type text. Each occurrence of an argument counts, and
each property is checked separately.

These limits can also reject finite types. A property whose array shape declares
over a thousand entries typed as ``T`` can work when the class is used without
arguments but exceed a limit when specialized, even with ``int`` as the argument.

Exceeding a limit stops generation and rolls back document state. Xdebug may
stop recursion earlier at its own frame limit. Extensions remain responsible
for avoiding unbounded recursion when they describe additional types.

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

Class-level hooks (naming policies, class resolvers and definition processors)
receive an ``ObjectType`` or a ``GenericType`` wrapping an ``ObjectType``.
``GenericType::getVariableTypes()`` returns its ordered arguments. Decorators
must forward the complete type. When calling a definition policy, class
resolver or definition processor yourself, pass ``Type::object($class)`` for
an ordinary class and the complete ``GenericType`` for a specialization.

.. versionadded:: 8.3

    Passing the complete object type to class-level extension hooks was introduced
    in Symfony 8.3.

For example, this processor adds a title containing the class and any generic
arguments, even when no property uses a template::

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

An extension that needs only the base class can extract it from the type::

    $objectType = $type instanceof GenericType ? $type->getWrappedType() : $type;
    $class = $objectType->getClassName();

Property schema providers identify a property by its class and property names,
so a provider for ``Envelope::$data`` replaces that property's schema in every
specialization. To adapt a property schema to its resolved type, use an
enricher: ``PropertySchema::$type`` contains ``string`` in ``Envelope<string>``
or the template type ``T`` when ``Envelope`` is used without arguments.

Class resolvers and definition processors can describe a type argument in the
same document by passing it to ``$document->describe()``.
``DefinitionParent::$class`` contains the owning class name without its generic
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
