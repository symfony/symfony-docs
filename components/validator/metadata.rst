Metadata
========

The :class:`Symfony\\Component\\Validator\\Mapping\\ClassMetadata` class
represents and manages all the configured constraints on a given class.

Properties
----------

The Validator component can validate public, protected or private properties.
The following example shows how to validate that the ``$firstName`` property of
the ``Author`` class has at least 3 characters::

    // ...
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Mapping\ClassMetadata;

    class Author
    {
        private string $firstName;

        public static function loadValidatorMetadata(ClassMetadata $metadata): void
        {
            $metadata->addPropertyConstraint('firstName', new Assert\NotBlank());
            $metadata->addPropertyConstraint(
                'firstName',
                new Assert\Length(["min" => 3])
            );
        }
    }

Getters
-------

Constraints can also be applied to the value returned by any public *getter*
method, which are the methods whose names start with ``get``, ``has`` or ``is``.
This feature allows validating your objects dynamically.

Suppose that, for security reasons, you want to validate that a password field
doesn't match the first name of the user. First, create a public method called
``isPasswordSafe()`` to define this custom validation logic::

    public function isPasswordSafe(): bool
    {
        return $this->firstName !== $this->password;
    }

Then, add the Validator component configuration to the class::

    // ...
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Mapping\ClassMetadata;

    class Author
    {
        public static function loadValidatorMetadata(ClassMetadata $metadata): void
        {
            $metadata->addGetterConstraint('passwordSafe', new Assert\IsTrue([
                'message' => 'The password cannot match your first name',
            ]));
        }
    }

Classes
-------

Some constraints allow validating the entire object. For example, the
:doc:`Callback </reference/constraints/Callback>` constraint is a generic
constraint that's applied to the class itself.

Suppose that the class defines a ``validate()`` method to hold its custom
validation logic::

        // ...
        use Symfony\Component\Validator\Context\ExecutionContextInterface;

        public function validate(ExecutionContextInterface $context): void
        {
            // ...
        }

Then, add the Validator component configuration to the class::

    // ...
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Mapping\ClassMetadata;

    class Author
    {
        public static function loadValidatorMetadata(ClassMetadata $metadata): void
        {
            $metadata->addConstraint(new Assert\Callback('validate'));
        }
    }
