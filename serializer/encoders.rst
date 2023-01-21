Serializer Encoders
===================

The Serializer component provides several built-in encoders:

:class:`Symfony\\Component\\Serializer\\Encoder\\JsonEncoder`
    This class encodes and decodes data in `JSON`_.

:class:`Symfony\\Component\\Serializer\\Encoder\\XmlEncoder`
    This class encodes and decodes data in `XML`_.

:class:`Symfony\\Component\\Serializer\\Encoder\\YamlEncoder`
    This encoder encodes and decodes data in `YAML`_. This encoder requires the
    :doc:`Yaml Component </components/yaml>`.

:class:`Symfony\\Component\\Serializer\\Encoder\\CsvEncoder`
    This encoder encodes and decodes data in `CSV`_.

.. note::

    You can also create your own encoder to use another structure. Read more at
    :ref:`Creating a Custom Encoder <serializer-custom-encoder>` below.

All these encoders are enabled by default when using the Serializer component
in a Symfony application.

The ``JsonEncoder``
-------------------

The ``JsonEncoder`` encodes to and decodes from JSON strings, based on the PHP
:phpfunction:`json_encode` and :phpfunction:`json_decode` functions. It can be
useful to modify how these functions operate in certain instances by providing
options such as ``JSON_PRESERVE_ZERO_FRACTION``. You can use the serialization
context to pass in these options using the key ``json_encode_options`` or
``json_decode_options`` respectively::

    $this->serializer->serialize($data, 'json', [
        'json_encode_options' => \JSON_PRESERVE_ZERO_FRACTION,
    ]);

The ``CsvEncoder``
------------------

The ``CsvEncoder`` encodes to and decodes from CSV. Serveral :ref:`context options <serializer-context>`
are available to customize the behavior of the encoder:

``csv_delimiter`` (default: ``,``)
    Sets the field delimiter separating values (one character only).
``csv_enclosure`` (default: ``"``)
    Sets the field enclosure (one character only).
``csv_end_of_line`` (default: ``\n``)
    Sets the character(s) used to mark the end of each line in the CSV file.

    .. versionadded:: 5.3

        The ``csv_end_of_line`` option was introduced in Symfony 5.3.
``csv_escape_char`` (default: empty string)
    Sets the escape character (at most one character).
``csv_key_separator`` (default: ``.``)
    Sets the separator for array's keys during its flattening
``csv_headers`` (default: ``[]``, inferred from input data's keys)
    Sets the order of the header and data columns.
    E.g. if you set it to ``['a', 'b', 'c']`` and serialize
    ``['c' => 3, 'a' => 1, 'b' => 2]``, the order will be ``a,b,c`` instead
    of the input order (``c,a,b``).
``csv_escape_formulas`` (default: ``false``)
    Escapes fields containing formulas by prepending them with a ``\t`` character.
``as_collection`` (default: ``true``)
    Always returns results as a collection, even if only one line is decoded.
``no_headers`` (default: ``false``)
    Setting to ``false`` will use first row as headers when denormalizing,
    ``true`` generates numeric headers.
``output_utf8_bom`` (default: ``false``)
    Outputs special `UTF-8 BOM`_ along with encoded data.

The ``XmlEncoder``
------------------

This encoder transforms PHP values into XML and vice versa.

For example, take an object that is normalized as following::

    $normalizedArray = ['foo' => [1, 2], 'bar' => true];

The ``XmlEncoder`` will encode this object like:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <response>
        <foo>1</foo>
        <foo>2</foo>
        <bar>1</bar>
    </response>

The special ``#`` key can be used to define the data of a node::

    ['foo' => ['@bar' => 'value', '#' => 'baz']];

    /* is encoded as follows:
       <?xml version="1.0"?>
       <response>
           <foo bar="value">
              baz
           </foo>
       </response>
     */

Furthermore, keys beginning with ``@`` will be considered attributes, and
the key  ``#comment`` can be used for encoding XML comments::

    $encoder = new XmlEncoder();
    $xml = $encoder->encode([
        'foo' => ['@bar' => 'value'],
        'qux' => ['#comment' => 'A comment'],
    ], 'xml');
    /* will return:
       <?xml version="1.0"?>
       <response>
           <foo bar="value"/>
           <qux><!-- A comment --!><qux>
       </response>
     */

You can pass the context key ``as_collection`` in order to have the results
always as a collection.

.. note::

    You may need to add some attributes on the root node::

        $encoder = new XmlEncoder();
        $encoder->encode([
            '@attribute1' => 'foo',
            '@attribute2' => 'bar',
            '#' => ['foo' => ['@bar' => 'value', '#' => 'baz']]
        ], 'xml');

        // will return:
        // <?xml version="1.0"?>
        // <response attribute1="foo" attribute2="bar">
        // <foo bar="value">baz</foo>
        // </response>

.. tip::

    XML comments are ignored by default when decoding contents, but this
    behavior can be changed with the optional context key ``XmlEncoder::DECODER_IGNORED_NODE_TYPES``.

    Data with ``#comment`` keys are encoded to XML comments by default. This can be
    changed with the optional ``$encoderIgnoredNodeTypes`` argument of the
    ``XmlEncoder`` class constructor.

The ``XmlEncoder`` Context Options
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

These are the options available on the :ref:`serializer context <serializer-context>`:

``xml_format_output`` (default: ``false``)
    If set to true, formats the generated XML with line breaks and indentation.
``xml_version`` (default: ``1.0``)
    Sets the XML version attribute.
``xml_encoding`` (default: ``utf-8``)
    Sets the XML encoding attribute.
``xml_standalone`` (default: ``true``)
    Adds standalone attribute in the generated XML.
``xml_type_cast_attributes`` (default: ``true``)
    This provides the ability to forget the attribute type casting.
``xml_root_node_name`` (default: ``response``)
    Sets the root node name.
``as_collection`` (default: ``false``)
    Always returns results as a collection, even if only one line is decoded.
``decoder_ignored_node_types`` (default: ``[\XML_PI_NODE, \XML_COMMENT_NODE]``)
    Array of node types (`DOM XML_* constants`_) to be ignored while decoding.
``encoder_ignored_node_types`` (default: ``[]``)
    Array of node types (`DOM XML_* constants`_) to be ignored while encoding.
``load_options`` (default: ``\LIBXML_NONET | \LIBXML_NOBLANKS``)
    XML loading `options with libxml`_.
``remove_empty_tags`` (default: ``false``)
    If set to true, removes all empty tags in the generated XML.

Example with a custom ``context``::

    use Symfony\Component\Serializer\Encoder\XmlEncoder;

    $data = [
        'id' => 'IDHNQIItNyQ',
        'date' => '2019-10-24',
    ];

    $xmlEncoder->encode($data, 'xml', ['xml_format_output' => true]);
    // outputs:
    // <?xml version="1.0"?>
    // <response>
    //   <id>IDHNQIItNyQ</id>
    //   <date>2019-10-24</date>
    // </response>

    $xmlEncoder->encode($data, 'xml', [
        'xml_format_output' => true,
        'xml_root_node_name' => 'track',
        'encoder_ignored_node_types' => [
            \XML_PI_NODE, // removes XML declaration (the leading xml tag)
        ],
    ]);
    // outputs:
    // <track>
    //   <id>IDHNQIItNyQ</id>
    //   <date>2019-10-24</date>
    // </track>

The ``YamlEncoder``
-------------------

This encoder requires the :doc:`Yaml Component </components/yaml>` and
transforms from and to Yaml.

Like other encoder, several :ref:`context options <serializer-context>` are
available:

``yaml_inline`` (default: ``0``)
    The level where you switch to inline YAML.
``yaml_indent`` (default: ``0``)
    The level of indentation (used internally).
``yaml_flags`` (default: ``0``)
    A bit field of ``Yaml::DUMP_*``/``Yaml::PARSE_*`` constants to
    customize the encoding/decoding YAML string.

.. _serializer-custom-encoder:

Creating a Custom Encoder
-------------------------

Imagine you want to serialize and deserialize `NEON`_. For that you'll have to
create your own encoder::

    // src/Serializer/YamlEncoder.php
    namespace App\Serializer;

    use Nette\Neon\Neon;
    use Symfony\Component\Serializer\Encoder\DecoderInterface;
    use Symfony\Component\Serializer\Encoder\EncoderInterface;

    class NeonEncoder implements EncoderInterface, DecoderInterface
    {
        public function encode($data, string $format, array $context = [])
        {
            return Neon::encode($data);
        }

        public function supportsEncoding(string $format)
        {
            return 'neon' === $format;
        }

        public function decode(string $data, string $format, array $context = [])
        {
            return Neon::decode($data);
        }

        public function supportsDecoding(string $format)
        {
            return 'neon' === $format;
        }
    }

.. tip::

    If you need access to ``$context`` in your ``supportsDecoding`` or
    ``supportsEncoding`` method, make sure to implement
    ``Symfony\Component\Serializer\Encoder\ContextAwareDecoderInterface``
    or ``Symfony\Component\Serializer\Encoder\ContextAwareEncoderInterface`` accordingly.

Registering it in Your App
~~~~~~~~~~~~~~~~~~~~~~~~~~

If you use the Symfony Framework, then you probably want to register this encoder
as a service in your app. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
that's done automatically!

If you're not using :ref:`autoconfigure <services-autoconfigure>`, make sure
to register your class as a service and tag it with ``serializer.encoder``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Serializer\NeonEncoder:
                tags: ['serializer.encoder']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Serializer\NeonEncoder">
                    <tag name="serializer.encoder"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Serializer\NeonEncoder;

        return function(ContainerConfigurator $container) {
            // ...

            $services->set(NeonEncoder::class)
                ->tag('serializer.encoder')
            ;
        };

Now you'll be able to serialize and deserialize NEON!

.. _JSON: https://www.json.org/json-en.html
.. _XML: https://www.w3.org/XML/
.. _YAML: https://yaml.org/
.. _CSV: https://tools.ietf.org/html/rfc4180
.. _`UTF-8 BOM`: https://en.wikipedia.org/wiki/Byte_order_mark
.. _`DOM XML_* constants`: https://www.php.net/manual/en/dom.constants.php
.. _`options with libxml`: https://www.php.net/manual/en/libxml.constants.php
.. _NEON: https://ne-on.org/
