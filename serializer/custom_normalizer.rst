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

    namespace AppBundle\Serializer;

    use AppBundle\Entity\Topic;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

    class TopicNormalizer implements NormalizerInterface
    {
        private $router;
        private $normalizer;

        public function __construct(UrlGeneratorInterface $router, ObjectNormalizer $normalizer)
        {
            $this->router = $router;
            $this->normalizer = $normalizer;
        }

        public function normalize($topic, $format = null, array $context = array())
        {
            $data = $this->normalizer->normalize($topic, $format, $context);

            // Here, add, edit, or delete some data:
            $data['href']['self'] = $this->router->generate('topic_show', array(
                'id' => $topic->getId(),
            ), UrlGeneratorInterface::ABSOLUTE_URL);

            return $data;
        }

        public function supportsNormalization($data, $format = null)
        {
            return $data instanceof Topic;
        }
    }

Registering it in Your Application
----------------------------------

In order to enable the normalizer in an application based on the entire Symfony
framework, you must register it as a service and :doc:`tag it </service_container/tags>`
with ``serializer.normalizer``.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.yaml_encoder:
                class: AppBundle\Serializer\TopicNormalizer
                tags:
                    - { name: serializer.normalizer }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.yaml_encoder" class="AppBundle\Serializer\TopicNormalizer">
                    <tag name="serializer.normalizer" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Serializer\TopicNormalizer;

        $container
            ->register('app.yaml_encoder', TopicNormalizer::class)
            ->addTag('serializer.normalizer')
        ;
