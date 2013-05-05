EqualTo
=======

.. versionadded:: 2.3
    The ``EqualTo`` validator was added in Symfony 2.3

Validates that a value is equal to some other defined value. It is equivalent 
to a `` == `` comparison in PHP.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `value`_                                                            |
|                | - `message`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\EqualTo`          |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\EqualToValidator` |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

If you wanted to ensure that the ``age`` property of a ``Student`` class
is 18, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/AppBundle/Resources/config/validation.yml
        Acme\AppBundle\Entity\Student:
            properties:
                age:
                    - EqualTo:
                        value: 18
                        message: Students for this course must be exactly 18 years old

    .. code-block:: php-annotations

        // src/Acme/AppBundle/Entity/Student.php
        namespace Acme\AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Student
        {
            /**
             * @Assert\EqualTo(
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
                <constraint name="EqualTo">
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
                $metadata->addPropertyConstraint('age', new Assert\EqualTo(array(
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

This required option is the comparison value. Validation will fail if the given
value doesn't equal this comparison value.

message
~~~~~~~

**type**: ``string`` **default**: 
``This value should be equal to {{ compared_value }}.``

This is the message that will be shown if the value doesn't equal the `value`_ 
option.
