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

This guide assumes you have setup security and have created a user object
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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="main">
                    <access-token token-handler="App\Security\AccessTokenHandler"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\AccessTokenHandler;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main')
                ->accessToken()
                    ->tokenHandler(AccessTokenHandler::class)
            ;
        };

This handler must implement
:class:`Symfony\\Component\\Security\\Http\\AccessToken\\AccessTokenHandlerInterface`::

    // src/Security/AccessTokenHandler.php
    namespace App\Security;

    use App\Repository\AccessTokenRepository;
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
            return new UserBadge($accessToken->getUserId());
        }
    }

The access token authenticator will use the returned user identifier to
load the user using the :ref:`user provider <security-user-providers>`.

.. caution::

    It is important to check the token if is valid. For instance, the
    example above verifies whether the token has not expired. With
    self-contained access tokens such as JWT, the handler is required to
    verify the digital signature and understand all claims, especially
    ``sub``, ``iat``, ``nbf`` and ``exp``.

2) Configure the Token Extractor (Optional)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The application is now ready to handle incoming tokens. A *token extractor*
retrieves the token from the request (e.g. a header or request body).

By default, the access token is read from the request header parameter
``Authorization`` with the scheme ``Bearer`` (e.g. ``Authorization: Bearer
the-token-value``).

Symfony provides other extractors as per the `RFC6750`_:

``header`` (default)
    The token is sent through the request header. Usually ``Authorization``
    with the ``Bearer`` scheme.
``query_string``
    The token is part of the request query string. Usually ``access_token``.
``request_body``
    The token is part of the request body during a POST request. Usually
    ``access_token``.

.. caution::

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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="main">
                    <access-token token-handler="App\Security\AccessTokenHandler">
                        <!-- use a different built-in extractor -->
                        <token-extractor>request_body</token-extractor>

                        <!-- or provide the service ID of a custom extractor -->
                        <token-extractor>App\Security\CustomTokenExtractor</token-extractor>
                    </access-token>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\AccessTokenHandler;
        use App\Security\CustomTokenExtractor;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main')
                ->accessToken()
                    ->tokenHandler(AccessTokenHandler::class)

                    // use a different built-in extractor
                    ->tokenExtractors('request_body')

                    # or provide the service ID of a custom extractor
                    ->tokenExtractors(CustomTokenExtractor::class)
            ;
        };

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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="main">
                    <access-token token-handler="App\Security\AccessTokenHandler">
                        <token-extractor>header</token-extractor>
                        <token-extractor>App\Security\CustomTokenExtractor</token-extractor>
                    </access-token>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\AccessTokenHandler;
        use App\Security\CustomTokenExtractor;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main')
                ->accessToken()
                    ->tokenHandler(AccessTokenHandler::class)
                    ->tokenExtractors([
                        'header',
                        CustomTokenExtractor::class,
                    ])
            ;
        };

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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="main">
                    <access-token token-handler="App\Security\AccessTokenHandler"
                        success-handler="App\Security\Authentication\AuthenticationSuccessHandler"
                    />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\AccessTokenHandler;
        use App\Security\Authentication\AuthenticationSuccessHandler;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main')
                ->accessToken()
                    ->tokenHandler(AccessTokenHandler::class)
                    ->successHandler(AuthenticationSuccessHandler::class)
            ;
        };

.. tip::

    If you want to customize the default failure handling, use the
    ``failure_handler`` option and create a class that implements
    :class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationFailureHandlerInterface`.

.. _`JSON Web Tokens (JWT)`: https://datatracker.ietf.org/doc/html/rfc7519
.. _`SAML2 (XML structures)`: https://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-tech-overview-2.0.html
.. _`RFC6750`: https://datatracker.ietf.org/doc/html/rfc6750
