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

The authenticator validates the ID token with the `web-token/jwt-library`_
package, which you must install first:

.. code-block:: terminal

    $ composer require web-token/jwt-library

1) Configure the Authenticator
------------------------------

Enable ``oidc_login`` under the firewall. Three options are required: the
issuer URL of your provider, and the credentials it issued to your application:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            providers:
                oidc:
                    oidc: ~

            firewalls:
                main:
                    provider: oidc
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
                    'oidc' => [
                        'oidc' => null,
                    ],
                ],

                'firewalls' => [
                    'main' => [
                        'provider' => 'oidc',
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
tokens.

``check_path`` is the path the provider redirects to. It must match one of the
redirect URIs registered with the provider. Register the full URL there
(e.g. ``https://example.com/oidc/callback``), not just the path.

2) Import the Callback Route
----------------------------

The callback path needs a route, otherwise the router answers the provider's
redirect with a 404 before the firewall ever sees it. Symfony declares that
route for you through a route loader, which your application imports, exactly
as it does for the logout routes:

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

    If you need to reference the callback path, you can use the
    ``_oidc_login_callback_<firewallname>`` route name (e.g.
    ``_oidc_login_callback_main``).

If ``check_path`` holds a route name instead of a path, no route is declared for
it and you must define that route yourself.

3) Start the Flow
-----------------

The authenticator is also the firewall entry point: an anonymous request to a
protected page is redirected to the provider's authorization endpoint. Nothing
else is needed to log a user in.

To offer an explicit "Log in with ..." button, point it at any protected path:
reaching that path triggers the entry point, which starts the flow. Do not point
it at ``check_path``, which only handles the provider's redirect back and
rejects a request carrying no authorization code.

4) Load the User
----------------

Once the ID token is validated, the user is loaded by the firewall's user
provider, from the verified ``sub`` claim, with every claim passed along as
badge attributes.

The built-in ``oidc`` provider shown above builds a self-contained
:class:`Symfony\\Component\\Security\\Core\\User\\OidcUser` from those claims. It
is the quickest way to get started, with two deliberate limits:

* the claims cannot grant roles: every user gets ``ROLE_USER``, and a ``roles``
  claim sent by the provider is dropped;
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
            // $identifier is the verified "sub" claim, $attributes holds every claim
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
makes the provider return the matching claims from its UserInfo endpoint.

Logging Out
-----------

Logging out works as usual, see :ref:`security-logging-out`. It ends the session
in your application only: the user stays logged in at the identity provider, so
opening a protected page again logs them straight back in without a prompt.

Security Considerations
-----------------------

The authenticator implements the checks the specifications require, without any
option to weaken them:

* ``provider_uri`` must use HTTPS, and so must every endpoint the discovery
  document announces. Loopback hosts (``localhost``, ``127.0.0.1``, ``::1``) and
  the names reserved for testing (``*.localhost``, ``*.test``) are accepted for
  local development;
* the discovered ``issuer`` must match the configured ``provider_uri``;
* every authorization request carries a ``state``, checked on the callback
  against the value stored in the session, which is what stops login CSRF;
* every authorization request carries a ``nonce``, checked against the ID token
  ``nonce`` claim, which binds the token to that very request;
* PKCE is always applied, with the ``S256`` challenge method, so an intercepted
  authorization code cannot be exchanged by anyone else (`RFC 7636`_);
* the ID token ``iss``, ``aud``, ``azp``, ``exp``, ``iat`` and ``nbf`` claims are
  validated, with ``allowed_time_drift`` as the only tolerance;
* the claims fetched from the UserInfo endpoint must carry the same ``sub`` as
  the ID token;
* the identity is always the verified ``sub`` claim, and no claim can grant a
  role through the built-in ``oidc`` user provider.

.. warning::

    The ID token signature is not verified in Symfony 8.2: the token is accepted
    because it comes straight from the token endpoint over TLS, which
    `OIDC Core 1.0, Section 3.1.3.7`_ allows. This holds only as long as the HTTP
    client used for that request verifies the TLS certificate, so never configure
    it with ``verify_peer: false`` or ``verify_host: false``, and do not put a
    TLS-terminating proxy in front of the token endpoint.

Configuration Reference
-----------------------

``provider_uri`` (**required**)
    The issuer URL of the OIDC provider, used for
    ``.well-known/openid-configuration`` discovery. Must use HTTPS.

``client_id`` (**required**)
    The client identifier issued by the provider.

``client_secret`` (**required**)
    The client secret issued by the provider.

``scope`` (default: ``['openid']``)
    The scopes of the authorization request, as a list or as a space-separated
    string.

``check_path`` (default: ``/oidc/callback``)
    The firewall path the provider redirects to. A route name is accepted too,
    in which case no route is declared for it.

``discovery_cache_ttl`` (default: ``3600``)
    How long, in seconds, the discovery document is cached.

``allowed_time_drift`` (default: ``0``)
    The clock skew, in seconds, tolerated when validating the ID token time
    claims.

.. _`OpenID Connect`: https://openid.net/developers/how-connect-works/
.. _`Authorization Code Flow`: https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowAuth
.. _`web-token/jwt-library`: https://github.com/web-token/jwt-library
.. _`RFC 7636`: https://datatracker.ietf.org/doc/html/rfc7636
.. _`OIDC Core 1.0, Section 3.1.3.7`: https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
