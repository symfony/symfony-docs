.. index::
    single: Security; Custom Authenticator

How to create a custom Authenticator
====================================

Introduction
------------

Imagine you want to allow access to your website only between 2pm and 4pm (for
the UTC timezone). Before Symfony 2.4, you had to create a custom token, factory,
listener and provider.

The Authenticator
-----------------

Thanks to new simplified authentication customization options  in Symfony 2.4,
you don't need to create a whole bunch of new classes, but use the
:class:`Symfony\\Component\\Security\\Core\\Authentication\\SimpleFormAuthenticatorInterface`
interface instead::

    // src/Acme/HelloBundle/Security/TimeAuthenticator.php
    namespace Acme\HelloBundle\Security;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
    use Symfony\Component\Security\Core\Authentication\TokenInterface;
    use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
    use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\User\UserProviderInterface;

    class TimeAuthenticator implements SimpleFormAuthenticatorInterface
    {
        private $encoderFactory;

        public function __construct(EncoderFactoryInterface $encoderFactory)
        {
            $this->encoderFactory = $encoderFactory;
        }

        public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
        {
            try {
                $user = $userProvider->loadUserByUsername($token->getUsername());
            } catch (UsernameNotFoundException $e) {
                throw new AuthenticationException('Invalid username or password');
            }

            $encoder = $this->encoderFactory->getEncoder($user);
            $passwordValid = $encoder->isPasswordValid(
                $user->getPassword(),
                $token->getCredentials(),
                $user->getSalt()
            );

            if ($passwordValid) {
                $currentHour = date('G');
                if ($currentHour < 14 || $currentHour > 16) {
                    throw new AuthenticationException(
                        'You can only log in between 2 and 4!',
                        100
                    );
                }

                return new UsernamePasswordToken(
                    $user->getUsername(),
                    $user->getPassword(),
                    $providerKey,
                    $user->getRoles()
                );
            }

            throw new AuthenticationException('Invalid username or password');
        }

        public function supportsToken(TokenInterface $token, $providerKey)
        {
            return $token instanceof UsernamePasswordToken
                && $token->getProviderKey() === $providerKey;
        }

        public function createToken(Request $request, $username, $password, $providerKey)
        {
            return new UsernamePasswordToken($username, $password, $providerKey);
        }
    }

.. versionadded:: 2.4
    The ``SimpleFormAuthenticatorInterface`` interface was added in Symfony 2.4.

How it works
------------

There are a lot of things going on:

* ``createToken()`` creates a Token that will be used to authenticate the user;
* ``authenticateToken()`` checks that the Token is allowed to log in by first
  getting the User via the user provider and then, by checking the password
  and the current time (a Token with roles is authenticated);
* ``supportsToken()`` is just a way to allow several authentication mechanisms to
  be used for the same firewall (that way, you can for instance first try to
  authenticate the user via a certificate or an API key and fall back to a
  form login);
* An encoder is needed to check the user password's validity; this is a
  service provided by default::

        $encoder = $this->encoderFactory->getEncoder($user);
        $passwordValid = $encoder->isPasswordValid(
            $user->getPassword(),
            $token->getCredentials(),
            $user->getSalt()
        );

Configuration
-------------

Now, configure your ``TimeAuthenticator`` as a service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            time_authenticator:
                class:     Acme\HelloBundle\Security\TimeAuthenticator
                arguments: [@security.encoder_factory]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <services>
            <service id="time_authenticator"
                class="Acme\HelloBundle\Security\TimeAuthenticator">
                <argument type="service" id="security.encoder_factory"/>
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('time_authenticator', new Definition(
            'Acme\HelloBundle\Security\TimeAuthenticator',
            array(new Reference('security.encoder_factory'))
        ));

Then, activate it in your ``firewalls`` section using the ``simple-form`` key
like this:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                secured_area:
                    pattern: ^/admin
                    provider: authenticator
                    simple-form:
                        provider:      ...
                        authenticator: time_authenticator
                        check_path:    login_check
                        login_path:    login

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <config>
                <firewall name="secured_area" pattern="^/admin">
                    <provider name="authenticator" />
                    <simple-form authenticator="time_authenticator"
                        check_path="login_check"
                        login_path="login" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area'    => array(
                    'pattern'     => '^/admin',
                    'provider'    => 'authenticator',
                    'simple-form' => array(
                        'provider'      => ...,
                        'authenticator' => 'time_authenticator',
                        'check_path'    => 'login_check',
                        'login_path'    => 'login',
                    ),
                ),
            ),
        ));
