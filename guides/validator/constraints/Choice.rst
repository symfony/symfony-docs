Choice
======

Validates that a value is one or more of a list of choices.

.. code-block:: yaml

    properties:
        gender:
            - Choice: [male, female]

Options
-------

* ``choices`` (**default**, required): The available choices
* ``callback``: Can be used instead of ``choices``. A static callback method
  returning the choices. If you pass a string, it is expected to be
  the name of a static method in the validated class.
* ``multiple``: Whether multiple choices are allowed. Default: ``false``
* ``min``: The minimum amount of selected choices
* ``max``: The maximum amount of selected choices
* ``message``: The error message if validation fails
* ``minMessage``: The error message if ``min`` validation fails
* ``maxMessage``: The error message if ``max`` validation fails

Example 1: Choices as static array
----------------------------------

If the choices are few and easy to determine, they can be passed to the
constraint definition as array.

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            properties:
                gender:
                    - Choice: [male, female]

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <property name="gender">
                <constraint name="Choice">
                    <value>male</value>
                    <value>female</value>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:Choice({"male", "female"})
             */
            protected $gender;
        }

    .. code-block:: php

        // Application/HelloBundle/Author.php
        use Symfony\Components\Validator\Constraints\Choice;
        
        class Author
        {
            protected $gender;
            
            public static function loadMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('gender', new Choice(array('male', 'female')));
            }
        }

Example 2: Choices from a callback
----------------------------------

When you also need the choices in other contexts (such as a drop-down box in
a form), it is more flexible to bind them to your domain model using a static
callback method.

.. code-block:: php

    // Application/HelloBundle/Author.php
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

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            properties:
                gender:
                    - Choice: { callback: getGenders }

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <property name="gender">
                <constraint name="Choice">
                    <option name="callback">getGenders</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:Choice(callback = "getGenders")
             */
            protected $gender;
        }

If the static callback is stored in a different class, for example ``Util``,
you can pass the class name and the method as array.

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            properties:
                gender:
                    - Choice: { callback: [Util, getGenders] }

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
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

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:Choice(callback = {"Util", "getGenders"})
             */
            protected $gender;
        }
