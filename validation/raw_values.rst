How to Validate Raw Values (Scalar Values and Arrays)
=====================================================

Usually you will be validating entire objects. But sometimes, you want
to validate a simple value - like to verify that a string is a valid email
address. From inside a controller, it looks like this::

    // ...
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Validator\ValidatorInterface;

    // ...
    public function addEmail($email, ValidatorInterface $validator)
    {
        $emailConstraint = new Assert\Email();
        // all constraint "options" can be set this way
        $emailConstraint->message = 'Invalid email address';

        // use the validator to validate the value
        $errors = $validator->validate(
            $email,
            $emailConstraint
        );

        if (!$errors->count()) {
            // ... this IS a valid email address, do something
        } else {
            // this is *not* a valid email address
            $errorMessage = $errors[0]->getMessage();

            // ... do something with the error
        }

        // ...
    }

By calling ``validate()`` on the validator, you can pass in a raw value and
the constraint object that you want to validate that value against. A full
list of the available constraints - as well as the full class name for each
constraint - is available in the :doc:`constraints reference </reference/constraints>`
section.

Validation of arrays is possible using the ``Collection`` constraint::

    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidator();

    $input = [
        'name' => [
            'first_name' => 'Fabien',
            'last_name' => 'Potencier',
        ],
        'email' => 'test@email.tld',
        'simple' => 'hello',
        'eye_color' => 3,
        'file' => null,
        'password' => 'test',
        'tags' => [
            [
                'slug' => 'symfony_doc',
                'label' => 'symfony doc',
            ],
        ],
    ];

    $groups = new Assert\GroupSequence(['Default', 'custom']);

    $constraint = new Assert\Collection([
        // the keys correspond to the keys in the input array
        'name' => new Assert\Collection([
            'first_name' => new Assert\Length(['min' => 101]),
            'last_name' => new Assert\Length(['min' => 1]),
        ]),
        'email' => new Assert\Email(),
        'simple' => new Assert\Length(['min' => 102]),
        'eye_color' => new Assert\Choice([3, 4]),
        'file' => new Assert\File(),
        'password' => new Assert\Length(['min' => 60]),
        'tags' => new Assert\Optional([
            new Assert\Type('array'),
            new Assert\Count(['min' => 1]),
            new Assert\All([
                new Assert\Collection([
                    'slug' => [
                        new Assert\NotBlank(),
                        new Assert\Type(['type' => 'string']),
                    ],
                    'label' => [
                        new Assert\NotBlank(),
                    ],
                ]),
                new CustomUniqueTagValidator(['groups' => 'custom']),
            ]),
        ]),
    ]);

    $violations = $validator->validate($input, $constraint, $groups);

The ``validate()`` method returns a :class:`Symfony\\Component\\Validator\\ConstraintViolationList`
object, which acts like an array of errors. Each error in the collection
is a :class:`Symfony\\Component\\Validator\\ConstraintViolation` object,
which holds the error message on its ``getMessage()`` method.

.. note::

    When using groups with the
    :doc:`Collection </reference/constraints/Collection>` constraint, be sure to
    use the ``Optional`` constraint when appropriate as explained in its
    reference documentation.
