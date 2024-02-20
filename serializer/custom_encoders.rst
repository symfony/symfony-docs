How to Create your Custom Encoder
=================================

The :doc:`Serializer Component </components/serializer>` uses Normalizers
to transform any data to an array. Then, by leveraging *Encoders*, that data can
be converted into any data-structure (e.g. JSON).

The Component provides several built-in encoders that are described
:doc:`in the serializer component </components/serializer>` but you may want
to use another structure that's not supported.

Creating a new encoder
----------------------

Imagine you want to serialize and deserialize YAML. For that you'll have to
create your own encoder that uses the
:doc:`Yaml Component </components/yaml>`::

    // src/Serializer/YamlEncoder.php
    namespace App\Serializer;

    use Symfony\Component\Serializer\Encoder\DecoderInterface;
    use Symfony\Component\Serializer\Encoder\EncoderInterface;
    use Symfony\Component\Yaml\Yaml;

    class YamlEncoder implements EncoderInterface, DecoderInterface
    {
        public function encode($data, string $format, array $context = []): string
        {
            return Yaml::dump($data);
        }

        public function supportsEncoding(string $format, array $context = []): bool
        {
            return 'yaml' === $format;
        }

        public function decode(string $data, string $format, array $context = []): array
        {
            return Yaml::parse($data);
        }

        public function supportsDecoding(string $format, array $context = []): bool
        {
            return 'yaml' === $format;
        }
    }


Registering it in your app
--------------------------

If you use the Symfony Framework. then you probably want to register this encoder
as a service in your app. If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
that's done automatically!

.. tip::

    If you're not using :ref:`autoconfigure <services-autoconfigure>`, make sure
    to register your class as a service and tag it with ``serializer.encoder``.

Now you'll be able to serialize and deserialize YAML!
