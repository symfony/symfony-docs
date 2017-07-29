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
as a service in your app. If you're using the :ref:`default services.yml configuration <service-container-services-load-example>`,
that's done automatically! 

.. tip::

    If you're not using autoconfigure, make sure to register your class as a service
    and tag it with ``serializer.encoder``.

Now you'll be able to serialize and deserialize Yaml!

.. _tracker: https://github.com/symfony/symfony/issues
