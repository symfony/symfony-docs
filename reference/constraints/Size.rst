Size
====

Validates that a given string length or collection elements count is *between* some minimum and maximum value.

+----------------+--------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`              |
+----------------+--------------------------------------------------------------------+
| Options        | - `min`_                                                           |
|                | - `max`_                                                           |
|                | - `type`_                                                          |
|                | - `charset`_                                                       |
|                | - `minMessage`_                                                    |
|                | - `maxMessage`_                                                    |
|                | - `exactMessage`_                                                  |
+----------------+--------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Size`          |
+----------------+--------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\SizeValidator` |
+----------------+--------------------------------------------------------------------+

Basic Usage
-----------

To verify that the ``firstName`` field length of a class is between "2" and
"50", you might add the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/EventBundle/Resources/config/validation.yml
        Acme\EventBundle\Entity\Height:
            properties:
                firstName:
                    - Size:
                        min: 2
                        max: 50
                        minMessage: Your first name must be at least 2 characters length
                        maxMessage: Your first name cannot be longer than than 50 characters length

    .. code-block:: php-annotations

        // src/Acme/EventBundle/Entity/Participant.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\Size(
             *      min = "2",
             *      max = "50",
             *      minMessage = "Your first name must be at least 2 characters length",
             *      maxMessage = "Your first name cannot be longer than than 50 characters length"
             * )
             */
             protected $firstName;
        }

Options
-------

min
~~~

**type**: ``integer`` [:ref:`default option<validation-default-option>`]

This required option is the "min" length value. Validation will fail if the given
value's length is **less** than this min value.

max
~~~

**type**: ``integer`` [:ref:`default option<validation-default-option>`]

This required option is the "max" length value. Validation will fail if the given
value's length is **greater** than this max value.

type
~~~~

**type**: ``string``

The type of value to validate. It can be either ``string`` or ``collection``. If
not specified, the validator will try to guess it.

charset
~~~~~~~

**type**: ``string``  **default**: ``UTF-8``

The charset to be used when computing value's length. The `grapheme_strlen`_ PHP
function is used if available. If not, the the `mb_strlen`_ PHP function
is used if available. If neither are available, the `strlen`_ PHP function
is used.

.. _`grapheme_strlen`: http://www.php.net/manual/en/function.grapheme_strlen.php
.. _`mb_strlen`: http://www.php.net/manual/en/function.mb_strlen.php
.. _`strlen`: http://www.php.net/manual/en/function.strlen.php

minMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too short. It should have {{ limit }} characters or more.`` when validating a string, or ``This collection should contain {{ limit }} elements or more.`` when validating a collection.

The message that will be shown if the underlying value's length or collection elements
count is less than the `min`_ option.

maxMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too long. It should have {{ limit }} characters or less.`` when validating a string, or ``This collection should contain {{ limit }} elements or less.`` when validating a collection.

The message that will be shown if the underlying value's length or collection elements
count is more than the `max`_ option.

exactMessage
~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should have exactly {{ limit }} characters.`` when validating a string, or ``This collection should contain exactly {{ limit }} elements.`` when validating a collection.

The message that will be shown if min and max values are equal and the underlying
value's length or collection elements count is not exactly this value.
