How to use Access Token Authentication
======================================

Access tokens or API tokens are commonly used as authentication mechanism
in API contexts. The access token is a string, obtained during authentication
(using the application or an authorization server). The access token's role
is to verify the user identity and receive consent before the token is
issued.

Access tokens can be of any kind, for instance opaque strings,
`JSON Web Tokens (JWT)`_ or `SAML2 (XML structures)`_. Please refer to the
`RFC6750`_: *The OAuth 2.0 Authorization Framework: Bearer Token Usage* for
a detailed specification.

Using the Access Token Authenticator
------------------------------------

This guide assumes you have set up security and have created a user object
in your application. Follow :doc:`the main security guide </security>` if
this is not yet the case.

1) Configure the Access Token Authenticator
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To use the access token authenticator, you must configure a ``token_handler``.
The token handler receives the token from the request and returns the
correct user identifier. To get the user identifier, implementations may
need to load and validate the token (e.g. revocation, expiration time,
digital signature, etc.).

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler: App\Security\AccessTokenHandler

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\AccessTokenHandler;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => AccessTokenHandler::class,
                        ],
                    ],
                ],
            ],
        ]);

This handler must implement
:class:`Symfony\\Component\\Security\\Http\\AccessToken\\AccessTokenHandlerInterface`::

    // src/Security/AccessTokenHandler.php
    namespace App\Security;

    use App\Repository\AccessTokenRepository;
    use Symfony\Component\Security\Core\Exception\BadCredentialsException;
    use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

    class AccessTokenHandler implements AccessTokenHandlerInterface
    {
        public function __construct(
            private AccessTokenRepository $repository
        ) {
        }

        public function getUserBadgeFrom(string $accessToken): UserBadge
        {
            // e.g. query the "access token" database to search for this token
            $accessToken = $this->repository->findOneByValue($accessToken);
            if (null === $accessToken || !$accessToken->isValid()) {
                throw new BadCredentialsException('Invalid credentials.');
            }

            // and return a UserBadge object containing the user identifier from the found token
            // (this is the same identifier used in Security configuration; it can be an email,
            // a UUID, a username, a database ID, etc.)
            return new UserBadge($accessToken->getUserId());
        }
    }

The access token authenticator will use the returned user identifier to
load the user using the :ref:`user provider <security-user-providers>`.

.. warning::

    It is important to check whether the token is valid. For instance, the
    example above verifies whether the token has not expired. With
    self-contained access tokens such as JWT, the handler is required to
    verify the digital signature and understand all claims, especially
    ``sub``, ``iat``, ``nbf`` and ``exp``.

2) Configure the Token Extractor (Optional)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The application is now ready to handle incoming tokens. A *token extractor*
retrieves the token from the request (e.g. a header or request body).

By default, the access token is read from the request header parameter
``Authorization`` with the scheme ``Bearer`` (e.g. ``Authorization: Bearer the-token-value``).

Symfony provides other extractors as per the `RFC6750`_:

``header`` (default)
    The token is sent through the request header. Usually ``Authorization``
    with the ``Bearer`` scheme.
``query_string``
    The token is part of the request query string. Usually ``access_token``.
``request_body``
    The token is part of the request body during a POST request. Usually
    ``access_token``.

.. warning::

    Because of the security weaknesses associated with the URI method,
    including the high likelihood that the URL or the request body
    containing the access token will be logged, methods ``query_string``
    and ``request_body`` **SHOULD NOT** be used unless it is impossible to
    transport the access token in the request header field.

You can also create a custom extractor. The class must implement
:class:`Symfony\\Component\\Security\\Http\\AccessToken\\AccessTokenExtractorInterface`.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler: App\Security\AccessTokenHandler

                        # use a different built-in extractor
                        token_extractors: request_body

                        # or provide the service ID of a custom extractor
                        token_extractors: 'App\Security\CustomTokenExtractor'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\AccessTokenHandler;
        use App\Security\CustomTokenExtractor;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => AccessTokenHandler::class,

                            // use a different built-in extractor
                            'token_extractors' => 'request_body',

                            // or provide the service ID of a custom extractor
                            'token_extractors' => CustomTokenExtractor::class,
                        ],
                    ],
                ],
            ],
        ]);

It is possible to set multiple extractors. In this case, **the order is
important**: the first in the list is called first.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler: App\Security\AccessTokenHandler
                        token_extractors:
                            - 'header'
                            - 'App\Security\CustomTokenExtractor'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\AccessTokenHandler;
        use App\Security\CustomTokenExtractor;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => AccessTokenHandler::class,
                            'token_extractors' => [
                                'header',
                                CustomTokenExtractor::class,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

3) Submit a Request
~~~~~~~~~~~~~~~~~~~

That's it! Your application can now authenticate incoming requests using an
API token.

Using the default header extractor, you can test the feature by submitting
a request like this:

.. code-block:: terminal

    $ curl -H 'Authorization: Bearer an-accepted-token-value' \
        https://localhost:8000/api/some-route

Customizing the Success Handler
-------------------------------

By default, the request continues (e.g. the controller for the route is
run). If you want to customize success handling, create your own success
handler by creating a class that implements
:class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationSuccessHandlerInterface`
and configure the service ID as the ``success_handler``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler: App\Security\AccessTokenHandler
                        success_handler: App\Security\Authentication\AuthenticationSuccessHandler

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\AccessTokenHandler;
        use App\Security\Authentication\AuthenticationSuccessHandler;

        return App::config([
            'security' => [
            'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => AccessTokenHandler::class,
                            'success_handler' => AuthenticationSuccessHandler::class,
                        ],
                    ],
                ],
            ],
        ]);

.. tip::

    If you want to customize the default failure handling, use the
    ``failure_handler`` option and create a class that implements
    :class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationFailureHandlerInterface`.

Serving the Protected Resource Metadata
---------------------------------------

A client holding no access token has to find out which authorization
servers issue the tokens your API accepts. `RFC 9728`_ answers that with a
JSON document the resource server publishes at
``/.well-known/oauth-protected-resource``. The ``401`` responses of the
firewall point at that document.

Declaring the ``resource_metadata`` option of the ``access_token``
authenticator serves that document and advertises its URL:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                api:
                    access_token:
                        realm: 'My API'
                        token_extractors: ['header', 'query_string']
                        token_handler:
                            oidc: ~
                        resource_metadata:
                            authorization_servers: ['https://accounts.example.com']
                            scopes_supported: ['profile', 'email']
                            resource_name: 'My API'
                            resource_documentation: 'https://api.example.com/docs'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'api' => [
                        'access_token' => [
                            'realm' => 'My API',
                            'token_extractors' => ['header', 'query_string'],
                            'token_handler' => [
                                'oidc' => null,
                            ],
                            'resource_metadata' => [
                                'authorization_servers' => ['https://accounts.example.com'],
                                'scopes_supported' => ['profile', 'email'],
                                'resource_name' => 'My API',
                                'resource_documentation' => 'https://api.example.com/docs',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

``GET /.well-known/oauth-protected-resource`` then answers:

.. code-block:: json

    {
        "resource": "https://api.example.com",
        "authorization_servers": ["https://accounts.example.com"],
        "scopes_supported": ["profile", "email"],
        "bearer_methods_supported": ["header", "query"],
        "resource_name": "My API",
        "resource_documentation": "https://api.example.com/docs"
    }

A request carrying no token at all gets the challenge that starts the
discovery, provided the firewall declares no other entry point:

.. code-block:: text

    WWW-Authenticate: Bearer realm="My API",resource_metadata="https://api.example.com/.well-known/oauth-protected-resource"

and a request carrying a token the firewall rejects gets the same pointer,
next to the reason:

.. code-block:: text

    WWW-Authenticate: Bearer realm="My API",error="invalid_token",error_description="Invalid credentials.",resource_metadata="https://api.example.com/.well-known/oauth-protected-resource"

These are the available options, each of them a metadata parameter of
`RFC 9728`_ and each of them omitted from the document when it has no
value. Only ``resource`` is always there, falling back to the origin the
document is served from:

``resource``
    Resource identifier of the firewall, as an HTTPS URL without a
    fragment. A loopback host (``localhost``, ``127.0.0.1``, ``::1``) and a
    name reserved for testing (``*.localhost``, ``*.test``) are accepted
    over HTTP, for local development. It defaults to the origin the
    document is served from, which is what a firewall covering a whole
    application wants. When it carries a path, that path is inserted after
    the well-known one, as Section 3.1 of the RFC prescribes:
    ``https://example.com/api`` is served at
    ``/.well-known/oauth-protected-resource/api``, so a single host can
    serve several protected resources. Give each firewall its own path
    component then, as two firewalls sharing one path is an error. That
    path is read when the route is declared, so an environment variable
    cannot hold the whole value; interpolate it into the URL instead, as
    in ``https://%env(API_HOST)%/v1``.

``authorization_servers``
    Issuer identifiers of the authorization servers issuing the access
    tokens this firewall accepts (e.g. ``https://accounts.example.com``).
    This is what tells a client holding no token where to get one.

``jwks_uri``
    URL of the JWK Set holding the keys this resource signs its own
    responses with. These are unrelated to the keys the access tokens are
    verified against, which belong to the authorization server.

``scopes_supported``
    Scope values this resource uses.

``bearer_methods_supported``
    The ways a client can send the bearer token to this resource, among
    ``header``, ``body`` and ``query``. Symfony deduces them from the
    ``token_extractors`` option when you leave this one empty, mapping
    ``header`` to ``header``, ``request_body`` to ``body`` and
    ``query_string`` to ``query``. Set it explicitly when a custom
    extractor makes that deduction incomplete.

``resource_name``
    Human-readable name of this resource, meant to be displayed to the end
    user.

``resource_documentation``
    URL of the developer documentation of this resource.

``resource_policy_uri``
    URL of the policy telling how the client may use the data this
    resource exposes.

``resource_tos_uri``
    URL of the terms of service of this resource.

A route loader declares the route serving the document. Import it in your
routes:

.. configuration-block::

    .. code-block:: yaml

        # config/routes/security.yaml
        _oauth_protected_resource_metadata:
            resource: security.authenticator.access_token.route_loader
            type: service

    .. code-block:: php

        // config/routes/security.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return Routes::config([
            '_oauth_protected_resource_metadata' => [
                'resource' => 'security.authenticator.access_token.route_loader',
                'type' => 'service',
            ],
        ]);

Keeping that path reachable without a token is up to your application: no
:ref:`access control rule <security-authorization-access-control>` and no
firewall pattern must require one on it, as for any other public path.

.. versionadded:: 8.2

    The ``resource_metadata`` option was introduced in Symfony 8.2.

Using OpenID Connect (OIDC)
---------------------------

`OpenID Connect (OIDC)`_ is the third generation of OpenID technology and it's a
RESTful HTTP API that uses JSON as its data format. OpenID Connect is an
authentication layer on top of the OAuth 2.0 authorization framework. It allows
you to verify the identity of an end user based on the authentication performed by
an authorization server.

1) Configure the OidcUserInfoTokenHandler
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``OidcUserInfoTokenHandler`` requires the ``symfony/http-client`` package to
make the needed HTTP requests. If you haven't installed it yet, run this command:

.. code-block:: terminal

    $ composer require symfony/http-client

Symfony provides a generic ``OidcUserInfoTokenHandler`` to call your OIDC server
and retrieve the user info:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc_user_info: https://www.example.com/realms/demo/protocol/openid-connect/userinfo

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oidc_user_info' => 'https://www.example.com/realms/demo/protocol/openid-connect/userinfo',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

To enable `OpenID Connect Discovery`_, the ``OidcUserInfoTokenHandler``
requires the ``symfony/cache`` package to store the OIDC configuration in
the cache. If you haven't installed it yet, run the following command:

.. code-block:: terminal

    $ composer require symfony/cache

Next, configure the ``base_uri`` and ``discovery`` options:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc_user_info:
                                base_uri: https://www.example.com/realms/demo/
                                discovery:
                                    cache:
                                        id: cache.app

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
            'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oidc_user_info' => [
                                    'base_uri' => 'https://www.example.com/realms/demo/',
                                    'discovery' => [
                                        'cache' => [
                                            'id' => 'cache.app',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

Following the `OpenID Connect Specification`_, the ``sub`` claim is used as user
identifier by default. To use another claim, specify it using the ``claim`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc_user_info:
                                claim: email
                                base_uri: https://www.example.com/realms/demo/protocol/openid-connect/userinfo

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oidc_user_info' => [
                                    'claim' => 'email',
                                    'base_uri' => 'https://www.example.com/realms/demo/protocol/openid-connect/userinfo',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

The ``oidc_user_info`` token handler automatically creates an HTTP client with
the specified ``base_uri``. If you prefer using your own client, you can
specify the service name via the ``client`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc_user_info:
                                client: oidc.client

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oidc_user_info' => [
                                    'client' => 'oidc.client',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

By default, the ``OidcUserInfoTokenHandler`` creates an ``OidcUser`` with the
claims. To create your own user object from the claims, you must
:doc:`create your own UserProvider </security/user_providers>`::

    // src/Security/Core/User/OidcUserProvider.php
    use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;

    class OidcUserProvider implements AttributesBasedUserProviderInterface
    {
        public function loadUserByIdentifier(string $identifier, array $attributes = []): UserInterface
        {
            // implement your own logic to load and return the user object
        }
    }

2) Configure the OidcTokenHandler
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``OidcTokenHandler`` requires the ``web-token/jwt-library`` package.
If you haven't installed it yet, run this command:

.. code-block:: terminal

    $ composer require web-token/jwt-library

.. warning::

    For production use, ensure the `GMP PHP extension`_ is installed. The
    ``web-token/jwt-library`` depends on ``brick/math``, which silently falls
    back to a pure PHP implementation when neither the GMP nor BCMath extensions
    are available. This can make JWT verification **orders of magnitude slower**.

Symfony provides a generic ``OidcTokenHandler`` that decodes the token, validates
it, and retrieves the user information from it. Optionally, the token can be encrypted (JWE):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc:
                                # Algorithms used to sign the JWS
                                algorithms: ['ES256', 'RS256']
                                # A JSON-encoded JWK
                                keyset: '{"keys":[{"kty":"...","k":"..."}]}'
                                # Audience (`aud` claim): required for validation purpose
                                audience: 'api-example'
                                # Issuers (`iss` claim): required for validation purpose
                                issuers: ['https://oidc.example.com']
                                # Tolerance in seconds for clock differences between the token
                                # issuer and this application, applied when validating the
                                # time-based claims (`iat`, `nbf`, `exp`)
                                allowed_time_drift: 5 # Default to 0 (no tolerance)
                                encryption:
                                    enabled: true # Default to false
                                    enforce: false # Default to false, requires an encrypted token when true
                                    algorithms: ['ECDH-ES', 'A128GCM']
                                    keyset: '{"keys": [...]}' # Encryption private keyset

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oidc' => [
                                    // Algorithms used to sign the JWS
                                    'algorithms' => ['ES256', 'RS256'],
                                    // A JSON-encoded JWK
                                    'keyset' => '{"keys":[{"kty":"...","k":"..."}]}',
                                    // Audience (`aud` claim): required for validation purpose
                                    'audience' => 'api-example',
                                    // Issuers (`iss` claim): required for validation purpose
                                    'issuers' => ['https://oidc.example.com'],
                                    // Tolerance in seconds for clock differences between the tokens
                                    // issuer and this application, applied when validating the
                                    // time-based claims (`iat`, `nbf`, `exp`)
                                    'allowed_time_drift' => 5, // Default to 0 (no tolerance)
                                    // Encryption:
                                    'encryption' => [
                                        'enabled' => true, // Default to false
                                        'enforce' => false, // Default to false, requires an encrypted token when true
                                        'algorithms' => ['ECDH-ES', 'A128GCM'],
                                        'keyset' => '{"keys": [...]}' // Encryption private keyset
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

.. versionadded:: 8.2

    The ``allowed_time_drift`` option was introduced in Symfony 8.2.

To enable `OpenID Connect Discovery`_, the ``OidcTokenHandler`` requires the
``symfony/cache`` package to store the OIDC configuration in the cache. If you
haven't installed it yet, run the following command:

.. code-block:: terminal

    $ composer require symfony/cache

Then, you can remove the ``keyset`` configuration option (it will be imported
from the OpenID Connect Discovery), and configure the ``discovery`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc:
                                claim: email
                                algorithms: ['ES256', 'RS256']
                                audience: 'api-example'
                                issuers: ['https://oidc.example.com']
                                discovery:
                                    base_uri: https://www.example.com/realms/demo/
                                    cache:
                                        id: cache.app

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oidc' => [
                                    'claim' => 'email',
                                    'algorithms' => ['ES256', 'RS256'],
                                    'audience' => 'api-example',
                                    'issuers' => ['https://oidc.example.com'],
                                    'discovery' => [
                                        'base_uri' => 'https://www.example.com/realms/demo/',
                                        'cache' => [
                                            'id' => 'cache.app',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

By default, when using OpenID Connect Discovery, only keys explicitly designated
for signature verification (i.e. keys with ``"use": "sig"`` or ``"key_ops"``
containing ``"sign"`` or ``"verify"`` per `RFC 7517`_) are accepted. If your
identity provider serves keys without any usage designation (no ``use`` or
``key_ops`` field), you can disable this strict filtering by setting the
``enforce_key_usage_verification`` option to ``false``:

.. configuration-block::

    .. code-block:: yaml

        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc:
                                # ...
                                discovery:
                                    base_uri: https://www.example.com/realms/demo/
                                    cache:
                                        id: cache.app
                                    enforce_key_usage_verification: false

    .. code-block:: php

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oidc' => [
                                    // ...
                                    'discovery' => [
                                        'base_uri' => 'https://www.example.com/realms/demo/',
                                        'cache' => [
                                            'id' => 'cache.app',
                                        ],
                                        'enforce_key_usage_verification' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

When disabled, keys are still filtered: those explicitly marked for encryption
only (``"use": "enc"`` or ``"key_ops"`` containing only encryption operations)
are excluded. Keys without any usage designation are included.

.. versionadded:: 8.1

    The ``enforce_key_usage_verification`` option was introduced in Symfony 8.1.

Following the `OpenID Connect Specification`_, the ``sub`` claim is used by
default as user identifier. To use another claim, specify it on the
configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc:
                                claim: email
                                algorithms: ['ES256', 'RS256']
                                keyset: '{"keys":[{"kty":"...","k":"..."}]}'
                                audience: 'api-example'
                                issuers: ['https://oidc.example.com']

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oidc' => [
                                    'claim' => 'email',
                                    'algorithms' => ['ES256', 'RS256'],
                                    'keyset' => '{"keys":[{"kty":"...","k":"..."}]}',
                                    'audience' => 'api-example',
                                    'issuers' => ['https://oidc.example.com'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

By default, the ``OidcTokenHandler`` creates an ``OidcUser`` with the claims. To
create your own User from the claims, you must
:doc:`create your own UserProvider </security/user_providers>`::

    // src/Security/Core/User/OidcUserProvider.php
    use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;

    class OidcUserProvider implements AttributesBasedUserProviderInterface
    {
        public function loadUserByIdentifier(string $identifier, array $attributes = []): UserInterface
        {
            // implement your own logic to load and return the user object
        }
    }

Configuring Multiple OIDC Discovery Endpoints
.............................................

The ``OidcTokenHandler`` supports multiple OIDC discovery endpoints, allowing it
to validate tokens from different identity providers:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc:
                                algorithms: ['ES256', 'RS256']
                                audience: 'api-example'
                                issuers: ['https://oidc1.example.com', 'https://oidc2.example.com']
                                discovery:
                                    base_uri:
                                        - https://idp1.example.com/realms/demo/
                                        - https://idp2.example.com/realms/demo/
                                    cache:
                                        id: cache.app

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oidc' => [
                                    'algorithms' => ['ES256', 'RS256'],
                                    'audience' => 'api-example',
                                    'issuers' => ['https://oidc1.example.com', 'https://oidc2.example.com'],
                                    'discovery' => [
                                        'base_uri' => [
                                            'https://idp1.example.com/realms/demo/',
                                            'https://idp2.example.com/realms/demo/',
                                        ],
                                        'cache' => [
                                            'id' => 'cache.app',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

The token handler fetches the JWK sets from all configured discovery endpoints
and builds a combined JWK set for token validation. This lets your application
accept and validate tokens from multiple identity providers within a single firewall.

.. _creating-a-oidc-token-from-the-command-line:

Creating an OIDC token from the command line
--------------------------------------------

The ``security:oidc:generate-token`` command helps you generate JWTs. It's mostly
useful when developing or testing applications that use OIDC authentication:

.. code-block:: terminal

    # generate a token using the default configuration
    $ php bin/console security:oidc:generate-token john.doe@example.com

    # specify the firewall, algorithm, and issuer if multiple are available
    $ php bin/console security:oidc:generate-token john.doe@example.com \
        --firewall="api" \
        --algorithm="HS256" \
        --issuer="https://example.com"

.. note::

    The JWK used for signing must have the appropriate `key operation flags`_ set.

Using CAS 2.0
-------------

`Central Authentication Service (CAS)`_ is an enterprise multilingual single
sign-on solution and identity provider for the web and attempts to be a
comprehensive platform for your authentication and authorization needs.

Configure the Cas2Handler
~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony provides a generic ``Cas2Handler`` to call your CAS server. It requires
the ``symfony/http-client`` package to make the needed HTTP requests. If you
haven't installed it yet, run this command:

.. code-block:: terminal

    $ composer require symfony/http-client

You can configure a ``cas`` token handler as follows:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            cas:
                                validation_url: https://www.example.com/cas/validate

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'cas' => [
                                    'validation_url' => 'https://www.example.com/cas/validate',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

The ``cas`` token handler automatically creates an HTTP client to call
the specified ``validation_url``. If you prefer using your own client, you can
specify the service name via the ``http_client`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            cas:
                                validation_url: https://www.example.com/cas/validate
                                http_client: cas.client

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
            'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'cas' => [
                                    'validation_url' => 'https://www.example.com/cas/validate',
                                    'http_client' => 'cas.client',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

By default the token handler will read the validation URL XML response with a
``cas`` prefix but you can configure another prefix:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            cas:
                                validation_url: https://www.example.com/cas/validate
                                prefix: cas-example

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'cas' => [
                                    'validation_url' => 'https://www.example.com/cas/validate',
                                    'prefix' => 'cas-example',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

Creating Users from Token
-------------------------

Some types of tokens (for instance OIDC) contain all information required
to create a user entity (e.g. username and roles). In this case, you don't
need a user provider to create a user from the database::

    // src/Security/AccessTokenHandler.php
    namespace App\Security;

    // ...
    class AccessTokenHandler implements AccessTokenHandlerInterface
    {
        // ...

        public function getUserBadgeFrom(string $accessToken): UserBadge
        {
            // get the data from the token
            $payload = ...;

            return new UserBadge(
                $payload->getUserId(),
                fn (string $userIdentifier) => new User($userIdentifier, $payload->getRoles())
            );
        }
    }

When using this strategy, you can omit the ``user_provider`` configuration
for :ref:`stateless firewalls <reference-security-stateless>`.

.. _`Central Authentication Service (CAS)`: https://en.wikipedia.org/wiki/Central_Authentication_Service
.. _`GMP PHP extension`: https://www.php.net/manual/en/book.gmp.php
.. _`JSON Web Tokens (JWT)`: https://datatracker.ietf.org/doc/html/rfc7519
.. _`OpenID Connect (OIDC)`: https://en.wikipedia.org/wiki/OpenID#OpenID_Connect_(OIDC)
.. _`OpenID Connect Specification`: https://openid.net/specs/openid-connect-core-1_0.html
.. _`OpenID Connect Discovery`: https://openid.net/specs/openid-connect-discovery-1_0.html
.. _`RFC 7517`: https://datatracker.ietf.org/doc/html/rfc7517
.. _`RFC 9728`: https://datatracker.ietf.org/doc/html/rfc9728
.. _`RFC6750`: https://datatracker.ietf.org/doc/html/rfc6750
.. _`SAML2 (XML structures)`: https://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-tech-overview-2.0.html
.. _`key operation flags`: https://www.iana.org/assignments/jose/jose.xhtml#web-key-operations
