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

``requirePrefix``
~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

When set to ``true``, the version string must start with a "v" prefix 
(e.g., "v1.2.3" instead of "1.2.3").

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Package.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Package
        {
            #[Assert\SemVer(requirePrefix: true)]
            protected string $version;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Package:
            properties:
                version:
                    - SemVer:
                        requirePrefix: true

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Package">
                <property name="version">
                    <constraint name="SemVer">
                        <option name="requirePrefix">true</option>
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
                    'requirePrefix' => true,
                ]));
            }
        }

``allowPreRelease``
~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

Whether to allow pre-release versions (e.g., "1.2.3-beta", "2.0.0-rc.1").
When set to ``false``, only stable versions are considered valid.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Package.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Package
        {
            #[Assert\SemVer(allowPreRelease: false)]
            protected string $version;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Package:
            properties:
                version:
                    - SemVer:
                        allowPreRelease: false

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Package">
                <property name="version">
                    <constraint name="SemVer">
                        <option name="allowPreRelease">false</option>
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
                    'allowPreRelease' => false,
                ]));
            }
        }

``allowBuildMetadata``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

Whether to allow build metadata in the version string (e.g., "1.2.3+20130313144700").
When set to ``false``, build metadata is not allowed.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Package.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Package
        {
            #[Assert\SemVer(allowBuildMetadata: false)]
            protected string $version;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Package:
            properties:
                version:
                    - SemVer:
                        allowBuildMetadata: false

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Package">
                <property name="version">
                    <constraint name="SemVer">
                        <option name="allowBuildMetadata">false</option>
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
                    'allowBuildMetadata' => false,
                ]));
            }
        }

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

Valid Version Examples
----------------------

The following are examples of valid semantic versions:

- ``1`` (partial version)
- ``1.2`` (partial version)
- ``1.2.3`` (full version)
- ``v1.2.3`` (with prefix)
- ``1.2.3-alpha`` (pre-release)
- ``1.2.3-beta.1`` (pre-release with numeric identifier)
- ``1.2.3+20130313144700`` (with build metadata)
- ``1.2.3-beta+exp.sha.5114f85`` (pre-release and build metadata)

.. _`Semantic Versioning`: https://semver.org/