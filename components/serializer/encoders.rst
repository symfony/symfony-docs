.. index::
   single: Serializer, Encoders

Encoders
========

Encoders basically turn **arrays** into **formats** and vice versa.
They implement :class:`Symfony\\Component\\Serializer\\Encoder\\EncoderInterface` for encoding (array to format) and :class:`Symfony\\Component\\Serializer\\Encoder\\DecoderInterface` for decoding (format to array).

You can add new encoders to a Serializer instance by using its second constructor argument::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;

    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $serializer = new Serializer(array(), $encoders);

Built-in encoders
-----------------

You can see in the example above that we use two encoders:

* :class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder` to encode/decode XML
* :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder` to encode/decode JSON

The ``XmlEncoder``
~~~~~~~~~~~~~~~~~~

This encoder transform arrays into XML and vice versa.

For example, we will guess that you have an object normalized as following::

    array('foo' => array(1, 2), 'bar' => true);

The ``XmlEncoder`` will encode this object like that::

    <?xml version="1.0"?>
    <response>
        <foo>1</foo>
        <foo>2</foo>
        <bar>1</bar>
    </response>

Be aware that this encoder will consider keys beginning with ``@`` as attributes::

    $encoder = new XmlEncoder();
    $encoder->encode(array('foo' => array('@bar' => 'value')));
    // will return:
    // <?xml version="1.0"?>
    // <response>
    //     <foo bar="value" />
    // </response>

The ``JsonEncoder``
~~~~~~~~~~~~~~~~~~~

The ``JsonEncoder`` is much simpler and is based on the PHP `json_encode`_ and `json_decode`_ functions.

.. _json_encode: https://secure.php.net/manual/fr/function.json-encode.php
.. _json_decode: https://secure.php.net/manual/fr/function.json-decode.php

Custom encoders
---------------

If you need to support another format than XML and JSON, you can create your own encoder.
We will guess that you want to serialize and deserialize Yaml. For that, we will use
:doc:`/components/yaml/index`::

    namespace App\Encoder;

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
            return 'json' === $format;
        }

        public function decode($data, $format, array $context = array())
        {
            return Yaml::parse($data);
        }

        public function supportsDecoding($format)
        {
            return 'json' === $format;
        }
    }

Then just pass it to your serializer::

    use Symfony\Component\Serializer\Serializer;

    $serializer = new Serializer(array(), array(new App\Encoder\YamlEncoder()));

Now you'll be able to serialize and deserialize Yaml.
