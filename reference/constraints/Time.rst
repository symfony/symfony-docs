Time
====

Validates that a value is a valid time, meaning a string (or an object that can
be cast into a string) that follows a valid ``HH:MM:SS`` format.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Time`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\TimeValidator`
==========  ===================================================================

Basic Usage
-----------

Suppose you have an Event class, with a ``startsAt`` field that is the time
of the day when the event starts:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Event.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            /**
             * @Assert\Time
             * @var string A "H:i:s" formatted value
             */
            protected $startsAt;
        }

    .. code-block:: php-attributes

        // src/Entity/Event.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Event
        {
            /**
             * @var string A "H:i:s" formatted value
             */
            #[Assert\Time]
            protected $startsAt;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Event:
            properties:
                startsAt:
                    - Time: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Event">
                <property name="startsAt">
                    <constraint name="Time"/>
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
           /**
            * @var string A "H:i:s" formatted value
            */
            protected $startsAt;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('startsAt', new Assert\Time());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid time.``

This message is shown if the underlying data is not a valid time.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. versionadded:: 5.2

    The ``{{ label }}`` parameter was introduced in Symfony 5.2.

.. include:: /reference/constraints/_payload-option.rst.inc
