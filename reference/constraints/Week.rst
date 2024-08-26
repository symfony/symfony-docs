Week
====

.. versionadded:: 7.2

    The ``Week`` constraint was introduced in Symfony 7.2.

Validates that a string (or an object implementing the ``Stringable`` PHP interface)
matches a given week number. The week number format is defined by `ISO-8601`_
and should be composed of the year and the week number, separated by a hyphen
(e.g. ``2022-W01``).

==========  =======================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Week`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\WeekValidator`
==========  =======================================================================

Basic Usage
-----------

If you wanted to ensure that the ``startWeek`` property of an ``OnlineCourse``
class is between the first and the twentieth week of the year 2022, you could do
the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/OnlineCourse.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class OnlineCourse
        {
            #[Assert\Week(min: '2022-W01', max: '2022-W20')]
            protected string $startWeek;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\OnlineCourse:
            properties:
                startWeek:
                    - Week:
                        min: '2022-W01'
                        max: '2022-W20'

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\OnlineCourse">
                <property name="startWeek">
                    <constraint name="Week">
                        <option name="min">2022-W01</option>
                        <option name="max">2022-W20</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/OnlineCourse.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class OnlineCourse
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('startWeek', new Assert\Week([
                    'min' => '2022-W01',
                    'max' => '2022-W20',
                ]));
            }
        }

.. note::

    The constraint also checks that the given week exists in the calendar. For example,
    ``2022-W53`` is not a valid week number for 2022, because 2022 only had 52 weeks.

Options
-------

``min``
~~~~~~~

**type**: ``string`` **default**: ``null``

The minimum week number that the value must match.

``max``
~~~~~~~

**type**: ``string`` **default**: ``null``

The maximum week number that the value must match.

.. include:: /reference/constraints/_groups-option.rst.inc

``invalidFormatMessage``
~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value does not represent a valid week in the ISO 8601 format.``

This is the message that will be shown if the value does not match the ISO 8601
week format.

``invalidWeekNumberMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The week "{{ value }}" is not a valid week.``

This is the message that will be shown if the value does not match a valid week
number.

You can use the following parameters in this message:

================  ==================================================
Parameter         Description
================  ==================================================
``{{ value }}``   The value that was passed to the constraint
================  ==================================================

``tooLowMessage``
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The value should not be before week "{{ min }}".``

This is the message that will be shown if the value is lower than the minimum
week number.

You can use the following parameters in this message:

================  ==================================================
Parameter         Description
================  ==================================================
``{{ min }}``     The minimum week number
================  ==================================================

``tooHighMessage``
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The value should not be after week "{{ max }}".``

This is the message that will be shown if the value is higher than the maximum
week number.

You can use the following parameters in this message:

================  ==================================================
Parameter         Description
================  ==================================================
``{{ max }}``     The maximum week number
================  ==================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. _ISO-8601: https://en.wikipedia.org/wiki/ISO_8601
