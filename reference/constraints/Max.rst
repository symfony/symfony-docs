Max
===

Validates that a given number is *less* than some maximum number.

+----------------+--------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`              |
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
        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\Max(limit = 50, message = "You must be 50 or under to enter.")
             */
             protected $age;
        }

Options
-------

limit
~~~~~

**type**: ``integer`` [:ref:`default option<validation-default-option>`]

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
the `is_numeric`_ PHP function).

.. _`is_numeric`: http://www.php.net/manual/en/function.is-numeric.php