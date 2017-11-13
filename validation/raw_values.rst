.. index::
    single: Validation; Validating raw values

How to Validate Raw Values (Scalar Values and Arrays)
=====================================================

Usually you will be validating entire objects. But sometimes, you just want
to validate a simple value - like to verify that a string is a valid email
address. This is actually pretty easy to do. From inside a controller, it
looks like this::

    // ...
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Validator\ValidatorInterface;

    // ...
    public function addEmailAction($email, ValidatorInterface $validator)
    {
        $emailConstraint = new Assert\Email();
        // all constraint "options" can be set this way
        $emailConstraint->message = 'Invalid email address';

        // use the validator to validate the value
        $errorList = $validator->validate(
            $email,
            $emailConstraint
        );

        if (0 === count($errorList)) {
            // ... this IS a valid email address, do something
        } else {
            // this is *not* a valid email address
            $errorMessage = $errorList[0]->getMessage();

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

    use Symfony\Component\Validator\Validation;
    use Symfony\Component\Validator\Constraints as Assert;

    $validator = Validation::createValidator();

    $constraint = new Assert\Collection(array(
        // the keys correspond to the keys in the input array
        'name' => new Assert\Collection(array(
          'first_name' => new Assert\Length(array('min' => 101)),
          'last_name' => new Assert\Length(array('min' => 1)),
        )),
        'email' => new Assert\Email(),
        'simple' => new Assert\Length(array('min' => 102)),
        'gender' => new Assert\Choice(array(3, 4)),
        'file' => new Assert\File(),
        'password' => new Assert\Length(array('min' => 60)),
    ));

    $violations = $validator->validate($input, $constraint);

The ``validate()`` method returns a :class:`Symfony\\Component\\Validator\\ConstraintViolationList`
object, which acts just like an array of errors. Each error in the collection
is a :class:`Symfony\\Component\\Validator\\ConstraintViolation` object,
which holds the error message on its ``getMessage()`` method.
