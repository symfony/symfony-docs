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

    // src/AppBundle/Form/TypeGuesser/PHPDocTypeGuesser.php
    namespace AppBundle\Form\TypeGuesser;

    use Symfony\Component\Form\FormTypeGuesserInterface;

    class PHPDocTypeGuesser implements FormTypeGuesserInterface
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

With this knowledge, you can easily implement the ``guessType()`` method of the
``PHPDocTypeGuesser``::

    namespace AppBundle\Form\TypeGuesser;

    use Symfony\Component\Form\Guess\Guess;
    use Symfony\Component\Form\Guess\TypeGuess;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\IntegerType;
    use Symfony\Component\Form\Extension\Core\Type\NumberType;
    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

    class PHPDocTypeGuesser implements FormTypeGuesserInterface
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
                    return new TypeGuess(TextType::class, array(), Guess::HIGH_CONFIDENCE);

                case 'int':
                case 'integer':
                    // integers can also be the id of an entity or a checkbox (0 or 1)
                    return new TypeGuess(IntegerType::class, array(), Guess::MEDIUM_CONFIDENCE);

                case 'float':
                case 'double':
                case 'real':
                    return new TypeGuess(NumberType::class, array(), Guess::MEDIUM_CONFIDENCE);

                case 'boolean':
                case 'bool':
                    return new TypeGuess(CheckboxType::class, array(), Guess::HIGH_CONFIDENCE);

                default:
                    // there is a very low confidence that this one is correct
                    return new TypeGuess(TextType::class, array(), Guess::LOW_CONFIDENCE);
            }
        }

        protected function readPhpDocAnnotations($class, $property)
        {
            $reflectionProperty = new \ReflectionProperty($class, $property);
            $phpdoc = $reflectionProperty->getDocComment();

            // parse the $phpdoc into an array like:
            // array('var' => 'string', 'since' => '1.0')
            $phpdocTags = ...;

            return $phpdocTags;
        }

        // ...
    }

This type guesser can now guess the field type for a property if it has
PHPdoc!

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

        # app/config/services.yml
        services:
            # ...

            AppBundle\Form\TypeGuesser\PHPDocTypeGuesser:
                tags: [form.type_guesser]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Form\TypeGuesser\PHPDocTypeGuesser">
                    <tag name="form.type_guesser"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Form\TypeGuesser\PHPDocTypeGuesser;

        $container->register(PHPDocTypeGuesser::class)
            ->addTag('form.type_guesser')
        ;

.. versionadded:: 3.3
    Prior to Symfony 3.3, you needed to define type guesser services as ``public``.
    Starting from Symfony 3.3, you can also define them as ``private``.

.. sidebar:: Registering a Type Guesser in the Component

    If you're using the Form component standalone in your PHP project, use
    :method:`Symfony\\Component\\Form\\FormFactoryBuilder::addTypeGuesser` or
    :method:`Symfony\\Component\\Form\\FormFactoryBuilder::addTypeGuessers` of
    the ``FormFactoryBuilder`` to register new type guessers::

        use Symfony\Component\Form\Forms;
        use Acme\Form\PHPDocTypeGuesser;

        $formFactory = Forms::createFormFactoryBuilder()
            // ...
            ->addTypeGuesser(new PHPDocTypeGuesser())
            ->getFormFactory();

        // ...
