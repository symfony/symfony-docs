How to Create your Custom Normalizer
====================================

The :doc:`Serializer component </serializer>` uses normalizers to transform
any data into an array. The component provides several
:ref:`built-in normalizers <serializer-built-in-normalizers>` but you may
need to create your own normalizer to transform an unsupported data
structure.

Creating a New Normalizer
-------------------------

Imagine you want add, modify, or remove some properties during the serialization
process. For that you'll have to create your own normalizer. But it's usually
preferable to let Symfony normalize the object, then hook into the normalization
to customize the normalized data. To do that, leverage the ``ObjectNormalizer``::

    // src/Serializer/TopicNormalizer.php
    namespace App\Serializer;

    use App\Entity\Topic;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
    use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
    use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    class TopicNormalizer implements NormalizerInterface
    {
        public function __construct(
            private UrlGeneratorInterface $router,
            private ObjectNormalizer $normalizer,
        ) {
        }

        public function normalize(mixed $object, ?string $format = null, array $context = []): array
        {
            $data = $this->normalizer->normalize($object, $format, $context);

            // Here, add, edit, or delete some data:
            $data['href']['self'] = $this->router->generate('topic_show', [
                'id' => $object->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            return $data;
        }

        public function supportsNormalization($data, ?string $format = null, array $context = []): bool
        {
            return $data instanceof Topic;
        }
    }

.. deprecated:: 6.1

    Injecting an ``ObjectNormalizer`` in your custom normalizer is deprecated
    since Symfony 6.1. Implement the
    :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerAwareInterface`
    and use the
    :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerAwareTrait` instead
    to inject the ``$normalizer`` property.

Registering it in your Application
----------------------------------

Before using this normalizer in a Symfony application it must be registered as
a service and :doc:`tagged </service_container/tags>` with ``serializer.normalizer``.
If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
this is done automatically!

If you're not using ``autoconfigure``, you have to tag the service with
``serializer.normalizer``. You can also use this method to set a priority
(higher means it's called earlier in the process):

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Serializer\TopicNormalizer:
                tags:
                    # register the normalizer with a high priority (called earlier)
                    - { name: 'serializer.normalizer', priority: 500 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="https://symfony.com/schema/services-1.0.xsd"
        >
            <services>
                <!-- ... -->

                <service id="App\Serializer\TopicNormalizer">
                    <!-- register the normalizer with a high priority (called earlier) -->
                    <tag name="serializer.normalizer"
                        priority="500"
                    />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Serializer\TopicNormalizer;

        return function(ContainerConfigurator $container) {
            // ...

            // if you're using autoconfigure, the tag will be automatically applied
            $services->set(TopicNormalizer::class)
                // register the normalizer with a high priority (called earlier)
                ->tag('serializer.normalizer', [
                    'priority' => 500,
                ])
            ;
        };

Performance
-----------

To figure which normalizer (or denormalizer) must be used to handle an object,
the :class:`Symfony\\Component\\Serializer\\Serializer` class will call the
:method:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface::supportsNormalization`
(or :method:`Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface::supportsDenormalization`)
of all registered normalizers (or denormalizers) in a loop.

The result of these methods can vary depending on the object to serialize, the
format and the context. That's why the result **is not cached** by default and
can result in a significant performance bottleneck.

However, most normalizers (and denormalizers) always return the same result when
the object's type and the format are the same, so the result can be cached. To
do so, make those normalizers (and denormalizers) implement the
:class:`Symfony\\Component\\Serializer\\Normalizer\\CacheableSupportsMethodInterface`
and return ``true`` when
:method:`Symfony\\Component\\Serializer\\Normalizer\\CacheableSupportsMethodInterface::hasCacheableSupportsMethod`
is called.

.. note::

    All built-in :ref:`normalizers and denormalizers <serializer-built-in-normalizers>`
    as well the ones included in `API Platform`_ natively implement this interface.

.. deprecated:: 6.3

    The :class:`Symfony\\Component\\Serializer\\Normalizer\\CacheableSupportsMethodInterface`
    interface is deprecated since Symfony 6.3. You should implement the
    ``getSupportedTypes()`` method instead, as shown in the section below.

Improving Performance of Normalizers/Denormalizers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 6.3

    The ``getSupportedTypes()`` method was introduced in Symfony 6.3.

Both :class:Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface
and :class:Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface
define a ``getSupportedTypes()`` method to declare which types they support and
whether their ``supports*()`` result can be cached.

This **does not** cache the actual normalization or denormalization result. It
only **caches the decision** of whether a normalizer supports a given type, allowing
the Serializer to skip unnecessary ``supports*()`` calls and improve performance.

The ``getSupportedTypes()`` method should return an array where the keys
represent the supported types, and the values indicate whether the result of the
corresponding ``supports*()`` call can be cached. The array format is as follows:

#. The special key ``object`` can be used to indicate that the normalizer or
   denormalizer supports any classes or interfaces.
#. The special key ``*`` can be used to indicate that the normalizer or
   denormalizer might support any type.
#. Other keys should correspond to specific types that the normalizer or
   denormalizer supports.
#. The values should be booleans indicating whether the result of the
   ``supports*()`` call for that type is cacheable. Use ``true`` if the result
   can be cached, ``false`` if it cannot.
#. A ``null`` value means the normalizer or denormalizer does not support that type.

Here is an example of how to use the ``getSupportedTypes()`` method::

    use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

    class MyNormalizer implements NormalizerInterface
    {
        // ...

        public function getSupportedTypes(?string $format): array
        {
            return [
                'object' => null,             // doesn't support any classes or interfaces
                '*' => false,                 // supports any other types, but the decision is not cacheable
                MyCustomClass::class => true, // supports MyCustomClass and decision is cacheable
            ];
        }
    }

.. note::

    The ``supports*()`` method implementations should not assume that
    ``getSupportedTypes()`` has been called before.

.. _`API Platform`: https://api-platform.com
