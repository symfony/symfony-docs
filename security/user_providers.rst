User Providers
==============

User providers (re)load users from a storage (e.g. a database) based on a
"user identifier" (e.g. the user's email address or username). See
:ref:`security-user-providers` for more detailed information when a user
provider is used.

Symfony provides several user providers:

:ref:`Entity User Provider <security-entity-user-provider>`
    Loads users from a database using :doc:`Doctrine </doctrine>`;
:ref:`LDAP User Provider <security-ldap-user-provider>`
    Loads users from a LDAP server;
:ref:`Memory User Provider <security-memory-user-provider>`
    Loads users from a configuration file;
:ref:`Chain User Provider <security-chain-user-provider>`
    Merges two or more user providers into a new user provider.

.. _security-entity-user-provider:

Entity User Provider
--------------------

This is the most common user provider. Users are stored in a database and
the user provider uses :doc:`Doctrine </doctrine>` to retrieve them.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            providers:
                users:
                    entity:
                        # the class of the entity that represents users
                        class: 'App\Entity\User'
                        # the property to query by - e.g. email, username, etc
                        property: 'email'

                        # optional: if you are using multiple Doctrine entity
                        # managers, this option defines which one to use
                        #manager_name: 'customer'

            # ...

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <provider name="users">
                    <!-- class:    the class of the entity that represents users
                         property: the property to query by - e.g. email, username, etc-->
                    <entity class="App\Entity\User" property="email"/>

                    <!-- optional, if you are using multiple Doctrine entity
                         managers, "manager-name" defines which one to use -->
                    <!-- <entity class="App\Entity\User" property="email"
                                 manager-name="customer"/> -->
                </provider>

                <!-- ... -->
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;
        use Symfony\Config\SecurityConfig;

        $container->loadFromExtension('security', [
            'providers' => [
                'users' => [
                    'entity' => [
                        // the class of the entity that represents users
                        'class'    => User::class,
                        // the property to query by - e.g. email, username, etc
                        'property' => 'email',

                        // optional: if you are using multiple Doctrine entity
                        // managers, this option defines which one to use
                        //'manager_name' => 'customer',
                    ],
                ],
            ],

            // ...
        ]);

.. _authenticating-someone-with-a-custom-entity-provider:

Using a Custom Query to Load the User
.....................................

The entity provider can only query from one *specific* field, specified by
the ``property`` config key. If you want a bit more control over this - e.g. you
want to find a user by ``email`` *or* ``username``, you can do that by
implementing :class:`Symfony\\Bridge\\Doctrine\\Security\\User\\UserLoaderInterface`
in your :ref:`Doctrine repository <doctrine-queries>` (e.g. ``UserRepository``)::

    // src/Repository/UserRepository.php
    namespace App\Repository;

    use App\Entity\User;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

    class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
    {
        // ...

        public function loadUserByIdentifier(string $usernameOrEmail): ?User
        {
            $entityManager = $this->getEntityManager();

            return $entityManager->createQuery(
                    'SELECT u
                    FROM App\Entity\User u
                    WHERE u.username = :query
                    OR u.email = :query'
                )
                ->setParameter('query', $usernameOrEmail)
                ->getOneOrNullResult();
        }

        /** @deprecated since Symfony 5.3 */
        public function loadUserByUsername(string $usernameOrEmail): ?User
        {
            return $this->loadUserByIdentifier($usernameOrEmail);
        }
    }

.. versionadded:: 5.3

    The method ``loadUserByIdentifier()`` was introduced to the
    ``UserLoaderInterface`` in Symfony 5.3.

To finish this, remove the ``property`` key from the user provider in
``security.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            providers:
                users:
                    entity:
                        class: App\Entity\User

            # ...

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <provider name="users">
                    <entity class="App\Entity\User"/>
                </provider>

                <!-- ... -->
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;

        $container->loadFromExtension('security', [
            'providers' => [
                'users' => [
                    'entity' => [
                        'class' => User::class,
                    ],
                ],
            ],

            // ...
        ]);

Now, whenever Symfony uses the user provider, the ``loadUserByIdentifier()``
method on your ``UserRepository`` will be called.

.. _security-memory-user-provider:

Memory User Provider
--------------------

It is not recommended to use this provider in real applications because of its
limitations and how difficult it is to manage users. It may be useful in application
prototypes and for limited applications that don't store users in databases.

This user provider stores all user information in a configuration file,
including their passwords. Make sure the passwords are hashed properly. See
:doc:`/security/passwords` for more information.

After setting up hashing, you can configure all the user information in
``security.yaml``:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        providers:
            backend_users:
                memory:
                    users:
                        john_admin: { password: '$2y$13$jxGxc ... IuqDju', roles: ['ROLE_ADMIN'] }
                        jane_admin: { password: '$2y$13$PFi1I ... rGwXCZ', roles: ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'] }

        # ...

.. caution::

    When using a ``memory`` provider, and not the ``auto`` algorithm, you have
    to choose an encoding without salt (i.e. ``bcrypt``).

.. _security-chain-user-provider:

Chain User Provider
-------------------

This user provider combines two or more of the other providers
to create a new user provider. The order in which
providers are configured is important because Symfony will look for users
starting from the first provider and will keep looking for in the other
providers until the user is found:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        # ...
        providers:
            backend_users:
                ldap:
                    # ...

            legacy_users:
                entity:
                    # ...

            users:
                entity:
                    # ...

            all_users:
                chain:
                    providers: ['legacy_users', 'users', 'backend_users']

.. _security-custom-user-provider:

Creating a Custom User Provider
-------------------------------

Most applications don't need to create a custom provider. If you store users in
a database, a LDAP server or a configuration file, Symfony supports that.
However, if you are loading users from a custom location (e.g. via an API or
legacy database connection), you will need to create a custom user provider.

First, make sure you have followed the :doc:`Security Guide </security>` to create
your ``User`` class.

If you used the ``make:user`` command to create your ``User`` class (and you
answered the questions indicating that you need a custom user provider), that
command will generate a nice skeleton to get you started::

    // src/Security/UserProvider.php
    namespace App\Security;

    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Symfony\Component\Security\Core\Exception\UserNotFoundException;
    use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
    use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;

    class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
    {
        /**
         * The loadUserByIdentifier() method was introduced in Symfony 5.3.
         * In previous versions it was called loadUserByUsername()
         *
         * Symfony calls this method if you use features like switch_user
         * or remember_me. If you are not using these features, you do not
         * need to implement this method.
         *
         * @throws UserNotFoundException if the user is not found
         */
        public function loadUserByIdentifier(string $identifier): UserInterface
        {
            // Load a User object from your data source or throw UserNotFoundException.
            // The $identifier argument is whatever value is being returned by the
            // getUserIdentifier() method in your User class.
            throw new \Exception('TODO: fill in loadUserByIdentifier() inside '.__FILE__);
        }

        /**
         * Refreshes the user after being reloaded from the session.
         *
         * When a user is logged in, at the beginning of each request, the
         * User object is loaded from the session and then this method is
         * called. Your job is to make sure the user's data is still fresh by,
         * for example, re-querying for fresh User data.
         *
         * If your firewall is "stateless: true" (for a pure API), this
         * method is not called.
         *
         * @return UserInterface
         */
        public function refreshUser(UserInterface $user)
        {
            if (!$user instanceof User) {
                throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
            }

            // Return a User object after making sure its data is "fresh".
            // Or throw a UserNotFoundException if the user no longer exists.
            throw new \Exception('TODO: fill in refreshUser() inside '.__FILE__);
        }

        /**
         * Tells Symfony to use this provider for this User class.
         */
        public function supportsClass(string $class)
        {
            return User::class === $class || is_subclass_of($class, User::class);
        }

        /**
         * Upgrades the hashed password of a user, typically for using a better hash algorithm.
         */
        public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
        {
            // TODO: when hashed passwords are in use, this method should:
            // 1. persist the new password in the user storage
            // 2. update the $user object with $user->setPassword($newHashedPassword);
        }
    }

Most of the work is already done! Read the comments in the code and update the
TODO sections to finish the user provider. When you are done, tell Symfony about
the user provider by adding it in ``security.yaml``:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        providers:
            # the name of your user provider can be anything
            your_custom_user_provider:
                id: App\Security\UserProvider

Lastly, update the ``config/packages/security.yaml`` file to set the
``provider`` key to ``your_custom_user_provider`` in all the firewalls which
will use this custom user provider.
