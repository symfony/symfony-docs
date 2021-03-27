Range
=====

Validates that a given number or ``DateTime`` object is *between* some minimum and maximum.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `invalidDateTimeMessage`_
            - `invalidMessage`_
            - `max`_
            - `maxMessage`_
            - `maxPropertyPath`_
            - `min`_
            - `minMessage`_
            - `minPropertyPath`_
            - `notInRangeMessage`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Range`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\RangeValidator`
==========  ===================================================================

Basic Usage
-----------

To verify that the ``height`` field of a class is between ``120`` and ``180``,
you might add the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Participant.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\Range(
             *      min = 120,
             *      max = 180,
             *      notInRangeMessage = "You must be between {{ min }}cm and {{ max }}cm tall to enter",
             * )
             */
            protected $height;
        }

    .. code-block:: php-attributes

        // src/Entity/Participant.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            #[Assert\Range(
                min: 120,
                max: 180,
                notInRangeMessage: 'You must be between {{ min }}cm and {{ max }}cm tall to enter',
            )]
            protected $height;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Participant:
            properties:
                height:
                    - Range:
                        min: 120
                        max: 180
                        notInRangeMessage: You must be between {{ min }}cm and {{ max }}cm tall to enter

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Participant">
                <property name="height">
                    <constraint name="Range">
                        <option name="min">120</option>
                        <option name="max">180</option>
                        <option name="notInRangeMessage">You must be between {{ min }}cm and {{ max }}cm tall to enter</option>
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
                $metadata->addPropertyConstraint('height', new Assert\Range([
                    'min' => 120,
                    'max' => 180,
                    'notInRangeMessage' => 'You must be between {{ min }}cm and {{ max }}cm tall to enter',
                ]));
            }
        }

Date Ranges
-----------

This constraint can be used to compare ``DateTime`` objects against date ranges.
The minimum and maximum date of the range should be given as any date string
`accepted by the DateTime constructor`_. For example, you could check that a
date must lie within the current year like this:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Event.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            /**
             * @Assert\Range(
             *      min = "first day of January",
             *      max = "first day of January next year"
             * )
             */
            protected $startDate;
        }

    .. code-block:: php-attributes

        // src/Entity/Event.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            #[Assert\Range(
                min: 'first day of January',
                max: 'first day of January next year',
            )]
            protected $startDate;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Event:
            properties:
                startDate:
                    - Range:
                        min: first day of January
                        max: first day of January next year

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Event">
                <property name="startDate">
                    <constraint name="Range">
                        <option name="min">first day of January</option>
                        <option name="max">first day of January next year</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Event.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Event
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('startDate', new Assert\Range([
                    'min' => 'first day of January',
                    'max' => 'first day of January next year',
                ]));
            }
        }

Be aware that PHP will use the server's configured timezone to interpret these
dates. If you want to fix the timezone, append it to the date string:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Event.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            /**
             * @Assert\Range(
             *      min = "first day of January UTC",
             *      max = "first day of January next year UTC"
             * )
             */
            protected $startDate;
        }

    .. code-block:: php-attributes

        // src/Entity/Event.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            #[Assert\Range(
                min: 'first day of January UTC',
                max: 'first day of January next year UTC',
            )]
            protected $startDate;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Event:
            properties:
                startDate:
                    - Range:
                        min: first day of January UTC
                        max: first day of January next year UTC

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Event">
                <property name="startDate">
                    <constraint name="Range">
                        <option name="min">first day of January UTC</option>
                        <option name="max">first day of January next year UTC</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Event
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('startDate', new Assert\Range([
                    'min' => 'first day of January UTC',
                    'max' => 'first day of January next year UTC',
                ]));
            }
        }

The ``DateTime`` class also accepts relative dates or times. For example, you
can check that a delivery date starts within the next five hours like this:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\Range(
             *      min = "now",
             *      max = "+5 hours"
             * )
             */
            protected $deliveryDate;
        }

    .. code-block:: php-attributes

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            #[Assert\Range(
                min: 'now',
                max: '+5 hours',
            )]
            protected $deliveryDate;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Order:
            properties:
                deliveryDate:
                    - Range:
                        min: now
                        max: +5 hours

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Order">
                <property name="deliveryDate">
                    <constraint name="Range">
                        <option name="min">now</option>
                        <option name="max">+5 hours</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Order
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('deliveryDate', new Assert\Range([
                    'min' => 'now',
                    'max' => '+5 hours',
                ]));
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``invalidDateTimeMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be a valid number.``

.. versionadded:: 5.2

    The ``invalidDateTimeMessage`` option was introduced in Symfony 5.2.

The message displayed when the ``min`` and ``max`` values are PHP datetimes but
the given value is not.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
===============  ==============================================================

``invalidMessage``
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be a valid number.``

The message displayed when the ``min`` and ``max`` values are numeric (per
the :phpfunction:`is_numeric` PHP function) but the given value is not.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. versionadded:: 5.2

    The ``{{ label }}`` parameter was introduced in Symfony 5.2.

``max``
~~~~~~~

**type**: ``number`` or ``string`` (date format)

This required option is the "max" value. Validation will fail if the given
value is **greater** than this max value.

``maxMessage``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be {{ limit }} or less.``

The message that will be shown if the underlying value is more than the
`max`_ option, and no `min`_ option has been defined (if both are defined, use
`notInRangeMessage`_).

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ limit }}``  The upper limit
``{{ value }}``  The current (invalid) value
===============  ==============================================================

``maxPropertyPath``
~~~~~~~~~~~~~~~~~~~

**type**: ``string``

It defines the object property whose value is used as ``max`` option.

For example, if you want to compare the ``$submittedDate`` property of some object
with regard to the ``$deadline`` property of the same object, use
``maxPropertyPath="deadline"`` in the range constraint of ``$submittedDate``.

.. tip::

    When using this option, its value is available in error messages as the
    ``{{ max_limit_path }}`` placeholder. Although it's not intended to
    include it in the error messages displayed to end users, it's useful when
    using APIs for doing any mapping logic on client-side.

``min``
~~~~~~~

**type**: ``number`` or ``string`` (date format)

This required option is the "min" value. Validation will fail if the given
value is **less** than this min value.

``minMessage``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be {{ limit }} or more.``

The message that will be shown if the underlying value is less than the
`min`_ option, and no `max`_ option has been defined (if both are defined, use
`notInRangeMessage`_).

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ limit }}``  The lower limit
``{{ value }}``  The current (invalid) value
===============  ==============================================================

``minPropertyPath``
~~~~~~~~~~~~~~~~~~~

**type**: ``string``

It defines the object property whose value is used as ``min`` option.

For example, if you want to compare the ``$endDate`` property of some object
with regard to the ``$startDate`` property of the same object, use
``minPropertyPath="startDate"`` in the range constraint of ``$endDate``.

.. tip::

    When using this option, its value is available in error messages as the
    ``{{ min_limit_path }}`` placeholder. Although it's not intended to
    include it in the error messages displayed to end users, it's useful when
    using APIs for doing any mapping logic on client-side.

``notInRangeMessage``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be between {{ min }} and {{ max }}.``

The message that will be shown if the underlying value is less than the
`min`_ option or greater than the `max`_ option.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ max }}``    The upper limit
``{{ min }}``    The lower limit
``{{ value }}``  The current (invalid) value
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`accepted by the DateTime constructor`: https://www.php.net/manual/en/datetime.formats.php
