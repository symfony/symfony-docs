SizeLength
==========

Validates that a given string length is *between* some minimum and maximum value according to the provided charset.

+----------------+--------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                    |
+----------------+--------------------------------------------------------------------------+
| Options        | - `min`_                                                                 |
|                | - `max`_                                                                 |
|                | - `charset`_                                                             |
|                | - `minMessage`_                                                          |
|                | - `maxMessage`_                                                          |
|                | - `exactMessage`_                                                        |
+----------------+--------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\SizeLength`          |
+----------------+--------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\SizeLengthValidator` |
+----------------+--------------------------------------------------------------------------+

Basic Usage
-----------

To verify that the "firstname" field length of a class is between "2" and "50", you might add
the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/EventBundle/Resources/config/validation.yml
        Acme\EventBundle\Entity\Height:
            properties:
                firstname:
                    - SizeLength: { min: 2, max: 50, minMessage: Firstname must be at least 2 characters length, maxMessage: Firstname cannot be longer than than 50 characters length }

    .. code-block:: php-annotations

        // src/Acme/EventBundle/Entity/Participant.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\SizeLength(min = "2", max = "50", minMessage = "Firstname must be at least 2 characters length", maxMessage="Firstname cannot be longer than than 50 characters length")
             */
             protected $firstname;
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
value's length is **less** than this max value.

charset
~~~~~~~

**type**: ``string``  **default**: ``UTF-8``

Charset to be used when computing value's length per the `grapheme_strlen`_ PHP function, fallback to the `mb_strlen`_ PHP function, fallback to the `strlen`_ PHP function.

.. _`grapheme_strlen`: http://www.php.net/manual/en/function.grapheme_strlen.php
.. _`mb_strlen`: http://www.php.net/manual/en/function.mb_strlen.php
.. _`strlen`: http://www.php.net/manual/en/function.strlen.php

minMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too short. It should have {{ limit }} characters or more``

The message that will be shown if the underlying value's length is less than the `limit`_
option.

maxMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too long. It should have {{ limit }} characters or less``

The message that will be shown if the underlying value's length is more than the `limit`_
option.

exactMessage
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should have exactly {{ limit }} characters``

The message that will be shown if min and max values equal and the underlying value's length is not exactly this value
