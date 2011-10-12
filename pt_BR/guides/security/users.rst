.. index::
   single: Security; Users

Users
=====

Security only makes sense because your application is accessed by clients you
cannot trust. A client can be a human behind a browser, but also a device, a
web service, or even a bot.

Defining the Users
------------------

During authentication, Symfony2 tries to retrieve a user matching the client
credentials (most of the time a username and a password). As Symfony2 makes no
assumption about the client/user PHP representation, it's up to the
application to define a user class and hook it up with Symfony2 via a user
provider class.

.. index::
   single: Security; UserProviderInterface

UserProviderInterface
~~~~~~~~~~~~~~~~~~~~~

The user provider must implement
:class:`Symfony\\Component\\Security\\User\\UserProviderInterface`::

    interface UserProviderInterface
    {
         function loadUserByUsername($username);
    }

The ``loadUserByUsername()`` method receives the username and must return the
User object. If the user cannot be found, it must throw a
:class:`Symfony\\Component\\Security\\Exception\\UsernameNotFoundException`
exception.

.. tip::

    Most of the time, you don't need to define a user provider yourself as
    Symfony2 comes with the most common ones. See the next section for more
    information.

.. index::
   single: Security; AccountInterface

AccountInterface
~~~~~~~~~~~~~~~~

The user provider must return objects that implement
:class:`Symfony\\Component\\Security\\User\\AccountInterface`::

    interface AccountInterface
    {
        function __toString();
        function getRoles();
        function getPassword();
        function getSalt();
        function getUsername();
        function eraseCredentials();
    }

* ``__toString()``: Returns a string representation for the user;
* ``getRoles()``: Returns the roles granted to the user;
* ``getPassword()``: Returns the password used to authenticate the user;
* ``getSalt()``: Returns the salt;
* ``getUsername()``: Returns the username used to authenticate the user;
* ``eraseCredentials()``: Removes sensitive data from the user.

.. index::
   single: Security; Password encoding

Encoding Passwords
~~~~~~~~~~~~~~~~~~

Instead of storing passwords in clear, you can encode them. When doing so, you
should use a
:class:`Symfony\\Component\\Security\\Encoder\\PasswordEncoderInterface`
object::

    interface PasswordEncoderInterface
    {
        function encodePassword($raw, $salt);
        function isPasswordValid($encoded, $raw, $salt);
    }

.. note::

    During authentication, Symfony2 will use the ``isPasswordValid()`` method
    to check the user password; read the next section to learn how to make
    your authentication provider aware of the encoder to use.

For most use case, use
:class:`Symfony\\Component\\Security\\Encoder\\MessageDigestPasswordEncoder`::

    $user = new User();

    $encoder = new MessageDigestPasswordEncoder('sha1');
    $password = $encoder->encodePassword('MyPass', $user->getSalt());
    $user->setPassword($password);

When encoding your passwords, it's better to also define a unique salt per user
(the ``getSalt()`` method can return the primary key if users are persisted in
a database for instance.)

.. index::
   single: Security; AdvancedAccountInterface

AdvancedAccountInterface
~~~~~~~~~~~~~~~~~~~~~~~~

Before and after authentication, Symfony2 can check various flags on the user.
If your user class implements
:class:`Symfony\\Component\\Security\\User\\AdvancedAccountInterface` instead
of :class:`Symfony\\Component\\Security\\User\\AccountInterface`, Symfony2
will make the associated checks automatically::

    interface AdvancedAccountInterface extends AccountInterface
    {
        function isAccountNonExpired();
        function isAccountNonLocked();
        function isCredentialsNonExpired();
        function isEnabled();
    }

* ``isAccountNonExpired()``: Returns ``true`` when the user's account has
  expired;
* ``isAccountNonLocked()``: Returns ``true`` when the user is locked;
* ``isCredentialsNonExpired()``: Returns ``true`` when the user's credentials
  (password) has expired;
* ``isEnabled()``: Returns ``true`` when the user is enabled.

.. note::

    The :class:`Symfony\\Component\\Security\\User\\AdvancedAccountInterface`
    relies on an
    :class:`Symfony\\Component\\Security\\User\\AccountCheckerInterface`
    object to do the pre-authentication and post-authentication checks.

.. index::
   single: Security; User Providers

Defining a Provider
-------------------

As we have seen in the previous section, a provider implements
:class:`Symfony\\Component\\Security\\User\\UserProviderInterface`. Symfony2
comes with provider for in-memory users, Doctrine Entities, Doctrine
Documents, and defines a base class for any DAO provider you might want to
create.

.. index::
   single: Security; In-memory user provider

In-memory Provider
~~~~~~~~~~~~~~~~~~

The in-memory provider is a great provider to secure a personal website backend
or a prototype. It is also the best provider when writing unit tests:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            providers:
                main:
                    users:
                        foo: { password: foo, roles: ROLE_USER }
                        bar: { password: bar, roles: [ROLE_USER, ROLE_ADMIN] }
                encoded:
                    password_encoder: sha1
                    users:
                        foo: { password: 0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33, roles: ROLE_USER }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <provider name="main">
                <user name="foo" password="foo" roles="ROLE_USER" />
                <user name="bar" password="bar" roles="ROLE_USER,ROLE_ADMIN" />
            </provider>

            <provider name="encoded">
                <password-encoder>sha1</password-encoder>
                <user name="foo" password="0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33" roles="ROLE_USER" />
            </provider>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'providers' => array(
                'main' => array('users' => array(
                    'foo' => array('password' => 'foo', 'roles' => 'ROLE_USER'),
                    'bar' => array('password' => 'bar', 'roles' => array('ROLE_USER', 'ROLE_ADMIN')),
                )),
                'encoded' => array('password_encoder' => 'sha1', 'users' => array(
                    'foo' => array('password' => '0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33', 'roles' => 'ROLE_USER'),
                )),
            ),
        ));

The above configuration defines two in-memory providers. As you can see, the
second one uses 'sha1' to encode the user passwords.

.. index::
   single: Security; Doctrine Entity Provider
   single: Doctrine; Doctrine Entity Provider

Doctrine Entity Provider
~~~~~~~~~~~~~~~~~~~~~~~~

Most of the time, users are described by a Doctrine Entity::

    /**
     * @Entity
     */
    class User implements AccountInterface
    {
        // ...
    }

In such a case, you can use the default Doctrine provider without creating one
yourself:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            providers:
                main:
                    password_encoder: sha1
                    entity: { class: SecurityBundle:User, property: username }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <provider name="main">
                <password-encoder>sha1</password-encoder>
                <entity class="SecurityBundle:User" property="username" />
            </provider>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'providers' => array(
                'main' => array(
                    'password_encoder' => 'sha1',
                    'entity' => array('class' => 'SecurityBundle:User', 'property' => 'username'),
                ),
            ),
        ));

The ``entity`` entry configures the Entity class to use for the user, and
``property`` the PHP column name where the username is stored.

If retrieving the user is more complex than a simple ``findOneBy()`` call,
remove the ``property`` setting and make your Entity Repository class
implement :class:`Symfony\\Component\\Security\\User\\UserProviderInterface`::

    /**
     * @Entity(repositoryClass="SecurityBundle:UserRepository")
     */
    class User implements AccountInterface
    {
        // ...
    }

    class UserRepository extends EntityRepository implements UserProviderInterface
    {
        public function loadUserByUsername($username)
        {
            // do whatever you need to retrieve the user from the database
            // code below is the implementation used when using the property setting

            return $this->findOneBy(array('username' => $username));
        }
    }

.. tip::

    If you use the
    :class:`Symfony\\Component\\Security\\User\\AdvancedAccountInterface`
    interface, don't check the various flags (locked, expired, enabled, ...)
    when retrieving the user from the database as this will be managed by the
    authentication system automatically (and proper exceptions will be thrown
    if needed). If you have special flags, override the default
    :class:`Symfony\\Component\\Security\\User\\AccountCheckerInterface`
    implementation.

.. index::
   single: Security; Doctrine Document Provider
   single: Doctrine; Doctrine Document Provider

Doctrine Document Provider
~~~~~~~~~~~~~~~~~~~~~~~~~~

Most of the time, users are described by a Doctrine Document::

    /**
     * @Document
     */
    class User implements AccountInterface
    {
        // ...
    }

In such a case, you can use the default Doctrine provider without creating one
yourself:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            providers:
                main:
                    password_encoder: sha1
                    document: { class: SecurityBundle:User, property: username }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <provider name="main">
                <password-encoder>sha1</password-encoder>
                <document class="SecurityBundle:User" property="username" />
            </provider>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'providers' => array(
                'main' => array(
                    'password_encoder' => 'sha1',
                    'document' => array('class' => 'SecurityBundle:User', 'property' => 'username'),
                ),
            ),
        ));

The ``document`` entry configures the Document class to use for the user, and
``property`` the PHP column name where the username is stored.

If retrieving the user is more complex than a simple ``findOneBy()`` call,
remove the ``property`` setting and make your Document Repository class
implement :class:`Symfony\\Component\\Security\\User\\UserProviderInterface`::

    /**
     * @Document(repositoryClass="SecurityBundle:UserRepository")
     */
    class User implements AccountInterface
    {
        // ...
    }

    class UserRepository extends DocumentRepository implements UserProviderInterface
    {
        public function loadUserByUsername($username)
        {
            // do whatever you need to retrieve the user from the database
            // code below is the implementation used when using the property setting

            return $this->findOneBy(array('username' => $username));
        }
    }

.. tip::

    If you use the
    :class:`Symfony\\Component\\Security\\User\\AdvancedAccountInterface`
    interface, don't check the various flags (locked, expired, enabled, ...)
    when retrieving the user from the database as this will be managed by the
    authentication system automatically (and proper exceptions will be thrown
    if needed). If you have special flags, override the default
    :class:`Symfony\\Component\\Security\\User\\AccountCheckerInterface`
    implementation.

Retrieving the User
-------------------

After authentication, the user is accessed via the security context::

    $user = $container->get('security.context')->getUser();

You can also check if the user is authenticated with the ``isAuthenticated()``
method::

    $container->get('security.context')->isAuthenticated();

.. tip::

    Be aware that anonymous users are considered authenticated. If you want to
    check if a user is "fully authenticated" (non-anonymous), you need to
    check if the user has the special ``IS_AUTHENTICATED_FULLY`` role (or
    check that the user has not the ``IS_AUTHENTICATED_ANONYMOUSLY`` role).

.. index::
   single: Security; Roles

Roles
-----

A User can have as many roles as needed. Roles are usually defined as strings,
but they can be any object implementing
:class:`Symfony\\Component\\Security\\Role\\RoleInterface` (roles are always
objects internally.) Roles defined as strings should begin with the ``ROLE_``
prefix to be automatically managed by Symfony2.

The roles are used by the access decision manager to secure resources. Read
the :doc:`Authorization </guides/security/authorization>` document to learn
more about access control, roles, and voters.

.. tip::

    If you define your own roles with a dedicated Role class, don't use the
    ``ROLE_`` prefix.

.. index::
   single: Security; Roles (Hierarchical)

Hierarchical Roles
~~~~~~~~~~~~~~~~~~

Instead of associating many roles to users, you can define role inheritance
rules by creating a role hierarchy:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            role_hierarchy:
                ROLE_ADMIN:       ROLE_USER
                ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <role-hierarchy>
                <role id="ROLE_ADMIN">ROLE_USER</role>
                <role id="ROLE_SUPER_ADMIN">ROLE_USER,ROLE_ADMIN,ROLE_ALLOWED_TO_SWITCH</role>
            </role-hierarchy>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'role_hierarchy' => array(
                'ROLE_ADMIN'       => 'ROLE_USER',
                'ROLE_SUPER_ADMIN' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'),
            ),
        ));

In the above configuration, users with 'ROLE_ADMIN' role will also have the
'ROLE_USER' role. The 'ROLE_SUPER_ADMIN' role has multiple inheritance.
