Security User Providers
=======================

User providers are PHP classes related to Symfony Security that have two jobs:

**Reload the User from the Session**
    At the beginning of each request (unless your firewall is ``stateless``), Symfony
    loads the ``User`` object from the session. To make sure it's not out-of-date,
    the user provider "refreshes it". The Doctrine user provider, for example,
    queries the database for fresh data. Symfony then checks to see if the user
    has "changed" and de-authenticates the user if they have (see :ref:`user_session_refresh`).

**Load the User for some Feature**
    Some features, like :doc:`user impersonation </security/impersonating_user>`,
    :doc:`Remember Me </security/remember_me>` and many of the built-in
    :doc:`authentication providers </security/auth_providers>`, use the user provider
    to load a User object via its "username" (or email, or whatever field you want).

Symfony comes with several built-in user providers:

* :ref:`Entity User Provider <security-entity-user-provider>` (loads users from
  a database);
* :ref:`LDAP User Provider <security-ldap-user-provider>` (loads users from a
  LDAP server);
* :ref:`Memory User Provider <security-memory-user-provider>` (loads users from
  a configuration file);
* :ref:`Chain User Provider <security-chain-user-provider>` (merges two or more
  user providers into a new user provider).

The built-in user providers cover all the needs for most applications, but you
can also create your own :ref:`custom user provider <custom-user-provider>`.

.. _security-entity-user-provider:

Entity User Provider
--------------------

This is the most common user provider for traditional web applications. Users
are stored in a database and the user provider uses :doc:`Doctrine </doctrine>`
to retrieve them:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
            # ...

            providers:
                users:
                    entity:
                        # the class of the entity that represents users
                        class: 'App\Entity\User'
                        # the property to query by - e.g. username, email, etc
                        property: 'username'
                        # optional: if you're using multiple Doctrine entity
                        # managers, this option defines which one to use
                        # manager_name: 'customer'

            # ...

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <provider name="users">
                    <!-- 'class' is the entity that represents users and 'property'
                         is the entity property to query by - e.g. username, email, etc -->
                    <entity class="App\Entity\User" property="username"/>

                    <!-- optional: if you're using multiple Doctrine entity
                         managers, this option defines which one to use -->
                    <!-- <entity class="App\Entity\User" property="username"
                                 manager-name="customer"/> -->
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
                        // the class of the entity that represents users
                        'class'    => User::class,
                        // the property to query by - e.g. username, email, etc
                        'property' => 'username',
                        // optional: if you're using multiple Doctrine entity
                        // managers, this option defines which one to use
                        // 'manager_name' => 'customer',
                    ],
                ],
            ],

            // ...
        ]);

The ``providers`` section creates a "user provider" called ``users`` that knows
how to query from your ``App\Entity\User`` entity by the ``username`` property.
You can choose any name for the user provider, but it's recommended to pick a
descriptive name because this will be later used in the firewall configuration.

.. _authenticating-someone-with-a-custom-entity-provider:

Using a Custom Query to Load the User
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``entity`` provider can only query from one *specific* field, specified by
the ``property`` config key. If you want a bit more control over this - e.g. you
want to find a user by ``email`` *or* ``username``, you can do that by making
your ``UserRepository`` implement the
:class:`Symfony\\Bridge\\Doctrine\\Security\\User\\UserLoaderInterface`. This
interface only requires one method: ``loadUserByUsername($username)``::

    // src/Repository/UserRepository.php
    namespace App\Repository;

    use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
    use Doctrine\ORM\EntityRepository;

    class UserRepository extends EntityRepository implements UserLoaderInterface
    {
        // ...

        public function loadUserByUsername($usernameOrEmail)
        {
            return $this->createQueryBuilder('u')
                ->where('u.username = :query OR u.email = :query')
                ->setParameter('query', $usernameOrEmail)
                ->getQuery()
                ->getOneOrNullResult();
        }
    }

To finish this, remove the ``property`` key from the user provider in
``security.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            providers:
                users:
                    entity:
                        class: App\Entity\User

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <provider name="users">
                    <entity class="App\Entity\User"/>
                </provider>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;

        $container->loadFromExtension('security', [
            // ...

            'providers' => [
                'users' => [
                    'entity' => [
                        'class' => User::class,
                    ],
                ],
            ],
        ]);

This tells Symfony to *not* query automatically for the User. Instead, when
needed (e.g. because :doc:`user impersonation </security/impersonating_user>`,
:doc:`Remember Me </security/remember_me>`, or some other security feature is
activated), the ``loadUserByUsername()`` method on ``UserRepository`` will be called.

.. _security-memory-user-provider:

Memory User Provider
--------------------

It's not recommended to use this provider in real applications because of its
limitations and how difficult it is to manage users. It may be useful in application
prototypes and for limited applications that don't store users in databases.

This user provider stores all user information in a configuration file,
including their passwords. That's why the first step is to configure how these
users will encode their passwords:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            encoders:
                # this internal class is used by Symfony to represent in-memory users
                Symfony\Component\Security\Core\User\User: 'bcrypt'

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <config>
                <!-- ... -->

                <!-- this internal class is used by Symfony to represent in-memory users -->
                <encoder class="Symfony\Component\Security\Core\User\User"
                    algorithm="bcrypt"
                />
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php

        // this internal class is used by Symfony to represent in-memory users
        use Symfony\Component\Security\Core\User\User;

        $container->loadFromExtension('security', [
            // ...
            'encoders' => [
                User::class => [
                    'algorithm' => 'bcrypt',
                ],
            ],
        ]);

Then, run this command to encode the plain text passwords of your users:

.. code-block:: terminal

    $ php bin/console security:encode-password

Now you can configure all the user information in ``config/packages/security.yaml``:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        # ...
        providers:
            backend_users:
                memory:
                    users:
                        john_admin: { password: '$2y$13$jxGxc ... IuqDju', roles: ['ROLE_ADMIN'] }
                        jane_admin: { password: '$2y$13$PFi1I ... rGwXCZ', roles: ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'] }

.. _security-ldap-user-provider:

LDAP User Provider
------------------

This user provider requires installing certain dependencies and using some
special authentication providers, so it's explained in a separate article:
:doc:`/security/ldap`.

.. _security-chain-user-provider:

Chain User Provider
-------------------

This user provider combines two or more of the other provider types (``entity``,
``memory`` and ``ldap``) to create a new user provider. The order in which
providers are configured is important because Symfony will look for users
starting from the first provider and will keep looking for in the other
providers until the user is found:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        # ...
        providers:
            backend_users:
                memory:
                    # ...

            legacy_users:
                entity:
                    # ...

            users:
                entity:
                    # ...

            all_users:
                chain:
                    providers: ['legacy_users', 'users', 'backend']

.. _custom-user-provider:

Creating a Custom User Provider
-------------------------------

Most applications don't need to create a custom provider. If you store users in
a database, a LDAP server or a configuration file, Symfony supports that.
However, if you're loading users from a custom location (e.g. via an API or
legacy database connection), you'll need to create a custom user provider.

First, make sure you've followed the :doc:`Security Guide </security>` to create
your ``User`` class.

If you used the ``make:user`` command to create your ``User`` class (and you
answered the questions indicating that you need a custom user provider), that
command will generate a nice skeleton to get you started::

    // src/Security/UserProvider.php
    namespace App\Security;

    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;

    class UserProvider implements UserProviderInterface
    {
        /**
         * Symfony calls this method if you use features like switch_user
         * or remember_me.
         *
         * If you're not using these features, you do not need to implement
         * this method.
         *
         * @return UserInterface
         *
         * @throws UsernameNotFoundException if the user is not found
         */
        public function loadUserByUsername($username)
        {
            // Load a User object from your data source or throw UsernameNotFoundException.
            // The $username argument may not actually be a username:
            // it is whatever value is being returned by the getUsername()
            // method in your User class.
            throw new \Exception('TODO: fill in loadUserByUsername() inside '.__FILE__);
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
            // Or throw a UsernameNotFoundException if the user no longer exists.
            throw new \Exception('TODO: fill in refreshUser() inside '.__FILE__);
        }

        /**
         * Tells Symfony to use this provider for this User class.
         */
        public function supportsClass($class)
        {
            return User::class === $class;
        }
    }

Most of the work is already done! Read the comments in the code and update the
TODO sections to finish the user provider. When you're done, tell Symfony about
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

.. _user_session_refresh:

Understanding how Users are Refreshed from the Session
------------------------------------------------------

At the end of every request (unless your firewall is ``stateless``), your
``User`` object is serialized to the session. At the beginning of the next
request, it's deserialized and then passed to your user provider to "refresh" it
(e.g. Doctrine queries for a fresh user).

Then, the two User objects (the original from the session and the refreshed User
object) are "compared" to see if they are "equal". By default, the core
``AbstractToken`` class compares the return values of the ``getPassword()``,
``getSalt()`` and ``getUsername()`` methods. If any of these are different, your
user will be logged out. This is a security measure to make sure that malicious
users can be de-authenticated if core user data changes.

However, in some cases, this process can cause unexpected authentication problems.
If you're having problems authenticating, it could be that you *are* authenticating
successfully, but you immediately lose authentication after the first redirect.

In that case, review the serialization logic (e.g. ``SerializableInterface``) if
you have any, to make sure that all the fields necessary are serialized.

Comparing Users Manually with EquatableInterface
------------------------------------------------

Or, if you need more control over the "compare users" process, make your User class
implement :class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface`.
Then, your ``isEqualTo()`` method will be called when comparing users.

Injecting a User Provider in your Services
------------------------------------------

Symfony defines several services related to user providers:

.. code-block:: terminal

    $ php bin/console debug:container user.provider

      Select one of the following services to display its information:
      [0] security.user.provider.in_memory
      [1] security.user.provider.in_memory.user
      [2] security.user.provider.ldap
      [3] security.user.provider.chain
      ...

Most of these services are abstract and cannot be injected in your services.
Instead, you must inject the normal service that Symfony creates for each of
your user providers. The names of these services follow this pattern:
``security.user.provider.concrete.<your-provider-name>``.

For example, if you are :doc:`building a form login </security/form_login_setup>`
and want to inject in your ``LoginFormAuthenticator`` a user provider of type
``memory`` and called  ``backend_users``, do the following::

    // src/Security/LoginFormAuthenticator.php
    namespace App\Security;

    use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
    use Symfony\Component\Security\Core\User\InMemoryUserProvider;

    class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
    {
        private $userProvider;

        // change the 'InMemoryUserProvider' type-hint in the constructor if
        // you are injecting a different type of user provider
        public function __construct(InMemoryUserProvider $userProvider, /* ... */)
        {
            $this->userProvider = $userProvider;
            // ...
        }
    }

Then, inject the concrete service created by Symfony for the ``backend_users``
user provider:

.. code-block:: yaml

    # config/services.yaml
    services:
        # ...

        App\Security\LoginFormAuthenticator:
            $userProvider: '@security.user.provider.concrete.backend_users'
