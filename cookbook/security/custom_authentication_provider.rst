.. index::
   single: Security; Custom Authentication Provider

How to create a custom Authentication Provider
==============================================

If you have read the chapter on :doc:`/book/security`, you understand the
distinction Symfony2 makes between authentication and authorization in the
implementation of security. This chapter discusses the core classes involved
in the authentication process, and how to implement a custom authentication
provider. Because authentication and authorization are separate concepts,
this extension will be user-provider agnostic, and will function with your
application's user providers, may they be based in memory, a database, or
wherever else you choose to store them.

Meet WSSE
---------

The following chapter demonstrates how to create a custom authentication
provider for WSSE authentication. The security protocol for WSSE provides
several security benefits:

1. Username / Password encryption
2. Safe guarding against replay attacks
3. No web server configuration required

WSSE is very useful for the securing of web services, may they be SOAP or
REST.

There is plenty of great documentation on `WSSE`_, but this article will
focus not on the security protocol, but rather the manner in which a custom
protocol can be added to your Symfony2 application. The basis of WSSE is
that a request header is checked for encrypted credentials, verified using
a timestamp and `nonce`_, and authenticated for the requested user using a
password digest.

.. note::

    WSSE also supports application key validation, which is useful for web
    services, but is outside the scope of this chapter.

The Token
---------

The role of the token in the Symfony2 security context is an important one.
A token represents the user authentication data present in the request. Once
a request is authenticated, the token retains the user's data, and delivers
this data across the security context. First, we will create our token class.
This will allow the passing of all relevant information to our authentication
provider.

.. code-block:: php

    // src/Acme/DemoBundle/Security/Authentication/Token/WsseUserToken.php
    namespace Acme\DemoBundle\Security\Authentication\Token;

    use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

    class WsseUserToken extends AbstractToken
    {
        public $created;
        public $digest;
        public $nonce;

        public function getCredentials()
        {
            return '';
        }
    }

.. note::

    The ``WsseUserToken`` class extends the security component's
    :class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\AbstractToken`
    class, which provides basic token functionality. Implement the
    :class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface`
    on any class to use as a token.

The Listener
------------

Next, you need a listener to listen on the security context. The listener
is responsible for fielding requests to the firewall and calling the authentication
provider. A listener must be an instance of
:class:`Symfony\\Component\\Security\\Http\\Firewall\\ListenerInterface`.
A security listener should handle the
:class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent` event, and
set an authenticated token in the security context if successful.

.. code-block:: php

    // src/Acme/DemoBundle/Security/Firewall/WsseListener.php
    namespace Acme\DemoBundle\Security\Firewall;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\Security\Http\Firewall\ListenerInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\SecurityContextInterface;
    use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Acme\DemoBundle\Security\Authentication\Token\WsseUserToken;

    class WsseListener implements ListenerInterface
    {
        protected $securityContext;
        protected $authenticationManager;

        public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
        {
            $this->securityContext = $securityContext;
            $this->authenticationManager = $authenticationManager;
        }

        public function handle(GetResponseEvent $event)
        {
            $request = $event->getRequest();

            if (!$request->headers->has('x-wsse')) {
                return;
            }

            $wsseRegex = '/UsernameToken Username="([^"]+)", PasswordDigest="([^"]+)", Nonce="([^"]+)", Created="([^"]+)"/';

            if (preg_match($wsseRegex, $request->headers->get('x-wsse'), $matches)) {
                $token = new WsseUserToken();
                $token->setUser($matches[1]);

                $token->digest   = $matches[2];
                $token->nonce    = $matches[3];
                $token->created  = $matches[4];

                try {
                    $returnValue = $this->authenticationManager->authenticate($token);

                    if ($returnValue instanceof TokenInterface) {
                        return $this->securityContext->setToken($returnValue);
                    } else if ($returnValue instanceof Response) {
                        return $event->setResponse($returnValue);
                    }
                } catch (AuthenticationException $e) {
                    // you might log something here
                }
            }

            $response = new Response();
            $response->setStatusCode(403);
            $event->setResponse($response);
        }
    }

This listener checks the request for the expected `X-WSSE` header, matches
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
    providing success / failure handlers, login form urls, and more. As WSSE
    does not require maintaining authentication sessions or login forms, it
    won't be used for this example.

The Authentication Provider
---------------------------

The authentication provider will do the verification of the ``WsseUserToken``.
Namely, the provider will verify the ``Created`` header value is valid within
five minutes, the ``Nonce`` header value is unique within five minutes, and
the ``PasswordDigest`` header value matches with the user's password.

.. code-block:: php

    // src/Acme/DemoBundle/Security/Authentication/Provider/WsseProvider.php
    namespace Acme\DemoBundle\Security\Authentication\Provider;

    use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\Exception\NonceExpiredException;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Acme\DemoBundle\Security\Authentication\Token\WsseUserToken;

    class WsseProvider implements AuthenticationProviderInterface
    {
        private $userProvider;
        private $cacheDir;

        public function __construct(UserProviderInterface $userProvider, $cacheDir)
        {
            $this->userProvider = $userProvider;
            $this->cacheDir     = $cacheDir;
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

        protected function validateDigest($digest, $nonce, $created, $secret)
        {
            // Expire timestamp after 5 minutes
            if (time() - strtotime($created) > 300) {
                return false;
            }

            // Validate nonce is unique within 5 minutes
            if (file_exists($this->cacheDir.'/'.$nonce) && file_get_contents($this->cacheDir.'/'.$nonce) + 300 >= time()) {
                throw new NonceExpiredException('Previously used nonce detected');
            }
            file_put_contents($this->cacheDir.'/'.$nonce, time());

            // Validate Secret
            $expected = base64_encode(sha1(base64_decode($nonce).$created.$secret, true));

            return $digest === $expected;
        }

        public function supports(TokenInterface $token)
        {
            return $token instanceof WsseUserToken;
        }
    }

.. note::

    The :class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface`
    requires an ``authenticate`` method on the user token, and a ``supports``
    method, which tells the authentication manager whether or not to use this
    provider for the given token. In the case of multiple providers, the
    authentication manager will then move to the next provider in the list.

The Factory
-----------

You have created a custom token, custom listener, and custom provider. Now
you need to tie them all together. How do you make your provider available
to your security configuration? The answer is by using a ``factory``. A factory
is where you hook into the security component, telling it the name of your
provider and any configuration options available for it. First, you must
create a class which implements
:class:`Symfony\\Bundle\\SecurityBundle\\DependencyInjection\\Security\\Factory\\SecurityFactoryInterface`.

.. code-block:: php

    // src/Acme/DemoBundle/DependencyInjection/Security/Factory/WsseFactory.php
    namespace Acme\DemoBundle\DependencyInjection\Security\Factory;

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;
    use Symfony\Component\DependencyInjection\DefinitionDecorator;
    use Symfony\Component\Config\Definition\Builder\NodeDefinition;
    use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

    class WsseFactory implements SecurityFactoryInterface
    {
        public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
        {
            $providerId = 'security.authentication.provider.wsse.'.$id;
            $container
                ->setDefinition($providerId, new DefinitionDecorator('wsse.security.authentication.provider'))
                ->replaceArgument(0, new Reference($userProvider))
            ;

            $listenerId = 'security.authentication.listener.wsse.'.$id;
            $listener = $container->setDefinition($listenerId, new DefinitionDecorator('wsse.security.authentication.listener'));

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
        {}
    }

The :class:`Symfony\\Bundle\\SecurityBundle\\DependencyInjection\\Security\\Factory\\SecurityFactoryInterface`
requires the following methods:

* ``create`` method, which adds the listener and authentication provider
  to the DI container for the appropriate security context;

* ``getPosition`` method, which must be of type ``pre_auth``, ``form``, ``http``,
  and ``remember_me`` and defines the position at which the provider is called;

* ``getKey`` method which defines the configuration key used to reference
  the provider;

* ``addConfiguration`` method, which is used to define the configuration
  options underneath the configuration key in your security configuration.
  Setting configuration options are explained later in this chapter.

.. note::

    A class not used in this example,
    :class:`Symfony\\Bundle\\SecurityBundle\\DependencyInjection\\Security\\Factory\\AbstractFactory`,
    is a very useful base class which provides commonly needed functionality
    for security factories. It may be useful when defining an authentication
    provider of a different type.

Now that you have created a factory class, the ``wsse`` key can be used as
a firewall in your security configuration.

.. note::

    You may be wondering "why do we need a special factory class to add listeners
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
to service ids that do not exist yet: ``wsse.security.authentication.provider`` and
``wsse.security.authentication.listener``. It's time to define those services.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/services.yml
        services:
          wsse.security.authentication.provider:
            class:  Acme\DemoBundle\Security\Authentication\Provider\WsseProvider
            arguments: ['', %kernel.cache_dir%/security/nonces]

          wsse.security.authentication.listener:
            class:  Acme\DemoBundle\Security\Firewall\WsseListener
            arguments: [@security.context, @security.authentication.manager]


    .. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/config/services.xml -->
        <services>
            <service id="wsse.security.authentication.provider"
              class="Acme\DemoBundle\Security\Authentication\Provider\WsseProvider" public="false">
                <argument /> <!-- User Provider -->
                <argument>%kernel.cache_dir%/security/nonces</argument>
            </service>

            <service id="wsse.security.authentication.listener"
              class="Acme\DemoBundle\Security\Firewall\WsseListener" public="false">
                <argument type="service" id="security.context"/>
                <argument type="service" id="security.authentication.manager" />
            </service>
        </services>

    .. code-block:: php

        // src/Acme/DemoBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('wsse.security.authentication.provider',
          new Definition(
            'Acme\DemoBundle\Security\Authentication\Provider\WsseProvider',
            array('', '%kernel.cache_dir%/security/nonces')
        ));

        $container->setDefinition('wsse.security.authentication.listener',
          new Definition(
            'Acme\DemoBundle\Security\Firewall\WsseListener', array(
              new Reference('security.context'),
              new Reference('security.authentication.manager'))
        ));

Now that your services are defined, tell your security context about your
factory. Factories must be included in an individual configuration file,
at the time of this writing. You need to create a file with your factory
service in it, and then use the ``factories`` key in your configuration
to import it.

.. code-block:: xml

    <!-- src/Acme/DemoBundle/Resources/config/security_factories.xml -->
    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

        <services>
            <service id="security.authentication.factory.wsse"
              class="Acme\DemoBundle\DependencyInjection\Security\Factory\WsseFactory" public="false">
                <tag name="security.listener.factory" />
            </service>
        </services>
    </container>

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
          factories:
            - "%kernel.root_dir%/../src/Acme/DemoBundle/Resources/config/security_factories.xml"

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <factories>
              "%kernel.root_dir%/../src/Acme/DemoBundle/Resources/config/security_factories.xml
            </factories>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'factories' => array(
              "%kernel.root_dir%/../src/Acme/DemoBundle/Resources/config/security_factories.xml"
            ),
        ));

You are finished! You can now define parts of your app as under WSSE protection.

.. code-block:: yaml

    security:
        firewalls:
            wsse_secured:
                pattern:   /api/.*
                wsse:      true

Congratulations!  You have written your very own custom security authentication
provider!

A Little Extra
--------------

How about making your WSSE authentication provider a bit more exciting? The
possibilities are endless. Why don't you start by adding some spackle
to that shine?

Configuration
~~~~~~~~~~~~~

You can add custom options under the ``wsse`` key in your security configuration.
For instance, the time allowed before expiring the Created header item,
by default, is 5 minutes. Make this configurable, so different firewalls
can have different timeout lengths.

You will first need to edit ``WsseFactory`` and define the new option in
the ``addConfiguration`` method.

.. code-block:: php

    class WsseFactory implements SecurityFactoryInterface
    {
        # ...

        public function addConfiguration(NodeDefinition $node)
        {
          $node
            ->children()
              ->scalarNode('lifetime')->defaultValue(300)
            ->end()
          ;
        }
    }

Now, in the ``create`` method of the factory, the ``$config`` argument will
contain a 'lifetime' key, set to 5 minutes (300 seconds) unless otherwise
set in the configuration. Pass this argument to your authentication provider
in order to put it to use.

.. code-block:: php

    class WsseFactory implements SecurityFactoryInterface
    {
        public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
        {
            $providerId = 'security.authentication.provider.wsse.'.$id;
            $container
                ->setDefinition($providerId,
                  new DefinitionDecorator('wsse.security.authentication.provider'))
                ->replaceArgument(0, new Reference($userProvider))
                ->replaceArgument(2, $config['lifetime'])
            ;
            // ...
        }
        // ...
    }

.. note::

    You'll also need to add a third argument to the ``wsse.security.authentication.provider``
    service configuration, which can be blank, but will be filled in with
    the lifetime in the factory. The ``WsseProvider`` class will also now
    need to accept a third constructor argument - the lifetime - which it
    should use instead of the hard-coded 300 seconds. These two steps are
    not shown here.

The lifetime of each wsse request is now configurable, and can be
set to any desirable value per firewall.

.. code-block:: yaml

    security:
        firewalls:
            wsse_secured:
                pattern:   /api/.*
                wsse:      { lifetime: 30 }

The rest is up to you! Any relevant configuration items can be defined
in the factory and consumed or passed to the other classes in the container.

.. _`WSSE`: http://www.xml.com/pub/a/2003/12/17/dive.html
.. _`nonce`: http://en.wikipedia.org/wiki/Cryptographic_nonce