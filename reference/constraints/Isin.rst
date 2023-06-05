Isin
====

Validates that a value is a valid
`International Securities Identification Number (ISIN)`_.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Isin`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\IsinValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/UnitAccount.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class UnitAccount
        {
            #[Assert\Isin]
            protected string $isin;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\UnitAccount:
            properties:
                isin:
                    - Isin: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\UnitAccount">
                <property name="isin">
                    <constraint name="Isin"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/UnitAccount.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class UnitAccount
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('isin', new Assert\Isin());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

message
~~~~~~~

**type**: ``string`` default: ``This value is not a valid International Securities Identification Number (ISIN).``

The message shown if the given value is not a valid ISIN.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`International Securities Identification Number (ISIN)`: https://en.wikipedia.org/wiki/International_Securities_Identification_Number
