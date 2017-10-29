.. index::
   single: Serializer, Encoders

Encoders
========

Encoders basically turn **arrays** into **formats** and vice versa.
They implement
:class:`Symfony\\Component\\Serializer\\Encoder\\EncoderInterface` for
encoding (array to format) and
:class:`Symfony\\Component\\Serializer\\Encoder\\DecoderInterface` for
decoding (format to array).

You can add new encoders to a Serializer instance by using its second constructor argument::

    use Symfony\Component\Serializer\Serializer;
    use Symfony\Component\Serializer\Encoder\XmlEncoder;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;

    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $serializer = new Serializer(array(), $encoders);

Built-in Encoders
-----------------

The Serializer component provides built-in encoders:

* :class:`Symfony\\Component\\Serializer\\Encoder\\CsvEncoder` to encode/decode CSV
* :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder` to encode/decode JSON
* :class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder` to encode/decode XML
* :class:`Symfony\\Component\\Serializer\\Encoder\\YamlEncoder` to encode/decode Yaml

.. versionadded:: 3.2
    The :class:`Symfony\\Component\\Serializer\\Encoder\\CsvEncoder` and the
    :class:`Symfony\\Component\\Serializer\\Encoder\\YamlEncoder` were introduced in
    Symfony 3.2.

The ``JsonEncoder``
~~~~~~~~~~~~~~~~~~~

The ``JsonEncoder`` encodes to and decodes from JSON strings, based on the PHP
:phpfunction:`json_encode` and :phpfunction:`json_decode` functions.

The ``XmlEncoder``
~~~~~~~~~~~~~~~~~~

This encoder transforms arrays into XML and vice versa.

For example, take an object normalized as following::

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

The ``YamlEncoder``
~~~~~~~~~~~~~~~~~~~

This encoder requires the :doc:`Yaml Component </components/yaml>` and
transforms from and to Yaml.
