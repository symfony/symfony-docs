Count
=====

Validates that a given collection's (i.e. an array or an object that implements
Countable) element count is *between* some minimum and maximum value.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Count`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CountValidator`
==========  ===================================================================

Basic Usage
-----------

To verify that the ``emails`` array field contains between 1 and 5 elements
you might add the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Participant.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            #[Assert\Count(
                min: 1,
                max: 5,
                minMessage: 'You must specify at least one email',
                maxMessage: 'You cannot specify more than {{ limit }} emails',
            )]
            protected $emails = [];
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Participant:
            properties:
                emails:
                    - Count:
                        min: 1
                        max: 5
                        minMessage: 'You must specify at least one email'
                        maxMessage: 'You cannot specify more than {{ limit }} emails'

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Participant">
                <property name="emails">
                    <constraint name="Count">
                        <option name="min">1</option>
                        <option name="max">5</option>
                        <option name="minMessage">You must specify at least one email</option>
                        <option name="maxMessage">You cannot specify more than {{ limit }} emails</option>
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
                $metadata->addPropertyConstraint('emails', new Assert\Count([
                    'min' => 1,
                    'max' => 5,
                    'minMessage' => 'You must specify at least one email',
                    'maxMessage' => 'You cannot specify more than {{ limit }} emails',
                ]));
            }
        }

Options
-------

``divisibleBy``
~~~~~~~~~~~~~~~

**type**: ``integer`` **default**: null

Validates that the number of elements of the given collection is divisible by
a certain number.

.. seealso::

    If you need to validate that other types of data different from collections
    are divisible by a certain number, use the
    :doc:`DivisibleBy </reference/constraints/DivisibleBy>` constraint.

``divisibleByMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The number of elements in this collection should be a multiple of {{ compared_value }}.``

The message that will be shown if the number of elements of the given collection
is not divisible by the number defined in the ``divisibleBy`` option.

You can use the following parameters in this message:

========================  ===================================================
Parameter                 Description
========================  ===================================================
``{{ compared_value }}``  The number configured in the ``divisibleBy`` option
========================  ===================================================

``exactMessage``
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This collection should contain exactly {{ limit }} elements.``

The message that will be shown if min and max values are equal and the underlying
collection elements count is not exactly this value.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ count }}``  The current collection size
``{{ limit }}``  The exact expected collection size
===============  ==============================================================

.. include:: /reference/constraints/_groups-option.rst.inc

``max``
~~~~~~~

**type**: ``integer``

This option is the "max" count value. Validation will fail if the given
collection elements count is **greater** than this max value.

This option is required when the ``min`` option is not defined.

``maxMessage``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This collection should contain {{ limit }} elements or less.``

The message that will be shown if the underlying collection elements count
is more than the `max`_ option.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ count }}``  The current collection size
``{{ limit }}``  The upper limit
===============  ==============================================================

``min``
~~~~~~~

**type**: ``integer``

This option is the "min" count value. Validation will fail if the given
collection elements count is **less** than this min value.

This option is required when the ``max`` option is not defined.

``minMessage``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This collection should contain {{ limit }} elements or more.``

The message that will be shown if the underlying collection elements count
is less than the `min`_ option.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ count }}``  The current collection size
``{{ limit }}``  The lower limit
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc
