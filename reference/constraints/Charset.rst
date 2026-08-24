Charset
=======

Validates that a string (or an object implementing the ``Stringable`` PHP interface)
is encoded in a given charset.

==========  =====================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Charset`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CharsetValidator`
==========  =====================================================================

Basic Usage
-----------

If you wanted to ensure that the ``content`` property of a ``FileDTO``
class uses UTF-8, you could do the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/FileDTO.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class FileDTO
        {
            #[Assert\Charset('UTF-8')]
            protected string $content;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\FileDTO:
            properties:
                content:
                    - Charset:
                        charset: 'UTF-8'

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\FileDTO">
                <property name="content">
                    <constraint name="Charset">
                        <option name="charset">UTF-8</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/FileDTO.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class FileDTO
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('content', new Assert\Charset('UTF-8'));
            }
        }

Options
-------

``encodings``
~~~~~~~~~~~~~

**type**: ``array`` | ``string`` **default**: ``[]``

An encoding or a set of encodings to check against. If you pass an array of
encodings, the validator will check if the value is encoded in *any* of the
encodings. This option accepts any value that can be passed to the
:phpfunction:`mb_detect_encoding` PHP function.

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``The detected character encoding is invalid ({{ detected }}). Allowed encodings are {{ encodings }}.``

This is the message that will be shown if the value does not match any of the
accepted encodings.

You can use the following parameters in this message:

===================  ==============================================================
Parameter            Description
===================  ==============================================================
``{{ detected }}``   The detected encoding
``{{ encodings }}``  The accepted encodings
===================  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc
