How to Sequentially Apply Validation Groups
===========================================

In some cases, you want to validate your groups by steps. To do this, you can
use the ``GroupSequence`` feature. In this case, an object defines a group
sequence, which determines the order groups should be validated.

For example, suppose you have a ``User`` class and want to validate that the
username and the password are different only if all other validation passes
(in order to avoid multiple error messages).

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Security\Core\User\UserInterface;
        use Symfony\Component\Validator\Constraints as Assert;

        #[Assert\GroupSequence(['User', 'Strict'])]
        class User implements UserInterface
        {
            #[Assert\NotBlank]
            private string $username;

            #[Assert\NotBlank]
            private string $password;

            #[Assert\IsTrue(
                message: 'The password cannot match your username',
                groups: ['Strict'],
            )]
            public function isPasswordSafe(): bool
            {
                return ($this->username !== $this->password);
            }
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            group_sequence:
                - User
                - Strict
            getters:
                passwordSafe:
                    - 'IsTrue':
                        message: 'The password cannot match your username'
                        groups: [Strict]
            properties:
                username:
                    - NotBlank: ~
                password:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="username">
                    <constraint name="NotBlank"/>
                </property>

                <property name="password">
                    <constraint name="NotBlank"/>
                </property>

                <getter property="passwordSafe">
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

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('username', new Assert\NotBlank());
                $metadata->addPropertyConstraint('password', new Assert\NotBlank());

                $metadata->addGetterConstraint('passwordSafe', new Assert\IsTrue([
                    'message' => 'The password cannot match your first name',
                    'groups'  => ['Strict'],
                ]));

                $metadata->setGroupSequence(['User', 'Strict']);
            }
        }

In this example, it will first validate all constraints in the group ``User``
(which is the same as the ``Default`` group). Only if all constraints in
that group are valid, the second group, ``Strict``, will be validated.

.. caution::

    As you have already seen in :doc:`/validation/groups`, the ``Default`` group
    and the group containing the class name (e.g. ``User``) were identical.
    However, when using Group Sequences, they are no longer identical. The
    ``Default`` group will now reference the group sequence, instead of all
    constraints that do not belong to any group.

    This means that you have to use the ``{ClassName}`` (e.g. ``User``) group
    when specifying a group sequence. When using ``Default``, you get an
    infinite recursion (as the ``Default`` group references the group
    sequence, which will contain the ``Default`` group which references the
    same group sequence, ...).

.. caution::

    Calling ``validate()`` with a group in the sequence (``Strict`` in previous
    example) will cause a validation **only** with that group and not with all
    the groups in the sequence. This is because sequence is now referred to
    ``Default`` group validation.

You can also define a group sequence in the ``validation_groups`` form option::

    // src/Form/MyType.php
    namespace App\Form;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Validator\Constraints\GroupSequence;
    // ...

    class MyType extends AbstractType
    {
        // ...
        public function configureOptions(OptionsResolver $resolver): void
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

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            #[Assert\NotBlank]
            private string $name;

            #[Assert\CardScheme(
                schemes: [Assert\CardScheme::VISA],
                groups: ['Premium'],
            )]
            private string $creditCard;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                name:
                    - NotBlank: ~
                creditCard:
                    - CardScheme:
                        schemes: [VISA]
                        groups: [Premium]

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="name">
                    <constraint name="NotBlank"/>
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

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            private string $name;
            private string $creditCard;

            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('name', new Assert\NotBlank());
                $metadata->addPropertyConstraint('creditCard', new Assert\CardScheme([
                    'schemes' => [Assert\CardScheme::VISA],
                    'groups'  => ['Premium'],
                ]));
            }
        }

Now, change the ``User`` class to implement
:class:`Symfony\\Component\\Validator\\GroupSequenceProviderInterface` and
add the
:method:`Symfony\\Component\\Validator\\GroupSequenceProviderInterface::getGroupSequence`,
method, which should return an array of groups to use::

    // src/Entity/User.php
    namespace App\Entity;

    // ...
    use Symfony\Component\Validator\GroupSequenceProviderInterface;

    class User implements GroupSequenceProviderInterface
    {
        // ...

        public function getGroupSequence(): array|GroupSequence
        {
            // when returning a simple array, if there's a violation in any group
            // the rest of the groups are not validated. E.g. if 'User' fails,
            // 'Premium' and 'Api' are not validated:
            return ['User', 'Premium', 'Api'];

            // when returning a nested array, all the groups included in each array
            // are validated. E.g. if 'User' fails, 'Premium' is also validated
            // (and you'll get its violations too) but 'Api' won't be validated:
            return [['User', 'Premium'], 'Api'];
        }
    }

At last, you have to notify the Validator component that your ``User`` class
provides a sequence of groups to be validated:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        // ...

        #[Assert\GroupSequenceProvider]
        class User implements GroupSequenceProviderInterface
        {
            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            group_sequence_provider: true

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <group-sequence-provider/>
                <!-- ... -->
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User implements GroupSequenceProviderInterface
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->setGroupSequenceProvider(true);
                // ...
            }
        }

Advanced Validation Group Provider
----------------------------------

In the previous section, you learned how to change the sequence of groups
dynamically based on the state of your entity. However, in more advanced cases
you might need to use some external configuration or service to define that
sequence of groups.

Managing the entity initialization and manually setting its dependencies can
be cumbersome, and the implementation might not align with the entity
responsibilities. To solve this, you can configure the implementation of the
:class:`Symfony\\Component\\Validator\\GroupProviderInterface` outside of the
entity, and even register the group provider as a service.

Here's how you can achieve this:

 1) **Define a Separate Group Provider Class:** create a class that implements
    the :class:`Symfony\\Component\\Validator\\GroupProviderInterface`
    and handles the dynamic group sequence logic;
 2) **Configure the User with the Provider:** use the ``provider`` option within
    the :class:`Symfony\\Component\\Validator\\Constraints\\GroupSequenceProvider`
    attribute to link the entity with the provider class;
 3) **Autowiring or Manual Tagging:** if :doc:` autowiring </service_container/autowiring>`
    is enabled, your custom provider will be automatically linked. Otherwise, you must
    :doc:`tag your service </service_container/tags>` manually with the ``validator.group_provider`` tag.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        // ...
        use App\Validator\UserGroupProvider;

        #[Assert\GroupSequenceProvider(provider: UserGroupProvider::class)]
        class User
        {
            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            group_sequence_provider: App\Validator\UserGroupProvider

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <group-sequence-provider>
                    <value>App\Validator\UserGroupProvider</value>
                </group-sequence-provider>
                <!-- ... -->
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        // ...
        use App\Validator\UserGroupProvider;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->setGroupProvider(UserGroupProvider::class);
                $metadata->setGroupSequenceProvider(true);
                // ...
            }
        }

With this approach, you can maintain a clean separation between the entity
structure and the group sequence logic, allowing for more advanced use cases.

How to Sequentially Apply Constraints on a Single Property
----------------------------------------------------------

Sometimes, you may want to apply constraints sequentially on a single
property. The :doc:`Sequentially constraint </reference/constraints/Sequentially>`
can solve this for you in a more straightforward way than using a ``GroupSequence``.
