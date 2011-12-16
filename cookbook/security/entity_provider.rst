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
`EntityUserProvider`_ object bundled with the framework and some configuration.
Finally, the tutorial will demonstrate how to create a custom
`EntityUserProvider`_ object to retrieve users from a database with custom
conditions.

This tutorial assumes there is a bootstrapped and loaded
``Acme\Bundle\UserBundle`` bundle in the application kernel.

The Data Model
--------------

For the purpose of this cookbook, the ``AcmeUserBundle`` bundle contains a
``User`` entity class with the following fields: ``id``, ``username``, ``salt``,
``password``, ``role`` and ``isActive``. The ``role`` field stores the role
string assigned to the user and the ``isActive`` value tells whether or not the
user account is active.

To make it shorter, the getter and setter methods for each have been removed to
focus on the most important methods that come from the `UserInterface`_.

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
         * @ORM\Column(name="role", type="string", length=20)
         */
        private $role;

        /**
         * @ORM\Column(name="is_active", type="boolean")
         */
        private $isActive;

        public function __construct()
        {
            $this->role = 'ROLE_USER';
            $this->isActive = true;
        }

        public function getRoles()
        {
            return array($this->role);
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
security layer, the entity class must implement the `UserInterface`_. This
interface forces the class to implement the six following methods:

* ``getUsername()`` returns the unique username,
* ``getSalt()`` returns the unique salt,
* ``getPassword()`` returns the encoded password,
* ``getRoles()`` returns an array of associated roles,
* ``equals()`` compares the current object with an other `UserInterface`_
  instance,
* ``eraseCredentials()`` removes sensible information stored in the
  `UserInterface`_ object.

To keep it simple, the ``equals()`` method just compares the ``username`` field
but it's also possible to make more checks depending on the complexity of your
data model. In the other hand, the ``eraseCredentials()`` method remains empty
as we don't care about it in this tutorial.

Below is an export of my ``User`` table from MySQL.

.. code-block::text

    mysql> select * from user;
    +----+----------+------------------------------------------+------------------------------------------+-----------------+-----------+
    | id | username | salt                                     | password                                 | role            | is_active |
    +----+----------+------------------------------------------+------------------------------------------+-----------------+-----------+
    |  1 | hhamon   | 7308e59b97f6957fb42d66f894793079c366d7c2 | 09610f61637408828a35d7debee5b38a8350eebe | ROLE_SUPERADMIN |         1 |
    |  2 | jsmith   | ce617a6cca9126bf4036ca0c02e82deea081e564 | 8390105917f3a3d533815250ed7c64b4594d7ebf | ROLE_ADMIN      |         1 |
    |  3 | maxime   | cd01749bb995dc658fa56ed45458d807b523e4cf | 9764731e5f7fb944de5fd8efad4949b995b72a3c | ROLE_ADMIN      |         0 |
    |  4 | donald   | 6683c2bfd90c0426088402930cadd0f84901f2f4 | 5c3bcec385f59edcc04490d1db95fdb8673bf612 | ROLE_USER       |         1 |
    +----+----------+------------------------------------------+------------------------------------------+-----------------+-----------+
    4 rows in set (0.00 sec)

The database now contains four users with different roles and statuses. The next
part will focus on how to authenticate one of these users thanks to the Doctrine
entity user provider and a couple of lines of configuration.

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

        role_hierarchy:
            ROLE_ADMIN:       ROLE_USER
            ROLE_SUPERADMIN:  [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

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
for **active** users, who **own** ``ROLE_ADMIN`` or ``ROLE_SUPERADMIN`` role.
As of now, we still can authenticate with both users ``maxime`` and
``donald``...

Authenticating Someone with a Custom Entity Provider
----------------------------------------------------

To limit access to the administration area to active people with ``ROLE_ADMIN``
or ``ROLE_SUPERADMIN``, the best way is to write a custom entity provider that
fetches a user with a custom SQL query.

The good news is that a Doctrine repository object can act as an entity user
provider if it implements the `UserProviderInterface`_. This interface comes
with three methods to implement:

* ``loadUserByUsername()`` that fetches and returns a `UserInterface`_
  instance by its unique username. Otherwise, it must throw a
  `UsernameNotFoundException`_ exception to indicate the security layer
  there is no user matching the credentials.
* ``refreshUser()`` that refreshes and returns a `UserInterface`_ instance.
  Otherwise it must throw a `UnsupportedUserException`_ exception to
  indicate the security layer we are unable to refresh the user.
* ``supportsClass()`` must return ``true`` if the fully qualified class name
  passed as its sole argument is supported by the entity provider.

The code below shows the implementation of the `UserProviderInterface`_ in the
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
                ->getAdministratorQueryBuilder()
                ->where('u.username = :username')
                ->setParameter('username', $username)
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
            return 'Acme\Bundle\UserBundle\Entity\User' === $class;
        }

        private function getAdministratorQueryBuilder()
        {
            $qb = $this
                ->createQueryBuilder('u')
                ->where('u.isActive = :status')
                ->andWhere('u.role IN (:role)')
                ->setParameter('status', true)
                ->setParameter('role', array('ROLE_ADMIN', 'ROLE_SUPERADMIN'))
            ;

            return $qb;
        }
    }

To finish the implementation, the configuration of the security layer must be
changed to tell Symfony to use the new custom entity provider instead of the
generic Doctrine entity provider. It's trival to achieve by removing the
``property`` variable in the ``security.providers.administrators.entity`` in the
``security.yml`` file.

.. code-block::yaml

    # app/config/security.yml
    security:
        # ...
        providers:
            administrators:
                entity: { class: AcmeUserBundle:User }
        # ...

By doing this, the security layer will use an instance of ``UserRepository`` and
call its ``loadUserByUsername()`` method to fetch an active administrator user
from the database.

.. _`EntityUserProvider`: http://api.symfony.com/2.0/Symfony/Bridge/Doctrine/Security/User/EntityUserProvider.html
.. _`UserInterface`: http://api.symfony.com/2.0/Symfony/Component/Security/Core/User/UserInterface.html
.. _`UserProviderInterface`: http://api.symfony.com/2.0/Symfony/Component/Security/Core/User/UserProviderInterface.html
.. _`UsernameNotFoundException`: http://api.symfony.com/2.0/Symfony/Component/Security/Core/Exception/UsernameNotFoundException.html
.. _`UnsupportedUserException`: http://api.symfony.com/2.0/Symfony/Component/Security/Core/Exception/UnsupportedUserException.html
.. _`SecurityBundle`: http://symfony.com/doc/current/reference/configuration/security.html