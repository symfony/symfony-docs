NotEqualTo
==========

Validates that a value is **not** equal to another value, defined in the
options. To force that a value is equal, see
:doc:`/reference/constraints/EqualTo`.

.. caution::

    This constraint compares using ``!=``, so ``3`` and ``"3"`` are considered
    equal. Use :doc:`/reference/constraints/NotIdenticalTo` to compare with
    ``!==``.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
            - `propertyPath`_
            - `value`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\NotEqualTo`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\NotEqualToValidator`
==========  ===================================================================

Basic Usage
-----------

If you want to ensure that the ``firstName`` of a ``Person`` is not equal to
``Mary`` and that the ``age`` of a ``Person`` class is not ``15``, you could do
the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\NotEqualTo("Mary")
             */
            protected $firstName;

            /**
             * @Assert\NotEqualTo(
             *     value = 15
             * )
             */
            protected $age;
        }

    .. code-block:: php-attributes

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            #[Assert\NotEqualTo('Mary')]
            protected $firstName;

            #[Assert\NotEqualTo(
                value: 15,
            )]
            protected $age;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                firstName:
                    - NotEqualTo: Mary
                age:
                    - NotEqualTo:
                        value: 15

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="firstName">
                    <constraint name="NotEqualTo">
                        Mary
                    </constraint>
                </property>
                <property name="age">
                    <constraint name="NotEqualTo">
                        <option name="value">15</option>
                    </constraint>
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
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new Assert\NotEqualTo('Mary'));

                $metadata->addPropertyConstraint('age', new Assert\NotEqualTo([
                    'value' => 15,
                ]));
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should not be equal to {{ compared_value }}.``

This is the message that will be shown if the value is equal.

You can use the following parameters in this message:

=============================  ================================================
Parameter                      Description
=============================  ================================================
``{{ compared_value }}``       The expected value
``{{ compared_value_type }}``  The expected value type
``{{ value }}``                The current (invalid) value
=============================  ================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. include:: /reference/constraints/_comparison-propertypath-option.rst.inc

.. include:: /reference/constraints/_comparison-value-option.rst.inc
