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

Two encoders are used in the example above:

* :class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder` to encode/decode XML
* :class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder` to encode/decode JSON

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

The ``JsonEncoder``
~~~~~~~~~~~~~~~~~~~

The ``JsonEncoder`` is much simpler and is based on the PHP
:phpfunction:`json_encode` and :phpfunction:`json_decode` functions.
