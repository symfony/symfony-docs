IdenticalTo
===========

Validates that a value is identical to another value, defined in the options.
To force that a value is *not* identical, see
:doc:`/reference/constraints/NotIdenticalTo`.

.. caution::

    This constraint compares using ``===``, so ``3`` and ``"3"`` are *not*
    considered equal. Use :doc:`/reference/constraints/EqualTo` to compare
    with ``==``.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\IdenticalTo`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\IdenticalToValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraints ensure that:

* ``firstName`` of ``Person`` class is equal to ``Mary`` *and* is a string
* ``age`` is equal to ``20`` *and* is of type integer

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            #[Assert\IdenticalTo('Mary')]
            protected $firstName;

            #[Assert\IdenticalTo(
                value: 20,
            )]
            protected $age;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                firstName:
                    - IdenticalTo: Mary
                age:
                    - IdenticalTo:
                        value: 20

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="firstName">
                    <constraint name="IdenticalTo">
                        Mary
                    </constraint>
                </property>
                <property name="age">
                    <constraint name="IdenticalTo">
                        <option name="value">20</option>
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
                $metadata->addPropertyConstraint('firstName', new Assert\IdenticalTo('Mary'));

                $metadata->addPropertyConstraint('age', new Assert\IdenticalTo([
                    'value' => 20,
                ]));
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be identical to {{ compared_value_type }} {{ compared_value }}.``

This is the message that will be shown if the value is not identical.

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
