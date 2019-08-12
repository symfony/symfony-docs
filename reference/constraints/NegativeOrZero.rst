NegativeOrZero
==============

.. versionadded:: 4.3

    The ``NegativeOrZero`` constraint was introduced in Symfony 4.3.

Validates that a value is a negative number or equal to zero. If you don't
want to allow zero as value, use :doc:`/reference/constraints/Negative` instead.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\NegativeOrZero`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\LesserThanOrEqualValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraint ensures that the ``level`` of a ``UnderGroundGarage``
is a negative number or equal to zero:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/TransferItem.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class UnderGroundGarage
        {
            /**
             * @Assert\NegativeOrZero
             */
            protected $level;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\UnderGroundGarage:
            properties:
                level:
                    - NegativeOrZero: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\UnderGroundGarage">
                <property name="level">
                    <constraint name="NegativeOrZero"></constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/UnderGroundGarage.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class UnderGroundGarage
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('level', new Assert\NegativeOrZero());
            }
        }

Available Options
-----------------

.. include:: /reference/constraints/_groups-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be either negative or zero.``

The default message supplied when the value is not less than or equal to zero.

You can use the following parameters in this message:

=============================  ================================================
Parameter                      Description
=============================  ================================================
``{{ compared_value }}``       Always zero
``{{ compared_value_type }}``  The expected value type
``{{ value }}``                The current (invalid) value
=============================  ================================================

.. include:: /reference/constraints/_payload-option.rst.inc
