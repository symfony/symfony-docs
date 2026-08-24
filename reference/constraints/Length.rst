Length
======

Validates that a given string length is *between* some minimum and maximum value.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Length`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\LengthValidator`
==========  ===================================================================

Basic Usage
-----------

To verify that the ``firstName`` field length of a class is between ``2``
and ``50``, you might add the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Participant.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            #[Assert\Length(
                min: 2,
                max: 50,
                minMessage: 'Your first name must be at least {{ limit }} characters long',
                maxMessage: 'Your first name cannot be longer than {{ limit }} characters',
            )]
            protected string $firstName;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Participant:
            properties:
                firstName:
                    - Length:
                        min: 2
                        max: 50
                        minMessage: 'Your first name must be at least {{ limit }} characters long'
                        maxMessage: 'Your first name cannot be longer than {{ limit }} characters'

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Participant">
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

        // src/Entity/Participant.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Participant
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('firstName', new Assert\Length(
                    min: 2,
                    max: 50,
                    minMessage: 'Your first name must be at least {{ limit }} characters long',
                    maxMessage: 'Your first name cannot be longer than {{ limit }} characters',
                ));
            }
        }

.. include:: /reference/constraints/_null-values-are-valid.rst.inc

Options
-------

``charset``
~~~~~~~~~~~

**type**: ``string``  **default**: ``UTF-8``

The charset to be used when computing value's length with the
:phpfunction:`mb_check_encoding` and :phpfunction:`mb_strlen`
PHP functions.

``charsetMessage``
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value does not match the expected {{ charset }} charset.``

The message that will be shown if the value is not using the given `charset`_.

You can use the following parameters in this message:

=================  ============================================================
Parameter          Description
=================  ============================================================
``{{ charset }}``  The expected charset
``{{ value }}``    The current (invalid) value
=================  ============================================================

``countUnit``
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Length::COUNT_CODEPOINTS``

The character count unit to use for the length check. By default :phpfunction:`mb_strlen`
is used, which counts Unicode code points.

Can be one of the following constants of the
:class:`Symfony\\Component\\Validator\\Constraints\\Length` class:

* ``COUNT_BYTES``: Uses :phpfunction:`strlen` counting the length of the string in bytes.
* ``COUNT_CODEPOINTS``: Uses :phpfunction:`mb_strlen` counting the length of the string in Unicode
  code points. This was the sole behavior until Symfony 6.2 and is the default since Symfony 6.3.
  Simple (multibyte) Unicode characters count as 1 character, while for example ZWJ sequences of
  composed emojis count as multiple characters.
* ``COUNT_GRAPHEMES``: Uses :phpfunction:`grapheme_strlen` counting the length of the string in
  graphemes, i.e. even emojis and ZWJ sequences of composed emojis count as 1 character.

``exactly``
~~~~~~~~~~~

**type**: ``integer``

This option is the exact length value. Validation will fail if
the given value's length is not **exactly** equal to this value.

.. note::

    This option is the one being set by default when using the Length constraint
    without passing any named argument to it. This means that for example,
    ``#[Assert\Length(20)]`` and ``#[Assert\Length(exactly: 20)]`` are equivalent.

``exactMessage``
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should have exactly {{ limit }} characters.``

The message that will be shown if min and max values are equal and the underlying
value's length is not exactly this value.

You can use the following parameters in this message:

======================  ============================================================
Parameter               Description
======================  ============================================================
``{{ limit }}``         The exact expected length
``{{ value }}``         The current (invalid) value
``{{ value_length }}``  The current value's length
======================  ============================================================

.. include:: /reference/constraints/_groups-option.rst.inc

``max``
~~~~~~~

**type**: ``integer``

This option is the "max" length value. Validation will fail if
the given value's length is **greater** than this max value.

This option is required when the ``min`` option is not defined.

``maxMessage``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too long. It should have {{ limit }} characters or less.``

The message that will be shown if the underlying value's length is more
than the `max`_ option.

You can use the following parameters in this message:

======================  ============================================================
Parameter               Description
======================  ============================================================
``{{ limit }}``         The expected maximum length
``{{ min }}``           The expected minimum length
``{{ max }}``           The expected maximum length
``{{ value }}``         The current (invalid) value
``{{ value_length }}``  The current value's length
======================  ============================================================

``min``
~~~~~~~

**type**: ``integer``

This option is the "min" length value. Validation will fail if
the given value's length is **less** than this min value.

This option is required when the ``max`` option is not defined.

It is important to notice that ``null`` values are considered
valid no matter if the constraint requires a minimum length. Validators
are triggered only if the value is not ``null``.

``minMessage``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too short. It should have {{ limit }} characters or more.``

The message that will be shown if the underlying value's length is less
than the `min`_ option.

You can use the following parameters in this message:

======================  ============================================================
Parameter               Description
======================  ============================================================
``{{ limit }}``         The expected minimum length
``{{ min }}``           The expected minimum length
``{{ max }}``           The expected maximum length
``{{ value }}``         The current (invalid) value
``{{ value_length }}``  The current value's length
======================  ============================================================

.. include:: /reference/constraints/_normalizer-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc
