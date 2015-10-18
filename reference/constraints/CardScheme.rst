CardScheme
==========

This constraint ensures that a credit card number is valid for a given credit
card company. It can be used to validate the number before trying to initiate
a payment through a payment gateway.

+----------------+--------------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                   |
+----------------+--------------------------------------------------------------------------+
| Options        | - `schemes`_                                                             |
|                | - `message`_                                                             |
|                | - `payload`_                                                             |
+----------------+--------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\CardScheme`          |
+----------------+--------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\CardSchemeValidator` |
+----------------+--------------------------------------------------------------------------+

Basic Usage
-----------

To use the ``CardScheme`` validator, simply apply it to a property or method
on an object that will contain a credit card number.

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Transaction.php
        namespace AppBundle\Entity\Transaction;

        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            /**
             * @Assert\CardScheme(
             *     schemes={"VISA"},
             *     message="Your credit card number is invalid."
             * )
             */
            protected $cardNumber;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Transaction:
            properties:
                cardNumber:
                    - CardScheme:
                        schemes: [VISA]
                        message: Your credit card number is invalid.

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Transaction">
                <property name="cardNumber">
                    <constraint name="CardScheme">
                        <option name="schemes">
                            <value>VISA</value>
                        </option>
                        <option name="message">Your credit card number is invalid.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Transaction.php
        namespace AppBundle\Entity\Transaction;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            protected $cardNumber;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('cardNumber', new Assert\CardScheme(array(
                    'schemes' => array(
                        'VISA'
                    ),
                    'message' => 'Your credit card number is invalid.',
                )));
            }
        }

Available Options
-----------------

schemes
~~~~~~~

**type**: ``mixed`` [:ref:`default option <validation-default-option>`]

This option is required and represents the name of the number scheme used
to validate the credit card number, it can either be a string or an array.
Valid values are:

* ``AMEX``
* ``CHINA_UNIONPAY``
* ``DINERS``
* ``DISCOVER``
* ``INSTAPAYMENT``
* ``JCB``
* ``LASER``
* ``MAESTRO``
* ``MASTERCARD``
* ``VISA``

For more information about the used schemes, see
`Wikipedia: Issuer identification number (IIN)`_.

message
~~~~~~~

**type**: ``string`` **default**: ``Unsupported card type or invalid card number.``

The message shown when the value does not pass the ``CardScheme`` check.

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`Wikipedia: Issuer identification number (IIN)`: https://en.wikipedia.org/wiki/Bank_card_number#Issuer_identification_number_.28IIN.29
