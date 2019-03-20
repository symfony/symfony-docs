.. index::
    single: Validation; Groups

How to Apply only a Subset of all Your Validation Constraints (Validation Groups)
=================================================================================

By default, when validating an object all constraints of this class will
be checked whether or not they actually pass. In some cases, however, you
will need to validate an object against only *some* constraints on that class.
To do this, you can organize each constraint into one or more "validation
groups" and then apply validation against just one group of constraints.

For example, suppose you have a ``User`` class, which is used both when a
user registers and when a user updates their contact information later:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/User.php
        namespace App\Entity;

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

        # config/validator/validation.yaml
        App\Entity\User:
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

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="
                http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd
            ">

            <class name="App\Entity\User">
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

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('email', new Assert\Email([
                    'groups' => ['registration'],
                ]));

                $metadata->addPropertyConstraint('password', new Assert\NotBlank([
                    'groups' => ['registration'],
                ]));
                $metadata->addPropertyConstraint('password', new Assert\Length([
                    'min'    => 7,
                    'groups' => ['registration'],
                ]));

                $metadata->addPropertyConstraint('city', new Assert\Length([
                    "min" => 3,
                ]));
            }
        }

With this configuration, there are three validation groups:

``Default``
    Contains the constraints in the current class and all referenced classes
    that belong to no other group. In this example, it only contains the
    ``city`` field.

``User``
    Equivalent to all constraints of the ``User`` object in the ``Default``
    group. This is always the name of the class. The difference between this
    and ``Default`` is explained in :doc:`/validation/sequence_provider`.

``registration``
    This is a custom validation group, so it only contains the constraints
    explicitly associated to it. In this example, only the ``email`` and
    ``password`` fields.

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

    $errors = $validator->validate($author, null, ['registration']);

If no groups are specified, all constraints that belong to the group ``Default``
will be applied.

In a full stack Symfony project, you'll usually work with validation indirectly through the form
library. For information on how to use validation groups inside forms, see
:doc:`/form/validation_groups`.
