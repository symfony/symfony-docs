.. index::
   single: Validation; Custom constraints

How to Create a Custom Validation Constraint
============================================

You can create a custom constraint by extending the base constraint class,
:class:`Symfony\\Component\\Validator\\Constraint`. As an example you're
going to create a basic validator that checks if a string contains only
alphanumeric characters.

Creating the Constraint Class
-----------------------------

First you need to create a Constraint class and extend :class:`Symfony\\Component\\Validator\\Constraint`:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Validator/ContainsAlphanumeric.php
        namespace App\Validator;

        use Symfony\Component\Validator\Constraint;

        /**
         * @Annotation
         */
        class ContainsAlphanumeric extends Constraint
        {
            public $message = 'The string "{{ string }}" contains an illegal character: it can only contain letters or numbers.';
        }

    .. code-block:: php-attributes

        // src/Validator/ContainsAlphanumeric.php
        namespace App\Validator;

        use Symfony\Component\Validator\Constraint;

        #[\Attribute]
        class ContainsAlphanumeric extends Constraint
        {
            public $message = 'The string "{{ string }}" contains an illegal character: it can only contain letters or numbers.';
        }

Add ``@Annotation`` or ``#[\Attribute]`` to the constraint class if you want to
use it as an annotation/attribute in other classes. If the constraint has
configuration options, define them as public properties on the constraint class.

.. versionadded:: 5.2

    The ability to use PHP attributes to configure constraints was introduced in
    Symfony 5.2. Prior to this, Doctrine Annotations were the only way to
    annotate constraints.

Creating the Validator itself
-----------------------------

As you can see, a constraint class is fairly minimal. The actual validation is
performed by another "constraint validator" class. The constraint validator
class is specified by the constraint's ``validatedBy()`` method, which
has this default logic::

    // in the base Symfony\Component\Validator\Constraint class
    public function validatedBy()
    {
        return static::class.'Validator';
    }

In other words, if you create a custom ``Constraint`` (e.g. ``MyConstraint``),
Symfony will automatically look for another class, ``MyConstraintValidator``
when actually performing the validation.

The validator class only has one required method ``validate()``::

    // src/Validator/ContainsAlphanumericValidator.php
    namespace App\Validator;

    use Symfony\Component\Validator\Constraint;
    use Symfony\Component\Validator\ConstraintValidator;
    use Symfony\Component\Validator\Exception\UnexpectedTypeException;
    use Symfony\Component\Validator\Exception\UnexpectedValueException;

    class ContainsAlphanumericValidator extends ConstraintValidator
    {
        public function validate($value, Constraint $constraint)
        {
            if (!$constraint instanceof ContainsAlphanumeric) {
                throw new UnexpectedTypeException($constraint, ContainsAlphanumeric::class);
            }

            // custom constraints should ignore null and empty values to allow
            // other constraints (NotBlank, NotNull, etc.) to take care of that
            if (null === $value || '' === $value) {
                return;
            }

            if (!is_string($value)) {
                // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
                throw new UnexpectedValueException($value, 'string');

                // separate multiple types using pipes
                // throw new UnexpectedValueException($value, 'string|int');
            }

            if (!preg_match('/^[a-zA-Z0-9]+$/', $value, $matches)) {
                // the argument must be a string or an object implementing __toString()
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $value)
                    ->addViolation();
            }
        }
    }

Inside ``validate``, you don't need to return a value. Instead, you add violations
to the validator's ``context`` property and a value will be considered valid
if it causes no violations. The ``buildViolation()`` method takes the error
message as its argument and returns an instance of
:class:`Symfony\\Component\\Validator\\Violation\\ConstraintViolationBuilderInterface`.
The ``addViolation()`` method call finally adds the violation to the context.

Using the new Validator
-----------------------

You can use custom validators like the ones provided by Symfony itself:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/AcmeEntity.php
        namespace App\Entity;

        use App\Validator as AcmeAssert;
        use Symfony\Component\Validator\Constraints as Assert;

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

    .. code-block:: php-attributes

        // src/Entity/AcmeEntity.php
        namespace App\Entity;

        use App\Validator as AcmeAssert;
        use Symfony\Component\Validator\Constraints as Assert;

        class AcmeEntity
        {
            // ...

            #[Assert\NotBlank]
            #[AcmeAssert\ContainsAlphanumeric]
            protected $name;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\AcmeEntity:
            properties:
                name:
                    - NotBlank: ~
                    - App\Validator\ContainsAlphanumeric: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\AcmeEntity">
                <property name="name">
                    <constraint name="NotBlank"/>
                    <constraint name="App\Validator\ContainsAlphanumeric"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/AcmeEntity.php
        namespace App\Entity;

        use App\Validator\ContainsAlphanumeric;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

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

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
then your validator is already registered as a service and :doc:`tagged </service_container/tags>`
with the necessary ``validator.constraint_validator``. This means you can
:ref:`inject services or configuration <services-constructor-injection>` like any other service.

Create a Reusable Set of Constraints
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In case you need to apply some common set of constraints in different places
consistently across your application, you can extend the :doc:`Compound constraint </reference/constraints/Compound>`.

.. versionadded:: 5.1

    The ``Compound`` constraint was introduced in Symfony 5.1.

Class Constraint Validator
~~~~~~~~~~~~~~~~~~~~~~~~~~

Besides validating a single property, a constraint can have an entire class
as its scope. You only need to add this to the ``Constraint`` class::

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

With this, the validator's ``validate()`` method gets an object as its first argument::

    class ProtocolClassValidator extends ConstraintValidator
    {
        public function validate($protocol, Constraint $constraint)
        {
            if ($protocol->getFoo() != $protocol->getBar()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('foo')
                    ->addViolation();
            }
        }
    }

.. tip::

    The ``atPath()`` method defines the property which the validation error is
    associated to. Use any :doc:`valid PropertyAccess syntax </components/property_access>`
    to define that property.

A class constraint validator is applied to the class itself, and
not to the property:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/AcmeEntity.php
        namespace App\Entity;

        use App\Validator as AcmeAssert;
        
        /**
         * @AcmeAssert\ProtocolClass
         */
        class AcmeEntity
        {
            // ...
        }

    .. code-block:: php-attributes

        // src/Entity/AcmeEntity.php
        namespace App\Entity;

        use App\Validator as AcmeAssert;

        #[AcmeAssert\ProtocolClass]
        class AcmeEntity
        {
            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\AcmeEntity:
            constraints:
                - App\Validator\ProtocolClass: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <class name="App\Entity\AcmeEntity">
            <constraint name="App\Validator\ProtocolClass"/>
        </class>

    .. code-block:: php

        // src/Entity/AcmeEntity.php
        namespace App\Entity;

        use App\Validator\ProtocolClass;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class AcmeEntity
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new ProtocolClass());
            }
        }
