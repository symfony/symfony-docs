.. index::
   single: Serializer; Custom normalizers

How to Create your Custom Normalizer
====================================

The :doc:`Serializer Component </components/serializer>` uses Normalizers
to transform any data to an array.

The Component provides several built-in normalizer that are described
:doc:`in their own section </serializer/normalizers>` but you may want
to use another structure that's not supported.

Creating a new normalizer
-------------------------

Imagine you want add, modify, or remove some properties during the serialization
process. For that you'll have to create your own normalizer. But it's usually
preferable to let Symfony normalize the object, then hook into the normalization
to customize the normalized data. To do that, we leverage the ObjectNormalizer::

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
            $data['href']['self'] = $this
                ->router
                ->generate(
                    'topic_show',
                    ['id' => $topic->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ;

            return $data;
        }

        public function supportsNormalization($data, $format = null)
        {
            return $data instanceof Topic;
        }
    }

Registering it in your app
--------------------------

If you use the Symfony Framework. then you probably want to register this
normalizer as a service in your app. Then, you only need to tag it with
``serializer.normalizer`` to inject your custom normalizer into the Serializer.

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

.. _tracker: https://github.com/symfony/symfony/issues
