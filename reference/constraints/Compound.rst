Compound
========

To the contrary to the other constraints, this constraint cannot be used on its own.
Instead, it allows you to create your own set of reusable constraints, representing
rules to use consistently across your application, by extending the constraint.

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>` or :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Compound`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CompoundValidator`
==========  ===================================================================

Basic Usage
-----------

Suppose that you have different places where a user password must be validated,
you can create your own named set or requirements to be reused consistently everywhere:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Validator/Constraints/PasswordRequirements.php
        namespace App\Validator\Constraints;

        use Symfony\Component\Validator\Constraints\Compound;
        use Symfony\Component\Validator\Constraints as Assert;

        #[\Attribute]
        class PasswordRequirements extends Compound
        {
            protected function getConstraints(array $options): array
            {
                return [
                    new Assert\NotBlank(),
                    new Assert\Type('string'),
                    new Assert\Length(['min' => 12]),
                    new Assert\NotCompromisedPassword(),
                ];
            }
        }

Add ``#[\Attribute]`` to the constraint class if you want to
use it as an attribute in other classes. If the constraint has
configuration options, define them as public properties on the constraint class.

You can now use it anywhere you need it:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity\User;

        use App\Validator\Constraints as Assert;

        class User
        {
            #[Assert\PasswordRequirements]
            public string $plainPassword;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                plainPassword:
                    - App\Validator\Constraints\PasswordRequirements: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="plainPassword">
                    <constraint name="App\Validator\Constraints\PasswordRequirements"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity\User;

        use App\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('plainPassword', new Assert\PasswordRequirements());
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc
