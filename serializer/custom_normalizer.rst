How to Create your Custom Normalizer
====================================

The :doc:`Serializer component </serializer>` uses normalizers to transform
any data into an array. The component provides several
:ref:`built-in normalizers <serializer-built-in-normalizers>` but you may
need to create your own normalizer to transform an unsupported data
structure.

Creating a New Normalizer
-------------------------

Imagine you want to add, modify, or remove some properties during the serialization
process. For that you'll have to create your own normalizer. But it's usually
preferable to let Symfony normalize the object, then hook into the normalization
to customize the normalized data. To do that, you can inject a
``NormalizerInterface`` and wire it to Symfony's object normalizer. This will give
you access to a ``$normalizer`` property which takes care of most of the
normalization process::

    // src/Serializer/TopicNormalizer.php
    namespace App\Serializer;

    use App\Entity\Topic;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

    class TopicNormalizer implements NormalizerInterface
    {
        public function __construct(
            #[Autowire(service: 'serializer.normalizer.object')]
            private readonly NormalizerInterface $normalizer,

            private UrlGeneratorInterface $router,
        ) {
        }

        public function normalize(mixed $data, ?string $format = null, array $context = []): array
        {
            $normalizedData = $this->normalizer->normalize($data, $format, $context);

            // Here, add, edit, or delete some data:
            $normalizedData['href']['self'] = $this->router->generate('topic_show', [
                'id' => $data->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            return $normalizedData;
        }

        public function supportsNormalization($data, ?string $format = null, array $context = []): bool
        {
            return $data instanceof Topic;
        }

        public function getSupportedTypes(?string $format): array
        {
            return [
                Topic::class => true,
            ];
        }
    }

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Serializer\TopicNormalizer;

        return App::config([
            'services' => [
                TopicNormalizer::class => [
                    'tags' => [
                        // register the normalizer with a high priority (called earlier)
                        ['serializer.normalizer' => ['priority' => 500]],
                    ],
                ],
            ],
        ]);

Improving Performance of Normalizers/Denormalizers
--------------------------------------------------

Both :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface`
and :class:`Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface`
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
#. The ``native-<type>`` strings (e.g. ``native-string``, ``native-integer``,
   ``native-boolean``) declare support for scalar values, using the names returned
   by :phpfunction:`gettype`;


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
