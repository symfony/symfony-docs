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
:class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`::

    interface UserProviderInterface
    {
         function loadUserByUsername($username);
         function loadUserByAccount(AccountInterface $account);
         function supportsClass($class);
    }

* ``loadUserByUsername()``: Receives a username and returns the corresponding
                            user object. If the username is not found, it must
                            throw :class:`Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException`
                            exception.
* ``loadUserByAccount()``: Receives an ``AccountInterface`` object, and must reload
                           the corresponding user object, or just merge the user
                           into the identity map of an ``EntityManager``. If the
                           given account's class is not supported, it must throw
                           :class:`Symfony\\Component\\Security\\Core\\Exception\\UnsupportedAccountException`
                           exception.
* ``supportsClass()``: Receives an account's class and returns whether the class
                       is supported by the provider. 

.. tip::

    Most of the time, you don't need to define a user provider yourself as
    Symfony2 comes with the most common ones. See the next section for more
    information.

.. index::
   single: Security; AccountInterface

AccountInterface
~~~~~~~~~~~~~~~~

The user provider must return objects that implement
:class:`Symfony\\Component\\Security\\Core\\User\\AccountInterface`::

    interface AccountInterface
    {
        function getRoles();
        function getPassword();
        function getSalt();
        function getUsername();
        function eraseCredentials();
        function equals(AccountInterface $account);
    }

* ``getRoles()``: Returns the roles granted to the user;
* ``getPassword()``: Returns the password used to authenticate the user;
* ``getSalt()``: Returns the salt;
* ``getUsername()``: Returns the username used to authenticate the user;
* ``eraseCredentials()``: Removes sensitive data from the user.
* ``equals()``: Whether data which is relevant for the authentication status has
                changed.

.. index::
   single: Security; Password encoding

Encoding Passwords
~~~~~~~~~~~~~~~~~~

Instead of storing passwords in clear, you can encode them. When doing so, you
should retrieve a
:class:`Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface`
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

If you need to encode passwords in your application code, for example when the
user is signing up, or changing his password, you can retrieve the encoder from
the :class:`Symfony\\Component\\Security\\Core\\Encoder\\EncoderFactoryInterface`::

    $factory = $this->container->get('security.encoder_factory');
    $user = new User();

    $encoder = $factory->getEncoder($user);
    $password = $encoder->encodePassword('MyPass', $user->getSalt());
    $user->setPassword($password);

When encoding your passwords, it's better to also define a unique salt per user
(the ``getSalt()`` method can return the primary key if users are persisted in
a database for instance).

.. index::
   single: Security; Configuring Encoders

Configuring Encoders
~~~~~~~~~~~~~~~~~~~~

In this section, we will look at how you can set-up different encoders for your
users. An encoder can either be one of the built-in encoders (
:class:`Symfony\\Component\\Security\\Core\\Encoder\\PlaintextPasswordEncoder`, or
:class:`Symfony\\Component\\Security\\Core\\Encoder\\MessageDigestPasswordEncoder`),
or even a custom service. The following lists all available configuration
options, you only need to select the one which suits your needs best:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            encoders:
                MyBundle/Entity/MyUser: sha512
                MyBundle/Entity/MyUser: plaintext
                MyBundle/Entity/MyUser:
                    algorithm: sha512
                    encode-as-base64: true
                    iterations: 5
                MyBundle/Entity/MyUser:
                    id: my.custom.encoder.service.id

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <security:config>
            <encoders>
                <encoder class="MyBundle\Entity\MyUser" algorithm="sha512" />
                <encoder class="MyBundle\Entity\MyUser" algorithm="plaintext" />
                <encoder class="MyBundle\Entity\MyUser"
                         algorithm="sha512"
                         encode-as-base64="true"
                         iterations="5"
                         />
                <encoder class="MyBundle\Entity\MyUser"
                         id="my.custom.encoder.service.id"
                         />
            </encoders>
        </security:config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'encoders' => array(
                'MyBundle\Entity\MyUser' => 'sha512',
                'MyBundle\Entity\MyUser' => 'plaintext',
                'MyBundle\Entity\MyUser' => array(
                    'algorithm' => 'sha512',
                    'encode-as-base64' => true,
                    'iterations' => 5,
                ),
                'MyBundle\Entity\MyUser' => array(
                    'id' => 'my.custom.encoder.service.id',
                ),
            ),
        ));

.. note::

    You must define an encoder for each of your user classes, but the
    configuration *must not* overlap. If you want to use the same encoder for
    all classes you can simply specify
    :class:`Symfony\\Component\\Security\\Core\\User\\AccountInterface` as class
    since all your user classes will implement it.

.. index::
   single: Security; AdvancedAccountInterface

AdvancedAccountInterface
~~~~~~~~~~~~~~~~~~~~~~~~

Before and after authentication, Symfony2 can check various flags on the user.
If your user class implements
:class:`Symfony\\Component\\Security\\Core\\User\\AdvancedAccountInterface` instead
of :class:`Symfony\\Component\\Security\\Core\\User\\AccountInterface`, Symfony2
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

    The :class:`Symfony\\Component\\Security\\Core\\User\\AdvancedAccountInterface`
    relies on an
    :class:`Symfony\\Component\\Security\\Core\\User\\AccountCheckerInterface`
    object to do the pre-authentication and post-authentication checks.

.. index::
   single: Security; User Providers

Defining a Provider
-------------------

As we have seen in the previous section, a provider implements
:class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`. Symfony2
comes with the following providers:

* In-Memory Provider: Mainly intended for development, and unit-tests
* Doctrine Entity Provider: A provider for Doctrine ORM entities
* Chain Provider: A wrapper around several other user providers which are called
                  in sequence until a matching user is found.

In addition, it is very easy to plug-in any custom user provider implementation.

.. index::
   single: Security; In-memory user provider

In-memory Provider
~~~~~~~~~~~~~~~~~~

The in-memory provider is a great provider to secure a personal website backend
or a prototype. It is also the best provider when writing unit tests:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            providers:
                main:
                    users:
                        foo: { password: foo, roles: ROLE_USER }
                        bar: { password: bar, roles: [ROLE_USER, ROLE_ADMIN] }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <provider name="main">
                <user name="foo" password="foo" roles="ROLE_USER" />
                <user name="bar" password="bar" roles="ROLE_USER,ROLE_ADMIN" />
            </provider>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'providers' => array(
                'main' => array('users' => array(
                    'foo' => array('password' => 'foo', 'roles' => 'ROLE_USER'),
                    'bar' => array('password' => 'bar', 'roles' => array('ROLE_USER', 'ROLE_ADMIN')),
                )),
            ),
        ));

.. index::
   single: Security; Doctrine Entity Provider
   single: Doctrine; Doctrine Entity User Provider

Doctrine Entity Provider
~~~~~~~~~~~~~~~~~~~~~~~~

Most of the time, users are described by a Doctrine Entity::

    /**
     * @orm:Entity
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
                    entity: { class: SecurityBundle:User, property: username }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <provider name="main">
                <entity class="SecurityBundle:User" property="username" />
            </provider>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'providers' => array(
                'main' => array(
                    'entity' => array('class' => 'SecurityBundle:User', 'property' => 'username'),
                ),
            ),
        ));

The ``entity`` entry configures the Entity class to use for the user, and
``property`` the PHP column name where the username is stored.

If retrieving the user is more complex than a simple ``findOneBy()`` call,
remove the ``property`` setting and make your Entity Repository class
implement :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`::

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
    :class:`Symfony\\Component\\Security\\Core\\User\\AdvancedAccountInterface`
    interface, don't check the various flags (locked, expired, enabled, ...)
    when retrieving the user from the database as this will be managed by the
    authentication system automatically (and proper exceptions will be thrown
    if needed). If you have special flags, override the default
    :class:`Symfony\\Component\\Security\\Core\\User\\AccountCheckerInterface`
    implementation.


.. index::
   single: Security; Chain Provider
   
Chain Provider
~~~~~~~~~~~~~~

The chain user provider is typically used as default provider to allow fallback
between several other user providers.

.. configuration-block::

    .. code-block:: yaml
    
        # app/config/security.yml
        security:
            providers:
                default_provider:
                    providers: [in_memory, dao_provider]
                in_memory:
                    users:
                        foo: { password: test }
                dao_provider:
                    entity: { class: MyBundle:User, property: username }
                    
    .. code-block:: xml
    
        <!-- app/config/security.xml -->
        <config>
            <provider name="default_provider" providers="in_memory, dao_provider" />
            <provider name="in_memory">
                <user name="foo" password="test" />
            </provider>
            <provider name="dao_provider">
                <entity class="MyBundle:User" property="username" />
            </provider>
        </config>
        
    .. code-block:: php
    
        // app/config/security.php
        $container->loadFromExtension('security', array(
            'providers' => array(
                'default_provider' => array(
                    'providers' => array('in_memory', 'dao_provider')
                ),
                'in_memory' => array(
                    'users' => array(
                        'foo' => array('password' => 'test')
                    )
                ),
                'dao_provider' => array(
                    'entity' => array(
                        'class' => 'MyBundle:User',
                        'property' => 'username',
                    )
                )
            )
        ));

.. index::
   single: Security; Custom User Provider
   single: Doctrine; Doctrine Document User Provider

Custom User Provider
~~~~~~~~~~~~~~~~~~~~

Lastly, you can always set-up a custom user provider when none of the built-in
user providers suits your needs. In this example, we will set-up a user provider
using Doctrine Mongo DB.

We assume that you have the DoctrineMongoDBBundle already installed. This bundle
ships with a user provider similar to the built-in entity provider, but for 
documents.

First, we need to wire the user provider service with the Dependency Injection
container, and second, we need to define this custom user provider in the 
security configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        services:
            my.mongodb.provider:
                parent: doctrine.odm.mongodb.security.user.provider
                arguments: [MyBundle:User, username]
        
        security:
            providers:
                custom_provider:
                    id: my.mongodb.provider
        
    .. code-block:: xml

        <!-- app/config/security.xml -->
        <services>
            <service id="my.mongodb.provider" parent="doctrine.odm.mongodb.security.user.provider">
                <argument>MyBundle:User</argument>
                <argument>username</argument>
            </service>
        </services>
        
        <security:config>
            <provider name="custom_provider" id="my.mongodb.provider" />
        </security:config>

    .. code-block:: php

        // app/config/security.php
        $container
            ->setDefinition('my.mongodb.provider', new DefinitionDecorator('doctrine.odm.mongodb.security.user.provider'))
            ->addArgument('MyBundle:User')
            ->addArgument('username')
        ;
        
        $container->loadFromExtension('security', array(
            'providers' => array(
                'custom_provider' => array(
                    'id' => 'my.mongodb.provider'
                )
            )
        ));

The first argument configures the Document class to use for the user, and the
second argument defines the PHP column name where the username is stored.

If retrieving the user is more complex than a simple ``findOneBy()`` call,
remove the second argument and make your Document Repository class
implement :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`::

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
    :class:`Symfony\\Component\\Security\\Core\\User\\AdvancedAccountInterface`
    interface, don't check the various flags (locked, expired, enabled, ...)
    when retrieving the user from the database as this will be managed by the
    authentication system automatically (and proper exceptions will be thrown
    if needed). If you have special flags, override the default
    :class:`Symfony\\Component\\Security\\Core\\User\\AccountCheckerInterface`
    implementation.

Retrieving the User
-------------------

After authentication, the user can be accessed via the security context::

    $user = $container->get('security.context')->getToken()->getUser();

.. index::
   single: Security; Roles

Roles
-----

A User can have as many roles as needed. Roles are usually defined as strings,
but they can be any object implementing
:class:`Symfony\\Component\\Security\\Core\\Role\\RoleInterface` (roles are always
objects internally). Roles defined as strings should begin with the ``ROLE_``
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
