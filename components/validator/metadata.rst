.. index::
    single: Validator; Metadata

Metadata
========

The :class:`Symfony\\Component\\Validator\\Mapping\\ClassMetadata` class represents and manages all the configured constraints on a given class.

Properties
----------

Validating class properties is the most basic validation technique. Validation component
allows you to validate private, protected or public properties. The next
listing shows you how to configure the ``$firstName`` property of an ``Author``
class to have at least 3 characters::

    // ...
    use Symfony\Component\Validator\Mapping\ClassMetadata;
    use Symfony\Component\Validator\Constraints as Assert;

    class Author
    {
        private $firstName;

        public static function loadValidatorMetadata(ClassMetadata $metadata)
        {
            $metadata->addPropertyConstraint('firstName', new Assert\NotBlank());
            $metadata->addPropertyConstraint(
                'firstName',
                new Assert\Length(array("min" => 3))
            );
        }
    }

Getters
-------

Constraints can also be applied to the return value of a method. Symfony
allows you to add a constraint to any public method whose name starts with
"get" or "is". In this guide, both of these types of methods are referred
to as "getters".

The benefit of this technique is that it allows you to validate your object
dynamically. For example, suppose you want to make sure that a password field
doesn't match the first name of the user (for security reasons). You can
do this by creating an ``isPasswordLegal`` method, and then asserting that
this method must return ``true``::

    // ...
    use Symfony\Component\Validator\Mapping\ClassMetadata;
    use Symfony\Component\Validator\Constraints as Assert;

    class Author
    {
        public static function loadValidatorMetadata(ClassMetadata $metadata)
        {
            $metadata->addGetterConstraint('passwordLegal', new Assert\True(array(
                'message' => 'The password cannot match your first name',
            )));
        }
    }

Now, create the ``isPasswordLegal()`` method and include the logic you need::

    public function isPasswordLegal()
    {
        return $this->firstName !== $this->password;
    }

.. note::

    The keen-eyed among you will have noticed that the prefix of the getter
    ("get" or "is") is omitted in the mapping. This allows you to move the
    constraint to a property with the same name later (or vice versa) without
    changing your validation logic.

Classes
-------

Some constraints apply to the entire class being validated. For example,
the :doc:`Callback </reference/constraints/Callback>` constraint is a generic
constraint that's applied to the class itself. When that class is validated,
methods specified by that constraint are simply executed so that each can
provide more custom validation.
