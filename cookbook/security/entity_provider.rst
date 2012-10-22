.. index::
   single: Security; User provider
   single: Security; Entity provider

How to load Security Users from the Database (the Entity Provider)
==================================================================

The security layer is one of the smartest tools of Symfony. It handles two
things: the authentication and the authorization processes. Although it may
seem difficult to understand how it works internally, the security system
is very flexible and allows you to integrate your application with any authentication
backend, like Active Directory, an OAuth server or a database.

Introduction
------------

This article focuses on how to authenticate users against a database table
managed by a Doctrine entity class. The content of this cookbook entry is split
in three parts. The first part is about designing a Doctrine ``User`` entity
class and making it usable in the security layer of Symfony. The second part
describes how to easily authenticate a user with the Doctrine
:class:`Symfony\\Bridge\\Doctrine\\Security\\User\\EntityUserProvider` object
bundled with the framework and some configuration.
Finally, the tutorial will demonstrate how to create a custom
:class:`Symfony\\Bridge\\Doctrine\\Security\\User\\EntityUserProvider` object to
retrieve users from a database with custom conditions.

This tutorial assumes there is a bootstrapped and loaded
``Acme\UserBundle`` bundle in the application kernel.

The Data Model
--------------

For the purpose of this cookbook, the ``AcmeUserBundle`` bundle contains a
``User`` entity class with the following fields: ``id``, ``username``, ``salt``,
``password``, ``email`` and ``isActive``. The ``isActive`` field tells whether
or not the user account is active.

To make it shorter, the getter and setter methods for each have been removed to
focus on the most important methods that come from the
:class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`.

.. code-block:: php

    // src/Acme/UserBundle/Entity/User.php
    namespace Acme\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Security\Core\User\UserInterface;

    /**
     * Acme\UserBundle\Entity\User
     *
     * @ORM\Table(name="acme_users")
     * @ORM\Entity(repositoryClass="Acme\UserBundle\Entity\UserRepository")
     */
    class User implements UserInterface
    {
        /**
         * @ORM\Column(type="integer")
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * @ORM\Column(type="string", length=25, unique=true)
         */
        private $username;

        /**
         * @ORM\Column(type="string", length=32)
         */
        private $salt;

        /**
         * @ORM\Column(type="string", length=40)
         */
        private $password;

        /**
         * @ORM\Column(type="string", length=60, unique=true)
         */
        private $email;

        /**
         * @ORM\Column(name="is_active", type="boolean")
         */
        private $isActive;

        public function __construct()
        {
            $this->isActive = true;
            $this->salt = md5(uniqid(null, true));
        }

        /**
         * @inheritDoc
         */
        public function getUsername()
        {
            return $this->username;
        }

        /**
         * @inheritDoc
         */
        public function getSalt()
        {
            return $this->salt;
        }

        /**
         * @inheritDoc
         */
        public function getPassword()
        {
            return $this->password;
        }

        /**
         * @inheritDoc
         */
        public function getRoles()
        {
            return array('ROLE_USER');
        }

        /**
         * @inheritDoc
         */
        public function eraseCredentials()
        {
        }
    }

In order to use an instance of the ``AcmeUserBundle:User`` class in the Symfony
security layer, the entity class must implement the
:class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`. This
interface forces the class to implement the five following methods:

* ``getRoles()``,
* ``getPassword()``,
* ``getSalt()``,
* ``getUsername()``,
* ``eraseCredentials()``

For more details on each of these, see :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`.

.. versionadded:: 2.1
    In Symfony 2.1, the ``equals`` method was removed from ``UserInterface``.
    If you need to override the default implementation of comparison logic,
    implement the new :class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface`
    interface and implement the ``isEqualTo`` method.

.. code-block:: php

    // src/Acme/UserBundle/Entity/User.php

    namespace Acme\UserBundle\Entity;

    use Symfony\Component\Security\Core\User\EquatableInterface;

    // ...

    public function isEqualTo(UserInterface $user)
    {
        return $this->username === $user->getUsername();
    }

Below is an export of my ``User`` table from MySQL. For details on how to
create user records and encode their password, see :ref:`book-security-encoding-user-password`.

.. code-block:: text

    mysql> select * from user;
    +----+----------+----------------------------------+------------------------------------------+--------------------+-----------+
    | id | username | salt                             | password                                 | email              | is_active |
    +----+----------+----------------------------------+------------------------------------------+--------------------+-----------+
    |  1 | hhamon   | 7308e59b97f6957fb42d66f894793079 | 09610f61637408828a35d7debee5b38a8350eebe | hhamon@example.com |         1 |
    |  2 | jsmith   | ce617a6cca9126bf4036ca0c02e82dee | 8390105917f3a3d533815250ed7c64b4594d7ebf | jsmith@example.com |         1 |
    |  3 | maxime   | cd01749bb995dc658fa56ed45458d807 | 9764731e5f7fb944de5fd8efad4949b995b72a3c | maxime@example.com |         0 |
    |  4 | donald   | 6683c2bfd90c0426088402930cadd0f8 | 5c3bcec385f59edcc04490d1db95fdb8673bf612 | donald@example.com |         1 |
    +----+----------+----------------------------------+------------------------------------------+--------------------+-----------+
    4 rows in set (0.00 sec)

The database now contains four users with different usernames, emails and
statuses. The next part will focus on how to authenticate one of these users
thanks to the Doctrine entity user provider and a couple of lines of
configuration.

Authenticating Someone against a Database
-----------------------------------------

Authenticating a Doctrine user against the database with the Symfony security
layer is a piece of cake. Everything resides in the configuration of the
:doc:`SecurityBundle</reference/configuration/security>` stored in the
``app/config/security.yml`` file.

Below is an example of configuration where the user will enter his/her
username and password via HTTP basic authentication. That information will
then be checked against our User entity records in the database:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            encoders:
                Acme\UserBundle\Entity\User:
                    algorithm:        sha1
                    encode_as_base64: false
                    iterations:       1

            role_hierarchy:
                ROLE_ADMIN:       ROLE_USER
                ROLE_SUPER_ADMIN: [ ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH ]

            providers:
                administrators:
                    entity: { class: AcmeUserBundle:User, property: username }

            firewalls:
                admin_area:
                    pattern:    ^/admin
                    http_basic: ~

            access_control:
                - { path: ^/admin, roles: ROLE_ADMIN }

The ``encoders`` section associates the ``sha1`` password encoder to the entity
class. This means that Symfony will expect the password that's stored in
the database to be encoded using this algorithm. For details on how to create
a new User object with a properly encoded password, see the
:ref:`book-security-encoding-user-password` section of the security chapter.

The ``providers`` section defines an ``administrators`` user provider. A
user provider is a "source" of where users are loaded during authentication.
In this case, the ``entity`` keyword means that Symfony will use the Doctrine
entity user provider to load User entity objects from the database by using
the ``username`` unique field. In other words, this tells Symfony how to
fetch the user from the database before checking the password validity.

This code and configuration works but it's not enough to secure the application
for **active** users. As of now, we still can authenticate with ``maxime``. The
next section explains how to forbid non active users.

Forbid non Active Users
-----------------------

The easiest way to exclude non active users is to implement the
:class:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface`
interface that takes care of checking the user's account status.
The :class:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface`
extends the :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`
interface, so you just need to switch to the new interface in the ``AcmeUserBundle:User``
entity class to benefit from simple and advanced authentication behaviors.

The :class:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface`
interface adds four extra methods to validate the account status:

* ``isAccountNonExpired()`` checks whether the user's account has expired,
* ``isAccountNonLocked()`` checks whether the user is locked,
* ``isCredentialsNonExpired()`` checks whether the user's credentials (password)
  has expired,
* ``isEnabled()`` checks whether the user is enabled.

For this example, the first three methods will return ``true`` whereas the
``isEnabled()`` method will return the boolean value in the ``isActive`` field.

.. code-block:: php

    // src/Acme/UserBundle/Entity/User.php
    namespace Acme\Bundle\UserBundle\Entity;

    // ...
    use Symfony\Component\Security\Core\User\AdvancedUserInterface;

    class User implements AdvancedUserInterface
    {
        // ...

        public function isAccountNonExpired()
        {
            return true;
        }

        public function isAccountNonLocked()
        {
            return true;
        }

        public function isCredentialsNonExpired()
        {
            return true;
        }

        public function isEnabled()
        {
            return $this->isActive;
        }
    }

If we try to authenticate a ``maxime``, the access is now forbidden as this
user does not have an enabled account. The next session will focus on how
to write a custom entity provider to authenticate a user with his username
or his email address.

Authenticating Someone with a Custom Entity Provider
----------------------------------------------------

The next step is to allow a user to authenticate with his username or his email
address as they are both unique in the database. Unfortunately, the native
entity provider is only able to handle a single property to fetch the user from
the database.

To accomplish this, create a custom entity provider that looks for a user
whose username *or* email field matches the submitted login username.
The good news is that a Doctrine repository object can act as an entity user
provider if it implements the
:class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`. This
interface comes with three methods to implement: ``loadUserByUsername($username)``,
``refreshUser(UserInterface $user)``, and ``supportsClass($class)``. For
more details, see :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`.

The code below shows the implementation of the
:class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface` in the
``UserRepository`` class::

    // src/Acme/UserBundle/Entity/UserRepository.php
    namespace Acme\UserBundle\Entity;

    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Doctrine\ORM\EntityRepository;
    use Doctrine\ORM\NoResultException;

    class UserRepository extends EntityRepository implements UserProviderInterface
    {
        public function loadUserByUsername($username)
        {
            $q = $this
                ->createQueryBuilder('u')
                ->where('u.username = :username OR u.email = :email')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->getQuery()
            ;

            try {
                // The Query::getSingleResult() method throws an exception
                // if there is no record matching the criteria.
                $user = $q->getSingleResult();
            } catch (NoResultException $e) {
                throw new UsernameNotFoundException(sprintf('Unable to find an active admin AcmeUserBundle:User object identified by "%s".', $username), null, 0, $e);
            }

            return $user;
        }

        public function refreshUser(UserInterface $user)
        {
            $class = get_class($user);
            if (!$this->supportsClass($class)) {
                throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
            }

            return $this->loadUserByUsername($user->getUsername());
        }

        public function supportsClass($class)
        {
            return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
        }
    }

To finish the implementation, the configuration of the security layer must be
changed to tell Symfony to use the new custom entity provider instead of the
generic Doctrine entity provider. It's trival to achieve by removing the
``property`` field in the ``security.providers.administrators.entity`` section
of the ``security.yml`` file.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...
            providers:
                administrators:
                    entity: { class: AcmeUserBundle:User }
            # ...

By doing this, the security layer will use an instance of ``UserRepository`` and
call its ``loadUserByUsername()`` method to fetch a user from the database
whether he filled in his username or email address.

Managing Roles in the Database
------------------------------

The end of this tutorial focuses on how to store and retrieve a list of roles
from the database. As mentioned previously, when your user is loaded, its
``getRoles()`` method returns the array of security roles that should be
assigned to the user. You can load this data from anywhere - a hardcoded
list used for all users (e.g. ``array('ROLE_USER')``), a Doctrine array
property called ``roles``, or via a Doctrine relationship, as we'll learn
about in this section.

.. caution::

    In a typical setup, you should always return at least 1 role from the ``getRoles()``
    method. By convention, a role called ``ROLE_USER`` is usually returned.
    If you fail to return any roles, it may appear as if your user isn't
    authenticated at all.

In this example, the ``AcmeUserBundle:User`` entity class defines a
many-to-many relationship with a ``AcmeUserBundle:Group`` entity class. A user
can be related to several groups and a group can be composed of one or
more users. As a group is also a role, the previous ``getRoles()`` method now
returns the list of related groups::

    // src/Acme/UserBundle/Entity/User.php
    namespace Acme\Bundle\UserBundle\Entity;

    use Doctrine\Common\Collections\ArrayCollection;
    // ...

    class User implements AdvancedUserInterface
    {
        /**
         * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
         *
         */
        private $groups;

        public function __construct()
        {
            $this->groups = new ArrayCollection();
        }

        // ...

        public function getRoles()
        {
            return $this->groups->toArray();
        }
    }

The ``AcmeUserBundle:Group`` entity class defines three table fields (``id``,
``name`` and ``role``). The unique ``role`` field contains the role name used by
the Symfony security layer to secure parts of the application. The most
important thing to notice is that the ``AcmeUserBundle:Group`` entity class
implements the :class:`Symfony\\Component\\Security\\Core\\Role\\RoleInterface`
that forces it to have a ``getRole()`` method::

    // src/Acme/Bundle/UserBundle/Entity/Group.php
    namespace Acme\Bundle\UserBundle\Entity;

    use Symfony\Component\Security\Core\Role\RoleInterface;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Table(name="acme_groups")
     * @ORM\Entity()
     */
    class Group implements RoleInterface
    {
        /**
         * @ORM\Column(name="id", type="integer")
         * @ORM\Id()
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * @ORM\Column(name="name", type="string", length=30)
         */
        private $name;

        /**
         * @ORM\Column(name="role", type="string", length=20, unique=true)
         */
        private $role;

        /**
         * @ORM\ManyToMany(targetEntity="User", mappedBy="groups")
         */
        private $users;

        public function __construct()
        {
            $this->users = new ArrayCollection();
        }

        // ... getters and setters for each property

        /**
         * @see RoleInterface
         */
        public function getRole()
        {
            return $this->role;
        }
    }

To improve performances and avoid lazy loading of groups when retrieving a user
from the custom entity provider, the best solution is to join the groups
relationship in the ``UserRepository::loadUserByUsername()`` method. This will
fetch the user and his associated roles / groups with a single query::

    // src/Acme/UserBundle/Entity/UserRepository.php
    namespace Acme\Bundle\UserBundle\Entity;

    // ...

    class UserRepository extends EntityRepository implements UserProviderInterface
    {
        public function loadUserByUsername($username)
        {
            $q = $this
                ->createQueryBuilder('u')
                ->select('u, g')
                ->leftJoin('u.groups', 'g')
                ->where('u.username = :username OR u.email = :email')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->getQuery();

            // ...
        }

        // ...
    }

The ``QueryBuilder::leftJoin()`` method joins and fetches related groups from
the ``AcmeUserBundle:User`` model class when a user is retrieved with his email
address or username.
