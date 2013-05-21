.. index::
   single: Security; User Provider

How to create a custom User Provider
====================================

Part of Symfony's standard authentication process depends on "user providers".
When a user submits a username and password, the authentication layer asks
the configured user provider to return a user object for a given username.
Symfony then checks whether the password of this user is correct and generates
a security token so the user stays authenticated during the current session.
Out of the box, Symfony has an "in_memory" and an "entity" user provider.
In this entry you'll see how you can create your own user provider, which
could be useful if your users are accessed via a custom database, a file,
or - as shown in this example - a web service.

Create a User Class
-------------------

First, regardless of *where* your user data is coming from, you'll need to
create a ``User`` class that represents that data. The ``User`` can look
however you want and contain any data. The only requirement is that the
class implements :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`.
The methods in this interface should therefore be defined in the custom user
class: :method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getRoles`,
:method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getPassword`,
:method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getSalt`,
:method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::getUsername`,
:method:`Symfony\\Component\\Security\\Core\\User\\UserInterface::eraseCredentials`.
It may also be useful to implement the
:class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface` interface,
which defines a method to check if the user is equal to the current user. This
interface requires an :method:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface::isEqualTo`
method.

Let's see this in action::

    // src/Acme/WebserviceUserBundle/Security/User/WebserviceUser.php
    namespace Acme\WebserviceUserBundle\Security\User;

    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\EquatableInterface;

    class WebserviceUser implements UserInterface, EquatableInterface
    {
        private $username;
        private $password;
        private $salt;
        private $roles;

        public function __construct($username, $password, $salt, array $roles)
        {
            $this->username = $username;
            $this->password = $password;
            $this->salt = $salt;
            $this->roles = $roles;
        }

        public function getRoles()
        {
            return $this->roles;
        }

        public function getPassword()
        {
            return $this->password;
        }

        public function getSalt()
        {
            return $this->salt;
        }

        public function getUsername()
        {
            return $this->username;
        }

        public function eraseCredentials()
        {
        }

        public function isEqualTo(UserInterface $user)
        {
            if (!$user instanceof WebserviceUser) {
                return false;
            }

            if ($this->password !== $user->getPassword()) {
                return false;
            }

            if ($this->getSalt() !== $user->getSalt()) {
                return false;
            }

            if ($this->username !== $user->getUsername()) {
                return false;
            }

            return true;
        }
    }

If you have more information about your users - like a "first name" - then
you can add a ``firstName`` field to hold that data.

Create a User Provider
----------------------

Now that you have a ``User`` class, you'll create a user provider, which will
grab user information from some web service, create a ``WebserviceUser`` object,
and populate it with data.

The user provider is just a plain PHP class that has to implement the
:class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`,
which requires three methods to be defined: ``loadUserByUsername($username)``,
``refreshUser(UserInterface $user)``, and ``supportsClass($class)``. For
more details, see :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`.

Here's an example of how this might look::

    // src/Acme/WebserviceUserBundle/Security/User/WebserviceUserProvider.php
    namespace Acme\WebserviceUserBundle\Security\User;

    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

    class WebserviceUserProvider implements UserProviderInterface
    {
        public function loadUserByUsername($username)
        {
            // make a call to your webservice here
            $userData = ...
            // pretend it returns an array on success, false if there is no user

            if ($userData) {
                $password = '...';

                // ...

                return new WebserviceUser($username, $password, $salt, $roles);
            }

            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        public function refreshUser(UserInterface $user)
        {
            if (!$user instanceof WebserviceUser) {
                throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
            }

            return $this->loadUserByUsername($user->getUsername());
        }

        public function supportsClass($class)
        {
            return $class === 'Acme\WebserviceUserBundle\Security\User\WebserviceUser';
        }
    }

Create a Service for the User Provider
--------------------------------------

Now you make the user provider available as a service:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/WebserviceUserBundle/Resources/config/services.yml
        parameters:
            webservice_user_provider.class: Acme\WebserviceUserBundle\Security\User\WebserviceUserProvider

        services:
            webservice_user_provider:
                class: "%webservice_user_provider.class%"

    .. code-block:: xml

        <!-- src/Acme/WebserviceUserBundle/Resources/config/services.xml -->
        <parameters>
            <parameter key="webservice_user_provider.class">Acme\WebserviceUserBundle\Security\User\WebserviceUserProvider</parameter>
        </parameters>

        <services>
            <service id="webservice_user_provider" class="%webservice_user_provider.class%"></service>
        </services>

    .. code-block:: php

        // src/Acme/WebserviceUserBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('webservice_user_provider.class', 'Acme\WebserviceUserBundle\Security\User\WebserviceUserProvider');

        $container->setDefinition('webservice_user_provider', new Definition('%webservice_user_provider.class%');

.. tip::

    The real implementation of the user provider will probably have some
    dependencies or configuration options or other services. Add these as
    arguments in the service definition.

.. note::

    Make sure the services file is being imported. See :ref:`service-container-imports-directive`
    for details.

Modify ``security.yml``
-----------------------

Everything comes together in your security configuration. Add the user provider
to the list of providers in the "security" section. Choose a name for the user provider
(e.g. "webservice") and mention the id of the service you just defined.

.. configuration-block::

    .. code-block:: yaml

        // app/config/security.yml
        security:
            providers:
                webservice:
                    id: webservice_user_provider

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <provider name="webservice" id="webservice_user_provider" />
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'providers' => array(
                'webservice' => array(
                    'id' => 'webservice_user_provider',
                ),
            ),
        ));

Symfony also needs to know how to encode passwords that are supplied by website
users, e.g. by filling in a login form. You can do this by adding a line to the
"encoders" section in your security configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            encoders:
                Acme\WebserviceUserBundle\Security\User\WebserviceUser: sha512

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <encoder class="Acme\WebserviceUserBundle\Security\User\WebserviceUser">sha512</encoder>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'encoders' => array(
                'Acme\WebserviceUserBundle\Security\User\WebserviceUser' => 'sha512',
            ),
        ));

The value here should correspond with however the passwords were originally
encoded when creating your users (however those users were created). When
a user submits her password, the password is appended to the salt value and
then encoded using this algorithm before being compared to the hashed password
returned by your ``getPassword()`` method. Additionally, depending on your
options, the password may be encoded multiple times and encoded to base64.

.. sidebar:: Specifics on how passwords are encoded

    Symfony uses a specific method to combine the salt and encode the password
    before comparing it to your encoded password. If ``getSalt()`` returns
    nothing, then the submitted password is simply encoded using the algorithm
    you specify in ``security.yml``. If a salt *is* specified, then the following
    value is created and *then* hashed via the algorithm:

        ``$password.'{'.$salt.'}';``

    If your external users have their passwords salted via a different method,
    then you'll need to do a bit more work so that Symfony properly encodes
    the password. That is beyond the scope of this entry, but would include
    sub-classing ``MessageDigestPasswordEncoder`` and overriding the ``mergePasswordAndSalt``
    method.

    Additionally, the hash, by default, is encoded multiple times and encoded
    to base64. For specific details, see `MessageDigestPasswordEncoder`_.
    To prevent this, configure it in your configuration file:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/security.yml
            security:
                encoders:
                    Acme\WebserviceUserBundle\Security\User\WebserviceUser:
                        algorithm: sha512
                        encode_as_base64: false
                        iterations: 1

        .. code-block:: xml

            <!-- app/config/security.xml -->
            <config>
                <encoder class="Acme\WebserviceUserBundle\Security\User\WebserviceUser"
                    algorithm="sha512"
                    encode-as-base64="false"
                    iterations="1"
                />
            </config>

        .. code-block:: php

            // app/config/security.php
            $container->loadFromExtension('security', array(
                'encoders' => array(
                    'Acme\WebserviceUserBundle\Security\User\WebserviceUser' => array(
                        'algorithm'         => 'sha512',
                        'encode_as_base64'  => false,
                        'iterations'        => 1,
                    ),
                ),
            ));

.. _MessageDigestPasswordEncoder: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Security/Core/Encoder/MessageDigestPasswordEncoder.php
