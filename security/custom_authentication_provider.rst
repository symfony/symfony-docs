.. index::
   single: Security; Custom authentication provider

How to Create a custom Authentication Provider
==============================================

.. tip::

    Creating a custom authentication system is hard, and this entry will walk
    you through that process. But depending on your needs, you may be able
    to solve your problem in a simpler manner, or via a community bundle:

    * :doc:`/security/guard_authentication`
    * :doc:`/security/custom_password_authenticator`
    * :doc:`/security/api_key_authentication`
    * To authenticate via OAuth using a third-party service such as Google, Facebook
      or Twitter, try using the `HWIOAuthBundle`_ community bundle.

If you have read the article on :doc:`/security`, you understand the
distinction Symfony makes between authentication and authorization in the
implementation of security. This article discusses the core classes involved
in the authentication process, and how to implement a custom authentication
provider. Because authentication and authorization are separate concepts,
this extension will be user-provider agnostic, and will function with your
application's user providers, may they be based in memory, a database, or
wherever else you choose to store them.

Meet WSSE
---------

The following article demonstrates how to create a custom authentication
provider for WSSE authentication. The security protocol for WSSE provides
several security benefits:

#. Username / Password encryption
#. Safe guarding against replay attacks
#. No web server configuration required

WSSE is very useful for the securing of web services, may they be SOAP or
REST.

There is plenty of great documentation on `WSSE`_, but this article will
focus not on the security protocol, but rather the manner in which a custom
protocol can be added to your Symfony application. The basis of WSSE is
that a request header is checked for encrypted credentials, verified using
a timestamp and `nonce`_, and authenticated for the requested user using a
password digest.

.. note::

    WSSE also supports application key validation, which is useful for web
    services, but is outside the scope of this article.

The Token
---------

The role of the token in the Symfony security context is an important one.
A token represents the user authentication data present in the request. Once
a request is authenticated, the token retains the user's data, and delivers
this data across the security context. First, you'll create your token class.
This will allow the passing of all relevant information to your authentication
provider::

    // src/Security/Authentication/Token/WsseUserToken.php
    namespace App\Security\Authentication\Token;

    use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

    class WsseUserToken extends AbstractToken
    {
        public $created;
        public $digest;
        public $nonce;

        public function __construct(array $roles = array())
        {
            parent::__construct($roles);

            // If the user has roles, consider it authenticated
            $this->setAuthenticated(count($roles) > 0);
        }

        public function getCredentials()
        {
            return '';
        }
    }

.. note::

    The ``WsseUserToken`` class extends the Security component's
    :class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\AbstractToken`
    class, which provides basic token functionality. Implement the
    :class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface`
    on any class to use as a token.

The Listener
------------

Next, you need a listener to listen on the firewall. The listener
is responsible for fielding requests to the firewall and calling the authentication
provider. A listener must be an instance of
:class:`Symfony\\Component\\Security\\Http\\Firewall\\ListenerInterface`.
A security listener should handle the
:class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent` event, and
set an authenticated token in the token storage if successful::

    // src/Security/Firewall/WsseListener.php
    namespace App\Security\Firewall;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
    use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Http\Firewall\ListenerInterface;
    use App\Security\Authentication\Token\WsseUserToken;

    class WsseListener implements ListenerInterface
    {
        protected $tokenStorage;
        protected $authenticationManager;

        public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager)
        {
            $this->tokenStorage = $tokenStorage;
            $this->authenticationManager = $authenticationManager;
        }

        public function handle(GetResponseEvent $event)
        {
            $request = $event->getRequest();

            $wsseRegex = '/UsernameToken Username="([^"]+)", PasswordDigest="([^"]+)", Nonce="([a-zA-Z0-9+\/]+={0,2})", Created="([^"]+)"/';
            if (!$request->headers->has('x-wsse') || 1 !== preg_match($wsseRegex, $request->headers->get('x-wsse'), $matches)) {
                return;
            }

            $token = new WsseUserToken();
            $token->setUser($matches[1]);

            $token->digest  = $matches[2];
            $token->nonce   = $matches[3];
            $token->created = $matches[4];

            try {
                $authToken = $this->authenticationManager->authenticate($token);
                $this->tokenStorage->setToken($authToken);

                return;
            } catch (AuthenticationException $failed) {
                // ... you might log something here

                // To deny the authentication clear the token. This will redirect to the login page.
                // Make sure to only clear your token, not those of other authentication listeners.
                // $token = $this->tokenStorage->getToken();
                // if ($token instanceof WsseUserToken && $this->providerKey === $token->getProviderKey()) {
                //     $this->tokenStorage->setToken(null);
                // }
                // return;
            }

            // By default deny authorization
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $event->setResponse($response);
        }
    }

This listener checks the request for the expected ``X-WSSE`` header, matches
the value returned for the expected WSSE information, creates a token using
that information, and passes the token on to the authentication manager. If
the proper information is not provided, or the authentication manager throws
an :class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`,
a 403 Response is returned.

.. note::

    A class not used above, the
    :class:`Symfony\\Component\\Security\\Http\\Firewall\\AbstractAuthenticationListener`
    class, is a very useful base class which provides commonly needed functionality
    for security extensions. This includes maintaining the token in the session,
    providing success / failure handlers, login form URLs, and more. As WSSE
    does not require maintaining authentication sessions or login forms, it
    won't be used for this example.

.. note::

    Returning prematurely from the listener is relevant only if you want to chain
    authentication providers (for example to allow anonymous users). If you want
    to forbid access to anonymous users and have a nice 403 error, you should set
    the status code of the response before returning.

The Authentication Provider
---------------------------

The authentication provider will do the verification of the ``WsseUserToken``.
Namely, the provider will verify the ``Created`` header value is valid within
five minutes, the ``Nonce`` header value is unique within five minutes, and
the ``PasswordDigest`` header value matches with the user's password::

    // src/Security/Authentication/Provider/WsseProvider.php
    namespace App\Security\Authentication\Provider;

    use Psr\Cache\CacheItemPoolInterface;
    use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\Exception\NonceExpiredException;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use App\Security\Authentication\Token\WsseUserToken;

    class WsseProvider implements AuthenticationProviderInterface
    {
        private $userProvider;
        private $cachePool;

        public function __construct(UserProviderInterface $userProvider, CacheItemPoolInterface $cachePool)
        {
            $this->userProvider = $userProvider;
            $this->cachePool = $cachePool;
        }

        public function authenticate(TokenInterface $token)
        {
            $user = $this->userProvider->loadUserByUsername($token->getUsername());

            if ($user && $this->validateDigest($token->digest, $token->nonce, $token->created, $user->getPassword())) {
                $authenticatedToken = new WsseUserToken($user->getRoles());
                $authenticatedToken->setUser($user);

                return $authenticatedToken;
            }

            throw new AuthenticationException('The WSSE authentication failed.');
        }

        /**
         * This function is specific to Wsse authentication and is only used to help this example
         *
         * For more information specific to the logic here, see
         * https://github.com/symfony/symfony-docs/pull/3134#issuecomment-27699129
         */
        protected function validateDigest($digest, $nonce, $created, $secret)
        {
            // Check created time is not in the future
            if (strtotime($created) > time()) {
                return false;
            }

            // Expire timestamp after 5 minutes
            if (time() - strtotime($created) > 300) {
                return false;
            }

            // Try to fetch the cache item from pool
            $cacheItem = $this->cachePool->getItem(md5($nonce));

            // Validate that the nonce is *not* in cache
            // if it is, this could be a replay attack
            if ($cacheItem->isHit()) {
                throw new NonceExpiredException('Previously used nonce detected');
            }

            // Store the item in cache for 5 minutes
            $cacheItem->set(null)->expiresAfter(300);
            $this->cachePool->save($cacheItem);

            // Validate Secret
            $expected = base64_encode(sha1(base64_decode($nonce).$created.$secret, true));

            return hash_equals($expected, $digest);
        }

        public function supports(TokenInterface $token)
        {
            return $token instanceof WsseUserToken;
        }
    }

.. note::

    The :class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface`
    requires an ``authenticate()`` method on the user token, and a ``supports()``
    method, which tells the authentication manager whether or not to use this
    provider for the given token. In the case of multiple providers, the
    authentication manager will then move to the next provider in the list.

.. note::

    While the :phpfunction:`hash_equals` function was introduced in PHP 5.6,
    you are safe to use it with any PHP version in your Symfony application. In
    PHP versions prior to 5.6, `Symfony Polyfill`_ (which is included in
    Symfony) will define the function for you.

The Factory
-----------

You have created a custom token, custom listener, and custom provider. Now
you need to tie them all together. How do you make a unique provider available
for every firewall? The answer is by using a *factory*. A factory
is where you hook into the Security component, telling it the name of your
provider and any configuration options available for it. First, you must
create a class which implements
:class:`Symfony\\Bundle\\SecurityBundle\\DependencyInjection\\Security\\Factory\\SecurityFactoryInterface`.

.. code-block:: php

    // src/DependencyInjection/Security/Factory/WsseFactory.php
    namespace App\DependencyInjection\Security\Factory;

    use Symfony\Component\DependencyInjection\ChildDefinition;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;
    use Symfony\Component\Config\Definition\Builder\NodeDefinition;
    use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
    use App\Security\Authentication\Provider\WsseProvider;
    use App\Security\Firewall\WsseListener;

    class WsseFactory implements SecurityFactoryInterface
    {
        public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
        {
            $providerId = 'security.authentication.provider.wsse.'.$id;
            $container
                ->setDefinition($providerId, new ChildDefinition(WsseProvider::class))
                ->replaceArgument(0, new Reference($userProvider))
            ;

            $listenerId = 'security.authentication.listener.wsse.'.$id;
            $listener = $container->setDefinition($listenerId, new ChildDefinition(WsseListener::class));

            return array($providerId, $listenerId, $defaultEntryPoint);
        }

        public function getPosition()
        {
            return 'pre_auth';
        }

        public function getKey()
        {
            return 'wsse';
        }

        public function addConfiguration(NodeDefinition $node)
        {
        }
    }

The :class:`Symfony\\Bundle\\SecurityBundle\\DependencyInjection\\Security\\Factory\\SecurityFactoryInterface`
requires the following methods:

``create()``
    Method which adds the listener and authentication provider
    to the DI container for the appropriate security context.

``getPosition()``
    Returns when the provider should be called. This can be one of ``pre_auth``,
    ``form``, ``http`` or ``remember_me``.

``getKey()``
    Method which defines the configuration key used to reference
    the provider in the firewall configuration.

``addConfiguration()``
    Method which is used to define the configuration
    options underneath the configuration key in your security configuration.
    Setting configuration options are explained later in this article.

.. note::

    A class not used in this example,
    :class:`Symfony\\Bundle\\SecurityBundle\\DependencyInjection\\Security\\Factory\\AbstractFactory`,
    is a very useful base class which provides commonly needed functionality
    for security factories. It may be useful when defining an authentication
    provider of a different type.

Now that you have created a factory class, the ``wsse`` key can be used as
a firewall in your security configuration.

.. note::

    You may be wondering "why do you need a special factory class to add listeners
    and providers to the dependency injection container?". This is a very
    good question. The reason is you can use your firewall multiple times,
    to secure multiple parts of your application. Because of this, each
    time your firewall is used, a new service is created in the DI container.
    The factory is what creates these new services.

Configuration
-------------

It's time to see your authentication provider in action. You will need to
do a few things in order to make this work. The first thing is to add the
services above to the DI container. Your factory class above makes reference
to service ids that may not exist yet: ``App\Security\Authentication\Provider\WsseProvider`` and
``App\Security\Firewall\WsseListener``. It's time to define those services.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Security\Authentication\Provider\WsseProvider:
                arguments:
                    $cachePool: '@cache.app'
                public: false

            App\Security\Firewall\WsseListener:
                arguments: ['@security.token_storage', '@security.authentication.manager']
                public: false

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Security\Authentication\Provider\WsseProvider"
                    public="false"
                >
                    <argument key="$cachePool" type="service" id="cache.app"></argument>
                </service>

                <service id="App\Security\Firewall\WsseListener"
                    public="false"
                >
                    <argument type="service" id="security.token_storage"/>
                    <argument type="service" id="security.authentication.manager" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Security\Authentication\Provider\WsseProvider;
        use App\Security\Firewall\WsseListener;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(WsseProvider::class)
            ->setArgument('$cachePool', new Reference('cache.app'))
            ->setPublic(false);

        $container->register(WsseListener::class)
            ->setArguments(array(
                new Reference('security.token_storage'),
                new Reference('security.authentication.manager'),
            ))
            ->setPublic(false);

Now that your services are defined, tell your security context about your
factory in the kernel::

    // src/Kernel.php
    namespace App;

    use App\DependencyInjection\Security\Factory\WsseFactory;
    // ...

    class Kernel extends BaseKernel
    {
        public function build(ContainerBuilder $container)
        {
            $extension = $container->getExtension('security');
            $extension->addSecurityListenerFactory(new WsseFactory());
        }

        // ...
    }

You are finished! You can now define parts of your app as under WSSE protection.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                wsse_secured:
                    pattern:   ^/api/
                    stateless: true
                    wsse:      true

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall
                    name="wsse_secured"
                    pattern="^/api/"
                    stateless="true"
                    wsse="true"
                />
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'wsse_secured' => array(
                    'pattern'   => '^/api/',
                    'stateless' => true,
                    'wsse'      => true,
                ),
            ),
        ));

Congratulations! You have written your very own custom security authentication
provider!

A little Extra
--------------

How about making your WSSE authentication provider a bit more exciting? The
possibilities are endless. Why don't you start by adding some sparkle
to that shine?

Configuration
~~~~~~~~~~~~~

You can add custom options under the ``wsse`` key in your security configuration.
For instance, the time allowed before expiring the ``Created`` header item,
by default, is 5 minutes. Make this configurable, so different firewalls
can have different timeout lengths.

You will first need to edit ``WsseFactory`` and define the new option in
the ``addConfiguration()`` method.

.. code-block:: php

    class WsseFactory implements SecurityFactoryInterface
    {
        // ...

        public function addConfiguration(NodeDefinition $node)
        {
          $node
            ->children()
                ->scalarNode('lifetime')->defaultValue(300)
            ->end();
        }
    }

Now, in the ``create()`` method of the factory, the ``$config`` argument will
contain a ``lifetime`` key, set to 5 minutes (300 seconds) unless otherwise
set in the configuration. Pass this argument to your authentication provider
in order to put it to use.

.. code-block:: php

    use App\Security\Authentication\Provider\WsseProvider;

    class WsseFactory implements SecurityFactoryInterface
    {
        public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
        {
            $providerId = 'security.authentication.provider.wsse.'.$id;
            $container
                ->setDefinition($providerId, new ChildDefinition(WsseProvider::class))
                ->replaceArgument(0, new Reference($userProvider))
                ->replaceArgument(2, $config['lifetime']);
            // ...
        }

        // ...
    }

.. note::

    The ``WsseProvider`` class will also now need to accept a third constructor argument -
    the lifetime - which it should use instead of the hard-coded 300 seconds. This
    step is not shown here.

The lifetime of each WSSE request is now configurable, and can be
set to any desirable value per firewall.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                wsse_secured:
                    pattern:   ^/api/
                    stateless: true
                    wsse:      { lifetime: 30 }

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="wsse_secured" pattern="^/api/" stateless="true">
                    <wsse lifetime="30" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'wsse_secured' => array(
                    'pattern'   => '^/api/',
                    'stateless' => true,
                    'wsse'      => array(
                        'lifetime' => 30,
                    ),
                ),
            ),
        ));

The rest is up to you! Any relevant configuration items can be defined
in the factory and consumed or passed to the other classes in the container.

.. _`HWIOAuthBundle`: https://github.com/hwi/HWIOAuthBundle
.. _`WSSE`: http://www.xml.com/pub/a/2003/12/17/dive.html
.. _`nonce`: https://en.wikipedia.org/wiki/Cryptographic_nonce
.. _`timing attacks`: https://en.wikipedia.org/wiki/Timing_attack
.. _`Symfony Polyfill`: https://github.com/symfony/polyfill
