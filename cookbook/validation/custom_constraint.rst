.. index::
   single: Validation; Custom constraints

How to Create a custom Validation Constraint
============================================

You can create a custom constraint by extending the base constraint class,
:class:`Symfony\\Component\\Validator\\Constraint`.
As an example you're going to create a simple validator that checks if a string
contains only alphanumeric characters.

Creating the Constraint Class
-----------------------------

First you need to create a Constraint class and extend :class:`Symfony\\Component\\Validator\\Constraint`::

    // src/AppBundle/Validator/Constraints/ContainsAlphanumeric.php
    namespace AppBundle\Validator\Constraints;

    use Symfony\Component\Validator\Constraint;

    /**
     * @Annotation
     */
    class ContainsAlphanumeric extends Constraint
    {
        public $message = 'The string "%string%" contains an illegal character: it can only contain letters or numbers.';
    }

.. note::

    The ``@Annotation`` annotation is necessary for this new constraint in
    order to make it available for use in classes via annotations.
    Options for your constraint are represented as public properties on the
    constraint class.

Creating the Validator itself
-----------------------------

As you can see, a constraint class is fairly minimal. The actual validation is
performed by another "constraint validator" class. The constraint validator
class is specified by the constraint's ``validatedBy()`` method, which
includes some simple default logic::

    // in the base Symfony\Component\Validator\Constraint class
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

In other words, if you create a custom ``Constraint`` (e.g. ``MyConstraint``),
Symfony will automatically look for another class, ``MyConstraintValidator``
when actually performing the validation.

The validator class is also simple, and only has one required method ``validate()``::

    // src/AppBundle/Validator/Constraints/ContainsAlphanumericValidator.php
    namespace AppBundle\Validator\Constraints;

    use Symfony\Component\Validator\Constraint;
    use Symfony\Component\Validator\ConstraintValidator;

    class ContainsAlphanumericValidator extends ConstraintValidator
    {
        public function validate($value, Constraint $constraint)
        {
            if (!preg_match('/^[a-zA-Z0-9]+$/', $value, $matches)) {
                // If you're using the new 2.5 validation API (you probably are!)
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', $value)
                    ->addViolation();

                // If you're using the old 2.4 validation API
                /*
                $this->context->addViolation(
                    $constraint->message,
                    array('%string%' => $value)
                );
                */
            }
        }
    }

Inside ``validate``, you don't need to return a value. Instead, you add violations
to the validator's ``context`` property and a value will be considered valid
if it causes no violations. The ``buildViolation`` method takes the error
message as its argument and returns an instance of
:class:`Symfony\\Component\\Validator\\Violation\\ConstraintViolationBuilderInterface`.
The ``addViolation`` method call finally adds the violation to the context.

Using the new Validator
-----------------------

Using custom validators is very easy, just as the ones provided by Symfony itself:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/AcmeEntity.php
        use Symfony\Component\Validator\Constraints as Assert;
        use AppBundle\Validator\Constraints as AcmeAssert;

        class AcmeEntity
        {
            // ...

            /**
             * @Assert\NotBlank
             * @AcmeAssert\ContainsAlphanumeric
             */
            protected $name;

            // ...
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\AcmeEntity:
            properties:
                name:
                    - NotBlank: ~
                    - AppBundle\Validator\Constraints\ContainsAlphanumeric: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\AcmeEntity">
                <property name="name">
                    <constraint name="NotBlank" />
                    <constraint name="AppBundle\Validator\Constraints\ContainsAlphanumeric" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/AcmeEntity.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use AppBundle\Validator\Constraints\ContainsAlphanumeric;

        class AcmeEntity
        {
            public $name;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new NotBlank());
                $metadata->addPropertyConstraint('name', new ContainsAlphanumeric());
            }
        }

If your constraint contains options, then they should be public properties
on the custom Constraint class you created earlier. These options can be
configured like options on core Symfony constraints.

Constraint Validators with Dependencies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your constraint validator has dependencies, such as a database connection,
it will need to be configured as a service in the Dependency Injection
Container. This service must include the ``validator.constraint_validator``
tag and an ``alias`` attribute:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            validator.unique.your_validator_name:
                class: Fully\Qualified\Validator\Class\Name
                tags:
                    - { name: validator.constraint_validator, alias: alias_name }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <service id="validator.unique.your_validator_name" class="Fully\Qualified\Validator\Class\Name">
            <argument type="service" id="doctrine.orm.default_entity_manager" />
            <tag name="validator.constraint_validator" alias="alias_name" />
        </service>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register('validator.unique.your_validator_name', 'Fully\Qualified\Validator\Class\Name')
            ->addTag('validator.constraint_validator', array('alias' => 'alias_name'));

Your constraint class should now use this alias to reference the appropriate
validator::

    public function validatedBy()
    {
        return 'alias_name';
    }

As mentioned above, Symfony will automatically look for a class named after
the constraint, with ``Validator`` appended. If your constraint validator
is defined as a service, it's important that you override the
``validatedBy()`` method to return the alias used when defining your service,
otherwise Symfony won't use the constraint validator service, and will
instantiate the class instead, without any dependencies injected.

Class Constraint Validator
~~~~~~~~~~~~~~~~~~~~~~~~~~

Beside validating a class property, a constraint can have a class scope by
providing a target in its ``Constraint`` class::

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

With this, the validator ``validate()`` method gets an object as its first argument::

    class ProtocolClassValidator extends ConstraintValidator
    {
        public function validate($protocol, Constraint $constraint)
        {
            if ($protocol->getFoo() != $protocol->getBar()) {
                // If you're using the new 2.5 validation API (you probably are!)
                $this->context->buildViolation($constraint->message)
                    ->atPath('foo')
                    ->addViolation();

                // If you're using the old 2.4 validation API
                /*
                $this->context->addViolationAt(
                    'foo',
                    $constraint->message,
                    array(),
                    null
                );
                */
            }
        }
    }

Note that a class constraint validator is applied to the class itself, and
not to the property:

.. configuration-block::

    .. code-block:: php-annotations

        /**
         * @AcmeAssert\ContainsAlphanumeric
         */
        class AcmeEntity
        {
            // ...
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\AcmeEntity:
            constraints:
                - AppBundle\Validator\Constraints\ContainsAlphanumeric: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <class name="AppBundle\Entity\AcmeEntity">
            <constraint name="AppBundle\Validator\Constraints\ContainsAlphanumeric" />
        </class>
