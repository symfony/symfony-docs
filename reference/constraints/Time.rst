Time
====

Validates that a value is a valid time, meaning either a ``DateTime`` object
or a string (or an object that can be cast into a string) that follows
a valid "HH:MM:SS" format.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                 |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
|                | - `payload`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Time`              |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\TimeValidator`     |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

Suppose you have an Event class, with a ``startAt`` field that is the time
of the day when the event starts:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Event.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            /**
             * @Assert\Time()
             */
             protected $startsAt;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Event:
            properties:
                startsAt:
                    - Time: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Event">
                <property name="startsAt">
                    <constraint name="Time" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Event.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('startsAt', new Assert\Time());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid time.``

This message is shown if the underlying data is not a valid time.

.. include:: /reference/constraints/_payload-option.rst.inc
