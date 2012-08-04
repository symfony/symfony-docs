Count
=====

Validates that a given collection's (i.e. an array or an object that implements Countable)
element count is *between* some minimum and maximum value.

.. versionadded:: 2.1
    The Count constraint was added in Symfony 2.1.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`               |
+----------------+---------------------------------------------------------------------+
| Options        | - `min`_                                                            |
|                | - `max`_                                                            |
|                | - `minMessage`_                                                     |
|                | - `maxMessage`_                                                     |
|                | - `exactMessage`_                                                   |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Count`          |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\CountValidator` |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

To verify that the ``emails`` array field contains between 1 and 5 elements
you might add the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/EventBundle/Resources/config/validation.yml
        Acme\EventBundle\Entity\Participant:
            properties:
                emails:
                    - Count:
                        min: 1
                        max: 5
                        minMessage: You must specify at least one email
                        maxMessage: You cannot specify more than 5 emails

    .. code-block:: php-annotations

        // src/Acme/EventBundle/Entity/Participant.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\Count(
             *      min = "1",
             *      max = "5",
             *      minMessage = "You must specify at least one email",
             *      maxMessage = "You cannot specify more than 5 emails"
             * )
             */
             protected $emails = array();
        }

Options
-------

min
~~~

**type**: ``integer`` [:ref:`default option<validation-default-option>`]

This required option is the "min" count value. Validation will fail if the given
collection elements count is **less** than this min value.

max
~~~

**type**: ``integer`` [:ref:`default option<validation-default-option>`]

This required option is the "max" count value. Validation will fail if the given
collection elements count is **greater** than this max value.

minMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This collection should contain {{ limit }} elements or more.``.

The message that will be shown if the underlying collection elements count is less than the `min`_ option.

maxMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This collection should contain {{ limit }} elements or less.``.

The message that will be shown if the underlying collection elements count is more than the `max`_ option.

exactMessage
~~~~~~~~~~~~

**type**: ``string`` **default**: ``This collection should contain exactly {{ limit }} elements.``.

The message that will be shown if min and max values are equal and the underlying collection elements 
count is not exactly this value.
