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
``User`` entity class with the following fields: ``id``, ``username``,
``password``, ``email`` and ``isActive``. The ``isActive`` field tells whether
or not the user account is active.

To make it shorter, the getter and setter methods for each have been removed to
focus on the most important methods that come from the
:class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`.

.. tip::

    You can :ref:`generate the missing getter and setters <book-doctrine-generating-getters-and-setters>`
    by running:

    .. code-block:: bash

        $ php app/console doctrine:generate:entities Acme/UserBundle/Entity/User

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
    class User implements UserInterface, \Serializable
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
         * @ORM\Column(type="string", length=64)
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
            // may not be needed, see section on salt below
            // $this->salt = md5(uniqid(null, true));
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
            // you *may* need a real salt depending on your encoder
            // see section on salt below
            return null;
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

        /**
         * @see \Serializable::serialize()
         */
        public function serialize()
        {
            return serialize(array(
                $this->id,
                $this->username,
                $this->password,
                // see section on salt below
                // $this->salt,
            ));
        }

        /**
         * @see \Serializable::unserialize()
         */
        public function unserialize($serialized)
        {
            list (
                $this->id,
                $this->username,
                $this->password,
                // see section on salt below
                // $this->salt
            ) = unserialize($serialized);
        }
    }

.. note::

    If you choose to implement
    :class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface`,
    you determine yourself which properties need to be compared to distinguish
    your user objects.

.. tip::

    :ref:`Generate the database table <book-doctrine-creating-the-database-tables-schema>`
    for your ``User`` entity by running:

    .. code-block:: bash

        $ php app/console doctrine:schema:update --force

In order to use an instance of the ``AcmeUserBundle:User`` class in the Symfony
security layer, the entity class must implement the
:class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`. This
interface forces the class to implement the five following methods:

* :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getRoles`
* :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getPassword`
* :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getSalt`
* :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getUsername`
* :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::eraseCredential`

For more details on each of these, see :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`.

.. sidebar:: What is the importance of serialize and unserialize?

    The :phpclass:`Serializable` interface and its ``serialize`` and ``unserialize``
    methods have been added to allow the ``User`` class to be serialized
    to the session. This may or may not be needed depending on your setup,
    but it's probably a good idea. The ``id`` is the most important value
    that needs to be serialized because the
    :method:`Symfony\\Bridge\\Doctrine\\Security\\User\\EntityUserProvider::refreshUser`
    method reloads the user on each request by using the ``id``. In practice,
    this means that the User object is reloaded from the database on each
    request using the ``id`` from the serialized object. This makes sure
    all of the User's data is fresh.

    Symfony also uses the ``username``, ``salt``, and ``password`` to verify
    that the User has not changed between requests. Failing to serialize
    these may cause you to be logged out on each request. If your User implements
    :class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface`,
    then instead of these properties being checked, your ``isEqualTo`` method
    is simply called, and you can check whatever properties you want. Unless
    you understand this, you probably *won't* need to implement this interface
    or worry about it.

Below is an export of the ``User`` table from MySQL with user ``admin`` and
password ``admin`` (which has been encoded). For details on how to create
user records and encode their password, see :ref:`book-security-encoding-user-password`.

.. code-block:: bash

    $ mysql> SELECT * FROM acme_users;
    +----+----------+------------------------------------------+--------------------+-----------+
    | id | username | password                                 | email              | is_active |
    +----+----------+------------------------------------------+--------------------+-----------+
    |  1 | admin    | d033e22ae348aeb5660fc2140aec35850c4da997 | admin@example.com  |         1 |
    +----+----------+------------------------------------------+--------------------+-----------+

The next part will focus on how to authenticate one of these users
thanks to the Doctrine entity user provider and a couple of lines of
configuration.

.. sidebar:: Do you need to use a Salt?

    Yes. Hashing a password with a salt is a necessary step so that encoded
    passwords can't be decoded. However, some encoders - like Bcrypt - have
    a built-in salt mechanism. If you configure ``bcrypt`` as your encoder
    in ``security.yml`` (see the next section), then ``getSalt()`` should
    return ``null``, so that Bcrypt generates the salt itself.

    However, if you use an encoder that does *not* have a built-in salting
    ability (e.g. ``sha512``), you *must* (from a security perspective) generate
    your own, random salt, store it on a ``salt`` property that is saved to
    the database, and return it from ``getSalt()``. Some of the code needed
    is commented out in the above example.

Authenticating Someone against a Database
-----------------------------------------

Authenticating a Doctrine user against the database with the Symfony security
layer is a piece of cake. Everything resides in the configuration of the
:doc:`SecurityBundle </reference/configuration/security>` stored in the
``app/config/security.yml`` file.

Below is an example of configuration where the user will enter their
username and password via HTTP basic authentication. That information will
then be checked against your User entity records in the database:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            encoders:
                Acme\UserBundle\Entity\User:
                    algorithm: bcrypt

            role_hierarchy:
                ROLE_ADMIN:       ROLE_USER
                ROLE_SUPER_ADMIN: [ ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH ]

            providers:
                administrators:
                    entity: { class: AcmeUserBundle:User, property: username }

            firewalls:
                admin_area:
                    pattern:    ^/admin
                    http_basic: ~

            access_control:
                - { path: ^/admin, roles: ROLE_ADMIN }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <encoder class="Acme\UserBundle\Entity\User"
                algorithm="bcrypt"
            />

            <role id="ROLE_ADMIN">ROLE_USER</role>
            <role id="ROLE_SUPER_ADMIN">ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH</role>

            <provider name="administrators">
                <entity class="AcmeUserBundle:User" property="username" />
            </provider>

            <firewall name="admin_area" pattern="^/admin">
                <http-basic />
            </firewall>

            <rule path="^/admin" role="ROLE_ADMIN" />
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'encoders' => array(
                'Acme\UserBundle\Entity\User' => array(
                    'algorithm' => 'bcrypt',
                ),
            ),
            'role_hierarchy' => array(
                'ROLE_ADMIN'       => 'ROLE_USER',
                'ROLE_SUPER_ADMIN' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'),
            ),
            'providers' => array(
                'administrator' => array(
                    'entity' => array(
                        'class'    => 'AcmeUserBundle:User',
                        'property' => 'username',
                    ),
                ),
            ),
            'firewalls' => array(
                'admin_area' => array(
                    'pattern' => '^/admin',
                    'http_basic' => null,
                ),
            ),
            'access_control' => array(
                array('path' => '^/admin', 'role' => 'ROLE_ADMIN'),
            ),
        ));

The ``encoders`` section associates the ``bcrypt`` password encoder to the entity
class. This means that Symfony will expect the password that's stored in
the database to be encoded using this encoder. For details on how to create
a new User object with a properly encoded password, see the
:ref:`book-security-encoding-user-password` section of the security chapter.

.. include:: /cookbook/security/_ircmaxwell_password-compat.rst.inc

The ``providers`` section defines an ``administrators`` user provider. A
user provider is a "source" of where users are loaded during authentication.
In this case, the ``entity`` keyword means that Symfony will use the Doctrine
entity user provider to load User entity objects from the database by using
the ``username`` unique field. In other words, this tells Symfony how to
fetch the user from the database before checking the password validity.

Forbid Inactive Users
---------------------

If a User's ``isActive`` property is set to ``false`` (i.e. ``is_active``
is 0 in the database), the user will still be able to login access the site
normally. To prevent "inactive" users from logging in, you'll need to do a
little more work.

The easiest way to exclude inactive users is to implement the
:class:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface`
interface that takes care of checking the user's account status.
The :class:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface`
extends the :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`
interface, so you just need to switch to the new interface in the ``AcmeUserBundle:User``
entity class to benefit from simple and advanced authentication behaviors.

The :class:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface`
interface adds four extra methods to validate the account status:

* :method:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface::isAccountNonExpired`
  checks whether the user's account has expired,
* :method:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface::isAccountNonLocked`
   checks whether the user is locked,
* :method:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface::isCredentialsNonExpired`
  checks whether the user's credentials (password) has expired,
* :method:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface::isEnabled`
  checks whether the user is enabled.

For this example, the first three methods will return ``true`` whereas the
``isEnabled()`` method will return the boolean value in the ``isActive`` field.

.. code-block:: php

    // src/Acme/UserBundle/Entity/User.php
    namespace Acme\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Security\Core\User\AdvancedUserInterface;

    class User implements AdvancedUserInterface, \Serializable
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

Now, if you try to authenticate as a user who's ``is_active`` database field
is set to 0, you won't be allowed.

.. note::

    When using the ``AdvancedUserInterface``, you should also add any of
    the properties used by these methods (like ``isActive()``) to the ``serialize()``
    method. If you *don't* do this, your user may not be deserialized correctly
    from the session on each request.

The next session will focus on how to write a custom entity provider
to authenticate a user with their username or email address.

Authenticating Someone with a Custom Entity Provider
----------------------------------------------------

The next step is to allow a user to authenticate with their username or email
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
                ->getQuery();

            try {
                // The Query::getSingleResult() method throws an exception
                // if there is no record matching the criteria.
                $user = $q->getSingleResult();
            } catch (NoResultException $e) {
                $message = sprintf(
                    'Unable to find an active admin AcmeUserBundle:User object identified by "%s".',
                    $username
                );
                throw new UsernameNotFoundException($message, 0, $e);
            }

            return $user;
        }

        public function refreshUser(UserInterface $user)
        {
            $class = get_class($user);
            if (!$this->supportsClass($class)) {
                throw new UnsupportedUserException(
                    sprintf(
                        'Instances of "%s" are not supported.',
                        $class
                    )
                );
            }

            return $this->find($user->getId());
        }

        public function supportsClass($class)
        {
            return $this->getEntityName() === $class
                || is_subclass_of($class, $this->getEntityName());
        }
    }

To finish the implementation, the configuration of the security layer must be
changed to tell Symfony to use the new custom entity provider instead of the
generic Doctrine entity provider. It's trivial to achieve by removing the
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

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <!-- ... -->

            <provider name="administrator">
                <entity class="AcmeUserBundle:User" />
            </provider>

            <!-- ... -->
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            ...,
            'providers' => array(
                'administrator' => array(
                    'entity' => array(
                        'class' => 'AcmeUserBundle:User',
                    ),
                ),
            ),
            ...,
        ));

By doing this, the security layer will use an instance of ``UserRepository`` and
call its ``loadUserByUsername()`` method to fetch a user from the database
whether they filled in their username or email address.

Managing Roles in the Database
------------------------------

The end of this tutorial focuses on how to store and retrieve a list of roles
from the database. As mentioned previously, when your user is loaded, its
``getRoles()`` method returns the array of security roles that should be
assigned to the user. You can load this data from anywhere - a hardcoded
list used for all users (e.g. ``array('ROLE_USER')``), a Doctrine array
property called ``roles``, or via a Doctrine relationship, as you'll learn
about in this section.

.. caution::

    In a typical setup, you should always return at least 1 role from the ``getRoles()``
    method. By convention, a role called ``ROLE_USER`` is usually returned.
    If you fail to return any roles, it may appear as if your user isn't
    authenticated at all.

In this example, the ``AcmeUserBundle:User`` entity class defines a
many-to-many relationship with a ``AcmeUserBundle:Role`` entity class.
A user can be related to several roles and a role can be composed of
one or more users. The previous ``getRoles()`` method now returns
the list of related roles. Notice that ``__construct()`` and ``getRoles()``
methods have changed::

    // src/Acme/UserBundle/Entity/User.php
    namespace Acme\UserBundle\Entity;

    use Doctrine\Common\Collections\ArrayCollection;
    // ...

    class User implements AdvancedUserInterface, \Serializable
    {
        // ...

        /**
         * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
         *
         */
        private $roles;

        public function __construct()
        {
            $this->roles = new ArrayCollection();
        }

        public function getRoles()
        {
            return $this->roles->toArray();
        }

        // ...

    }

The ``AcmeUserBundle:Role`` entity class defines three fields (``id``,
``name`` and ``role``). The unique ``role`` field contains the role name
(e.g. ``ROLE_ADMIN``) used by the Symfony security layer to secure parts
of the application::

    // src/Acme/Bundle/UserBundle/Entity/Role.php
    namespace Acme\UserBundle\Entity;

    use Symfony\Component\Security\Core\Role\RoleInterface;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Table(name="acme_role")
     * @ORM\Entity()
     */
    class Role implements RoleInterface
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
         * @ORM\ManyToMany(targetEntity="User", mappedBy="roles")
         */
        private $users;

        public function __construct()
        {
            $this->users = new ArrayCollection();
        }

        /**
         * @see RoleInterface
         */
        public function getRole()
        {
            return $this->role;
        }

        // ... getters and setters for each property
    }

For brevity, the getter and setter methods are hidden, but you can
:ref:`generate them <book-doctrine-generating-getters-and-setters>`:

.. code-block:: bash

    $ php app/console doctrine:generate:entities Acme/UserBundle/Entity/User

Don't forget also to update your database schema:

.. code-block:: bash

    $ php app/console doctrine:schema:update --force

This will create the ``acme_role`` table and a ``user_role`` that stores
the many-to-many relationship between ``acme_user`` and ``acme_role``. If
you had one user linked to one role, your database might look something like
this:

.. code-block:: bash

    $ mysql> SELECT * FROM acme_role;
    +----+-------+------------+
    | id | name  | role       |
    +----+-------+------------+
    |  1 | admin | ROLE_ADMIN |
    +----+-------+------------+

    $ mysql> SELECT * FROM user_role;
    +---------+---------+
    | user_id | role_id |
    +---------+---------+
    |       1 |       1 |
    +---------+---------+

And that's it! When the user logs in, Symfony security system will call the
``User::getRoles`` method. This will return an array of ``Role`` objects
that Symfony will use to determine if the user should have access to certain
parts of the system.

.. sidebar:: What's the purpose of the RoleInterface?

    Notice that the ``Role`` class implements
    :class:`Symfony\\Component\\Security\\Core\\Role\\RoleInterface`. This is
    because Symfony's security system requires that the ``User::getRoles`` method
    returns an array of either role strings or objects that implement this interface.
    If ``Role`` didn't implement this interface, then ``User::getRoles``
    would need to iterate over all the ``Role`` objects, call ``getRole``
    on each, and create an array of strings to return. Both approaches are
    valid and equivalent.

.. _cookbook-doctrine-entity-provider-role-db-schema:

Improving Performance with a Join
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To improve performance and avoid lazy loading of roles when retrieving a user
from the custom entity provider, you can use a Doctrine join to the roles
relationship in the ``UserRepository::loadUserByUsername()`` method. This will
fetch the user and their associated roles with a single query::

    // src/Acme/UserBundle/Entity/UserRepository.php
    namespace Acme\UserBundle\Entity;

    // ...

    class UserRepository extends EntityRepository implements UserProviderInterface
    {
        public function loadUserByUsername($username)
        {
            $q = $this
                ->createQueryBuilder('u')
                ->select('u, r')
                ->leftJoin('u.roles', 'r')
                ->where('u.username = :username OR u.email = :email')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->getQuery();

            // ...
        }

        // ...
    }

The ``QueryBuilder::leftJoin()`` method joins and fetches related roles from
the ``AcmeUserBundle:User`` model class when a user is retrieved by their email
address or username.

.. _`cookbook-security-serialize-equatable`:

Understanding serialize and how a User is Saved in the Session
--------------------------------------------------------------

If you're curious about the importance of the ``serialize()`` method inside
the ``User`` class or how the User object is serialized or deserialized, then
this section is for you. If not, feel free to skip this.

Once the user is logged in, the entire User object is serialized into the
session. On the next request, the User object is deserialized. Then, value
of the ``id`` property is used to re-query for a fresh User object from the
database. Finally, the fresh User object is compared in some way to the deserialized
User object to make sure that they represent the same user. For example, if
the ``username`` on the 2 User objects doesn't match for some reason, then
the user will be logged out for security reasons.

Even though this all happens automatically, there are a few important side-effects.

First, the :phpclass:`Serializable` interface and its ``serialize`` and ``unserialize``
methods have been added to allow the ``User`` class to be serialized
to the session. This may or may not be needed depending on your setup,
but it's probably a good idea. In theory, only the ``id`` needs to be serialized,
because the :method:`Symfony\\Bridge\\Doctrine\\Security\\User\\EntityUserProvider::refreshUser`
method refreshes the user on each request by using the ``id`` (as explained
above). However in practice, this means that the User object is reloaded from
the database on each request using the ``id`` from the serialized object.
This makes sure all of the User's data is fresh.

Symfony also uses the ``username``, ``salt``, and ``password`` to verify
that the User has not changed between requests. Failing to serialize
these may cause you to be logged out on each request. If your User implements
the :class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface`,
then instead of these properties being checked, your ``isEqualTo`` method
is simply called, and you can check whatever properties you want. Unless
you understand this, you probably *won't* need to implement this interface
or worry about it.

.. versionadded:: 2.1
    In Symfony 2.1, the ``equals`` method was removed from ``UserInterface``
    and the ``EquatableInterface`` was introduced in its place.
