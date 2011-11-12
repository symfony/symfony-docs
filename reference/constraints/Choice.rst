Choice
======

This constraint is used to ensure that the given value is one of a given
set of *valid* choices. It can also be used to validate that each item in
an array of items is one of those valid choices.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `choices`_                                                          |
|                | - `callback`_                                                         |
|                | - `multiple`_                                                         |
|                | - `min`_                                                              |
|                | - `max`_                                                              |
|                | - `message`_                                                          |
|                | - `multipleMessage`_                                                  |
|                | - `minMessage`_                                                       |
|                | - `maxMessage`_                                                       |
|                | - `strict`_                                                           |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Choice`           |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\ChoiceValidator`  |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

The basic idea of this constraint is that you supply it with an array of
valid values (this can be done in several ways) and it validates that the
value of the given property exists in that array.

If your valid choice list is simple, you can pass them in directly via the
`choices`_ option:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                gender:
                    - Choice:
                        choices:  [male, female]
                        message:  Choose a valid gender.

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\EntityAuthor">
            <property name="gender">
                <constraint name="Choice">
                    <option name="choices">
                        <value>male</value>
                        <value>female</value>
                    </option>
                    <option name="message">Choose a valid gender.</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice(choices = {"male", "female"}, message = "Choose a valid gender.")
             */
            protected $gender;
        }

    .. code-block:: php

        // src/Acme/BlogBundle/EntityAuthor.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\Choice;
        
        class Author
        {
            protected $gender;
            
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('gender', new Choice(array(
                    'choices' => array('male', 'female'),
                    'message' => 'Choose a valid gender',
                )));
            }
        }

Supplying the Choices with a Callback Function
----------------------------------------------

You can also use a callback function to specify your options. This is useful
if you want to keep your choices in some central location so that, for example,
you can easily access those choices for validation or for building a select
form element.

.. code-block:: php

    // src/Acme/BlogBundle/Entity/Author.php
    class Author
    {
        public static function getGenders()
        {
            return array('male', 'female');
        }
    }

You can pass the name of this method to the `callback_` option of the ``Choice``
constraint.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                gender:
                    - Choice: { callback: getGenders }

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice(callback = "getGenders")
             */
            protected $gender;
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Author">
            <property name="gender">
                <constraint name="Choice">
                    <option name="callback">getGenders</option>
                </constraint>
            </property>
        </class>

If the static callback is stored in a different class, for example ``Util``,
you can pass the class name and the method as an array.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                gender:
                    - Choice: { callback: [Util, getGenders] }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Author">
            <property name="gender">
                <constraint name="Choice">
                    <option name="callback">
                        <value>Util</value>
                        <value>getGenders</value>
                    </option>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice(callback = {"Util", "getGenders"})
             */
            protected $gender;
        }

Available Options
-----------------

choices
~~~~~~~

**type**: ``array`` [:ref:`default option<validation-default-option>`]

A required option (unless `callback`_ is specified) - this is the array
of options that should be considered in the valid set. The input value
will be matched against this array.

callback
~~~~~~~~

**type**: ``string|array|Closure``

This is a callback method that can be used instead of the `choices`_ option
to return the choices array. See `Supplying the Choices with a Callback Function`_
for details on its usage.

multiple
~~~~~~~~

**type**: ``Boolean`` **default**: ``false``

If this option is true, the input value is expected to be an array instead
of a single, scalar value. The constraint will check that each value of
the input array can be found in the array of valid choices. If even one
of the input values cannot be found, the validation will fail.

min
~~~

**type**: ``integer``

If the ``multiple`` option is true, then you can use the ``min`` option
to force at least XX number of values to be selected. For example, if
``min`` is 3, but the input array only contains 2 valid items, the validation
will fail.

max
~~~

**type**: ``integer``

If the ``multiple`` option is true, then you can use the ``max`` option
to force no more than XX number of values to be selected. For example, if
``max`` is 3, but the input array contains 4 valid items, the validation
will fail.

message
~~~~~~~

**type**: ``string`` **default**: ``The value you selected is not a valid choice``

This is the message that you will receive if the ``multiple`` option is set
to ``false``, and the underlying value is not in the valid array of choices.

multipleMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``One or more of the given values is invalid``

This is the message that you will receive if the ``multiple`` option is set
to ``true``, and one of the values on the underlying array being checked
is not in the array of valid choices.

minMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``You must select at least {{ limit }} choices``

This is the validation error message that's displayed when the user chooses
too few choices per the `min`_ option.

maxMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``You must select at most {{ limit }} choices``

This is the validation error message that's displayed when the user chooses
too many options per the `max`_ option.

strict
~~~~~~

**type**: ``Boolean`` **default**: ``false``

If true, the validator will also check the type of the input value. Specifically,
this value is passed to as the third argument to the PHP `in_array`_ method
when checking to see if a value is in the valid choices array.

.. _`in_array`: http://php.net/manual/en/function.in-array.php
