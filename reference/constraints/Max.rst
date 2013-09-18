Max
===

.. caution::

    The Max constraint is deprecated since version 2.1 and will be removed
    in Symfony 2.3. Use :doc:`/reference/constraints/Range` with the ``max``
    option instead.

Validates that a given number is *less* than some maximum number.

+----------------+--------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`             |
+----------------+--------------------------------------------------------------------+
| Options        | - `limit`_                                                         |
|                | - `message`_                                                       |
|                | - `invalidMessage`_                                                |
+----------------+--------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Max`           |
+----------------+--------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\MaxValidator`  |
+----------------+--------------------------------------------------------------------+

Basic Usage
-----------

To verify that the "age" field of a class is not greater than "50", you might
add the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/EventBundle/Resources/config/validation.yml
        Acme\EventBundle\Entity\Participant:
            properties:
                age:
                    - Max: { limit: 50, message: You must be 50 or under to enter. }

    .. code-block:: php-annotations

        // src/Acme/EventBundle/Entity/Participant.php
        namespace Acme\EventBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\Max(limit = 50, message = "You must be 50 or under to enter.")
             */
             protected $age;
        }

    .. code-block:: xml

        <!-- src/Acme/EventBundle/Resources/config/validation.yml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\EventBundle\Entity\Participant">
                <property name="age">
                    <constraint name="Max">
                        <option name="limit">50</option>
                        <option name="message">You must be 50 or under to enter.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/EventBundle/Entity/Participant.php
        namespace Acme\EventBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('age', new Assert\Max(array(
                    'limit'   => 50,
                    'message' => 'You must be 50 or under to enter.',
                )));
            }
        }

Options
-------

limit
~~~~~

**type**: ``integer`` [:ref:`default option <validation-default-option>`]

This required option is the "max" value. Validation will fail if the given
value is **greater** than this max value.

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be {{ limit }} or less``

The message that will be shown if the underlying value is greater than the
`limit`_ option.

invalidMessage
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be a valid number``

The message that will be shown if the underlying value is not a number (per
the :phpfunction:`is_numeric` PHP function).
