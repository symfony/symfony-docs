Issn
====

.. versionadded:: 2.3
    The Issn constraint was introduced in Symfony 2.3.

Validates that a value is a valid
`International Standard Serial Number (ISSN)`_.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `message`_                                                          |
|                | - `caseSensitive`_                                                    |
|                | - `requireHyphen`_                                                    |
|                | - `payload`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Issn`             |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\IssnValidator`    |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Journal.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Journal
        {
            /**
             * @Assert\Issn
             */
             protected $issn;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Journal:
            properties:
                issn:
                    - Issn: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Journal">
                <property name="issn">
                    <constraint name="Issn" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Journal.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Journal
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('issn', new Assert\Issn());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``String`` default: ``This value is not a valid ISSN.``

The message shown if the given value is not a valid ISSN.

caseSensitive
~~~~~~~~~~~~~

**type**: ``boolean`` default: ``false``

The validator will allow ISSN values to end with a lower case 'x' by default.
When switching this to ``true``, the validator requires an upper case 'X'.

requireHyphen
~~~~~~~~~~~~~

**type**: ``boolean`` default: ``false``

The validator will allow non hyphenated ISSN values by default. When switching
this to ``true``, the validator requires a hyphenated ISSN value.

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`International Standard Serial Number (ISSN)`: https://en.wikipedia.org/wiki/Issn
