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

    // ...
    public function addEmailAction($email)
    {
        $emailConstraint = new Assert\Email();
        // all constraint "options" can be set this way
        $emailConstraint->message = 'Invalid email address';

        // use the validator to validate the value
        // If you're using the new 2.5 validation API (you probably are!)
        $errorList = $this->get('validator')->validate(
            $email,
            $emailConstraint
        );

        // If you're using the old 2.4 validation API
        /*
        $errorList = $this->get('validator')->validateValue(
            $email,
            $emailConstraint
        );
        */

        if (0 === count($errorList)) {
            // ... this IS a valid email address, do something
        } else {
            // this is *not* a valid email address
            $errorMessage = $errorList[0]->getMessage();

            // ... do something with the error
        }

        // ...
    }

By calling ``validate`` on the validator, you can pass in a raw value and
the constraint object that you want to validate that value against. A full
list of the available constraints - as well as the full class name for each
constraint - is available in the :doc:`constraints reference </reference/constraints>`
section.

The ``validate`` method returns a :class:`Symfony\\Component\\Validator\\ConstraintViolationList`
object, which acts just like an array of errors. Each error in the collection
is a :class:`Symfony\\Component\\Validator\\ConstraintViolation` object,
which holds the error message on its ``getMessage`` method.
