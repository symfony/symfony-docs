Time
====

Validates that a value is a valid time, meaning an object implementing
``DateTimeInterface`` or a string (or an object that can be cast into a string)
that follows a valid ``HH:MM:SS`` format.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                 |
+----------------+------------------------------------------------------------------------+
| Options        | - `groups`_                                                            |
|                | - `message`_                                                           |
|                | - `payload`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Time`              |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\TimeValidator`     |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

Suppose you have an Event class, with a ``startsAt`` field that is the time
of the day when the event starts:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Event.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            /**
             * @Assert\Time
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
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Event">
                <property name="startsAt">
                    <constraint name="Time"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Event.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Event
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('startsAt', new Assert\Time());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups_option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid time.``

This message is shown if the underlying data is not a valid time.

You can use the following parameters in this message:

+-----------------+-----------------------------+
| Parameter       | Description                 |
+=================+=============================+
| ``{{ value }}`` | The current (invalid) value |
+-----------------+-----------------------------+

.. include:: /reference/constraints/_payload_option.rst.inc
