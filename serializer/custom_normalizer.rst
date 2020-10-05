.. index::
   single: Serializer; Custom normalizers

How to Create your Custom Normalizer
====================================

The :doc:`Serializer component </components/serializer>` uses
normalizers to transform any data into an array. The component provides several
:doc:`built-in normalizers </serializer/normalizers>` but you may need to create
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
    use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    class TopicNormalizer implements ContextAwareNormalizerInterface
    {
        private $router;
        private $normalizer;

        public function __construct(UrlGeneratorInterface $router, ObjectNormalizer $normalizer)
        {
            $this->router = $router;
            $this->normalizer = $normalizer;
        }

        public function normalize($topic, $format = null, array $context = [])
        {
            $data = $this->normalizer->normalize($topic, $format, $context);

            // Here, add, edit, or delete some data:
            $data['href']['self'] = $this->router->generate('topic_show', [
                'id' => $topic->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            return $data;
        }

        public function supportsNormalization($data, $format = null, array $context = [])
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
