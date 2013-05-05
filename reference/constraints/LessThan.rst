LessThan
========

.. versionadded:: 2.3
    The ``LessThan`` validator was added in Symfony 2.3

Validates that a value is less than some other defined value. It is equivalent 
to a `` < `` comparison in PHP.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                  |
+----------------+------------------------------------------------------------------------+
| Options        | - `value`_                                                             |
|                | - `message`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\LessThan`          |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\LessThanValidator` |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

If you wanted to ensure that the ``age`` property of a ``Student`` class
is less than 18, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/AppBundle/Resources/config/validation.yml
        Acme\AppBundle\Entity\Student:
            properties:
                age:
                    - LessThan:
                        value: 18
                        message: Students for this course must be under 18 years old

    .. code-block:: php-annotations

        // src/Acme/AppBundle/Entity/Student.php
        namespace Acme\AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Student
        {
            /**
             * @Assert\LessThan(
             *      age = 18,
             *      message = "Students for this course must be under 18 years old"
             * )
             */
            protected $age;
        }

    .. code-block:: xml

        <!-- src/Acme/AppBundle/Resources/config/validation.xml -->
        <class name="Acme\AppBundle\Entity\Student">
            <property name="age">
                <constraint name="LessThan">
                    <option name="value">18</option>
                    <option name="message">
                        Students for this course must be under 18 years old
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
                $metadata->addPropertyConstraint('age', new Assert\LessThan(array(
                    'value' => 18,
                    'message' => 'Students for this course must be under 18 years old'
                )));
            }
        }

Options
-------

value
~~~~~

**type**: ``mixed`` [:ref:`default option<validation-default-option>`]

This required option is the comparison value. Validation will fail if the 
given value is greater than or equal to this comparison value.

message
~~~~~~~

**type**: ``string`` **default**: 
``This value should be less than {{ compared_value }}.``

This is the message that will be shown if the value is greater than or equal 
to the `value`_ option.
