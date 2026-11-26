Xml
===

Validates that a value has valid `XML`_ syntax. Optionally, it can also
validate the content against an XSD schema.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Xml`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\XmlValidator`
==========  ===================================================================

.. versionadded:: 8.1

    The Xml constraint was introduced in Symfony 8.1.

Basic Usage
-----------

The ``Xml`` constraint can be applied to a property or a getter method:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Report.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Report
        {
            #[Assert\Xml]
            private string $xmlContent;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Report:
            properties:
                xmlContent:
                    - Xml: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Report">
                <property name="xmlContent">
                    <constraint name="Xml"/>
                </property>
            </class>
        </constraint-mapping>

Validating Against an XSD Schema
--------------------------------

You can validate that the XML content conforms to a specific XSD schema using
the ``schemaPath`` option:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Report.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Report
        {
            #[Assert\Xml(schemaPath: 'path/to/schema.xsd')]
            private string $xmlContent;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Report:
            properties:
                xmlContent:
                    - Xml:
                        schemaPath: path/to/schema.xsd

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Report">
                <property name="xmlContent">
                    <constraint name="Xml">
                        <option name="schemaPath">path/to/schema.xsd</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

Options
-------

``formatMessage``
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not valid XML.``

This message is shown if the underlying data is not valid XML.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
===============  ==============================================================

.. include:: /reference/constraints/_groups-option.rst.inc

``maxSize``
~~~~~~~~~~~

**type**: ``integer`` **default**: ``5242880`` (5 MiB)

The maximum size, in bytes, of the XML content. If the value is larger than
this, validation fails with the `tooLargeMessage`_.

.. include:: /reference/constraints/_payload-option.rst.inc

``schemaFlags``
~~~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``0``

A bitmask of `libxml constants`_ (e.g. ``LIBXML_SCHEMA_CREATE``) that enables
optional features of the XML parser when validating the content against the
schema defined in the `schemaPath`_ option.

``schemaMessage``
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value does not conform to the expected XSD schema.``

This message is shown if the underlying data doesn't conform to the XSD schema
defined in the `schemaPath`_ option.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ error }}``  The full error message from the XML parser
``{{ line }}``   The line where the error occurred
===============  ==============================================================

``schemaPath``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

The path to the XSD schema file to validate the XML content against. When this
option is set, the constraint validates not only that the content is well-formed
XML, but also that it conforms to the specified schema.

``tooLargeMessage``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This XML payload is too large ({{ size }} bytes): it exceeds the limit of {{ limit }} bytes.``

This message is shown if the size of the underlying data is larger than the
value of the `maxSize`_ option.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ size }}``   The size of the value (in bytes)
``{{ limit }}``  The maximum allowed size (in bytes)
===============  ==============================================================

.. _`XML`: https://en.wikipedia.org/wiki/XML
.. _`libxml constants`: https://php.net/manual/libxml.constants
