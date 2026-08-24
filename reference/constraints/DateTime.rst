DateTime
========

Validates that a value is a valid "datetime", meaning a string (or an object
that can be cast into a string) that follows a specific format.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\DateTime`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\DateTimeValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @var string A "Y-m-d H:i:s" formatted value
             */
            #[Assert\DateTime]
            protected string $createdAt;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                createdAt:
                    - DateTime: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="createdAt">
                    <constraint name="DateTime"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
           /**
            * @var string A "Y-m-d H:i:s" formatted value
            */
            protected string $createdAt;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('createdAt', new Assert\DateTime());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

``format``
~~~~~~~~~~

**type**: ``string`` **default**: ``Y-m-d H:i:s``

This option allows you to validate a custom date format. See
:phpmethod:`DateTime::createFromFormat` for formatting options.

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid datetime.``

This message is shown if the underlying data is not a valid datetime.

You can use the following parameters in this message:

================  ==============================================================
Parameter         Description
================  ==============================================================
``{{ value }}``   The current (invalid) value
``{{ label }}``   Corresponding form field label
``{{ format }}``  The date format defined in ``format``
================  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc
