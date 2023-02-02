Using the new Authenticator-based Security
==========================================

.. versionadded:: 5.1

    Authenticator-based security was introduced as an
    :doc:`experimental feature </contributing/code/experimental>` in
    Symfony 5.1.

In Symfony 5.1, a new authentication system was introduced. This system
changes the internals of Symfony Security, to make it more extensible
and more understandable.

.. _security-enable-authenticator-manager:

Enabling the System
-------------------

The authenticator-based system can be enabled using the
``enable_authenticator_manager`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            enable_authenticator_manager: true
            # ...

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
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            'enable_authenticator_manager' => true,
            // ...
        ]);

The new system is backwards compatible with the current authentication
system, with some exceptions that will be explained in this article:

* :ref:`Access control must be used to enforce authentication <authenticators-access-control>`
* :ref:`Anonymous users no longer exist <authenticators-removed-anonymous>`
* :ref:`Configuring the authentication entry point is required when more than one authenticator is used <authenticators-required-entry-point>`
* :ref:`The authentication providers are refactored into Authenticators <authenticators-removed-authentication-providers>`

.. _authenticators-access-control:

Use Access Control to Require Authentication
--------------------------------------------

Previously, if the firewall wasn't configured with ``anonymous`` support,
it automatically required users to authenticate. As the new firewall
always supports unauthenticated requests (:ref:`authenticators-removed-anonymous`),
you **must** define ``access_control`` rules to enforce authentication.
Without this, unauthenticated users can visit pages behind the firewall.

If the application doesn't use roles, you can check for
``IS_AUTHENTICATED_REMEMBERED`` to require authentication (both normal and
remembered):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            enable_authenticator_manager: true

            # ...
            access_control:
                # require authentication for all routes under /admin
                - { path: ^/admin, roles: IS_AUTHENTICATED_REMEMBERED }

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

                <access-control>
                    <!-- require authentication for all routes under /admin -->
                    <rule path="^/admin" role="IS_AUTHENTICATED_REMEMBERED"/>
                </access-control>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

        $container->loadFromExtension('security', [
            'enable_authenticator_manager' => true,

            // ...
            'access_control' => [
                // require authentication for all routes under /admin
                ['path' => '^/admin', 'roles' => 'IS_AUTHENTICATED_REMEMBERED']
            ],
        ]);

.. tip::

    If you're using Symfony 5.4 or newer, use ``IS_AUTHENTICATED`` instead.

.. _authenticators-removed-anonymous:

Adding Support for Unsecured Access (i.e. Anonymous Users)
----------------------------------------------------------

In Symfony, visitors that haven't yet logged in to your website were called
:ref:`anonymous users <firewalls-authentication>`. The new system no longer
has anonymous authentication. Instead, these sessions are now treated as
unauthenticated (i.e. there is no security token). When using
``isGranted()``, the result will always be ``false`` (i.e. denied) as this
session is handled as a user without any privileges.

In the ``access_control`` configuration, you can use the new
``PUBLIC_ACCESS`` security attribute to whitelist some routes for
unauthenticated access (e.g. the login page):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            enable_authenticator_manager: true

            # ...
            access_control:
                # allow unauthenticated users to access the login form
                - { path: ^/admin/login, roles: PUBLIC_ACCESS }

                # but require authentication for all other admin routes
                - { path: ^/admin, roles: ROLE_ADMIN }

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

                <access-control>
                    <!-- allow unauthenticated users to access the login form -->
                    <rule path="^/admin/login" role="PUBLIC_ACCESS"/>

                    <!-- but require authentication for all other admin routes -->
                    <rule path="^/admin" role="ROLE_ADMIN"/>
                </access-control>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

        $container->loadFromExtension('security', [
            'enable_authenticator_manager' => true,

            // ...
            'access_control' => [
                // allow unauthenticated users to access the login form
                ['path' => '^/admin/login', 'roles' => AuthenticatedVoter::PUBLIC_ACCESS],

                // but require authentication for all other admin routes
                ['path' => '^/admin', 'roles' => 'ROLE_ADMIN'],
            ],
        ]);

Granting Anonymous Users Access in a Custom Voter
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.2

    The ``NullToken`` class was introduced in Symfony 5.2.

If you're using a :doc:`custom voter </security/voters>`, you can allow
anonymous users access by checking for a special
:class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\NullToken`. This token is used
in the voters to represent the unauthenticated access::

    // src/Security/PostVoter.php
    namespace App\Security;

    // ...
    use Symfony\Component\Security\Core\Authentication\Token\NullToken;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\Voter;

    class PostVoter extends Voter
    {
        // ...

        protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
        {
            // ...

            if ($token instanceof NullToken) {
                // the user is not authenticated, e.g. only allow them to
                // see public posts
                return $subject->isPublic();
            }
        }
    }

.. _authenticators-required-entry-point:

Configuring the Authentication Entry Point
------------------------------------------

Sometimes, one firewall has multiple ways to authenticate (e.g. both a form
login and an API token authentication). In these cases, it is now required
to configure the *authentication entry point*. The entry point is used to
generate a response when the user is not yet authenticated but tries to access
a page that requires authentication. This can be used for instance to redirect
the user to the login page.

You can configure this using the ``entry_point`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            enable_authenticator_manager: true

            # ...
            firewalls:
                main:
                    # allow authentication using a form or HTTP basic
                    form_login: ~
                    http_basic: ~

                    # configure the form authentication as the entry point for unauthenticated users
                    entry_point: form_login

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

                <!-- entry-point: configure the form authentication as the entry
                                  point for unauthenticated users -->
                <firewall name="main"
                    entry-point="form_login"
                >
                    <!-- allow authentication using a form or HTTP basic -->
                    <form-login/>
                    <http-basic/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Component\Security\Http\Firewall\AccessListener;

        $container->loadFromExtension('security', [
            'enable_authenticator_manager' => true,

            // ...
            'firewalls' => [
                'main' => [
                    // allow authentication using a form or HTTP basic
                    'form_login' => null,
                    'http_basic' => null,

                    // configure the form authentication as the entry point for unauthenticated users
                    'entry_point' => 'form_login'
                ],
            ],
        ]);

.. note::

    You can also create your own authentication entry point by creating a
    class that implements
    :class:`Symfony\\Component\\Security\\Http\\EntryPoint\\AuthenticationEntryPointInterface`.
    You can then set ``entry_point`` to the service id (e.g.
    ``entry_point: App\Security\CustomEntryPoint``)

.. _authenticators-removed-authentication-providers:

Creating a Custom Authenticator
-------------------------------

Security traditionally could be extended by writing
:doc:`custom authentication providers </security/custom_authentication_provider>`.
The authenticator-based system dropped support for these providers and
introduced a new authenticator interface as a base for custom
authentication methods.

.. tip::

    :doc:`Guard authenticators </security/guard_authentication>` are still
    supported in the authenticator-based system. It is however recommended
    to also update these when you're refactoring your application to the
    new system. The new authenticator interface has many similarities with the
    guard authenticator interface, making the rewrite easier.

Authenticators should implement the
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\AuthenticatorInterface`.
You can also extend
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\AbstractAuthenticator`,
which has a default implementation for the ``createAuthenticatedToken()``
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
    use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
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

        public function authenticate(Request $request): PassportInterface
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

The authenticator can be enabled using the ``custom_authenticators`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            enable_authenticator_manager: true

            # ...
            firewalls:
                main:
                    custom_authenticators:
                        - App\Security\ApiKeyAuthenticator

                    # don't forget to also configure the entry_point if the
                    # authenticator implements AuthenticationEntryPointInterface
                    # entry_point: App\Security\CustomFormLoginAuthenticator

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

                <!-- don't forget to also configure the entry-point if the
                     authenticator implements AuthenticatorEntryPointInterface
                <firewall name="main"
                    entry-point="App\Security\CustomFormLoginAuthenticator"> -->

                <firewall name="main">
                    <custom-authenticator>App\Security\ApiKeyAuthenticator</custom-authenticator>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\ApiKeyAuthenticator;
        use Symfony\Component\Security\Http\Firewall\AccessListener;

        $container->loadFromExtension('security', [
            'enable_authenticator_manager' => true,

            // ...
            'firewalls' => [
                'main' => [
                    'custom_authenticators' => [
                        ApiKeyAuthenticator::class,
                    ],

                    // don't forget to also configure the entry_point if the
                    // authenticator implements AuthenticatorEntryPointInterface
                    // 'entry_point' => [App\Security\CustomFormLoginAuthenticator::class],
                ],
            ],
        ]);

The ``authenticate()`` method is the most important method of the
authenticator. Its job is to extract credentials (e.g. username &
password, or API tokens) from the ``Request`` object and transform these
into a security
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Passport`.

.. tip::

    If you want to customize the login form, you can also extend from the
    :class:`Symfony\\Component\\Security\\Http\\Authenticator\\AbstractLoginFormAuthenticator`
    class instead.

Security Passports
~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.2

    The ``UserBadge`` was introduced in Symfony 5.2. Prior to 5.2, the user
    instance was provided directly to the passport.

A passport is an object that contains the user that will be authenticated as
well as other pieces of information, like whether a password should be checked
or if "remember me" functionality should be enabled.

The default
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Passport`
requires a user and credentials.

Use the
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\UserBadge`
to attach the user to the passport. The ``UserBadge`` requires a user
identifier (e.g. the username or email), which is used to load the user
using :ref:`the user provider <security-user-providers>`::

    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

    // ...
    $passport = new Passport(new UserBadge($email), $credentials);

.. note::

    You can optionally pass a user loader as second argument to the
    ``UserBadge``. This callable receives the ``$userIdentifier``
    and must return a ``UserInterface`` object (otherwise a
    ``UsernameNotFoundException`` is thrown)::

        // src/Security/CustomAuthenticator.php
        namespace App\Security;

        use App\Repository\UserRepository;
        // ...

        class CustomAuthenticator extends AbstractAuthenticator
        {
            private $userRepository;

            public function __construct(UserRepository $userRepository)
            {
                $this->userRepository = $userRepository;
            }

            public function authenticate(Request $request): PassportInterface
            {
                // ...

                return new Passport(
                    new UserBadge($email, function ($userIdentifier) {
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

        use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;

        // ...
        return new Passport(new UserBadge($email), new CustomCredentials(
            // If this function returns anything else than `true`, the credentials
            // are marked as invalid.
            // The $credentials parameter is equal to the next argument of this class
            function ($credentials, UserInterface $user) {
                return $user->getApiToken() === $credentials;
            },

            // The custom credentials
            $apiToken
        ));


Self Validating Passport
........................

If you don't need any credentials to be checked (e.g. when using API
tokens), you can use the
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\SelfValidatingPassport`.
This class only requires a ``UserBadge`` object and optionally `Passport
Badges`_.

Passport Badges
~~~~~~~~~~~~~~~

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
    successful login. This badge requires the plaintext password and a
    password upgrader (e.g. the user repository). See :doc:`/security/password_migration`.

:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\CsrfTokenBadge`
    Automatically validates CSRF tokens for this authenticator during
    authentication. The constructor requires a token ID (unique per form)
    and CSRF token (unique per request). See :doc:`/security/csrf`.

:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\PreAuthenticatedUserBadge`
    Indicates that this user was pre-authenticated (i.e. before Symfony was
    initiated). This skips the
    :doc:`pre-authentication user checker </security/user_checkers>`.

.. versionadded:: 5.2

    Since 5.2, the ``PasswordUpgradeBadge`` is automatically added to
    the passport if the passport has ``PasswordCredentials``.

For instance, if you want to add CSRF to your custom authenticator, you
would initialize the passport like this::

    // src/Service/LoginAuthenticator.php
    namespace App\Service;

    // ...
    use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
    use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
    use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

    class LoginAuthenticator extends AbstractAuthenticator
    {
        public function authenticate(Request $request): PassportInterface
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

.. tip::

    Besides badges, passports can define attributes, which allows the
    ``authenticate()`` method to store arbitrary information in the
    passport to access it from other authenticator methods (e.g.
    ``createAuthenticatedToken()``)::

        // ...
        use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

        class LoginAuthenticator extends AbstractAuthenticator
        {
            // ...

            public function authenticate(Request $request): PassportInterface
            {
                // ... process the request

                $passport = new SelfValidatingPassport(new UserBadge($username), []);

                // set a custom attribute (e.g. scope)
                $passport->setAttribute('scope', $oauthScope);

                return $passport;
            }

            public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface
            {
                // read the attribute value
                return new CustomOauthToken($passport->getUser(), $passport->getAttribute('scope'));
            }
        }

.. versionadded:: 5.2

    Passport attributes were introduced in Symfony 5.2.
