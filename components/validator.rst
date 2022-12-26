.. index::
   single: Validator
   single: Components; Validator

The Validator Component
=======================

    The Validator component provides tools to validate values following the
    `JSR-303 Bean Validation specification`_.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/validator

.. include:: /components/require_autoload.rst.inc

Usage
-----

.. seealso::

    This article explains how to use the Validator features as an independent
    component in any PHP application. Read the :doc:`/validation` article to
    learn about how to validate data and entities in Symfony applications.

The Validator component behavior is based on two concepts:

* Constraints, which define the rules to be validated;
* Validators, which are the classes that contain the actual validation logic.

The following example shows how to validate that a string is at least 10
characters long::

    use Symfony\Component\Validator\Constraints\Length;
    use Symfony\Component\Validator\Constraints\NotBlank;
    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidator();
    $violations = $validator->validate('Bernhard', [
        new Length(['min' => 10]),
        new NotBlank(),
    ]);

    if (0 !== count($violations)) {
        // there are errors, now you can show them
        foreach ($violations as $violation) {
            echo $violation->getMessage().'<br>';
        }
    }

The  ``validate()`` method returns the list of violations as an object that
implements :class:`Symfony\\Component\\Validator\\ConstraintViolationListInterface`.
If you have lots of validation errors, you can filter them by error code::

    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

    $violations = $validator->validate(/* ... */);
    if (0 !== count($violations->findByCodes(UniqueEntity::NOT_UNIQUE_ERROR))) {
        // handle this specific error (display some message, send an email, etc.)
    }

Retrieving a Validator Instance
-------------------------------

The Validator object (that implements :class:`Symfony\\Component\\Validator\\Validator\\ValidatorInterface`) is the main access
point of the Validator component. To create a new instance of it, it's
recommended to use the :class:`Symfony\\Component\\Validator\\Validation` class::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidator();

This ``$validator`` object can validate simple variables such as strings, numbers
and arrays, but it can't validate objects. To do so, configure the
``Validator`` as explained in the next sections.

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /components/validator/*
    /validation
    /validation/*

.. _`JSR-303 Bean Validation specification`: https://jcp.org/en/jsr/detail?id=303
