Length
======

Validates that a given string length is *between* some minimum and maximum value.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `allowEmptyString`_
            - `charset`_
            - `charsetMessage`_
            - `exactMessage`_
            - `groups`_
            - `max`_
            - `maxMessage`_
            - `min`_
            - `minMessage`_
            - `normalizer`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Length`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\LengthValidator`
==========  ===================================================================

Basic Usage
-----------

To verify that the ``firstName`` field length of a class is between ``2``
and ``50``, you might add the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Participant.php
        namespace App\Entity;

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
            protected $firstName;
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
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new Assert\Length([
                    'min' => 2,
                    'max' => 50,
                    'minMessage' => 'Your first name must be at least {{ limit }} characters long',
                    'maxMessage' => 'Your first name cannot be longer than {{ limit }} characters',
                ]));
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

``allowEmptyString``
~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean``  **default**: ``false``

.. deprecated:: 5.2

    The ``allowEmptyString`` option is deprecated since Symfony 5.2. If you
    want to allow empty strings too, combine the ``Length`` constraint with
    the :doc:`Blank constraint </reference/constraints/Blank>` inside the
    :doc:`AtLeastOneOf constraint </reference/constraints/AtLeastOneOf>`.

If set to ``true``, empty strings are considered valid (which is the same
behavior as previous Symfony versions). The default ``false`` value considers
empty strings not valid.

.. caution::

    This option does not have any effect when no minimum length is given.

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

exactMessage
~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should have exactly {{ limit }} characters.``

The message that will be shown if min and max values are equal and the underlying
value's length is not exactly this value.

You can use the following parameters in this message:

=================  ============================================================
Parameter          Description
=================  ============================================================
``{{ limit }}``    The exact expected length
``{{ value }}``    The current (invalid) value
=================  ============================================================

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

=================  ============================================================
Parameter          Description
=================  ============================================================
``{{ limit }}``    The expected maximum length
``{{ value }}``    The current (invalid) value
=================  ============================================================

``min``
~~~~~~~

**type**: ``integer``

This option is the "min" length value. Validation will fail if
the given value's length is **less** than this min value.

This option is required when the ``max`` option is not defined.

It is important to notice that NULL values and empty strings are considered
valid no matter if the constraint required a minimum length. Validators
are triggered only if the value is not blank.

``minMessage``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too short. It should have {{ limit }} characters or more.``

The message that will be shown if the underlying value's length is less
than the `min`_ option.

You can use the following parameters in this message:

=================  ============================================================
Parameter          Description
=================  ============================================================
``{{ limit }}``    The expected minimum length
``{{ value }}``    The current (invalid) value
=================  ============================================================

.. include:: /reference/constraints/_normalizer-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc
