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
        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\Min(limit = "18", message = "You must be 18 or older to enter")
             */
             protected $age;
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
the `is_numeric`_ PHP function).

.. _`is_numeric`: http://www.php.net/manual/en/function.is-numeric.php