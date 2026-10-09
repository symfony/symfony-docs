How to Log in Users with OpenID Connect
=======================================

`OpenID Connect`_ (OIDC) is an identity layer on top of OAuth 2.0. It is what
powers the "Log in with ..." buttons that delegate authentication to an identity
provider such as Keycloak, Authentik, Auth0, Okta or Microsoft Entra ID.

The ``oidc_login`` authenticator implements the OIDC `Authorization Code Flow`_,
the interactive flow a web application needs:

#. A user opens a protected page and Symfony redirects them to the identity
   provider;
#. The user authenticates there, and the provider redirects them back to your
   application with an authorization code;
#. The authenticator exchanges that code for tokens, validates the ID token,
   reads the user's claims and starts a session.

.. note::

    This authenticator is different from the ``oidc`` and ``oidc_user_info``
    token handlers of the :doc:`access token authenticator </security/access_token>`.
    Those validate a token that the client already holds, which is useful for
    APIs; they don't redirect the browser and don't start a session.

.. versionadded:: 8.2

    The ``oidc_login`` authenticator was introduced in Symfony 8.2.

Installation
------------

The authenticator talks to the provider with the HttpClient component, and
validates the ID token with the `web-token/jwt-library`_ package. Install both
first:

.. code-block:: terminal

    $ composer require symfony/http-client web-token/jwt-library

1) Configure the Authenticator
------------------------------

Enable ``oidc_login`` in the firewall. Every client needs three options: the
issuer URL of your provider, the client identifier that the provider issued to
your application, and how your application authenticates at the token endpoint
of the provider:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            providers:
                oidc_provider_name:
                    oidc: ~

            firewalls:
                main:
                    provider: oidc_provider_name
                    oidc_login:
                        provider_uri: '%env(OIDC_PROVIDER_URI)%'
                        client_id: '%env(OIDC_CLIENT_ID)%'
                        client_authentication:
                            client_secret_basic: '%env(OIDC_CLIENT_SECRET)%'
                        scope: ['openid', 'profile', 'email']

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'providers' => [
                    'oidc_provider_name' => [
                        'oidc' => null,
                    ],
                ],

                'firewalls' => [
                    'main' => [
                        'provider' => 'oidc_provider_name',
                        'oidc_login' => [
                            'provider_uri' => '%env(OIDC_PROVIDER_URI)%',
                            'client_id' => '%env(OIDC_CLIENT_ID)%',
                            'client_authentication' => [
                                'client_secret_basic' => '%env(OIDC_CLIENT_SECRET)%',
                            ],
                            'scope' => ['openid', 'profile', 'email'],
                        ],
                    ],
                ],
            ],
        ]);

``provider_uri`` is the issuer URL (e.g. ``https://accounts.example.com``). The
authenticator fetches ``.well-known/openid-configuration`` from it to discover
the authorization, token and UserInfo endpoints, so you don't have to configure
them. The discovery document is cached for one hour by default (change it with
the ``discovery_cache_ttl`` option).

``client_id`` identifies your application to the provider. The authenticator
sends it in the authorization request and checks the ``aud`` claim of the ID
token against it.

``client_authentication`` defines how your application authenticates at the
token endpoint when it exchanges the authorization code for tokens.
``client_secret_basic`` sends the client secret issued by the provider; the
other methods are explained in :ref:`oidc-login-token-endpoint`.

The provider redirects users back to ``check_path``, which is ``/oidc/callback``
by default. Register the full URL of this path as a redirect URI in the provider
(e.g. ``https://example.com/oidc/callback``).

2) Import the OIDC Routes
-------------------------

The callback path needs a route; otherwise, the router returns a 404 error
before the firewall can handle the redirect of the provider. Symfony defines
this route (and the route that starts the flow, explained in the next section)
in a route loader that your application must import, as it does for the
:ref:`logout routes <security-logging-out>`. If your project uses
:ref:`Symfony Flex <symfony-flex>`, the SecurityBundle recipe already imports
it. Otherwise, import it yourself:

.. configuration-block::

    .. code-block:: yaml

        # config/routes/security.yaml
        _security_oidc_login:
            resource: security.authenticator.oidc_login.route_loader
            type: service

    .. code-block:: php

        // config/routes/security.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return Routes::config([
            '_security_oidc_login' => [
                'resource' => 'security.authenticator.oidc_login.route_loader',
                'type' => 'service',
            ],
        ]);

The loader names the routes after the firewall:
``_oidc_login_callback_<firewallname>`` and ``_oidc_login_start_<firewallname>``
(e.g. ``_oidc_login_callback_main``). Use these names to generate their URLs.

If ``check_path`` or ``start_path`` is a route name instead of a path, the
loader doesn't define a route for it, so you must define it yourself. Two
firewalls can share the same callback path, but not the same start path,
because the start route is tied to the firewall whose flow it starts.

3) Start the Flow
-----------------

When ``oidc_login`` is the only authenticator of the firewall that can start
the authentication, it also becomes the
:doc:`entry point </security/entry_point>` of the firewall: when an anonymous
user requests a protected page, Symfony redirects them to the authorization
endpoint of the provider. You don't need anything else to log in users.

If the firewall uses other authenticators that can start the authentication
(e.g. ``form_login``), set the ``entry_point`` option of the firewall to the one
you want to use; otherwise, Symfony throws an exception when compiling the
container.

To display a "Log in with ..." button (e.g. in a login page that lists several
ways to log in), link to the ``_oidc_login_start_<firewallname>`` route imported
in the previous section. This route redirects to the provider. Its path is
``/oidc/start`` by default, and you can change it with the ``start_path``
option:

.. code-block:: html+twig

    <a href="{{ path('_oidc_login_start_main') }}">Log in with Example</a>

Don't link to ``check_path``, because it only handles the redirect coming back
from the provider and rejects any request that doesn't include an authorization
code.

.. note::

    The authenticator stores the ``state``, the ``nonce`` and the PKCE verifier
    of the flow in the session, so it doesn't work in stateless firewalls or
    when the session is disabled.

When the Login Fails
~~~~~~~~~~~~~~~~~~~~

When the authenticator rejects the callback (e.g. because the ``state`` expired
or doesn't match, the provider returned an ``error`` instead of a code, or the
ID token isn't valid), Symfony redirects the user to ``failure_path``. If this
option is not set, it uses ``login_path``, which is ``/login`` by default.
Applications that only log in users with OIDC usually don't have that page,
so set one of these options to a page of your application to avoid a 404
error on failed logins:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        failure_path: app_login_failed

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'failure_path' => 'app_login_failed',
                        ],
                    ],
                ],
            ],
        ]);

Symfony stores the authentication exception in the session, so the controller
of that page can get it with the ``getLastAuthenticationError()`` method of
:class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationUtils`,
as :ref:`login form controllers <security-form-login>` do.

4) Load the User
----------------

After validating the ID token, the authenticator fetches the user claims from
the UserInfo endpoint of the provider. Then, the user provider of the firewall
loads the user using the ``sub`` claim as the identifier, and receives all the
claims as attributes. You can change both the source of the claims and the
identifier claim, as explained later in this section.

The built-in ``oidc`` user provider shown above creates an
:class:`Symfony\\Component\\Security\\Core\\User\\OidcUser` object from those
claims. It's a convenient way to get started, but it has two limitations:

* Claims can't grant roles or define the identity of the user: all users get
  ``ROLE_USER``, and the ``roles`` and ``user_identifier`` claims sent by the
  identity provider are ignored;
* It can't load users by their identifier alone, so it doesn't work with
  :doc:`user impersonation </security/impersonating_user>`.

To map claims to your own roles or to load users from your own storage, create
a custom user provider. If it implements
:class:`Symfony\\Component\\Security\\Core\\User\\AttributesBasedUserProviderInterface`,
it receives the claims as the second argument of ``loadUserByIdentifier()``::

    // src/Security/OidcUserProvider.php
    namespace App\Security;

    use App\Entity\User;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    /**
     * @implements AttributesBasedUserProviderInterface<User>
     */
    class OidcUserProvider implements AttributesBasedUserProviderInterface
    {
        public function __construct(
            private EntityManagerInterface $entityManager,
        ) {
        }

        // $identifier is the "sub" claim by default; $attributes has all claims
        public function loadUserByIdentifier(
            string $identifier,
            array $attributes = [],
        ): UserInterface {
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['oidcSubject' => $identifier]) ?? new User($identifier);

            $isAdmin = \in_array('admins', $attributes['groups'] ?? [], true);
            $user->setEmail($attributes['email'] ?? null);
            $user->setRoles($isAdmin ? ['ROLE_ADMIN'] : []);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $user;
        }

        // ...
    }

Use the ``scope`` option to request the claims you need. The authenticator
always requests the ``openid`` scope, because OIDC requires it; add scopes like
``profile`` or ``email`` to get their related claims.

The authenticator stores the tokens returned by the provider as attributes of
the security token, so you can use them to call the APIs of the provider::

    $token = $security->getToken();

    $accessToken = $token->getAttribute('oidc_access_token');
    $idToken = $token->getAttribute('oidc_id_token');
    $refreshToken = $token->getAttribute('oidc_refresh_token');
    $expiresAt = $token->getAttribute('oidc_access_token_expires_at');

The last two attributes are ``null`` when the provider doesn't return them.
Providers only issue a refresh token when you request it (on most providers,
with the ``offline_access`` scope), and ``expires_in`` is optional in the token
response. Read :ref:`oidc-login-renewing-access-token` to learn how to renew the
access token using the refresh token.

Reading the Claims from the ID Token
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the claims are fetched from the UserInfo endpoint. However, some
providers include all the requested claims in the ID token, and some don't
provide a UserInfo endpoint at all. Set ``user_data_source`` to ``id_token`` to
read the claims from the validated ID token instead. In this case, the provider
doesn't need to announce a ``userinfo_endpoint``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        user_data_source: id_token

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'user_data_source' => 'id_token',
                        ],
                    ],
                ],
            ],
        ]);

In both cases, the ID token must include a valid ``sub`` claim. When the claims
come from the UserInfo endpoint, their ``sub`` claim must be the same as the one
of the ID token (`OIDC Core 1.0, Section 5.3.2`_).

Identifying Users by Another Claim
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the user identifier is the ``sub`` claim, which is the only claim
that OIDC guarantees to be stable and unique for each user. If your application
identifies users with another claim (e.g. ``email``), set it in the
``user_identifier_claim`` option. This claim is read from the same source as
the other claims, and the authentication fails when it's missing, empty or not
a string:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        user_identifier_claim: email

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'user_identifier_claim' => 'email',
                        ],
                    ],
                ],
            ],
        ]);

.. warning::

    Anyone who can change the value of that claim in the provider can log in
    as the matching user of your application. Only use claims that the provider
    guarantees to be unique, verified and stable. For example, don't use an
    email address that users can change themselves or that the provider
    doesn't verify.

The built-in ``oidc`` user provider uses that value as the user identifier,
and custom user providers receive it as the first argument of
``loadUserByIdentifier()``.

Customizing the Authorization Request
-------------------------------------

The authenticator manages the parameters required by the flow:
``response_type``, ``client_id``, ``redirect_uri``, ``scope``, ``state``,
``nonce``, ``max_age`` and the PKCE parameters. Symfony throws an exception if
you try to set any of them. Add any other parameter supported by your provider
in the ``authorization_params`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        authorization_params:
                            prompt: 'consent'
                            ui_locales: 'fr-FR'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'authorization_params' => [
                                'prompt' => 'consent',
                                'ui_locales' => 'fr-FR',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

`OIDC Core 1.0, Section 3.1.2.1`_ defines the ``prompt``, ``display``,
``ui_locales``, ``acr_values`` and ``login_hint`` parameters for this request.
Providers can also support other custom parameters.

Computing the Parameters per Request
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``authorization_params`` option defines static values. If some parameter
depends on the current request (e.g. a ``ui_locales`` value based on the current
locale or a ``login_hint`` value stored in the session), listen to the
:class:`Symfony\\Component\\Security\\Http\\Event\\OidcAuthorizationRequestEvent`,
which is dispatched right before redirecting the user to the provider::

    // src/Security/OidcAuthorizationRequestListener.php
    namespace App\Security;

    use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
    use Symfony\Component\Security\Http\Event\OidcAuthorizationRequestEvent;

    #[AsEventListener]
    final class OidcAuthorizationRequestListener
    {
        public function __invoke(OidcAuthorizationRequestEvent $event): void
        {
            $request = $event->getRequest();

            $event->setParam('ui_locales', $request->getLocale());

            if ($email = $request->getSession()->get('login_email')) {
                $event->setParam('login_hint', $email);
            } else {
                $event->removeParam('login_hint');
            }
        }
    }

The event initially contains the parameters configured in
``authorization_params``. The ``setParam()`` and ``removeParam()`` methods
change a single parameter, so several listeners can change their own parameters
without overwriting the changes made by others. The ``setParams()`` method
replaces all parameters. If the same listener is used in several firewalls,
call ``getFirewallName()`` to know which firewall is starting the flow.

As with the ``authorization_params`` option, Symfony throws an exception if you
try to set any of the parameters managed by the authenticator.

.. tip::

    Listeners registered with ``#[AsEventListener]`` listen to the global event
    dispatcher, so they are called for all firewalls. To listen to the events of
    a single firewall, register the listener in the event dispatcher of that
    firewall, as explained in :ref:`security-security-events`.

Receiving the Response in a POST Request
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the provider redirects the user to ``check_path`` with the
authorization response (the authorization code, the ``state``, etc.) in the
query string, so this response ends up in the browser history and in the access
logs of web servers and proxies. Providers that support the
`Form Post Response Mode`_ can instead return an HTML page that sends the same
response to ``check_path`` in a POST request. Enable it with the
``response_mode`` parameter:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        authorization_params:
                            response_mode: 'form_post'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'authorization_params' => [
                                'response_mode' => 'form_post',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

When the callback is a POST request, the authenticator reads the response from
the request body and ignores the query string; otherwise, it reads the response
from the query string. The callback route defined by the imported route loader
accepts any HTTP method, but if ``check_path`` is the name of a route of your
own, make sure that route accepts POST requests.

.. warning::

    When the provider runs on another site, this POST request is cross-site.
    Browsers don't send the session cookie with it unless the cookie uses
    ``SameSite=None``, so the authenticator can't find the ``state`` of the
    login attempt and the login fails. In that case, set the
    ``framework.session.cookie_samesite`` option to ``none``. Browsers only
    accept this value in secure cookies, so your application must use HTTPS.
    This value also makes browsers send the session cookie in all other
    cross-site requests, so protect your forms against CSRF attacks.

Requiring a Recent Authentication
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``max_age`` option defines the maximum time, in seconds, since the user last
authenticated in the provider. If more time has passed, the provider must ask
the user to authenticate again. This is a separate option (instead of an item
of ``authorization_params``) because the authenticator also checks it: the ID
token must include an ``auth_time`` claim, which is validated against this
value, using ``allowed_time_drift`` as the only tolerance:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        max_age: 300

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'max_age' => 300,
                        ],
                    ],
                ],
            ],
        ]);

The security token also stores the authentication details reported by the
provider:

* The ``auth_time`` claim is used as the moment when the user authenticated
  (and it's never later than the moment when the security token is created).
  Without this claim, the moment of the login is used instead;
* The ``amr`` claim is used as the authentication methods used by the provider
  (e.g. ``pwd``, ``otp``, ``mfa``, etc. as defined in `RFC 8176`_). Without this
  claim, the authentication method is recorded as unspecified.

This way, ``max_age`` and the ``IS_AUTHENTICATED_RECENTLY`` and
``IS_AUTHENTICATED_VERY_RECENTLY`` attributes use the same moment as the last
time the user authenticated. The ``recent_authentication_lifetime`` and
``very_recent_authentication_lifetime`` options of the firewall define for how
long those two attributes are granted.

Asking the Provider to Authenticate the User Again
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When access is denied because the user doesn't have the
``IS_AUTHENTICATED_RECENTLY`` or ``IS_AUTHENTICATED_VERY_RECENTLY`` attribute,
the authenticator starts a new authorization request. If ``oidc_login`` is the
entry point of the firewall, it's also its re-authentication entry point, so you
don't need to configure anything. Otherwise, set the
``re_authentication_entry_point`` option of the firewall to the
``security.authenticator.oidc_login.<firewallname>`` service.

This new request includes two parameters:

* ``prompt=login``, which asks the provider to prompt the user for their
  credentials again instead of reusing its existing session
  (`OIDC Core 1.0, Section 3.1.2.1`_);
* ``id_token_hint`` with the previous ID token, so the provider knows which user
  is re-authenticating instead of displaying an account selector.

Both parameters are applied after ``authorization_params`` and after the
``OidcAuthorizationRequestEvent`` listeners. This prevents a configured
``prompt=none`` value or a listener that removes the parameter from disabling
the re-authentication.

.. tip::

    The specification only recommends providers to follow the ``prompt``
    parameter (it's a "SHOULD", not a "MUST"). If you need to enforce the
    re-authentication, also configure the ``max_age`` option. Symfony verifies
    this value, so if the provider ignores it, the ``auth_time`` check fails
    instead of accepting the old authentication.

Using PKCE
~~~~~~~~~~

The authenticator applies PKCE (`RFC 7636`_) to all authorization requests,
using the ``S256`` challenge method: it sends the hash of a random verifier
stored in the session, and it only sends the verifier itself when exchanging the
authorization code. This way, nobody else can exchange an intercepted code.

All modern providers should support PKCE. Use the ``pkce`` option only for
providers that reject the ``code_challenge`` parameter (set ``enabled`` to
``false``) or that only support the ``plain`` method:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        pkce:
                            enabled: true
                            method: 'plain'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'pkce' => [
                                'enabled' => true,
                                'method' => 'plain',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

Public clients can't disable PKCE, because nothing else would tie the
authorization code to them.

.. _oidc-login-token-endpoint:

Authenticating at the Token Endpoint
------------------------------------

When exchanging the authorization code for tokens, your application
authenticates at the token endpoint using the method configured in
``client_authentication``. This option must define exactly one of the
following keys:

``client_secret_basic``
    Sends the client secret as HTTP Basic credentials. This is the method
    recommended by `RFC 6749`_, Section 2.3.1, so use it when the provider
    supports it.

``client_secret_post``
    Sends the client secret in the body of the token request. Use it only for
    providers that don't support any other method.

``client_secret_jwt`` and ``private_key_jwt``
    Sign a JWT assertion instead of sending the secret (see
    :ref:`oidc-login-jwt-assertion`).

``tls_client_auth`` and ``self_signed_tls_client_auth``
    Authenticate with the TLS certificate of the client (see
    :ref:`oidc-login-tls-client-auth`). They don't take any parameter, so
    ``client_authentication: tls_client_auth`` is the short form.

``none``
    Declares a public client: an application that can't keep a secret (e.g.
    a single-page, mobile or native application). It relies on PKCE to protect
    the code exchange, and it can't disable PKCE or the ID token signature
    verification. ``client_authentication: none`` is the short form.

``id``
    The id of a service that implements a custom authentication method (see
    :ref:`oidc-login-custom-client-authentication`). Any string other than
    ``none``, ``tls_client_auth`` and ``self_signed_tls_client_auth`` is the
    short form (e.g.
    ``client_authentication: App\Security\KmsJwtAuthentication``).

Use the method registered for your application in the provider. Providers list
the methods they support in the ``token_endpoint_auth_methods_supported`` entry
of their discovery document. For example, to send the secret in the body of the
request:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        client_authentication:
                            client_secret_post: '%env(OIDC_CLIENT_SECRET)%'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'client_authentication' => [
                                'client_secret_post' => '%env(OIDC_CLIENT_SECRET)%',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

To declare a public client, set ``client_authentication`` to ``none``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        provider_uri: '%env(OIDC_PROVIDER_URI)%'
                        client_id: '%env(OIDC_CLIENT_ID)%'
                        client_authentication: none

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            'provider_uri' => '%env(OIDC_PROVIDER_URI)%',
                            'client_id' => '%env(OIDC_CLIENT_ID)%',
                            'client_authentication' => 'none',
                        ],
                    ],
                ],
            ],
        ]);

.. _oidc-login-jwt-assertion:

Signing a JWT Assertion
~~~~~~~~~~~~~~~~~~~~~~~

Instead of sending the secret, the client can authenticate with a signed JWT
assertion (`RFC 7523`_), using the ``private_key_jwt`` or ``client_secret_jwt``
methods of `OIDC Core 1.0, Section 9`_.

``private_key_jwt`` signs the assertion with a private key of the client. The
provider only knows the public key (registered as the ``jwks`` of the client or
published at its ``jwks_uri``), so it can't use it to authenticate as the
client:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        client_authentication:
                            private_key_jwt:
                                key: '%env(OIDC_CLIENT_SIGNING_KEY)%'
                                algorithm: 'ES256'
                                lifetime: 60

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'client_authentication' => [
                                'private_key_jwt' => [
                                    'key' => '%env(OIDC_CLIENT_SIGNING_KEY)%',
                                    'algorithm' => 'ES256',
                                    'lifetime' => 60,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

``key`` is the JSON-encoded JWK of the private key (you can also pass this value
directly as a string to ``private_key_jwt``). If the client publishes several
keys, add a ``kid`` to the JWK so the provider knows which key verifies the
signature. ``algorithm`` defaults to ``RS256`` and accepts the ``RS*``, ``PS*``
and ``ES*`` algorithms (`FAPI 2.0`_ requires ``PS256`` or ``ES256``).

``client_secret_jwt`` signs the assertion with an HMAC that uses the client
secret as the key, so the secret is never sent:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        client_authentication:
                            client_secret_jwt: '%env(OIDC_CLIENT_SECRET)%'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'client_authentication' => [
                                'client_secret_jwt' => '%env(OIDC_CLIENT_SECRET)%',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

The string value sets only the secret. To also set other options, use the
``secret``, ``algorithm``, ``lifetime`` and ``audience`` keys.
``algorithm`` defaults to ``HS256`` and also accepts ``HS384`` and ``HS512``.
As required by `RFC 7518`_, Section 3.2, the secret must be at least as long as
the output of the algorithm: 32 bytes for ``HS256``, 48 for ``HS384`` and 64 for
``HS512``.

.. tip::

    Prefer ``private_key_jwt`` over ``client_secret_jwt``. With
    ``client_secret_jwt``, the provider also knows the secret, so it could sign
    assertions in the name of the client.

In both methods, ``lifetime`` defines for how many seconds the assertion is
valid. It defaults to ``60``. Keep it short: each assertion is created for a
single request and sent immediately, and providers that don't track the ``jti``
claim would accept a stolen assertion during all that time. The ``algorithm``
must be one of those listed by your provider in the
``token_endpoint_auth_signing_alg_values_supported`` entry of its discovery
document.

By default, the assertion names the issuer of the provider as its audience
(the ``aud`` claim) and is typed ``client-authentication+jwt`` (the ``typ``
header), as required by `draft-ietf-oauth-rfc7523bis`_ and `FAPI 2.0`_. Symfony
takes the issuer from the discovery document, after checking it against
``provider_uri``. Some providers (e.g. Ory Hydra, Curity and Okta) refuse this
audience. For them, set the ``audience`` option to ``token_endpoint``; the
assertion then names the token endpoint and has no ``typ`` header:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        provider_uri: 'https://accounts.example.com'
                        client_id: '%env(OIDC_CLIENT_ID)%'
                        client_authentication:
                            private_key_jwt:
                                key: '%env(OIDC_CLIENT_SIGNING_KEY)%'
                                audience: 'token_endpoint'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            'provider_uri' => 'https://accounts.example.com',
                            'client_id' => '%env(OIDC_CLIENT_ID)%',
                            'client_authentication' => [
                                'private_key_jwt' => [
                                    'key' => '%env(OIDC_CLIENT_SIGNING_KEY)%',
                                    'audience' => 'token_endpoint',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

.. note::

    If you create ``ClientSecretJwt`` or ``PrivateKeyJwt`` objects yourself,
    their assertions name the token endpoint and have no ``typ`` header unless
    you pass the ``OidcDiscovery`` object of the provider as the
    ``$discovery`` argument.

.. _oidc-login-tls-client-auth:

Authenticating with a TLS Certificate
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

With mutual TLS (`RFC 8705`_), the client authenticates with the certificate it
presents in the TLS connection instead of a secret or a signed assertion.
Define the certificate in the ``client_certificate`` option and pick the method
that tells the provider how to verify it:

* ``tls_client_auth``: the certificate is issued by a certificate authority
  and the provider checks its subject against the one registered for the
  client;
* ``self_signed_tls_client_auth``: the certificate is self-signed and the
  provider checks it against the certificates registered for the client (in
  its ``jwks`` or ``jwks_uri``).

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        client_certificate:
                            # the paths of the PEM files of the certificate
                            # and its private key
                            certificate: '%env(OIDC_CLIENT_CERT_PATH)%'
                            key: '%env(OIDC_CLIENT_KEY_PATH)%'
                            # only needed if the private key is encrypted
                            passphrase: '%env(OIDC_CLIENT_KEY_PASSPHRASE)%'
                        client_authentication: tls_client_auth

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'client_certificate' => [
                                // the paths of the PEM files of the certificate
                                // and its private key
                                'certificate' => '%env(OIDC_CLIENT_CERT_PATH)%',
                                'key' => '%env(OIDC_CLIENT_KEY_PATH)%',
                                // only needed if the private key is encrypted
                                'passphrase' => '%env(OIDC_CLIENT_KEY_PASSPHRASE)%',
                            ],
                            'client_authentication' => 'tls_client_auth',
                        ],
                    ],
                ],
            ],
        ]);

If the same PEM file contains both the certificate and the private key, pass
its path directly as the value of ``client_certificate``.

The ``tls_client_auth`` and ``self_signed_tls_client_auth`` methods require
the ``client_certificate`` option, but this option works with any other method
too. For example, combine it with ``private_key_jwt`` to get access tokens
bound to the certificate (`RFC 8705`_, Section 3).

When a certificate is defined, the authenticator presents it in the token
requests (including the ones that renew the access token) and in the UserInfo
requests. If the provider lists other URLs for these endpoints in the
``mtls_endpoint_aliases`` entry of its discovery document, those requests are
sent to them. Aliases that can't be used safely (e.g. ``http://`` URLs) are
rejected. The alias of the authorization endpoint is ignored, because the
browser of the user, not your application, sends that request. The discovery
and JWKS requests never present the certificate.

.. _oidc-login-custom-client-authentication:

Using Your Own Authentication Method
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To use a client authentication method not provided by Symfony, or to
authenticate in a way that the built-in methods don't support (e.g. signing the
assertion with a key that never leaves a key management service), create a
service that implements
:class:`Symfony\\Component\\Security\\Http\\OAuth2\\ClientAuthentication\\ClientAuthenticationInterface`.
Its ``authenticate()`` method receives the HttpClient options of the token
request and returns them with the client authentication added. Its
``getMethod()`` method returns the name of the method as defined in
`RFC 7591`_, Section 2::

    // src/Security/KmsJwtAuthentication.php
    namespace App\Security;

    use App\Kms\AssertionSigner;
    use Symfony\Component\Security\Http\OAuth2\ClientAuthentication\ClientAuthenticationInterface;

    final class KmsJwtAuthentication implements ClientAuthenticationInterface
    {
        public function __construct(
            private AssertionSigner $assertionSigner,
        ) {
        }

        public function authenticate(
            string $clientId,
            string $tokenEndpoint,
            array $options,
        ): array {
            // the private key never leaves the KMS, which signs the assertion
            $options['body']['client_assertion_type'] =
                'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';
            $options['body']['client_assertion'] =
                $this->assertionSigner->sign($clientId, $tokenEndpoint);

            return $options;
        }

        public function getMethod(): string
        {
            return 'private_key_jwt';
        }
    }

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        client_authentication: App\Security\KmsJwtAuthentication

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\KmsJwtAuthentication;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'client_authentication' => KmsJwtAuthentication::class,
                        ],
                    ],
                ],
            ],
        ]);

If ``getMethod()`` returns ``none``, the rules of public clients also apply to
this service.

.. _oidc-login-dpop:

Binding Tokens to a Key (DPoP)
------------------------------

By default, the tokens issued by the provider are bearer tokens: anyone who
steals one can use it. With DPoP (Demonstrating Proof of Possession, defined in
`RFC 9449`_), the application signs a proof with its private key in each
request to the provider, and the provider binds the tokens it issues to that
key. A stolen token is useless without the key. `FAPI 2.0`_ accepts DPoP as an
alternative to mutual TLS to bind the tokens.

Set the ``dpop`` option to enable it:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        dpop:
                            key: '%env(OIDC_DPOP_KEY)%'
                            algorithm: 'ES256'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'dpop' => [
                                'key' => '%env(OIDC_DPOP_KEY)%',
                                'algorithm' => 'ES256',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

``key`` is the JSON-encoded JWK of a private EC or RSA key (you can also pass
this value directly as a string to ``dpop``). The key must match the
``algorithm``, which defaults to ``ES256``: public keys and keys of another
type or curve are rejected. Keep this key as secret as a client secret. You
don't have to register it at the provider, because each proof includes the
public key. The ``algorithm`` must be one of those listed by your provider in
the ``dpop_signing_alg_values_supported`` entry of its discovery document.

When DPoP is enabled, the requests to the token endpoint (including token
refreshes) and to the UserInfo endpoint include a proof, and the access token
is sent with the ``DPoP`` authorization scheme instead of ``Bearer``. The
authorization request also includes the ``dpop_jkt`` parameter with the
thumbprint of the key, so an attacker who steals the authorization code can't
exchange it for tokens. The authenticator manages this parameter, so you can't
set it in ``authorization_params``.

.. warning::

    Only enable ``dpop`` if your provider supports it. When it's enabled, the
    authenticator rejects any token response whose ``token_type`` is not
    ``DPoP``, because that means that the provider didn't bind the token. When
    it's disabled, any ``token_type`` other than ``Bearer`` is rejected.

.. note::

    If your application uses the access token to call an API, that API also
    expects a DPoP proof, which your application must create and sign itself.

Verifying the ID Token Signature
--------------------------------

The authenticator verifies the signature of the ID token by default. It gets
the signing keys from the ``jwks_uri`` announced by the provider in its
discovery document (this URL must use HTTPS, like all the other endpoints). The
keys are cached for the lifetime advertised by the provider in that response
(up to 30 days), or for ``discovery_cache_ttl`` seconds if the provider doesn't
advertise any lifetime. If an ID token is signed with a key that isn't in the
cache, the authenticator fetches the keys again, so you don't have to do
anything when the provider rotates its keys.

By default, only the ``RS256`` algorithm is accepted, because it's the only one
that OIDC providers are required to support. If your provider uses other
algorithms, list them in the ``algorithms`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        id_token_signature:
                            algorithms: ['ES256']

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'id_token_signature' => [
                                'algorithms' => ['ES256'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

The accepted algorithms are ``RS256``, ``RS384``, ``RS512``, ``ES256``,
``ES384``, ``ES512``, ``PS256``, ``PS384`` and ``PS512``. Your provider lists
the algorithms it uses in the ``id_token_signing_alg_values_supported`` entry
of its discovery document. HMAC algorithms are not accepted, to prevent using
a public key of the provider as a shared secret.

By default, only the keys that the provider explicitly designates for signing
are used. Set ``id_token_signature.enforce_key_usage_verification`` to ``false``
to also accept keys that don't define any usage. Keys that are restricted to
encryption are always rejected. This option works the same as in the
:doc:`OIDC access token handler </security/access_token>`.

.. warning::

    If you set ``id_token_signature.required`` to ``false``, the ID token is
    decoded without verifying its signature. `OIDC Core 1.0, Section 3.1.3.7`_
    only allows this because the token comes directly from the token endpoint
    over TLS. This makes the ID token only as trustworthy as the TLS
    verification of the HTTP client used in that request. Never disable it
    when the HTTP client uses ``verify_peer: false`` or ``verify_host: false``,
    or when the requests go through a proxy that terminates TLS. Public
    clients can't disable this verification.

Configuring the HTTP Client
---------------------------

The authenticator makes all its requests to the provider (discovery document,
JWKS, token endpoint and UserInfo endpoint) with the ``http_client`` service.
Some providers require a ``User-Agent`` header that identifies your application
(and use it for quotas and diagnostics), and you might also want to define your
own timeout or retry strategy. Use the ``http_client`` option to set the id of
the HTTP client used in all those requests:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        http_client: oidc.client

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'http_client' => 'oidc.client',
                        ],
                    ],
                ],
            ],
        ]);

``oidc.client`` can be any service that implements
:class:`Symfony\\Contracts\\HttpClient\\HttpClientInterface`. Usually, it's a
scoped client (see :doc:`/http_client`):

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        http_client:
            scoped_clients:
                oidc.client:
                    scope: 'https://accounts\.example\.com'
                    headers: { 'User-Agent': 'AcmeApp/1.0 (+https://example.com)' }
                    timeout: 5
                    retry_failed: { max_retries: 2 }

.. warning::

    The ``scope`` of the scoped client must match all the endpoints, not only
    the issuer. The requests are sent to the URLs announced by the provider in
    its discovery document, which are often on other hosts. For example, the
    issuer of Google is ``accounts.google.com``, but it uses
    ``oauth2.googleapis.com`` for its token endpoint, ``www.googleapis.com``
    for its JWKS and ``openidconnect.googleapis.com`` for its UserInfo
    endpoint. The options of a scoped client only apply to the URLs that match
    its scope, and Symfony doesn't warn you about it. If the scope only matches
    the issuer, the options apply only to the discovery request, and the other
    three requests use the default options.

If the provider uses several hosts for its endpoints, create the client with
:method:`Symfony\\Contracts\\HttpClient\\HttpClientInterface::withOptions`
instead, so its options apply to all requests, whatever their host:

.. code-block:: yaml

    # config/services.yaml
    services:
        oidc.client:
            class: Symfony\Contracts\HttpClient\HttpClientInterface
            factory: ['@http_client', 'withOptions']
            arguments:
                - headers: { 'User-Agent': 'AcmeApp/1.0 (+https://example.com)' }
                  timeout: 5

.. warning::

    Never add the client credentials to this HTTP client (with ``auth_basic``
    or with a custom header). They would also be sent in the discovery, JWKS
    and UserInfo requests, which don't need them. In addition, ``auth_basic``
    encodes the credentials in base64 as they are, whereas
    ``client_secret_basic`` first applies form URL encoding to them, as
    required by `RFC 6749`_, Section 2.3.1. The ``client_authentication``
    option only sends the credentials to the token endpoint, and in the format
    required by the specification.

For the same reason, define the TLS client certificate in the
``client_certificate`` option (see :ref:`oidc-login-tls-client-auth`) instead
of the ``local_cert`` and ``local_pk`` options of this HTTP client. The
requests that present the ``client_certificate`` ignore the ``local_cert``,
``local_pk`` and ``passphrase`` options of the HTTP client.

.. note::

    These four requests are always made with ``max_redirects`` set to ``0``,
    whatever the configuration of the HTTP client. Otherwise, a redirect
    returned by the provider could send the client credentials, the
    authorization code or the access token to any other URL.

.. _oidc-login-renewing-access-token:

Renewing the Access Token
-------------------------

Access tokens are short-lived (they usually expire after a few minutes). To
keep calling the APIs of the provider on behalf of the logged-in user, Symfony
can renew the access token with the refresh token grant defined in
`RFC 6749, Section 6`_.
Providers only issue a refresh token when you request it, so add the scope
required by your provider (``offline_access`` on most providers) to the
``scope`` option.

Renewing the Token on Demand
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Security\\Http\\Authenticator\\Oidc\\OidcTokenRefresher`
class renews the tokens of logged-in users. Symfony registers it as the
``security.authenticator.oidc_login.token_refresher.<firewallname>`` service
for each firewall that uses ``oidc_login`` (even if the automatic renewal
explained later is disabled). This service doesn't have an autowiring alias, so
inject it using its id::

    // src/Service/ProviderApiClient.php
    namespace App\Service;

    use Symfony\Bundle\SecurityBundle\Security;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\Security\Http\Authenticator\Oidc\OidcTokenRefresher;
    use Symfony\Contracts\HttpClient\HttpClientInterface;

    class ProviderApiClient
    {
        public function __construct(
            #[Autowire(
                service: 'security.authenticator.oidc_login.token_refresher.main',
            )]
            private OidcTokenRefresher $refresher,
            private Security $security,
            private HttpClientInterface $client,
        ) {
        }

        public function fetchProfile(): array
        {
            $token = $this->security->getToken();
            if (null === $token) {
                throw new \LogicException('The API requires a logged-in user.');
            }

            $this->refresher->refreshIfNeeded($token);

            return $this->client->request('GET', 'https://api.example.com/me', [
                'auth_bearer' => $token->getAttribute('oidc_access_token'),
            ])->toArray();
        }
    }

The ``refreshIfNeeded()`` method renews the tokens only if the access token
expires within the configured leeway, and returns ``true`` if it renewed them.
It does nothing if the security token doesn't include a refresh token or if the
expiration time of the access token is unknown. The ``refresh()`` method always
renews the tokens, whatever their expiration time.

Both methods replace the attributes of the security token with the new values.
If the provider returns a new ID token, it's validated before storing anything:
its signature (if the firewall verifies signatures), its ``iss``, ``aud``,
``exp``, ``iat`` and ``nbf`` claims, and its ``sub`` claim, which must be the
same as the one of the login. If the ID token is rejected, the security token
doesn't change. If the provider returns a new refresh token, it replaces the
previous one; otherwise, the previous refresh token is kept.

When the provider returns the ``invalid_grant`` OAuth 2.0 error, both methods
throw an
:class:`Symfony\\Component\\Security\\Http\\Exception\\OidcInvalidGrantException`.
This means that the refresh token can no longer be used, so catch this
exception to make the user log in again. Any other error (the provider is not
available, it returns a 5xx error, the request times out, etc.) throws an
:class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`,
so you can try again later.

Renewing the Token Automatically
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Enable the ``refresh_access_token`` option to renew the access token
automatically when it's about to expire. This option is disabled by default
because it sends a request to the token endpoint while handling the request of
the user, and because it can log out the user:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        scope: ['openid', 'profile', 'offline_access']
                        refresh_access_token:
                            enabled: true
                            leeway: 30

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'scope' => ['openid', 'profile', 'offline_access'],
                            'refresh_access_token' => [
                                'enabled' => true,
                                'leeway' => 30,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

When this option is enabled, a listener runs right after the firewall loads the
security token from the session, so the current request and the rest of the
session use the renewed tokens. If the provider returns an ``invalid_grant``
error, the security token is removed and the user is logged out. Any other
error logs a warning and keeps the current tokens, so the renewal is tried again
in the next request. This way, if the provider is temporarily unavailable, your
users are not logged out.

.. warning::

    The automatic renewal requires the ``expires_in`` value in the token
    response. If the provider doesn't return it, the access token is never
    renewed automatically, because its expiration time is unknown.

.. warning::

    Providers that rotate refresh tokens expect each refresh token to be used
    only once. If your session handler doesn't lock the session, two concurrent
    requests of the same user could use the same refresh token twice, and the
    provider would then revoke all the tokens of that user. The default session
    handler locks the session, so it doesn't have this problem.

Injecting the Client of a Firewall
----------------------------------

Each firewall that uses ``oidc_login`` has its own
:class:`Symfony\\Component\\Security\\Http\\Authenticator\\Oidc\\OidcClientInterface`
service, which sends the requests of your application to the provider (e.g. to
exchange the authorization code or to renew the tokens). Inject it when you
need to call other endpoints of the provider, such as revoking a token.

To autowire it, type-hint the interface and name the argument after the
firewall in camelCase followed by ``OidcClient`` (e.g. ``$mainOidcClient`` for
the ``main`` firewall or ``$adminAreaOidcClient`` for the ``admin_area``
firewall)::

    // src/Security/TokenRevoker.php
    namespace App\Security;

    use Symfony\Component\Security\Http\Authenticator\Oidc\OidcClientInterface;

    class TokenRevoker
    {
        public function __construct(
            private OidcClientInterface $mainOidcClient,
        ) {
        }

        public function revoke(string $refreshToken): void
        {
            // the first argument is the name of the endpoint in the discovery
            // document of the provider; the second one is the form body; the
            // optional third one is an array of HttpClient options (e.g. to
            // add an 'Accept' header); the client authenticates the request
            // and adds the 'client_id' parameter to the body
            $response = $this->mainOidcClient->request('revocation_endpoint', [
                'token' => $refreshToken,
                'token_type_hint' => 'refresh_token',
            ]);

            // the response is lazy, so check its status code yourself
            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException('The token was not revoked.');
            }
        }
    }

Alternatively, use the
:class:`Symfony\\Component\\DependencyInjection\\Attribute\\Target` attribute
with the name of the firewall. In that case, the name of the argument doesn't
matter::

    use Symfony\Component\DependencyInjection\Attribute\Target;
    use Symfony\Component\Security\Http\Authenticator\Oidc\OidcClientInterface;

    public function __construct(
        #[Target('main')]
        private OidcClientInterface $oidcClient,
    ) {
    }

The interface can't be autowired without the name of the firewall, because an
application can define several ``oidc_login`` firewalls. You can also inject
the client using its service id, which is
``security.authenticator.oidc_login.client.<firewallname>``.

Calling Other Endpoints of the Provider
---------------------------------------

Besides logging users in, your application may need to call other endpoints of
the provider: to revoke a token (`RFC 7009`_), to introspect it (`RFC 7662`_),
to push an authorization request (`RFC 9126`_) or to run a grant that Symfony
doesn't provide, such as the client credentials grant. Use the
:method:`Symfony\\Component\\Security\\Http\\Authenticator\\Oidc\\OidcClientInterface::request`
method of the firewall client to make a client-authenticated ``POST`` request
to any endpoint announced by the provider. This request uses the same client
authentication, TLS client certificate and mTLS endpoint aliases (`RFC 8705`_)
as the other requests sent to the provider.

The method returns the raw HttpClient response, because each endpoint answers
differently: a JSON object, a signed JWT or an empty body. It throws an
:class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`
if the provider doesn't announce the endpoint or if the endpoint URL doesn't
use HTTPS.

Keep these rules in mind:

* Symfony overrides the ``body`` and ``max_redirects`` HttpClient options that
  you pass.
* A client assertion (see :ref:`oidc-login-jwt-assertion`) names the same
  audience as in the requests to the token endpoint (the issuer or the token
  endpoint, depending on the ``audience`` option), regardless of the endpoint
  that you call.
* When using DPoP (`RFC 9449`_), Symfony only sends a proof to the token and
  pushed authorization request endpoints. A grant run through this method
  returns a DPoP-bound token whose ``token_type`` Symfony doesn't check, and
  your application must send its own proofs when using that token.
* Public clients (``client_authentication: none``) can use this method too
  (e.g. to revoke a token or to redeem a device code). The only request that
  Symfony refuses is redeeming an authorization code without a PKCE verifier.

Logging Out
-----------

:ref:`Logging out <security-logging-out>` works as usual. By default, it only
ends the session in your application: the user is still logged in to the
identity provider, so the next time they open a protected page, they are logged
in again without being asked for their credentials.

Logging Out of the Provider
~~~~~~~~~~~~~~~~~~~~~~~~~~~

To also log out the user from the provider, enable `RP-Initiated Logout`_ with
the ``enable_end_session`` option. The authenticator then redirects the user to
the ``end_session_endpoint`` announced by the provider in its discovery
document, sending the ID token as ``id_token_hint``. After closing its own
session, the provider redirects the user back to ``post_logout_redirect_path``,
which is a path or route name of your application:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        # ...
                        enable_end_session: true
                        post_logout_redirect_path: /

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            // ...
                            'enable_end_session' => true,
                            'post_logout_redirect_path' => '/',
                        ],
                    ],
                ],
            ],
        ]);

Register the full URL of this post-logout path in the provider, as you did with
the ``check_path`` URL. Set ``post_logout_redirect_path`` to ``null`` to not
send any ``post_logout_redirect_uri``; in this case, the provider decides where
to redirect the user.

If the provider can't be reached, or if it doesn't announce an
``end_session_endpoint`` that uses HTTPS, the user is only logged out from your
application and a warning is logged.

.. warning::

    With this option, logging out becomes a chain of redirects: first to your
    application, which closes its session; then to the provider, which closes
    its own session; and finally back to your application. `Symfony UX Turbo`_
    intercepts clicks on the logout link and follows the redirects itself, which
    doesn't work with this chain: your application closes its session, but the
    redirect to the provider never happens. The user is still logged in to the
    provider, so they are logged in again when opening the next protected page.

    Add the ``data-turbo="false"`` attribute to the logout link so Turbo
    doesn't handle it:

    .. code-block:: html+twig

        <a href="{{ path('_logout_main') }}" data-turbo="false">Log out</a>

    A logout form (see :ref:`security-logout-form`) needs the same attribute
    on its ``<form>`` element.

Logging Out Users When the Provider Ends Their Session
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The provider can also end a session on its own (e.g. when the user logs out
directly from the provider, or from another application that propagates the
logout to it with `RP-Initiated Logout`_). With `Back-Channel Logout`_, the
provider then sends a logout token to your application, which logs out the user
on the next request made by their browser. Enable it with the
``backchannel_logout`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        provider_uri: 'https://accounts.example.com'
                        client_id: '%env(OIDC_CLIENT_ID)%'
                        client_authentication:
                            client_secret_basic: '%env(OIDC_CLIENT_SECRET)%'
                        # these are the default values; set this option to
                        # 'true' to enable the feature using them
                        backchannel_logout:
                            path: /oidc/backchannel-logout
                            cache: cache.app
                            lifetime: 86400

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'oidc_login' => [
                            'provider_uri' => 'https://accounts.example.com',
                            'client_id' => '%env(OIDC_CLIENT_ID)%',
                            'client_authentication' => [
                                'client_secret_basic' => '%env(OIDC_CLIENT_SECRET)%',
                            ],
                            // these are the default values; set this option to
                            // 'true' to enable the feature using them
                            'backchannel_logout' => [
                                'path' => '/oidc/backchannel-logout',
                                'cache' => 'cache.app',
                                'lifetime' => 86400,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

The imported route loader also defines the route that receives the logout
tokens, named ``_oidc_login_backchannel_logout_<firewallname>``. In the
provider, register the full URL of this ``path`` as the
``backchannel_logout_uri`` of your client and enable
``backchannel_logout_session_required``. Logout tokens without a ``sid``
(session ID) claim are rejected with a ``400`` response.

This route verifies the signature and the claims of the logout token and stores
the ``sid`` of the ended session in a cache pool. The request comes from the
provider, not from the browser of the user, so the session of the user can't be
changed at that moment. Instead, on the next request of the user, the security
token is removed if it belongs to an ended session. This also applies to other
firewalls that share the
:ref:`security context <reference-security-firewall-context>` of the
``oidc_login`` firewall.

Keep these constraints in mind:

* The firewall must be stateful, and each firewall needs its own ``path``;
* The ``id_token_signature.required`` option must stay ``true``, because the
  signature is the only proof that the logout token comes from the provider;
* Don't protect ``path`` with an ``access_control`` rule: the provider sends
  requests to it without any session or cookie;
* The ``cache`` pool must be shared by all the servers of your application and
  must not remove items before they expire. For example, APCu (which is local
  to each server) or Redis with an LRU eviction policy could lose some logouts;
* ``lifetime`` is how many seconds an ended session is remembered. Set it to at
  least the maximum time that a session can stay idle; otherwise, a user who
  comes back after that time is still logged in;
* Users logged in with the :doc:`remember me </security/remember_me>` feature
  are not logged out, because their security token doesn't contain any ``sid``.

The security token is removed, but the session is not invalidated. To also
invalidate the session, as the regular logout does, listen to the
:class:`Symfony\\Component\\Security\\Http\\Event\\TokenDeauthenticatedEvent`
and check the exception that caused it::

    // src/Security/OidcSessionEndedListener.php
    namespace App\Security;

    use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
    use Symfony\Component\Security\Http\Event\TokenDeauthenticatedEvent;
    use Symfony\Component\Security\Http\Exception\OidcSessionEndedException;

    final class OidcSessionEndedListener
    {
        #[AsEventListener]
        public function onTokenDeauthenticated(TokenDeauthenticatedEvent $event): void
        {
            if ($event->getException() instanceof OidcSessionEndedException) {
                $event->getRequest()->getSession()->invalidate();
            }
        }
    }

Security Considerations
-----------------------

The authenticator implements all the checks required by the specifications.
You can only disable PKCE and the ID token signature verification, and public
clients can't disable either of them. These are the main checks:

* ``provider_uri`` and all the endpoints announced in the discovery document
  must use HTTPS. For local development, loopback hosts (``localhost``,
  ``127.0.0.1``, ``::1``) and ``*.localhost`` hosts are also accepted;
* The ``issuer`` of the discovery document must match ``provider_uri``;
* All authorization requests include a ``state`` parameter, which is checked
  in the callback against the value stored in the session. This prevents login
  CSRF attacks;
* The ``iss`` authorization response parameter (`RFC 9207`_) is checked against
  the issuer, and it's required when the provider announces
  ``authorization_response_iss_parameter_supported``. This prevents mix-up
  attacks against clients registered with several providers;
* All authorization requests include a ``nonce`` parameter, which is checked
  against the ``nonce`` claim of the ID token. This ties the ID token to the
  authorization request;
* PKCE (`RFC 7636`_) is applied with the ``S256`` challenge method (unless the
  ``pkce`` option changes it), so an intercepted authorization code can't be
  exchanged by anyone else;
* The ID token signature is verified with the keys published by the provider
  (unless ``id_token_signature.required`` is disabled);
* The parameters managed by the authenticator can't be overridden with
  ``authorization_params`` or with ``OidcAuthorizationRequestEvent`` listeners.
  The ``prompt=login`` and ``id_token_hint`` parameters of a re-authentication
  are applied after both of them, so neither can disable the re-authentication;
* The ``iss``, ``aud``, ``exp``, ``iat`` and ``sub`` claims of the ID token are
  required and validated. The ``azp`` and ``nbf`` claims are also validated
  when the provider includes them. ``allowed_time_drift`` is the only tolerance
  applied to time claims;
* When the claims are fetched from the UserInfo endpoint, their ``sub`` claim
  must be the same as the one of the ID token;
* The user identifier is the ``sub`` claim. If ``user_identifier_claim`` sets
  another claim, that claim must be present and not empty;
* No redirects are followed in the discovery, JWKS, token and UserInfo
  requests;
* The built-in ``oidc`` user provider doesn't allow claims to grant roles.

Configuration Reference
-----------------------

``provider_uri`` (**required**)
    The issuer URL of the OIDC provider, used for
    ``.well-known/openid-configuration`` discovery. Must use HTTPS.

``client_id`` (**required**)
    The client identifier issued by the provider.

``client_authentication`` (**required**)
    How the application authenticates at the token endpoint:
    ``client_secret_basic`` or ``client_secret_post`` (with the client secret),
    ``client_secret_jwt`` or ``private_key_jwt`` (with an assertion signed by
    the client), ``tls_client_auth`` or ``self_signed_tls_client_auth`` (with
    the ``client_certificate`` option), ``none`` (for public clients) or ``id``
    (with the id of a service that implements
    ``ClientAuthenticationInterface``). Any string value other than ``none``,
    ``tls_client_auth`` and ``self_signed_tls_client_auth`` is considered a
    service id.

``client_authentication.client_secret_jwt.secret`` (**required**)
    The client secret used as the key of the HMAC that signs the assertion.
    Passing a string to ``client_secret_jwt`` sets this value.

``client_authentication.client_secret_jwt.algorithm`` (default: ``HS256``)
    The MAC algorithm used to sign the assertion: ``HS256``, ``HS384`` or
    ``HS512``.

``client_authentication.client_secret_jwt.lifetime`` (default: ``60``)
    For how many seconds the assertion is valid.

``client_authentication.client_secret_jwt.audience`` (default: ``issuer``)
    What the assertion names as its audience: ``issuer`` (the issuer of the
    provider) or ``token_endpoint`` (for a provider that refuses the issuer).

``client_authentication.private_key_jwt.key`` (**required**)
    The JSON-encoded JWK of the private key used to sign the assertion.
    Passing a string to ``private_key_jwt`` sets this value.

``client_authentication.private_key_jwt.algorithm`` (default: ``RS256``)
    The algorithm used to sign the assertion: ``RS256``, ``RS384``, ``RS512``,
    ``ES256``, ``ES384``, ``ES512``, ``PS256``, ``PS384`` or ``PS512``.

``client_authentication.private_key_jwt.lifetime`` (default: ``60``)
    For how many seconds the assertion is valid.

``client_authentication.private_key_jwt.audience`` (default: ``issuer``)
    What the assertion names as its audience: ``issuer`` (the issuer of the
    provider) or ``token_endpoint`` (for a provider that refuses the issuer).

``client_certificate``
    The TLS client certificate presented in the token and UserInfo requests.
    Required by the ``tls_client_auth`` and ``self_signed_tls_client_auth``
    methods. Passing a string sets ``client_certificate.certificate``.

``client_certificate.certificate`` (**required**)
    The path of the PEM file of the certificate.

``client_certificate.key`` (default: ``null``)
    The path of the PEM file of the private key, if the certificate file
    doesn't contain it.

``client_certificate.passphrase`` (default: ``null``)
    The passphrase of the private key, if it's encrypted.

``http_client`` (default: ``http_client``)
    The id of the HTTP client used in all requests made to the provider
    (discovery document, JWKS, token endpoint and UserInfo endpoint).

``dpop.key`` (**required** when ``dpop`` is set)
    The JSON-encoded JWK of the private key used to sign the DPoP proofs and
    to which the provider binds the tokens (see :ref:`oidc-login-dpop`).
    Passing a string to ``dpop`` sets this value.

``dpop.algorithm`` (default: ``ES256``)
    The algorithm used to sign the DPoP proofs: ``ES256``, ``ES384``,
    ``ES512``, ``PS256``, ``PS384``, ``PS512``, ``RS256``, ``RS384`` or
    ``RS512``.

``scope`` (default: ``['openid']``)
    The scopes of the authorization request, as a list or as a space-separated
    string.

``user_data_source`` (default: ``userinfo``)
    Where to read the user claims from: ``userinfo`` fetches them from the
    UserInfo endpoint and ``id_token`` reads them from the validated ID token.

``user_identifier_claim`` (default: ``sub``)
    The claim that contains the user identifier. ``sub`` is the only claim that
    OIDC guarantees to be stable and unique for each user.

``check_path`` (default: ``/oidc/callback``)
    The path where the provider redirects users after authenticating them. You
    can also use a route name; in that case, no route is defined for it.

``start_path`` (default: ``/oidc/start``)
    The path of the route that starts the flow by redirecting to the provider.
    You can also use a route name; in that case, no route is defined for it.

``enable_end_session`` (default: ``false``)
    Whether logging out also redirects the user to the ``end_session_endpoint``
    of the provider (RP-Initiated Logout).

``post_logout_redirect_path`` (default: ``/``)
    The path or route name of your application where the provider redirects
    users after RP-Initiated Logout. It's sent as ``post_logout_redirect_uri``;
    set it to ``null`` to not send it. It's ignored unless
    ``enable_end_session`` is ``true``.

``backchannel_logout.path`` (default: ``/oidc/backchannel-logout``)
    The path of the route that receives the logout tokens sent by the provider
    (Back-Channel Logout). Setting ``backchannel_logout`` to ``true`` enables
    this feature with the default values.

``backchannel_logout.cache`` (default: ``cache.app``)
    The id of the cache pool that stores the ended sessions.

``backchannel_logout.lifetime`` (default: ``86400``)
    For how many seconds an ended session is remembered.

``refresh_access_token.enabled`` (default: ``false``)
    Whether to renew the access token automatically, using the refresh token,
    before it expires.

``refresh_access_token.leeway`` (default: ``30``)
    How many seconds before its expiration the access token is renewed.

``id_token_signature.required`` (default: ``true``)
    Whether to verify the signature of the ID token using the keys published by
    the provider.

``id_token_signature.algorithms`` (default: ``['RS256']``)
    The signature algorithms accepted for the ID token.

``id_token_signature.enforce_key_usage_verification`` (default: ``true``)
    Whether to only use the keys that the provider designates for signing to
    verify the ID token.

``pkce.enabled`` (default: ``true``)
    Whether to protect the authorization code with PKCE.

``pkce.method`` (default: ``S256``)
    The PKCE code challenge method: ``S256`` or ``plain``.

``max_age`` (no default value)
    The maximum time, in seconds, since the user last authenticated in the
    provider. When set, it's sent as the ``max_age`` authorization parameter and checked
    against the ``auth_time`` claim of the ID token.

``authorization_params`` (default: ``[]``)
    Additional parameters of the authorization request, such as ``prompt`` or
    ``login_hint``.

``discovery_cache_ttl`` (default: ``3600``)
    For how many seconds the discovery document is cached. It's also used for
    the keys of the provider when it doesn't advertise their lifetime.

``allowed_time_drift`` (default: ``0``)
    The clock skew, in seconds, allowed when validating the time claims of the
    ID token.

This authenticator also accepts the options shared by all firewall
authenticators: ``success_handler`` and ``failure_handler`` (to replace the
default handlers with your own services), and ``login_path``,
``default_target_path``, ``always_use_default_target_path``,
``target_path_parameter``, ``use_referer``, ``failure_path`` and
``failure_path_parameter`` (to configure the default handlers), as explained in
:ref:`reference-security-firewall-form-login`.

.. _`OpenID Connect`: https://openid.net/developers/how-connect-works/
.. _`Authorization Code Flow`: https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowAuth
.. _`web-token/jwt-library`: https://github.com/web-token/jwt-library
.. _`OIDC Core 1.0, Section 3.1.2.1`: https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
.. _`RFC 6749`: https://datatracker.ietf.org/doc/html/rfc6749#section-2.3.1
.. _`RFC 6749, Section 6`: https://datatracker.ietf.org/doc/html/rfc6749#section-6
.. _`RFC 7009`: https://datatracker.ietf.org/doc/html/rfc7009
.. _`RFC 7518`: https://datatracker.ietf.org/doc/html/rfc7518#section-3.2
.. _`RFC 7523`: https://datatracker.ietf.org/doc/html/rfc7523
.. _`draft-ietf-oauth-rfc7523bis`: https://datatracker.ietf.org/doc/draft-ietf-oauth-rfc7523bis/
.. _`RFC 7591`: https://datatracker.ietf.org/doc/html/rfc7591#section-2
.. _`RFC 7636`: https://datatracker.ietf.org/doc/html/rfc7636
.. _`RFC 7662`: https://datatracker.ietf.org/doc/html/rfc7662
.. _`RFC 8176`: https://datatracker.ietf.org/doc/html/rfc8176
.. _`RFC 8705`: https://datatracker.ietf.org/doc/html/rfc8705
.. _`RFC 9126`: https://datatracker.ietf.org/doc/html/rfc9126
.. _`RFC 9207`: https://datatracker.ietf.org/doc/html/rfc9207
.. _`RFC 9449`: https://datatracker.ietf.org/doc/html/rfc9449
.. _`OIDC Core 1.0, Section 3.1.3.7`: https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
.. _`OIDC Core 1.0, Section 9`: https://openid.net/specs/openid-connect-core-1_0.html#ClientAuthentication
.. _`OIDC Core 1.0, Section 5.3.2`: https://openid.net/specs/openid-connect-core-1_0.html#UserInfoResponse
.. _`FAPI 2.0`: https://openid.net/specs/fapi-security-profile-2_0-final.html
.. _`Form Post Response Mode`: https://openid.net/specs/oauth-v2-form-post-response-mode-1_0.html
.. _`Back-Channel Logout`: https://openid.net/specs/openid-connect-backchannel-1_0.html
.. _`RP-Initiated Logout`: https://openid.net/specs/openid-connect-rpinitiated-1_0.html
.. _`Symfony UX Turbo`: https://symfony.com/bundles/ux-turbo/current/index.html
