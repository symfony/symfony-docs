.. index::
   single: Security; User provider
   single: Security; Entity provider

How to Load Security Users from the Database (the Entity Provider)
==================================================================

Symfony's security system can load security users from anywhere - like a
database, via Active Directory or an OAuth server. This article will show
you how to load your users from the database via a Doctrine entity.

Introduction
------------

.. tip::

    Before you start, you should check out `FOSUserBundle`_. This external
    bundle allows you to load users from the database (like you'll learn here)
    *and* gives you built-in routes & controllers for things like login,
    registration and forgot password. But, if you need to heavily customize
    your user system *or* if you want to learn how things work, this tutorial
    is even better.

Loading users via a Doctrine entity has 2 basic steps:

#. :ref:`Create your User entity <security-crete-user-entity>`
#. :ref:`Configure security.yml to load from your entity <security-config-entity-provider>`

Afterwards, you can learn more about :ref:`forbidding inactive users <security-advanced-user-interface>`,
:ref:`using a custom query <authenticating-someone-with-a-custom-entity-provider>`
and :ref:`user serialization to the session <cookbook-security-serialize-equatable>`

.. _security-crete-user-entity:
.. _the-data-model:

1) Create your User Entity
--------------------------

For this entry, suppose that you already have a ``User`` entity inside an
``AppBundle`` with the following fields: ``id``, ``username``, ``password``,
``email`` and ``isActive``:

.. code-block:: php

    // src/AppBundle/Entity/User.php
    namespace AppBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Security\Core\User\UserInterface;

    /**
     * @ORM\Table(name="app_users")
     * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
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

        public function getUsername()
        {
            return $this->username;
        }

        public function getSalt()
        {
            // you *may* need a real salt depending on your encoder
            // see section on salt below
            return null;
        }

        public function getPassword()
        {
            return $this->password;
        }

        public function getRoles()
        {
            return array('ROLE_USER');
        }

        public function eraseCredentials()
        {
        }

        /** @see \Serializable::serialize() */
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

        /** @see \Serializable::unserialize() */
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

To make things shorter, some of the getter and setter methods aren't shown.
But you can :ref:`generate <book-doctrine-generating-getters-and-setters>` these
by running:

.. code-block:: bash

    $ php app/console doctrine:generate:entities AppBundle/Entity/User

Next, make sure to :ref:`create the database table <book-doctrine-creating-the-database-tables-schema>`:

.. code-block:: bash

    $ php app/console doctrine:schema:update --force

What's this UserInterface?
~~~~~~~~~~~~~~~~~~~~~~~~~~

So far, this is just a normal entity. But in order to use this class in the
security system, it must implement
:class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`. This
forces the class to have the five following methods:

* :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getRoles`
* :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getPassword`
* :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getSalt`
* :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getUsername`
* :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::eraseCredentials`

To learn more about each of these, see :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`.

What do the serialize and unserialize Methods do?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

At the end of each request, the User object is serialized to the session.
On the next request, it's unserialized. To help PHP do this correctly, you
need to implement ``Serializable``. But you don't need to serialize everything:
you only need a few fields (the ones shown above plus a few extra if you
decide to implement :ref:`AdvancedUserInterface <security-advanced-user-interface>`).
On each request, the ``id`` is used to query for a fresh ``User`` object
from the database.

Want to know more? See :ref:`cookbook-security-serialize-equatable`.

.. _authenticating-someone-against-a-database:
.. _security-config-entity-provider:

2) Configure Security to load from your Entity
----------------------------------------------

Now that you have a ``User`` entity that implements ``UserInterface``, you
just need to tell Symfony's security system about it in ``security.yml``.

In this example, the user will enter their username and password via HTTP
basic authentication. Symfony will query for a ``User`` entity matching
the username and then check the password (more on passwords in a moment):

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            encoders:
                AppBundle\Entity\User:
                    algorithm: bcrypt

            # ...

            providers:
                our_db_provider:
                    entity:
                        class: AppBundle:User
                        property: username
                        # if you're using multiple entity managers
                        # manager_name: customer

            firewalls:
                default:
                    pattern:    ^/
                    http_basic: ~
                    provider: our_db_provider

            # ...

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <encoder class="AppBundle\Entity\User"
                algorithm="bcrypt"
            />

            <!-- ... -->

            <provider name="our_db_provider">
                <entity class="AppBundle:User" property="username" />
            </provider>

            <firewall name="default" pattern="^/" provider="our_db_provider">
                <http-basic />
            </firewall>
            
            <!-- ... -->
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'encoders' => array(
                'AppBundle\Entity\User' => array(
                    'algorithm' => 'bcrypt',
                ),
            ),
            // ...
            'providers' => array(
                'our_db_provider' => array(
                    'entity' => array(
                        'class'    => 'AppBundle:User',
                        'property' => 'username',
                    ),
                ),
            ),
            'firewalls' => array(
                'default' => array(
                    'pattern' => '^/',
                    'http_basic' => null,
                    'provider' => 'our_db_provider',
                ),
            ),
            // ...
        ));

First, the ``encoders`` section tells Symfony to expect that the passwords
in the database will be encoded using ``bcrypt``. Second, the ``providers``
section creates a "user provider" called ``our_db_provider`` that knows to
query from your ``AppBundle:User`` entity by the ``username`` property. The
name ``our_db_provider`` isn't important: it just needs to match the value
of the ``provider`` key under your firewall. Or, if you don't set the ``provider``
key under your firewall, the first "user provider" is automatically used.

.. include:: /cookbook/security/_ircmaxwell_password-compat.rst.inc

Creating your First User
~~~~~~~~~~~~~~~~~~~~~~~~

To add users, you can implement a :doc:`registration form </cookbook/doctrine/registration_form>`
or add some `fixtures`_. This is just a normal entity, so there's nothing
tricky, *except* that you need to encode each user's password. But don't
worry, Symfony gives you a service that will do this for you. See :ref:`security-encoding-password`
for details.

Below is an export of the ``app_users`` table from MySQL with user ``admin``
and password ``admin`` (which has been encoded).

.. code-block:: bash

    $ mysql> SELECT * FROM app_users;
    +----+----------+------------------------------------------+--------------------+-----------+
    | id | username | password                                 | email              | is_active |
    +----+----------+------------------------------------------+--------------------+-----------+
    |  1 | admin    | d033e22ae348aeb5660fc2140aec35850c4da997 | admin@example.com  |         1 |
    +----+----------+------------------------------------------+--------------------+-----------+

.. sidebar:: Do you need to a Salt property?

    If you use ``bcrypt``, no. Otherwise, yes. All passwords must be hashed
    with a salt, but ``bcrypt`` does this internally. Since this tutorial
    *does* use ``bcrypt``, the ``getSalt()`` method in ``User`` can just
    return ``null`` (it's not used). If you use a different algorithm, you'll
    need to uncomment the ``salt`` lines in the ``User`` entity and add a
    persisted ``salt`` property.

.. _security-advanced-user-interface:

Forbid Inactive Users (AdvancedUserInterface)
---------------------------------------------

If a User's ``isActive`` property is set to ``false`` (i.e. ``is_active``
is 0 in the database), the user will still be able to login to the site
normally. This is easily fixable.

To exclude inactive users, change your ``User`` clas to implement
:class:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface`.
This extends :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`,
so you only need the new interface::

    // src/AppBundle/Entity/User.php

    use Symfony\Component\Security\Core\User\AdvancedUserInterface;
    // ...

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

        // serialize and unserialize must be updated - see below
        public function serialize()
        {
            return serialize(array(
                // ...
                $this->isActive
            ));
        }
        public function unserialize($serialized)
        {
            list (
                // ...
                $this->isActive
            ) = unserialize($serialized);
        }
    }

The :class:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface`
interface adds four extra methods to validate the account status:

* :method:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface::isAccountNonExpired`
  checks whether the user's account has expired;
* :method:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface::isAccountNonLocked`
  checks whether the user is locked;
* :method:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface::isCredentialsNonExpired`
  checks whether the user's credentials (password) has expired;
* :method:`Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface::isEnabled`
  checks whether the user is enabled.

If *any* of these return ``false``, the user won't be allowed to login. You
can choose to have persisted properties for all of these, or whatever you
need (in this example, only ``isActive`` pulls from the database).

So what's the difference between the methods? Each returns a slightly different
error message (and these can be translated when you render them in your login
template to customize them further).

.. note::

    If you use ``AdvancedUserInterface``, you also need to add any of the
    properties used by these methods (like ``isActive``) to the ``serialize()``
    and ``unserialize()`` methods. If you *don't* do this, your user may
    not be deserialized correctly from the session on each request.

Congrats! Your database-loading security system is all setup! Next, add a
true :doc:`login form </cookbook/security/form_login>` instead of HTTP Basic
or keep reading for other topics.

.. _authenticating-someone-with-a-custom-entity-provider:

Using a Custom Query to Load the User
-------------------------------------

It would be great if a user could login with their username *or* email, as
both are unique in the database. Unfortunately, the native entity provider
is only able to handle querying via a single property on the user.

To do this, make your ``UserRepository`` implement a special
:class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`. This
interface requires three methods: ``loadUserByUsername($username)``,
``refreshUser(UserInterface $user)``, and ``supportsClass($class)``::

    // src/AppBundle/Entity/UserRepository.php
    namespace AppBundle\Entity;

    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Doctrine\ORM\EntityRepository;

    class UserRepository extends EntityRepository implements UserProviderInterface
    {
        public function loadUserByUsername($username)
        {
            $user = $this->createQueryBuilder('u')
                ->where('u.username = :username OR u.email = :email')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->getQuery()
                ->getOneOrNullResult()

            if ($user) {
                $message = sprintf(
                    'Unable to find an active admin AppBundle:User object identified by "%s".',
                    $username
                );
                throw new UsernameNotFoundException($message);
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

For more details on these methods, see :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`.

.. tip::

    Don't forget to add the repository class to the 
    :ref:`mapping definition of your entity <book-doctrine-custom-repository-classes>`.

To finish this, just remove the ``property`` key from the user provider in
``security.yml``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...
            providers:
                our_db_provider:
                    entity:
                        class: AppBundle:User
            # ...

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <!-- ... -->

            <provider name="our_db_provider">
                <entity class="AppBundle:User" />
            </provider>

            <!-- ... -->
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            ...,
            'providers' => array(
                'our_db_provider' => array(
                    'entity' => array(
                        'class' => 'AppBundle:User',
                    ),
                ),
            ),
            ...,
        ));

This tells Symfony to *not* query automatically for the User. Instead, when
someone logs in, the ``loadUserByUsername()`` method on ``UserRepository``
will be called.

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

.. caution::

    In order to work with the security configuration examples on this page
    all roles must be prefixed with ``ROLE_`` (see
    the :ref:`section about roles <book-security-roles>` in the book). For
    example, your roles will be ``ROLE_ADMIN`` or ``ROLE_USER`` instead of
    ``ADMIN`` or ``USER``.

In this example, the ``AppBundle:User`` entity class defines a
many-to-many relationship with a ``AppBundle:Role`` entity class.
A user can be related to several roles and a role can be composed of
one or more users. The previous ``getRoles()`` method now returns
the list of related roles. Notice that ``__construct()`` and ``getRoles()``
methods have changed::

    // src/AppBundle/Entity/User.php
    namespace AppBundle\Entity;

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

The ``AppBundle:Role`` entity class defines three fields (``id``,
``name`` and ``role``). The unique ``role`` field contains the role name
(e.g. ``ROLE_ADMIN``) used by the Symfony security layer to secure parts
of the application::

    // src/AppBundle/Entity/Role.php
    namespace AppBundle\Entity;

    use Symfony\Component\Security\Core\Role\RoleInterface;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Table(name="app_role")
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

    $ php app/console doctrine:generate:entities AppBundle/Entity/User

Don't forget also to update your database schema:

.. code-block:: bash

    $ php app/console doctrine:schema:update --force

This will create the ``app_role`` table and a ``user_role`` that stores
the many-to-many relationship between ``app_user`` and ``app_role``. If
you had one user linked to one role, your database might look something like
this:

.. code-block:: bash

    $ mysql> SELECT * FROM app_role;
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

    // src/AppBundle/Entity/UserRepository.php
    namespace AppBundle\Entity;

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
the ``AppBundle:User`` model class when a user is retrieved by their email
address or username.

.. _`cookbook-security-serialize-equatable`:

Understanding serialize and how a User is Saved in the Session
--------------------------------------------------------------

If you're curious about the importance of the ``serialize()`` method inside
the ``User`` class or how the User object is serialized or deserialized, then
this section is for you. If not, feel free to skip this.

Once the user is logged in, the entire User object is serialized into the
session. On the next request, the User object is deserialized. Then, the value
of the ``id`` property is used to re-query for a fresh User object from the
database. Finally, the fresh User object is compared to the deserialized
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
above). This gives us a "fresh" User object.

But Symfony also uses the ``username``, ``salt``, and ``password`` to verify
that the User has not changed between requests (it also calls your ``AdvancedUserInterface``
methods if you implement it). Failing to serialize these may cause you to
be logged out on each request. If your User implements the
:class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface`,
then instead of these properties being checked, your ``isEqualTo`` method
is simply called, and you can check whatever properties you want. Unless
you understand this, you probably *won't* need to implement this interface
or worry about it.

.. _fixtures: http://symfony.com/doc/master/bundles/DoctrineFixturesBundle/index.html
.. _FOSUserBundle: https://github.com/FriendsOfSymfony/FOSUserBundle
