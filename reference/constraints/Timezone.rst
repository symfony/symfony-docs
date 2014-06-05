Timezone
========

.. versionadded:: 2.6
    The Timezone constraint was introduced in Symfony 2.6.

Validates that a value is a valid timezone.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                 |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Timezone`          |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\TimezoneValidator` |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

Suppose you have an User class, with a ``timezone`` field that is the timezone where live the user when the event starts:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/validation.yml
        Acme\DemoBundle\Entity\User:
            properties:
                timezone:
                    - Timezone: ~

    .. code-block:: php-annotations

        // src/Acme/DemoBundle/Entity/Event.php
        namespace Acme\DemoBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            /**
             * @Assert\Timezone()
             */
             protected $timezone;
        }

    .. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\DemoBundle\Entity\User">
                <property name="timezone">
                    <constraint name="Timezone" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/DemoBundle/Entity/User.php
        namespace Acme\DemoBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('timezone', new Assert\Timezone());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid timezone.``

This message is shown if the underlying data is not a valid timezone.

