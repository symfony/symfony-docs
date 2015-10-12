Bic
===

.. versionadded:: 2.8
    The Bic constraint was introduced in Symfony 2.8.

This constraint is used to ensure that a value has the proper format of a
`Business Identifier Code (BIC)`_. BIC is an internationally agreed means to
uniquely identify both financial and non-financial institutions.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                |
+----------------+-----------------------------------------------------------------------+
| Options        | - `message`_                                                          |
|                | - `payload`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Bic`              |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\BicValidator`     |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

To use the Bic validator, simply apply it to a property on an object that
will contain a Business Identifier Code (BIC).

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Transaction.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            /**
             * @Assert\Bic()
             */
            protected $businessIdentifierCode;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Transaction:
            properties:
                businessIdentifierCode:
                    - Bic: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Transaction">
                <property name="businessIdentifierCode">
                    <constraint name="Bic" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Transaction.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Transaction
        {
            protected $businessIdentifierCode;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('businessIdentifierCode', new Assert\Bic());
            }
        }

Available Options
-----------------

message
~~~~~~~

**type**: ``string`` **default**: ``This is not a valid Business Identifier Code (BIC).``

The default message supplied when the value does not pass the BIC check.

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`Business Identifier Code (BIC)`: https://en.wikipedia.org/wiki/Business_Identifier_Code
