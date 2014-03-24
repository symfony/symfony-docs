.. index::
   single: Validator
   single: Components; Validator

The Validator Component
=======================

    The Validator component provides tools to validate values following the
    `JSR-303 Bean Validation specification`_.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/validator`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/Validator).

Usage
-----

The Validator component allows you to use very advanced validation rules, but
it is also really easy to do easy validation tasks. For instance, if you want
to validate a string is at least 10 character long, the only code you need is::

    use Symfony\Component\Validator\Validation;
    use Symfony\Component\Validator\Constraints\Length;

    $validator = Validation::createValidator();

    $violations = $validator->validateValue('Bernhard', new Length(array('min' => 10)));

    if (0 !== count($violations)) {
        // there are errors, now you can show them
        foreach ($violations as $violation) {
            echo $violation->getMessage().'<br>';
        }
    }

Retrieving a Validator Instance
-------------------------------

The :class:`Symfony\\Component\\Validator\\Validator` class is the main access
point of the Validator component. To create a new instance of this class, it
is recommend to use the :class:`Symfony\\Component\Validator\Validation`
class.

You can get a very basic ``Validator`` by calling 
:method:`Validation::createValidator() <Symfony\\Component\\Validator\\Validation::createValidator>`::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidator();

The created validator can be used to validate strings, array, numbers, but it
can't validate classes. To be able to do that, you have to configure the ``Validator``
class. To do that, you can use the :class:`Symfony\\Component\\Validator\\ValidatorBuilder`.
This class can be retrieved by using the
:method:`Validation::createValidatorBuilder() <Symfony\\Component\\Validator\\Validation::createValidatorBuilder>`
method::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        // ... build a custom instance of the Validator
        ->getValidator();

What things you can configure will be documented in the following sections.

Sections
--------

* :doc:`/components/validator/resources`
* :doc:`/components/validator/metadata`
* :doc:`/components/validator/validating_values`

.. _`JSR-303 Bean Validation specification`: http://jcp.org/en/jsr/detail?id=303
