.. index::
   single: Serializer

How to Use the Serializer
=========================

Serializing and deserializing to and from objects and different formats (e.g.
JSON or XML) is a very complex topic. Symfony comes with a
:doc:`Serializer Component </components/serializer>`, which gives you some
tools that you can leverage for your solution.

In fact, before you start, get familiar with the serializer, normalizers
and encoders by reading the :doc:`Serializer Component </components/serializer>`.

Activating the Serializer
-------------------------

The ``serializer`` service is not available by default. To turn it on, activate
it in your configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            serializer:
                enabled: true

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <!-- ... -->
            <framework:serializer enabled="true" />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'serializer' => array(
                'enabled' => true,
            ),
        ));

Using the Serializer Service
----------------------------

Once enabled, the ``serializer`` service can be injected in any service where
you need it or it can be used in a controller like the following::

    // src/AppBundle/Controller/DefaultController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class DefaultController extends Controller
    {
        public function indexAction()
        {
            $serializer = $this->get('serializer');

            // ...
        }
    }

Adding Normalizers and Encoders
-------------------------------

Once enabled, the ``serializer`` service will be available in the container
and will be loaded with two :ref:`encoders <component-serializer-encoders>`
(:class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder` and
:class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder`) and the
:ref:`ObjectNormalizer normalizer <component-serializer-normalizers>`.

You can load normalizers and/or encoders by tagging them as
:ref:`serializer.normalizer <reference-dic-tags-serializer-normalizer>` and
:ref:`serializer.encoder <reference-dic-tags-serializer-encoder>`. It's also
possible to set the priority of the tag in order to decide the matching order.

Here is an example on how to load the
:class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            get_set_method_normalizer:
                class: Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer
                public: false
                tags:
                    - { name: serializer.normalizer }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="get_set_method_normalizer" class="Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer" public="false">
                <tag name="serializer.normalizer" />
            </service>
        </services>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition(
            'Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer'
        ));
        $definition->setPublic(false);
        $definition->addTag('serializer.normalizer');
        $container->setDefinition('get_set_method_normalizer', $definition);

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
        <framework:config>
            <!-- ... -->
            <framework:serializer enable-annotations="true" />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'serializer' => array(
                'enable_annotations' => true,
            ),
        ));

Next, add the :ref:`@Groups annotations <component-serializer-attributes-groups-annotations>`
to your class and choose which groups to use when serializing::

    $serializer = $this->get('serializer');
    $json = $serializer->serialize(
        $someObject,
        'json', array('groups' => array('group1'))
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
enhance application performance. Any service implementing the ``Doctrine\Common\Cache\Cache``
interface can be used.

A service leveraging `APCu`_ (and APC for PHP < 5.5) is built-in.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_prod.yml
        framework:
            # ...
            serializer:
                cache: serializer.mapping.cache.apc

    .. code-block:: xml

        <!-- app/config/config_prod.xml -->
        <framework:config>
            <!-- ... -->
            <framework:serializer cache="serializer.mapping.cache.apc" />
        </framework:config>

    .. code-block:: php

        // app/config/config_prod.php
        $container->loadFromExtension('framework', array(
            // ...
            'serializer' => array(
                'cache' => 'serializer.mapping.cache.apc',
            ),
        ));

Going Further with the Serializer Component
-------------------------------------------

`ApiPlatform`_ provides an API system supporting `JSON-LD`_ and `Hydra Core Vocabulary`_
hypermedia formats. It is built on top of the Symfony Framework and its Serializer
component. It provides custom normalizers and a custom encoder, custom metadata
and a caching system.

If you want to leverage the full power of the Symfony Serializer component,
take a look at how this bundle works.

.. _`APCu`: https://github.com/krakjoe/apcu
.. _`ApiPlatform`: https://github.com/api-platform/core
.. _`JSON-LD`: http://json-ld.org
.. _`Hydra Core Vocabulary`: http://hydra-cg.com
