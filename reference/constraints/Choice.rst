Choice
======

The ``Choice`` constraint validates that a given value is one or more of
a list of given choices.

+----------------+-----------------------------------------------------------------------+
| Validates      | a scalar value or an array of scalar values (if ``multiple`` is true) |
+----------------+-----------------------------------------------------------------------+
| Options        | - ``choices``                                                         |
|                | - ``callback``                                                        |
|                | - ``multiple``                                                        |
|                | - ``min``                                                             |
|                | - ``max``                                                             |
|                | - ``message``                                                         |
|                | - ``minMessage``                                                      |
|                | - ``maxMessage``                                                      |
+----------------+-----------------------------------------------------------------------+
| Default Option | ``choices``                                                           |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Choice`           |
+----------------+-----------------------------------------------------------------------+

Available Options
-----------------

*   ``choices`` (**default**) [type: array]
    A required option (unless ``callback`` is specified) - this is the array
    of options that should be considered in the valid set. The input value
    will be matched against this array.

*   ``callback``: [type: string|array]
    This is a static callback method that can be used instead of the ``choices``
    option to return the choices array.
    
    If you pass a string method name (e.g. ``getGenders``), that static method
    will be called on the validated class.
    
    If you pass an array (e.g. ``array('Util', 'getGenders')``), it follows
    the normal callable syntax where the first argument is the class name
    and the second argument is the method name.

*   ``multiple``: [type: Boolean, default: false]
    If this option is true, the input value is expected to be an array instead
    of a single, scalar value. The constraint will check that each value of
    the input array can be found in the array of valid choices. If even one
    of the input values cannot be found, the validation will fail.

*   ``min``: [type: integer]
    If the ``multiple`` option is true, then you can use the ``min`` option
    to force at least XX number of values to be selected. For example, if
    ``min`` is 3, but the input array only contains 2 valid items, the
    validation will fail.

*   ``max``: [type: integer]
    If the ``multiple`` option is true, then you can use the ``max`` option
    to force no more than XX number of values to be selected. For example, if
    ``max`` is 3, but the input array contains 4 valid items, the validation
    will fail.

*   ``message``: [type: string, default: `This value should be one of the given choices`]
    This is the validation error message that's displayed when the input
    value is invalid.

*   ``minMessage``: [type: string, default: `You should select at least {{ limit }} choices`]
    This is the validation error message that's displayed when the user chooses
    too few options per the ``min`` option.

*   ``maxMessage``: [type: string, default: `You should select at most {{ limit }} choices`]
    This is the validation error message that's displayed when the user chooses
    too many options per the ``max`` option.

Basic Usage
-----------

If the choices are simple, they can be passed to the constraint definition
as an array.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/validation.yml
        Acme\HelloBundle\Author:
            properties:
                gender:
                    - Choice:
                        choices:  [male, female]
                        message:  Choose a valid gender.

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Author">
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

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice(choices = {"male", "female"}, message = "Choose a valid gender.")
             */
            protected $gender;
        }

    .. code-block:: php

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\Choice;
        
        class Author
        {
            protected $gender;
            
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('gender', new Choice(
                    'choices' => array('male', 'female'),
                    'message' => 'Choose a valid gender',
                ));
            }
        }

Supplying the Choices with a Callback Function
----------------------------------------------

You can also use a callback function to specify your options. This is useful
if you want to keep your choices in some central location so that, for example,
you can easily access those choices for validation or for building a select
form element.

.. code-block:: php

    // src/Acme/HelloBundle/Author.php
    class Author
    {
        public static function getGenders()
        {
            return array('male', 'female');
        }
    }

You can pass the name of this method to the ``callback`` option of the ``Choice``
constraint.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/validation.yml
        Acme\HelloBundle\Author:
            properties:
                gender:
                    - Choice: { callback: getGenders }

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Author">
            <property name="gender">
                <constraint name="Choice">
                    <option name="callback">getGenders</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice(callback = "getGenders")
             */
            protected $gender;
        }

If the static callback is stored in a different class, for example ``Util``,
you can pass the class name and the method as an array.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/validation.yml
        Acme\HelloBundle\Author:
            properties:
                gender:
                    - Choice: { callback: [Util, getGenders] }

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Author">
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

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice(callback = {"Util", "getGenders"})
             */
            protected $gender;
        }
