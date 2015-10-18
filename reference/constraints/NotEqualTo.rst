NotEqualTo
==========

.. versionadded:: 2.3
    The ``NotEqualTo`` constraint was introduced in Symfony 2.3.

Validates that a value is **not** equal to another value, defined in the
options. To force that a value is equal, see
:doc:`/reference/constraints/EqualTo`.

.. caution::

    This constraint compares using ``!=``, so ``3`` and ``"3"`` are considered
    equal. Use :doc:`/reference/constraints/NotIdenticalTo` to compare with
    ``!==``.

+----------------+-------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                   |
+----------------+-------------------------------------------------------------------------+
| Options        | - `value`_                                                              |
|                | - `message`_                                                            |
|                | - `payload`_                                                            |
+----------------+-------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\NotEqualTo`         |
+----------------+-------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\NotEqualToValidator`|
+----------------+-------------------------------------------------------------------------+

Basic Usage
-----------

If you want to ensure that the ``age`` of a ``Person`` class is not equal
to ``15``, you could do the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Person.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\NotEqualTo(
             *     value = 15
             * )
             */
            protected $age;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Person:
            properties:
                age:
                    - NotEqualTo:
                        value: 15

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Person">
                <property name="age">
                    <constraint name="NotEqualTo">
                        <option name="value">15</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Person.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('age', new Assert\NotEqualTo(array(
                    'value' => 15,
                )));
            }
        }

Options
-------

.. include:: /reference/constraints/_comparison-value-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should not be equal to {{ compared_value }}.``

This is the message that will be shown if the value is equal.

.. include:: /reference/constraints/_payload-option.rst.inc
