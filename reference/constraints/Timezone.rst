Timezone
========

Validates that a value is a valid timezone identifier (e.g. ``Europe/Paris``).

==========  ======================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Timezone`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\TimezoneValidator`
==========  ======================================================================

Basic Usage
-----------

Suppose you have a ``UserSettings`` class, with a ``timezone`` field that is a
string which contains any of the `PHP timezone identifiers`_ (e.g. ``America/New_York``):

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/UserSettings.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class UserSettings
        {
            #[Assert\Timezone]
            protected string $timezone;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\UserSettings:
            properties:
                timezone:
                    - Timezone: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\UserSettings">
                <property name="timezone">
                    <constraint name="Timezone"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/UserSettings.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class UserSettings
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('timezone', new Assert\Timezone());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

``countryCode``
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

If the ``zone`` option is set to ``\DateTimeZone::PER_COUNTRY``, this option
restricts the valid timezone identifiers to the ones that belong to the given
country.

The value of this option must be a valid `ISO 3166-1 alpha-2`_ country code
(e.g. ``CN`` for China).

.. include:: /reference/constraints/_groups-option.rst.inc

``intlCompatible``
~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

This constraint considers valid both the `PHP timezone identifiers`_ and the
:ref:`ICU timezones <component-intl-timezones>` provided by Symfony's
:doc:`Intl component </components/intl>`

However, the timezones provided by the Intl component can be different from the
timezones provided by PHP's Intl extension (because they use different ICU
versions). If this option is set to ``true``, this constraint only considers
valid the values compatible with the PHP ``\IntlTimeZone::createTimeZone()`` method.

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid timezone.``

This message is shown if the underlying data is not a valid timezone identifier.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

``zone``
~~~~~~~~

**type**: ``string`` **default**: ``\DateTimeZone::ALL``

Set this option to any of the following constants to restrict the valid timezone
identifiers to the ones that belong to that geographical zone:

* ``\DateTimeZone::AFRICA``
* ``\DateTimeZone::AMERICA``
* ``\DateTimeZone::ANTARCTICA``
* ``\DateTimeZone::ARCTIC``
* ``\DateTimeZone::ASIA``
* ``\DateTimeZone::ATLANTIC``
* ``\DateTimeZone::AUSTRALIA``
* ``\DateTimeZone::EUROPE``
* ``\DateTimeZone::INDIAN``
* ``\DateTimeZone::PACIFIC``

In addition, there are some special zone values:

* ``\DateTimeZone::ALL`` accepts any timezone excluding deprecated timezones;
* ``\DateTimeZone::ALL_WITH_BC`` accepts any timezone including deprecated
  timezones;
* ``\DateTimeZone::PER_COUNTRY`` restricts the valid timezones to a certain
  country (which is defined using the ``countryCode`` option).

.. _`PHP timezone identifiers`: https://www.php.net/manual/en/timezones.php
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
