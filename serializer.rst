.. index::
   single: Serializer

How to Use the Serializer
=========================

Symfony provides a serializer to serialize/deserialize to and from objects and
different formats (e.g. JSON or XML).

.. _activating_the_serializer:

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the ``serializer`` :ref:`Symfony pack <symfony-packs>` before using it:

.. code-block:: terminal

    $ composer require symfony/serializer-pack

.. note::

    This pack also installs some commonly used optional dependencies of the
    Serializer component. When using this component outside the Symfony
    framework, you might want to start with the ``symfony/serializer``
    package and install optional dependencies if you need them.

Serializing an Object
---------------------

For this example, assume the following class exists in your project::

    namespace App\Model;

    class Person
    {
        private int $age;
        private string $name;
        private bool $sportsperson;

        public function __construct(string $name, int $age, bool $sportsperson)
        {
            $this->age = $age;
            $this->name = $name;
            $this->sportsperson = $sportsperson;
        }

        public function getAge(): int
        {
            return $this->age;
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function isSportsperson(): bool
        {
            return $this->sportsperson;
        }
    }

If you want to serialize this object into JSON, you need to use the
``serializer`` service by typehinting for
:class:`Symfony\\Component\\Serializer\\SerializerInterface`:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Controller/PersonController.php
        namespace App\Controller;

        use App\Model\Person;
        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\JsonResponse;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Serializer\SerializerInterface;

        class PersonController extends AbstractController
        {
            public function index(SerializerInterface $serializer): Response
            {
                $person = new Person('Jane Doe', 39, false);

                $jsonContent = $serializer->serialize($person, 'json');
                // $jsonContent contains {"name":"Jane Doe","age":39,"sportsperson":false}

                return JsonResponse::fromJsonString($jsonContent);
            }
        }

    .. code-block:: php-standalone

        use App\Model\Person;
        use Symfony\Component\Serializer\Encoder\JsonEncoder;
        use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
        use Symfony\Component\Serializer\Serializer;

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $person = new Person('Jane Done', 39, false);

        $jsonContent = $serializer->serialize($person, 'json');
        // $jsonContent contains {"name":"Jane Doe","age":39,"sportsperson":false}

The first parameter of the :method:`Symfony\\Component\\Serializer\\Serializer::serialize`
is the object to be serialized and the second is used to choose the proper encoder (i.e. format),
in this case the :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`.

.. tip::

    When your controller class extends ``AbstractController`` (like in the
    example above), you can simplify your controller by using
    the :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController::json`
    method to create a JSON response from an object using the Serializer::

        class PersonController extends AbstractController
        {
            public function index(): Response
            {
                $person = new Person('Jane Doe', 39, false);

                return $this->json($person);
            }
        }

Using the Serializer in Twig Templates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.3

    A ``serialize`` filter that uses the Serializer component was
    introduced in Symfony 5.3.

You can also serialize objects in any Twig template using the ``serialize``
filter:

.. code-block:: twig

    {{ person|serialize(format = 'json') }}

See the :doc:`twig reference </reference/twig_reference>` for more
information.

Deserializing an Object
-----------------------

APIs often also need to convert a formatted request body (e.g. JSON) to an
object. This is called *deserialization*:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Controller/PersonController.php
        namespace App\Controller;

        // ...
        use Symfony\Component\HttpFoundation\Exception\BadRequestException;
        use Symfony\Component\HttpFoundation\Request;

        class PersonController extends AbstractController
        {
            // ...

            public function create(Request $request, SerializerInterface $serializer): Response
            {
                if ('json' !== $request->getContentTypeFormat()) {
                    throw new BadRequestException('Unsupported content format');
                }

                $jsonData = $request->getContent();
                $person = $serializer->deserialize($jsonData, Person::class, 'json');

                // ... do something with $person and return a response
            }
        }

    .. code-block:: php-standalone

        use App\Model\Person;
        use Symfony\Component\Serializer\Encoder\JsonEncoder;
        use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
        use Symfony\Component\Serializer\Serializer;

        // ...
        $jsonData = ...; // fetch JSON from the request
        $person = $serializer->deserialize($jsonData, Person::class, 'json');

In this case, :method:`Symfony\\Component\\Serializer\\Serializer::deserialize`
needs three parameters:

#. The data to be decoded
#. The name of the class this information will be decoded to
#. The name of the encoder used to convert the data to an array (i.e. the
   input format)

When sending a request to this controller (e.g.
``{"first_name":"John Doe","age":54,"sportsperson":true}``), the serializer
will create a new instance of ``Person`` and sets the properties to the
values from the given JSON.

.. TODO updates these 2 links

.. note::

    By default, additional attributes that are not mapped to the
    denormalized object will be ignored by the Serializer component. For
    instance, if a request to the above controller contains ``{..., "city": "Paris"}``,
    the ``city`` field will be ignored. You can disallow extra attributes
    using the :ref:`serializer context <serializer-context>` you'll learn
    about later.

.. seealso::

    You can also deserialize data into an existing object instance (e.g.
    when updating data). See ...

The Serialization Process: Normalizers and Encoders
---------------------------------------------------

The serializer uses a two-step process when (de)serializing objects:

.. raw:: html

    <object data="../_images/components/serializer/serializer_workflow.svg" type="image/svg+xml"
        alt="A diagram with a block representing the object at the top, and
        one representing the format (JSON, XML, CSV) at the bottom. An
        arrow pointing from the format to the object is labelled
        'deserialize', while an opposite arrow is labelled 'serialize'. In
        between the blocks, a block named 'array' exists. Arrows in between
        the object and array in the serialize direction are called
        'normalize' and the opposite 'denormalize', while arrows in between
        the array and the format are called 'encode' in serialize
        direction, and 'decode' when deserializing."
    ></object>

In both directions, data is always first converted to an array. This splits
the process in two seperate responsiblities:

Normalizers
    These classes convert **objects** into **arrays** and vice versa. They
    do the heavy lifting of finding out which class properties to
    serialize, what value they hold and what name they should have.
Encoders
    Encoders convert **arrays** into a specific **format** and the other
    way around. Each encoder knows exactly how to parse and generate a
    specific format, for instance JSON or XML.

Internally, the ``Serializer`` class uses a sorted list of normalizers and
one encoder for the specific format when (de)serializing an object.

There are several normalizers configured in the default ``serializer``
service. The most important normalizer is the
:class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`. This
normalizer uses reflection and the :doc:`PropertyAccess component </components/property_access>`
to transform between any object and an array. You'll learn more about
:ref:`this and other normalizers <serializer-normalizers>` later.

The service is also configured with some encoders, covering the common
formats used by HTTP applications:

* :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`
* :class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder`
* :class:`Symfony\\Component\\Serializer\\Encoder\\CsvEncoder`
* :class:`Symfony\\Component\\Serializer\\Encoder\\YamlEncoder`

.. tip::

    The `API Platform`_ project provides encoders for more advanced
    formats:

    * `JSON-LD`_ along with the `Hydra Core Vocabulary`_
    * `OpenAPI`_ v2 (formerly Swagger) and v3
    * `GraphQL`_
    * `JSON:API`_
    * `HAL`_

Serializer Context
------------------

The serializer, and its normalizers and encoders are configured through a
*serializer context*. This context can be configured in multiple places:

Globally through the framework configuration
    You can configure a default context in the framework configuration, for
    instance to disallow extra fields while deserializing:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/serialize.yaml
            framework:
                serializer:
                    default_context:
                        allow_extra_attributes: false

    .. versionadded:: 5.4

        The ability to configure the ``default_context`` option in the
        Serializer was introduced in Symfony 5.4.

As last argument to ``serialize()``/``deserialize()``
    You can also configure the context for a single call to
    ``serialize()``/``deserialize()``. For instance, you can skip
    properties with a ``null`` value only for one serialize call::

        use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

        // ...
        $serializer->serialize($person, 'json', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true
        ]);

        // next calls to serialize() will not skip null values

For a specific property
    At last, you can also configure context values on a specific object
    property. For instance, to configure the datetime format:

    .. configuration-block::

        .. code-block:: php-annotations

            // ...
            use Symfony\Component\Serializer\Annotation\Context;
            use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

            class Person
            {
                /**
                 * @Context({ DateTimeNormalizer::FORMAT_KEY = 'Y-m-d' })
                 */
                public \DateTimeImmutable $birthday;

                // ...
            }

        .. code-block:: php-attributes

            // ...
            use Symfony\Component\Serializer\Annotation\Context;
            use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

            class Person
            {
                #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
                public \DateTimeImmutable $birthday;

                // ...
            }

        .. code-block:: yaml

            App\Model\Person:
                attributes:
                    birthday:
                        context:
                            datetime_format: 'Y-m-d'

        .. code-block:: xml

            <?xml version="1.0" encoding="UTF-8" ?>
            <serializer xmlns="http://symfony.com/schema/dic/serializer-mapping"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/serializer-mapping
                    https://symfony.com/schema/dic/serializer-mapping/serializer-mapping-1.0.xsd"
            >
                <class name="App\Model\Person">
                    <attribute name="birthday">
                        <context>
                            <entry name="datetime_format">Y-m-d</entry>
                        </context>
                    </attribute>
                </class>
            </serializer>

Adding Normalizers and Encoders
-------------------------------

Once enabled, the ``serializer`` service will be available in the container.
It comes with a set of useful :ref:`encoders <component-serializer-encoders>`
and :ref:`normalizers <component-serializer-normalizers>`.

By default, the serializer service is configured with the following
normalizers:

* :class:`Symfony\\Component\\Serializer\\Normalizer\\UnwrappingDenormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ProblemNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\UidNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ConstraintViolationListNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeZoneNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateIntervalNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\FormErrorNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\BackedEnumNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DataUriNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\JsonSerializableNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ArrayDenormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`

.. versionadded:: 5.1

    :class:`Symfony\\Component\\Serializer\\Normalizer\\UnwrappingDenormalizer`
    was introduced in Symfony 5.1.

.. versionadded:: 5.4

    :class:`Symfony\\Component\\Serializer\\Normalizer\\BackedEnumNormalizer`
    was introduced in Symfony 5.4. PHP's ``BackedEnum`` requires at least PHP 8.1.

Encoders supporting the following formats are enabled:

* JSON: :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`
* XML: :class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder`
* CSV: :class:`Symfony\\Component\\Serializer\\Encoder\\CsvEncoder`
* YAML: :class:`Symfony\\Component\\Serializer\\Encoder\\YamlEncoder`

Other :ref:`built-in normalizers <component-serializer-normalizers>` and
custom normalizers and/or encoders can also be loaded by tagging them as
:ref:`serializer.normalizer <reference-dic-tags-serializer-normalizer>` and
:ref:`serializer.encoder <reference-dic-tags-serializer-encoder>`. It's also
possible to set the priority of the tag in order to decide the matching order.

.. caution::

    Always make sure to load the ``DateTimeNormalizer`` when serializing the
    ``DateTime`` or ``DateTimeImmutable`` classes to avoid excessive memory
    usage and exposing internal details.

.. _serializer-using-serialization-groups-annotations:

Using Serialization Groups Annotations
--------------------------------------

You can add the :ref:`@Groups annotations <component-serializer-attributes-groups-annotations>`
to your class::

    // src/Entity/Product.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Serializer\Annotation\Groups;

    /**
     * @ORM\Entity()
     */
    class Product
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         * @Groups({"show_product", "list_product"})
         */
        private $id;

        /**
         * @ORM\Column(type="string", length=255)
         * @Groups({"show_product", "list_product"})
         */
        private $name;

        /**
         * @ORM\Column(type="integer")
         * @Groups({"show_product"})
         */
        private $description;
    }

You can now choose which groups to use when serializing::

    $json = $serializer->serialize(
        $product,
        'json',
        ['groups' => 'show_product']
    );

.. tip::

    The value of the ``groups`` key can be a single string, or an array of strings.

In addition to the ``@Groups`` annotation, the Serializer component also
supports YAML or XML files. These files are automatically loaded when being
stored in one of the following locations:

* All ``*.yaml`` and ``*.xml`` files in the ``config/serializer/``
  directory.
* The ``serialization.yaml`` or ``serialization.xml`` file in
  the ``Resources/config/`` directory of a bundle;
* All ``*.yaml`` and ``*.xml`` files in the ``Resources/config/serialization/``
  directory of a bundle.

.. _serializer-enabling-metadata-cache:

Configuring the Metadata Cache
------------------------------

The metadata for the serializer is automatically cached to enhance application
performance. By default, the serializer uses the ``cache.system`` cache pool
which is configured using the :ref:`cache.system <reference-cache-system>`
option.

Enabling a Name Converter
-------------------------

The use of a :ref:`name converter <component-serializer-converting-property-names-when-serializing-and-deserializing>`
service can be defined in the configuration using the :ref:`name_converter <reference-serializer-name_converter>`
option.

The built-in :ref:`CamelCase to snake_case name converter <using-camelized-method-names-for-underscored-attributes>`
can be enabled by using the ``serializer.name_converter.camel_case_to_snake_case``
value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            serializer:
                name_converter: 'serializer.name_converter.camel_case_to_snake_case'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <framework:config>
            <!-- ... -->
            <framework:serializer name-converter="serializer.name_converter.camel_case_to_snake_case"/>
        </framework:config>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->serializer()->nameConverter('serializer.name_converter.camel_case_to_snake_case');
        };

Going Further with the Serializer
---------------------------------

`API Platform`_ provides an API system supporting the following formats:

* `JSON-LD`_ along with the `Hydra Core Vocabulary`_
* `OpenAPI`_ v2 (formerly Swagger) and v3
* `GraphQL`_
* `JSON:API`_
* `HAL`_
* JSON
* XML
* YAML
* CSV

It is built on top of the Symfony Framework and its Serializer
component. It provides custom normalizers and a custom encoder, custom metadata
and a caching system.

If you want to leverage the full power of the Symfony Serializer component,
take a look at how this bundle works.

.. toctree::
    :maxdepth: 1

    serializer/custom_encoders
    serializer/custom_normalizer

.. _`API Platform`: https://api-platform.com
.. _`JSON-LD`: https://json-ld.org
.. _`Hydra Core Vocabulary`: http://www.hydra-cg.com
.. _`OpenAPI`: https://www.openapis.org
.. _`GraphQL`: https://graphql.org
.. _`JSON:API`: https://jsonapi.org
.. _`HAL`: https://stateless.group/hal_specification.html

Deserializing in an Existing Object
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The serializer can also be used to update an existing object. You can do
this by configuring the ``object_to_populate`` serializer context option::

    use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

    // ...
    $person = new Person();
    $person->setName('Jane Doe');
    $person->setAge(59);
    $person->setSportsperson(true);

    $serializerContext = [
        AbstractNormalizer::OBJECT_TO_POPULATE
    ];
    $serializer->deserialize($jsonData, Person::class, 'json', $serializerContext);
    // instead of returning a new object, $person is updated instead

.. note::

    The ``AbstractNormalizer::OBJECT_TO_POPULATE`` option is only used for
    the top level object. If that object is the root of a tree structure,
    all child elements that exist in the normalized data will be re-created
    with new instances.

    When the ``AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE`` option
    is set to ``true``, existing children of the root
    ``OBJECT_TO_POPULATE`` are updated from the normalized data, instead of
    the denormalizer re-creating them. Note that
    ``DEEP_OBJECT_TO_POPULATE`` only works for single child objects, but
    not for arrays of objects. Those will still be replaced when present in
    the normalized data.

.. TODO move context attribute docs to another place

Use the options to specify context specific to normalization or denormalization::

    namespace App\Model;

    use Symfony\Component\Serializer\Annotation\Context;
    use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

    class Person
    {
        #[Context(
            normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'],
            denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
        )]
        public $createdAt;

        // ...
    }

You can also restrict the usage of a context to some groups::

    namespace App\Model;

    use Symfony\Component\Serializer\Annotation\Context;
    use Symfony\Component\Serializer\Annotation\Groups;
    use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

    class Person
    {
        #[Groups(['extended'])]
        #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339])]
        #[Context(
            context: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339_EXTENDED],
            groups: ['extended'],
        )]
        public $createdAt;

        // ...
    }

The attribute/annotation can be repeated as much as needed on a single property.
Context without group is always applied first. Then context for the matching groups are merged in the provided order.

.. versionadded:: 5.3

    The ``Context`` attribute, annotation and the configuration options were introduced in Symfony 5.3.
