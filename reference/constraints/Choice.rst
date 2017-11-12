Choice
======

This constraint is used to ensure that the given value is one of a given
set of *valid* choices. It can also be used to validate that each item in
an array of items is one of those valid choices.

+----------------+----------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`               |
+----------------+----------------------------------------------------------------------+
| Options        | - `choices`_                                                         |
|                | - `callback`_                                                        |
|                | - `multiple`_                                                        |
|                | - `min`_                                                             |
|                | - `max`_                                                             |
|                | - `message`_                                                         |
|                | - `multipleMessage`_                                                 |
|                | - `minMessage`_                                                      |
|                | - `maxMessage`_                                                      |
|                | - `strict`_                                                          |
|                | - `payload`_                                                         |
+----------------+----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Choice`          |
+----------------+----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\ChoiceValidator` |
+----------------+----------------------------------------------------------------------+

Basic Usage
-----------

The basic idea of this constraint is that you supply it with an array of
valid values (this can be done in several ways) and it validates that the
value of the given property exists in that array.

If your valid choice list is simple, you can pass them in directly via the
`choices`_ option:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice({"New York", "Berlin", "Tokyo"})
             */
            protected $city;

            /**
             * @Assert\Choice(choices = {"fiction", "non-fiction"}, message = "Choose a valid genre.")
             */
            protected $genre;
        }

    .. code-block:: yaml

        # src/Resources/config/validation.yml
        App\Entity\Author:
            properties:
                city:
                    - Choice: [New York, Berlin, Tokyo]
                genre:
                    - Choice:
                        choices:  [fiction, non-fiction]
                        message:  Choose a valid genre.

    .. code-block:: xml

        <!-- src/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="city">
                    <constraint name="Choice">
                        <value>New York</value>
                        <value>Berlin</value>
                        <value>Tokyo</value>
                    </constraint>
                </property>
                <property name="genre">
                    <constraint name="Choice">
                        <option name="choices">
                            <value>fiction</value>
                            <value>non-fiction</value>
                        </option>
                        <option name="message">Choose a valid genre.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/EntityAuthor.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $genre;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint(
                    'city',
                     new Assert\Choice(array('New York', 'Berlin', 'Tokyo'))
                 );

                $metadata->addPropertyConstraint('genre', new Assert\Choice(array(
                    'choices' => array('fiction', 'non-fiction'),
                    'message' => 'Choose a valid genre.',
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

    // src/Entity/Author.php
    namespace App\Entity;

    class Author
    {
        public static function getGenres()
        {
            return array('fiction', 'non-fiction');
        }
    }

You can pass the name of this method to the `callback`_ option of the ``Choice``
constraint.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice(callback = "getGenres")
             */
            protected $genre;
        }

    .. code-block:: yaml

        # src/Resources/config/validation.yml
        App\Entity\Author:
            properties:
                genre:
                    - Choice: { callback: getGenres }

    .. code-block:: xml

        <!-- src/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="genre">
                    <constraint name="Choice">
                        <option name="callback">getGenres</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/EntityAuthor.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $genre;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('genre', new Assert\Choice(array(
                    'callback' => 'getGenres',
                )));
            }
        }

If the callback is stored in a different class and is static, for example ``Util``,
you can pass the class name and the method as an array.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice(callback = {"Util", "getGenres"})
             */
            protected $genre;
        }

    .. code-block:: yaml

        # src/Resources/config/validation.yml
        App\Entity\Author:
            properties:
                genre:
                    - Choice: { callback: [Util, getGenres] }

    .. code-block:: xml

        <!-- src/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="genre">
                    <constraint name="Choice">
                        <option name="callback">
                            <value>Util</value>
                            <value>getGenres</value>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $genre;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('genre', new Assert\Choice(array(
                    'callback' => array('Util', 'getGenres'),
                )));
            }
        }

Available Options
-----------------

choices
~~~~~~~

**type**: ``array`` [:ref:`default option <validation-default-option>`]

A required option (unless `callback`_ is specified) - this is the array
of options that should be considered in the valid set. The input value
will be matched against this array.

callback
~~~~~~~~

**type**: ``string|array|Closure``

This is a callback method that can be used instead of the `choices`_ option
to return the choices array. See
`Supplying the Choices with a Callback Function`_ for details on its usage.

multiple
~~~~~~~~

**type**: ``boolean`` **default**: ``false``

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

**type**: ``string`` **default**: ``The value you selected is not a valid choice.``

This is the message that you will receive if the ``multiple`` option is
set to ``false`` and the underlying value is not in the valid array of
choices.

multipleMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``One or more of the given values is invalid.``

This is the message that you will receive if the ``multiple`` option is
set to ``true`` and one of the values on the underlying array being checked
is not in the array of valid choices.

minMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``You must select at least {{ limit }} choices.``

This is the validation error message that's displayed when the user chooses
too few choices per the `min`_ option.

maxMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``You must select at most {{ limit }} choices.``

This is the validation error message that's displayed when the user chooses
too many options per the `max`_ option.

strict
~~~~~~

**type**: ``boolean`` **default**: ``false``

The validator will also check the type of the input value. Specifically,
this value is passed to as the third argument to the PHP :phpfunction:`in_array`
method when checking to see if a value is in the valid choices array.

.. include:: /reference/constraints/_payload-option.rst.inc
