Callback
========

Calls methods during validation on the object. These methods can then perform
any type of validation and assign errors where needed:

.. code-block:: yaml

    Acme\DemoBundle\Entity\Author:
        constraints:
            - Callback:
                methods:   [isAuthorValid]

Usage
-----

The callback method is passed a special ``ExecutionContext`` object::

    use Symfony\Component\Validator\ExecutionContext;
    
    private $firstName;
    
    public function isAuthorValid(ExecutionContext $context)
    {
        // somehow you get an array of "fake names"
        $fakeNames = array();
        
        // check if the name is actually a fake name
        if (in_array($this->getFirstName(), $fakeNames)) {
            $property_path = $context->getPropertyPath() . '.firstName';
            $context->setPropertyPath($property_path);
            $context->addViolation('This name sounds totally fake', array(), null);
        }
    }

Options
-------

* ``methods``: The method names that should be executed as callbacks.
* ``message``: The error message if the validation fails
