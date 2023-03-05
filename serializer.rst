.. index::
   single: Serializer

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
    use Symfony\Component\Serializer\SerializerInterface;

    class DefaultController extends AbstractController
    {
        public function index(SerializerInterface $serializer)
        {
            // keep reading for usage examples
        }
    }

Or you can use the ``serialize`` Twig filter in a template:

.. code-block:: twig

    {{ object|serialize(format = 'json') }}

See the :doc:`twig reference </reference/twig_reference>` for
more information.

.. versionadded:: 5.3

    A ``serialize`` filter was introduced in Symfony 5.3 that uses the Serializer component.

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

.. versionadded:: 5.4

    :class:`Symfony\\Component\\Serializer\\Normalizer\\BackedEnumNormalizer`
    was introduced in Symfony 5.4. PHP BackedEnum requires at least PHP 8.1.

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

.. versionadded:: 5.4

    The usage of the ``empty_array_as_object`` option in the
    Serializer was introduced in Symfony 5.4.

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

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <framework:config>
            <!-- ... -->
            <framework:serializer>
                <default-context enable-max-depth="true"/>
            </framework:serializer>
        </framework:config>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->serializer()
                ->defaultContext([
                    AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true
                ])
            ;
        };

.. versionadded:: 5.4

    The ability to configure the ``default_context`` option in the
    Serializer was introduced in Symfony 5.4.

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
Context without group is always applied first. Then context for the matching groups are merged in the provided order.

.. versionadded:: 5.3

    The ``Context`` attribute, annotation and the configuration options were introduced in Symfony 5.3.

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
