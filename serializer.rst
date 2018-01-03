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

In applications using :doc:`Symfony Flex </setup/flex>`, run this command to
install the serializer before using it:

.. code-block:: terminal

    $ composer require serializer

Using the Serializer Service
----------------------------

Once enabled, the serializer service can be injected in any service where
you need it or it can be used in a controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

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

As well as the following normalizers:

* :class:`Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer` to
  handle typical data objects
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DateTimeNormalizer` for
  objects implementing the :class:`DateTimeInterface` interface
* :class:`Symfony\\Component\\Serializer\\Normalizer\\DataUriNormalizer` to
  transform :class:`SplFileInfo` objects in `Data URIs`_
* :class:`Symfony\\Component\\Serializer\\Normalizer\\JsonSerializableNormalizer`
  to deal with objects implementing the :class:`JsonSerializable` interface
* :class:`Symfony\\Component\\Serializer\\Normalizer\\ArrayDenormalizer` to
  denormalize arrays of objects using a format like `MyObject[]` (note the `[]` suffix)

Custom normalizers and/or encoders can also be loaded by tagging them as
:ref:`serializer.normalizer <reference-dic-tags-serializer-normalizer>` and
:ref:`serializer.encoder <reference-dic-tags-serializer-encoder>`. It's also
possible to set the priority of the tag in order to decide the matching order.

Here is an example on how to load the
:class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`, a
faster alternative to the `ObjectNormalizer` when data objects always use
getters and setters:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            get_set_method_normalizer:
                class: Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer
                public: false
                tags: [serializer.normalizer]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="get_set_method_normalizer" class="Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer" public="false">
                    <tag name="serializer.normalizer" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

        $container->register('get_set_method_normalizer', GetSetMethodNormalizer::class)
            ->setPublic(false)
            ->addTag('serializer.normalizer')
        ;

.. _serializer-using-serialization-groups-annotations:

Using Serialization Groups Annotations
--------------------------------------

To use annotations, first install the annotations package:

.. code-block:: terminal

    $ composer require annotations

Next, add the :ref:`@Groups annotations <component-serializer-attributes-groups-annotations>`
to your class and choose which groups to use when serializing::

    $json = $serializer->serialize(
        $someObject,
        'json', array('groups' => array('group1'))
    );

In addition to the ``@Groups`` annotation, the Serializer component also
supports YAML or XML files. These files are automatically loaded when being
stored in one of the following locations:

* The ``serialization.yaml`` or ``serialization.xml`` file in
  the ``Resources/config/`` directory of a bundle;
* All ``*.yaml`` and ``*.xml`` files in the ``Resources/config/serialization/``
  directory of a bundle.

.. _serializer-enabling-metadata-cache:

Configuring the Metadata Cache
------------------------------

The metadata for the serializer is automatically cached. To configure the cache,
configure the ``framework.cache.pools`` key in ``config/packages/framework.yaml``.

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
            <framework:serializer name-converter="serializer.name_converter.camel_case_to_snake_case" />
        </framework:config>

    .. code-block:: php

        // config/packages/framework.php
        $container->loadFromExtension('framework', array(
            // ...
            'serializer' => array(
                'name_converter' => 'serializer.name_converter.camel_case_to_snake_case',
            ),
        ));

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

    serializer/encoders
    serializer/custom_encoders

.. _`APCu`: https://github.com/krakjoe/apcu
.. _`ApiPlatform`: https://github.com/api-platform/core
.. _`JSON-LD`: http://json-ld.org
.. _`Hydra Core Vocabulary`: http://hydra-cg.com
.. _`Data URIs`: https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Data_URIs
