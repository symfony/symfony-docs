UniqueEntity
============

Validates that a particular field (or fields) in a Doctrine entity is (are)
unique. This is commonly used, for example, to prevent a new user from registering
with an email address that already exists in the system.

.. seealso::

    If you want to validate that all the elements of the collection are unique
    use the :doc:`Unique constraint </reference/constraints/Unique>`.

.. note::

    In order to use this constraint, you should have installed the
    symfony/doctrine-bridge with Composer.

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>`
Class       :class:`Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntity`
Validator   :class:`Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator`
==========  ===================================================================

Basic Usage
-----------

Suppose you have a ``User`` entity that has an ``email`` field. You can use the
``UniqueEntity`` constraint to guarantee that the ``email`` field remains unique
between all of the rows in your user table:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Constraints as Assert;

        #[ORM\Entity]
        #[UniqueEntity('email')]
        class User
        {
            #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true)]
            #[Assert\Email]
            protected string $email;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity:
                    fields: email
            properties:
                email:
                    - Email: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity">
                    <option name="fields">email</option>
                </constraint>
                <property name="email">
                    <constraint name="Email"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true)]
            protected string $email;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addConstraint(new UniqueEntity(
                    fields: 'email',
                ));

                $metadata->addPropertyConstraint('email', new Assert\Email());
            }
        }

        // src/Form/Type/UserType.php
        namespace App\Form\Type;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Form\AbstractType;
        use Symfony\Component\OptionsResolver\OptionsResolver;

        class UserType extends AbstractType
        {
            // ...

            public function configureOptions(OptionsResolver $resolver): void
            {
                $resolver->setDefaults([
                    // ...
                    'data_class' => User::class,
                    'constraints' => [
                        new UniqueEntity(fields: ['email']),
                    ],
                ]);
            }
        }

.. warning::

    This constraint does not support Doctrine types like ``simple_array``,
    ``json``, or ``jsonb``, nor does it handle association mappings such as
    ``OneToMany`` or ``ManyToMany`` relationships. It may also not work
    correctly with custom Doctrine types. To check uniqueness for these fields,
    define a custom ``repositoryMethod`` that implements the required logic.

.. warning::

    This constraint doesn't provide any protection against `race conditions`_.
    They may occur when another entity is persisted by an external process after
    this validation has passed and before this entity is actually persisted in
    the database.

.. warning::

    This constraint cannot deal with duplicates found in a collection of items
    that haven't been persisted as entities yet. You'll need to create your own
    validator to handle that case.

.. _reference-constraints-unique-entity-php-class:

Using a PHP class
-----------------

Instead of applying the ``UniqueEntity`` constraint directly to a Doctrine
entity, you can apply it to any PHP class (such as a DTO) and use the
`entityClass`_ option to specify which Doctrine entity table to check against.

Consider the following Doctrine entity::

    // src/Entity/User.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity]
    class User
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        public int $id;

        #[ORM\Column]
        public string $email;

        // ...
    }

You can check for uniqueness in a DTO that creates ``User`` entities by
defining the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Dto/UserDto.php
        namespace App\Dto;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Constraints as Assert;

        #[UniqueEntity(
            fields: 'email',
            entityClass: User::class,
        )]
        class UserDto
        {
            #[Assert\Email]
            public ?string $email = null;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Dto\UserDto:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity:
                    fields: email
                    entityClass: App\Entity\User
            properties:
                email:
                    - Email: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Dto\UserDto">
                <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity">
                    <option name="fields">email</option>
                    <option name="entityClass">App\Entity\User</option>
                </constraint>
                <property name="email">
                    <constraint name="Email"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Dto/UserDto.php
        namespace App\Dto;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Constraints as Assert;

        class UserDto
        {
            public ?string $email = null;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addConstraint(new UniqueEntity(
                    fields: 'email',
                    entityClass: User::class,
                ));

                $metadata->addPropertyConstraint('email', new Assert\Email());
            }
        }

Options
-------

em
~~

**type**: ``string`` **default**: ``null``

The name of the entity manager to use for making the query to determine
the uniqueness. If it's left blank, the correct entity manager will be
determined for this class. For that reason, this option should probably
not need to be used.

``entityClass``
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

By default, the query performed to ensure the uniqueness uses the repository of
the current class instance. However, in some cases, such as when using Doctrine
inheritance mapping, you need to execute the query in a different repository.
Use this option to define the fully-qualified class name (FQCN) of the Doctrine
entity associated with the repository you want to use.

This option is also useful when the object being validated is not a Doctrine entity.

``errorPath``
~~~~~~~~~~~~~

**type**: ``string`` **default**: The name of the first field in `fields`_

If the entity violates the constraint the error message is bound to the
first field in `fields`_. If there is more than one field, you may want
to map the error message to another field.

Consider this example:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Service.php
        namespace App\Entity;

        use App\Entity\Host;
        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        #[ORM\Entity]
        #[UniqueEntity(
            fields: ['host', 'port'],
            message: 'This port is already in use on that host.',
            errorPath: 'port',
        )]
        class Service
        {
            #[ORM\ManyToOne(targetEntity: Host::class)]
            public Host $host;

            #[ORM\Column(type: 'integer')]
            public int $port;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Service:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity:
                    fields: [host, port]
                    message: 'This port is already in use on that host.'
                    errorPath: port

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Service">
                <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity">
                    <option name="fields">
                        <value>host</value>
                        <value>port</value>
                    </option>
                    <option name="message">This port is already in use on that host.</option>
                    <option name="errorPath">port</option>
                </constraint>
            </class>

        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Service.php
        namespace App\Entity;

        use App\Entity\Host;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Service
        {
            public Host $host;
            public int $port;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addConstraint(new UniqueEntity(
                    fields: ['host', 'port'],
                    message: 'This port is already in use on that host.',
                    errorPath: 'port',
                ));
            }
        }

Now, the message would be bound to the ``port`` field with this configuration.

``fields``
~~~~~~~~~~

**type**: ``array`` | ``string``

This required option is the field (or list of fields) on which this entity
should be unique. For example, if you specified both the ``email`` and ``name``
field in a single ``UniqueEntity`` constraint, then it would enforce that
the combination value is unique (e.g. two users could have the same email,
as long as they don't have the same name also).

If you need to require two fields to be individually unique (e.g. a unique
``email`` and a unique ``username``), you must use two ``UniqueEntity`` entries,
each with a single field.

When :ref:`using a PHP class <reference-constraints-unique-entity-php-class>`
(i.e. ``entityClass`` is set and the object itself is not a Doctrine-managed entity),
if any property names on the validated object differ from those on the entity,
pass a key-value array where each key is the object property name and the value
is the corresponding entity property name:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Dto/UserDto.php
        namespace App\Dto;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Constraints as Assert;

        #[UniqueEntity(
            // 'userIdentifier' is the field name in the PHP class and
            // 'email' is the field name in the Doctrine entity
            fields: ['userIdentifier' => 'email'],
            entityClass: User::class,
        )]
        class UserDto
        {
            // ...

            public ?string $userIdentifier = null;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Dto\UserDto:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity:
                    # 'userIdentifier' is the field name in the PHP class and
                    # 'email' is the field name in the Doctrine entity
                    fields: {'userIdentifier': 'email'}
                    entityClass: App\Entity\User
            # ...

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Dto\UserDto">
                <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity">
                    <option name="fields">
                        <!--
                            'userIdentifier' is the field name in the PHP class and
                            'email' is the field name in the Doctrine entity
                        -->
                        <value key="userIdentifier">email</value>
                    </option>
                    <option name="entityClass">App\Entity\User</option>
                </constraint>
                <!-- ... -->
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Dto/UserDto.php
        namespace App\Dto;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class UserDto
        {
            public string $userIdentifier;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addConstraint(new UniqueEntity(
                    // 'userIdentifier' is the field name in the PHP class and
                    // 'email' is the field name in the Doctrine entity
                    fields: ['userIdentifier' => 'email'],
                    entityClass: User::class,
                ));

                // ...
            }
        }

.. include:: /reference/constraints/_groups-option.rst.inc

``identifierFieldNames``
~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

.. versionadded:: 7.1

    The ``identifierFieldNames`` option was introduced in Symfony 7.1.

When :ref:`using a PHP class <reference-constraints-unique-entity-php-class>` to
update an existing entity, use this option to specify the field (or fields) that
identify the corresponding Doctrine entity (defined via the ``entityClass`` option).
The matching entity is then excluded from the uniqueness check, so its own values
do not trigger a violation.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Dto/UserDto.php
        namespace App\Dto;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        #[UniqueEntity(
            fields: 'email',
            entityClass: User::class,
            // 'id' is the name of the Doctrine entity field used as the primary key
            identifierFieldNames: ['id']
        )]
        class UserDto
        {
            public int $id;

            public string $email;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Dto\UserDto:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity:
                    fields: 'email'
                    entityClass: 'App\Entity\User'
                    # 'id' is the name of the Doctrine entity field used as the primary key
                    identifierFieldNames: ['id']
            properties:
                # ...

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Dto\UserDto">
                <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity">
                    <option name="fields">email</option>
                    <option name="entityClass">App\Entity\User</option>
                    <!-- 'id' is the name of the Doctrine entity field used as the primary key -->
                    <option name="identifierFieldNames">id</option>
                </constraint>
                <!-- ... -->
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Dto/UserDto.php
        namespace App\Dto;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        class UserDto
        {
            public int $id;

            public string $email;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new UniqueEntity([
                    'fields' => 'email',
                    'entityClass' => User::class,
                    // 'id' is the name of the Doctrine entity field used as the primary key
                    'identifierFieldNames' => ['id']
                ]));

                // ...
            }
        }

If the identifier field name in the PHP class differs from the one in the
entity, you can map them using a key-value pair.

Consider the following Doctrine entity::

    // src/Entity/User.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity]
    class User
    {
        #[ORM\Column(type: 'string')]
        public string $id;

        #[ORM\Column(type: 'string')]
        public string $username;
    }

You can configure the ``UniqueEntity`` constraint as follows:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Message/UpdateEmployeeProfile.php
        namespace App\Message;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

        #[UniqueEntity(
            entityClass: User::class,
            // 'name' maps to 'username' on the User entity
            fields: ['name' => 'username'],
            // 'uid' holds the entity's primary key, mapped to 'id' on User
            identifierFieldNames: ['uid' => 'id'],
        )]
        class UpdateEmployeeProfile
        {
            public function __construct(
                public string $uid,
                public string $name,
            ) {
            }
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Message\UpdateEmployeeProfile:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity:
                    fields: {name: username}
                    entityClass: 'App\Entity\User'
                    identifierFieldNames: {uid: id}

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Message\UpdateEmployeeProfile">
                <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity">
                    <option name="fields">
                        <value key="name" >username</value>
                    </option>
                    <option name="entityClass">App\Entity\User</option>
                    <option name="identifierFieldNames">
                        <value key="uid" >id</value>
                    </option>
                </constraint>
            </class>

        </constraint-mapping>

    .. code-block:: php

        // src/Message/UpdateEmployeeProfile.php
        namespace App\Message;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class UpdateEmployeeProfile
        {
            public string $uid;
            public string $name;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new UniqueEntity([
                    'fields' => ['name' => 'username'],
                    'entityClass' => User::class,
                    'identifierFieldNames' => ['uid' => 'id'],
                ]));
            }
        }

.. note::

    This option has no effect when the constraint is applied to an entity.

``ignoreNull``
~~~~~~~~~~~~~~

**type**: ``boolean``, ``string`` or ``array`` **default**: ``true``

If this option is set to ``true``, then the constraint will allow multiple
entities to have a ``null`` value for a field without failing validation.
If set to ``false``, only one ``null`` value is allowed - if a second entity
also has a ``null`` value, validation would fail.

In addition to ignoring the ``null`` values of all unique fields, you can also use
this option to specify one or more fields to only ignore ``null`` values on them:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Constraints as Assert;

        #[ORM\Entity]
        #[UniqueEntity(fields: ['email', 'phoneNumber'], ignoreNull: 'phoneNumber')]
        class User
        {
            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            constraints:
                - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity:
                      fields: ['email', 'phoneNumber']
                      ignoreNull: 'phoneNumber'
            properties:
                # ...

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity">
                    <option name="fields">email</option>
                    <option name="fields">phoneNumber</option>
                    <option name="ignore-null">phoneNumber</option>
                </constraint>
                <!-- ... -->
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new UniqueEntity(
                    fields: ['email', 'phoneNumber'],
                    ignoreNull: 'phoneNumber',
                ));

                // ...
            }
        }

.. warning::

    If you set ``ignoreNull`` on fields that are part of a unique index in your
    database, you might see insertion errors when your application attempts to
    persist entities that the ``UniqueEntity`` constraint considers valid.

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is already used.``

The message that's displayed when this constraint fails. This message is by default
mapped to the first field causing the violation. When using multiple fields
in the constraint, the mapping can be specified via the `errorPath`_ property.

Messages can include the ``{{ value }}`` placeholder to display a string
representation of the invalid entity. If the entity doesn't define the
``__toString()`` method, the following generic value will be used: *"Object of
class __CLASS__ identified by <comma separated IDs>"*

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

``repositoryMethod``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``findBy``

The name of the repository method used to determine the uniqueness. If it's left
blank, ``findBy()`` will be used. The method receives as its argument a
``fieldName => value`` associative array (where ``fieldName`` is each of the
fields configured in the ``fields`` option). The method should return a
:phpfunction:`countable PHP variable <is_countable>`.

.. tip::

    For ``binary`` and ``blob`` fields, instead of comparing large files directly,
    consider storing and comparing a hash (e.g. ``hash('xxh128', $contents)``)
    of the contents in your custom ``repositoryMethod``.

.. _`race conditions`: https://en.wikipedia.org/wiki/Race_condition
