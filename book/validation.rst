.. index::
   single: Validation

Validation
==========

Validation is a very common task in web applications. Data entered in forms
needs to be validated. Data also needs to be validated before it is written
into a database or passed to a web service.

Symfony ships with a `Validator`_ component that makes this task easy and
transparent. This component is based on the
`JSR303 Bean Validation specification`_.

.. index::
   single: Validation; The basics

The Basics of Validation
------------------------

The best way to understand validation is to see it in action. To start, suppose
you've created a plain-old-PHP object that you need to use somewhere in
your application::

    // src/AppBundle/Entity/Author.php
    namespace AppBundle\Entity;

    class Author
    {
        public $name;
    }

So far, this is just an ordinary class that serves some purpose inside your
application. The goal of validation is to tell you if the data
of an object is valid. For this to work, you'll configure a list of rules
(called :ref:`constraints <validation-constraints>`) that the object must
follow in order to be valid. These rules can be specified via a number of
different formats (YAML, XML, annotations, or PHP).

For example, to guarantee that the ``$name`` property is not empty, add the
following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\NotBlank()
             */
            public $name;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                name:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="name">
                    <constraint name="NotBlank" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;

        class Author
        {
            public $name;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new NotBlank());
            }
        }

.. tip::

    Protected and private properties can also be validated, as well as "getter"
    methods (see :ref:`validator-constraint-targets`).

.. versionadded:: 2.7
    As of Symfony 2.7, XML and Yaml constraint files located in the
    ``Resources/config/validation`` sub-directory of a bundle are loaded. Prior
    to 2.7, only ``Resources/config/validation.yml`` (or ``.xml``) were loaded.

.. index::
   single: Validation; Using the validator

Using the ``validator`` Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, to actually validate an ``Author`` object, use the ``validate`` method
on the ``validator`` service (class :class:`Symfony\\Component\\Validator\\Validator`).
The job of the ``validator`` is easy: to read the constraints (i.e. rules)
of a class and verify if the data on the object satisfies those
constraints. If validation fails, a non-empty list of errors
(class :class:`Symfony\\Component\\Validator\\ConstraintViolationList`) is
returned. Take this simple example from inside a controller::

    // ...
    use Symfony\Component\HttpFoundation\Response;
    use AppBundle\Entity\Author;

    // ...
    public function authorAction()
    {
        $author = new Author();

        // ... do something to the $author object

        $validator = $this->get('validator');
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

    AppBundle\Author.name:
        This value should not be blank

If you insert a value into the ``name`` property, the happy success message
will appear.

.. tip::

    Most of the time, you won't interact directly with the ``validator``
    service or need to worry about printing out the errors. Most of the time,
    you'll use validation indirectly when handling submitted form data. For
    more information, see the :ref:`book-validation-forms`.

You could also pass the collection of errors into a template::

    if (count($errors) > 0) {
        return $this->render('author/validation.html.twig', array(
            'errors' => $errors,
        ));
    }

Inside the template, you can output the list of errors exactly as needed:

.. configuration-block::

    .. code-block:: html+jinja

        {# app/Resources/views/author/validation.html.twig #}
        <h3>The author has the following errors</h3>
        <ul>
        {% for error in errors %}
            <li>{{ error.message }}</li>
        {% endfor %}
        </ul>

    .. code-block:: html+php

        <!-- app/Resources/views/author/validation.html.php -->
        <h3>The author has the following errors</h3>
        <ul>
        <?php foreach ($errors as $error): ?>
            <li><?php echo $error->getMessage() ?></li>
        <?php endforeach ?>
        </ul>

.. note::

    Each validation error (called a "constraint violation"), is represented by
    a :class:`Symfony\\Component\\Validator\\ConstraintViolation` object.

.. index::
   single: Validation; Validation with forms

.. _book-validation-forms:

Validation and Forms
~~~~~~~~~~~~~~~~~~~~

The ``validator`` service can be used at any time to validate any object.
In reality, however, you'll usually work with the ``validator`` indirectly
when working with forms. Symfony's form library uses the ``validator`` service
internally to validate the underlying object after values have been submitted.
The constraint violations on the object are converted into ``FormError``
objects that can easily be displayed with your form. The typical form submission
workflow looks like the following from inside a controller::

    // ...
    use AppBundle\Entity\Author;
    use AppBundle\Form\AuthorType;
    use Symfony\Component\HttpFoundation\Request;

    // ...
    public function updateAction(Request $request)
    {
        $author = new Author();
        $form = $this->createForm(new AuthorType(), $author);

        $form->handleRequest($request);

        if ($form->isValid()) {
            // the validation passed, do something with the $author object

            return $this->redirectToRoute(...);
        }

        return $this->render('author/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

.. note::

    This example uses an ``AuthorType`` form class, which is not shown here.

For more information, see the :doc:`Forms </book/forms>` chapter.

.. index::
   pair: Validation; Configuration

.. _book-validation-configuration:

Configuration
-------------

The Symfony validator is enabled by default, but you must explicitly enable
annotations if you're using the annotation method to specify your constraints:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            validation: { enable_annotations: true }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:validation enable-annotations="true" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'validation' => array(
                'enable_annotations' => true,
            ),
        ));

.. index::
   single: Validation; Constraints

.. _validation-constraints:

Constraints
-----------

The ``validator`` is designed to validate objects against *constraints* (i.e.
rules). In order to validate an object, simply map one or more constraints
to its class and then pass it to the ``validator`` service.

Behind the scenes, a constraint is simply a PHP object that makes an assertive
statement. In real life, a constraint could be: "The cake must not be burned".
In Symfony, constraints are similar: they are assertions that a condition
is true. Given a value, a constraint will tell you if that value
adheres to the rules of the constraint.

Supported Constraints
~~~~~~~~~~~~~~~~~~~~~

Symfony packages many of the most commonly-needed constraints:

.. include:: /reference/constraints/map.rst.inc

You can also create your own custom constraints. This topic is covered in
the ":doc:`/cookbook/validation/custom_constraint`" article of the cookbook.

.. index::
   single: Validation; Constraints configuration

.. _book-validation-constraint-configuration:

Constraint Configuration
~~~~~~~~~~~~~~~~~~~~~~~~

Some constraints, like :doc:`NotBlank </reference/constraints/NotBlank>`,
are simple whereas others, like the :doc:`Choice </reference/constraints/Choice>`
constraint, have several configuration options available. Suppose that the
``Author`` class has another property called ``gender`` that can be set to either
"male", "female" or "other":

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice(
             *     choices = { "male", "female", "other" },
             *     message = "Choose a valid gender."
             * )
             */
            public $gender;

            // ...
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                gender:
                    - Choice: { choices: [male, female, other], message: Choose a valid gender. }
                # ...

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="gender">
                    <constraint name="Choice">
                        <option name="choices">
                            <value>male</value>
                            <value>female</value>
                            <value>other</value>
                        </option>
                        <option name="message">Choose a valid gender.</option>
                    </constraint>
                </property>

                <!-- ... -->
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public $gender;

            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                // ...

                $metadata->addPropertyConstraint('gender', new Assert\Choice(array(
                    'choices' => array('male', 'female', 'other'),
                    'message' => 'Choose a valid gender.',
                )));
            }
        }

.. _validation-default-option:

The options of a constraint can always be passed in as an array. Some constraints,
however, also allow you to pass the value of one, "*default*", option in place
of the array. In the case of the ``Choice`` constraint, the ``choices``
options can be specified in this way.

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Choice({"male", "female", "other"})
             */
            protected $gender;

            // ...
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                gender:
                    - Choice: [male, female, other]
                # ...

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="gender">
                    <constraint name="Choice">
                        <value>male</value>
                        <value>female</value>
                        <value>other</value>
                    </constraint>
                </property>

                <!-- ... -->
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $gender;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                // ...

                $metadata->addPropertyConstraint(
                    'gender',
                    new Assert\Choice(array('male', 'female', 'other'))
                );
            }
        }

This is purely meant to make the configuration of the most common option of
a constraint shorter and quicker.

If you're ever unsure of how to specify an option, either check the API documentation
for the constraint or play it safe by always passing in an array of options
(the first method shown above).

Translation Constraint Messages
-------------------------------

For information on translating the constraint messages, see
:ref:`book-translation-constraint-messages`.

.. index::
   single: Validation; Constraint targets

.. _validator-constraint-targets:

Constraint Targets
------------------

Constraints can be applied to a class property (e.g. ``name``) or a public
getter method (e.g. ``getFullName``). The first is the most common and easy
to use, but the second allows you to specify more complex validation rules.

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

    .. code-block:: php-annotations

        // AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\NotBlank()
             * @Assert\Length(min=3)
             */
            private $firstName;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                firstName:
                    - NotBlank: ~
                    - Length:
                        min: 3

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="firstName">
                    <constraint name="NotBlank" />
                    <constraint name="Length">
                        <option name="min">3</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            private $firstName;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new Assert\NotBlank());
                $metadata->addPropertyConstraint(
                    'firstName',
                    new Assert\Length(array("min" => 3))
                );
            }
        }

.. index::
   single: Validation; Getter constraints

Getters
~~~~~~~

Constraints can also be applied to the return value of a method. Symfony
allows you to add a constraint to any public method whose name starts with
"get", "is" or "has". In this guide, these types of methods are referred to
as "getters".

The benefit of this technique is that it allows you to validate your object
dynamically. For example, suppose you want to make sure that a password field
doesn't match the first name of the user (for security reasons). You can
do this by creating an ``isPasswordLegal`` method, and then asserting that
this method must return ``true``:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\True(message = "The password cannot match your first name")
             */
            public function isPasswordLegal()
            {
                // ... return true or false
            }
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            getters:
                passwordLegal:
                    - "True": { message: "The password cannot match your first name" }

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <getter property="passwordLegal">
                    <constraint name="True">
                        <option name="message">The password cannot match your first name</option>
                    </constraint>
                </getter>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addGetterConstraint('passwordLegal', new Assert\True(array(
                    'message' => 'The password cannot match your first name',
                )));
            }
        }

Now, create the ``isPasswordLegal()`` method and include the logic you need::

    public function isPasswordLegal()
    {
        return $this->firstName !== $this->password;
    }

.. note::

    The keen-eyed among you will have noticed that the prefix of the getter
    ("get", "is" or "has") is omitted in the mapping. This allows you to move
    the constraint to a property with the same name later (or vice versa)
    without changing your validation logic.

.. _validation-class-target:

Classes
~~~~~~~

Some constraints apply to the entire class being validated. For example,
the :doc:`Callback </reference/constraints/Callback>` constraint is a generic
constraint that's applied to the class itself. When that class is validated,
methods specified by that constraint are simply executed so that each can
provide more custom validation.

.. _book-validation-validation-groups:

Validation Groups
-----------------

So far, you've been able to add constraints to a class and ask whether or
not that class passes all the defined constraints. In some cases, however,
you'll need to validate an object against only *some* constraints
on that class. To do this, you can organize each constraint into one or more
"validation groups", and then apply validation against just one group of
constraints.

For example, suppose you have a ``User`` class, which is used both when a
user registers and when a user updates their contact information later:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        use Symfony\Component\Security\Core\User\UserInterface;
        use Symfony\Component\Validator\Constraints as Assert;

        class User implements UserInterface
        {
            /**
            * @Assert\Email(groups={"registration"})
            */
            private $email;

            /**
            * @Assert\NotBlank(groups={"registration"})
            * @Assert\Length(min=7, groups={"registration"})
            */
            private $password;

            /**
            * @Assert\Length(min=2)
            */
            private $city;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\User:
            properties:
                email:
                    - Email: { groups: [registration] }
                password:
                    - NotBlank: { groups: [registration] }
                    - Length: { min: 7, groups: [registration] }
                city:
                    - Length:
                        min: 2

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="
                http://symfony.com/schema/dic/constraint-mapping
                http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd
            ">

            <class name="AppBundle\Entity\User">
                <property name="email">
                    <constraint name="Email">
                        <option name="groups">
                            <value>registration</value>
                        </option>
                    </constraint>
                </property>

                <property name="password">
                    <constraint name="NotBlank">
                        <option name="groups">
                            <value>registration</value>
                        </option>
                    </constraint>
                    <constraint name="Length">
                        <option name="min">7</option>
                        <option name="groups">
                            <value>registration</value>
                        </option>
                    </constraint>
                </property>

                <property name="city">
                    <constraint name="Length">
                        <option name="min">7</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('email', new Assert\Email(array(
                    'groups' => array('registration'),
                )));

                $metadata->addPropertyConstraint('password', new Assert\NotBlank(array(
                    'groups' => array('registration'),
                )));
                $metadata->addPropertyConstraint('password', new Assert\Length(array(
                    'min'    => 7,
                    'groups' => array('registration'),
                )));

                $metadata->addPropertyConstraint('city', new Assert\Length(array(
                    "min" => 3,
                )));
            }
        }

With this configuration, there are three validation groups:

``Default``
    Contains the constraints in the current class and all referenced classes
    that belong to no other group.

``User``
    Equivalent to all constraints of the ``User`` object in the ``Default``
    group. This is always the name of the class. The difference between this
    and ``Default`` is explained below.

``registration``
    Contains the constraints on the ``email`` and ``password`` fields only.

Constraints in the ``Default`` group of a class are the constraints that have
either no explicit group configured or that are configured to a group equal to
the class name or the string ``Default``.

.. caution::

    When validating *just* the User object, there is no difference between the
    ``Default`` group and the ``User`` group. But, there is a difference if
    ``User`` has embedded objects. For example, imagine ``User`` has an
    ``address`` property that contains some ``Address`` object and that you've
    added the :doc:`/reference/constraints/Valid` constraint to this property
    so that it's validated when you validate the ``User`` object.

    If you validate ``User`` using the ``Default`` group, then any constraints
    on the ``Address`` class that are in the ``Default`` group *will* be used.
    But, if you validate ``User`` using the ``User`` validation group, then
    only constraints on the ``Address`` class with the ``User`` group will be
    validated.

    In other words, the ``Default`` group and the class name group (e.g.
    ``User``) are identical, except when the class is embedded in another
    object that's actually the one being validated.

    If you have inheritance (e.g. ``User extends BaseUser``) and you validate
    with the class name of the subclass (i.e. ``User``), then all constraints
    in the ``User`` and ``BaseUser`` will be validated. However, if you
    validate using the base class (i.e. ``BaseUser``), then only the default
    constraints in the ``BaseUser`` class will be validated.

To tell the validator to use a specific group, pass one or more group names
as the third argument to the ``validate()`` method::

    // If you're using the new 2.5 validation API (you probably are!)
    $errors = $validator->validate($author, null, array('registration'));

    // If you're using the old 2.4 validation API, pass the group names as the second argument
    // $errors = $validator->validate($author, array('registration'));

If no groups are specified, all constraints that belong to the group ``Default``
will be applied.

Of course, you'll usually work with validation indirectly through the form
library. For information on how to use validation groups inside forms, see
:ref:`book-forms-validation-groups`.

.. index::
   single: Validation; Validating raw values

.. _book-validation-group-sequence:

Group Sequence
--------------

In some cases, you want to validate your groups by steps. To do this, you can
use the ``GroupSequence`` feature. In this case, an object defines a group
sequence, which determines the order groups should be validated.

For example, suppose you have a ``User`` class and want to validate that the
username and the password are different only if all other validation passes
(in order to avoid multiple error messages).

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        use Symfony\Component\Security\Core\User\UserInterface;
        use Symfony\Component\Validator\Constraints as Assert;

        /**
         * @Assert\GroupSequence({"User", "Strict"})
         */
        class User implements UserInterface
        {
            /**
            * @Assert\NotBlank
            */
            private $username;

            /**
            * @Assert\NotBlank
            */
            private $password;

            /**
             * @Assert\True(message="The password cannot match your username", groups={"Strict"})
             */
            public function isPasswordLegal()
            {
                return ($this->username !== $this->password);
            }
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\User:
            group_sequence:
                - User
                - Strict
            getters:
                passwordLegal:
                    - "True":
                        message: "The password cannot match your username"
                        groups: [Strict]
            properties:
                username:
                    - NotBlank: ~
                password:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\User">
                <property name="username">
                    <constraint name="NotBlank" />
                </property>

                <property name="password">
                    <constraint name="NotBlank" />
                </property>

                <getter property="passwordLegal">
                    <constraint name="True">
                        <option name="message">The password cannot match your username</option>
                        <option name="groups">
                            <value>Strict</value>
                        </option>
                    </constraint>
                </getter>

                <group-sequence>
                    <value>User</value>
                    <value>Strict</value>
                </group-sequence>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('username', new Assert\NotBlank());
                $metadata->addPropertyConstraint('password', new Assert\NotBlank());

                $metadata->addGetterConstraint('passwordLegal', new Assert\True(array(
                    'message' => 'The password cannot match your first name',
                    'groups'  => array('Strict'),
                )));

                $metadata->setGroupSequence(array('User', 'Strict'));
            }
        }

In this example, it will first validate all constraints in the group ``User``
(which is the same as the ``Default`` group). Only if all constraints in
that group are valid, the second group, ``Strict``, will be validated.

.. caution::

    As you have already seen in the previous section, the ``Default`` group
    and the group containing the class name (e.g. ``User``) were identical.
    However, when using Group Sequences, they are no longer identical. The
    ``Default`` group will now reference the group sequence, instead of all
    constraints that do not belong to any group.

    This means that you have to use the ``{ClassName}`` (e.g. ``User``) group
    when specifying a group sequence. When using ``Default``, you get an
    infinite recursion (as the ``Default`` group references the group
    sequence, which will contain the ``Default`` group which references the
    same group sequence, ...).

Group Sequence Providers
~~~~~~~~~~~~~~~~~~~~~~~~

Imagine a ``User`` entity which can be a normal user or a premium user. When
it's a premium user, some extra constraints should be added to the user entity
(e.g. the credit card details). To dynamically determine which groups should
be activated, you can create a Group Sequence Provider. First, create the
entity and a new constraint group called ``Premium``:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            /**
             * @Assert\NotBlank()
             */
            private $name;

            /**
             * @Assert\CardScheme(
             *     schemes={"VISA"},
             *     groups={"Premium"},
             * )
             */
            private $creditCard;

            // ...
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\User:
            properties:
                name:
                    - NotBlank: ~
                creditCard:
                    - CardScheme:
                        schemes: [VISA]
                        groups: [Premium]

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\User">
                <property name="name">
                    <constraint name="NotBlank" />
                </property>

                <property name="creditCard">
                    <constraint name="CardScheme">
                        <option name="schemes">
                            <value>VISA</value>
                        </option>
                        <option name="groups">
                            <value>Premium</value>
                        </option>
                    </constraint>
                </property>

                <!-- ... -->
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            private $name;
            private $creditCard;

            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new Assert\NotBlank());
                $metadata->addPropertyConstraint('creditCard', new Assert\CardScheme(
                    'schemes' => array('VISA'),
                    'groups'  => array('Premium'),
                ));
            }
        }

Now, change the ``User`` class to implement
:class:`Symfony\\Component\\Validator\\GroupSequenceProviderInterface` and
add the
:method:`Symfony\\Component\\Validator\\GroupSequenceProviderInterface::getGroupSequence`,
method, which should return an array of groups to use::

    // src/AppBundle/Entity/User.php
    namespace AppBundle\Entity;

    // ...
    use Symfony\Component\Validator\GroupSequenceProviderInterface;

    class User implements GroupSequenceProviderInterface
    {
        // ...

        public function getGroupSequence()
        {
            $groups = array('User');

            if ($this->isPremium()) {
                $groups[] = 'Premium';
            }

            return $groups;
        }
    }

At last, you have to notify the Validator component that your ``User`` class
provides a sequence of groups to be validated:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        // ...

        /**
         * @Assert\GroupSequenceProvider
         */
        class User implements GroupSequenceProviderInterface
        {
            // ...
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\User:
            group_sequence_provider: true

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\User">
                <group-sequence-provider />
                <!-- ... -->
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User implements GroupSequenceProviderInterface
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->setGroupSequenceProvider(true);
                // ...
            }
        }

.. _book-validation-raw-values:

Validating Values and Arrays
----------------------------

So far, you've seen how you can validate entire objects. But sometimes, you
just want to validate a simple value - like to verify that a string is a valid
email address. This is actually pretty easy to do. From inside a controller,
it looks like this::

    // ...
    use Symfony\Component\Validator\Constraints as Assert;

    // ...
    public function addEmailAction($email)
    {
        $emailConstraint = new Assert\Email();
        // all constraint "options" can be set this way
        $emailConstraint->message = 'Invalid email address';

        // use the validator to validate the value
        // If you're using the new 2.5 validation API (you probably are!)
        $errorList = $this->get('validator')->validate(
            $email,
            $emailConstraint
        );

        // If you're using the old 2.4 validation API
        /*
        $errorList = $this->get('validator')->validateValue(
            $email,
            $emailConstraint
        );
        */

        if (0 === count($errorList)) {
            // ... this IS a valid email address, do something
        } else {
            // this is *not* a valid email address
            $errorMessage = $errorList[0]->getMessage();

            // ... do something with the error
        }

        // ...
    }

By calling ``validate`` on the validator, you can pass in a raw value and
the constraint object that you want to validate that value against. A full
list of the available constraints - as well as the full class name for each
constraint - is available in the :doc:`constraints reference </reference/constraints>`
section.

The ``validate`` method returns a :class:`Symfony\\Component\\Validator\\ConstraintViolationList`
object, which acts just like an array of errors. Each error in the collection
is a :class:`Symfony\\Component\\Validator\\ConstraintViolation` object,
which holds the error message on its ``getMessage`` method.

Final Thoughts
--------------

The Symfony ``validator`` is a powerful tool that can be leveraged to
guarantee that the data of any object is "valid". The power behind validation
lies in "constraints", which are rules that you can apply to properties or
getter methods of your object. And while you'll most commonly use the validation
framework indirectly when using forms, remember that it can be used anywhere
to validate any object.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/validation/custom_constraint`

.. _Validator: https://github.com/symfony/Validator
.. _JSR303 Bean Validation specification: http://jcp.org/en/jsr/detail?id=303
