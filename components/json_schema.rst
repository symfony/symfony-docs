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
    The properties to exclude.

``allowExtraAttributes``
    Set it to ``false`` to add ``additionalProperties: false`` to the
    object definitions. It defaults to ``true``.

``validationGroups``
    The validation groups (or a ``GroupSequence``) used to select the
    constraints reflected in the schema. By default, the generator uses the
    groups of the class.

``definitionName`` and ``definitionPrefix``
    Customize the name of the definition of the root class. The prefix replaces
    the short name of the class. The name replaces the suffix built from the
    other options (groups, direction, etc.) and is separated from the prefix
    by a hyphen; an empty name leaves only the prefix.

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
            // not required because it has a default value
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
    it must include everything that changes the shape of the definition.

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

The class schema resolvers passed to ``create()`` run before the built-in one,
and the property schema enrichers run after the built-in ones (Validator
constraints and ``#[JsonSchemaConstraint]``). There are no built-in property
schema providers or definition processors::

    use Acme\JsonSchema\ExamplesPropertySchemaEnricher;
    use Acme\JsonSchema\MoneyClassSchemaResolver;

    $generator = SchemaGenerator::create(
        // the definition policy (null uses ShortNameDefinitionPolicy)
        null,
        // the class schema resolvers
        [new MoneyClassSchemaResolver()],
        // the property schema providers
        [],
        // the property schema enrichers
        [new ExamplesPropertySchemaEnricher()],
    );

.. _`JSON Schema`: https://json-schema.org/
