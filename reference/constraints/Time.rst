Time
====

Validates that a value is a valid time, meaning either a ``DateTime`` object
or a string (or an object that can be cast into a string) that follows
a valid "HH:MM:SS" format.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                  |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
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

    .. code-block:: yaml

        # src/Acme/EventBundle/Resources/config/validation.yml
        Acme\EventBundle\Entity\Event:
            properties:
                startsAt:
                    - Time: ~

    .. code-block:: php-annotations

        // src/Acme/EventBundle/Entity/Event.php
        namespace Acme\EventBundle\Entity;
        
        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            /**
             * @Assert\Time()
             */
             protected $startsAt;
        }

    .. code-block:: xml

        <!-- src/Acme/EventBundle/Resources/config/validation.xml -->
        <class name="Acme\EventBundle\Entity\Event">
            <property name="startsAt">
                <constraint name="Time" />
            </property>
        </class>

    .. code-block:: php
        
        // src/Acme/EventBundle/Entity/Event.php
        namespace Acme\EventBundle\Entity;
        
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

**type**: ``string`` **default**: ``This value is not a valid time``

This message is shown if the underlying data is not a valid time.
