.. index::
   single: Security; User Provider

How to load Security Users from the Database (the Entity Provider)
==================================================================

The security layer is one of the smartest tools of Symfony. It handles two
things: the authentication and the authorization processes. Although it seems
quiet complex to understand how everything works internally, this security
system is very flexible and allows you to plug your application to any
authentication backends like an Active Directory, an OAuth server or a database.

Introduction
------------

This article focuses on how to authenticate users against a database table
managed by a Doctrine entity class. The content of this cookbook entry is split
in three parts. The first part is about designing a Doctrine ``User`` entity
class and make it usable in the security layer of Symfony. The second part
describes how to easily authenticate a user with the Doctrine
:class:`Symfony\\Bridge\\Doctrine\\Security\\User\\EntityUserProvider` object
bundled with the framework and some configuration.
Finally, the tutorial will demonstrate how to create a custom
:class:`Symfony\\Bridge\\Doctrine\\Security\\User\\EntityUserProvider` object to
retrieve users from a database with custom conditions.

This tutorial assumes there is a bootstrapped and loaded
``Acme\Bundle\UserBundle`` bundle in the application kernel.

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

    // src/Acme/Bundle/UserBundle/Entity/User.php

    namespace Acme\Bundle\UserBundle\Entity;

    use Symfony\Component\Security\Core\User\UserInterface;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * Acme\Bundle\UserBundle\Entity\User
     *
     * @ORM\Table()
     * @ORM\Entity(repositoryClass="Acme\Bundle\UserBundle\Entity\UserRepository")
     */
    class User implements UserInterface
    {
        /**
         * @ORM\Column(name="id", type="integer")
         * @ORM\Id()
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * @ORM\Column(name="username", type="string", length=25, unique=true)
         */
        private $username;

        /**
         * @ORM\Column(name="salt", type="string", length=40)
         */
        private $salt;

        /**
         * @ORM\Column(name="password", type="string", length=40)
         */
        private $password;

        /**
         * @ORM\Column(name="email", type="string", length=60, unique=true)
         */
        private $email;

        /**
         * @ORM\Column(name="is_active", type="boolean")
         */
        private $isActive;

        public function __construct()
        {
            $this->isActive = true;
        }

        public function getRoles()
        {
            return array('ROLE_USER');
        }

        public function equals(UserInterface $user)
        {
            return $user->getUsername() === $this->username;
        }

        public function eraseCredentials()
        {
        }

        public function getUsername()
        {
            return $this->username;
        }

        public function getSalt()
        {
            return $this->salt;
        }

        public function getPassword()
        {
            return $this->password;
        }
    }

In order to use an instance of the ``AcmeUserBundle:User`` class in the Symfony
security layer, the entity class must implement the
:class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`. This
interface forces the class to implement the six following methods:

* ``getUsername()`` returns the unique username,
* ``getSalt()`` returns the unique salt,
* ``getPassword()`` returns the encoded password,
* ``getRoles()`` returns an array of associated roles,
* ``equals()`` compares the current object with an other
  :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`
  instance,
* ``eraseCredentials()`` removes sensible information stored in the
  :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface` object.

To keep it simple, the ``equals()`` method just compares the ``username`` field
but it's also possible to make more checks depending on the complexity of your
data model. In the other hand, the ``eraseCredentials()`` method remains empty
as we don't care about it in this tutorial.

Below is an export of my ``user`` table from MySQL.

.. code-block::text

    mysql> select * from user;
    +----+----------+------------------------------------------+------------------------------------------+--------------------+-----------+
    | id | username | salt                                     | password                                 | email              | is_active |
    +----+----------+------------------------------------------+------------------------------------------+--------------------+-----------+
    |  1 | hhamon   | 7308e59b97f6957fb42d66f894793079c366d7c2 | 09610f61637408828a35d7debee5b38a8350eebe | hhamon@example.com |         1 |
    |  2 | jsmith   | ce617a6cca9126bf4036ca0c02e82deea081e564 | 8390105917f3a3d533815250ed7c64b4594d7ebf | jsmith@example.com |         1 |
    |  3 | maxime   | cd01749bb995dc658fa56ed45458d807b523e4cf | 9764731e5f7fb944de5fd8efad4949b995b72a3c | maxime@example.com |         0 |
    |  4 | donald   | 6683c2bfd90c0426088402930cadd0f84901f2f4 | 5c3bcec385f59edcc04490d1db95fdb8673bf612 | donald@example.com |         1 |
    +----+----------+------------------------------------------+------------------------------------------+--------------------+-----------+
    4 rows in set (0.00 sec)

The database now contains four users with different usernames, emails and
statuses. The next part will focus on how to authenticate one of these users
thanks to the Doctrine entity user provider and a couple of lines of
configuration.

Authenticating Someone Against a Database
-----------------------------------------

Authenticating a Doctrine user against the database with the Symfony security
layer is a piece of cake. Everything resides in the configuration of the
`SecurityBundle`_ stored in the ``app/config/security.yml`` file.

Below is an example of configuration to authenticate the user with an HTTP basic
authentication connected to the database.

.. code-block::yaml

    # app/config/security.yml
    security:
        encoders:
            Acme\Bundle\UserBundle\Entity\User:
                algorithm: sha1
                encode_as_base64: false
                iterations: 1

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
class. It allows Symfony to check your user credentials by calling the
``isPasswordValid()`` method of the encoder object.

The ``providers`` section defines an ``administrators`` provider. The ``entity``
keyword means Symfony will use the Doctrine entity user provider. This provider
is configured to use the ``AcmeUserBundle:User`` model class and retrieve a
user instance by using the ``username`` unique field. In other words, this
configuration tells Symfony how to fetch the user from the
database before checking the password validity.

This code and configuration works but it's not enough to secure the application
for **active** users. As of now, we still can authenticate with ``maxime``. The
next section explains how to forbid non active users.

Forbid non Active Users
-----------------------

The easiest way to exclude non active users is to implement the
:class:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface`
interface that takes care of the user's account status.
The :class:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface`
extends the :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`
interface, so that we just need to switch to it in the ``AcmeUserBundle:User``
entity class to take benefit from simple and advanced authentication behaviors.

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

    // src/Acme/Bundle/UserBundle/Entity/User.php

    namespace Acme\Bundle\UserBundle\Entity;

    // ...
    use Symfony\Component\Security\Core\User\AdvancedUserInterface;

    // ...
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

If we try to authenticate with ``maxime``'s username, the access is now
forbidden as this user does not have an enabled account. The next session will
focus on how to write a custom entity provider to authenticate a user with his
username or his email address.

Authenticating Someone with a Custom Entity Provider
----------------------------------------------------

The next step is to allow a user to authenticate with his username or his email
address as they are both unique in the database. Unfortunatelly, the native
entity provider is only able to handle a single property to fetch the user from
the database.

The best way to get this behavior is to write a custom entity provider that
fetches a user with a custom SQL query with a logical ``OR`` condition on both
``username`` and ``email`` table fields. The good news is that a Doctrine
repository object can act as an entity user provider if it implements the
:class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`. This
interface comes with three methods to implement:

* ``loadUserByUsername()`` that fetches and returns a
  :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`
  instance by its unique username. Otherwise, it must throw a
    :class:`Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException`
  exception to indicate the security layer
  there is no user matching the credentials.
* ``refreshUser()`` that refreshes and returns a
  :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface` instance.
  Otherwise it must throw a
  :class:`Symfony\\Component\\Security\\Core\\Exception\\UnsupportedUserException`
  exception to indicate the security layer we are unable to refresh the user.
* ``supportsClass()`` must return ``true`` if the fully qualified class name
  passed as its sole argument is supported by the entity provider.

The code below shows the implementation of the
:class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface` in the
``UserRepository`` class.

.. code-block::php

    // src/Acme/Bundle/UserBundle/Entity/UserRepository.php

    namespace Acme\Bundle\UserBundle\Entity;

    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Doctrine\ORM\EntityRepository;

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
            } catch (\Exception $e) {
                throw new UsernameNotFoundException(sprintf('Unable to find an active admin AcmeUserBundle:User object identified by "%s".', $username), null, 0, $e);
            }

            return $user;
        }

        public function refreshUser(UserInterface $user)
        {
            $username = $user->getUsername();

            try {
                $user = $this->loadUserByUsername($username);
            } catch (UsernameNotFoundException $e) {
                throw new UnsupportedUserException(sprintf('Unable to refresh active admin AcmeUserBundle:User object identified by "%s".', $username), null, 0, $e);
            }

            return $user;
        }

        public function supportsClass($class)
        {
            return is_subclass_of($class, 'Acme\Bundle\UserBundle\Entity\User');
        }
    }

To finish the implementation, the configuration of the security layer must be
changed to tell Symfony to use the new custom entity provider instead of the
generic Doctrine entity provider. It's trival to achieve by removing the
``property`` field in the ``security.providers.administrators.entity`` section
of the ``security.yml`` file.

.. code-block::yaml

    # app/config/security.yml
    security:
        # ...
        providers:
            administrators:
                entity: { class: AcmeUserBundle:User }
        # ...

By doing this, the security layer will use an instance of ``UserRepository`` and
call its ``loadUserByUsername()`` method to fetch a user from the database
wether he filled his username or email address.

Managing Roles in the Database
------------------------------

The end of this tutorial focuses on how to store and retrieve a list of roles
from the database. The ``AcmeUserBundle:User`` entity class defines a
many-to-many relationship with a ``AcmeUserBundle:Group`` entity class. A user
can be affected to zero or several groups and a group can be composed of one or
more users. As a group is also a role, the previous ``getRoles()`` method now
returns the list of related groups.

.. code-block::php

    // src/Acme/Bundle/UserBundle/Entity/User.php

    namespace Acme\Bundle\UserBundle\Entity;

    // ...
    class User implements AdvancedUserInterface
    {
        /**
         * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
         *
         */
        private $groups;

        // ...

        public function getRoles()
        {
            return $this->groups;
        }
    }

The ``AcmeUserBundle:Group`` entity class defines three table fields (``id``,
``name`` and ``role``). The unique ``role`` field contains the role name used by
the Symfony security layer to secure parts of the application. The most
important thing to notice is that the ``AcmeUserBundle:Group`` entity class
implements the :class:`Symfony\\Component\\Security\\Core\\Role\\RoleInterface`
that forces it to have a ``getRole()`` method.

.. code-block::php

    namespace Acme\Bundle\UserBundle\Entity;

    use Symfony\Component\Security\Core\Role\RoleInterface;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\Mapping as ORM;

    /** @ORM\Entity() */
    class Group implements RoleInterface
    {
        /**
         * @ORM\Column(name="id", type="integer")
         * @ORM\Id()
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /** @ORM\Column(name="name", type="string", length=30) */
        private $name;

        /** @ORM\Column(name="role", type="string", length=20, unique=true) */
        private $role;

        /** @ORM\ManyToMany(targetEntity="User", mappedBy="groups") */
        private $users;

        public function __construct()
        {
            $this->users = new ArrayCollection();
        }

        // ... getters and setters for each property

        public function getRole()
        {
            return $this->role;
        }
    }

To improve performances and avoid lazy loading of groups when retrieving a user
from the custom entity provider, the best solution is to join the groups
relationship in the ``UserRepository::loadUserByUsername()`` method. This will
fetch the user and his associated roles / groups with one single query.

.. code-block::php

    // src/Acme/Bundle/UserBundle/Entity/UserRepository.php

    namespace Acme\Bundle\UserBundle\Entity;

    // ...

    class UserRepository extends EntityRepository implements UserProviderInterface
    {
        public function loadUserByUsername($username)
        {
            $q = $this
                ->createQueryBuilder('u')
                ->leftJoin('u.groups', 'g')
                ->where('u.username = :username OR u.email = :email')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->getQuery()
            ;

            // ...
        }

        // ...
    }

The ``QueryBuilder::leftJoin()`` method joins and fetches related groups from
the ``AcmeUserBundle:User`` model class when a user is retrieved with his email
address or username.

.. _`SecurityBundle`: http://symfony.com/doc/current/reference/configuration/security.html