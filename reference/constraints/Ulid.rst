ULID
====

Validates that a value is a valid `Universally Unique Lexicographically Sortable Identifier (ULID)`_.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Ulid`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\UlidValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/File.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class File
        {
            #[Assert\Ulid]
            protected string $identifier;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\File:
            properties:
                identifier:
                    - Ulid: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\File">
                <property name="identifier">
                    <constraint name="Ulid"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/File.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class File
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('identifier', new Assert\Ulid());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This is not a valid ULID.``

This message is shown if the string is not a valid ULID.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_normalizer-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc


.. _`Universally Unique Lexicographically Sortable Identifier (ULID)`: https://github.com/ulid/spec
