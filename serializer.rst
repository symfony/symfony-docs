How to Use the Serializer
=========================

Symfony provides a serializer to serialize/deserialize to and from objects and
different formats (e.g. JSON or XML). Before using it, read the
:doc:`Serializer component docs </components/serializer>` to get familiar with
its philosophy and the normalizers and encoders terminology.

.. _activating_the_serializer:

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the ``serializer`` :ref:`Symfony pack <symfony-packs>` before using it:

.. code-block:: terminal

    $ composer require symfony/serializer-pack

Using the Serializer Service
----------------------------

Once enabled, the serializer service can be injected in any service where
you need it or it can be used in a controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Serializer\SerializerInterface;

    class DefaultController extends AbstractController
    {
        public function index(SerializerInterface $serializer): Response
        {
            // keep reading for usage examples
        }
    }

Or you can use the ``serialize`` Twig filter in a template:

.. code-block:: twig

    {{ object|serialize(format = 'json') }}

See the :doc:`twig reference </reference/twig_reference>` for
more information.

Adding Normalizers and Encoders
-------------------------------

Once enabled, the ``serializer`` service will be available in the container.
It comes with a set of useful :ref:`encoders <component-serializer-encoders>`
and :ref:`normalizers <component-serializer-normalizers>`.

Encoders supporting the following formats are enabled:

* JSON: :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`
* XML: :class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder`
* CSV: :class:`Symfony\\Component\\Serializer\\Encoder\\CsvEncoder`
* YAML: :class:`Symfony\\Component\\Serializer\\Encoder\\YamlEncoder`

As well as the following normalizers:

* :class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeZoneNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateIntervalNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\FormErrorNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DataUriNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\JsonSerializableNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ArrayDenormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ConstraintViolationListNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ProblemNormalizer`
* :class:`Symfony\\Component\\Serializer\\Normalizer\\BackedEnumNormalizer`

Other :ref:`built-in normalizers <component-serializer-normalizers>` and
custom normalizers and/or encoders can also be loaded by tagging them as
:ref:`serializer.normalizer <reference-dic-tags-serializer-normalizer>` and
:ref:`serializer.encoder <reference-dic-tags-serializer-encoder>`. It's also
possible to set the priority of the tag in order to decide the matching order.

.. caution::

    Always make sure to load the ``DateTimeNormalizer`` when serializing the
    ``DateTime`` or ``DateTimeImmutable`` classes to avoid excessive memory
    usage and exposing internal details.

.. _serializer_serializer-context:

Serializer Context
------------------

The serializer can define a context to control the (de)serialization of
resources. This context is passed to all normalizers. For example:

* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeNormalizer` uses
  ``datetime_format`` key as date time format;
* :class:`Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer`
  uses ``preserve_empty_objects`` to represent empty objects as ``{}`` instead
  of ``[]`` in JSON.
* :class:`Symfony\\Component\\Serializer\\Serializer`
  uses ``empty_array_as_object`` to represent empty arrays as ``{}`` instead
  of ``[]`` in JSON.

You can pass the context as follows::

    $serializer->serialize($something, 'json', [
        DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
    ]);

    $serializer->deserialize($someJson, Something::class, 'json', [
        DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
    ]);

You can also configure the default context through the framework
configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            serializer:
                default_context:
                    enable_max_depth: true
                    yaml_indentation: 2

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <framework:config>
            <!-- ... -->
            <framework:serializer>
                <default-context enable-max-depth="true" yaml-indentation="2"/>
            </framework:serializer>
        </framework:config>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Component\Serializer\Encoder\YamlEncoder;
        use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->serializer()
                ->defaultContext([
                    AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                    YamlEncoder::YAML_INDENTATION => 2,
                ])
            ;
        };

.. versionadded:: 6.2

    The option to configure YAML indentation was introduced in Symfony 6.2.

You can also specify the context on a per-property basis::

.. configuration-block::

    .. code-block:: php-annotations

        namespace App\Model;

        use Symfony\Component\Serializer\Annotation\Context;
        use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

        class Person
        {
            /**
             * @Context({ DateTimeNormalizer::FORMAT_KEY = 'Y-m-d' })
             */
            public $createdAt;

            // ...
        }

    .. code-block:: php-attributes

        namespace App\Model;

        use Symfony\Component\Serializer\Annotation\Context;
        use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

        class Person
        {
            #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
            public $createdAt;

            // ...
        }

    .. code-block:: yaml

        App\Model\Person:
            attributes:
                createdAt:
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
                <attribute name="createdAt">
                    <context>
                        <entry name="datetime_format">Y-m-d</entry>
                    </context>
                </attribute>
            </class>
        </serializer>

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
Context without group is always applied first. Then context for the matching
groups are merged in the provided order.

.. _serializer-using-context-builders:

Using Context Builders
----------------------

.. versionadded:: 6.1

    Context builders were introduced in Symfony 6.1.

To define the (de)serialization context, you can use "context builders", which
are objects that help you to create that context by providing autocompletion,
validation, and documentation::

    use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;

    $contextBuilder = (new DateTimeNormalizerContextBuilder())->withFormat('Y-m-d H:i:s');
    $serializer->serialize($something, 'json', $contextBuilder->toArray());

Each normalizer/encoder has its related :ref:`context builder <component-serializer-context-builders>`.
To create a more complex (de)serialization context, you can chain them using the
``withContext()`` method::

    use Symfony\Component\Serializer\Context\Encoder\CsvEncoderContextBuilder;
    use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

    $initialContext = [
        'custom_key' => 'custom_value',
    ];

    $contextBuilder = (new ObjectNormalizerContextBuilder())
        ->withContext($initialContext)
        ->withGroups(['group1', 'group2']);

    $contextBuilder = (new CsvEncoderContextBuilder())
        ->withContext($contextBuilder)
        ->withDelimiter(';');

    $serializer->serialize($something, 'csv', $contextBuilder->toArray());

You can also :doc:`create your context builders </serializer/custom_context_builders>`
to have autocompletion, validation, and documentation for your custom context values.

.. _serializer-using-serialization-groups-annotations:
.. _serializer-using-serialization-groups-attributes:

Using Serialization Groups Attributes
-------------------------------------

You can add :ref:`#[Groups] attributes <component-serializer-attributes-groups-annotations>`
to your class::

    // src/Entity/Product.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Serializer\Annotation\Groups;

    #[ORM\Entity]
    class Product
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: 'integer')]
        #[Groups(['show_product', 'list_product'])]
        private int $id;

        #[ORM\Column(type: 'string', length: 255)]
        #[Groups(['show_product', 'list_product'])]
        private string $name;

        #[ORM\Column(type: 'text')]
        #[Groups(['show_product'])]
        private string $description;
    }

You can now choose which groups to use when serializing::

    use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

    $context = (new ObjectNormalizerContextBuilder())
        ->withGroups('show_product')
        ->toArray();

    $json = $serializer->serialize($product, 'json', $context);

.. tip::

    The value of the ``groups`` key can be a single string, or an array of strings.

In addition to the ``#[Groups]`` attribute, the Serializer component also
supports YAML or XML files. These files are automatically loaded when being
stored in one of the following locations:

* All ``*.yaml`` and ``*.xml`` files in the ``config/serializer/``
  directory.
* The ``serialization.yaml`` or ``serialization.xml`` file in
  the ``Resources/config/`` directory of a bundle;
* All ``*.yaml`` and ``*.xml`` files in the ``Resources/config/serialization/``
  directory of a bundle.

.. _serializer-enabling-metadata-cache:

Using Nested Attributes
-----------------------

To map nested properties, use the ``SerializedPath`` configuration to define
their paths using a :doc:`valid PropertyAccess syntax </components/property_access>`:

.. configuration-block::

    .. code-block:: php-annotations

        namespace App\Model;

        use Symfony\Component\Serializer\Annotation\SerializedPath;

        class Person
        {
            /**
             * @SerializedPath("[profile][information][birthday]")
             */
            private string $birthday;

            // ...
        }

    .. code-block:: php-attributes

        namespace App\Model;

        use Symfony\Component\Serializer\Annotation\SerializedPath;

        class Person
        {
            #[SerializedPath('[profile][information][birthday]')]
            private string $birthday;

            // ...
        }

    .. code-block:: yaml

        App\Model\Person:
            attributes:
                dob:
                    serialized_path: '[profile][information][birthday]'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <serializer xmlns="http://symfony.com/schema/dic/serializer-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/serializer-mapping
                https://symfony.com/schema/dic/serializer-mapping/serializer-mapping-1.0.xsd"
        >
            <class name="App\Model\Person">
                <attribute name="dob" serialized-path="[profile][information][birthday]"/>
            </class>
        </serializer>

.. versionadded:: 6.2

    The option to configure a ``SerializedPath`` was introduced in Symfony 6.2.

Using the configuration from above, denormalizing with a metadata-aware
normalizer will write the ``birthday`` field from ``$data`` onto the ``Person``
object::

    $data = [
        'profile' => [
            'information' => [
                'birthday' => '01-01-1970',
            ],
        ],
    ];
    $person = $normalizer->denormalize($data, Person::class, 'any');
    $person->getBirthday(); // 01-01-1970

When using annotations or attributes, the ``SerializedPath`` can either
be set on the property or the associated _getter_ method. The ``SerializedPath``
cannot be used in combination with a ``SerializedName`` for the same property.

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

        return static function (FrameworkConfig $framework): void {
            $framework->serializer()->nameConverter('serializer.name_converter.camel_case_to_snake_case');
        };

Debugging the Serializer
------------------------

Use the ``debug:serializer`` command to dump the serializer metadata of a
given class:

.. code-block:: terminal

    $ php bin/console debug:serializer 'App\Entity\Book'

        App\Entity\Book
        ---------------

        +----------+------------------------------------------------------------+
        | Property | Options                                                    |
        +----------+------------------------------------------------------------+
        | name     | [                                                          |
        |          |   "groups" => [                                            |
        |          |       "book:read",                                         |
        |          |       "book:write",                                        |
        |          |   ]                                                        |
        |          |   "maxDepth" => 1,                                         |
        |          |   "serializedName" => "book_name"                          |
        |          |   "ignore" => false                                        |
        |          |   "normalizationContexts" => [],                           |
        |          |   "denormalizationContexts" => []                          |
        |          | ]                                                          |
        | isbn     | [                                                          |
        |          |   "groups" => [                                            |
        |          |       "book:read",                                         |
        |          |   ]                                                        |
        |          |   "maxDepth" => null,                                      |
        |          |   "serializedName" => null                                 |
        |          |   "ignore" => false                                        |
        |          |   "normalizationContexts" => [],                           |
        |          |   "denormalizationContexts" => []                          |
        |          | ]                                                          |
        +----------+------------------------------------------------------------+

.. versionadded:: 6.3

    The debug:serializer`` command was introduced in Symfony 6.3.


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
    serializer/custom_context_builders

.. _`API Platform`: https://api-platform.com
.. _`JSON-LD`: https://json-ld.org
.. _`Hydra Core Vocabulary`: https://www.hydra-cg.com/
.. _`OpenAPI`: https://www.openapis.org
.. _`GraphQL`: https://graphql.org
.. _`JSON:API`: https://jsonapi.org
.. _`HAL`: https://stateless.group/hal_specification.html
