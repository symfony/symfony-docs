CardScheme
==========

.. versionadded:: 2.2
    The CardScheme validation is new in Symfony 2.2.

This constraint ensures that a credit card number is valid for a given credit card
company. It can be used to validate the number before trying to initiate a payment 
through a payment gateway.

+----------------+--------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                    |
+----------------+--------------------------------------------------------------------------+
| Options        | - `schemes`_                                                             |
|                | - `message`_                                                             |
+----------------+--------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\CardScheme`          |
+----------------+--------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\CardSchemeValidator` |
+----------------+--------------------------------------------------------------------------+

Basic Usage
-----------

To use the CardScheme validator, simply apply it to a property or method on an 
object that will contain a credit card number.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/SubscriptionBundle/Resources/config/validation.yml
        Acme\SubscriptionBundle\Entity\Transaction:
            properties:
                cardNumber:
                    - CardScheme:
                        schemes: [VISA]
                        message: You credit card number is invalid.

    .. code-block:: xml

        <!-- src/Acme/SubscriptionBundle/Resources/config/validation.xml -->
        <class name="Acme\SubscriptionBundle\Entity\Transaction">
            <property name="cardNumber">
                <constraint name="CardScheme">
                    <option name="schemes">
                        <value>VISA</value>
                    </option>
                    <option name="message">You credit card number is invalid.</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/SubscriptionBundle/Entity/Transaction.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            /**
             * @Assert\CardScheme(schemes = {"VISA"}, message = "You credit card number is invalid.")
             */
            protected $cardNumber;
        }

    .. code-block:: php

        // src/Acme/SubscriptionBundle/Entity/Transaction.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\CardScheme;

        class Transaction
        {
            protected $cardNumber;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('cardSchema', new CardScheme(array(
                    'schemes' => array(
                        'VISA'
                    ),
                    'message' => 'You credit card number is invalid.',
                )));
            }
        }

Available Options
-----------------

schemes
-------

**type**: ``array``

The name of the number scheme used to validate the credit card number. Valid values are:

* AMEX
* CHINA_UNIONPAY
* DINERS
* DISCOVER
* INSTAPAYMENT
* JCB
* LASER
* MAESTRO
* MASTERCARD
* VISA

For more information about the used schemes, see `Wikipedia`_.

message
~~~~~~~

**type**: ``string`` **default**: ``Unsupported card type or invalid card number``

The default message supplied when the value does not pass the CardScheme check.

.. _`Wikipedia`: http://en.wikipedia.org/wiki/Bank_card_number#Issuer_identification_number_.28IIN.29