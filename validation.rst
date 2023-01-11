.. index::
   single: Validation

Validation
==========

Validation is a very common task in web applications. Data entered in forms
needs to be validated. Data also needs to be validated before it is written
into a database or passed to a web service.

Symfony provides a `Validator`_ component to handle this for you. This
component is based on the `JSR303 Bean Validation specification`_.

.. index::
   pair: Validation; Installation
   pair: Validation; Configuration

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

.. index::
   single: Validation; The basics

The Basics of Validation
------------------------

The best way to understand validation is to see it in action. To start, suppose
you've created a plain-old-PHP object that you need to use somewhere in
your application::

    // src/Entity/Author.php
    namespace App\Entity;

    class Author
    {
        private $name;
    }

So far, this is an ordinary class that serves some purpose inside your
application. The goal of validation is to tell you if the data of an object is
valid. For this to work, you'll configure a list of rules (called
:ref:`constraints <validation-constraints>`) that the object must follow in
order to be valid. These rules are usually defined using PHP code or
attributes but they can also be defined as ``.yaml`` or ``.xml`` files inside
the ``config/validator/`` directory:

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
            private $name;
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
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            private $name;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new NotBlank());
            }
        }

Adding this configuration by itself does not yet guarantee that the value will
not be blank; you can still set it to a blank value if you want.
To actually guarantee that the value adheres to the constraint, the object must
be passed to the validator service to be checked.

.. tip::

    Symfony's validator uses PHP reflection, as well as *"getter"* methods, to
    get the value of any property, so they can be public, private or protected
    (see :ref:`validator-constraint-targets`).

.. index::
   single: Validation; Using the validator

Using the Validator Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, to actually validate an ``Author`` object, use the ``validate()`` method
on the ``validator`` service (which implements :class:`Symfony\\Component\\Validator\\Validator\\ValidatorInterface`).
The job of the ``validator`` is to read the constraints (i.e. rules)
of a class and verify if the data on the object satisfies those
constraints. If validation fails, a non-empty list of errors
(:class:`Symfony\\Component\\Validator\\ConstraintViolationList` class) is
returned. Take this simple example from inside a controller::

    // ...
    use App\Entity\Author;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Validator\Validator\ValidatorInterface;

    // ...
    public function author(ValidatorInterface $validator)
    {
        $author = new Author();

        // ... do something to the $author object

        $errors = $validator->validate($author);

        if (count($errors) > 0) {
            /*
             * Uses a __toString method on the $errors variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            $errorsString = (string) $errors;

            return new Response($errorsString);
        }

        return new Response('The author is valid! Yes!');
    }

If the ``$name`` property is empty, you will see the following error
message:

.. code-block:: text

    Object(App\Entity\Author).name:
        This value should not be blank.

If you insert a value into the ``name`` property, the happy success message
will appear.

.. tip::

    Most of the time, you won't interact directly with the ``validator``
    service or need to worry about printing out the errors. Most of the time,
    you'll use validation indirectly when handling submitted form data. For
    more information, see :ref:`how to validate Symfony forms <validating-forms>`.

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

.. versionadded:: 6.3

    The ``ConstraintViolation::getConstraint()`` method was introduced in Symfony 6.3.

.. index::
   single: Validation; Callables

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

.. index::
   single: Validation; Constraints

.. _validation-constraints:

Constraints
-----------

The ``validator`` is designed to validate objects against *constraints* (i.e.
rules). In order to validate an object, simply map one or more constraints
to its class and then pass it to the ``validator`` service.

Behind the scenes, a constraint is simply a PHP object that makes an assertive
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

.. index::
   single: Validation; Constraints configuration

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
            private $genre;

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
            private $genre;

            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                // ...

                $metadata->addPropertyConstraint('genre', new Assert\Choice([
                    'choices' => ['fiction', 'non-fiction'],
                    'message' => 'Choose a valid genre.',
                ]));
            }
        }

.. _validation-default-option:

The options of a constraint can always be passed in as an array. Some constraints,
however, also allow you to pass the value of one, "*default*", option in place
of the array. In the case of the ``Choice`` constraint, the ``choices``
options can be specified in this way.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Choice(['fiction', 'non-fiction'])]
            private $genre;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                genre:
                    - Choice: [fiction, non-fiction]
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
                        <value>fiction</value>
                        <value>non-fiction</value>
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
            private $genre;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                // ...

                $metadata->addPropertyConstraint(
                    'genre',
                    new Assert\Choice(['fiction', 'non-fiction'])
                );
            }
        }

This is purely meant to make the configuration of the most common option of
a constraint shorter and quicker.

If you're ever unsure of how to specify an option, either check the namespace
``Symfony\Component\Validator\Constraints`` for the constraint or play it safe
by always passing in an array of options (the first method shown above).

Constraints in Form Classes
---------------------------

Constraints can be defined while building the form via the ``constraints`` option
of the form fields::

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('myField', TextType::class, [
                'required' => true,
                'constraints' => [new Length(['min' => 3])],
            ])
        ;
    }

.. index::
   single: Validation; Constraint targets

.. _validator-constraint-targets:

Constraint Targets
------------------

Constraints can be applied to a class property (e.g. ``name``),
a getter method (e.g. ``getFullName()``) or an entire class. Property constraints
are the most common and easy to use. Getter constraints allow you to specify
more complex validation rules. Finally, class constraints are intended
for scenarios where you want to validate a class as a whole.

.. index::
   single: Validation; Property constraints

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
            private $firstName;
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
            private $firstName;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new Assert\NotBlank());
                $metadata->addPropertyConstraint(
                    'firstName',
                    new Assert\Length(['min' => 3])
                );
            }
        }

.. caution::

    The validator will use a value ``null`` if a typed property is uninitialized.
    This can cause unexpected behavior if the property holds a value when initialized.
    In order to avoid this, make sure all properties are initialized before validating them.

.. index::
   single: Validation; Getter constraints

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
            public function isPasswordSafe()
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
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addGetterConstraint('passwordSafe', new Assert\IsTrue([
                    'message' => 'The password cannot match your first name',
                ]));
            }
        }

Now, create the ``isPasswordSafe()`` method and include the logic you need::

    public function isPasswordSafe()
    {
        return $this->firstName !== $this->password;
    }

.. note::

    The keen-eyed among you will have noticed that the prefix of the getter
    ("get", "is" or "has") is omitted in the mappings for the YAML, XML and PHP
    formats. This allows you to move the constraint to a property with the same
    name later (or vice versa) without changing your validation logic.

.. _validation-class-target:

Classes
~~~~~~~

Some constraints apply to the entire class being validated. For example,
the :doc:`Callback </reference/constraints/Callback>` constraint is a generic
constraint that's applied to the class itself. When that class is validated,
methods specified by that constraint are simply executed so that each can
provide more custom validation.

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

Final Thoughts
--------------

The Symfony ``validator`` is a powerful tool that can be leveraged to
guarantee that the data of any object is "valid". The power behind validation
lies in "constraints", which are rules that you can apply to properties or
getter methods of your object. And while you'll most commonly use the validation
framework indirectly when using forms, remember that it can be used anywhere
to validate any object.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /validation/*

.. _Validator: https://github.com/symfony/validator
.. _JSR303 Bean Validation specification: https://jcp.org/en/jsr/detail?id=303
