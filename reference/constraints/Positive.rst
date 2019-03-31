Positive
========

.. versionadded:: 4.3

    The ``Positive`` constraint was introduced in Symfony 4.3.

Validates that a value is a positive number. To force that a value is positive
number or equal to zero, see :doc:`/reference/constraints/PositiveOrZero`.
To force a value is negative, see :doc:`/reference/constraints/Negative`.

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

The following constraint ensure that:

* the ``income`` of an ``Employee`` is a positive number (greater than zero)

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

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Employee:
            properties:
                income:
                    - Positive

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

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Employee
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('income', new Assert\Positive();
            }
        }
