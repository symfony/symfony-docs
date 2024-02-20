Luhn
====

This constraint is used to ensure that a credit card number passes the
`Luhn algorithm`_. It is useful as a first step to validating a credit
card: before communicating with a payment gateway.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Luhn`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\LuhnValidator`
==========  ===================================================================

Basic Usage
-----------

To use the Luhn validator, apply it to a property on an object that
will contain a credit card number.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Transaction.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            #[Assert\Luhn(message: 'Please check your credit card number.')]
            protected string $cardNumber;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Transaction:
            properties:
                cardNumber:
                    - Luhn:
                        message: Please check your credit card number.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Transaction">
                <property name="cardNumber">
                    <constraint name="Luhn">
                        <option name="message">Please check your credit card number.</option>
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
                $metadata->addPropertyConstraint('cardNumber', new Assert\Luhn([
                    'message' => 'Please check your credit card number',
                ]));
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``Invalid card number.``

The default message supplied when the value does not pass the Luhn check.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`Luhn algorithm`: https://en.wikipedia.org/wiki/Luhn_algorithm
