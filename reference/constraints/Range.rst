Range
=====

Validates that a given number is *between* some minimum and maximum number.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `min`_                                                            |
|                | - `max`_                                                            |
|                | - `minMessage`_                                                     |
|                | - `maxMessage`_                                                     |
|                | - `invalidMessage`_                                                 |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Range`          |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\RangeValidator` |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

To verify that the "height" field of a class is between "120" and "180",
you might add the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Participant.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\Range(
             *      min = 120,
             *      max = 180,
             *      minMessage = "You must be at least {{ limit }}cm tall to enter",
             *      maxMessage = "You cannot be taller than {{ limit }}cm to enter"
             * )
             */
             protected $height;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Participant:
            properties:
                height:
                    - Range:
                        min: 120
                        max: 180
                        minMessage: You must be at least {{ limit }}cm tall to enter
                        maxMessage: You cannot be taller than {{ limit }}cm to enter

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Participant">
                <property name="height">
                    <constraint name="Range">
                        <option name="min">120</option>
                        <option name="max">180</option>
                        <option name="minMessage">You must be at least {{ limit }}cm tall to enter</option>
                        <option name="maxMessage">You cannot be taller than {{ limit }}cm to enter</option>
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
                $metadata->addPropertyConstraint('height', new Assert\Range(array(
                    'min'        => 120,
                    'max'        => 180,
                    'minMessage' => 'You must be at least {{ limit }}cm tall to enter',
                    'maxMessage' => 'You cannot be taller than {{ limit }}cm to enter',
                )));
            }
        }

Options
-------

min
~~~

**type**: ``integer``

This required option is the "min" value. Validation will fail if the given
value is **less** than this min value.

max
~~~

**type**: ``integer``

This required option is the "max" value. Validation will fail if the given
value is **greater** than this max value.

minMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be {{ limit }} or more.``

The message that will be shown if the underlying value is less than the
`min`_ option.

maxMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be {{ limit }} or less.``

The message that will be shown if the underlying value is more than the
`max`_ option.

invalidMessage
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be a valid number.``

The message that will be shown if the underlying value is not a number (per
the `is_numeric`_ PHP function).

.. _`is_numeric`: http://www.php.net/manual/en/function.is-numeric.php
