Length
======

Validates that a given string length is *between* some minimum and maximum
value.

+----------------+----------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`               |
+----------------+----------------------------------------------------------------------+
| Options        | - `min`_                                                             |
|                | - `max`_                                                             |
|                | - `charset`_                                                         |
|                | - `minMessage`_                                                      |
|                | - `maxMessage`_                                                      |
|                | - `exactMessage`_                                                    |
|                | - `payload`_                                                         |
+----------------+----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Length`          |
+----------------+----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\LengthValidator` |
+----------------+----------------------------------------------------------------------+

Basic Usage
-----------

To verify that the ``firstName`` field length of a class is between "2"
and "50", you might add the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Participant.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\Length(
             *      min = 2,
             *      max = 50,
             *      minMessage = "Your first name must be at least {{ limit }} characters long",
             *      maxMessage = "Your first name cannot be longer than {{ limit }} characters"
             * )
             */
             protected $firstName;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Participant:
            properties:
                firstName:
                    - Length:
                        min: 2
                        max: 50
                        minMessage: 'Your first name must be at least {{ limit }} characters long'
                        maxMessage: 'Your first name cannot be longer than {{ limit }} characters'

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Participant">
                <property name="firstName">
                    <constraint name="Length">
                        <option name="min">2</option>
                        <option name="max">50</option>
                        <option name="minMessage">
                            Your first name must be at least {{ limit }} characters long
                        </option>
                        <option name="maxMessage">
                            Your first name cannot be longer than {{ limit }} characters
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Participant.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new Assert\Length(array(
                    'min'        => 2,
                    'max'        => 50,
                    'minMessage' => 'Your first name must be at least {{ limit }} characters long',
                    'maxMessage' => 'Your first name cannot be longer than {{ limit }} characters',
                )));
            }
        }

Options
-------

min
~~~

**type**: ``integer``

This required option is the "min" length value. Validation will fail if
the given value's length is **less** than this min value.

It is important to notice that NULL values and empty strings are considered
valid no matter if the constraint required a minimum length. Validators
are triggered only if the value is not blank.

max
~~~

**type**: ``integer``

This required option is the "max" length value. Validation will fail if
the given value's length is **greater** than this max value.

charset
~~~~~~~

**type**: ``string``  **default**: ``UTF-8``

The charset to be used when computing value's length. The
:phpfunction:`grapheme_strlen` PHP function is used if available. If not,
the :phpfunction:`mb_strlen` PHP function is used if available. If neither
are available, the :phpfunction:`strlen` PHP function is used.

minMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too short. It should have {{ limit }} characters or more.``

The message that will be shown if the underlying value's length is less
than the `min`_ option.

maxMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too long. It should have {{ limit }} characters or less.``

The message that will be shown if the underlying value's length is more
than the `max`_ option.

exactMessage
~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should have exactly {{ limit }} characters.``

The message that will be shown if min and max values are equal and the underlying
value's length is not exactly this value.

.. include:: /reference/constraints/_payload-option.rst.inc
