IBAN
====

This constraint is used to ensure that a bank account number has the proper
format of an `International Bank Account Number (IBAN)`_. IBAN is an
internationally agreed means of identifying bank accounts across national
borders with a reduced risk of propagating transcription errors.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Iban`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\IbanValidator`
==========  ===================================================================

Basic Usage
-----------

To use the IBAN validator, apply it to a property on an object that
will contain an International Bank Account Number.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Transaction.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            #[Assert\Iban(
                message: 'This is not a valid International Bank Account Number (IBAN).',
            )]
            protected string $bankAccountNumber;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Transaction:
            properties:
                bankAccountNumber:
                    - Iban:
                        message: This is not a valid International Bank Account Number (IBAN).

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Transaction">
                <property name="bankAccountNumber">
                    <constraint name="Iban">
                        <option name="message">
                            This is not a valid International Bank Account Number (IBAN).
                        </option>
                    </constraint>
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
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('bankAccountNumber', new Assert\Iban([
                    'message' => 'This is not a valid International Bank Account Number (IBAN).',
                ]));
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This is not a valid International Bank Account Number (IBAN).``

The default message supplied when the value does not pass the IBAN check.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`International Bank Account Number (IBAN)`: https://en.wikipedia.org/wiki/International_Bank_Account_Number
