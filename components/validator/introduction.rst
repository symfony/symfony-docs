.. index::
   single: Validator
   single: Components; Validator

The Validator Component
=======================

    The Validator component provides tools to validate values following the
    `JSR-303 Bean Validation specification`_.

Installation
------------

You can install the component in two different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/validator`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/Validator).

.. include:: /components/require_autoload.rst.inc

Usage
-----

The Validator component behavior is based on two concepts:

* Contraints, which define the rules to be validated;
* Validators, which are the classes that contain the actual validation logic.

The following example shows how to validate that a string is at least 10
characters long::

    use Symfony\Component\Validator\Validation;
    use Symfony\Component\Validator\Constraints\Length;
    use Symfony\Component\Validator\Constraints\NotBlank;

    $validator = Validation::createValidator();
    $violations = $validator->validate('Bernhard', array(
        new Length(array('min' => 10)),
        new NotBlank(),
    ));

    if (0 !== count($violations)) {
        // there are errors, now you can show them
        foreach ($violations as $violation) {
            echo $violation->getMessage().'<br>';
        }
    }

Retrieving a Validator Instance
-------------------------------

The :class:`Symfony\\Component\\Validator\\Validator` class is the main access
point of the Validator component. To create a new instance of this class, it's
recommended to use the :class:`Symfony\\Component\\Validator\\Validation` class::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidator();

This ``$validator`` object can validate simple variables such as strings, numbers
and arrays, but it can't validate objects. To do so, configure the
``Validator`` class as explained in the next sections:

* :doc:`/components/validator/resources`
* :doc:`/components/validator/metadata`

.. _`JSR-303 Bean Validation specification`: http://jcp.org/en/jsr/detail?id=303
.. _Packagist: https://packagist.org/packages/symfony/validator
