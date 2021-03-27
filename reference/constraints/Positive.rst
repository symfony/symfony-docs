Positive
========

Validates that a value is a positive number. Zero is neither positive nor
negative, so you must use :doc:`/reference/constraints/PositiveOrZero` if you
want to allow zero as value.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Positive`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThanValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraint ensures that the ``income`` of an ``Employee`` is a
positive number (greater than zero):

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Employee.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Employee
        {
            /**
             * @Assert\Positive
             */
            protected $income;
        }

    .. code-block:: php-attributes

        // src/Entity/Employee.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Employee
        {
            #[Assert\Positive]
            protected $income;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Employee:
            properties:
                income:
                    - Positive: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Employee">
                <property name="income">
                    <constraint name="Positive"></constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Employee.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;


        class Employee
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('income', new Assert\Positive());
            }
        }

Available Options
-----------------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be positive.``

The default message supplied when the value is not greater than zero.

You can use the following parameters in this message:

=============================  ================================================
Parameter                      Description
=============================  ================================================
``{{ compared_value }}``       Always zero
``{{ compared_value_type }}``  The expected value type
``{{ value }}``                The current (invalid) value
=============================  ================================================

.. include:: /reference/constraints/_payload-option.rst.inc
