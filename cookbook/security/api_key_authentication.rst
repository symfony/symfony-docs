.. index::
    single: Security; Custom Request Authenticator

How to Authenticate Users with API Keys
=======================================

Nowadays, it's quite usual to authenticate the user via an API key (when developing
a web service for instance). The API key is provided for every request and is
passed as a query string parameter or via a HTTP header.

The API Key Authenticator
-------------------------

.. versionadded:: 2.4
    The ``SimplePreAuthenticatorInterface`` interface was added in Symfony 2.4.

Authenticating a user based on the Request information should be done via a
pre-authentication mechanism. The :class:`Symfony\\Component\\Security\\Core\\Authentication\\SimplePreAuthenticatorInterface`
interface allows to implement such a scheme really easily::

    // src/Acme/HelloBundle/Security/ApiKeyAuthenticator.php
    namespace Acme\HelloBundle\Security;

    use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Core\User\User;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\Exception\BadCredentialsException;

    class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface
    {
        protected $userProvider;

        public function __construct(ApiKeyUserProviderInterface $userProvider)
        {
            $this->userProvider = $userProvider;
        }

        public function createToken(Request $request, $providerKey)
        {
            if (!$request->query->has('apikey')) {
                throw new BadCredentialsException('No API key found');
            }

            return new PreAuthenticatedToken(
                'anon.',
                $request->query->get('apikey'),
                $providerKey
            );
        }

        public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
        {
            $apikey = $token->getCredentials();
            if (!$this->userProvider->getUsernameForApiKey($apikey)) {
                throw new AuthenticationException(
                    sprintf('API Key "%s" does not exist.', $apikey)
                );
            }

            $user = new User(
                $this->userProvider->getUsernameForApiKey($apikey),
                $apikey,
                array('ROLE_USER')
            );

            return new PreAuthenticatedToken(
                $user,
                $apikey,
                $providerKey,
                $user->getRoles()
            );
        }

        public function supportsToken(TokenInterface $token, $providerKey)
        {
            return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
        }
    }

``$userProvider`` can be any user provider implementing an interface similar to
this::

    // src/Acme/HelloBundle/Security/ApiKeyUserProviderInterface.php
    namespace Acme\HelloBundle\Security;

    use Symfony\Component\Security\Core\User\UserProviderInterface;

    interface ApiKeyUserProviderInterface extends UserProviderInterface
    {
        public function getUsernameForApiKey($apikey);
    }

.. note::

    Read the dedicated article to learn
    :doc:`how to create a custom user provider </cookbook/security/custom_provider>`.

To access a resource protected by such an authenticator, you need to add an apikey
parameter to the query string, like in ``http://example.com/admin/foo?apikey=37b51d194a7513e45b56f6524f2d51f2``.

Configuration
-------------

Configure your ``ApiKeyAuthenticator`` as a service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            # ...

            apikey_authenticator:
                class:     Acme\HelloBundle\Security\ApiKeyAuthenticator
                arguments: [@your_api_key_user_provider]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <!-- ... -->

                <service id="apikey_authenticator"
                    class="Acme\HelloBundle\Security\ApiKeyAuthenticator"
                >
                    <argument type="service" id="your_api_key_user_provider" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...

        $container->setDefinition('apikey_authenticator', new Definition(
            'Acme\HelloBundle\Security\ApiKeyAuthenticator',
            array(new Reference('your_api_key_user_provider'))
        ));

Then, activate it in your firewalls section using the ``simple-preauth`` key
like this:

.. configuration-block::

    .. code-block:: yaml

        security:
        firewalls:
            secured_area:
                pattern: ^/admin
                simple-preauth:
                    provider:      ...
                    authenticator: apikey_authenticator

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
                    pattern="^/admin"
                    provider="..."
                >
                    <simple-preauth authenticator="apikey_authenticator" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ..

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area'       => array(
                    'pattern'        => '^/admin',
                    'provider'       => 'authenticator',
                    'simple-preauth' => array(
                        'provider'       => ...,
                        'authenticator'  => 'apikey_authenticator',
                    ),
                ),
            ),
        ));
