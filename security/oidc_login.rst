How to Log in Users with OpenID Connect
=======================================

`OpenID Connect`_ (OIDC) is an identity layer on top of OAuth 2.0. It is what
powers the "Log in with ..." buttons that delegate authentication to an identity
provider such as Keycloak, Authentik, Auth0, Okta or Microsoft Entra ID.

The ``oidc_login`` authenticator implements the OIDC `Authorization Code Flow`_,
the interactive flow a web application needs:

#. a user opens a protected page and is redirected to the identity provider;
#. the user authenticates there, and the provider redirects them back to your
   application with an authorization code;
#. the authenticator exchanges that code for tokens, validates the ID token,
   reads the user's claims and opens a session.

.. note::

    This is different from the :doc:`access token authenticator </security/access_token>`
    and its ``oidc`` token handlers. Those validate a token the client already
    holds, which suits APIs; they do not drive the browser redirect and do not
    open a session.

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

Enable ``oidc_login`` under the firewall. A confidential client needs three
options: the issuer URL of your provider, and the credentials it issued to your
application:

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
                        client_secret: '%env(OIDC_CLIENT_SECRET)%'
                        scope: ['openid', 'profile', 'email']
                        check_path: /oidc/callback

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
                            'client_secret' => '%env(OIDC_CLIENT_SECRET)%',
                            'scope' => ['openid', 'profile', 'email'],
                            'check_path' => '/oidc/callback',
                        ],
                    ],
                ],
            ],
        ]);

``provider_uri`` is the issuer URL (e.g. ``https://accounts.example.com``). The
authenticator fetches ``.well-known/openid-configuration`` from it to discover
the authorization, token and UserInfo endpoints, so you never configure those
by hand. The discovery document is cached for an hour by default.

``client_id`` identifies your application to the provider. It is a required
parameter of the authorization request, and it is also the value the ID token
``aud`` claim is checked against. ``client_secret`` authenticates your
application at the token endpoint when the authorization code is exchanged for
tokens; it is required unless the application is declared as a public client,
as described below.

``check_path`` is the path the provider redirects to. It must match one of the
redirect URIs registered with the provider. Register the full URL there
(e.g. ``https://example.com/oidc/callback``), not just the path.

2) Import the OIDC Routes
-------------------------

The callback path needs a route, otherwise the router answers the provider's
redirect with a 404 before the firewall ever sees it. Symfony declares that
route for you through a route loader, which your application imports, exactly
as it does for the logout routes. The same loader declares the route that
starts the flow, described in the next section:

.. configuration-block::

    .. code-block:: yaml

        # config/routes/security.yaml
        _oidc_login_callbacks:
            resource: security.authenticator.oidc_login.route_loader
            type: service

    .. code-block:: php

        // config/routes/security.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return Routes::config([
            '_oidc_login_callbacks' => [
                'resource' => 'security.authenticator.oidc_login.route_loader',
                'type' => 'service',
            ],
        ]);

.. tip::

    To reference either path, use the route names the loader declares for the
    firewall: ``_oidc_login_callback_<firewallname>`` and
    ``_oidc_login_start_<firewallname>`` (e.g. ``_oidc_login_callback_main``).

If ``check_path`` or ``start_path`` holds a route name instead of a path, no
route is declared for it and you must define that route yourself. Two firewalls
may share a callback path, but not a start path: the route declared for it
carries the name of the firewall whose flow it starts.

3) Start the Flow
-----------------

When ``oidc_login`` is the only authenticator of the firewall able to start an
authentication, it also becomes its entry point: an anonymous request to a
protected page is redirected to the provider's authorization endpoint, and
nothing else is needed to log a user in. Next to another such authenticator,
``form_login`` for instance, name the one to use in the ``entry_point`` option
of the firewall, otherwise the container refuses to compile.

To offer an explicit "Log in with ..." button, on a login page listing several
ways to log in for instance, point it at the ``_oidc_login_start_<firewallname>``
route imported in the previous section, which redirects to the provider. Its
path is ``/oidc/start`` by default, and the ``start_path`` option sets it:

.. code-block:: html+twig

    <a href="{{ path('_oidc_login_start_main') }}">Log in with Example</a>

Never point such a button at ``check_path``, which only handles the provider's
redirect back and rejects a request carrying no authorization code.

.. note::

    The flow keeps its ``state``, its ``nonce`` and its PKCE verifier in the
    session, so the authenticator needs one: it works neither on a stateless
    firewall nor with the session disabled.

4) Load the User
----------------

Once the ID token is validated, the claims of the user are fetched from the
provider's UserInfo endpoint, and the user is loaded by the firewall's user
provider from their ``sub`` claim, with every claim passed along as badge
attributes. Both the source of the claims and the identifier claim can be
changed, as described below in this section.

The built-in ``oidc`` provider shown above builds a self-contained
:class:`Symfony\\Component\\Security\\Core\\User\\OidcUser` from those claims. It
is the quickest way to get started, with two deliberate limits:

* the claims can neither grant roles nor define the identity: every user gets
  ``ROLE_USER``, and a ``roles`` or ``user_identifier`` claim sent by the
  provider is dropped;
* it cannot load a user by identifier alone, so it does not work with
  :doc:`user impersonation </security/impersonating_user>`.

To map claims onto your own roles, or to load users from your own storage, use
your own user provider. A provider implementing
:class:`Symfony\\Component\\Security\\Core\\User\\AttributesBasedUserProviderInterface`
receives the claims as its second argument::

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

        public function loadUserByIdentifier(string $identifier, array $attributes = []): UserInterface
        {
            // $identifier is "sub" by default, $attributes holds every claim
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['oidcSubject' => $identifier]) ?? new User($identifier);

            $user->setEmail($attributes['email'] ?? null);
            $user->setRoles(\in_array('admins', $attributes['groups'] ?? [], true) ? ['ROLE_ADMIN'] : []);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $user;
        }

        // ...
    }

Ask the provider for the claims you need with the ``scope`` option: ``openid``
is always requested, as OIDC requires it, and adding ``profile`` or ``email``
makes the provider return the matching claims.

The ID token and the access token the provider returned are kept as attributes
of the security token, to call the provider's own APIs with::

    $accessToken = $security->getToken()->getAttribute('oidc_access_token');
    $idToken = $security->getToken()->getAttribute('oidc_id_token');

Reading the Claims from the ID Token
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The claims are fetched from the UserInfo endpoint by default. Some providers
put every requested claim in the ID token itself, and some expose no UserInfo
endpoint at all. Set ``user_data_source`` to ``id_token`` to read the claims
from the validated ID token instead, in which case the provider no longer
needs to announce a ``userinfo_endpoint``.

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

The ``sub`` claim of the ID token is required and validated either way, and
when the claims come from the UserInfo endpoint their ``sub`` must match it
(`OIDC Core 1.0, Section 5.3.2`_).

Identifying Users by Another Claim
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The user identifier is the ``sub`` claim, the only one OIDC guarantees stable
and unique for a user. When your users are keyed on another claim, ``email``
for instance, name it in ``user_identifier_claim``. The claim is read from the
same source as the other claims, and the authentication fails when it is
missing, empty or not a string.

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

    Whoever controls the value of that claim at the provider owns the matching
    account in your application, so only pick a claim the provider guarantees
    unique, verified and stable. An email address that users can change
    themselves, or that the provider does not verify, is not one.

The built-in ``oidc`` user provider then builds the user on that identifier,
and a custom provider receives it as the first argument of
``loadUserByIdentifier()``.

Customizing the Authorization Request
-------------------------------------

The authenticator owns the parameters the flow relies on: ``response_type``,
``client_id``, ``redirect_uri``, ``scope``, ``state``, ``nonce``, ``max_age``
and the PKCE ones. Setting any of them by hand is rejected. Every other
parameter your provider accepts goes into ``authorization_params``:

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

``prompt``, ``display``, ``ui_locales``, ``acr_values`` and ``login_hint`` are
the parameters `OIDC Core 1.0, Section 3.1.2.1`_ defines for that request, and
providers accept their own on top of them.

Requiring a Recent Authentication
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

``max_age`` is how old, in seconds, the authentication of the user at the
provider may be for it to be accepted without prompting them again. It has an
option of its own because the authenticator does more than send the parameter:
the ID token must then carry an ``auth_time`` claim, which is checked against
that value, with ``allowed_time_drift`` as the only tolerance.

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

Using PKCE
~~~~~~~~~~

PKCE (`RFC 7636`_) is applied to every authorization request, with the ``S256``
challenge method: the authenticator sends the hash of a random verifier it keeps
in the session, and reveals the verifier itself only when it exchanges the
authorization code, so an intercepted code cannot be exchanged by anyone else.

Any recent provider should support it. Only for one that rejects the
``code_challenge`` parameter, or that supports the ``plain`` method alone, use
the ``pkce`` option:

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

A public client cannot disable PKCE, as nothing else would then tie the
authorization code to it.

Authenticating at the Token Endpoint
------------------------------------

When the authorization code is exchanged for tokens, the application
authenticates at the token endpoint with the method set in
``token_endpoint_auth_method``:

``client_secret_post`` (default)
    The client identifier and secret are sent in the POST request body,
    which mean your applications is registered in and authorized
    by the provider to exchange authorization code for tokens.

``client_secret_basic``
    The client identifier and secret are sent as HTTP Basic credentials, each
    form-urlencoded as `RFC 6749`_ requires.

``none``
    The application is a public client: it holds no secret at all, and PKCE
    alone binds the authorization code to it.

Use the method registered for your application at the provider; providers
announce the ones they accept in the ``token_endpoint_auth_methods_supported``
entry of their discovery document.

A public client is an application that cannot keep a secret confidential, such
as a single-page, a mobile or a native application. Declare it by setting
``token_endpoint_auth_method`` to ``none`` and by leaving ``client_secret`` out
entirely, as setting both is rejected:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    oidc_login:
                        provider_uri: '%env(OIDC_PROVIDER_URI)%'
                        client_id: '%env(OIDC_CLIENT_ID)%'
                        token_endpoint_auth_method: 'none'

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
                            'token_endpoint_auth_method' => 'none',
                        ],
                    ],
                ],
            ],
        ]);

Verifying the ID Token Signature
--------------------------------

The signature of the ID token is verified by default. The signing keys are
fetched from the ``jwks_uri`` the provider announces in its discovery document,
which must use HTTPS like every other endpoint, and they are cached: for the
lifetime the provider advertises for that response, capped at 30 days, or for
``discovery_cache_ttl`` seconds when it advertises none. An ID token signed with
a key missing from the cached set makes the authenticator fetch the keys again,
so a key rotation at the provider needs nothing on your side.

Only ``RS256`` is accepted by default, the single algorithm OIDC providers are
required to support. When your provider signs with another one, list it in the
``algorithms`` option:

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
the ones it uses in the ``id_token_signing_alg_values_supported`` entry of its
discovery document. No HMAC algorithm is accepted, so that a public key
published by the provider can never be turned into a shared secret.

Only the keys the provider explicitly designates for signature are used. Set
``id_token_signature.enforce_key_usage_verification`` to ``false`` to also
accept the keys it publishes without any usage designation; keys explicitly
restricted to encryption are rejected either way. This is the same option as
the one of the :doc:`OIDC access token handler </security/access_token>`.

.. warning::

    Setting ``id_token_signature.required`` to ``false`` decodes the ID token
    without verifying it. `OIDC Core 1.0, Section 3.1.3.7`_ only tolerates that
    because the token comes straight from the token endpoint over TLS, which
    makes the ID token exactly as trustworthy as the TLS verification of the
    HTTP client used for that request: never turn it off with a client
    configured with ``verify_peer: false`` or ``verify_host: false``, nor behind
    a TLS-terminating proxy. A public client cannot turn it off at all.

Logging Out
-----------

Logging out works as usual, see :ref:`security-logging-out`. By default, it ends
the session in your application only: the user stays logged in at the identity
provider, so opening a protected page again logs them straight back in without
a prompt.

Logging Out of the Provider
~~~~~~~~~~~~~~~~~~~~~~~~~~~

To log the user out of the provider itself, enable `RP-Initiated Logout`_
with the ``enable_end_session`` option. The authenticator redirects the user
to the ``end_session_endpoint`` the provider announces in its discovery
document, with the ID token sent as ``id_token_hint``. Once its own session is
closed, the provider sends the user back to ``post_logout_redirect_path``, a
path or route name of your application.

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

Register the full post-logout URL at the provider, exactly as the
``check_path`` URL is registered as a redirect URI. Set
``post_logout_redirect_path`` to ``null`` to send no
``post_logout_redirect_uri`` at all, in which case the provider decides where
the user ends up.

When the provider cannot be reached, or announces no usable (HTTPS)
``end_session_endpoint``, the logout completes in the application alone and
a warning is logged.

.. warning::

    Logging out is now a chain of redirects: to your application, which closes
    its session, then to the provider, which closes its own, then back to your
    application. `Symfony UX Turbo`_ intercepts the click on the logout link
    and follows those redirects itself, which does not work across that chain:
    the session is closed in your application, but the redirect to the provider
    never takes effect, so the user stays logged in there and is logged straight
    back in on the next protected page.

    Make the logout link a real navigation Turbo leaves alone, with
    ``data-turbo="false"``:

    .. code-block:: html+twig

        <a href="{{ path('_logout_main') }}" data-turbo="false">Log out</a>

    A logout form (see :ref:`security-logout-form`) needs the same attribute
    on its ``<form>`` element.

Security Considerations
-----------------------

The authenticator implements the checks the specifications require. Only PKCE
and the ID token signature verification can be turned off, and a public client
can turn off neither:

* ``provider_uri`` must use HTTPS, and so must every endpoint the discovery
  document announces. Loopback hosts (``localhost``, ``127.0.0.1``, ``::1``) and
  the names reserved for testing (``*.localhost``, ``*.test``) are accepted for
  local development, the token endpoint excepted: it carries the authorization
  code, the PKCE verifier and the tokens it is exchanged for, so HTTPS is
  required there even locally;
* the discovered ``issuer`` must match the configured ``provider_uri``;
* every authorization request carries a ``state``, checked on the callback
  against the value stored in the session, which is what stops login CSRF;
* every authorization request carries a ``nonce``, checked against the ID token
  ``nonce`` claim, which binds the token to that very request;
* PKCE is applied with the ``S256`` challenge method, unless the ``pkce``
  option says otherwise, so an intercepted authorization code cannot be
  exchanged by anyone else (`RFC 7636`_);
* the ID token signature is verified against the keys the provider publishes,
  unless ``id_token_signature.required`` is turned off;
* the parameters the flow relies on cannot be overridden through
  ``authorization_params``;
* the ID token ``iss``, ``aud``, ``exp`` and ``iat`` claims are required and
  validated, and its ``azp`` and ``nbf`` claims whenever the provider sends
  them, with ``allowed_time_drift`` as the only tolerance;
* the ``sub`` claim of the ID token is required, and the claims fetched
  from the UserInfo endpoint, when they are the source, must carry the same one;
* the identity is the ``sub`` claim, unless ``user_identifier_claim`` names
  another one, which must then be present and non-empty, and no claim can
  grant a role through the built-in ``oidc`` user provider.

Configuration Reference
-----------------------

``provider_uri`` (**required**)
    The issuer URL of the OIDC provider, used for
    ``.well-known/openid-configuration`` discovery. Must use HTTPS.

``client_id`` (**required**)
    The client identifier issued by the provider.

``client_secret`` (**required**, except for a public client)
    The client secret issued by the provider. It must not be set when
    ``token_endpoint_auth_method`` is ``none``.

``scope`` (default: ``['openid']``)
    The scopes of the authorization request, as a list or as a space-separated
    string.

``user_data_source`` (default: ``userinfo``)
    Where the claims of the user are read from: ``userinfo`` fetches them from
    the UserInfo endpoint, ``id_token`` reads them from the validated ID token.

``user_identifier_claim`` (default: ``sub``)
    The claim the user identifier is read from. ``sub`` is the only claim OIDC
    guarantees stable and unique for a user.

``check_path`` (default: ``/oidc/callback``)
    The firewall path the provider redirects to. A route name is accepted too,
    in which case no route is declared for it.

``start_path`` (default: ``/oidc/start``)
    The path of the route that starts the flow by redirecting to the provider.
    A route name is accepted too, in which case no route is declared for it.

``enable_end_session`` (default: ``false``)
    Whether logging out also redirects the user to the provider's
    ``end_session_endpoint`` (RP-Initiated Logout).

``post_logout_redirect_path`` (default: ``/``)
    The path or route name of your application the provider redirects to after
    RP-Initiated Logout, sent as ``post_logout_redirect_uri``; ``null`` sends
    none. Ignored unless ``enable_end_session`` is ``true``.

``token_endpoint_auth_method`` (default: ``client_secret_post``)
    How the application authenticates at the token endpoint, one of
    ``client_secret_post``, ``client_secret_basic`` or ``none``. The latter
    declares a public client.

``id_token_signature.required`` (default: ``true``)
    Whether the ID token signature is verified against the keys published by
    the provider.

``id_token_signature.algorithms`` (default: ``['RS256']``)
    The signature algorithms the ID token is accepted to be signed with.

``id_token_signature.enforce_key_usage_verification`` (default: ``true``)
    Whether only the keys the provider designates for signature are used to
    verify the ID token.

``pkce.enabled`` (default: ``true``)
    Whether the authorization code is protected with PKCE.

``pkce.method`` (default: ``S256``)
    The PKCE code challenge method, either ``S256`` or ``plain``.

``max_age`` (default: none)
    How old, in seconds, the authentication of the user at the provider may be.
    Sent as the ``max_age`` authorization parameter, and checked against the
    ``auth_time`` claim of the ID token.

``authorization_params`` (default: ``[]``)
    Additional parameters of the authorization request, such as ``prompt`` or
    ``login_hint``.

``discovery_cache_ttl`` (default: ``3600``)
    How long, in seconds, the discovery document is cached, and the provider
    keys when it advertises no lifetime for them.

``allowed_time_drift`` (default: ``0``)
    The clock skew, in seconds, tolerated when validating the ID token time
    claims.

On top of these, the authenticator accepts the options every firewall
authenticator shares: ``success_handler``, ``failure_handler``,
``default_target_path``, ``always_use_default_target_path``,
``target_path_parameter``, ``use_referer``, ``failure_path``,
``failure_forward`` and ``failure_path_parameter``, all described in
:doc:`/security/form_login`.

.. _`OpenID Connect`: https://openid.net/developers/how-connect-works/
.. _`Authorization Code Flow`: https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowAuth
.. _`web-token/jwt-library`: https://github.com/web-token/jwt-library
.. _`OIDC Core 1.0, Section 3.1.2.1`: https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
.. _`RFC 6749`: https://datatracker.ietf.org/doc/html/rfc6749#section-2.3.1
.. _`RFC 7636`: https://datatracker.ietf.org/doc/html/rfc7636
.. _`OIDC Core 1.0, Section 3.1.3.7`: https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
.. _`OIDC Core 1.0, Section 5.3.2`: https://openid.net/specs/openid-connect-core-1_0.html#UserInfoResponse
.. _`RP-Initiated Logout`: https://openid.net/specs/openid-connect-rpinitiated-1_0.html
.. _`Symfony UX Turbo`: https://symfony.com/bundles/ux-turbo/current/index.html
