Country
=======

Validates that a value is a valid `ISO 3166-1 alpha-2`_ country code.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Country`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CountryValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            #[Assert\Country]
            protected string $country;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                country:
                    - Country: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="country">
                    <constraint name="Country"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            // ...

            public static function loadValidationMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('country', new Assert\Country());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

alpha3
~~~~~~

**type**: ``boolean`` **default**: ``false``

If this option is ``true``, the constraint checks that the value is a
`ISO 3166-1 alpha-3`_ three-letter code (e.g. France = ``FRA``) instead
of the default `ISO 3166-1 alpha-2`_ two-letter code (e.g. France = ``FR``).

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid country.``

This message is shown if the string is not a valid country code.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) country code
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`ISO 3166-1 alpha-3`: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-3#Current_codes
