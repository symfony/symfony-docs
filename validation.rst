Validation
==========

Validation is a very common task in web applications. Data entered in forms
needs to be validated. Data also needs to be validated before it is written
into a database or passed to a web service.

Symfony provides a Validator component to handle this for you. The component
is based on two concepts:

* Constraints, which define the rules to be validated;
* Validators, which are the classes that contain the actual validation logic.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the validator before using it:

.. code-block:: terminal

    $ composer require symfony/validator

.. note::

    If your application doesn't use Symfony Flex, you might need to do some
    manual configuration to enable validation. Check out the
    :ref:`Validation configuration reference <reference-validation>`.

The Basics of Validation
------------------------

The best way to understand validation is to see it in action. To start, suppose
you've created a class that you need to use somewhere in your application::

    // src/Entity/Author.php
    namespace App\Entity;

    class Author
    {
        private string $name;
    }

So far, this is an ordinary class that serves some purpose inside your
application. The goal of validation is to tell you if the data of an object is
valid. For this to work, you'll configure a list of rules (called
:ref:`constraints <validation-constraints>`) that the object must follow in
order to be valid. These rules are usually defined using PHP code or
attributes but they can also be defined as ``.yaml`` or ``.xml`` files inside
the ``config/validator/`` directory.

For example, to indicate that the ``$name`` property must not be empty, add the
following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\NotBlank]
            private string $name;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                name:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="name">
                    <constraint name="NotBlank"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;
        // ...
        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            private string $name;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('name', new Assert\NotBlank());
            }
        }

Adding this configuration by itself does not yet guarantee that the value will
not be blank; you can still set it to a blank value if you want.
To actually guarantee that the value adheres to the constraint, the object must
be passed to the validator to be checked.

.. note::

    When using the Validator component outside a Symfony application, you must
    tell the validator explicitly where to look for the constraint metadata
    (PHP attributes, YAML or XML files, etc.). Read the
    :doc:`/components/validator/resources` article for more details.

.. tip::

    Symfony's validator uses PHP reflection, as well as *"getter"* methods, to
    get the value of any property, so they can be public, private or protected
    (see :ref:`validator-constraint-targets`).

.. tip::

    Symfony provides a JSON schema for validation mapping files that enables
    autocompletion and validation in IDEs like PhpStorm. Add the following
    ``$schema`` key at the beginning of your YAML files to enable this feature:

    .. code-block:: yaml

        # config/validator/validation.yaml
        '$schema': https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.json
        App\Entity\Author:
            properties:
                # your IDE will now provide autocompletion here...

Using the Validator
~~~~~~~~~~~~~~~~~~~

Next, to actually validate an ``Author`` object, use the ``validate()`` method
of the validator (which implements :class:`Symfony\\Component\\Validator\\Validator\\ValidatorInterface`).
The job of the validator is to read the constraints (i.e. rules) of a class and
verify if the data on the object satisfies those constraints. If validation
fails, a non-empty list of errors
(:class:`Symfony\\Component\\Validator\\ConstraintViolationList` class) is
returned. In Symfony applications, this validator is available as the
``validator`` service, which you can inject anywhere thanks to
:doc:`autowiring </service_container/autowiring>`:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Controller/AuthorController.php
        namespace App\Controller;

        use App\Entity\Author;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Validator\Validator\ValidatorInterface;

        // ...
        public function author(ValidatorInterface $validator): Response
        {
            $author = new Author();

            // ... do something to the $author object

            $errors = $validator->validate($author);

            if (count($errors) > 0) {
                // the __toString() method of the error list returns a
                // string representation useful for debugging
                $errorsString = (string) $errors;

                return new Response($errorsString);
            }

            return new Response('The author is valid! Yes!');
        }

    .. code-block:: php-standalone

        use App\Entity\Author;
        use Symfony\Component\Validator\Validation;

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $author = new Author();

        // ... do something to the $author object

        $errors = $validator->validate($author);

        if (count($errors) > 0) {
            // the __toString() method of the error list returns a
            // string representation useful for debugging
            $errorsString = (string) $errors;
        }

If the ``$name`` property is empty, you will see the following error
message:

.. code-block:: text

    Object(App\Entity\Author).name:
        This value should not be blank.

.. tip::

    Most of the time you won't interact directly with the validator or need
    to worry about printing out the errors, because you'll use validation
    indirectly when handling submitted form data. For more information, see
    :ref:`how to validate Symfony forms <validating-forms>`.

You could also pass the collection of errors into a template::

    if (count($errors) > 0) {
        return $this->render('author/validation.html.twig', [
            'errors' => $errors,
        ]);
    }

Inside the template, you can output the list of errors exactly as needed:

.. code-block:: html+twig

    {# templates/author/validation.html.twig #}
    <h3>The author has the following errors</h3>
    <ul>
    {% for error in errors %}
        <li>{{ error.message }}</li>
    {% endfor %}
    </ul>

.. note::

    Each validation error (called a "constraint violation"), is represented by
    a :class:`Symfony\\Component\\Validator\\ConstraintViolation` object. This
    object allows you, among other things, to get the constraint that caused this
    violation thanks to the ``ConstraintViolation::getConstraint()`` method.

If you have lots of validation errors, you can filter them by error code::

    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

    $violations = $validator->validate(/* ... */);
    if (0 !== count($violations->findByCodes(UniqueEntity::NOT_UNIQUE_ERROR))) {
        // handle this specific error (display some message, send an email, etc.)
    }

Validation Callables
~~~~~~~~~~~~~~~~~~~~

The ``Validation`` also allows you to create a closure to validate values
against a set of constraints (useful for example when
:ref:`validating Console command answers <console-validate-question-answer>` or
when :ref:`validating OptionsResolver values <optionsresolver-validate-value>`):

:method:`Symfony\\Component\\Validator\\Validation::createCallable`
    This returns a closure that throws ``ValidationFailedException`` when the
    constraints aren't matched.
:method:`Symfony\\Component\\Validator\\Validation::createIsValidCallable`
    This returns a closure that returns ``false`` when the constraints aren't matched.

Validating Properties
~~~~~~~~~~~~~~~~~~~~~

Instead of validating an entire object, you can validate a single property
using the ``validateProperty()`` method::

    $violations = $validator->validateProperty($author, 'name');

This method reads the current value of the property and validates it against
the constraints defined for that property.

You can also validate a value against the constraints of a property without
setting it on the object, using ``validatePropertyValue()`` to check whether a
value would be valid before assigning it::

    $violations = $validator->validatePropertyValue($author, 'name', 'John');

By default, ``validateProperty()`` and ``validatePropertyValue()`` silently
return zero violations when the given property has no constraints defined.
This means that typos or renamed properties won't produce any error.

You can enable a strict check that throws a
:class:`Symfony\\Component\\Validator\\Exception\\ValidatorException` if no
metadata is found for the given property:

.. configuration-block::

    .. code-block:: php-symfony

        // register a compiler pass to enable the check

        use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
        use Symfony\Component\DependencyInjection\ContainerBuilder;

        class EnablePropertyMetadataExistenceCheckPass implements CompilerPassInterface
        {
            public function process(ContainerBuilder $container): void
            {
                $container->getDefinition('validator.builder')
                    ->addMethodCall('enablePropertyMetadataExistenceCheck');
            }
        }

    .. code-block:: php-standalone

        use Symfony\Component\Validator\Validation;

        $validator = Validation::createValidatorBuilder()
            ->enablePropertyMetadataExistenceCheck()
            ->getValidator();

With this configuration, the following code throws a ``ValidatorException``
because ``'nmae'`` (note the typo) does not exist::

    $violations = $validator->validateProperty($author, 'nmae');

.. versionadded:: 8.1

    The ``enablePropertyMetadataExistenceCheck()`` method was introduced in
    Symfony 8.1.

.. _validation-constraints:

Constraints
-----------

The ``validator`` is designed to validate objects against *constraints* (i.e.
rules). In order to validate an object, map one or more constraints
to its class and then pass it to the ``validator`` service.

Internally, a constraint is a PHP object that makes an assertive
statement. In real life, a constraint could be: ``'The cake must not be burned'``.
In Symfony, constraints are similar: they are assertions that a condition
is true. Given a value, a constraint will tell you if that value
adheres to the rules of the constraint.

Supported Constraints
~~~~~~~~~~~~~~~~~~~~~

Symfony packages many of the most commonly-needed constraints:

.. include:: /reference/constraints/map.rst.inc

You can also create your own custom constraints. This topic is covered in
the :doc:`/validation/custom_constraint` article.

.. _validation-constraint-configuration:

Constraint Configuration
~~~~~~~~~~~~~~~~~~~~~~~~

Some constraints, like :doc:`NotBlank </reference/constraints/NotBlank>`,
are simple whereas others, like the :doc:`Choice </reference/constraints/Choice>`
constraint, have several configuration options available. Suppose that the
``Author`` class has another property called ``genre`` that defines the
literature genre mostly associated with the author, which can be set to either
"fiction" or "non-fiction":

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Choice(
                choices: ['fiction', 'non-fiction'],
                message: 'Choose a valid genre.',
            )]
            private string $genre;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                genre:
                    - Choice: { choices: [fiction, non-fiction], message: Choose a valid genre. }
                # ...

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="genre">
                    <constraint name="Choice">
                        <option name="choices">
                            <value>fiction</value>
                            <value>non-fiction</value>
                        </option>
                        <option name="message">Choose a valid genre.</option>
                    </constraint>
                </property>

                <!-- ... -->
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        // ...
        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            private string $genre;

            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                // ...

                $metadata->addPropertyConstraint('genre', new Assert\Choice(
                    choices: ['fiction', 'non-fiction'],
                    message: 'Choose a valid genre.',
                ));
            }
        }

Constraints in Form Classes
---------------------------

Constraints can be defined while building the form via the ``constraints`` option
of the form fields::

    use Symfony\Component\Validator\Constraints as Assert;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('myField', TextType::class, [
                'required' => true,
                'constraints' => [new Assert\Length(min: 3)],
            ])
        ;
    }

.. _validator-constraint-targets:

Constraint Targets
------------------

Constraints can be applied to a class property (e.g. ``name``),
a getter method (e.g. ``getFullName()``) or an entire class. Property constraints
are the most common and easy to use. Getter constraints allow you to specify
more complex validation rules. Finally, class constraints are intended
for scenarios where you want to validate a class as a whole.

.. _validation-property-target:

Properties
~~~~~~~~~~

Validating class properties is the most basic validation technique. Symfony
allows you to validate private, protected or public properties. The next
listing shows you how to configure the ``$firstName`` property of an ``Author``
class to have at least 3 characters.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\NotBlank]
            #[Assert\Length(min: 3)]
            private string $firstName;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                firstName:
                    - NotBlank: ~
                    - Length:
                        min: 3

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="firstName">
                    <constraint name="NotBlank"/>
                    <constraint name="Length">
                        <option name="min">3</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        // ...
        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            private string $firstName;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('firstName', new Assert\NotBlank());
                $metadata->addPropertyConstraint(
                    'firstName',
                    new Assert\Length(min: 3)
                );
            }
        }

.. warning::

    The validator will use a value ``null`` if a typed property is uninitialized.
    This can cause unexpected behavior if the property holds a value when initialized.
    In order to avoid this, make sure all properties are initialized before validating them.

Getters
~~~~~~~

Constraints can also be applied to the return value of a method. Symfony
allows you to add a constraint to any private, protected or public method whose name starts with
"get", "is" or "has". In this guide, these types of methods are referred to
as "getters".

The benefit of this technique is that it allows you to validate your object
dynamically. For example, suppose you want to make sure that a password field
doesn't match the first name of the user (for security reasons). You can
do this by creating an ``isPasswordSafe()`` method, and then asserting that
this method must return ``true``:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\IsTrue(message: 'The password cannot match your first name')]
            public function isPasswordSafe(): bool
            {
                // ... return true or false
            }
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            getters:
                passwordSafe:
                    - 'IsTrue': { message: 'The password cannot match your first name' }

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <getter property="passwordSafe">
                    <constraint name="IsTrue">
                        <option name="message">The password cannot match your first name</option>
                    </constraint>
                </getter>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        // ...
        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addGetterConstraint('passwordSafe', new Assert\IsTrue(
                    message: 'The password cannot match your first name',
                ));
            }
        }

Now, create the ``isPasswordSafe()`` method and include the logic you need::

    public function isPasswordSafe(): bool
    {
        return $this->firstName !== $this->password;
    }

.. note::

    The prefix of the getter ("get", "is" or "has") is omitted in the mappings
    for the YAML, XML and PHP formats. This allows you to move the constraint
    to a property with the same name later (or vice versa) without changing
    your validation logic.

.. _validation-class-target:

Classes
~~~~~~~

Some constraints apply to the entire class being validated. For example,
the :doc:`Callback </reference/constraints/Callback>` constraint is a generic
constraint that's applied to the class itself. When that class is validated,
methods specified by that constraint are simply executed so that each can
provide more custom validation.

Validating Object With Inheritance
----------------------------------

When you validate an object that extends another class, the validator
automatically validates constraints defined in the parent class as well.

**The constraints defined in the parent properties will be applied to the child
properties even if the child properties override those constraints**. Symfony
will always merge the parent constraints for each property.

You can't change this behavior, but you can overcome it by defining the parent
and the child constraints in different :doc:`validation groups </validation/groups>`
and then select the appropriate group when validating each object.

.. _validation-extends-validation:

Extending Validation for a Class
--------------------------------

Sometimes you may want to add or override validation constraints on a class you
cannot modify (for example, a model coming from a third party library or a
bundle).

Suppose you use a third party ``Product`` class that validates the ``name``
property with a minimum length of 2, but in your application you want to enforce
a minimum of 10 characters.

To do this, create a separate class and use the ``#[ExtendsValidationFor]``
attribute to tell the Validator which class should receive these constraints.
Your new class name is irrelevant and the class is typically made ``abstract`` to
make it clear it is never instantiated::

    use Symfony\Component\Validator\Attribute\ExtendsValidationFor;
    use Symfony\Component\Validator\Constraints as Assert;

    #[ExtendsValidationFor(Product::class)]
    abstract class MyProductValidation
    {
        #[Assert\NotBlank(groups: ['my_app'])]
        #[Assert\Length(min: 10, groups: ['my_app'])]
        public string $name = '';
    }

The constraints defined in this class are applied to the target class (``Product``)
as if they were defined there.

You can only define constraints for properties that exist on the target class.
Otherwise, a ``MappingException`` is thrown.

Debugging the Constraints
-------------------------

Use the ``debug:validator`` command to list the validation constraints of a
given class:

.. code-block:: terminal

    $ php bin/console debug:validator 'App\Entity\SomeClass'

        App\Entity\SomeClass
        -----------------------------------------------------

        +---------------+--------------------------------------------------+---------+------------------------------------------------------------+
        | Property      | Name                                             | Groups  | Options                                                    |
        +---------------+--------------------------------------------------+---------+------------------------------------------------------------+
        | firstArgument | Symfony\Component\Validator\Constraints\NotBlank | Default | [                                                          |
        |               |                                                  |         |   "message" => "This value should not be blank.",          |
        |               |                                                  |         |   "allowNull" => false,                                    |
        |               |                                                  |         |   "normalizer" => null,                                    |
        |               |                                                  |         |   "payload" => null                                        |
        |               |                                                  |         | ]                                                          |
        | firstArgument | Symfony\Component\Validator\Constraints\Email    | Default | [                                                          |
        |               |                                                  |         |   "message" => "This value is not a valid email address.", |
        |               |                                                  |         |   "mode" => null,                                          |
        |               |                                                  |         |   "normalizer" => null,                                    |
        |               |                                                  |         |   "payload" => null                                        |
        |               |                                                  |         | ]                                                          |
        +---------------+--------------------------------------------------+---------+------------------------------------------------------------+

You can also validate all the classes stored in a given directory:

.. code-block:: terminal

    $ php bin/console debug:validator src/Entity

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /components/validator/*
    /validation/*
