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
it is also really easy to do very minor validation. For instance, if you want
to validate a string against a specific length, the only code you need is::

    use Symfony\Component\Validator\Validation;
    use Symfony\Component\Validator\Constraints\Length;

    $validator = Validation::createValidator();

    $violations = $validator->validateValue('Bernhard', new Length(array('min' => 10)));

    if (0 !== count($violations)) {
        // there are errors, let's show them
        foreach ($violations as $violation) {
            echo $violation->getMessage().'<br>';
        }
    }

Sections
--------

* :doc:`/components/validator/configuration`

.. _`JSR-303 Bean Validation specification`: http://jcp.org/en/jsr/detail?id=303
