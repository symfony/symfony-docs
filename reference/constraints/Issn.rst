Issn
====

.. versionadded:: 2.3
    The Issn constraint was introduced in Symfony 2.3.

Validates that a value is a valid `International Standard Serial Number (ISSN)`_.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `message`_                                                          |
|                | - `caseSensitive`_                                                    |
|                | - `requireHyphen`_                                                    |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Issn`             |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\IssnValidator`    |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/JournalBundle/Resources/config/validation.yml
        Acme\JournalBundle\Entity\Journal:
            properties:
                issn:
                    - Issn: ~

    .. code-block:: php-annotations

        // src/Acme/JournalBundle/Entity/Journal.php
        namespace Acme\JournalBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Journal
        {
            /**
             * @Assert\Issn
             */
             protected $issn;
        }

    .. code-block:: xml

        <!-- src/Acme/JournalBundle/Resources/config/validation.xml -->
        <class name="Acme\JournalBundle\Entity\Journal">
            <property name="issn">
                <constraint name="Issn" />
            </property>
        </class>

    .. code-block:: php

        // src/Acme/JournalBundle/Entity/Journal.php
        namespace Acme\JournalBundle\Entity;

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

**type**: ``Boolean`` default: ``false``

The validator will allow ISSN values to end with a lower case 'x' by default.
When switching this to ``true``, the validator requires an upper case 'X'.

requireHyphen
~~~~~~~~~~~~~

**type**: ``Boolean`` default: ``false``

The validator will allow non hyphenated ISSN values by default. When switching
this to ``true``, the validator requires a hyphenated ISSN value.

.. _`International Standard Serial Number (ISSN)`: http://en.wikipedia.org/wiki/Issn

