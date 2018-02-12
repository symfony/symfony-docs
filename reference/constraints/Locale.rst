Locale
======

Validates that a value is a valid locale.

The "value" for each locale is any of `ICU format locale IDs`. By instance the two
letter `ISO 639-1`_ *language* code (e.g. ``fr``), or the language code followed
by an underscore (``_``), then the `ISO 3166-1 alpha-2`_ *country* code (e.g. ``fr_FR``
for French/France).

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                 |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
|                | - `payload`_                                                           |
|                | - `canonicalize`_                                                      |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Locale`            |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\LocaleValidator`   |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            /**
             * @Assert\Locale(
             *     canonicalize = true
             * )
             */
             protected $locale;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                locale:
                    - Locale:
                        canonicalize: true

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="locale">
                    <constraint name="Locale">
                        <option name="canonicalize">true</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('locale', new Assert\Locale(array('canonicalize' => true)));
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid locale.``

This message is shown if the string is not a valid locale.

.. include:: /reference/constraints/_payload-option.rst.inc

canonicalize
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 4.1
    Using this option with value ``false`` was deprecated in Symfony 4.1 and it
    will throw an exception in Symfony 5.0, use `true` instead.

If true, the :phpmethod:`Locale::canonicalize` method will be applied before checking
the validity of the given locale (e.g. ``FR-fr.utf8`` is transformed into ``fr_FR``
).

.. _`ISO 639-1`: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`ICU format locale IDs`: http://userguide.icu-project.org/locale#TOC-Canonicalization
