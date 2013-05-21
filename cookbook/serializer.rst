.. index::
   single: Serializer

How to use the Serializer
=========================

Serializing and deserializing to and from objects and different formats (e.g.
JSON or XML) is a very complex topic. Symfony comes with a
:doc:`Serializer Component</components/serializer>`, which gives you some
tools that you can leverage for your solution.

In fact, before you start, get familiar with the serializer, normalizers
and encoders by reading the :doc:`Serializer Component</components/serializer>`.
You should also check out the `JMSSerializerBundle`_, which expands on the
functionality offered by Symfony's core serializer.

Activating the Serializer
-------------------------

.. versionadded:: 2.3
    The Serializer has always existed in Symfony, but prior to Symfony 2.3,
    you needed to build the ``serializer`` service yourself.

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
        <framework:config ...>
            <!-- ... -->
            <framework:serializer enabled="true" />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'serializer' => array(
                'enabled' => true
            ),
        ));

Adding Normalizers and Encoders
-------------------------------

Once enabled, the ``serializer`` service will be available in the container
and will be loaded with two :ref:`encoders<component-serializer-encoders>`
(:class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder` and
:class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder`)
but no :ref:`normalizers<component-serializer-normalizers>`, meaning you'll
need to load your own.

You can load normalizers and/or encoders by tagging them as
:ref:`serializer.normalizer<reference-dic-tags-serializer-normalizer>` and
:ref:`serializer.encoder<reference-dic-tags-serializer-encoder>`. It's also
possible to set the priority of the tag in order to decide the matching order.

Here an example on how to load the load 
the :class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`:

.. configuration-block:: 

    .. code-block:: yaml

       # app/config/config.yml
       services:
          get_set_method_normalizer:
             class: Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer
             tags:
                - { name: serializer.normalizer }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <services>
            <service id="get_set_method_normalizer" class="Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer">
                <tag name="serializer.normalizer" />
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition(
            'Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer'
        ));
        $definition->addTag('serializer.normalizer');
        $container->setDefinition('get_set_method_normalizer', $definition);

.. note::

    The :class:`Symfony\\Component\\Serializer\\Normalizer\\GetSetMethodNormalizer`
    is broken by design. As soon as you have a circular object graph, an
    infinite loop is created when calling the getters. You're encouraged
    to add your own normalizers that fit your use-case.

.. _JMSSerializerBundle: http://jmsyst.com/bundles/JMSSerializerBundle