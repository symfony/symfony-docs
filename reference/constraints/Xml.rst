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
the ``schema`` option:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Report.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Report
        {
            #[Assert\Xml(schema: 'path/to/schema.xsd')]
            private string $xmlContent;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Report:
            properties:
                xmlContent:
                    - Xml:
                        schema: path/to/schema.xsd

Options
-------

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not valid XML.``

This message is shown if the underlying data is not valid XML.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ error }}``  The full error message from the XML parser
``{{ line }}``   The line where the XML syntax error occurred
===============  ==============================================================

``schema``
~~~~~~~~~~

**type**: ``string`` **default**: ``null``

The path to the XSD schema file to validate the XML content against. When this
option is set, the constraint validates not only that the content is well-formed
XML, but also that it conforms to the specified schema.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`XML`: https://en.wikipedia.org/wiki/XML
