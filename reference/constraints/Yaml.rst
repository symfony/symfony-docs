Yaml
====

Validates that a value has valid `YAML`_ syntax.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Yaml`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\YamlValidator`
==========  ===================================================================

Basic Usage
-----------

The ``Yaml`` constraint can be applied to a property or a "getter" method:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Report.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Report
        {
            #[Assert\Yaml(
                message: "Your configuration doesn't have valid YAML syntax."
            )]
            private string $customConfiguration;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Report:
            properties:
                customConfiguration:
                    - Yaml:
                        message: Your configuration doesn't have valid YAML syntax.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Report">
                <property name="customConfiguration">
                    <constraint name="Yaml">
                        <option name="message">Your configuration doesn't have valid YAML syntax.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Report.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Report
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('customConfiguration', new Assert\Yaml(
                    message: 'Your configuration doesn\'t have valid YAML syntax.',
                ));
            }
        }

Options
-------

``flags``
~~~~~~~~~

**type**: ``integer`` **default**: ``0``

This option enables optional features of the YAML parser when validating contents.
Its value is a combination of one or more of the :ref:`flags defined by the Yaml component <yaml-flags>`:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Report.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Yaml\Yaml;

        class Report
        {
            #[Assert\Yaml(
                message: "Your configuration doesn't have valid YAML syntax.",
                flags: Yaml::PARSE_CONSTANT | Yaml::PARSE_CUSTOM_TAGS | Yaml::PARSE_DATETIME,
            )]
            private string $customConfiguration;
        }

    .. code-block:: php

        // src/Entity/Report.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Yaml\Yaml;

        class Report
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('customConfiguration', new Assert\Yaml(
                    message: 'Your configuration doesn\'t have valid YAML syntax.',
                    flags: Yaml::PARSE_CONSTANT | Yaml::PARSE_CUSTOM_TAGS | Yaml::PARSE_DATETIME,
                ));
            }
        }

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not valid YAML.``

This message shown if the underlying data is not a valid YAML value.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ error }}``  The full error message from the YAML parser
``{{ line }}``   The line where the YAML syntax error happened
===============  ==============================================================

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`YAML`: https://en.wikipedia.org/wiki/YAML
