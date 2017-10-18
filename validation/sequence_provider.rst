.. index::
    single: Validation; Group Sequences
    single: Validation; Group Sequence Providers

How to Sequentially Apply Validation Groups
===========================================

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

You can also define a group sequence in the ``validation_groups`` form option::

    use Symfony\Component\Validator\Constraints\GroupSequence;
    use Symfony\Component\Form\AbstractType;
    // ...
    
    class MyType extends AbstractType
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'validation_groups' => new GroupSequence(['First', 'Second']),
            ]);
        }
    }

Group Sequence Providers
------------------------

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
                $metadata->addPropertyConstraint('creditCard', new Assert\CardScheme(array(
                    'schemes' => array('VISA'),
                    'groups'  => array('Premium'),
                )));
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
