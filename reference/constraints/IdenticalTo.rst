IdenticalTo
===========

.. versionadded:: 2.3
    The ``IdenticalTo`` validator was added in Symfony 2.3

Validates that a value is equal to some other defined value in value and 
type. It is equivalent to a `` === `` comparison in PHP.

+----------------+---------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                     |
+----------------+---------------------------------------------------------------------------+
| Options        | - `value`_                                                                |
|                | - `message`_                                                              |
+----------------+---------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\IdenticalTo`          |
+----------------+---------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\IdenticalToValidator` |
+----------------+---------------------------------------------------------------------------+

Basic Usage
-----------

If you wanted to ensure that the ``age`` property of a ``Student`` class
is the integer 18, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/AppBundle/Resources/config/validation.yml
        Acme\AppBundle\Entity\Student:
            properties:
                age:
                    - IdenticalTo:
                        value: 18
                        message: Students for this course must be exactly 18 years old

    .. code-block:: php-annotations

        // src/Acme/AppBundle/Entity/Student.php
        namespace Acme\AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Student
        {
            /**
             * @Assert\IdenticalTo(
             *      age = 18,
             *      message = "Students for this course must be exactly 18 years old"
             * )
             */
            protected $age;
        }

    .. code-block:: xml

        <!-- src/Acme/AppBundle/Resources/config/validation.xml -->
        <class name="Acme\AppBundle\Entity\Student">
            <property name="age">
                <constraint name="IdenticalTo">
                    <option name="value">18</option>
                    <option name="message">
                        Students for this course must be exactly 18 years old
                    </option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/Acme/AppBundle/Entity/Student.php
        namespace Acme\AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('age', new Assert\IdenticalTo(array(
                    'value' => 18,
                    'message' => 'Students for this course must be exactly 18 years old'
                )));
            }
        }

Options
-------

value
~~~~~

**type**: ``mixed`` [:ref:`default option<validation-default-option>`]

This required option is the comparison value. Validation will fail if the 
given value doesn't equal this comparison value or the type of the given 
value isn't the same as the type of the comparison value.

message
~~~~~~~

**type**: ``string`` **default**: 
``This value should be identical to {{ compared_value_type }} {{ compared_value }}.``

This is the message that will be shown if the value doesn't equal the `value`_ 
option or the type of the value is different to the type of the `value`_ 
option.
