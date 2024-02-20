Negative
========

Validates that a value is a negative number. Zero is neither positive nor
negative, so you must use :doc:`/reference/constraints/NegativeOrZero` if you
want to allow zero as value.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Negative`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\LesserThanValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraint ensures that the ``withdraw`` of a  bank account
``TransferItem`` is a negative number (lesser than zero):

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/TransferItem.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class TransferItem
        {
            #[Assert\Negative]
            protected int $withdraw;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\TransferItem:
            properties:
                withdraw:
                    - Negative: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\TransferItem">
                <property name="withdraw">
                    <constraint name="Negative"></constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/TransferItem.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class TransferItem
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('withdraw', new Assert\Negative());
            }
        }

Available Options
-----------------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be negative.``

The default message supplied when the value is not less than zero.

You can use the following parameters in this message:

=============================  ================================================
Parameter                      Description
=============================  ================================================
``{{ compared_value }}``       Always zero
``{{ compared_value_type }}``  The expected value type
``{{ value }}``                The current (invalid) value
=============================  ================================================

.. include:: /reference/constraints/_payload-option.rst.inc
