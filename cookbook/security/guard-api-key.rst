.. index::
    single: Security; Custom Request Authenticator

How to Authenticate Users with an API Key using Guard
=====================================================

Nowadays, it's quite usual to authenticate the user via an API key (when developing
a web service for instance). The API key is provided for every request and is
passed as a query string parameter or via an HTTP header.

Setting up the authenticator is just 3 steps:

* :ref:`cookbook-guard-api-authenticator`
* :ref:`cookbook-guard-api-configuration`
* :ref:`cookbook-guard-api-user-provider`

.. _cookbook-guard-api-authenticator:

A) Create the Guard Authenticator
---------------------------------

Suppose you want to read an ``X-API-TOKEN`` header on each request and use
that to authenticate the user. To do this, create a class that extends
:class:`Symfony\\Component\\Security\\Guard\\AbstractGuardAuthenticator`
(or which implements :class:`Symfony\\Component\\Security\\Guard\\GuardAuthenticatorInterface`)::

    // src/AppBundle/Security/TokenAuthenticator.php
    namespace AppBundle\Security;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
    use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\User\UserProviderInterface;

    class TokenAuthenticator extends AbstractGuardAuthenticator
    {
        public function getCredentialsFromRequest(Request $request)
        {
            $token = $request->headers->get('X-AUTH-TOKEN');

            // no token? Don't do anything :D
            if (!$token) {
                return;
            }

            return [
                'token' => $token,
            ];
        }

        public function authenticate($credentials, UserProviderInterface $userProvider)
        {
            $token = $credentials['token'];

            // call a method on your UserProvider - see below for details
            $user = $userProvider->loadUserByToken($token);

            if (!$user) {
                throw new AuthenticationCredentialsNotFoundException();
            }

            return $user;
        }

        public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
        {
            // on success, just let the request keep going!
            return null;
        }

        public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
        {
            $data = array(
                // you might translate this message
                'message' => $exception->getMessageKey()
            );

            return new Response(json_encode($data), 403);
        }

        /**
         * Called when authentication is needed, but it's not sent
         */
        public function start(Request $request, AuthenticationException $authException = null)
        {
            $data = array(
                // you might translate this message
                'message' => 'Authentication Required'
            );

            return new Response(json_encode($data), 401);
        }

        public function supportsRememberMe()
        {
            return false;
        }
    }

Nice work! You're not done yet, but let's look at each piece:

**getCredentialsFromRequest()**
    The guard system calls this method on *every* request and your job is
    to read the token from the request and return it (it'll be passed to
    ``authenticate()``). If you return ``null``, the rest of the authentication
    process is skipped.

**authenticate**
    If you returned something from ``getCredentialsFromRequest()``, that
    value is passed here as ``$credentials``. This is the *core* of the authentication
    process. Your job is to use ``$credentials`` to return a ``UserInterface``
    object *or* throw an :class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`
    (e.g. if the token does not exist). *How* you do this is up to you and
    you'll learn more in :ref:`user provider <cookbook-guard-api-user-provider>`
    section.

**onAuthenticationSuccess**
    This is called after successful authentication and your job is to either
    return a :class:`Symfony\\Component\\HttpFoundation\\Response` object
    that will be sent to the client or ``null`` to continue the request
    (e.g. allow the route/controller to be called like normal). Since this
    is an API where each request authenticates itself, you want to return
    ``nul``.

**onAuthenticationFailure**
    This is called if authentication fails (i.e. if you throw an
    :class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`)
    inside ``authenticate()`` or ``getCredentialsFromRequest()``. Your job
    is to return the :class:`Symfony\\Component\\HttpFoundation\\Response`
    object that should be sent to the client.

**start**
    This is called if the client accesses a URI/resource that requires authentication,
    but no authentication details were sent (i.e. you returned ``null`` from
    ``getCredentialsFromRequest()``). Your job is to return a
    :class:`Symfony\\Component\\HttpFoundation\\Response` object that helps
    the user authenticate (e.g. a 401 response that says "token is missing!").

**supportsRememberMe**
    Since this is a stateless API, you do not want to support "remember me"
    functionality.

.. _cookbook-guard-api-configuration:

B) Configure the Service and security.yml
-----------------------------------------

To use your configurator, you'll need to register it as a service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.token_authenticator:
                class: AppBundle\Security\TokenAuthenticator
                arguments: []

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <!-- ... -->

                <service id="app.token_authenticator"
                    class="AppBundle\Security\TokenAuthenticator" />
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php

        // ...
        $container
            ->register('app.token_authenticator', 'AppBundle\Security\TokenAuthenticator');

Now you can configure your firewall to use the ``guard`` authentication system
and your new ``app.token_authenticator`` authenticator:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                secured_area:
                    pattern: ^/
                    # set to false if you *do* want to store users in the session
                    stateless: true
                    anonymous: true
                    guard:
                        authenticators:
                            - app.token_authenticator

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

                <firewall name="secured_area"
                    pattern="^/"
                    stateless="true"
                    anonymous="true"
                >
                    <guard>
                        <authenticator>apikey_authenticator</authenticator>
                    </guard>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ..

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area'       => array(
                    'pattern'        => '^/',
                    'stateless'      => true,
                    'anonymous'      => true,
                    'simple_preauth' => array(
                        'authenticators'  => array(
                            'app.token_authenticator'
                        ),
                    ),
                ),
            ),
        ));

Perfect! Now that the security system knows about your authenticator, the
last step is to configure your "user provider" and make it work nicely with
the authenticator.

C) Adding the User Provider
---------------------------

Even though you're authenticating with an API token, the end result is that
a "User" object is set on the security system. Returning this user is the goal
of your configurator's ``authenticate()`` method.

To help out, you'll need to configure a :ref:`user provider <security-user-providers>`.
A few core providers exist (including one that :ref:`loads users from Doctrine <book-security-user-entity>`),
but creating one that does exactly what you want is easy.

Suppose you're using Doctrine and have a ``User`` entity that has a ``token``
property that can be used to authenticate with your API (the ``User`` entity
class isn't shown here). To load users from that entity, create a class that
implements :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`::

    namespace AppBundle\Security;

    use AppBundle\Repository\UserRepository;
    use Doctrine\ORM\EntityManager;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;

    class UserProvider implements UserProviderInterface
    {
        private $em;

        public function __construct(EntityManager $em)
        {
            $this->em = $em;
        }

        // UserProviderInterface
        public function loadUserByUsername($username)
        {
            $user = $this->getUserRepository()->findOneBy(array('username' => $username));

            if (null === $user) {
                throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
            }

            return $user;
        }

        // UserProviderInterface
        public function refreshUser(UserInterface $user)
        {
            $user = $this->getUserRepository()->find($user->getId());
            if (!$user) {
                throw new UsernameNotFoundException(sprintf('User with id "%s" not found!', $user->getId()));
            }

            return $user;
        }

        // UserProviderInterface
        public function supportsClass($class)
        {
            return $class === get_class($this) || is_subclass_of($class, get_class($this));
        }

        // our own custom method
        public function loadUserByToken($token)
        {
            return $this->getUserRepository()->findOneBy(array(
                'token' => $token
            ));
        }

        /**
         * @return UserRepository
         */
        private function getUserRepository()
        {
            return $this->em->getRepository('AppBundle:User');
        }
    }

Most of these methods are part of ``UserInterface`` and are used internally.
But ``loadUserByToken()`` is a custom method that you'll use in a moment.

Register your brand-new "user provider" as a service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.user_provider:
                class: AppBundle\Security\UserProvider
                arguments: ['@doctrine.orm.entity_manager']


    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <!-- ... -->

                <service id="app.user_provider"
                    class="AppBundle\Security\UserProvider">
                    <argument type="service">doctrine.orm.entity_manager</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php

        // ...
        $container
            ->register('app.user_provider', 'AppBundle\Security\UserProvider')
            ->setArguments(array(
                new Reference('doctrine.orm.entity_manager')
            ));

And finally plug this into your security system and tell your firewall that
this is your provider:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            providers:
                # this key could be anything, but it's referenced below
                main_provider:
                    id: app.user_provider

            firewalls:
                secured_area:
                    # all the existing stuff ...
                    provider: main_provider

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

                <firewall name="secured_area"
                    provider="main_provider"
                >
                    <!-- ... -->
                </firewall>

                <provider name="main_provider" id="app.user_provider" />
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ..

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area'       => array(
                    // ...
                    'provider' => 'main_provider'
                ),
            ),
            'providers' => array(
                'main_provider'  => array(
                    'id' => 'app.user_provider',
                ),
            ),
        ));

Great work! Because of this, when ``authenticate()`` is called on your ``TokenAuthenticator``,
the ``$userProvider`` argument will be *your* ``UserProvider`` class. This
means you can add whatever methods to ``UserProvider`` that you want, and
then use them inside ``authenticate()``.

As a reminder, the ``TokenAuthenticator::authenticate()`` looks like this::

    // src/AppBundle/Security/TokenAuthenticator.php
    // ...

    public function authenticate($credentials, UserProviderInterface $userProvider)
    {
        $token = $credentials['token'];

        // call a method on your UserProvider - see below for details
        $user = $userProvider->loadUserByToken($token);

        if (!$user) {
            throw new AuthenticationCredentialsNotFoundException();
        }

        return $user;
    }

To query for a ``User`` object whose ``token`` property matches the ``$token``,
you can use the ``UserProvider::loadUserByToken()`` that was added a moment
ago. It makes that query and reutrns the ``User`` object.

This is just *one* example: you could add whatever methods to ``UserProvider``
that you want or use whatever logic you want to return the ``User`` object.

Doing more with Guard Auth
--------------------------

Now that you know how to authenticate with a token, see what else you can
do:

Doing more with Guard Auth
--------------------------

Now that you know how to authenticate with a token, see what else you can
do:

* :doc:`Creating a Login Form </cookbook/security/guard-login-form>`
* :doc:`Using Multiple Authenticators (Login form *and* API Token) </cookbook/security/guard-multi>`

