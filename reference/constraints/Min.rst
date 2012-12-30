Min
===

Validates that a given number is *greater* than some minimum number.

+----------------+--------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`              |
+----------------+--------------------------------------------------------------------+
| Options        | - `limit`_                                                         |
|                | - `message`_                                                       |
|                | - `invalidMessage`_                                                |
+----------------+--------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Min`           |
+----------------+--------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\MinValidator`  |
+----------------+--------------------------------------------------------------------+

Basic Usage
-----------

To verify that the "age" field of a class is "18" or greater, you might add
the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/EventBundle/Resources/config/validation.yml
        Acme\EventBundle\Entity\Participant:
            properties:
                age:
                    - Min: { limit: 18, message: You must be 18 or older to enter. }

    .. code-block:: php-annotations

        // src/Acme/EventBundle/Entity/Participant.php
        namespace Acme\EventBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\Min(limit = "18", message = "You must be 18 or older to enter")
             */
             protected $age;
        }

    .. code-block:: xml

        <!-- src/Acme/EventBundle/Resources/config/validation.yml -->
        <class name="Acme\EventBundle\Entity\Participant">
            <property name="age">
                <constraint name="Min">
                    <option name="limit">18</option>
                    <option name="message">You must be 18 or older to enter</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/Acme/EventBundle/Entity/Participant.php
        namespace Acme\EventBundle\Entity\Participant;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('age', new Assert\Min(array(
                    'limit'   => '18',
                    'message' => 'You must be 18 or older to enter',
                ));
            }
        }

Options
-------

limit
~~~~~

**type**: ``integer`` [:ref:`default option<validation-default-option>`]

This required option is the "min" value. Validation will fail if the given
value is **less** than this min value.

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be {{ limit }} or more``

The message that will be shown if the underlying value is less than the `limit`_
option.

invalidMessage
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be a valid number``

The message that will be shown if the underlying value is not a number (per
the :phpfunction:`is_numeric` PHP function).
