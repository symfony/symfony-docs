Bic
===

.. versionadded:: 2.6
    The Bic constraint was introduced in Symfony 2.6.

This constraint is used to ensure that a property has the proper format of
a `Business Identifier Code (BIC)`_.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `country`_                                                          |
|                | - `message`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Bic`              |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\BicValidator`     |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

``Bic`` requires the `malkusch/bav`_ library. BAV's default configuration
is not recommended for BIC validation. Use a configuration with one of the
following ``DataBackendContainer`` implementations: ``PDODataBackendContainer`` or
``DoctrineBackendContainer``. This is preferably done by providing the file
``vendor/malkusch/bav/configuration.php``::

    // vendor/malkusch/bav/configuration.php
    namespace malkusch\bav;

    $configuration = new DefaultConfiguration();

    $pdo = new \PDO("mysql:host=localhost;dbname=test");
    $configuration->setDataBackendContainer(new PDODataBackendContainer($pdo));

    return $configuration;

To use the Bic validator, simply apply it to a property on an object that
will contain a BIC.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/SubscriptionBundle/Resources/config/validation.yml
        Acme\SubscriptionBundle\Entity\Transaction:
            properties:
                businessIdentifierCode:
                    - Bic:
                        country: de
                        message: This is not a valid BIC.

    .. code-block:: xml

        <!-- src/Acme/SubscriptionBundle/Resources/config/validation.xml -->
        <class name="Acme\SubscriptionBundle\Entity\Transaction">
            <property name="businessIdentifierCode">
                <constraint name="Bic">
                    <option name="country">de</option>
                    <option name="message">This is not a valid BIC.</option>
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
             * @Assert\Bic(country = Assert\Bic::DE, message = "This is not a valid BIC.")
             */
            protected $businessIdentifierCode;
        }

    .. code-block:: php

        // src/Acme/SubscriptionBundle/Entity/Transaction.php
        namespace Acme\SubscriptionBundle\Entity\Transaction;
        
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            protected $businessIdentifierCode;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('businessIdentifierCode', new Assert\Bic(array(
                    'country' => Assert\Bic::DE
                    'message' => 'This is not a valid BIC.',
                )));
            }
        }

Available Options
-----------------

country
~~~~~~~

**type**: ``string``

This option limits the valid BIC to one country. Currently the only supported country is
Germany with the value ``Bic::DE``.

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid BIC.``

The default message supplied when the value does not pass the Bic check.

.. _`Business Identifier Code (BIC)`: http://en.wikipedia.org/wiki/ISO_9362
.. _malkusch/bav: http://bav.malkusch.de/en/
