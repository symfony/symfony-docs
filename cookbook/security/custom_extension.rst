How to create a custom Security Extension
=========================================

If you have read the chapter on :doc:`/book/security`, understand the distinction
Symfony2 makes between authentication and authorization in the implementation
of security. This chapter discusses the core classes involved in the authorization
process, and how to implement a custom authorization extension. Because
authentication and authorization are separate concepts, this extension will
be user-provider agnostic, and will function with your application's user
providers, may they be based in memory, a database, or wherever else you
choose to store them.

Meet WSSE
---------

The following chapter demonstrates how to create a custom authentication
extension for WSSE authentication. The security protocol for WSSE provides
several security benefits:

1. Username / Password encryption
2. Safe guarding against replay attacks
3. No web server configuration required

This makes it primarily useful for the securing of web services, may they be
SOAP, REST, or something else all together. The implementation in this article
will be stateless.

You can read up on `WSSE`_ if you'd like, but the important thing is WSSE checks
the request header for encrypted credentials, retrieves a user from the
user provider, and determines whether or not the credentials provided match
the user requested.

.. note::

    WSSE also supports application key validation, which is
    a useful device for web services, but is outside the scope of this chapter.

The Token
---------

The role of the token in the Symfony2 security context is an important one.
A token represents the user authentication data present in the request. Once
a request is authenticated, the token retains the user's data, and delivers
this data across the security context. Once authenticated, the token is passed
to the context in order to be authorized. First, we will create our token.
This will allow the passing of all relevant information to our authentication
provider.

.. code-block:: php

    namespace Acme\HelloBendle\Security\Authentication\Token;

    use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

    class WsseUserToken extends AbstractToken
    {
        public $username;
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
    ``AbstractToken`` class, which provides basic token functionality.
    Implement the ``TokenInterface`` on any class to use as a token.

The Listener
------------

In order for this token to be used, a listener must be defined to
listen on the security context. The listener is responsible for fielding requests
to the firewall and calling the authentication provider. A listener must
be an instance of ``ListenerInterface``. A security listener should handle
the ``GetResponseEvent`` event, and set an authenticated token in the security
context if successful.

.. code-block:: php

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

        public function __construct(SecurityContextInterface $securityContext,
            AuthenticationManagerInterface $authenticationManager)
        {
            $this->securityContext = $securityContext;
            $this->authenticationManager = $authenticationManager;
        }

        public function handle(GetResponseEvent $event)
        {
            $request = $event->getRequest();

            $wsseRegex = '/UsernameToken Username="(.*)" PasswordDigest="(.*)" Nonce="(.*)" Created="(.*)"/';
            if (preg_match($request->headers->get('x-wsse'), $wsseRegex, $matches)) {
                $token = new WsseUserToken();
                $token->setUser($matches[0]);

                $token->username = $matches[0];
                $token->digest   = $matches[1];
                $token->nonce    = $matches[2];
                $token->created  = $matches[3];

                try {
                    $returnValue = $this->authenticationManager->authenticate($token);

                    if ($returnValue instanceof TokenInterface) {
                        return $this->securityContext->setToken($returnValue);
                    } else if ($returnValue instanceof Response) {
                        return $event->setResponse($response);
                    }
                } catch (AuthenticationException $e) {}
            }

            $response = new Response();
            $response->setStatusCode(403);
            $event->setResponse($response);
        }
    }

.. note::

    The ``AbstractAuthenticationListener`` class is a very useful base
    class which provides commonly needed functionality for authentication
    extensions. This includes maintaining the token in the session, providing
    success / failure handlers, login form urls, and more. As WSSE does
    not require maintaining authentication sessions or login forms, it won't
    be used for this example.

This listener checks the header for the expected WSSE information,
creates a token using that information, and passes the token on to
the authentication manager. If the proper information is not provided,
or the authentication manager throws an ``AuthenticationException``,
a 403 Response is returned.

The Authentication Provider
---------------------------

The authentication provider will do the verification of the parameters passed
in using the ``UsernameToken`` header. Namely, the provider will verify the
``Created`` header value is valid within five minutes, the ``Nonce`` header
value is unique within five minutes, and the ``PasswordDigest`` header value
matches with the user's password.

.. code-block:: php

    namespace Acme\DemoBundle\Security\Authentication\Provider;

    use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
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

            if($user && $this->validateDigest($token->digest, $token->nonce, $token->created, $user->getPassword()))
            {
                $token->setUser($user);
                return $token;
            }

            throw new AuthenticationException('The WSSE authentication failed.');
        }

        protected function validateDigest($digest, $nonce, $created, $secret)
        {
            // Expire timestamp after 5 minutes
            if (time() - strtotime($created) > 300) {
                return false;
            }

            // Validate nonce has not been used in last 5 minutes
            $nonceFile = $this->cacheDir . DIRECTORY_SEPARATOR . 'nonces' . DIRECTORY_SEPARATOR . $nonce;
            if (file_exists($nonceFile) && file_get_contents($nonceFile) + 300 >= time()) {
                return false;
            }
            file_put_contents($nonceFile, time());

            // Validate Secret
            $expected = base64_encode(sha1($nonce.$created.$secret, true));

            return $digest === $expected;
        }

        public function supports(TokenInterface $token)
        {
            return $token instanceof WsseUserToken;
        }
    }

.. note::

    The ``AuthenticationProviderInterface`` requires an ``authenticate``
    method on the user token, and a ``supports`` method, which tells the
    security component whether or not to use this provider for the given
    token. In the case of multiple providers, the security component will
    then move to the next provider in the list.

The Factory
-----------

You have created a custom token, custom listener, and custom provider.
Now you need to tie it all together. Whenever a new provider is added,
you are essentially writing an extension for the security container. You
have created a custom security provider type, with its own set of configuration
options, which a user has access to in their security configuration. In
order for the security context to use this extension, you must create a
class which implements the interface ``SecurityFactoryInterface``. This
class will inform the security context of your new ``wsse`` authentication
provider type, and allow you to use it as a firewall.

.. code-block:: php

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

.. note::

    The ``SecurityFactoryInterface`` requires the following methods:
    A ``create`` method, which adds the listener and authentication
    provider to the DI container for the appropriate security context,
    a ``getPosition`` method, which must be of type 'pre_auth', 'form',
    'http', and 'remember_me' and defines the position at which the provider
    is called, a ``getKey`` method which defines the configuration key
    used to reference the provider, and an ``addConfiguration`` method,
    which is used to define the configuration options underneath the
    configuration key in your security configuration.

.. note::

    A class not used in this example, ``AbstractFactory``, is a very useful
    base class which provides commonly needed functionality for security
    factories. It may be useful when defining an authentication provider
    of a different type.

Now that you have created a factory class, the ``wsse`` key can be used as
a firewall in your security configuration.

.. note::

    You may be wondering "why do we need a special factory class to add listeners
    and providers to the DI container?". This is a very good question.  The reason
    is you can use your firewall multiple times, to secure multiple parts of
    your application. Because of this, each time your firewall is used, a new
    service is created in the DI container. The factory is what creates these
    new services.

Configuration
-------------

It's time to see your authentication provider in action. You will need to
do a few things in order to make this work. The first thing is to add the
services above to the DI container.  The factory class above references the
service ids ``wsse.security.authentication.provider`` and
``wsse.security.authentication.listener``. It's time to define those services.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
          wsse.security.authentication.provider:
            class:  Acme\DemoBundle\Security\Authentication\Provider\WsseProvider
            arguments: ['', %kernel.cache_dir%/security/nonces]

          wsse.security.authentication.listener:
            class:  Acme\DemoBundle\Security\Firewall\WsseListener
            arguments: [@security.context, @security.authentication.manager]


    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
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

        // src/Acme/HelloBundle/Resources/config/services.php
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

Now that your services are defined, you need to tell your security context
about your factory.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
          factories:
            - "%kernel.root_dir%/../vendor/bundles/Acme/DemoBundle/Resources/config/security_factories.xml"

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <factories>
              "%kernel.root_dir%/../vendor/bundles/Acme/DemoBundle/Resources/config/security_factories.xml
            </factories>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'factories' => array(
              "%kernel.root_dir%/../vendor/bundles/Acme/DemoBundle/Resources/config/security_factories.xml"
            ),
        ));

You are finished! You can now define parts of your app as under WSSE
protection.

  .. code-block:: yaml

      security:
          firewalls:
              wsse_secured:
                  pattern:   /api/.*
                  wsse:      true

Congratulations!  You have written your very own authentication provider!

A Little Extra
--------------

How about making your WSSE security extension a bit more exciting? The
possibilities are endless. You can start with adding options under the
``wsse`` key in your security configuration. For instance, the time allowed
before expiring the Created header item, by default, is 5 minutes. To make
this configurable, you will first need to edit ``WsseFactory`` and define
the new option in the ``addConfiguration`` method.

.. code-block:: php

    class WsseFactory implements SecurityFactoryInterface
    {
        # ...

        public function addConfiguration(NodeDefinition $node)
        {
          $builder = $node->children();

          $builder->scalarNode('lifetime')->defaultValue(300);
        }
    }

Now, in the ``create`` method of the factory, the ``$config``
argument will contain a 'lifetime' key, set to five minutes
unless otherwise set in the configuration. Pass this argument
to your authentication provider in order to put it to use.

.. code-block:: php

    class WsseFactory implements SecurityFactoryInterface
    {
        public function create(ContainerBuilder $container, $id,
          $config, $userProvider, $defaultEntryPoint)
        {
            $providerId = 'security.authentication.provider.wsse.'.$id;
            $container
                ->setDefinition($providerId,
                  new DefinitionDecorator('wsse.security.authentication.provider'))
                ->replaceArgument(0, new Reference($userProvider))
                ->replaceArgument(1, $config['lifetime'])
            ;
            # ...
        }
        # ...
    }

The rest is up to you! Any relevant configuration items can be defined
in the factory and consumed or passed to the other classes in the container.

.. _`WSSE`: http://www.xml.com/pub/a/2003/12/17/dive.html
