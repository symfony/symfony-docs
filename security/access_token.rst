.. index::
   single: Security; Access Token

How to use Access Token Authentication
======================================

Access tokens are commonly used in API contexts. The access token is obtained
through an authorization server (or similar) whose role is to verify the user identity
and receive consent before the token is issued.

Access Tokens can be of any kind: opaque strings, Json Web Tokens (JWT) or SAML2 (XML structures).
Please refer to the `RFC6750`_: *The OAuth 2.0 Authorization Framework: Bearer Token Usage*.

Using the Access Token Authenticator
------------------------------------

This guide assumes you have setup security and have created a user object
in your application. Follow :doc:`the main security guide </security>` if
this is not yet the case.

1) Configure the Access Token Authenticator
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To use the access token authenticator, you must configure a ``token_handler``.
The token handler retrieves the user identifier from the token.
In order to get the user identifier, implementations may need to load and validate
the token (e.g. revocation, expiration time, digital signature...).

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler: App\Security\AccessTokenHandler

This handler shall implement the interface
:class:`Symfony\\Component\\Security\\Http\\AccessToken\\AccessTokenHandlerInterface`.
In the following example, the handler will retrieve the token from a database
using a fictive repository.

.. configuration-block::

    .. code-block:: php

        // src/Security/AccessTokenHandler.php
        namespace App\Security;

        use App\Repository\AccessTokenRepository;
        use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;

        class AccessTokenHandler implements AccessTokenHandlerInterface
        {
            public function __construct(
                private readonly AccessTokenRepository $repository
            ) {
            }

            public function getUserIdentifierFrom(string $token): string
            {
                $accessToken = $this->repository->findOneByValue($token);
                if ($accessToken === null || !$accessToken->isValid()) {
                    throw new BadCredentialsException('Invalid credentials.');
                }

                return $accessToken->getUserId();
            }
        }

.. caution::

    It is important to check the token is valid.
    For instance, in the example we verify the token has not expired.
    With self-contained access tokens such as JWT, the handler is required to
    verify the digital signature and understand all claims,
    especially ``sub``, ``iat``, ``nbf`` and ``exp``.

Customizing the Authenticator
-----------------------------

1) Access Token Extractors

By default, the access token is read from the request header parameter ``Authorization`` with the scheme ``Bearer``.
You can change the behavior and send the access token through different ways.

This authenticator provides services able to extract the access token as per the RFC6750:

- ``header`` or ``security.access_token_extractor.header``: the token is sent through the request header. Usually ``Authorization`` with the ``Bearer`` scheme.
- ``query_string`` or ``security.access_token_extractor.query_string``: the token is part of the query string. Usually ``access_token``.
- ``request_body`` or ``security.access_token_extractor.request_body``: the token is part of the request body during a POST request. Usually ``access_token``.

.. caution::

    Because of the security weaknesses associated with the URI method,
    including the high likelihood that the URL or the request body containing the access token will be logged,
    methods ``query_string`` and ``request_body`` **SHOULD NOT** be used unless it is impossible
    to transport the access token in the request header field.

Also, you can also create a custom extractor. The class shall implement the interface
:class:`Symfony\\Component\\Security\\Http\\AccessToken\\AccessTokenExtractorInterface`.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler: App\Security\AccessTokenHandler
                        token_extractors: 'my_custom_access_token_extractor'

It is possible to set multiple extractors.
In this case, **the order is important**: the first in the list is called first.

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
                            - 'request_body'
                            - 'query_string'
                            - 'my_custom_access_token_extractor'

2) Customizing the Success Handler

Sometimes, the default success handling does not fit your use-case (e.g.
when you need to generate and return additional response header parameters).
To customize how the success handler behaves, create your own handler as a class that implements
:class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationSuccessHandlerInterface`::

    // src/Security/Authentication/AuthenticationSuccessHandler.php
    namespace App\Security\Authentication;

    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

    class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
    {
        public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
        {
            $user = $token->getUser();
            $userApiToken = $user->getApiToken();

            return new JsonResponse(['apiToken' => $userApiToken]);
        }
    }

Then, configure this service ID as the ``success_handler``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    access_token:
                        token_handler: App\Security\AccessTokenHandler
                        success_handler: App\Security\Authentication\AuthenticationSuccessHandler

.. tip::

    If you want to customize the default failure handling, use the
    ``failure_handler`` option and create a class that implements
    :class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationFailureHandlerInterface`.

.. _`RFC6750`: https://datatracker.ietf.org/doc/html/rfc6750
