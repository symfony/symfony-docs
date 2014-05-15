.. index::
    single: Forms; Custom Type Guesser

Creating a custom Type Guesser
==============================

The Form component can guess the type and some options of a form field by
using type guessers. The component already includes a type guesser using the
assertions of the Validation component, but you can also add your own custom
type guessers.

.. sidebar:: Form Type Guessers in the Bridges

    Symfony also provides some form type guessers in the bridges:

    * :class:`Symfony\\Bridge\\Propel1\\Form\\PropelTypeGuesser` provided by
      the Propel1 bridge;
    * :class:`Symfony\\Bridge\\Doctrine\\Form\\DoctrineOrmTypeGuesser`
      provided by the Doctrine bridge.

Create a PHPDoc Type Guesser
----------------------------

In this section, you are going to build a guesser that reads information about
fields from the PHPDoc of the properties. At first, you need to create a class
which implements :class:`Symfony\\Component\\Form\\FormTypeGuesserInterface`.
This interface requires 4 methods:

* :method:`Symfony\\Component\\Form\\FormTypeGuesserInterface::guessType` -
  tries to guess the type of a field;
* :method:`Symfony\\Component\\Form\\FormTypeGuesserInterface::guessRequired` -
  tries to guess the value of the :ref:`required <reference-form-option-required>`
  option;
* :method:`Symfony\\Component\\Form\\FormTypeGuesserInterface::guessMaxLength` -
  tries to guess the value of the :ref:`max_length <reference-form-option-max_length>`
  option;
* :method:`Symfony\\Component\\Form\\FormTypeGuesserInterface::guessPattern` -
  tries to guess the value of the :ref:`pattern <reference-form-option-pattern>`
  option.

Start by creating the class and these methods. Next, you'll learn how to fill each on.

.. code-block:: php

    namespace Acme\Form;

    use Symfony\Component\Form\FormTypeGuesserInterface;

    class PhpdocTypeGuesser implements FormTypeGuesserInterface
    {
        public function guessType($class, $property)
        {
        }

        public function guessRequired($class, $property)
        {
        }

        public function guessMaxLength($class, $property)
        {
        }

        public function guessPattern($class, $property)
        {
        }
    }

Guessing the Type
~~~~~~~~~~~~~~~~~

When guessing a type, the method returns either an instance of
:class:`Symfony\\Component\\Form\\Guess\\TypeGuess` or nothing, to determine
that the type guesser cannot guess the type.

The ``TypeGuess`` constructor requires 3 options:

* The type name (one of the :doc:`form types </reference/forms/types>`);
* Additional options (for instance, when the type is ``entity``, you also
  want to set the ``class`` option). If no types are guessed, this should be
  set to an empty array;
* The confidence that the guessed type is correct. This can be one of the
  constants of the :class:`Symfony\\Component\\Form\\Guess\\Guess` class:
  ``LOW_CONFIDENCE``, ``MEDIUM_CONFIDENCE``, ``HIGH_CONFIDENCE``,
  ``VERY_HIGH_CONFIDENCE``. After all type guessers have been executed, the
  type with the highest confidence is used.

With this knowledge, you can easily implement the ``guessType`` method of the
``PHPDocTypeGuesser``::

    namespace Acme\Form;

    use Symfony\Component\Form\Guess\Guess;
    use Symfony\Component\Form\Guess\TypeGuess;

    class PhpdocTypeGuesser implements FormTypeGuesserInterface
    {
        public function guessType($class, $property)
        {
            $annotations = $this->readPhpDocAnnotations($class, $property);

            if (!isset($annotations['var'])) {
                return; // guess nothing if the @var annotation is not available
            }

            // otherwise, base the type on the @var annotation
            switch ($annotations['var']) {
                case 'string':
                    // there is a high confidence that the type is text when
                    // @var string is used
                    return new TypeGuess('text', array(), Guess::HIGH_CONFIDENCE);

                case 'int':
                case 'integer':
                    // integers can also be the id of an entity or a checkbox (0 or 1)
                    return new TypeGuess('integer', array(), Guess::MEDIUM_CONFIDENCE);

                case 'float':
                case 'double':
                case 'real':
                    return new TypeGuess('number', array(), Guess::MEDIUM_CONFIDENCE);

                case 'boolean':
                case 'bool':
                    return new TypeGuess('checkbox', array(), Guess::HIGH_CONFIDENCE);

                default:
                    // there is a very low confidence that this one is correct
                    return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
            }
        }

        protected function readPhpDocAnnotations($class, $property)
        {
            $reflectionProperty = new \ReflectionProperty($class, $property);
            $phpdoc = $reflectionProperty->getDocComment();

            // parse the $phpdoc into an array like:
            // array('type' => 'string', 'since' => '1.0')
            $phpdocTags = ...;

            return $phpdocTags;
        }
    }

This type guesser can now guess the field type for a property if it has
PHPdoc!

Guessing Field Options
~~~~~~~~~~~~~~~~~~~~~~

The other 3 methods (``guessMaxLength``, ``guessRequired`` and
``guessPattern``) return a :class:`Symfony\\Component\\Form\\Guess\\ValueGuess`
instance with the value of the option. This constructor has 2 arguments:

* The value of the option;
* The confidence that the guessed value is correct (using the constants of the
  ``Guess`` class).

``null`` is guessed when you believe the value of the option should not be
set.

.. caution::

    You should be very careful using the ``guessPattern`` method. When the
    type is a float, you cannot use it to determine a min or max value of the
    float (e.g. you want a float to be greater than ``5``, ``4.512313`` is not valid
    but ``length(4.512314) > length(5)`` is, so the pattern will succeed). In
    this case, the value should be set to ``null`` with a ``MEDIUM_CONFIDENCE``.

Registering a Type Guesser
--------------------------

The last thing you need to do is registering your custom type guesser by using
:method:`Symfony\\Component\\Form\\FormFactoryBuilder::addTypeGuesser` or
:method:`Symfony\\Component\\Form\\FormFactoryBuilder::addTypeGuessers`::

    use Symfony\Component\Form\Forms;
    use Acme\Form\PHPDocTypeGuesser;

    $formFactory = Forms::createFormFactoryBuilder()
        // ...
        ->addTypeGuesser(new PHPDocTypeGuesser())
        ->getFormFactory();

    // ...

.. note::

    When you use the Symfony framework, you need to register your type guesser
    and tag it with ``form.type_guesser``. For more information see
    :ref:`the tag reference <reference-dic-type_guesser>`.
