DateTime
========

Validates that a value is a valid "datetime", meaning either a ``DateTime``
object or a string (or an object that can be cast into a string) that follows
a specific format.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                 |
+----------------+------------------------------------------------------------------------+
| Options        | - `format`_                                                            |
|                | - `message`_                                                           |
|                | - `payload`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\DateTime`          |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\DateTimeValidator` |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\DateTime()
             */
             protected $createdAt;
        }

    .. code-block:: yaml

        # src/Resources/config/validation.yml
        App\Entity\Author:
            properties:
                createdAt:
                    - DateTime: ~

    .. code-block:: xml

        <!-- src/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="createdAt">
                    <constraint name="DateTime" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('createdAt', new Assert\DateTime());
            }
        }

Options
-------

format
~~~~~~

**type**: ``string`` **default**: ``Y-m-d H:i:s``

This option allows to validate a custom date format. See
:phpmethod:`DateTime::createFromFormat` for formatting options.

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid datetime.``

This message is shown if the underlying data is not a valid datetime.

.. include:: /reference/constraints/_payload-option.rst.inc
