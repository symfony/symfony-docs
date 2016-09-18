.. index::
   single: Serializer; Custom encoders

How to Create your Custom Encoder
=================================

The :doc:`Serializer Component </components/serializer>` uses Normalizers
to transform any data to an array that can be then converted in whatever
data-structured language you want thanks to Encoders.

The Component provides several built-in encoders that are described
:doc:`in their own section </serializer/encoders>` but you may want
to use another language not supported.

Creating a new encoder
----------------------

Imagine you want to serialize and deserialize Yaml. For that you'll have to
create your own encoder that may use the
:doc:`Yaml Component </components/yaml>`::

    namespace AppBundle\Encoder;

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

If you use the Symfony Framework then you probably want to register this encoder
as a service in your app. Then you only need to tag it as `serializer.encoder` and it will be
injected in the Serializer.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.encoder.yaml:
                class: AppBundle\Encoder\YamlEncoder
                tags:
                    - { name: serializer.encoder }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.encoder.yaml" class="AppBundle\Encoder\YamlEncoder">
                    <tag name="serializer.encoder" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register(
                'app.encoder.yaml',
                'AppBundle\Encoder\YamlEncoder'
            )
            ->addTag('serializer.encoder')
        ;

Now you'll be able to serialize and deserialize Yaml!

.. _tracker: https://github.com/symfony/symfony/issues
