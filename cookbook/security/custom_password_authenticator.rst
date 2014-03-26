.. index::
    single: Security; Custom Password Authenticator

How to Create a Custom Form Password Authenticator
==================================================

Imagine you want to allow access to your website only between 2pm and 4pm
UTC. Before Symfony 2.4, you had to create a custom token, factory, listener
and provider. In this entry, you'll learn how to do this for a login form
(i.e. where your user submits their username and password).

The Password Authenticator
--------------------------

.. versionadded:: 2.4
    The ``SimpleFormAuthenticatorInterface`` interface was introduced in Symfony 2.4.

First, create a new class that implements
:class:`Symfony\\Component\\Security\\Core\\Authentication\\SimpleFormAuthenticatorInterface`.
Eventually, this will allow you to create custom logic for authenticating
the user::

    // src/Acme/HelloBundle/Security/TimeAuthenticator.php
    namespace Acme\HelloBundle\Security;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
                    $user,
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

How it Works
------------

Great! Now you just need to setup some :ref:`cookbook-security-password-authenticator-config`.
But first, you can find out more about what each method in this class does.

1) createToken
~~~~~~~~~~~~~~

When Symfony begins handling a request, ``createToken()`` is called, where
you create a :class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface`
object that contains whatever information you need in ``authenticateToken()``
to authenticate the user (e.g. the username and password).

Whatever token object you create here will be passed to you later in ``authenticateToken()``.

2) supportsToken
~~~~~~~~~~~~~~~~

.. include:: _supportsToken.rst.inc

3) authenticateToken
~~~~~~~~~~~~~~~~~~~~

If ``supportsToken`` returns ``true``, Symfony will now call ``authenticateToken()``.
Your job here is to check that the token is allowed to log in by first
getting the ``User`` object via the user provider and then, by checking the password
and the current time.

.. note::

    The "flow" of how you get the ``User`` object and determine whether or not
    the token is valid (e.g. checking the password), may vary based on your
    requirements.

Ultimately, your job is to return a *new* token object that is "authenticated"
(i.e. it has at least 1 role set on it) and which has the ``User`` object
inside of it.

Inside this method, an encoder is needed to check the password's validity::

        $encoder = $this->encoderFactory->getEncoder($user);
        $passwordValid = $encoder->isPasswordValid(
            $user->getPassword(),
            $token->getCredentials(),
            $user->getSalt()
        );

This is a service that is already available in Symfony and the password algorithm
is configured in the security configuration (e.g. ``security.yml``) under
the ``encoders`` key. Below, you'll see how to inject that into the ``TimeAuthenticator``.

.. _cookbook-security-password-authenticator-config:

Configuration
-------------

Now, configure your ``TimeAuthenticator`` as a service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            # ...

            time_authenticator:
                class:     Acme\HelloBundle\Security\TimeAuthenticator
                arguments: ["@security.encoder_factory"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <!-- ... -->

                <service id="time_authenticator"
                    class="Acme\HelloBundle\Security\TimeAuthenticator"
                >
                    <argument type="service" id="security.encoder_factory" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;
        
        // ...

        $container->setDefinition('time_authenticator', new Definition(
            'Acme\HelloBundle\Security\TimeAuthenticator',
            array(new Reference('security.encoder_factory'))
        ));

Then, activate it in the ``firewalls`` section of the security configuration
using the ``simple_form`` key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                secured_area:
                    pattern: ^/admin
                    # ...
                    simple_form:
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
                <!-- ... -->

                <firewall name="secured_area"
                    pattern="^/admin"
                    >
                    <simple-form authenticator="time_authenticator"
                        check-path="login_check"
                        login-path="login"
                    />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ..

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area'    => array(
                    'pattern'     => '^/admin',
                    'simple_form' => array(
                        'provider'      => ...,
                        'authenticator' => 'time_authenticator',
                        'check_path'    => 'login_check',
                        'login_path'    => 'login',
                    ),
                ),
            ),
        ));

The ``simple_form`` key has the same options as the normal ``form_login``
option, but with the additional ``authenticator`` key that points to the
new service. For details, see :ref:`reference-security-firewall-form-login`.

If creating a login form in general is new to you or you don't understand
the ``check_path`` or ``login_path`` options, see :doc:`/cookbook/security/form_login`.
