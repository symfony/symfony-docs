.. index::
   single: Serializer

How to Use the Serializer
=========================

Serializing and deserializing to and from objects and different formats (e.g.
JSON or XML) is a very complex topic. Symfony comes with a
:doc:`Serializer Component </components/serializer>`, which gives you some
tools that you can leverage for your solution.

In fact, before you start, get familiar with the serializer, normalizers
and encoders by reading the :doc:`Serializer Component </components/serializer>`
documentation.

Activating the Serializer
-------------------------

The ``serializer`` service is not available by default. To turn it on, activate
it in your configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            serializer: { enable_annotations: true }
            # Alternatively, if you don't want to use annotations
            #serializer: { enabled: true }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
            <framework:config>
                <!-- ... -->
                <framework:serializer enable-annotations="true"/>
                <!--
                Alternatively, if you don't want to use annotations
                <framework:serializer enabled="true"/>
                -->
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', [
            // ...
            'serializer' => [
                'enable_annotations' => true,
                // Alternatively, if you don't want to use annotations
                //'enabled' => true,
            ],
        ]);

Using the Serializer Service
----------------------------

Once enabled, the ``serializer`` service can be injected in any service where
you need it or it can be used in a controller::

    // src/AppBundle/Controller/DefaultController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Serializer\SerializerInterface;

    class DefaultController extends Controller
    {
        public function indexAction(SerializerInterface $serializer)
        {
            // keep reading for usage examples
        }
    }

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

* :class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer` to
  handle typical data objects
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeNormalizer` for
  objects implementing the :phpclass:`DateTimeInterface` interface
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateIntervalNormalizer` for :phpclass:`DateInterval` objects
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DataUriNormalizer` to
  transform :phpclass:`SplFileInfo` objects in `Data URIs`_
* :class:`Symfony\\Component\\Serializer\\Normalizer\\JsonSerializableNormalizer`
  to deal with objects implementing the :phpclass:`JsonSerializable` interface
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ArrayDenormalizer` to
  denormalize arrays of objects using a format like `MyObject[]` (note the `[]` suffix)

Custom normalizers and/or encoders can also be loaded by tagging them as
:ref:`serializer.normalizer <reference-dic-tags-serializer-normalizer>` and
:ref:`serializer.encoder <reference-dic-tags-serializer-encoder>`. It's also
possible to set the priority of the tag in order to decide the matching order.

.. caution::

    Always make sure to load the ``DateTimeNormalizer`` when serializing the
    ``DateTime`` or ``DateTimeImmutable`` classes to avoid excessive memory
    usage and exposing internal details.

Here is an example on how to load the
:class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`, a
faster alternative to the `ObjectNormalizer` when data objects always use
getters (``getXxx()``), issers (``isXxx()``) or hassers (``hasXxx()``) to read
properties and setters (``setXxx()``) to change properties:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            get_set_method_normalizer:
                class: Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer
                public: false
                tags: [serializer.normalizer]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="get_set_method_normalizer" class="Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer" public="false">
                    <tag name="serializer.normalizer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

        $container->register('get_set_method_normalizer', GetSetMethodNormalizer::class)
            ->setPublic(false)
            ->addTag('serializer.normalizer')
        ;

.. versionadded:: 3.4

    Support for hasser methods (``hasXxx()``) in ``GetSetMethodNormalizer`` was
    introduced in Symfony 3.4. In previous Symfony versions only getters (``getXxx()``)
    and issers (``isXxx()``) were supported.

.. _serializer-using-serialization-groups-annotations:

Using Serialization Groups Annotations
--------------------------------------

Enable :ref:`serialization groups annotation <component-serializer-attributes-groups>`
with the following configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            serializer:
                enable_annotations: true

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- ... -->
                <framework:serializer enable-annotations="true"/>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', [
            // ...
            'serializer' => [
                'enable_annotations' => true,
            ],
        ]);

Next, add the :ref:`@Groups annotations <component-serializer-attributes-groups-annotations>`
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

In addition to the ``@Groups`` annotation, the Serializer component also
supports Yaml or XML files. These files are automatically loaded when being
stored in one of the following locations:

* The ``serialization.yml`` or ``serialization.xml`` file in
  the ``Resources/config/`` directory of a bundle;
* All ``*.yml`` and ``*.xml`` files in the ``Resources/config/serialization/``
  directory of a bundle.

.. _serializer-enabling-metadata-cache:

Enabling the Metadata Cache
---------------------------

Metadata used by the Serializer component such as groups can be cached to
enhance application performance. By default, the serializer uses the ``cache.system``
cache pool which is configured using the :ref:`cache.system <reference-cache-system>`
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

        # app/config/config.yml
        framework:
            # ...
            serializer:
                name_converter: 'serializer.name_converter.camel_case_to_snake_case'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <!-- ... -->
            <framework:serializer name-converter="serializer.name_converter.camel_case_to_snake_case"/>
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', [
            // ...
            'serializer' => [
                'name_converter' => 'serializer.name_converter.camel_case_to_snake_case',
            ],
        ]);

Enabling Circular Reference Handler
-----------------------------------

The use of the :ref:`circular reference handler <component-serializer-handling-circular-references>` service can be defined
in the configuration using the :ref:`circular_reference_handler <reference-serializer-circular_reference_handler>` option.

A circular reference handler service has to implement the magic ``__invoke($object)`` method, like in example::

    class MyCircularReferenceHandler
    {
        public function __invoke($object)
        {
            return $object->getName();
        }
    }

Going Further with the Serializer
---------------------------------

`ApiPlatform`_ provides an API system supporting `JSON-LD`_ and `Hydra Core Vocabulary`_
hypermedia formats. It is built on top of the Symfony Framework and its Serializer
component. It provides custom normalizers and a custom encoder, custom metadata
and a caching system.

If you want to leverage the full power of the Symfony Serializer component,
take a look at how this bundle works.

.. toctree::
    :maxdepth: 1
    :glob:

    serializer/*

.. _`ApiPlatform`: https://github.com/api-platform/core
.. _`JSON-LD`: http://json-ld.org
.. _`Hydra Core Vocabulary`: http://hydra-cg.com
.. _`Data URIs`: https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Data_URIs
