.. index::
   single: Security; User Provider

How to Create a custom User Provider
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

This is how your ``WebserviceUser`` class looks in action::

    // src/AppBundle/Security/User/WebserviceUser.php
    namespace AppBundle\Security\User;

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

            if ($this->salt !== $user->getSalt()) {
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

    // src/AppBundle/Security/User/WebserviceUserProvider.php
    namespace AppBundle\Security\User;

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

            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        public function refreshUser(UserInterface $user)
        {
            if (!$user instanceof WebserviceUser) {
                throw new UnsupportedUserException(
                    sprintf('Instances of "%s" are not supported.', get_class($user))
                );
            }

            return $this->loadUserByUsername($user->getUsername());
        }

        public function supportsClass($class)
        {
            return $class === 'AppBundle\Security\User\WebserviceUser';
        }
    }

Create a Service for the User Provider
--------------------------------------

Now you make the user provider available as a service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.webservice_user_provider:
                class: AppBundle\Security\User\WebserviceUserProvider

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.webservice_user_provider"
                    class="AppBundle\Security\User\WebserviceUserProvider"
                />
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setDefinition(
            'app.webservice_user_provider',
            new Definition('AppBundle\Security\User\WebserviceUserProvider')
        );

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
(e.g. "webservice") and mention the ``id`` of the service you just defined.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            providers:
                webservice:
                    id: app.webservice_user_provider

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <provider name="webservice" id="app.webservice_user_provider" />
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...

            'providers' => array(
                'webservice' => array(
                    'id' => 'app.webservice_user_provider',
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
            # ...

            encoders:
                AppBundle\Security\User\WebserviceUser: bcrypt

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <encoder class="AppBundle\Security\User\WebserviceUser"
                    algorithm="bcrypt" />
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...

            'encoders' => array(
                'AppBundle\Security\User\WebserviceUser' => 'bcrypt',
            ),
            // ...
        ));

The value here should correspond with however the passwords were originally
encoded when creating your users (however those users were created). When
a user submits their password, it's encoded using this algorithm and the result
is compared to the hashed password returned by your ``getPassword()`` method.

.. sidebar:: Specifics on how Passwords are Encoded

    Symfony uses a specific method to combine the salt and encode the password
    before comparing it to your encoded password. If ``getSalt()`` returns
    nothing, then the submitted password is simply encoded using the algorithm
    you specify in ``security.yml``. If a salt *is* specified, then the following
    value is created and *then* hashed via the algorithm::

        $password.'{'.$salt.'}'

    If your external users have their passwords salted via a different method,
    then you'll need to do a bit more work so that Symfony properly encodes
    the password. That is beyond the scope of this entry, but would include
    sub-classing ``MessageDigestPasswordEncoder`` and overriding the
    ``mergePasswordAndSalt`` method.

    Additionally, you can configure the details of the algorithm used to hash
    passwords. In this example, the application sets explicitly the cost of
    the bcrypt hashing:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/security.yml
            security:
                # ...

                encoders:
                    AppBundle\Security\User\WebserviceUser:
                        algorithm: bcrypt
                        cost: 12

        .. code-block:: xml

            <!-- app/config/security.xml -->
            <?xml version="1.0" encoding="UTF-8"?>
            <srv:container xmlns="http://symfony.com/schema/dic/security"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:srv="http://symfony.com/schema/dic/services"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd">

                <config>
                    <!-- ... -->

                    <encoder class="AppBundle\Security\User\WebserviceUser"
                        algorithm="bcrypt"
                        cost="12" />
                </config>
            </srv:container>

        .. code-block:: php

            // app/config/security.php
            $container->loadFromExtension('security', array(
                // ...

                'encoders' => array(
                    'AppBundle\Security\User\WebserviceUser' => array(
                        'algorithm' => 'bcrypt',
                        'cost' => 12,
                    ),
                ),
            ));

.. _MessageDigestPasswordEncoder: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Security/Core/Encoder/MessageDigestPasswordEncoder.php
