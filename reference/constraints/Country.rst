Country
=======

Validates that a value is a valid `ISO 3166-1 alpha-2`_ country code.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                 |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
|                | - `payload`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Country`           |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\CountryValidator`  |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            /**
             * @Assert\Country()
             */
             protected $country;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\User:
            properties:
                country:
                    - Country: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\User">
                <property name="country">
                    <constraint name="Country" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            public static function loadValidationMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('country', new Assert\Country());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid country.``

This message is shown if the string is not a valid country code.

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
