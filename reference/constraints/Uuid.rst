Uuid
====

.. versionadded:: 2.5
    The Uuid constraint was introduced in Symfony 2.5.

Validates that a value is a valid `Universally unique identifier (UUID)`_ per `RFC 4122`_.
By default, this will validate the format according to the RFC's guidelines, but this can
be relaxed to accept non-standard UUIDs that other systems (like PostgreSQL) accept.
UUID versions can also be restricted using a whitelist.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `message`_                                                        |
|                | - `strict`_                                                         |
|                | - `versions`_                                                       |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Uuid`           |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\UuidValidator`  |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/UploadsBundle/Resources/config/validation.yml
        Acme\UploadsBundle\Entity\File:
            properties:
                identifier:
                    - Uuid: ~

    .. code-block:: php-annotations

        // src/Acme/UploadsBundle/Entity/File.php
        namespace Acme\UploadsBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class File
        {
            /**
             * @Assert\Uuid
             */
             protected $identifier;
        }

    .. code-block:: xml

        <!-- src/Acme/UploadsBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\UploadsBundle\Entity\File">
                <property name="identifier">
                    <constraint name="Uuid" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/UploadsBundle/Entity/File.php
        namespace Acme\UploadsBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class File
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('identifier', new Assert\Uuid());
            }
        }


Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This is not a valid UUID.``

This message is shown if the string is not a valid UUID.

strict
~~~~~~

**type**: ``boolean`` **default**: ``true``

If this option is set to ``true`` the constraint will check if the UUID is formatted per the
RFC's input format rules: ``216fff40-98d9-11e3-a5e2-0800200c9a66``. Setting this to ``false``
will allow alternate input formats like:

* ``216f-ff40-98d9-11e3-a5e2-0800-200c-9a66``
* ``{216fff40-98d9-11e3-a5e2-0800200c9a66}``
* ``216fff4098d911e3a5e20800200c9a66``

versions
~~~~~~~~

**type**: ``int[]`` **default**: ``[1,2,3,4,5]``

This option can be used to only allow specific `UUID versions`_.  Valid versions are 1 - 5.
The following PHP constants can also be used:

* ``Uuid::V1_MAC``
* ``Uuid::V2_DCE``
* ``Uuid::V3_MD5``
* ``Uuid::V4_RANDOM``
* ``Uuid::V5_SHA1``

All five versions are allowed by default.

.. _`Universally unique identifier (UUID)`: http://en.wikipedia.org/wiki/Universally_unique_identifier
.. _`RFC 4122`: http://tools.ietf.org/html/rfc4122
.. _`UUID versions`: http://en.wikipedia.org/wiki/Universally_unique_identifier#Variants_and_versions
