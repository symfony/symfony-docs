.. index::
   single: Validation; Custom constraints

How to create a Custom Validation Constraint
============================================

You can create a custom constraint by extending the base constraint class,
:class:`Symfony\\Component\\Validator\\Constraint`.
As an example you're going to create a simple validator that checks if a string
contains only alphanumeric characters.

Creating Constraint class
-------------------------

First you need to create a Constraint class and extend :class:`Symfony\\Component\\Validator\\Constraint`::

    // src/Acme/DemoBundle/Validator/Constraints/ContainsAlphanumeric.php
    namespace Acme\DemoBundle\Validator\Constraints;

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
Symfony2 will automatically look for another class, ``MyConstraintValidator``
when actually performing the validation.

The validator class is also simple, and only has one required method ``validate()``::

    // src/Acme/DemoBundle/Validator/Constraints/ContainsAlphanumericValidator.php
    namespace Acme\DemoBundle\Validator\Constraints;

    use Symfony\Component\Validator\Constraint;
    use Symfony\Component\Validator\ConstraintValidator;

    class ContainsAlphanumericValidator extends ConstraintValidator
    {
        public function validate($value, Constraint $constraint)
        {
            if (!preg_match('/^[a-zA-Za0-9]+$/', $value, $matches)) {
                $this->context->addViolation(
                    $constraint->message,
                    array('%string%' => $value)
                );
            }
        }
    }

.. note::

    The ``validate`` method does not return a value; instead, it adds violations
    to the validator's ``context`` property with an ``addViolation`` method
    call if there are validation failures. Therefore, a value could be considered
    as being valid if it causes no violations to be added to the context.
    The first parameter of the ``addViolation`` call is the error message to
    use for that violation.

Using the new Validator
-----------------------

Using custom validators is very easy, just as the ones provided by Symfony2 itself:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\DemoBundle\Entity\AcmeEntity:
            properties:
                name:
                    - NotBlank: ~
                    - Acme\DemoBundle\Validator\Constraints\ContainsAlphanumeric: ~

    .. code-block:: php-annotations

        // src/Acme/DemoBundle/Entity/AcmeEntity.php
        use Symfony\Component\Validator\Constraints as Assert;
        use Acme\DemoBundle\Validator\Constraints as AcmeAssert;

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

    .. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\DemoBundle\Entity\AcmeEntity">
                <property name="name">
                    <constraint name="NotBlank" />
                    <constraint name="Acme\DemoBundle\Validator\Constraints\ContainsAlphanumeric" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/DemoBundle/Entity/AcmeEntity.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Acme\DemoBundle\Validator\Constraints\ContainsAlphanumeric;

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
it will need to be configured as a service in the dependency injection
container. This service must include the ``validator.constraint_validator``
tag and an ``alias`` attribute:

.. configuration-block::

    .. code-block:: yaml

        services:
            validator.unique.your_validator_name:
                class: Fully\Qualified\Validator\Class\Name
                tags:
                    - { name: validator.constraint_validator, alias: alias_name }

    .. code-block:: xml

        <service id="validator.unique.your_validator_name" class="Fully\Qualified\Validator\Class\Name">
            <argument type="service" id="doctrine.orm.default_entity_manager" />
            <tag name="validator.constraint_validator" alias="alias_name" />
        </service>

    .. code-block:: php

        $container
            ->register('validator.unique.your_validator_name', 'Fully\Qualified\Validator\Class\Name')
            ->addTag('validator.constraint_validator', array('alias' => 'alias_name'));

Your constraint class should now use this alias to reference the appropriate
validator::

    public function validatedBy()
    {
        return 'alias_name';
    }

As mentioned above, Symfony2 will automatically look for a class named after
the constraint, with ``Validator`` appended. If your constraint validator
is defined as a service, it's important that you override the
``validatedBy()`` method to return the alias used when defining your service,
otherwise Symfony2 won't use the constraint validator service, and will
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
                $this->context->addViolationAt(
                    'foo',
                    $constraint->message,
                    array(),
                    null
                );
            }
        }
    }

Note that a class constraint validator is applied to the class itself, and
not to the property:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\DemoBundle\Entity\AcmeEntity:
            constraints:
                - Acme\DemoBundle\Validator\Constraints\ContainsAlphanumeric: ~

    .. code-block:: php-annotations

        /**
         * @AcmeAssert\ContainsAlphanumeric
         */
        class AcmeEntity
        {
            // ...
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\DemoBundle\Entity\AcmeEntity">
            <constraint name="Acme\DemoBundle\Validator\Constraints\ContainsAlphanumeric" />
        </class>
