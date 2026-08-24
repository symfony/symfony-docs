Bic
===

This constraint is used to ensure that a value has the proper format of a
`Business Identifier Code (BIC)`_. BIC is an internationally agreed means to
uniquely identify both financial and non-financial institutions. You may also
check that the BIC's country code is the same as a given IBAN's one.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Bic`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\BicValidator`
==========  ===================================================================

Basic Usage
-----------

To use the Bic validator, apply it to a property on an object that
will contain a Business Identifier Code (BIC).

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Transaction.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            #[Assert\Bic]
            protected string $businessIdentifierCode;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Transaction:
            properties:
                businessIdentifierCode:
                    - Bic: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Transaction">
                <property name="businessIdentifierCode">
                    <constraint name="Bic"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Transaction.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Transaction
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('businessIdentifierCode', new Assert\Bic());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Available Options
-----------------

.. include:: /reference/constraints/_groups-option.rst.inc

``iban``
~~~~~~~~

**type**: ``string`` **default**: ``null``

An IBAN value to validate that its country code is the same as the BIC's one.

``ibanMessage``
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This Business Identifier Code (BIC) is not associated with IBAN {{ iban }}.``

The default message supplied when the value does not pass the combined BIC/IBAN check.

``ibanPropertyPath``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

It defines the object property whose value stores the IBAN used to check the BIC with.

For example, if you want to compare the ``$bic`` property of some object
with regard to the ``$iban`` property of the same object, use
``ibanPropertyPath="iban"`` in the comparison constraint of ``$bic``.

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This is not a valid Business Identifier Code (BIC).``

The default message supplied when the value does not pass the BIC check.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) BIC value
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

``mode``
~~~~~~~~

**type**: ``string`` **default**: ``Bic::VALIDATION_MODE_STRICT``

This option defines how the BIC is validated. The possible values are available
as constants in the :class:`Symfony\\Component\\Validator\\Constraints\\Bic` class:

* ``Bic::VALIDATION_MODE_STRICT`` validates the given value without any modification;
* ``Bic::VALIDATION_MODE_CASE_INSENSITIVE`` converts the given value to uppercase before validating it.

.. _`Business Identifier Code (BIC)`: https://en.wikipedia.org/wiki/Business_Identifier_Code
