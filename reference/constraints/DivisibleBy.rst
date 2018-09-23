DivisibleBy
===========

Validates that a value is divisible by another value, defined in the options.

+----------------+---------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                     |
+----------------+---------------------------------------------------------------------------+
| Options        | - `value`_                                                                |
|                | - `message`_                                                              |
|                | - `payload`_                                                              |
|                | - `propertyPath`_                                                         |
+----------------+---------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\DivisibleBy`          |
+----------------+---------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\DivisibleByValidator` |
+----------------+---------------------------------------------------------------------------+

Basic Usage
-----------

The following constraints ensure that:

* the ``weight`` of the ``Item`` is provided in increments of ``0.25``
* the ``quantity`` of the ``Item`` must be divisible by ``5``

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Item.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Item
        {

            /**
             * @Assert\DivisibleBy(0.25)
             */
            protected $weight;

            /**
             * @Assert\DivisibleBy(
             *     value = 5
             * )
             */
            protected $quantity;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Item:
            properties:
                weight:
                    - DivisibleBy: 0.25
                quantity:
                    - DivisibleBy:
                        value: 5

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Item">
                <property name="weight">
                    <constraint name="DivisibleBy">
                        <value>0.25</value>
                    </constraint>
                </property>
                <property name="quantity">
                    <constraint name="DivisibleBy">
                        <option name="value">5</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Item.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Item
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('weight', new Assert\DivisibleBy(0.25));

                $metadata->addPropertyConstraint('quantity', new Assert\DivisibleBy(array(
                    'value' => 5,
                )));
            }
        }

Options
-------

.. include:: /reference/constraints/_comparison-value-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be a multiple of {{ compared_value }}.``

This is the message that will be shown if the value is not divisible by the
comparison value.

.. include:: /reference/constraints/_payload-option.rst.inc

propertyPath
~~~~~~~~~~~~

**type**: ``string``

It defines the object property whose value is used to make the comparison.

For example, if you want to compare the ``$value`` property of some object
with regard to the ``$increments`` property of the same object, use
``propertyPath="increments"`` in the comparison constraint of ``$value``.
