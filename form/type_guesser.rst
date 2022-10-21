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

    * :class:`Symfony\\Bridge\\Doctrine\\Form\\DoctrineOrmTypeGuesser`
      provided by the Doctrine bridge.

Create a PHPDoc Type Guesser
----------------------------

In this section, you are going to build a guesser that reads information about
fields from the PHPDoc of the properties. At first, you need to create a class
which implements :class:`Symfony\\Component\\Form\\FormTypeGuesserInterface`.
This interface requires four methods:

:method:`Symfony\\Component\\Form\\FormTypeGuesserInterface::guessType`
    Tries to guess the type of a field;
:method:`Symfony\\Component\\Form\\FormTypeGuesserInterface::guessRequired`
    Tries to guess the value of the :ref:`required <reference-form-option-required>`
    option;
:method:`Symfony\\Component\\Form\\FormTypeGuesserInterface::guessMaxLength`
    Tries to guess the value of the ``maxlength`` input attribute;
:method:`Symfony\\Component\\Form\\FormTypeGuesserInterface::guessPattern`
    Tries to guess the value of the ``pattern`` input attribute.

Start by creating the class and these methods. Next, you'll learn how to fill each in::

    // src/Form/TypeGuesser/PHPDocTypeGuesser.php
    namespace App\Form\TypeGuesser;

    use Symfony\Component\Form\FormTypeGuesserInterface;
    use Symfony\Component\Form\Guess\TypeGuess;
    use Symfony\Component\Form\Guess\ValueGuess;

    class PHPDocTypeGuesser implements FormTypeGuesserInterface
    {
        public function guessType(string $class, string $property): ?TypeGuess
        {
        }

        public function guessRequired(string $class, string $property): ?ValueGuess
        {
        }

        public function guessMaxLength(string $class, string $property): ?ValueGuess
        {
        }

        public function guessPattern(string $class, string $property): ?ValueGuess
        {
        }
    }

Guessing the Type
~~~~~~~~~~~~~~~~~

When guessing a type, the method returns either an instance of
:class:`Symfony\\Component\\Form\\Guess\\TypeGuess` or nothing, to determine
that the type guesser cannot guess the type.

The ``TypeGuess`` constructor requires three options:

* The type name (one of the :doc:`form types </reference/forms/types>`);
* Additional options (for instance, when the type is ``entity``, you also
  want to set the ``class`` option). If no types are guessed, this should be
  set to an empty array;
* The confidence that the guessed type is correct. This can be one of the
  constants of the :class:`Symfony\\Component\\Form\\Guess\\Guess` class:
  ``LOW_CONFIDENCE``, ``MEDIUM_CONFIDENCE``, ``HIGH_CONFIDENCE``,
  ``VERY_HIGH_CONFIDENCE``. After all type guessers have been executed, the
  type with the highest confidence is used.

With this knowledge, you can implement the ``guessType()`` method of the
``PHPDocTypeGuesser``::

    // src/Form/TypeGuesser/PHPDocTypeGuesser.php
    namespace App\Form\TypeGuesser;

    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\Extension\Core\Type\IntegerType;
    use Symfony\Component\Form\Extension\Core\Type\NumberType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Guess\Guess;
    use Symfony\Component\Form\Guess\TypeGuess;

    class PHPDocTypeGuesser implements FormTypeGuesserInterface
    {
        public function guessType(string $class, string $property): ?TypeGuess
        {
            $annotations = $this->readPhpDocAnnotations($class, $property);

            if (!isset($annotations['var'])) {
                return null; // guess nothing if the @var annotation is not available
            }

            // otherwise, base the type on the @var annotation
            return match($annotations['var']) {
                // there is a high confidence that the type is text when
                // @var string is used
                'string' => new TypeGuess(TextType::class, [], Guess::HIGH_CONFIDENCE),

                // integers can also be the id of an entity or a checkbox (0 or 1)
                'int', 'integer' => new TypeGuess(IntegerType::class, [], Guess::MEDIUM_CONFIDENCE),

                'float', 'double', 'real' => new TypeGuess(NumberType::class, [], Guess::MEDIUM_CONFIDENCE),

                'boolean', 'bool' => new TypeGuess(CheckboxType::class, [], Guess::HIGH_CONFIDENCE),

                // there is a very low confidence that this one is correct
                default => new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE)
            };
        }

        protected function readPhpDocAnnotations(string $class, string $property): array
        {
            $reflectionProperty = new \ReflectionProperty($class, $property);
            $phpdoc = $reflectionProperty->getDocComment();

            // parse the $phpdoc into an array like:
            // ['var' => 'string', 'since' => '1.0']
            $phpdocTags = ...;

            return $phpdocTags;
        }

        // ...
    }

This type guesser can now guess the field type for a property if it has
PHPDoc!

Guessing Field Options
~~~~~~~~~~~~~~~~~~~~~~

The other three methods (``guessMaxLength()``, ``guessRequired()`` and
``guessPattern()``) return a :class:`Symfony\\Component\\Form\\Guess\\ValueGuess`
instance with the value of the option. This constructor has 2 arguments:

* The value of the option;
* The confidence that the guessed value is correct (using the constants of the
  ``Guess`` class).

``null`` is guessed when you believe the value of the option should not be
set.

.. caution::

    You should be very careful using the ``guessPattern()`` method. When the
    type is a float, you cannot use it to determine a min or max value of the
    float (e.g. you want a float to be greater than ``5``, ``4.512313`` is not valid
    but ``length(4.512314) > length(5)`` is, so the pattern will succeed). In
    this case, the value should be set to ``null`` with a ``MEDIUM_CONFIDENCE``.

Registering a Type Guesser
--------------------------

If you're using :ref:`autowire <services-autowire>` and
:ref:`autoconfigure <services-autoconfigure>`, you're done! Symfony already knows
and is using your form type guesser.

If you're **not** using autowire and autoconfigure, register your service manually
and tag it with ``form.type_guesser``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Form\TypeGuesser\PHPDocTypeGuesser:
                tags: [form.type_guesser]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Form\TypeGuesser\PHPDocTypeGuesser">
                    <tag name="form.type_guesser"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Form\TypeGuesser\PHPDocTypeGuesser;

        $container->register(PHPDocTypeGuesser::class)
            ->addTag('form.type_guesser')
        ;

.. sidebar:: Registering a Type Guesser in the Component

    If you're using the Form component standalone in your PHP project, use
    :method:`Symfony\\Component\\Form\\FormFactoryBuilder::addTypeGuesser` or
    :method:`Symfony\\Component\\Form\\FormFactoryBuilder::addTypeGuessers` of
    the ``FormFactoryBuilder`` to register new type guessers::

        use App\Form\TypeGuesser\PHPDocTypeGuesser;
        use Symfony\Component\Form\Forms;

        $formFactory = Forms::createFormFactoryBuilder()
            // ...
            ->addTypeGuesser(new PHPDocTypeGuesser())
            ->getFormFactory();

        // ...

.. tip::

    Run the following command to verify that the form type guesser was
    successfully registered in the application:

    .. code-block:: terminal

        $ php bin/console debug:form
