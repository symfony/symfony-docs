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

* :class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer` to
  handle typical data objects
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeNormalizer` for
  objects implementing the :phpclass:`DateTimeInterface` interface
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeZoneNormalizer` for
  :phpclass:`DateTimeZone` objects
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateIntervalNormalizer`
  for :phpclass:`DateInterval` objects
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DataUriNormalizer` to
  transform :phpclass:`SplFileInfo` objects in `Data URIs`_
* :class:`Symfony\\Component\\Serializer\\Normalizer\\FormErrorNormalizer` for
  objects implementing the :class:`Symfony\\Component\\Form\\FormInterface` to
  normalize form errors.
* :class:`Symfony\\Component\\Serializer\\Normalizer\\JsonSerializableNormalizer`
  to deal with objects implementing the :phpclass:`JsonSerializable` interface
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ArrayDenormalizer` to
  denormalize arrays of objects using a notation like ``MyObject[]`` (note the ``[]`` suffix)
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ConstraintViolationListNormalizer` for objects implementing the :class:`Symfony\\Component\\Validator\\ConstraintViolationListInterface` interface
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ProblemNormalizer` for :class:`Symfony\\Component\\ErrorHandler\\Exception\\FlattenException` objects

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

        # config/services.yaml
        services:
            get_set_method_normalizer:
                class: Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer
                tags: [serializer.normalizer]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="get_set_method_normalizer" class="Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer">
                    <tag name="serializer.normalizer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set('get_set_method_normalizer', GetSetMethodNormalizer::class)
                ->tag('serializer.normalizer')
            ;
        };

.. _serializer-using-serialization-groups-annotations:

Using Serialization Groups Annotations
--------------------------------------

To use annotations, first add support for them via the SensioFrameworkExtraBundle:

.. code-block:: terminal

    $ composer require sensio/framework-extra-bundle

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

    serializer/normalizers
    serializer/custom_encoders
    serializer/custom_normalizer

.. _`API Platform`: https://api-platform.com
.. _`JSON-LD`: https://json-ld.org
.. _`Hydra Core Vocabulary`: http://www.hydra-cg.com
.. _`OpenAPI`: https://www.openapis.org
.. _`GraphQL`: https://graphql.org
.. _`JSON:API`: https://jsonapi.org
.. _`HAL`: http://stateless.co/hal_specification.html
.. _`Data URIs`: https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Data_URIs
