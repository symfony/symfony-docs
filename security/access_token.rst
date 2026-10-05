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

.. _access-token-resource-metadata:

Publishing the Protected Resource Metadata
------------------------------------------

.. versionadded:: 8.2

    The ``resource_metadata`` option was introduced in Symfony 8.2.

Clients that don't have an access token yet (e.g. MCP clients) must find out
which authorization servers issue the tokens accepted by your API. `RFC 9728`_
solves this with a JSON document that the API publishes at
``/.well-known/oauth-protected-resource`` and with a ``resource_metadata``
parameter in the ``WWW-Authenticate`` header of ``401`` responses, which points
to that document.

Add the ``resource_metadata`` option to the ``access_token`` authenticator to
publish this document and to add its URL to the ``401`` responses:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                api:
                    access_token:
                        realm: 'My API'
                        token_extractors: ['header', 'query_string']
                        token_handler: App\Security\AccessTokenHandler
                        resource_metadata:
                            authorization_servers: ['https://accounts.example.com']
                            scopes_supported: ['profile', 'email']
                            resource_name: 'My API'
                            resource_documentation: 'https://api.example.com/docs'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\AccessTokenHandler;

        return App::config([
            'security' => [
                'firewalls' => [
                    'api' => [
                        'access_token' => [
                            'realm' => 'My API',
                            'token_extractors' => ['header', 'query_string'],
                            'token_handler' => AccessTokenHandler::class,
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

Then, import the route loader that defines the route of this document:

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

Clients must be able to get this document without a token, so don't add any
:ref:`access control rule <security-authorization-access-control>` that
requires authentication for its path.

If your API runs at ``https://api.example.com``, a
``GET /.well-known/oauth-protected-resource`` request now returns:

.. code-block:: json

    {
        "resource": "https://api.example.com",
        "authorization_servers": ["https://accounts.example.com"],
        "scopes_supported": ["profile", "email"],
        "bearer_methods_supported": ["header", "query"],
        "resource_name": "My API",
        "resource_documentation": "https://api.example.com/docs"
    }

Requests without a token get a ``401`` response with the following header,
unless another authenticator of the firewall (e.g. ``form_login``) is its
:ref:`entry point <security-entry-point>`:

.. code-block:: text

    WWW-Authenticate: Bearer realm="My API",resource_metadata="https://api.example.com/.well-known/oauth-protected-resource"

Requests with a token rejected by the firewall get the same URL next to the
error details:

.. code-block:: text

    WWW-Authenticate: Bearer realm="My API",error="invalid_token",error_description="Invalid credentials.",resource_metadata="https://api.example.com/.well-known/oauth-protected-resource"

These are the options of ``resource_metadata``. Each of them is a metadata
parameter defined by `RFC 9728`_ and it's left out of the document when it
has no value:

``resource``
    The identifier of the protected resource. It must be an HTTPS URL without
    a fragment, but HTTP is allowed for loopback hosts (``localhost``,
    ``127.0.0.1``, ``::1``) and hostnames reserved for testing
    (``*.localhost``, ``*.test``). By default, it's the origin (scheme, host
    and port) of the request, which is correct when the firewall protects the
    whole application.

    If the URL includes a path, that path is added after the well-known path,
    as defined in Section 3.1 of the RFC. For example, the metadata of
    ``https://example.com/api`` is served at
    ``/.well-known/oauth-protected-resource/api``. This allows several
    firewalls of the same host to publish their own metadata, as long as each
    of them uses a different path.

    Symfony reads this path when compiling the container, so you can't use an
    environment variable as the whole value. Use it inside the URL instead
    (e.g. ``https://%env(API_HOST)%/v1``).

``authorization_servers``
    The issuer identifiers of the authorization servers that issue the access
    tokens accepted by this firewall (e.g. ``https://accounts.example.com``).
    Clients use them to know where to get a token.

``jwks_uri``
    The URL of the JWK Set with the keys that your API uses to sign its own
    responses. These are not the keys used to verify the access tokens, which
    belong to the authorization server.

``scopes_supported``
    The scope values used by your API.

``bearer_methods_supported``
    The ways clients can send the token to your API (``header``, ``body`` or
    ``query``). If you don't set this option, Symfony computes it from the
    ``token_extractors`` option (``header`` becomes ``header``,
    ``request_body`` becomes ``body`` and ``query_string`` becomes
    ``query``). Custom extractors are ignored, so set this option explicitly
    when using them.

``resource_name``
    The human-readable name of your API, which clients can display to end
    users.

``resource_documentation``
    The URL of the developer documentation of your API.

``resource_policy_uri``
    The URL of the policy that explains how clients can use the data returned
    by your API.

``resource_tos_uri``
    The URL of the terms of service of your API.

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
                                # Requires the `typ` header of the token to be
                                # `at+jwt` or `application/at+jwt` (RFC 9068)
                                enforce_at_jwt_type: true # Default to false
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
                                    // Requires the `typ` header of the token to be
                                    // `at+jwt` or `application/at+jwt` (RFC 9068)
                                    'enforce_at_jwt_type' => true, // Default to false
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

    The ``allowed_time_drift`` and ``enforce_at_jwt_type`` options were
    introduced in Symfony 8.2.

`RFC 9068`_ requires JWT access tokens to define a ``typ`` header with the
``at+jwt`` or ``application/at+jwt`` value. When ``enforce_at_jwt_type`` is
``true``, the handler rejects tokens without that header or with any other
value (the comparison is case-insensitive). For encrypted tokens, the handler
checks the header of the signed token after decrypting it.

This check prevents "cross-JWT confusion" attacks. If your API and your login
application use the same client on the OpenID Connect provider, the ID tokens
are signed by an allowed issuer and include your ``audience`` in their ``aud``
claim. Without this check, the handler accepts those ID tokens as access tokens.

The option is ``false`` by default to keep compatibility with providers that
don't follow RFC 9068 and issue tokens with a ``JWT`` type. Its default value
will change to ``true`` in the next major version, so set it explicitly. The
tokens generated from the
:ref:`command line <creating-a-oidc-token-from-the-command-line>` use the
``at+jwt`` type, so they pass this check.

.. deprecated:: 8.2

    Not setting the ``enforce_at_jwt_type`` option was deprecated in Symfony 8.2.

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
                                # each "issuer" announced by the discovery documents
                                issuers:
                                    - https://idp1.example.com/realms/demo
                                    - https://idp2.example.com/realms/demo
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
                                    // each "issuer" announced by the discovery documents
                                    'issuers' => [
                                        'https://idp1.example.com/realms/demo',
                                        'https://idp2.example.com/realms/demo',
                                    ],
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

The token handler fetches the JWK set of each discovery endpoint and binds its
keys to the issuer announced by that discovery document, so each token is only
verified with the keys of its own issuer (the ``iss`` claim). That's why every
announced issuer must be listed in the ``issuers`` option exactly as announced
(including any trailing slash) and two discovery documents can't announce the
same issuer.

Checking the Issuer of Discovery Documents
..........................................

.. versionadded:: 8.2

    The ``check_issuer`` option was introduced in Symfony 8.2.

Each discovery document announces the issuer of its identity provider. Enable
the ``check_issuer`` option to require each discovery document to announce its
own base URI as the issuer (OpenID Connect Discovery builds the discovery URL by
appending ``/.well-known/openid-configuration`` to the issuer). If a document
announces another issuer, authentication fails. The keys of each document then
only validate the tokens of that issuer, even when you configure a single
discovery endpoint:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc:
                                # ...
                                issuers: ['https://idp.example.com/realms/demo']
                                discovery:
                                    base_uri: https://idp.example.com/realms/demo/
                                    cache:
                                        id: cache.app
                                    # a trailing slash in the base URI or in the
                                    # announced issuer is ignored when comparing them
                                    check_issuer: true

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
                                    // ...
                                    'issuers' => ['https://idp.example.com/realms/demo'],
                                    'discovery' => [
                                        'base_uri' => 'https://idp.example.com/realms/demo/',
                                        'cache' => [
                                            'id' => 'cache.app',
                                        ],
                                        // a trailing slash in the base URI or in the
                                        // announced issuer is ignored when comparing them
                                        'check_issuer' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

Some identity providers announce an issuer that is different from their base URI
(e.g. the v1.0 endpoints of Microsoft Entra ID announce
``https://sts.windows.net/<tenant>/`` for the
``https://login.microsoftonline.com/<tenant>/`` base URI). In those cases, set
``check_issuer`` to a map of base URIs to the issuer they announce. The base URIs
that are not listed in the map must announce their own base URI as the issuer:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oidc:
                                # ...
                                issuers: ['https://sts.windows.net/<tenant>/']
                                discovery:
                                    base_uri: https://login.microsoftonline.com/<tenant>/
                                    # ...
                                    check_issuer:
                                        'https://login.microsoftonline.com/<tenant>/': 'https://sts.windows.net/<tenant>/'

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
                                    // ...
                                    'issuers' => ['https://sts.windows.net/<tenant>/'],
                                    'discovery' => [
                                        'base_uri' => 'https://login.microsoftonline.com/<tenant>/',
                                        // ...
                                        'check_issuer' => [
                                            'https://login.microsoftonline.com/<tenant>/' => 'https://sts.windows.net/<tenant>/',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

The checked issuer must also be one of the values of the ``issuers`` option,
written exactly as the discovery document announces it (including its trailing
slash, if any).

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

Using OAuth 2.0 Token Introspection
-----------------------------------

Use OAuth 2.0 Token Introspection (`RFC 7662`_) when your application
receives opaque access tokens that only the authorization server can
validate. The ``oauth2`` token handler sends each token to the
introspection endpoint of that server and creates an
:class:`Symfony\\Component\\Security\\Core\\User\\OAuth2User` from its
response, so your application never reads the token itself.

This token handler requires the ``symfony/http-client`` package to make
the needed HTTP requests. If you haven't installed it yet, run this
command:

.. code-block:: terminal

    $ composer require symfony/http-client

The URL of the introspection endpoint and the credentials your application
uses to call it are configured in the HTTP client, not in the firewall.
Define a :ref:`scoped client <http-client-scoped-clients>` whose
``base_uri`` is the introspection endpoint and whose ``auth_basic`` option
holds the client ID and secret of your application:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            http_client:
                scoped_clients:
                    oauth2.introspection:
                        base_uri: 'https://auth.example.com/introspect'
                        auth_basic: '%env(OAUTH2_ID)%:%env(OAUTH2_SECRET)%'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'http_client' => [
                    'scoped_clients' => [
                        'oauth2.introspection' => [
                            'base_uri' => 'https://auth.example.com/introspect',
                            'auth_basic' => '%env(OAUTH2_ID)%:%env(OAUTH2_SECRET)%',
                        ],
                    ],
                ],
            ],
        ]);

.. warning::

    The HTTP client sends the ``auth_basic`` credentials as they are, but
    the ``client_secret_basic`` method of `RFC 6749`_ URL-encodes the
    client ID and the secret first. If any of them contains a colon, a
    plus sign, a space or a non-ASCII character, store it already
    URL-encoded.

Then, pass the service ID of that client to the ``http_client`` option of
the token handler and define the values used to validate the response:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oauth2:
                                http_client: 'oauth2.introspection'
                                issuer: 'https://auth.example.com/'
                                audience: 'https://api.example.com'
                                claim: 'sub'
                                allowed_time_drift: 5

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oauth2' => [
                                    'http_client' => 'oauth2.introspection',
                                    'issuer' => 'https://auth.example.com/',
                                    'audience' => 'https://api.example.com',
                                    'claim' => 'sub',
                                    'allowed_time_drift' => 5,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

These are the available options:

``http_client``
    The service ID of the HTTP client used to call the introspection
    endpoint. It defaults to the ``http_client`` service, which doesn't
    define the endpoint URL or the credentials, so in practice you always
    need to define a scoped client as shown above.

``issuer``
    The identifier of the authorization server, compared with the ``iss``
    member of the introspection response. It defaults to ``null``, which
    skips this check.

``audience``
    The identifier (as a string) or identifiers (as an array) of your
    application. The ``aud`` member of the response must contain at least
    one of them. It defaults to an empty array, which skips this check.

``claim``
    The claim that contains the user identifier (e.g. ``sub``,
    ``username``, ``email``). It defaults to ``null``, which uses the
    ``sub`` claim and falls back to the ``username`` claim.

``allowed_time_drift``
    The tolerance, in seconds, applied when checking the ``iat``, ``nbf``
    and ``exp`` members of the response, to account for clock differences
    between the servers. It defaults to ``0``.

``cache``
    The configuration used to cache the introspection responses, as
    explained in the next section.

The handler rejects the token when the response reports it as inactive,
when its ``exp``, ``nbf`` or ``iat`` dates are not valid timestamps or
place the token outside of its validity period, and when its ``iss`` or
``aud`` members don't match the configured ``issuer`` and ``audience``.
`RFC 7662`_ makes all these members optional, so the handler only checks
the dates included in the response. However, when you configure the
``issuer`` or ``audience`` options, the response must include the
corresponding member.

If you only need to configure the HTTP client, pass its service ID
directly as the value of the ``oauth2`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oauth2: 'oauth2.introspection'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oauth2' => 'oauth2.introspection',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

.. versionadded:: 8.2

    The ``http_client``, ``issuer``, ``audience``, ``claim``,
    ``allowed_time_drift`` and ``cache`` options of the ``oauth2`` token
    handler were introduced in Symfony 8.2.

Caching the Introspection Responses
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the handler calls the authorization server on every request.
Use the ``cache`` option to store the responses of active tokens in a
cache pool. This requires the ``symfony/cache`` package:

.. code-block:: terminal

    $ composer require symfony/cache

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler:
                            oauth2:
                                http_client: 'oauth2.introspection'
                                cache:
                                    # the cache pool service ID (required)
                                    id: cache.app
                                    # max lifetime in seconds (default: 60)
                                    ttl: 60

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_token' => [
                            'token_handler' => [
                                'oauth2' => [
                                    'http_client' => 'oauth2.introspection',
                                    'cache' => [
                                        // the cache pool service ID (required)
                                        'id' => 'cache.app',
                                        // max lifetime in seconds (default: 60)
                                        'ttl' => 60,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

A revoked token is still accepted until its cache entry expires, so keep
the ``ttl`` short. Entries never outlive the ``exp`` member of the
response and the responses of inactive tokens are never cached. Cache keys
use a hash of the token instead of the token itself, so the cache pool
doesn't store any usable credentials.

.. _access-token-dpop:

Binding Access Tokens to a Key (DPoP)
-------------------------------------

.. versionadded:: 8.2

    The ``dpop`` option was introduced in Symfony 8.2.

A bearer access token works for anyone who presents it, so a stolen token can
be used until it expires. DPoP (Demonstrating Proof of Possession, defined in
`RFC 9449`_) prevents this: the authorization server binds the token to a key
of the client (in the ``cnf`` claim of the token) and the client signs a proof
with that key for each request. Enable the ``dpop`` option to only accept
tokens bound to a key whose possession the request proves. This option requires
the ``web-token/jwt-library`` package:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                api:
                    pattern: ^/api
                    stateless: true
                    access_token:
                        dpop: true
                        token_handler:
                            oidc:
                                discovery:
                                    base_uri: 'https://oidc.example.com'
                                issuers: ['https://oidc.example.com']
                                audience: 'api-example'

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'api' => [
                        'pattern' => '^/api',
                        'stateless' => true,
                        'access_token' => [
                            'dpop' => true,
                            'token_handler' => [
                                'oidc' => [
                                    'discovery' => [
                                        'base_uri' => 'https://oidc.example.com',
                                    ],
                                    'issuers' => ['https://oidc.example.com'],
                                    'audience' => 'api-example',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

Clients of this firewall must send the token with the ``DPoP`` scheme instead
of ``Bearer`` and the signed proof in the ``DPoP`` header:

.. code-block:: text

    GET /api/orders HTTP/1.1
    Host: api.example.com
    Authorization: DPoP eyJhbGciOiJFUzI1NiIsInR5cCI6ImF0K2p3dCJ9...
    DPoP: eyJ0eXAiOiJkcG9wK2p3dCIsImFsZyI6IkVTMjU2In0...

Tokens that are not bound to a key are rejected. The ``WWW-Authenticate``
header of ``401`` responses uses the ``DPoP`` scheme and lists the accepted
algorithms. If you :ref:`publish the protected resource metadata
<access-token-resource-metadata>`, the document also sets
``dpop_bound_access_tokens_required`` to ``true``.

The ``dpop`` option accepts these keys:

``algorithms`` (default: ``['ES256', 'PS256', 'RS256']``)
    The algorithms that clients can use to sign the proofs. To accept another
    algorithm, tag its service with
    ``security.access_token_handler.oidc.signature_algorithm``.

``cache`` (default: ``cache.app``)
    The service ID of the cache pool that stores the used proofs to reject
    replayed ones. If the proof can't be stored in the pool, the request is
    rejected. When your application runs on several servers, this pool must be
    shared by all of them (e.g. a Redis pool); otherwise, a proof can be
    replayed against another server.

``proof_lifetime`` (default: ``60``)
    The number of seconds a proof is accepted after the time defined in its
    ``iat`` claim (plus the allowed time drift).

``allowed_time_drift`` (default: ``5``)
    The number of seconds of difference allowed, in both directions, between
    the ``iat`` claim of a proof and the time of the server.

DPoP has these requirements and limitations:

* The ``htu`` claim of the proof is compared with the URL of the request. If
  your application runs behind a reverse proxy, :doc:`configure the trusted
  proxies and hosts </deployment/proxies>` so Symfony generates the same URL
  that the client used.
* The firewall must be ``stateless``. Otherwise, the session would
  authenticate the next requests without any proof.
* The token handler must give access to the claims of the token. The
  ``oidc_user_info`` and ``cas`` handlers don't do that, so they can't be used
  with DPoP. A custom token handler must pass all the claims of the token
  (including ``cnf``) as the attributes of the returned ``UserBadge``.
* Server-provided nonces (Section 8 of the RFC) are not supported.

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
.. _`RFC 6749`: https://datatracker.ietf.org/doc/html/rfc6749
.. _`RFC 7517`: https://datatracker.ietf.org/doc/html/rfc7517
.. _`RFC 7662`: https://datatracker.ietf.org/doc/html/rfc7662
.. _`RFC 9068`: https://datatracker.ietf.org/doc/html/rfc9068
.. _`RFC 9449`: https://datatracker.ietf.org/doc/html/rfc9449
.. _`RFC 9728`: https://datatracker.ietf.org/doc/html/rfc9728
.. _`RFC6750`: https://datatracker.ietf.org/doc/html/rfc6750
.. _`SAML2 (XML structures)`: https://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-tech-overview-2.0.html
.. _`key operation flags`: https://www.iana.org/assignments/jose/jose.xhtml#web-key-operations
