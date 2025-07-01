SemVer
======

Validates that a value is a valid semantic version string according to the 
`Semantic Versioning`_ specification. This constraint supports various 
version formats including partial versions, pre-release versions, and 
build metadata.

.. versionadded:: 7.4

    The ``SemVer`` constraint was introduced in Symfony 7.4.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\SemVer`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\SemVerValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Package.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Package
        {
            #[Assert\SemVer]
            protected string $version;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Package:
            properties:
                version:
                    - SemVer: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Package">
                <property name="version">
                    <constraint name="SemVer" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Package.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Package
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('version', new Assert\SemVer());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

``strict``
~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

When set to ``true``, the version must strictly follow the official 
`Semantic Versioning`_ specification. This means:

- No "v" prefix is allowed (use "1.2.3", not "v1.2.3")
- A full version is required (major.minor.patch)

When set to ``false``, common version variations are allowed:

- The "v" prefix is accepted (e.g., "v1.2.3")
- Partial versions are valid (e.g., "1", "1.2")

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Package.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Package
        {
            #[Assert\SemVer(strict: false)]
            protected string $version;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Package:
            properties:
                version:
                    - SemVer:
                        strict: false

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Package">
                <property name="version">
                    <constraint name="SemVer">
                        <option name="strict">false</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Package.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Package
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('version', new Assert\SemVer([
                    'strict' => false,
                ]));
            }
        }

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

Valid Version Examples
----------------------

When using ``strict: true`` (default), the following are valid:

- ``1.2.3`` (full version)
- ``1.2.3-alpha`` (pre-release)
- ``1.2.3-beta.1`` (pre-release with numeric identifier)
- ``1.2.3+20130313144700`` (with build metadata)
- ``1.2.3-beta+exp.sha.5114f85`` (pre-release and build metadata)

When using ``strict: false``, additional formats are accepted:

- ``1`` (partial version)
- ``1.2`` (partial version)
- ``v1.2.3`` (with "v" prefix)
- ``v1.2.3-alpha`` (prefix with pre-release)

.. _`Semantic Versioning`: https://semver.org/