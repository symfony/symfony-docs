.. index::
    single: Security; Custom Password Authenticator

How to Create a Custom Form Password Authenticator
==================================================

.. tip::

    Check out :doc:`/cookbook/security/guard-authentication` for a simpler and more
    flexible way to accomplish custom authentication tasks like this.

Imagine you want to allow access to your website only between 2pm and 4pm
UTC. In this entry, you'll learn how to do this for a login form (i.e. where
your user submits their username and password).

The Password Authenticator
--------------------------

.. versionadded:: 2.8
    The ``SimpleFormAuthenticatorInterface`` interface was moved to the
    ``Symfony\Component\Security\Http\Authentication`` namespace in Symfony
    2.8. Prior to 2.8, it was located in the
    ``Symfony\Component\Security\Core\Authentication`` namespace.

First, create a new class that implements
:class:`Symfony\\Component\\Security\\Http\\Authentication\\SimpleFormAuthenticatorInterface`.
Eventually, this will allow you to create custom logic for authenticating
the user::

    // src/Acme/HelloBundle/Security/TimeAuthenticator.php
    namespace Acme\HelloBundle\Security;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
    use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
    use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;

    class TimeAuthenticator implements SimpleFormAuthenticatorInterface
    {
        private $encoder;

        public function __construct(UserPasswordEncoderInterface $encoder)
        {
            $this->encoder = $encoder;
        }

        public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
        {
            try {
                $user = $userProvider->loadUserByUsername($token->getUsername());
            } catch (UsernameNotFoundException $e) {
                // CAUTION: this message will be returned to the client
                // (so don't put any un-trusted messages / error strings here)
                throw new CustomUserMessageAuthenticationException('Invalid username or password');
            }

            $passwordValid = $this->encoder->isPasswordValid($user, $token->getCredentials());

            if ($passwordValid) {
                $currentHour = date('G');
                if ($currentHour < 14 || $currentHour > 16) {
                    // CAUTION: this message will be returned to the client
                    // (so don't put any un-trusted messages / error strings here)
                    throw new CustomUserMessageAuthenticationException(
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

            // CAUTION: this message will be returned to the client
            // (so don't put any un-trusted messages / error strings here)
            throw new CustomUserMessageAuthenticationException('Invalid username or password');
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

.. versionadded:: 2.8
    The ``CustomUserMessageAuthenticationException`` class is new in Symfony 2.8
    and helps you return custom authentication messages. In 2.7 or earlier, throw
    an ``AuthenticationException`` or any sub-class (you can still do this in 2.8).

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

Inside this method, the password encoder is needed to check the password's validity::

        $passwordValid = $this->encoder->isPasswordValid($user, $token->getCredentials());

This is a service that is already available in Symfony and it uses the password algorithm
that is configured in the security configuration (e.g. ``security.yml``) under
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
                arguments: ["@security.password_encoder"]

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
                    <argument type="service" id="security.password_encoder" />
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
            array(new Reference('security.password_encoder'))
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
