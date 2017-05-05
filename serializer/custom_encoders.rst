.. index::
   single: Serializer; Custom encoders

How to Create your Custom Encoder
=================================

The :doc:`Serializer Component </components/serializer>` uses Normalizers
to transform any data to an array. Then, by leveraging *Encoders*, that data can
be converted into any data-structure (e.g. JSON).

The Component provides several built-in encoders that are described
:doc:`in their own section </serializer/encoders>` but you may want
to use another structure that's not supported.

Creating a new encoder
----------------------

Imagine you want to serialize and deserialize Yaml. For that you'll have to
create your own encoder that uses the
:doc:`Yaml Component </components/yaml>`::

    namespace AppBundle\Serializer;

    use Symfony\Component\Serializer\Encoder\DecoderInterface;
    use Symfony\Component\Serializer\Encoder\EncoderInterface;
    use Symfony\Component\Yaml\Yaml;

    class YamlEncoder implements EncoderInterface, DecoderInterface
    {
        public function encode($data, $format, array $context = array())
        {
            return Yaml::dump($data);
        }

        public function supportsEncoding($format)
        {
            return 'yaml' === $format;
        }

        public function decode($data, $format, array $context = array())
        {
            return Yaml::parse($data);
        }

        public function supportsDecoding($format)
        {
            return 'yaml' === $format;
        }
    }

Registering it in your app
--------------------------

If you use the Symfony Framework. then you probably want to register this encoder
as a service in your app. Then, you only need to tag it with ``serializer.encoder``
to inject your custom encoder into the Serializer.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.yaml_encoder:
                class: AppBundle\Serializer\YamlEncoder
                tags: [serializer.encoder]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.yaml_encoder" class="AppBundle\Serializer\YamlEncoder">
                    <tag name="serializer.encoder" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Serializer\YamlEncoder;

        $container
            ->register('app.yaml_encoder', YamlEncoder::class)
            ->addTag('serializer.encoder')
        ;

Now you'll be able to serialize and deserialize Yaml!

.. _tracker: https://github.com/symfony/symfony/issues
