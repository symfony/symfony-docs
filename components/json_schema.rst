The JsonSchema Component
========================

The JsonSchema component generates `JSON Schema`_ documents from PHP types.
Use it to describe your API in an OpenAPI specification, or to describe the
input and the output of tools exposed to AI agents.

The component turns a :doc:`TypeInfo </components/type_info>` type, or the
parameters of a function, into a schema. The properties of a class come from
the :doc:`PropertyInfo component </components/property_info>`, so the
serializer groups and attributes, the name converter, the discriminator maps
and the validation constraints are taken into account when they are available.

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

Usage
-----

Create a :class:`Symfony\\Component\\JsonSchema\\SchemaGenerator` with its
``create()`` method and call ``generate()`` with the type to describe. The
generator takes advantage of the Serializer and Validator components, and of
the PHPDoc parsers, when they are installed::

    use Acme\Entity\Book;
    use Symfony\Component\JsonSchema\SchemaGenerator;
    use Symfony\Component\TypeInfo\Type;

    $generator = SchemaGenerator::create();
    $schema = $generator->generate(Type::object(Book::class));

    echo json_encode($schema, \JSON_PRETTY_PRINT);

The result is a :class:`Symfony\\Component\\JsonSchema\\Schema` object. It
holds a root schema and the named definitions it references:

* ``getRoot()`` returns the root schema;
* ``getDefinitions()`` returns the definitions, indexed by name;
* ``toArray()`` (also used by ``json_encode()``) returns a complete document,
  with the definitions placed where the dialect expects them (``$defs`` for
  JSON Schema, ``components/schemas`` for OpenAPI) and, for JSON Schema, the
  ``$schema`` keyword;
* ``flatten()`` returns a self-contained schema where every reference is
  replaced by its definition. Only the references that close a cycle in
  recursive types are kept.

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

The generated schema looks like this::

    $schema = $generator->generate(Type::object(Book::class))->flatten()->getRoot();

    // [
    //     'type' => 'object',
    //     'properties' => [
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
are required. The constraints of the Validator component are not applied to
parameters.

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
    references, the syntax of ``null`` and which keywords are available
    (``const``, ``examples``, numeric ``exclusiveMinimum`` and
    ``exclusiveMaximum`` or ``$ref`` siblings).

``groups``
    The serializer groups used to select the properties.

``attributes``
    The serializer ``attributes`` option: the properties to include.

``ignoredAttributes``
    The properties to exclude.

``allowExtraAttributes``
    Set it to ``false`` to add ``additionalProperties: false`` to the
    object definitions. It defaults to ``true``.

``validationGroups``
    The validation groups (or a ``GroupSequence``) used to pick the
    constraints that are reflected in the schema. By default, the groups of
    the class are used.

``definitionName`` and ``definitionPrefix``
    Customize the name of the definition of the root class. The prefix replaces
    the short name of the class. The name replaces the suffix built from the
    other options (groups, direction, etc.) and is separated from the prefix
    by a hyphen; an empty name leaves the prefix alone.

``format``
    The serializer format, such as ``json``. It is unrelated to the dialect.

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

The validation constraints of the Validator component, such as ``NotBlank``,
``Length``, ``Range`` or ``Email``, are converted to the matching JSON Schema
keywords. ``NotBlank`` (unless it allows ``null``) and ``NotNull`` also make
the property required. When a property needs more information, add the
:class:`Symfony\\Component\\JsonSchema\\Attribute\\JsonSchemaConstraint`
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
            #[JsonSchemaConstraint('Search terms')]
            string $query,
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
``generate()`` call: a definition is built once per document, and the
definitions are available from the document::

    use Acme\Entity\Author;
    use Symfony\Component\JsonSchema\SchemaDocument;

    $document = new SchemaDocument();
    $book = $generator->generate(Type::object(Book::class), $config, $document);
    $author = $generator->generate(Type::object(Author::class), $config, $document);

    // all the definitions of the document, indexed by name
    $definitions = $document->getDefinitions();

Definitions are named by a *definition policy*. The default one,
:class:`Symfony\\Component\\JsonSchema\\DefinitionPolicy\\ShortNameDefinitionPolicy`,
uses the short name of the class followed by the groups, the direction and
the other options that change its shape (e.g. ``Book-book.write_input`` for
the ``book:write`` group and the input direction).

Extending the Generator
-----------------------

``SchemaGenerator::create()`` accepts a definition policy and extensions that
run together with the built-in ones. Each extension receives the
``SchemaDocument``, whose ``describe()`` method generates the schema of
another type in the same document:

:class:`Symfony\\Component\\JsonSchema\\DefinitionPolicy\\DefinitionPolicyInterface`
    Names the definition of a class. The name identifies the definition, so
    everything that changes its shape must be part of it.

:class:`Symfony\\Component\\JsonSchema\\ClassSchemaResolver\\ClassSchemaResolverInterface`
    Describes a class with an inline schema instead of a definition, for
    example value objects serialized as strings. The built-in resolver handles
    dates, UIDs, enums and a few other classes normalized to scalars.

:class:`Symfony\\Component\\JsonSchema\\PropertySchemaProvider\\PropertySchemaProviderInterface`
    Replaces the whole schema of a property.

:class:`Symfony\\Component\\JsonSchema\\PropertySchemaEnricher\\PropertySchemaEnricherInterface`
    Refines the schema of a property, for example to add keywords or to
    change whether the property is required.

:class:`Symfony\\Component\\JsonSchema\\DefinitionProcessor\\DefinitionProcessorInterface`
    Post-processes each object definition once its properties are built.

The class schema resolvers passed to ``create()`` run before the built-in one.
The property schema enrichers run after the built-in ones (Validator
constraints and ``#[JsonSchemaConstraint]``) and so do the definition
processors. There are no built-in property schema providers or definition
processors::

    $generator = SchemaGenerator::create(
        null,
        [new MoneyClassSchemaResolver()],
        [],
        [new ExamplesPropertySchemaEnricher()],
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

The generator follows the mapping of the Serializer and Validator components
when they are enabled, and dates and UIDs are described according to the
``datetime_format`` and ``uid_normalization_format`` options of the default
serializer context.

Services that implement one of the extension interfaces described in the
previous section are autoconfigured with the following tags:

* ``json_schema.class_schema_resolver``
* ``json_schema.property_schema_provider``
* ``json_schema.property_schema_enricher``
* ``json_schema.definition_processor``

The built-in class schema resolver runs last, so your own resolvers (default
priority ``0``) can describe dates, UIDs and enums in a different way. The
Validator enricher runs first and the ``#[JsonSchemaConstraint]`` enricher runs
last, so your own enrichers run in between.

.. _`JSON Schema`: https://json-schema.org/
