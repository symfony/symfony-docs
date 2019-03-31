PositiveOrZero
==============

.. versionadded:: 4.3

    The ``PositiveOrZero`` constraint was introduced in Symfony 4.3.

Validates that a value is a positive number or equal to zero. To force that
a value is only a positiven umber, see :doc:`/reference/constraints/Positive`.
To force a value is negative or equal to zero,
see :doc:`/reference/constraints/NegativeOrZero`.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\PositiveOrZero`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThanOrEqualValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraint ensure that:

* the number of ``siblings`` of a ``Person`` is positive or zero

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\PositiveOrZero
             */
            protected $siblings;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                siblings:
                    - PositiveOrZero

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

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('siblings', new Assert\PositiveOrZero();
            }
        }
