Validation Groups
=================

Objects are usually validated evaluating all the constraints defined by their
classes. However, in some cases, you'll need to validate an object against only
*some* constraints on that class. To do this, you can organize each constraint
into one or more "validation groups", and then apply validation against just one
group of constraints.

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
as the second argument to the ``validate()`` method::

    $errors = $validator->validate($author, array('registration'));

If no groups are specified, all constraints that belong in group ``Default``
will be applied.

Of course, you'll usually work with validation indirectly through the form
library. In that case, you'll need to specify which validation group(s) your
form should use::

    $form = $this->createFormBuilder($users, array(
        'validation_groups' => array('registration'),
    ))->add(...);

If you're creating :ref:`form classes <book-form-creating-form-classes>` (a
good practice), then you'll need to add the following to the ``setDefaultOptions()``
method::

    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('registration'),
        ));
    }

In both of these cases, *only* the ``registration`` validation group will
be used to validate the underlying object.

Disabling Validation
--------------------

.. versionadded:: 2.3
    The ability to set ``validation_groups`` to false was introduced in Symfony 2.3.

Sometimes it is useful to suppress the validation of a form altogether. For
these cases you can set the ``validation_groups`` option to ``false``::

    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => false,
        ));
    }

Note that when you do that, the form will still run basic integrity checks,
for example whether an uploaded file was too large or whether non-existing
fields were submitted. If you want to suppress validation, you can use the
:ref:`POST_SUBMIT event <cookbook-dynamic-form-modification-suppressing-form-validation>`.

Validation Groups based on the Submitted Data
----------------------------------------------

If you need some advanced logic to determine the validation groups (e.g.
based on submitted data), you can set the ``validation_groups`` option
to an array callback::

    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    // ...
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array(
                'AppBundle\Entity\Client',
                'determineValidationGroups',
            ),
        ));
    }

This will call the static method ``determineValidationGroups()`` on the
``Client`` class after the form is submitted, but before validation is executed.
The Form object is passed as an argument to that method (see next example).
You can also define whole logic inline by using a ``Closure``::

    use AppBundle\Entity\Client;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    // ...
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();

                if (Client::TYPE_PERSON == $data->getType()) {
                    return array('person');
                }

                return array('company');
            },
        ));
    }

Using the ``validation_groups`` option overrides the default validation
group which is being used. If you want to validate the default constraints
of the entity as well you have to adjust the option as follows::

    use AppBundle\Entity\Client;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    // ...
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();

                if (Client::TYPE_PERSON == $data->getType()) {
                    return array('Default', 'person');
                }

                return array('Default', 'company');
            },
        ));
    }

Validation Groups based on the Clicked Button
----------------------------------------------

.. versionadded:: 2.3
    Support for buttons in forms was introduced in Symfony 2.3.

When your form contains multiple submit buttons, you can change the validation
group depending on which button is used to submit the form. For example,
consider a form in a wizard that lets you advance to the next step or go back
to the previous step. Also assume that when returning to the previous step,
the data of the form should be saved, but not validated.

First, we need to add the two buttons to the form::

    $form = $this->createFormBuilder($task)
        // ...
        ->add('nextStep', 'submit')
        ->add('previousStep', 'submit')
        ->getForm();

Then, we configure the button for returning to the previous step to run
specific validation groups. In this example, we want it to suppress validation,
so we set its ``validation_groups`` option to false::

    $form = $this->createFormBuilder($task)
        // ...
        ->add('previousStep', 'submit', array(
            'validation_groups' => false,
        ))
        ->getForm();

Now the form will skip your validation constraints. It will still validate
basic integrity constraints, such as checking whether an uploaded file was too
large or whether you tried to submit text in a number field.

Validation Group Sequence
-------------------------

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
             * @Assert\IsTrue(message="The password cannot match your username", groups={"Strict"})
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
                    - 'IsTrue':
                        message: 'The password cannot match your username'
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
                    <constraint name="IsTrue">
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

                $metadata->addGetterConstraint('passwordLegal', new Assert\IsTrue(array(
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

    As already explained in a previous section of this article, the ``Default``
    group and the group containing the class name (e.g. ``User``) were identical.
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
