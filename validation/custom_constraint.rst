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

First you need to create a Constraint class and extend :class:`Symfony\\Component\\Validator\\Constraint`::

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

.. note::

    The ``@Annotation`` annotation is necessary for this new constraint
    to make it available for use in classes via annotations.
    Options for your constraint are represented as public properties on the
    constraint class.

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

.. versionadded:: 4.4

    The feature to allow passing an object as the ``buildViolation()`` argument
    was introduced in Symfony 4.4.

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

Class Constraint Validator
~~~~~~~~~~~~~~~~~~~~~~~~~~

Besides validating a single property, a constraint can have an entire class
as its scope. Consider the following classes, that describe the receipt of some payment::

    // src/AppBundle/Model/PaymentReceipt.php
    class PaymentReceipt
    {
        /**
         * @var User
         */
        private $user;

        /**
         * @var array
         */
        private $payload;

        public function __construct(User $user, array $payload)
        {
            $this->user = $user;
            $this->payload = $payload;
        }

        public function getUser(): User
        {
            return $this->user;
        }

        public function getPayload(): array
        {
            return $this->payload;
        }
    }

    // src/AppBundle/Model/User.php

    class User
    {
        /**
         * @var string
         */
        private $email;

        public function __construct($email)
        {
            $this->email = $email;
        }

        public function getEmail(): string
        {
            return $this->email;
        }
    }

As an example you're going to check if the email in receipt payload matches the user email.
To validate the receipt, it is required to create the constraint first.
You only need to add the ``getTargets()`` method to the ``Constraint`` class::

    // src/AppBundle/Validator/Constraints/ConfirmedPaymentReceipt.php
    namespace AppBundle\Validator\Constraints;

    use Symfony\Component\Validator\Constraint;

    /**
     * @Annotation
     */
    class ConfirmedPaymentReceipt extends Constraint
    {
        public $userDoesntMatchMessage = 'User email does not match the receipt email';

        public function getTargets()
        {
            return self::CLASS_CONSTRAINT;
        }
    }

With this, the validator's ``validate()`` method gets an object as its first argument::

    // src/AppBundle/Validator/Constraints/ConfirmedPaymentReceiptValidator.php
    namespace AppBundle\Validator\Constraints;

    use Symfony\Component\Validator\Constraint;
    use Symfony\Component\Validator\ConstraintValidator;
    use Symfony\Component\Validator\Exception\UnexpectedValueException;

    class ConfirmedPaymentReceiptValidator extends ConstraintValidator
    {
        /**
         * @param PaymentReceipt $receipt
         * @param Constraint|ConfirmedPaymentReceipt $constraint
         */
        public function validate($receipt, Constraint $constraint)
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
                    ->buildViolation($constraint->userDoesntMatchMessage)
                    ->atPath('user.email')
                    ->addViolation();
            }
        }
    }

.. tip::

    The ``atPath()`` method defines the property with which the validation error is
    associated. Use any :doc:`valid PropertyAccess syntax </components/property_access>`
    to define that property.

A class constraint validator is applied to the class itself, and
not to the property:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/AcmeEntity.php
        namespace App\Entity;

        use App\Validator as AcmeAssert;

        /**
         * @AppAssert\ConfirmedPaymentReceipt
         */
        class PaymentReceipt
        {
            // ...
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Model\PaymentReceipt:
            constraints:
                - AppBundle\Validator\Constraints\ConfirmedPaymentReceipt: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <class name="AppBundle\Model\PaymentReceipt">
            <constraint name="AppBundle\Validator\Constraints\ConfirmedPaymentReceipt"/>
        </class>

    .. code-block:: php

        // src/AppBundle/Model/PaymentReceipt.php
        use AppBundle\Validator\Constraints\ConfirmedPaymentReceipt;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class PaymentReceipt
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new ConfirmedPaymentReceipt());
            }
        }

<<<<<<< HEAD
Testing Custom Constraints
--------------------------

Use the ``ConstraintValidatorTestCase`` utility to simplify the creation of
unit tests for your custom constraints::

    // ...
    use App\Validator\ContainsAlphanumeric;
    use App\Validator\ContainsAlphanumericValidator;

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

How to Unit Test your Validator
-------------------------------

To create a unit test for you custom validator, your test case class should
extend the ``ConstraintValidatorTestCase`` class and implement the ``createValidator()`` method::

    protected function createValidator()
    {
        return new ContainsAlphanumericValidator();
    }

After that you can add any test cases you need to cover the validation logic::

    use AppBundle\Validator\Constraints\ContainsAlphanumeric;
    use AppBundle\Validator\Constraints\ContainsAlphanumericValidator;
    use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

    class ContainsAlphanumericValidatorTest extends ConstraintValidatorTestCase
    {
        protected function createValidator()
        {
            return new ContainsAlphanumericValidator();
        }

        /**
         * @dataProvider getValidStrings
         */
        public function testValidStrings($string)
        {
            $this->validator->validate($string, new ContainsAlphanumeric());

            $this->assertNoViolation();
        }

        public function getValidStrings()
        {
            return [
                ['Fabien'],
                ['SymfonyIsGreat'],
                ['HelloWorld123'],
            ];
        }

        /**
         * @dataProvider getInvalidStrings
         */
        public function testInvalidStrings($string)
        {
            $constraint = new ContainsAlphanumeric([
                'message' => 'myMessage',
            ]);

            $this->validator->validate($string, $constraint);

            $this->buildViolation('myMessage')
                ->setParameter('{{ string }}', $string)
                ->assertRaised();
        }

        public function getInvalidStrings()
        {
            return [
                ['example_'],
                ['@$^&'],
                ['hello-world'],
                ['<body>'],
            ];
        }
    }

You can also use the ``ConstraintValidatorTestCase`` class for creating test cases for class constraints::

    use AppBundle\Validator\Constraints\ConfirmedPaymentReceipt;
    use AppBundle\Validator\Constraints\ConfirmedPaymentReceiptValidator;
    use Symfony\Component\Validator\Exception\UnexpectedValueException;
    use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

    class ConfirmedPaymentReceiptValidatorTest extends ConstraintValidatorTestCase
    {
        protected function createValidator()
        {
            return new ConfirmedPaymentReceiptValidator();
        }

        public function testValidReceipt()
        {
            $receipt = new PaymentReceipt(new User('foo@bar.com'), ['email' => 'foo@bar.com', 'data' => 'baz']);
            $this->validator->validate($receipt, new ConfirmedPaymentReceipt());

            $this->assertNoViolation();
        }

        /**
         * @dataProvider getInvalidReceipts
         */
        public function testInvalidReceipt($paymentReceipt)
        {
            $this->validator->validate(
                $paymentReceipt,
                new ConfirmedPaymentReceipt(['userDoesntMatchMessage' => 'myMessage'])
            );

            $this->buildViolation('myMessage')
                ->atPath('property.path.user.email')
                ->assertRaised();
        }

        public function getInvalidReceipts()
        {
            return [
                [new PaymentReceipt(new User('foo@bar.com'), [])],
                [new PaymentReceipt(new User('foo@bar.com'), ['email' => 'baz@foo.com'])],
            ];
        }

        /**
         * @dataProvider getUnexpectedArguments
         */
        public function testUnexpectedArguments($value, $constraint)
        {
            self::expectException(UnexpectedValueException::class);

            $this->validator->validate($value, $constraint);
        }

        public function getUnexpectedArguments()
        {
            return [
                [new \stdClass(), new ConfirmedPaymentReceipt()],
                [new PaymentReceipt(new User('foo@bar.com'), []), new Unique()],
            ];
        }
    }
