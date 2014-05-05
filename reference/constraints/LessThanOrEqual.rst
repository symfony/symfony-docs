LessThanOrEqual
===============

.. versionadded:: 2.3
    The ``LessThanOrEqual`` constraint was introduced in Symfony 2.3.

Validates that a value is less than or equal to another value, defined in the
options. To force that a value is less than another value, see
:doc:`/reference/constraints/LessThan`.

+----------------+-------------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                         |
+----------------+-------------------------------------------------------------------------------+
| Options        | - `value`_                                                                    |
|                | - `message`_                                                                  |
+----------------+-------------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\LessThanOrEqual`          |
+----------------+-------------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\LessThanOrEqualValidator` |
+----------------+-------------------------------------------------------------------------------+

Basic Usage
-----------

If you want to ensure that the ``age`` of a ``Person`` class is less than or
equal to ``80``, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/SocialBundle/Resources/config/validation.yml
        Acme\SocialBundle\Entity\Person:
            properties:
                age:
                    - LessThanOrEqual:
                        value: 80

    .. code-block:: php-annotations

        // src/Acme/SocialBundle/Entity/Person.php
        namespace Acme\SocialBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\LessThanOrEqual(
             *     value = 80
             * )
             */
            protected $age;
        }

    .. code-block:: xml

        <!-- src/Acme/SocialBundle/Resources/config/validation.xml -->
        <class name="Acme\SocialBundle\Entity\Person">
            <property name="age">
                <constraint name="LessThanOrEqual">
                    <option name="value">80</option>
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
                $metadata->addPropertyConstraint('age', new Assert\LessThanOrEqual(array(
                    'value' => 80,
                )));
            }
        }

Options
-------

.. include:: /reference/constraints/_comparison-value-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be less than or equal to {{ compared_value }}.``

This is the message that will be shown if the value is not less than or equal
to the comparison value.
