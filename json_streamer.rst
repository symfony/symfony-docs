Streaming JSON
==============

.. versionadded:: 7.3

    The JsonStreamer component was introduced in Symfony 7.3.

Symfony can encode PHP data structures to JSON streams and decode JSON streams
back into PHP data structures.

To do so, it relies on the **JsonStreamer** component, which is designed for
high efficiency and can process large JSON data incrementally without needing
to load the entire content into memory.

This component is ideal for handling APIs or interacting with third-party APIs.
It transforms incoming JSON request payloads into PHP objects that your
application can work with. Similarly, it converts processed PHP objects into a
JSON stream for outgoing responses.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the JsonStreamer component:

.. code-block:: terminal

    $ composer require symfony/json-streamer

.. include:: /components/require_autoload.rst.inc

Encoding Objects
----------------

JsonStreamer only works with PHP classes that have **no constructor** and are
composed solely of **public properties**, like `DTO classes`_. Consider the
following ``Cat`` class::

    // src/Dto/Cat.php
    namespace App\Dto;

    class Cat
    {
        public string $name;
        public string $age;
    }

To encode ``Cat`` objects into a JSON stream (e.g., to send them in an API
response), first apply the ``#[JsonStreamable]`` attribute to the class. This
attribute is optional, but it :ref:`improves performance <json-streamer-streamable-attribute>`
by pre-generating encoding and decoding files during cache warm-up::

    namespace App\Dto;

    use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

    #[JsonStreamable]
    class Cat
    {
        // ...
    }

Next, inject the JSON stream writer into your service. The service ``id`` is
``json_streamer.stream_writer``, but you can also get it by type-hinting a
``$jsonStreamWriter`` argument with :class:`Symfony\\Component\\JsonStreamer\\StreamWriterInterface`.

Use the :method:`Symfony\\Component\\JsonStreamer\\StreamWriterInterface::write`
method of the service to perform the actual JSON conversion:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Controller/CatController.php
        namespace App\Controller;

        use App\Dto\Cat;
        use App\Repository\CatRepository;
        use Symfony\Component\HttpFoundation\StreamedResponse;
        use Symfony\Component\JsonStreamer\StreamWriterInterface;
        use Symfony\Component\TypeInfo\Type;

        class CatController
        {
            public function retrieveCats(StreamWriterInterface $jsonStreamWriter, CatRepository $catRepository): StreamedResponse
            {
                $cats = $catRepository->findAll();
                $type = Type::list(Type::object(Cat::class));

                $json = $jsonStreamWriter->write($cats, $type);

                return new StreamedResponse($json);
            }
        }

    .. code-block:: php-standalone

        use App\Dto\Cat;
        use App\Repository\CatRepository;
        use Symfony\Component\HttpFoundation\StreamedResponse;
        use Symfony\Component\JsonStreamer\JsonStreamWriter;
        use Symfony\Component\TypeInfo\Type;

        // ...

        $jsonWriter = JsonStreamWriter::create();

        $cats = $catRepository->findAll();
        $type = Type::list(Type::object(Cat::class));

        $json = $jsonWriter->write($cats, $type);

        $response = new StreamedResponse($json);

        // ...

.. tip::

    You can explicitly inject the ``json_streamer.stream_writer`` service by
    using the ``#[Target('json_streamer.stream_writer')]`` autowire attribute.

Decoding Objects
----------------

In addition to encoding, you can decode JSON into PHP objects.

To do this, inject the JSON stream reader into your service. The service ``id`` is
``json_streamer.stream_reader``, but you can also get it by type-hinting a
``$jsonStreamReader`` argument with :class:`Symfony\\Component\\JsonStreamer\\StreamReaderInterface`.
Next, use the :method:`Symfony\\Component\\JsonStreamer\\StreamReaderInterface::read`
method to perform the actual JSON parsing:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Service/TombolaService.php
        namespace App\Service;

        use App\Dto\Cat;
        use Symfony\Component\DependencyInjection\Attribute\Autowire;
        use Symfony\Component\JsonStreamer\StreamReaderInterface;
        use Symfony\Component\TypeInfo\Type;

        class TombolaService
        {
            private string $catsJsonFile;

            public function __construct(
                private StreamReaderInterface $jsonStreamReader,
                #[Autowire(param: 'kernel.root_dir')]
                string $rootDir,
            ) {
                $this->catsJsonFile = sprintf('%s/var/cats.json', $rootDir);
            }

            public function pickTheTenthCat(): ?Cat
            {
                $jsonResource = fopen($this->catsJsonFile, 'r');
                $type = Type::iterable(Type::object(Cat::class));

                /** @var iterable<Cat> $cats */
                $cats = $this->jsonStreamReader->read($jsonResource, $type);

                $i = 0;
                foreach ($cats as $cat) {
                    if ($i === 9) {
                        return $cat;
                    }

                    ++$i;
                }

                return null;
            }

            /**
             * @return list<string>
             */
            public function listEligibleCatNames(): array
            {
                $json = file_get_contents($this->catsJsonFile);
                $type = Type::iterable(Type::object(Cat::class));

                /** @var iterable<Cat> $cats */
                $cats = $this->jsonStreamReader->read($json, $type);

                return array_map(fn(Cat $cat) => $cat->name, iterator_to_array($cats));
            }
        }

    .. code-block:: php-standalone

        // src/Service/TombolaService.php
        namespace App\Service;

        use App\Dto\Cat;
        use Symfony\Component\JsonStreamer\JsonStreamReader;
        use Symfony\Component\JsonStreamer\StreamReaderInterface;
        use Symfony\Component\TypeInfo\Type;

        class TombolaService
        {
            private StreamReaderInterface $jsonStreamReader;
            private string $catsJsonFile;

            public function __construct(
                private string $catsJsonFile,
            ) {
                $this->jsonStreamReader = JsonStreamReader::create();
            }

            public function pickTheTenthCat(): ?Cat
            {
                $jsonResource = fopen($this->catsJsonFile, 'r');
                $type = Type::iterable(Type::object(Cat::class));

                /** @var iterable<Cat> $cats */
                $cats = $this->jsonStreamReader->read($jsonResource, $type);

                $i = 0;
                foreach ($cats as $cat) {
                    if ($i === 9) {
                        return $cat;
                    }

                    ++$i;
                }

                return null;
            }

            /**
             * @return list<string>
             */
            public function listEligibleCatNames(): array
            {
                $json = file_get_contents($this->catsJsonFile);
                $type = Type::iterable(Type::object(Cat::class));

                /** @var iterable<Cat> $cats */
                $cats = $this->jsonStreamReader->read($json, $type);

                return array_map(fn(Cat $cat) => $cat->name, iterator_to_array($cats));
            }
        }

.. tip::

    You can explicitly inject the ``json_streamer.stream_reader`` service by
    using the ``#[Target('json_streamer.stream_reader')]`` autowire attribute.

The examples above demonstrate two different approaches to decoding JSON data
using JsonStreamer:

* decoding from a stream (``pickTheTenthCat``)
* decoding from a string (``listEligibleCatNames``)

Both methods handle the same JSON data but differ in memory usage and performance.
Use streams if optimizing memory usage is more important. Use strings if
performance is more important.

Decoding from a Stream
~~~~~~~~~~~~~~~~~~~~~~

In the ``pickTheTenthCat`` method, the JSON data is read as a stream using
:phpfunction:`fopen`. This is useful for large files, as the data is processed
incrementally rather than being fully loaded into memory.

To optimize memory usage, JsonStreamer creates `ghost objects`_ instead of
fully instantiating them. These lightweight placeholders delay object creation
until the data is actually needed.

* Advantage: Efficient memory usage, ideal for very large JSON files.
* Disadvantage: Slightly slower due to lazy loading.

Decoding from a String
~~~~~~~~~~~~~~~~~~~~~~

In the ``listEligibleCatNames`` method, the entire JSON file is read into a
string using :phpfunction:`file_get_contents`. The decoder then instantiates
all the objects immediately.

This approach is faster because all objects are created immediately, but it
requires more memory.

* Advantage: Faster, ideal for small to medium JSON files.
* Disadvantage: Higher memory usage, unsuitable for large files.

Enabling PHPDoc Reading
-----------------------

The JsonStreamer component can read advanced PHPDoc type definitions (e.g.,
generics) and process complex PHP objects accordingly.

Consider the ``Shelter`` class that defines a generic ``TAnimal`` type, which
can be a ``Cat`` or a ``Dog``::

    // src/Dto/Shelter.php
    namespace App\Dto;

    use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

    /**
     * @template TAnimal of Cat|Dog
     */
    #[JsonStreamable]
    class Shelter
    {
        /**
         * @var list<TAnimal>
         */
        public array $animals;
    }

To enable PHPDoc parsing, run:

.. code-block:: terminal

    $ composer require phpstan/phpdoc-parser

Then, when encoding/decoding a ``Shelter`` instance, you can specify the
concrete type information, and JsonStreamer will correctly interpret the JSON
structure::

    use App\Dto\Cat;
    use App\Dto\Shelter;
    use Symfony\Component\TypeInfo\Type;

    $json = <<<JSON
    {
      "animals": [
        {"name": "Eva", "age": 29},
        {...}
      ]
    }
    JSON;

    // maps the TAnimal template in Shelter to the Cat concrete type
    $type = Type::generic(Type::object(Shelter::class), Type::object(Cat::class));

    $catShelter = $jsonStreamReader->read($json, $type); // will be populated with Cat instances

Configuring Encoding/Decoding
-----------------------------

While it's usually best not to alter the shape or values of objects during
serialization, sometimes it's necessary.

Including Null Properties
~~~~~~~~~~~~~~~~~~~~~~~~~

By default, properties with ``null`` values are excluded from the JSON output.
Pass the ``include_null_properties`` option to include them explicitly::

    use App\Dto\Cat;
    use Symfony\Component\TypeInfo\Type;

    // ...

    $json = $jsonStreamWriter->write($cat, Type::object(Cat::class), [
        'include_null_properties' => true,
    ]);

    // Without the option: {"name": "Garfield"}
    // With the option:    {"name": "Garfield", "color": null}

.. versionadded:: 7.4

    The ``include_null_properties`` option was introduced in Symfony 7.4.

Configuring the Encoded Name
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can configure the JSON key for a property using the
:class:`Symfony\\Component\\JsonStreamer\\Attribute\\StreamedName` attribute::

    // src/Dto/Duck.php
    namespace App\Dto;

    use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;
    use Symfony\Component\JsonStreamer\Attribute\StreamedName;

    #[JsonStreamable]
    class Duck
    {
        #[StreamedName('@id')]
        public string $id;
    }

This maps the ``Duck::$id`` property to the ``@id`` JSON key::

    use App\Dto\Duck;
    use Symfony\Component\TypeInfo\Type;

    // ...

    $duck = new Duck();
    $duck->id = '/ducks/daffy';

    echo (string) $jsonStreamWriter->write($duck, Type::object(Duck::class));

    // This will output:
    // {
    //   "@id": "/ducks/daffy"
    // }

Configuring the Encoded Value
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To transform a property's value during encoding, use the
:class:`Symfony\\Component\\JsonStreamer\\Attribute\\ValueTransformer`
attribute. Its ``nativeToStream`` option accepts a callable or a
:ref:`value transformer service id <json-streamer-transform-with-services>`.

The callable must be a public static method or non-anonymous function with this
signature::

    $transformer = function (mixed $data, array $options = []): mixed { /* ... */ };

Then specify it in the attribute::

    // src/Dto/Duck.php
    namespace App\Dto;

    use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;
    use Symfony\Component\JsonStreamer\Attribute\ValueTransformer;

    #[JsonStreamable]
    class Duck
    {
        #[ValueTransformer(nativeToStream: 'strtoupper')]
        public string $name;

        #[ValueTransformer(nativeToStream: [self::class, 'formatHeight'])]
        public int $height;

        public static function formatHeight(int $value, array $options = []): string
        {
            return sprintf('%.2fcm', $value / 100);
        }
    }

The following example transforms the ``name`` and ``height`` properties during
encoding::

    use App\Dto\Duck;
    use Symfony\Component\TypeInfo\Type;

    // ...

    $duck = new Duck();
    $duck->name = 'daffy';
    $duck->height = 5083;

    echo (string) $jsonStreamWriter->write($duck, Type::object(Duck::class));

    // This will output:
    // {
    //   "name": "DAFFY",
    //   "height": "50.83cm"
    // }

Configuring the Decoded Value
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To transform a property's value during decoding, use the
:class:`Symfony\\Component\\JsonStreamer\\Attribute\\ValueTransformer`
attribute. Its ``streamToNative`` option accepts a callable or a
:ref:`value transformer service id <json-streamer-transform-with-services>`.

The callable must be a public static method or non-anonymous function with this
signature::

    $valueTransformer = function (mixed $data, array $options = []): mixed { /* ... */ };

Then specify it in the attribute::

    // src/Dto/Duck.php
    namespace App\Dto;

    use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;
    use Symfony\Component\JsonStreamer\Attribute\ValueTransformer;

    #[JsonStreamable]
    class Duck
    {
        #[ValueTransformer(streamToNative: [self::class, 'retrieveFirstName'])]
        public string $firstName;

        #[ValueTransformer(streamToNative: [self::class, 'retrieveLastName'])]
        public string $lastName;

        public static function retrieveFirstName(string $normalized, array $options = []): string
        {
            return explode(' ', $normalized)[0];
        }

        public static function retrieveLastName(string $normalized, array $options = []): string
        {
            return explode(' ', $normalized)[1];
        }
    }

This will extract first and last names from a full name in the input JSON::

    use App\Dto\Duck;
    use Symfony\Component\TypeInfo\Type;

    // ...

    $duck = $jsonStreamReader->read(
        '{"name": "Daffy Duck"}',
        Type::object(Duck::class),
    );

    // The $duck variable will contain:
    // object(Duck)#1 (1) {
    //   ["firstName"] => string(5) "Daffy"
    //   ["lastName"] => string(4) "Duck"
    // }

.. _json-streamer-transform-with-services:

Transforming Value Using Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When callables are not enough, you can use a service implementing the
:class:`Symfony\\Component\\JsonStreamer\\ValueTransformer\\ValueTransformerInterface`::

    // src/Transformer/DogUrlTransformer.php
    namespace App\Transformer;

    use Symfony\Component\JsonStreamer\ValueTransformer\ValueTransformerInterface;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\TypeInfo\Type;

    class DogUrlTransformer implements ValueTransformerInterface
    {
        public function __construct(
            private UrlGeneratorInterface $urlGenerator,
        ) {
        }

        public function transform(mixed $value, array $options = []): string
        {
            if (!is_int($value)) {
                throw new \InvalidArgumentException(sprintf('The value must be "int", "%s" given.', get_debug_type($value)));
            }

            return $this->urlGenerator->generate('show_dog', ['id' => $value]);
        }

        public static function getStreamValueType(): Type
        {
            return Type::string();
        }
    }

.. note::

    The ``getStreamValueType()`` method must return the value's type as it will
    appear in the JSON stream.

.. tip::

    The ``$options`` argument of the ``transform()`` method includes a special
    option called ``_current_object`` which gives access to the object holding
    the current property (or ``null`` if there's none).

    .. versionadded:: 7.4

        The ``_current_object`` option was introduced in Symfony 7.4.

To use this transformer in a class, configure the ``#[ValueTransformer]`` attribute::

    // src/Dto/Dog.php
    namespace App\Dto;

    use App\Transformer\DogUrlTransformer;
    use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;
    use Symfony\Component\JsonStreamer\Attribute\StreamedName;
    use Symfony\Component\JsonStreamer\Attribute\ValueTransformer;

    #[JsonStreamable]
    class Dog
    {
        #[StreamedName('url')]
        #[ValueTransformer(nativeToStream: DogUrlTransformer::class)]
        public int $id;
    }

.. tip::

    Value transformers are called frequently during encoding and decoding. Keep
    them lightweight and avoid calls to external APIs or the database.

Configuring Keys and Values Dynamically
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

JsonStreamer uses services that implement the
:class:`Symfony\\Component\\JsonStreamer\\Mapping\\PropertyMetadataLoaderInterface`
to control the shape and values of objects during encoding/decoding.

These services are highly flexible and can be decorated to support dynamic
configurations, providing more flexibility than attributes::

    namespace App\Streamer\SensitivePropertyMetadataLoader;

    use App\Dto\SensitiveInterface;
    use App\Streamer\ValueTransformer\EncryptorValueTransformer;
    use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
    use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
    use Symfony\Component\JsonStreamer\Mapping\PropertyMetadata;
    use Symfony\Component\JsonStreamer\Mapping\PropertyMetadataLoaderInterface;
    use Symfony\Component\TypeInfo\Type;

    #[AsDecorator('json_streamer.write.property_metadata_loader')]
    class SensitivePropertyMetadataLoader implements PropertyMetadataLoaderInterface
    {
        public function __construct(
            #[AutowireDecorated]
            private PropertyMetadataLoaderInterface $decorated,
        ) {
        }

        public function load(string $className, array $options = [], array $context = []): array
        {
            $propertyMetadataMap = $this->decorated->load($className, $options, $context);

            if (!is_a($className, SensitiveInterface::class, true)) {
                return $propertyMetadataMap;
            }

            // you can configure value transformers
            foreach ($propertyMetadataMap as $jsonKey => $metadata) {
                if (in_array($metadata->getName(), $className::getPropertiesToEncrypt(), true)) {
                    $propertyMetadataMap[$jsonKey] = $metadata
                        ->withType(Type::string())
                        ->withAdditionalNativeToStreamValueTransformer(EncryptorValueTransformer::class);
                }
            }

            // you can remove existing properties
            foreach ($propertyMetadataMap as $jsonKey => $metadata) {
                if (in_array($metadata->getName(), $className::getPropertiesToRemove(), true)) {
                    unset($propertyMetadataMap[$jsonKey]);
                }
            }

            // you can rename JSON keys
            foreach ($propertyMetadataMap as $jsonKey => $metadata) {
                $propertyMetadataMap[md5($jsonKey)] = $propertyMetadataMap[$jsonKey];
                unset($propertyMetadataMap[$jsonKey]);
            }

            // you can add virtual properties
            $propertyMetadataMap['is_sensitive'] = new PropertyMetadata(
                name: 'theNameWontBeUsed',
                type: Type::bool(),
                nativeToStreamValueTransformers: [fn() => true],
            );

            return $propertyMetadataMap;
        }
    }

Although powerful, this approach introduces complexity. Decorating property
metadata loaders requires a deep understanding of the internals.

For most use cases, attribute-based configuration is sufficient. Reserve
dynamic loaders for advanced scenarios.

.. _json-streamer-streamable-attribute:

Marking Objects as Streamable
-----------------------------

The ``JsonStreamable`` attribute marks a class as streamable. While not strictly
required, it's highly recommended because it enables cache warm-up to pre-generate
encoding/decoding files, improving performance.

It includes two optional properties: ``asObject`` and ``asList``, which define
how the class should be prepared during cache warm-up::

    use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

    #[JsonStreamable(asObject: true, asList: true)]
    class StreamableData
    {
        // ...
    }

.. _`DTO classes`: https://en.wikipedia.org/wiki/Data_transfer_object
.. _ghost objects: https://en.wikipedia.org/wiki/Lazy_loading#Ghost
