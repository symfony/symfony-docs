EntityExists
============

Validates that a value references an existing Doctrine entity. This is
typically used on identifiers received in DTOs or API payloads, to replace the
repetitive "find the entity, check it's not ``null``, throw an exception"
logic with a regular validation violation.

.. note::

    In order to use this constraint, you should have installed the
    symfony/doctrine-bridge with Composer.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\EntityExists`
Validator   :class:`Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\EntityExistsValidator`
==========  ===================================================================

.. versionadded:: 8.2

    The ``EntityExists`` constraint was introduced in Symfony 8.2.

Basic Usage
-----------

Suppose a command object stores the identifier of the ``User`` entity it refers
to. Use the ``EntityExists`` constraint to ensure that a ``User`` with that
identifier exists in the database:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Command/AssignUserCommand.php
        namespace App\Command;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\EntityExists;
        use Symfony\Component\Validator\Constraints as Assert;

        final class AssignUserCommand
        {
            #[Assert\NotBlank]
            #[EntityExists(entityClass: User::class)]
            public int $userId;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Command\AssignUserCommand:
            properties:
                userId:
                    - NotBlank: ~
                    - Symfony\Bridge\Doctrine\Validator\Constraints\EntityExists:
                        entityClass: App\Entity\User

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Command\AssignUserCommand">
                <property name="userId">
                    <constraint name="NotBlank"/>
                    <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\EntityExists">
                        <option name="entityClass">App\Entity\User</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Command/AssignUserCommand.php
        namespace App\Command;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\EntityExists;
        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        final class AssignUserCommand
        {
            public int $userId;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('userId', new Assert\NotBlank());
                $metadata->addPropertyConstraint('userId', new EntityExists(
                    entityClass: User::class,
                ));
            }
        }

By default, the value is looked up by the single identifier field of the
entity, using the ``findOneBy()`` method of its repository. The entity loaded
during validation is kept in Doctrine's identity map, so fetching it again
later with ``find()`` doesn't trigger a second query.

.. note::

    As with most constraints, ``null`` and empty strings are considered valid
    values. Combine it with :doc:`NotBlank </reference/constraints/NotBlank>`,
    as in the example above, when the value is required.

Options
-------

``em``
~~~~~~

**type**: ``string`` **default**: ``null``

The name of the entity manager to use to look the entity up. If it's left
blank, the entity manager is resolved from the `entityClass`_ option. For that
reason, this option should probably not need to be used.

``entityClass``
~~~~~~~~~~~~~~~

**type**: ``string``

This required option is the fully-qualified class name (FQCN) of the Doctrine
entity to look up.

.. include:: /reference/constraints/_groups-option.rst.inc

``identifierField``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

By default, the value is looked up by the identifier field of the entity. Use
this option to look it up by another field or association instead (e.g. a
``slug`` or an ``email`` field). This option is required when the entity has a
composite identifier.

This option can't be combined with the `repositoryMethod`_ option.

``invalidMessage``
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not valid.``

The message shown when the value can't be converted to the type of the field
used for the lookup (e.g. a malformed UUID for a ``uuid`` field).

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
===============  ==============================================================

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``The referenced entity does not exist.``

The message shown when no entity matches the value.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

``repositoryMethod``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

The name of a method of the entity repository to call instead of
``findOneBy()``. The method receives the validated value as its only argument
and the entity is considered to exist when the method returns a truthy value.
The return value is not used otherwise, so the method can return anything: an
entity, a count, a boolean, etc. This is useful, for example, to run a
lightweight ``COUNT()`` query and avoid hydrating the entity::

    // src/Repository/UserRepository.php
    namespace App\Repository;

    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

    class UserRepository extends ServiceEntityRepository
    {
        // ...

        public function existsWithActiveSubscription(int $id): bool
        {
            return 0 < $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->andWhere('u.id = :id')
                ->andWhere('u.subscriptionEndsAt > CURRENT_TIMESTAMP()')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleScalarResult();
        }
    }

.. configuration-block::

    .. code-block:: php-attributes

        // src/Command/AssignUserCommand.php
        namespace App\Command;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\EntityExists;

        final class AssignUserCommand
        {
            #[EntityExists(entityClass: User::class, repositoryMethod: 'existsWithActiveSubscription')]
            public int $userId;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Command\AssignUserCommand:
            properties:
                userId:
                    - Symfony\Bridge\Doctrine\Validator\Constraints\EntityExists:
                        entityClass: App\Entity\User
                        repositoryMethod: existsWithActiveSubscription

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Command\AssignUserCommand">
                <property name="userId">
                    <constraint name="Symfony\Bridge\Doctrine\Validator\Constraints\EntityExists">
                        <option name="entityClass">App\Entity\User</option>
                        <option name="repositoryMethod">existsWithActiveSubscription</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Command/AssignUserCommand.php
        namespace App\Command;

        use App\Entity\User;
        use Symfony\Bridge\Doctrine\Validator\Constraints\EntityExists;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        final class AssignUserCommand
        {
            public int $userId;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('userId', new EntityExists(
                    entityClass: User::class,
                    repositoryMethod: 'existsWithActiveSubscription',
                ));
            }
        }

This option can't be combined with the `identifierField`_ option.
