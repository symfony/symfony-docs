.. index::
   single: Serializer; Custom normalizers

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
    use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
    use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    class TopicNormalizer implements NormalizerInterface, NormalizerAwareInterface
    {
        use NormalizerAwareTrait;
        
        private $router;
        private $normalizer;

        public function __construct(UrlGeneratorInterface $router)
        {
            $this->router = $router;
        }

        public function normalize($topic, string $format = null, array $context = [])
        {
            $data = $this->normalizer->normalize($topic, $format, $context);

            // Here, add, edit, or delete some data:
            $data['href']['self'] = $this->router->generate('topic_show', [
                'id' => $topic->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            return $data;
        }

        public function supportsNormalization($data, string $format = null, array $context = [])
        {
            return $data instanceof Topic;
        }
    }

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

.. _`API Platform`: https://api-platform.com

