.. index::
   single: Validation

Validation
==========

Validation is a very common task in web applications. Data entered in forms
needs to be validated. Data also needs to be validated before it is written
into a database or passed to a web service.

Symfony2 ships with a `Validator`_ component that makes this task easy and transparent.
This component is based on the `JSR303 Bean Validation specification`_. What?
A Java specification in PHP? You heard right, but it's not as bad as it sounds.
Let's look at how it can be used in PHP.

.. index:
   single: Validation; The basics

The Basics of Validation
------------------------

The best way to understand validation is to see it in action. To start, suppose
you've created a plain-old-PHP object that you need to use somewhere in
your application:

.. code-block:: php

    // Acme/BlogBundle/Author.php
    class Author
    {
        public $name;
    }

So far, this is just an ordinary class that serves some purpose inside your
application. The goal of validation is to tell you whether or not the data
of an object is valid. For this to work, you need to configure a list of
rules (called :ref:`constraints<validation-constraints>`) that the object
must follow in order to be valid. These rules can be specified via a number
of different formats (YAML, XML, annotations, or PHP). To guarantee that
the ``$name`` property is not empty, add the following:

.. configuration-block::

    .. code-block:: yaml

        # Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Author:
            properties:
                name:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Author">
            <property name="name">
                <constraint name="NotBlank" />
            </property>
        </class>

    .. code-block:: php-annotations

        // Acme/BlogBundle/Author.php
        class Author
        {
            /**
             * @assert:NotBlank()
             */
            public $name;
        }

    .. code-block:: php

        // Acme/BlogBundle/Author.php
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
    methods (see `validator-constraint-targets`).

.. index::
   single: Validation; Using the validator

Using the ``validator`` Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To actually validate an ``Author`` object, use the ``validate`` method
on the ``validator`` service (class :class:`Symfony\\Component\\Validator\\Validator`).
The job of the ``validator`` is easy: to read the constraints (i.e. rules)
of a class and verify whether or not the data on the object satisfies those
constraints. If validation fails, an array of errors is returned. Take this
simple example from inside a controller:

.. code-block:: php

    use Symfony\Component\HttpFoundation\Response;
    // ...

    public function indexAction()
    {
        $author = new Acme\BlogBundle\Author();
        // ... do something to the $author object

        $validator = $container->get('validator');
        $errorList = $validator->validate($author);

        if (count($errorList) > 0) {
            return new Response(print_r($errorList, true));
        } else {
            return new Response('The author is valid! Yes!');
        }
    }

If the ``$name`` property is empty, you will see the following error
message:

.. code-block:: text

    Acme\BlogBundle\Author.name:
        This value should not be blank

If you insert a value into the ``name`` property, the happy success message
will appear.

Each validation error (called a "constraint violation"), is represented by
a :class:`Symfony\\Component\\Validator\\ConstraintViolation` object, which
holds a message describing the error. Moreover, the ``validate`` method returns
a :class:`Symfony\\Component\\Validator\\ConstraintViolationList` object,
which acts like an array. That's a long way of saying that you can use the
errors returned by ``validate`` in more advanced ways. Start by rendering
a template and passing in the ``$errorList`` variable:

.. code-block:: php

    if (count($errorList) > 0) {
        return $this->render('AcmeBlog:Author:validate.html.twig', array(
            'errorList' => $errorList,
        ));
    } else {
        // ...
    }

Inside the template, you can output the list of errors exactly as needed:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/BlogBundle/Resources/views/Author/validate.html.twig #}

        <h3>The author has the following errors</h3>
        <ul>
        {% for error in errorList %}
            <li>{{ error.message }}</li>
        {% endfor %}
        </ul>

    .. code-block:: html+php

        <!-- src/Acme/BlogBundle/Resources/views/Author/validate.html.php -->

        <h3>The author has the following errors</h3>
        <ul>
        <?php foreach ($errorList as $error): ?>
            <li><?php echo $error->getMessage() ?></li>
        <?php endforeach; ?>
        </ul>

.. index::
   single: Validation; Validation with forms

Validation and Forms
~~~~~~~~~~~~~~~~~~~~

The ``validator`` service can be used at any time to validate any object.
In reality, however, you'll usually work with the ``validator`` indirectly
via the ``Form`` class. The ``Form`` class uses the ``validator`` service
internally to validate the underlying object after values have been submitted
and bound. The constraint violations on the object are converted into ``FieldError``
objects that can then be displayed with your form:

.. code-block:: php

    $author = new Acme\BlogBundle\Author();
    $form = new Acme\BlogBundle\AuthorForm('author', $author, $this->get('validator'));
    $form->bind($this->get('request')->request->get('customer'));

    if ($form->isValid()) {
        // process the Author object
    } else {
        // render the template with the errors
        $this->render('AcmeBlog:Author:form.html.twig', array('form' => $form));
    }

For more information, see the :doc:`Forms</book/forms/overview>` chapter.

.. index::
   pair: Validation; Configuration

Configuration
-------------

To use the Symfony2 validator, ensure that it's enable in your application
configuration:

.. configuration-block::

    .. code-block:: yaml

        # hello/config/config.yml
        framework:
            validation: { enabled: true, annotations: true }


    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:validation enabled="true" annotations="true" />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array('validation' => array(
            'enabled'     => true,
            'annotations' => true,
        ));

.. note::

    The ``annotations`` configuration needs to be set to ``true`` only if
    you're mapping constraints via annotations.

.. index::
   single: Validation; Constraints

.. _validation-constraints:

Constraints
-----------

The ``validator`` is designed to validate objects against *constraints* (i.e.
rules). In order to validate an object, simply map one or more constraints
to its class and then pass it to the ``validator`` service.

A constraint is simply a PHP object that makes an assertive statement. In
real life, a constraint could be: "The cake must not be burned". In Symfony2,
constraints are similar: they are assertions that a condition is true. Given
a value, a constraint will tell you whether or not that value adheres to
the rules of the constraint.

Supported Constraints
~~~~~~~~~~~~~~~~~~~~~

Symfony2 packages a large number of the most commonly-needed constraints.
The full list of constraints with details is available in the
:doc:`constraints reference section</reference/constraints>`.

.. index::
   single: Validation; Constraints configuration

Constraint Configuration
~~~~~~~~~~~~~~~~~~~~~~~~

Some constraints, like :doc:`NotBlank</reference/constraints/NotBlank>`,
are simple whereas others, like the :doc:`Choice</reference/constraints/Choice>`
constraint, have several configuration options available. The available
options are public properties on the constraint and each can be set by passing
an options array to the constraint. Suppose that the ``Author`` class has
another property, ``gender`` that can be set to either "male" or "female":

.. configuration-block::

    .. code-block:: yaml

        # Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Author:
            properties:
                gender:
                    - Choice: { choices: [male, female], message: Choose a valid gender. }

    .. code-block:: xml

        <!-- Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Author">
            <property name="gender">
                <constraint name="Choice">
                    <option name="choices">
                        <value>male</value>
                        <value>female</value>
                    </option>
                    <option name="message">Choose a valid gender.</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // Acme/BlogBundle/Author.php
        class Author
        {
            /**
             * @assert:Choice(
             *     choices = { "male", "female" },
             *     message = "Choose a valid gender."
             * )
             */
            public $gender;
        }

    .. code-block:: php

        // Acme/BlogBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;

        class Author
        {
            public $gender;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('gender', new Choice(array(
                    'choices' => array('male', 'female'),
                    'message' => 'Choose a valid gender.',
                ));
            }
        }

The options of a constraint can always be passed in as an array. Some constraints
also allow you to pass the value of one, "default", option to the constraint
in place of the array. In the case of the ``Choice`` constraint, the ``choices``
options can be specified in this way.

.. configuration-block::

    .. code-block:: yaml

        # Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Author:
            properties:
                gender:
                    - Choice: [male, female]

    .. code-block:: xml

        <!-- Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Author">
            <property name="gender">
                <constraint name="Choice">
                    <value>male</value>
                    <value>female</value>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // Acme/BlogBundle/Author.php
        class Author
        {
            /**
             * @assert:Choice({"male", "female"})
             */
            protected $gender;
        }

    .. code-block:: php

        // Acme/BlogBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\Choice;

        class Author
        {
            protected $gender;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('gender', new Choice(array('male', 'female')));
            }
        }

Be sure not to let the two different methods of specifying options confuse
you. If you're unsure, either check the API documentation for the constraint
or play it safe by always passing in an array of options (the first method
shown above).

.. index::
   single: Validation; Constraint targets

.. _validator-constraint-targets:

Constraint Targets
------------------

Constraints can be applied to a class property or a public getter method
(e.g. ``getFullName``).

.. index::
   single: Validation; Property constraints

Properties
~~~~~~~~~~

Validating class properties is the most basic validation technique. Symfony2
allows you to validate private, protected or public properties. The next
listing shows you how to configure the properties ``$firstName`` and ``$lastName``
of a class ``Author`` to have at least 3 characters.

.. configuration-block::

    .. code-block:: yaml

        # Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Author:
            properties:
                firstName:
                    - NotBlank: ~
                    - MinLength: 3
                lastName:
                    - NotBlank: ~
                    - MinLength: 3

    .. code-block:: xml

        <!-- Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Author">
            <property name="firstName">
                <constraint name="NotBlank" />
                <constraint name="MinLength">3</constraint>
            </property>
            <property name="lastName">
                <constraint name="NotBlank" />
                <constraint name="MinLength">3</constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // Acme/BlogBundle/Author.php
        class Author
        {
            /**
             * @assert:NotBlank()
             * @assert:MinLength(3)
             */
            private $firstName;

            /**
             * @assert:NotBlank()
             * @assert:MinLength(3)
             */
            private $lastName;
        }

    .. code-block:: php

        // Acme/BlogBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Constraints\MinLength;

        class Author
        {
            private $firstName;

            private $lastName;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new NotBlank());
                $metadata->addPropertyConstraint('firstName', new MinLength(3));
                $metadata->addPropertyConstraint('lastName', new NotBlank());
                $metadata->addPropertyConstraint('lastName', new MinLength(3));
            }
        }

.. index::
   single: Validation; Getter constraints

Getters
~~~~~~~

Constraints can also be applied to the return value of a method. Symfony2
allows you to add a constraint to any public method whose name starts with
"get" or "is". In this guide, both of these types of methods are referred
to as "getters".

The benefit of this technique is that it allows you to validate your object
dynamically. Depending on the state of your object, the method may return
different values which are then validated.

The next listing shows you how to use the :doc:`True</reference/constraints/True>`
constraint to validate whether a dynamically generated token is correct:

.. configuration-block::

    .. code-block:: yaml

        # Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Author:
            getters:
                tokenValid:
                    - True: { message: "The token is invalid" }

    .. code-block:: xml

        <!-- Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Author">
            <getter property="tokenValid">
                <constraint name="True">
                    <option name="message">The token is invalid</option>
                </constraint>
            </getter>
        </class>

    .. code-block:: php-annotations

        // Acme/BlogBundle/Author.php
        class Author
        {
            /**
             * @assert:True(message = "The token is invalid")
             */
            public function isTokenValid()
            {
                // return true or false
            }
        }

    .. code-block:: php

        // Acme/BlogBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\True;

        class Author
        {

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addGetterConstraint('tokenValid', new True(array(
                    'message' => 'The token is invalid',
                )));
            }

            public function isTokenValid()
            {
                // return true or false
            }
        }

The public ``isTokenValid`` method will perform any logic to determine if
the internal token is valid and then return ``true`` or ``false``.

.. note::

    The keen-eyed among you will have noticed that the prefix of the getter
    ("get" or "is") is omitted in the mapping. This allows you to move the
    constraint to a property with the same name later (or vice versa) without
    changing your validation logic.

Final Thoughts
--------------

The Symfony2 ``validator`` is a powerful tool that can be leveraged to
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