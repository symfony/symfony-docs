GreaterThan
===========

.. versionadded:: 2.3
    This constraint is new in version 2.3.

Validates that a value is greater than another value, defined in the options. To
force that a value is greater than or equal to another value, see
:doc:`/reference/constraints/GreaterThanOrEqual`. To force a value is less
than another value, see :doc:`/reference/constraints/LessThan`.

+----------------+---------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                     |
+----------------+---------------------------------------------------------------------------+
| Options        | - `value`_                                                                |
|                | - `message`_                                                              |
+----------------+---------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThan`          |
+----------------+---------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThanValidator` |
+----------------+---------------------------------------------------------------------------+

Basic Usage
-----------

If you want to ensure that the ``age`` of a ``Person`` class is greater than
``18``, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/SocialBundle/Resources/config/validation.yml
        Acme\SocialBundle\Entity\Person:
            properties:
                age:
                    - GreaterThan:
                        value: 18

    .. code-block:: php-annotations

        // src/Acme/SocialBundle/Entity/Person.php
        namespace Acme\SocialBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\GreaterThan(
             *     value = 18
             * )
             */
            protected $age;
        }

    .. code-block:: xml

        <!-- src/Acme/SocialBundle/Resources/config/validation.xml -->
        <class name="Acme\SocialBundle\Entity\Person">
            <property name="age">
                <constraint name="GreaterThan">
                    <option name="value">18</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/Acme/SocialBundle/Entity/Person.php
        namespace Acme\SocialBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('age', new Assert\GreaterThan(array(
                    'value' => 18,
                )));
            }
        }

Options
-------

.. include:: /reference/constraints/_comparison-value-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be greater than {{ compared_value }}``

This is the message that will be shown if the value is not greather than the
comparison value.
