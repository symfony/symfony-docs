How to Write a Custom Authenticator
===================================

Symfony comes with :ref:`many authenticators <security-authenticators>` and
third party bundles also implement more complex cases like JWT and oAuth
2.0. However, sometimes you need to implement a custom authentication
mechanism that doesn't exist yet or you need to customize one. In such
cases, you must create and use your own authenticator.

Authenticators should implement the
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\AuthenticatorInterface`.
You can also extend
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\AbstractAuthenticator`,
which has a default implementation for the ``createToken()``
method that fits most use-cases::

    // src/Security/ApiKeyAuthenticator.php
    namespace App\Security;

    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
    use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
    use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
    use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

    class ApiKeyAuthenticator extends AbstractAuthenticator
    {
        /**
         * Called on every request to decide if this authenticator should be
         * used for the request. Returning `false` will cause this authenticator
         * to be skipped.
         */
        public function supports(Request $request): ?bool
        {
            return $request->headers->has('X-AUTH-TOKEN');
        }

        public function authenticate(Request $request): Passport
        {
            $apiToken = $request->headers->get('X-AUTH-TOKEN');
            if (null === $apiToken) {
                // The token header was empty, authentication fails with HTTP Status
                // Code 401 "Unauthorized"
                throw new CustomUserMessageAuthenticationException('No API token provided');
            }

            return new SelfValidatingPassport(new UserBadge($apiToken));
        }

        public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
        {
            // on success, let the request continue
            return null;
        }

        public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
        {
            $data = [
                // you may want to customize or obfuscate the message first
                'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

                // or to translate this message
                // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
            ];

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }
    }

.. tip::

    If your custom authenticator is a login form, you can extend from the
    :class:`Symfony\\Component\\Security\\Http\\Authenticator\\AbstractLoginFormAuthenticator`
    class instead to make your job easier.

The authenticator can be enabled using the ``custom_authenticators`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:

            # ...
            firewalls:
                main:
                    custom_authenticators:
                        - App\Security\ApiKeyAuthenticator

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config enable-authenticator-manager="true">
                <!-- ... -->

                <firewall name="main">
                    <custom-authenticator>App\Security\ApiKeyAuthenticator</custom-authenticator>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\ApiKeyAuthenticator;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->enableAuthenticatorManager(true);
            // ....

            $security->firewall('main')
                ->customAuthenticators([ApiKeyAuthenticator::class])
            ;
        };

.. tip::

    You may want your authenticator to implement
    ``AuthenticationEntryPointInterface``. This defines the response sent
    to users to start authentication (e.g. when they visit a protected
    page). Read more about it in :doc:`/security/entry_point`.

The ``authenticate()`` method is the most important method of the
authenticator. Its job is to extract credentials (e.g. username &
password, or API tokens) from the ``Request`` object and transform these
into a security
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Passport`
(security passports are explained later in this article).

After the authentication process finished, the user is either authenticated
or there was something wrong (e.g. incorrect password). The authenticator
can define what happens in these cases:

``onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response``
    If the user is authenticated, this method is called with the
    authenticated ``$token``. This method can return a response (e.g.
    redirect the user to the homepage).

    If ``null`` is returned, the request continues like normal (i.e. the
    controller matching the login route is called). This is useful for API
    routes where each route is protected by an API key header.

``onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response``
    If an ``AuthenticationException`` is thrown during authentication, the
    process fails and this method is called. This method can return a
    response (e.g. to return a 401 Unauthorized response in API routes).

    If ``null`` is returned, the request continues like normal. This is
    useful for e.g. login forms, where the login controller is run again
    with the login errors.

    If you're using :ref:`login throttling <security-login-throttling>`,
    you can check if ``$exception`` is an instance of
    :class:`Symfony\\Component\\Security\\Core\\Exception\\TooManyLoginAttemptsAuthenticationException`
    (e.g. to display an appropriate message).

    **Caution**: Never use ``$exception->getMessage()`` for ``AuthenticationException``
    instances. This message might contain sensitive information that you
    don't want to be publicly exposed. Instead, use ``$exception->getMessageKey()``
    and ``$exception->getMessageData()`` like shown in the full example
    above. Use :class:`Symfony\\Component\\Security\\Core\\Exception\\CustomUserMessageAuthenticationException`
    if you want to set custom error messages.

.. tip::

    If your login method is interactive, which means that the user actively
    logged into your application, you may want your authenticator to implement the
    :class:`Symfony\\Component\\Security\\Http\\Authenticator\\InteractiveAuthenticatorInterface`
    so that it dispatches an
    :class:`Symfony\\Component\\Security\\Http\\Event\\InteractiveLoginEvent`

.. _security-passport:

Security Passports
------------------

A passport is an object that contains the user that will be authenticated as
well as other pieces of information, like whether a password should be checked
or if "remember me" functionality should be enabled.

The default
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Passport`
requires a user and some sort of "credentials" (e.g. a password).

Use the
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\UserBadge`
to attach the user to the passport. The ``UserBadge`` requires a user
identifier (e.g. the username or email), which is used to load the user
using :ref:`the user provider <security-user-providers>`::

    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

    // ...
    $passport = new Passport(new UserBadge($email), $credentials);

.. note::

    The maximum length allowed for the user identifier is 4096 characters to
    prevent `session storage flooding`_ attacks.

.. note::

    You can optionally pass a user loader as second argument to the
    ``UserBadge``. This callable receives the ``$userIdentifier``
    and must return a ``UserInterface`` object (otherwise a
    ``UserNotFoundException`` is thrown)::

        // src/Security/CustomAuthenticator.php
        namespace App\Security;

        use App\Repository\UserRepository;
        // ...

        class CustomAuthenticator extends AbstractAuthenticator
        {
            public function __construct(
                private UserRepository $userRepository,
            ) {
            }

            public function authenticate(Request $request): Passport
            {
                // ...

                return new Passport(
                    new UserBadge($email, function (string $userIdentifier): ?User {
                        return $this->userRepository->findOneBy(['email' => $userIdentifier]);
                    }),
                    $credentials
                );
            }
        }

The following credential classes are supported by default:

:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Credentials\\PasswordCredentials`
    This requires a plaintext ``$password``, which is validated using the
    :ref:`password encoder configured for the user <security-encoding-user-password>`::

        use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

        // ...
        return new Passport(new UserBadge($email), new PasswordCredentials($plaintextPassword));

:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Credentials\\CustomCredentials`
    Allows a custom closure to check credentials::

        use Symfony\Component\Security\Core\User\UserInterface;
        use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;

        // ...
        return new Passport(new UserBadge($email), new CustomCredentials(
            // If this function returns anything else than `true`, the credentials
            // are marked as invalid.
            // The $credentials parameter is equal to the next argument of this class
            function (string $credentials, UserInterface $user): bool {
                return $user->getApiToken() === $credentials;
            },

            // The custom credentials
            $apiToken
        ));


Self Validating Passport
~~~~~~~~~~~~~~~~~~~~~~~~

If you don't need any credentials to be checked (e.g. when using API
tokens), you can use the
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\SelfValidatingPassport`.
This class only requires a ``UserBadge`` object and optionally `Passport Badges`_.

Passport Badges
---------------

The ``Passport`` also optionally allows you to add *security badges*.
Badges attach more data to the passport (to extend security). By default,
the following badges are supported:

:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\RememberMeBadge`
    When this badge is added to the passport, the authenticator indicates
    remember me is supported. Whether remember me is actually used depends
    on special ``remember_me`` configuration. Read
    :doc:`/security/remember_me` for more information.

:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\PasswordUpgradeBadge`
    This is used to automatically upgrade the password to a new hash upon
    successful login (if needed). This badge requires the plaintext password and a
    password upgrader (e.g. the user repository). See :ref:`security-password-migration`.

:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\CsrfTokenBadge`
    Automatically validates CSRF tokens for this authenticator during
    authentication. The constructor requires a token ID (unique per form)
    and CSRF token (unique per request). See :doc:`/security/csrf`.

:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\PreAuthenticatedUserBadge`
    Indicates that this user was pre-authenticated (i.e. before Symfony was
    initiated). This skips the
    :doc:`pre-authentication user checker </security/user_checkers>`.

.. note::

    The ``PasswordUpgradeBadge`` is automatically added to the passport if the
    passport has ``PasswordCredentials``.

For instance, if you want to add CSRF to your custom authenticator, you
would initialize the passport like this::

    // src/Service/LoginAuthenticator.php
    namespace App\Service;

    // ...
    use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
    use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

    class LoginAuthenticator extends AbstractAuthenticator
    {
        public function authenticate(Request $request): Passport
        {
            $password = $request->request->get('password');
            $username = $request->request->get('username');
            $csrfToken = $request->request->get('csrf_token');

            // ... validate no parameter is empty

            return new Passport(
                new UserBadge($username),
                new PasswordCredentials($password),
                [new CsrfTokenBadge('login', $csrfToken)]
            );
        }
    }

Passport Attributes
-------------------

Besides badges, passports can define attributes, which allows the ``authenticate()``
method to store arbitrary information in the passport to access it from other
authenticator methods (e.g. ``createToken()``)::

    // ...
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

    class LoginAuthenticator extends AbstractAuthenticator
    {
        // ...

        public function authenticate(Request $request): Passport
        {
            // ... process the request

            $passport = new SelfValidatingPassport(new UserBadge($username), []);

            // set a custom attribute (e.g. scope)
            $passport->setAttribute('scope', $oauthScope);

            return $passport;
        }

        public function createToken(Passport $passport, string $firewallName): TokenInterface
        {
            // read the attribute value
            return new CustomOauthToken($passport->getUser(), $passport->getAttribute('scope'));
        }
    }

.. _`session storage flooding`: https://symfony.com/blog/cve-2016-4423-large-username-storage-in-session
