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
    (PHP attributes, YAML or XML files, etc.). See
    :ref:`how to load the constraint metadata <validation-metadata-loaders>`.

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

    .. versionadded:: 7.4

        The JSON schema for validation mapping files was introduced in Symfony 7.4.

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
    :ref:`how to validate Symfony forms <validating-forms>`. The same happens
    when :ref:`mapping the request payload <controller-mapping-request-payload>`
    to an object in your controllers.

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

    Each validation error (called a "constraint violation") is represented by
    a :class:`Symfony\\Component\\Validator\\ConstraintViolation` object. This
    object allows you, among other things, to get the constraint that caused this
    violation thanks to the ``ConstraintViolation::getConstraint()`` method.

If you have many validation errors, you can filter them by error code::

    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

    $violations = $validator->validate(/* ... */);
    if (0 !== count($violations->findByCodes(UniqueEntity::NOT_UNIQUE_ERROR))) {
        // handle this specific error (display some message, send an email, etc.)
    }

.. _validation-raw-values:

Validating Raw Values
~~~~~~~~~~~~~~~~~~~~~

Usually you validate entire objects, but sometimes you need to validate a
single value (e.g. to check that a string is a valid email address). To do
so, pass the value and the constraint (or an array of constraints) to the
``validate()`` method::

    // ...
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Validator\ValidatorInterface;

    // ...
    public function addEmail(string $email, ValidatorInterface $validator): void
    {
        $errors = $validator->validate($email, [
            new Assert\NotBlank(),
            new Assert\Email(message: 'Invalid email address'),
        ]);

        if (0 !== count($errors)) {
            // this is *not* a valid email address
            $errorMessage = $errors[0]->getMessage();

            // ... do something with the error
        }

        // ...
    }

Read the :doc:`constraints reference </reference/constraints>` to see the
full list of available constraints and their options.

To validate arrays, use the
:doc:`Collection </reference/constraints/Collection>` constraint::

    use Symfony\Component\Validator\Constraints as Assert;

    $input = [
        'name' => 'Fabien Potencier',
        'email' => 'fabien@example.com',
        'tags' => ['symfony', 'php'],
    ];

    $constraint = new Assert\Collection([
        // the keys correspond to the keys in the input array; by default,
        // keys missing from the input or not defined here are reported as errors
        'name' => [new Assert\NotBlank(), new Assert\Length(max: 100)],
        'email' => new Assert\Email(),
        // the 'tags' key can be missing from the input array
        'tags' => new Assert\Optional([
            new Assert\Type('array'),
            // applies the given constraints to each element of the array
            new Assert\All(new Assert\Length(min: 2)),
        ]),
    ]);

    $violations = $validator->validate($input, $constraint);

Validation Callables
~~~~~~~~~~~~~~~~~~~~

The ``Validation`` class also allows you to create a closure to validate values
against a set of constraints (useful for example when
:ref:`validating Console command answers <console-validate-question-answer>` or
when :ref:`validating OptionsResolver values <optionsresolver-validate-value>`):

:method:`Symfony\\Component\\Validator\\Validation::createCallable`
    This returns a closure that throws ``ValidationFailedException`` when the
    constraints aren't matched.
:method:`Symfony\\Component\\Validator\\Validation::createIsValidCallable`
    This returns a closure that returns ``false`` when the constraints aren't matched.

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

.. _validation-payload:

Attaching Custom Data to Constraints with Payloads
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All constraints define a ``payload`` option to attach arbitrary data to them.
The Validator component ignores this data, so you can use it for your own
purposes, such as displaying some errors differently than others.

For example, in a registration form, the username and password are required,
but the bank account number is optional. If users enter a bank account number,
it must be valid, but you want to display its errors as warnings instead of
errors. Use the payload to define the severity of each constraint:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            #[Assert\NotBlank(payload: ['severity' => 'error'])]
            protected string $username;

            #[Assert\NotBlank(payload: ['severity' => 'error'])]
            protected string $password;

            #[Assert\Iban(payload: ['severity' => 'warning'])]
            protected string $bankAccountNumber;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                username:
                    - NotBlank:
                        payload:
                            severity: error
                password:
                    - NotBlank:
                        payload:
                            severity: error
                bankAccountNumber:
                    - Iban:
                        payload:
                            severity: warning

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="username">
                    <constraint name="NotBlank">
                        <option name="payload">
                            <value key="severity">error</value>
                        </option>
                    </constraint>
                </property>
                <property name="password">
                    <constraint name="NotBlank">
                        <option name="payload">
                            <value key="severity">error</value>
                        </option>
                    </constraint>
                </property>
                <property name="bankAccountNumber">
                    <constraint name="Iban">
                        <option name="payload">
                            <value key="severity">warning</value>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('username', new Assert\NotBlank(
                    payload: ['severity' => 'error'],
                ));
                $metadata->addPropertyConstraint('password', new Assert\NotBlank(
                    payload: ['severity' => 'error'],
                ));
                $metadata->addPropertyConstraint('bankAccountNumber', new Assert\Iban(
                    payload: ['severity' => 'warning'],
                ));
            }
        }

When the validation of the ``User`` object fails, use the
:method:`Symfony\\Component\\Validator\\ConstraintViolation::getConstraint`
method to get the constraint that caused each violation. The payload is
available in the public ``payload`` property of the constraint::

    $violations = $validator->validate($user);

    foreach ($violations as $violation) {
        $severity = $violation->getConstraint()->payload['severity'] ?? null;
        // ...
    }

In forms, you can use this to customize the ``form_errors`` block in a
:ref:`form theme <create-your-own-form-theme>` and add the severity as an
HTML class:

.. code-block:: html+twig

    {# templates/form/custom_errors.html.twig #}
    {%- block form_errors -%}
        {%- if errors|length > 0 -%}
        <ul>
            {%- for error in errors -%}
                <li class="{{ error.cause.constraint.payload.severity ?? '' }}">{{ error.message }}</li>
            {%- endfor -%}
        </ul>
        {%- endif -%}
    {%- endblock form_errors -%}

.. seealso::

    :doc:`How to customize form rendering </form/form_customization>`.

.. _validation-translations:

Translating Constraint Messages
-------------------------------

When the :doc:`Translation component </translation>` is installed, Symfony
translates constraint violation messages into the current locale using the
``validators`` :ref:`translation domain <translation-resource-locations>`.
Symfony already provides translations of the built-in constraint messages in
many languages, so you only need to translate your own messages.

To do so, set the ``message`` option of the constraint to the translation key.
For example, to make sure that the ``$name`` property of the ``Author`` class
is not empty:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\NotBlank(message: 'author.name.not_blank')]
            public string $name;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                name:
                    - NotBlank: { message: 'author.name.not_blank' }

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="name">
                    <constraint name="NotBlank">
                        <option name="message">author.name.not_blank</option>
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
            public string $name;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('name', new Assert\NotBlank(
                    message: 'author.name.not_blank',
                ));
            }
        }

Then, translate that key in a ``validators`` catalog file stored in the
``translations/`` directory:

.. configuration-block::

    .. code-block:: xml

        <!-- translations/validators/validators.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="author.name.not_blank">
                        <source>author.name.not_blank</source>
                        <target>Please enter an author name.</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: yaml

        # translations/validators/validators.en.yaml
        author.name.not_blank: Please enter an author name.

    .. code-block:: php

        // translations/validators/validators.en.php
        return [
            'author.name.not_blank' => 'Please enter an author name.',
        ];

You may need to clear your cache (even in the dev environment) after creating
this file for the first time.

.. tip::

    Set the :ref:`enabled_locales <reference-translator-enabled-locales>`
    option to restrict the locales of your application. Symfony then only
    generates the translation files of the built-in validation messages for
    those locales, which improves performance.

When building violations in your own validation logic, you can also pass a
:class:`Symfony\\Component\\Translation\\TranslatableMessage` object as the
violation message::

    use Symfony\Component\Translation\TranslatableMessage;
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Context\ExecutionContextInterface;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
    {
        // somehow you have an array of "fake names"
        $fakeNames = [/* ... */];

        // check if the name is actually a fake name
        if (in_array($this->getFirstName(), $fakeNames, true)) {
            $message = new TranslatableMessage('author.name.fake', [], 'validators');

            $context->buildViolation($message)
                ->atPath('firstName')
                ->addViolation()
            ;
        }
    }

You can learn more about translatable messages in
:ref:`the dedicated section <translatable-objects>`.

Custom Translation Domain
~~~~~~~~~~~~~~~~~~~~~~~~~

To use a different translation domain for all violation messages, set the
:ref:`translation_domain <reference-validation-translation-domain>` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/validator.yaml
        framework:
            validation:
                translation_domain: validation_errors

    .. code-block:: xml

        <!-- config/packages/validator.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:validation
                    translation-domain="validation_errors"
                />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/validator.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework
                ->validation()
                    ->translationDomain('validation_errors')
            ;
        };

To change the domain of a single violation, call ``setTranslationDomain()``
when building it in a
:doc:`constraint validator </validation/custom_constraint>`::

    public function validate(mixed $value, Constraint $constraint): void
    {
        // validation logic

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value)
            ->setTranslationDomain('validation_errors')
            ->addViolation();
    }

Constraints in Form Classes
---------------------------

Constraints can be defined while building the form via the
:ref:`constraints option <reference-form-option-constraints>` of the form
fields::

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
are the most common and the simplest to use. Getter constraints allow you to specify
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

    The validator will use the ``null`` value if a typed property is uninitialized.
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
methods specified by that constraint are executed so that each can
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
and then selecting the appropriate group when validating each object.

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

.. versionadded:: 7.4

    The ``#[ExtendsValidationFor]`` attribute was introduced in Symfony 7.4.

.. _validation-metadata-loaders:

Loading the Constraint Metadata
-------------------------------

In Symfony applications, the validator loads the constraint metadata of your
classes from these sources automatically:

* PHP attributes (e.g. ``#[Assert\NotBlank]``);
* YAML and XML files stored in the ``config/validator/`` directory (use the
  :ref:`mapping.paths <reference-validation-mapping-paths>` option to add other
  directories);
* the static ``loadValidatorMetadata()`` method of the class, if it exists.

When using the Validator component outside a Symfony application, enable each
metadata source in the validator builder::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        // reads PHP attributes such as #[Assert\NotBlank]
        ->enableAttributeMapping()
        // calls this static method of the class (if it exists) to get its metadata;
        // use addMethodMappings() to pass several method names
        ->addMethodMapping('loadValidatorMetadata')
        // loading YAML files requires the symfony/yaml package;
        // use addYamlMappings() and addXmlMappings() to pass several files
        ->addYamlMapping('config/validator/validation.yaml')
        ->addXmlMapping('config/validator/validation.xml')
        ->getValidator();

The builder combines all these loaders in a
:class:`Symfony\\Component\\Validator\\Mapping\\Loader\\LoaderChain` and merges
the metadata that each of them returns for a class. Call
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::disableAttributeMapping`
to disable the attribute loader after enabling it.

Compile-Time Attribute Metadata
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 7.4

    Compile-time discovery of classes using constraint attributes was introduced
    in Symfony 7.4.

In Symfony applications that use
:ref:`autoconfiguration <services-autoconfigure>`, the classes that use
constraint attributes (such as ``#[Assert\NotBlank]``, ``#[Assert\Length]``,
etc.) are discovered at compile time. This allows the attribute loader to only
process the classes that are known to have constraint attributes, which
improves performance in production.

If you need to explicitly register a class that uses constraint attributes
(e.g. from a third-party library that is not part of your service
definitions), tag it with ``validator.attribute_metadata`` and
``container.excluded``:

.. code-block:: yaml

    # config/services.yaml
    services:
        Vendor\Library\SomeModel:
            tags:
                - { name: container.excluded }
                - { name: validator.attribute_metadata }

Caching the Metadata
~~~~~~~~~~~~~~~~~~~~

Without a cache, on every request the loaders parse the attributes and files
again and the validator merges their results into a
:class:`Symfony\\Component\\Validator\\Mapping\\ClassMetadata` object for each
validated class, which slows down your application. In Symfony applications,
this metadata is cached automatically when the ``kernel.debug`` parameter is
``false`` (e.g. in the ``prod`` environment). Outside a Symfony application,
pass any PSR-6 cache pool to
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::setMappingCache`::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        // ... add the loaders
        ->setMappingCache(new FilesystemAdapter())
        ->getValidator();

Using a Custom Metadata Factory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the builder passes all loaders and the cache to a
:class:`Symfony\\Component\\Validator\\Mapping\\Factory\\LazyLoadingMetadataFactory`,
which creates the ``ClassMetadata`` objects. To create them differently,
implement
:class:`Symfony\\Component\\Validator\\Mapping\\Factory\\MetadataFactoryInterface`
and pass an instance of your class to
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::setMetadataFactory`::

    use Acme\Validation\CustomMetadataFactory;
    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        ->setMetadataFactory(new CustomMetadataFactory(...))
        ->getValidator();

A custom metadata factory can't be combined with the builder methods that
configure the loaders and the cache (``add*Mapping()``, ``add*Mappings()``,
``enableAttributeMapping()`` and ``setMappingCache()``), so your factory must
load and cache the metadata on its own.

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

    /validation/*
