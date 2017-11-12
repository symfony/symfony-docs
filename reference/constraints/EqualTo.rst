EqualTo
=======

Validates that a value is equal to another value, defined in the options.
To force that a value is *not* equal, see :doc:`/reference/constraints/NotEqualTo`.

.. caution::

    This constraint compares using ``==``, so ``3`` and ``"3"`` are considered
    equal. Use :doc:`/reference/constraints/IdenticalTo` to compare with
    ``===``.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `value`_                                                            |
|                | - `message`_                                                          |
|                | - `payload`_                                                          |
|                | - `propertyPath`_                                                     |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\EqualTo`          |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\EqualToValidator` |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

If you want to ensure that the ``firstName`` of a ``Person`` class is equal to ``Mary``
and that the ``age`` is ``20``, you could do the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\EqualTo("Mary")
             */
            protected $firstName;

            /**
             * @Assert\EqualTo(
             *     value = 20
             * )
             */
            protected $age;
        }

    .. code-block:: yaml

        # src/Resources/config/validation.yml
        App\Entity\Person:
            properties:
                firstName:
                    - EqualTo: Mary
                age:
                    - EqualTo:
                        value: 20

    .. code-block:: xml

        <!-- src/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="firstName">
                    <constraint name="EqualTo">
                        <value>Mary</value>
                    </constraint>
                </property>
                <property name="age">
                    <constraint name="EqualTo">
                        <option name="value">20</option>
                    </constraint>
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
                $metadata->addPropertyConstraint('firstName', new Assert\EqualTo('Mary'));

                $metadata->addPropertyConstraint('age', new Assert\EqualTo(array(
                    'value' => 20,
                )));
            }
        }

Options
-------

.. include:: /reference/constraints/_comparison-value-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be equal to {{ compared_value }}.``

This is the message that will be shown if the value is not equal.

.. include:: /reference/constraints/_payload-option.rst.inc

.. include:: /reference/constraints/_comparison-propertypath-option.rst.inc
