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
:phpfunction:`json_encode` and :phpfunction:`json_decode` functions.

It can be useful to modify how these functions operate in certain instances
by providing options such as ``JSON_PRESERVE_ZERO_FRACTION``. You can use
the serialization context to pass in these options using the key
``json_encode_options`` or ``json_decode_options`` respectively::

    $this->serializer->serialize($data, 'json', [
        'json_encode_options' => \JSON_PRESERVE_ZERO_FRACTION,
    ]);

All context options available for the JSON encoder are:

``json_decode_associative`` (default: ``false``)
    If set to ``true`` returns the result as an array, returns a nested ``stdClass`` hierarchy otherwise.
``json_decode_detailed_errors`` (default: ``false``)
    If set to ``true`` exceptions thrown on parsing of JSON are more specific. Requires `seld/jsonlint`_ package.
``json_decode_options`` (default: ``0``)
    Flags passed to :phpfunction:`json_decode` function.
``json_encode_options`` (default: ``\JSON_PRESERVE_ZERO_FRACTION``)
    Flags passed to :phpfunction:`json_encode` function.
``json_decode_recursion_depth`` (default: ``512``)
    Sets maximum recursion depth.

The ``CsvEncoder``
------------------

The ``CsvEncoder`` encodes to and decodes from CSV. Several :ref:`context options <serializer-context>`
are available to customize the behavior of the encoder:

``csv_delimiter`` (default: ``,``)
    Sets the field delimiter separating values (one character only).
``csv_enclosure`` (default: ``"``)
    Sets the field enclosure (one character only).
``csv_end_of_line`` (default: ``\n``)
    Sets the character(s) used to mark the end of each line in the CSV file.
``csv_key_separator`` (default: ``.``)
    Sets the separator for array's keys during its flattening
``csv_headers`` (default: ``[]``, inferred from input data's keys)
    Sets the order of the header and data columns.
    E.g. if you set it to ``['a', 'b', 'c']`` and serialize
    ``['c' => 3, 'a' => 1, 'b' => 2]``, the order will be ``a,b,c`` instead
    of the input order (``c,a,b``).
    When encoding, this option also accepts an associative array to
    :ref:`select and rename the columns <serializer-csv-headers-mapping>`.
``csv_escape_formulas`` (default: ``false``)
    Escapes fields containing formulas by prepending them with a ``\t`` character.
``as_collection`` (default: ``true``)
    Always returns results as a collection, even if only one line is decoded.
``no_headers`` (default: ``false``)
    Setting to ``false`` will use first row as headers when denormalizing,
    ``true`` generates numeric headers.
``output_utf8_bom`` (default: ``false``)
    Outputs special `UTF-8 BOM`_ along with encoded data.

.. _serializer-csv-headers-mapping:

Selecting and Renaming CSV Columns
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.2

    Support for associative arrays in ``csv_headers`` was introduced in Symfony 8.2.

When encoding, ``csv_headers`` also accepts an associative array whose keys are
the labels of the CSV columns and whose values are the paths read from each
row::

    use Symfony\Component\Serializer\Encoder\CsvEncoder;

    $data = [
        ['firstName' => 'Kévin', 'lastName' => 'Dunglas', 'company' => ['name' => 'Les-Tilleuls.coop']],
        ['firstName' => 'Nicolas', 'lastName' => 'Grekas', 'company' => ['name' => 'Symfony']],
    ];

    $encoder->encode($data, 'csv', [
        CsvEncoder::HEADERS_KEY => [
            'Name' => 'firstName',
            'Company' => 'company.name',
        ],
    ]);

    // Name,Company
    // Kévin,Les-Tilleuls.coop
    // Nicolas,Symfony

Contrary to the list syntax, only the listed columns are encoded, in the given
order, and no other column is appended (this is why ``lastName`` is missing from
the output above). Rows are flattened before the paths are resolved, so nested
values are read using the ``csv_key_separator`` option (``company.name`` in the
example). A path that doesn't exist in a given row produces an empty cell.

This syntax only applies to encoding. When decoding, ``csv_headers`` still
expects a list of column names, and it's only used when the ``no_headers``
option is set to ``true``.

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
    changed by adding the ``\XML_COMMENT_NODE`` option to the ``XmlEncoder::ENCODER_IGNORED_NODE_TYPES``
    key of the ``$defaultContext`` of the ``XmlEncoder`` constructor or
    directly to the ``$context`` argument of the ``encode()`` method::

        $xmlEncoder->encode($array, 'xml', [XmlEncoder::ENCODER_IGNORED_NODE_TYPES => [\XML_COMMENT_NODE]]);

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
``save_options`` (default: ``0``)
    XML saving `options with libxml`_.
``remove_empty_tags`` (default: ``false``)
    If set to ``true``, removes all empty tags in the generated XML.
``cdata_wrapping`` (default: ``true``)
    If set to ``false``, will not wrap any value containing one of the
    following characters ( ``<``, ``>``, ``&``) in `a CDATA section`_ like
    following: ``<![CDATA[...]]>``.
``cdata_wrapping_pattern`` (default: ``/[<>&]/``)
    A regular expression pattern to determine if a value should be wrapped
    in a CDATA section.
``cdata_wrapping_name_pattern`` (default: ``false``)
    A regular expression pattern that defines the names of fields whose values
    should always be wrapped in a CDATA section, even if their contents don't
    require it. Example: ``'/(firstname|lastname)/'``
``ignore_empty_attributes`` (default: ``false``)
    If set to true, ignores all attributes with empty values in the generated XML
``preserve_numeric_keys`` (default: ``false``)
    If set to true, it keeps numeric array indexes (e.g. ``<item key="0">``)
    instead of collapsing them into ``<item>`` nodes.

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

Example with ``preserve_numeric_keys``::

    use Symfony\Component\Serializer\Encoder\XmlEncoder;

    $data = [
        'person' => [
            ['firstname' => 'Benjamin', 'lastname' => 'Alexandre'],
            ['firstname' => 'Damien', 'lastname' => 'Clay'],
        ],
    ];

    $xmlEncoder->encode($data, 'xml', ['preserve_numeric_keys' => false]);
    // outputs:
    //<response>
    //  <person>
    //    <firstname>Benjamin</firstname>
    //    <lastname>Alexandre</lastname>
    //  </person>
    //  <person>
    //    <firstname>Damien</firstname>
    //    <lastname>Clay</lastname>
    //  </person>
    //</response>

    $xmlEncoder->encode($data, 'xml', ['preserve_numeric_keys' => true]);
    // outputs:
    //<response>
    //  <person>
    //    <item key="0">
    //        <firstname>Benjamin</firstname>
    //        <lastname>Alexandre</lastname>
    //    </item>
    //      <item key="1">
    //        <firstname>Damien</firstname>
    //        <lastname>Clay</lastname>
    //      </item>
    //  </person>
    //</response>

The ``YamlEncoder``
-------------------

This encoder requires the :doc:`Yaml Component </components/yaml>` and
transforms from and to Yaml.

Like other encoders, several :ref:`context options <serializer-context>` are
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

    // src/Serializer/NeonEncoder.php
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
to register your class as a service and tag it with
:ref:`serializer.encoder <reference-dic-tags-serializer-encoder>`:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Serializer\NeonEncoder:
                tags: ['serializer.encoder']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Serializer\NeonEncoder;

        return App::config([
            'services' => [
                NeonEncoder::class => [
                    'tags' => ['serializer.encoder'],
                ],
            ],
        ]);

Now you'll be able to serialize and deserialize NEON!

.. _JSON: https://www.json.org/json-en.html
.. _XML: https://www.w3.org/XML/
.. _YAML: https://yaml.org/
.. _CSV: https://tools.ietf.org/html/rfc4180
.. _seld/jsonlint: https://github.com/Seldaek/jsonlint
.. _`UTF-8 BOM`: https://en.wikipedia.org/wiki/Byte_order_mark
.. _`DOM XML_* constants`: https://www.php.net/manual/en/dom.constants.php
.. _`options with libxml`: https://www.php.net/manual/en/libxml.constants.php
.. _`a CDATA section`: https://en.wikipedia.org/wiki/CDATA
.. _NEON: https://neon.nette.org/
