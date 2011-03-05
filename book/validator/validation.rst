Constraint Validation
=====================

Objects with constraints are validated by the
:class:`Symfony\\Component\\Validator\\Validator` class. If you use Symfony2,
this class is already registered as a service in the Dependency Injection
Container. To enable the service, add the following lines to your
configuration:

.. code-block:: yaml

    # hello/config/config.yml
    framework:
        validation:
            enabled: true

Then you can get the validator from the container and start validating your
objects:

.. code-block:: php

    $validator = $container->get('validator');
    $author = new Author();

    print $validator->validate($author);

The ``validate()`` method returns a
:class:`Symfony\\Component\\Validator\\ConstraintViolationList` object. This
object behaves exactly like an array. You can iterate over it and you can even
print it in a nicely formatted manner. Every element of the list corresponds
to one validation error. If the list is empty, it's time to dance, because
then validation succeeded.

The above call will output something similar to this:

.. code-block:: text

    Sensio\HelloBundle\Author.firstName:
        This value should not be blank
    Sensio\HelloBundle\Author.lastName:
        This value should not be blank
    Sensio\HelloBundle\Author.fullName:
        This value is too short. It should have 10 characters or more

If you fill the object with correct values the validation errors disappear.

Learn more from the Cookbook
----------------------------
