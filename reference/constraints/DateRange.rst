DateRange
=========

Validates that two dates in an object constitutes to a valid date range.

+----------------+-------------------------------------------------------------------------------------+
| Applies to     | :ref:`class <validation-class-target>`                                              |
+----------------+-------------------------------------------------------------------------------------+
| Options        | - `invalidMessage`_                                                                 |
|                | - `startMessage`_                                                                   |
|                | - `endMessage`_                                                                     |
|                | - `limitFormat`_                                                                    |
|                | - `min`_                                                                            |
|                | - `max`_                                                                            |
|                | - `errorPath`_                                                                      |
+----------------+-------------------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\DateRange`                      |
+----------------+-------------------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\DateRangeValidator`             |
+----------------+-------------------------------------------------------------------------------------+

Basic Usage
-----------

Suppose you have an ``Acme\CalendarBundle\Entity\Event`` class that has looks like this:

.. code-block:: php

    namespace Acme\CalendarBundle\Entity;

    class Event
    {
        /**
         * @var DateTime
         */
        protected $start;

        /**
         * @var DateTime
         */
        protected $end;

        public function getStartDate()
        {
            return $this->start;
        }

        public function getEndDate()
        {
            return $this->end;
        }
    }

You would want to validate that `start` is earlier than `end`:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/CalendarBundle/Resources/config/validation.yml
        Acme\CalendarBundle\Entity\Event:
            constraints:
                - DateRange:
                    start: startDate
                    end: endDate

    .. code-block:: php-annotations

        // src/Acme/CalendarBundle/Entity/Author.php
        namespace Acme\CalendarBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;


        /**
         * @Assert\DateRange(start="startDate", end="endDate")
         */
        class Event
        {
            /**
             * @var DateTime
             */
            protected $start;

            /**
             * @var DateTime
             */
            protected $end;

            /** Getters **/
        }

    .. code-block:: xml

        <!-- src/Acme/CalendarBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\CalendarBundle\Entity\Event">
                <constraint name="DateRange">
                    <option name="start">startDate</option>
                    <option name="end">endDate</option>
                </constraint>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/CalendarBundle/Entity/Author.php
        namespace Acme\CalendarBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\DateRange(array(
                    'start' => 'startDate',
                    'end' => 'endDate',
                )));
            }
        }

Options
-------

invalidMessage
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Invalid date range.``

The message that's displayed when this constraint fails. This message is attached at the root. Also
thrown when either of the date values aren't ``DateTime`` instances.

startMessage
~~~~~~~~~~~~

**type**: ``string`` **default**: ``Start date must be less than or equal to {{ limit }}``

The message that is displayed when this constraint fails, and the ``errorPath`` is set to the value
of the ``start`` option (the start date property)

endMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``End date must be greater than or equal to {{ limit }}``

The message that is displayed when this constraint fails, and the ``errorPath`` is set to the value
of the ``end`` option (the end date property)

limitFormat
~~~~~~~~~~~

**type**: ``string`` **default**: ``Y-m-d``

The date format in which the ``{{ limit }}`` parameter is formatted as.

min
~~~

**type**: ``string`` **default**: ``null``

Minimum interval between dates. Must be a string that ``DateInterval::createFromDateString`` can understand.

max
~~~

**type**: ``string`` **default**: ``null``


Maximum interval between dates. Must be a string that ``DateInterval::createFromDateString`` can understand.

errorPath
~~~~~~~~~

**type**: ``string`` **default**: ``null``

Denotes where the violation message is attached. The error message is added at root level if the value is ``null``.
Otherwise, it must be the value of either the ``start`` or ``end`` options.