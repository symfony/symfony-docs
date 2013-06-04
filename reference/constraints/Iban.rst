Iban
====

.. versionadded:: 2.3
    The Iban constraint was added in Symfony 2.3.

This constraint is used to ensure that a bank account number has the proper format of
an `International Bank Account Number (IBAN)`_. IBAN is an internationally agreed means
of identifying bank accounts across national borders with a reduced risk of propagating
transcription errors.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `message`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Iban`             |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\IbanValidator`    |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

To use the Iban validator, simply apply it to a property on an object that
will contain an International Bank Account Number.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/SubscriptionBundle/Resources/config/validation.yml
        Acme\SubscriptionBundle\Entity\Transaction:
            properties:
                bankAccountNumber:
                    - Iban:
                        message: This is not a valid International Bank Account Number (IBAN).

    .. code-block:: xml

        <!-- src/Acme/SubscriptionBundle/Resources/config/validation.xml -->
        <class name="Acme\SubscriptionBundle\Entity\Transaction">
            <property name="bankAccountNumber">
                <constraint name="Iban">
                    <option name="message">This is not a valid International Bank Account Number (IBAN).</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/SubscriptionBundle/Entity/Transaction.php
        namespace Acme\SubscriptionBundle\Entity\Transaction;
        
        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            /**
             * @Assert\Iban(message = "This is not a valid International Bank Account Number (IBAN).")
             */
            protected $bankAccountNumber;
        }

    .. code-block:: php

        // src/Acme/SubscriptionBundle/Entity/Transaction.php
        namespace Acme\SubscriptionBundle\Entity\Transaction;
        
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            protected $bankAccountNumber;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('bankAccountNumber', new Assert\Iban(array(
                    'message' => 'This is not a valid International Bank Account Number (IBAN).',
                )));
            }
        }

Available Options
-----------------

message
~~~~~~~

**type**: ``string`` **default**: ``This is not a valid International Bank Account Number (IBAN).``

The default message supplied when the value does not pass the Iban check.

.. _`International Bank Account Number (IBAN)`: http://en.wikipedia.org/wiki/International_Bank_Account_Number
