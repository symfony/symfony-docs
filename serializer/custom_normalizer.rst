How to Create your Custom Normalizer
====================================

The :doc:`Serializer component </components/serializer>` uses
normalizers to transform any data into an array. The component provides several
:ref:`built-in normalizers <component-serializer-normalizers>` but you may need to create
your own normalizer to transform an unsupported data structure.

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
    use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    class TopicNormalizer implements NormalizerInterface
    {
        public function __construct(
            private UrlGeneratorInterface $router,
            private ObjectNormalizer $normalizer,
        ) {
        }

        public function normalize($topic, string $format = null, array $context = []): array
        {
            $data = $this->normalizer->normalize($topic, $format, $context);

            // Here, add, edit, or delete some data:
            $data['href']['self'] = $this->router->generate('topic_show', [
                'id' => $topic->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            return $data;
        }

        public function supportsNormalization($data, string $format = null, array $context = []): bool
        {
            return $data instanceof Topic;
        }
    }

.. deprecated:: 6.4

    Injecting an ``ObjectNormalizer`` in your custom normalizer is deprecated
    since Symfony 6.4. Implement the
    :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerAwareInterface`
    and use the the
    :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerAwareTrait` instead
    to inject the ``$normalizer`` property.

Registering it in your Application
----------------------------------

Before using this normalizer in a Symfony application it must be registered as
a service and :doc:`tagged </service_container/tags>` with ``serializer.normalizer``.
If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
this is done automatically!

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

    All built-in :ref:`normalizers and denormalizers <component-serializer-normalizers>`
    as well the ones included in `API Platform`_ natively implement this interface.

.. deprecated:: 6.3

    The :class:`Symfony\\Component\\Serializer\\Normalizer\\CacheableSupportsMethodInterface`
    interface is deprecated since Symfony 6.3. You should implement the
    ``getSupportedTypes()`` method instead, as shown in the section below.

Improving Performance of Normalizers/Denormalizers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 6.3

    The ``getSupportedTypes()`` method was introduced in Symfony 6.3.

Both :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface`
and :class:`Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface`
contain a new method ``getSupportedTypes()``. This method allows normalizers or
denormalizers to declare the type of objects they can handle, and whether they
are cacheable. With this info, even if the ``supports*()`` call is not cacheable,
the Serializer can skip a ton of method calls to ``supports*()`` improving
performance substantially in some cases.

The ``getSupportedTypes()`` method should return an array where the keys
represent the supported types, and the values indicate whether the result of
the ``supports*()`` method call can be cached or not. The format of the
returned array is as follows:

#. The special key ``object`` can be used to indicate that the normalizer or
   denormalizer supports any classes or interfaces.
#. The special key ``*`` can be used to indicate that the normalizer or
   denormalizer might support any types.
#. The other keys in the array should correspond to specific types that the
   normalizer or denormalizer supports.
#. The values associated with each type should be a boolean indicating if the
   result of the ``supports*()`` method call for that type can be cached or not.
   A value of ``true`` means that the result is cacheable, while ``false`` means
   that the result is not cacheable.
#. A ``null`` value for a type means that the normalizer or denormalizer does
   not support that type.

Here is an example of how to use the ``getSupportedTypes()`` method::

    use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

    class MyNormalizer implements NormalizerInterface
    {
        // ...

        public function getSupportedTypes(?string $format): array
        {
            return [
                'object' => null,             // Doesn't support any classes or interfaces
                '*' => false,                 // Supports any other types, but the result is not cacheable
                MyCustomClass::class => true, // Supports MyCustomClass and result is cacheable
            ];
        }
    }

Note that ``supports*()`` method implementations should not assume that
``getSupportedTypes()`` has been called before.

.. _`API Platform`: https://api-platform.com
