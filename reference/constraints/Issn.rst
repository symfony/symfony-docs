Issn
====

Validates that a value is a valid
`International Standard Serial Number (ISSN)`_.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Issn`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\IssnValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Journal.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Journal
        {
            #[Assert\Issn]
            protected string $issn;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Journal:
            properties:
                issn:
                    - Issn: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Journal">
                <property name="issn">
                    <constraint name="Issn"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Journal.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Journal
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('issn', new Assert\Issn());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

``caseSensitive``
~~~~~~~~~~~~~~~~~

**type**: ``boolean`` default: ``false``

The validator will allow ISSN values to end with a lower case 'x' by default.
When switching this to ``true``, the validator requires an upper case 'X'.

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` default: ``This value is not a valid ISSN.``

The message shown if the given value is not a valid ISSN.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

``requireHyphen``
~~~~~~~~~~~~~~~~~

**type**: ``boolean`` default: ``false``

The validator will allow non hyphenated ISSN values by default. When switching
this to ``true``, the validator requires a hyphenated ISSN value.

.. _`International Standard Serial Number (ISSN)`: https://en.wikipedia.org/wiki/Issn
