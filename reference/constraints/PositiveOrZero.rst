PositiveOrZero
==============

Validates that a value is a positive number or equal to zero. If you don't
want to allow zero as value, use :doc:`/reference/constraints/Positive` instead.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\PositiveOrZero`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThanOrEqualValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraint ensures that the number of ``siblings`` of a ``Person``
is positive or zero:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            #[Assert\PositiveOrZero]
            protected int $siblings;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                siblings:
                    - PositiveOrZero: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="siblings">
                    <constraint name="PositiveOrZero"></constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Person
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('siblings', new Assert\PositiveOrZero());
            }
        }

Available Options
-----------------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be either positive or zero.``

The default message supplied when the value is not greater than or equal to zero.

You can use the following parameters in this message:

=============================  ================================================
Parameter                      Description
=============================  ================================================
``{{ compared_value }}``       Always zero
``{{ compared_value_type }}``  The expected value type
``{{ value }}``                The current (invalid) value
=============================  ================================================

.. include:: /reference/constraints/_payload-option.rst.inc
