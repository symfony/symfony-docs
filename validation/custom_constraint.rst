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

    .. code-block:: php-attributes

        // src/Validator/ContainsAlphanumeric.php
        namespace App\Validator;

        use Symfony\Component\Validator\Constraint;

        #[\Attribute]
        class ContainsAlphanumeric extends Constraint
        {
            public string $message = 'The string "{{ string }}" contains an illegal character: it can only contain letters or numbers.';
            // If the constraint has configuration options, define them as public properties
            public string $mode = 'strict';
        }

Add ``#[\Attribute]`` to the constraint class if you want to
use it as an attribute in other classes.

.. versionadded:: 6.1

    The ``#[HasNamedArguments]`` attribute was introduced in Symfony 6.1.

You can use ``#[HasNamedArguments]`` to make some constraint options required::

    // src/Validator/ContainsAlphanumeric.php
    namespace App\Validator;

    use Symfony\Component\Validator\Attribute\HasNamedArguments;
    use Symfony\Component\Validator\Constraint;

    #[\Attribute]
    class ContainsAlphanumeric extends Constraint
    {
        public string $message = 'The string "{{ string }}" contains an illegal character: it can only contain letters or numbers.';

        #[HasNamedArguments]
        public function __construct(
            public string $mode,
            array $groups = null,
            mixed $payload = null,
        ) {
            parent::__construct([], $groups, $payload);
        }
    }

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
        public function validate($value, Constraint $constraint): void
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

            // access your configuration options like this:
            if ('strict' === $constraint->mode) {
                // ...
            }

            if (!preg_match('/^[a-zA-Z0-9]+$/', $value, $matches)) {
                // the argument must be a string or an object implementing __toString()
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $value)
                    ->addViolation();
            }
        }
    }

Inside ``validate()``, you don't need to return a value. Instead, you add violations
to the validator's ``context`` property and a value will be considered valid
if it causes no violations. The ``buildViolation()`` method takes the error
message as its argument and returns an instance of
:class:`Symfony\\Component\\Validator\\Violation\\ConstraintViolationBuilderInterface`.
The ``addViolation()`` method call finally adds the violation to the context.

Using the new Validator
-----------------------

You can use custom validators like the ones provided by Symfony itself:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/AcmeEntity.php
        namespace App\Entity;

        use App\Validator as AcmeAssert;
        use Symfony\Component\Validator\Constraints as Assert;

        class AcmeEntity
        {
            // ...

            #[Assert\NotBlank]
            #[AcmeAssert\ContainsAlphanumeric(mode: 'loose')]
            protected string $name;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                name:
                    - NotBlank: ~
                    - App\Validator\ContainsAlphanumeric:
                        mode: 'loose'

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="name">
                    <constraint name="NotBlank"/>
                    <constraint name="App\Validator\ContainsAlphanumeric">
                        <option name="mode">loose</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use App\Validator\ContainsAlphanumeric;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            protected string $name = '';

            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('name', new NotBlank());
                $metadata->addPropertyConstraint('name', new ContainsAlphanumeric(['mode' => 'loose']));
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

In case you need to consistently apply a common set of constraints
across your application, you can extend the :doc:`Compound constraint </reference/constraints/Compound>`.

Class Constraint Validator
~~~~~~~~~~~~~~~~~~~~~~~~~~

Besides validating a single property, a constraint can have an entire class
as its scope.

For instance, imagine you also have a ``PaymentReceipt`` entity and you
need to make sure the email of the receipt payload matches the user's
email. First, create a constraint and override the ``getTargets()`` method::

    // src/Validator/ConfirmedPaymentReceipt.php
    namespace App\Validator;

    use Symfony\Component\Validator\Constraint;

    #[\Attribute]
    class ConfirmedPaymentReceipt extends Constraint
    {
        public string $userDoesNotMatchMessage = 'User\'s e-mail address does not match that of the receipt';

        public function getTargets(): string
        {
            return self::CLASS_CONSTRAINT;
        }
    }

Now, the constraint validator will get an object as the first argument to
``validate()``::

    // src/Validator/ConfirmedPaymentReceiptValidator.php
    namespace App\Validator;

    use Symfony\Component\Validator\Constraint;
    use Symfony\Component\Validator\ConstraintValidator;
    use Symfony\Component\Validator\Exception\UnexpectedValueException;

    class ConfirmedPaymentReceiptValidator extends ConstraintValidator
    {
        /**
         * @param PaymentReceipt $receipt
         */
        public function validate($receipt, Constraint $constraint): void
        {
            if (!$receipt instanceof PaymentReceipt) {
                throw new UnexpectedValueException($receipt, PaymentReceipt::class);
            }

            if (!$constraint instanceof ConfirmedPaymentReceipt) {
                throw new UnexpectedValueException($constraint, ConfirmedPaymentReceipt::class);
            }

            $receiptEmail = $receipt->getPayload()['email'] ?? null;
            $userEmail = $receipt->getUser()->getEmail();

            if ($userEmail !== $receiptEmail) {
                $this->context
                    ->buildViolation($constraint->userDoesNotMatchMessage)
                    ->atPath('user.email')
                    ->addViolation();
            }
        }
    }

.. tip::

    The ``atPath()`` method defines the property with which the validation error is
    associated. Use any :doc:`valid PropertyAccess syntax </components/property_access>`
    to define that property.

A class constraint validator must be applied to the class itself:

.. configuration-block::

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
        App\Entity\PaymentReceipt:
            constraints:
                - App\Validator\ConfirmedPaymentReceipt: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\PaymentReceipt">
                <constraint name="App\Validator\ConfirmedPaymentReceipt"/>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/PaymentReceipt.php
        namespace App\Entity;

        use App\Validator\ConfirmedPaymentReceipt;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class PaymentReceipt
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addConstraint(new ConfirmedPaymentReceipt());
            }
        }

Testing Custom Constraints
--------------------------

Use the :class:`Symfony\\Component\\Validator\\Test\\ConstraintValidatorTestCase``
class to simplify writing unit tests for your custom constraints::

    // tests/Validator/ContainsAlphanumericValidatorTest.php
    namespace App\Tests\Validator;

    use App\Validator\ContainsAlphanumeric;
    use App\Validator\ContainsAlphanumericValidator;
    use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

    class ContainsAlphanumericValidatorTest extends ConstraintValidatorTestCase
    {
        protected function createValidator()
        {
            return new ContainsAlphanumericValidator();
        }

        public function testNullIsValid()
        {
            $this->validator->validate(null, new ContainsAlphanumeric());

            $this->assertNoViolation();
        }

        /**
         * @dataProvider provideInvalidConstraints
         */
        public function testTrueIsInvalid(ContainsAlphanumeric $constraint)
        {
            $this->validator->validate('...', $constraint);

            $this->buildViolation('myMessage')
                ->setParameter('{{ string }}', '...')
                ->assertRaised();
        }

        public function provideInvalidConstraints(): iterable
        {
            yield [new ContainsAlphanumeric(message: 'myMessage')];
            // ...
        }
    }
