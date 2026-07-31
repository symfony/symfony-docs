.. _reference-http-client:

http_client
~~~~~~~~~~~

When the HttpClient component is installed, an HTTP client is available
as a service named ``http_client`` or using the autowiring alias
:class:`Symfony\\Contracts\\HttpClient\\HttpClientInterface`.

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

.. _reference-http-client-default-options:

This service can be configured using ``framework.http_client.default_options``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            http_client:
                max_host_connections: 10
                default_options:
                    headers: { 'X-Powered-By': 'ACME App' }
                    max_redirects: 7

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:http-client max-host-connections="10">
                    <framework:default-options max-redirects="7">
                        <framework:header name="X-Powered-By">ACME App</framework:header>
                    </framework:default-options>
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        $container->loadFromExtension('framework', [
            'http_client' => [
                'max_host_connections' => 10,
                'default_options' => [
                    'headers' => [
                        'X-Powered-By' => 'ACME App',
                    ],
                    'max_redirects' => 7,
                ],
            ],
        ]);

    .. code-block:: php-standalone

        $client = HttpClient::create([
            'headers' => [
                'X-Powered-By' => 'ACME App',
            ],
            'max_redirects' => 7,
        ], 10);

.. _reference-http-client-scoped-clients:

Multiple pre-configured HTTP client services can be defined, each with its
service name defined as a key under ``scoped_clients``. Scoped clients inherit
the default options defined for the ``http_client`` service. You can override
these options and can define a few others:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            http_client:
                scoped_clients:
                    my_api.client:
                        auth_bearer: secret_bearer_token
                        # ...

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:http-client>
                    <framework:scoped-client name="my_api.client" auth-bearer="secret_bearer_token"/>
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        $container->loadFromExtension('framework', [
            'http_client' => [
                'scoped_clients' => [
                    'my_api.client' => [
                        'auth_bearer' => 'secret_bearer_token',
                        // ...
                    ],
                ],
            ],
        ]);

    .. code-block:: php-standalone

        $client = HttpClient::createForBaseUri('https://...', [
            'auth_bearer' => 'secret_bearer_token',
            // ...
        ]);

Options defined for scoped clients apply only to URLs that match either their
`base_uri`_ or the `scope`_ option when it is defined. Non-matching URLs always
use default options.

Each scoped client also defines a corresponding named autowiring alias.
If you use for example
``Symfony\Contracts\HttpClient\HttpClientInterface $myApiClient``
as the type and name of an argument, autowiring will inject the ``my_api.client``
service into your autowired classes.

auth_basic
..........

**type**: ``string``

The username and password used to create the ``Authorization`` HTTP header
used in HTTP Basic authentication. The value of this option must follow the
format ``username:password``.

auth_bearer
...........

**type**: ``string``

The token used to create the ``Authorization`` HTTP header used in HTTP Bearer
authentication (also called token authentication).

auth_ntlm
.........

**type**: ``string``

The username and password used to create the ``Authorization`` HTTP header used
in the `Microsoft NTLM authentication protocol`_. The value of this option must
follow the format ``username:password``. This authentication mechanism requires
using the cURL-based transport.

.. _reference-http-client-base-uri:

base_uri
........

**type**: ``string``

URI that is merged into relative URIs, following the rules explained in the
`RFC 3986`_ standard. This is useful when all the requests you make share a
common prefix (e.g. ``https://api.github.com/``) so you can avoid adding it to
every request.

Here are some common examples of how ``base_uri`` merging works in practice:

==========================  ==================  =============================
``base_uri``                Relative URI        Actual Requested URI
==========================  ==================  =============================
http://example.org          /bar                http://example.org/bar
http://example.org/foo      /bar                http://example.org/bar
http://example.org/foo      bar                 http://example.org/bar
http://example.org/foo/     /bar                http://example.org/bar
http://example.org/foo/     bar                 http://example.org/foo/bar
http://example.org          http://symfony.com  http://symfony.com
http://example.org/?bar     bar                 http://example.org/bar
http://example.org/api/v4   /bar                http://example.org/bar
http://example.org/api/v4/  /bar                http://example.org/bar
http://example.org/api/v4   bar                 http://example.org/api/bar
http://example.org/api/v4/  bar                 http://example.org/api/v4/bar
==========================  ==================  =============================

bindto
......

**type**: ``string``

A network interface name, IP address, a host name or a UNIX socket to use as the
outgoing network interface.

buffer
......

**type**: ``boolean`` | ``Closure``

Buffering the response means that you can access its content multiple times
without performing the request again. Buffering is enabled by default when the
content type of the response is ``text/*``, ``application/json`` or ``application/xml``.

If this option is a boolean value, the response is buffered when the value is
``true``. If this option is a closure, the response is buffered when the
returned value is ``true`` (the closure receives as argument an array with the
response headers).

caching
.......

**type**: ``array``

This option configures the behavior of the :ref:`HTTP client caching <http-client_caching>`,
including which types of requests to cache and how many times. The behavior is
defined with the following options:

* :ref:`cache_pool <reference-http-client-caching-cache-pool>`
* :ref:`shared <reference-http-client-caching-shared>`
* :ref:`max_ttl <reference-http-client-caching-max-ttl>`

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            # ...
            default_options:
                caching:
                    cache_pool: cache.app
                    shared: true
                    max_ttl: 86400

            scoped_clients:
                my_api.client:
                    # ...
                    caching:
                        cache_pool: my_taggable_pool

.. versionadded:: 7.4

    The ``caching`` option was introduced in Symfony 7.4.

.. _reference-http-client-caching-cache-pool:

cache_pool
""""""""""

**type**: ``string``

The service ID of the cache pool used to store the cached responses. The service
must implement the :class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface`.

By default, it uses an instance of :class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`
wrapping the ``cache.app`` pool.

.. versionadded:: 7.4

    The ``cache_pool`` option was introduced in Symfony 7.4.

.. _reference-http-client-caching-shared:

shared
""""""

**type**: ``boolean`` **default**: ``true``

If ``true``, it uses a `shared cache`_ so cached responses can be reused across
users. Set it to ``false`` to use a `private cache`_.

.. versionadded:: 7.4

    The ``shared`` option was introduced in Symfony 7.4.

.. _reference-http-client-caching-max-ttl:

max_ttl
"""""""

**type**: ``integer`` **default**: ``null``

The maximum time-to-live (in seconds) for cached responses. By default, responses
are cached for as long as the TTL specified by the server. When this option is
set, server-provided TTLs are capped to this value.

.. versionadded:: 7.4

    The ``max_ttl`` option was introduced in Symfony 7.4.

cafile
......

**type**: ``string``

The path of the certificate authority file that contains one or more
certificates used to verify the other servers' certificates.

capath
......

**type**: ``string``

The path to a directory that contains one or more certificate authority files.

ciphers
.......

**type**: ``string``

A list of the names of the ciphers allowed for the TLS connections. They
can be separated by colons, commas or spaces (e.g. ``'RC4-SHA:TLS13-AES-128-GCM-SHA256'``).

crypto_method
.............

**type**: ``integer``

The minimum version of TLS to accept. The value must be one of the
``STREAM_CRYPTO_METHOD_TLSv*_CLIENT`` constants defined by PHP.

extra
.....

**type**: ``array``

Arbitrary additional data to pass to the HTTP client for further use.
This can be particularly useful when :ref:`decorating an existing client <extensibility>`.

.. _http-headers:

headers
.......

**type**: ``array``

An associative array of the HTTP headers added before making the request. This
value must use the format ``['header-name' => 'value0, value1, ...']``.

http_version
............

**type**: ``string`` | ``null`` **default**: ``null``

The HTTP version to use, typically ``'1.1'``  or ``'2.0'``. Leave it to ``null``
to let Symfony select the best version automatically.

local_cert
..........

**type**: ``string``

The path to a file that contains the `PEM formatted`_ certificate used by the
HTTP client. This is often combined with the ``local_pk`` and ``passphrase``
options.

local_pk
........

**type**: ``string``

The path of a file that contains the `PEM formatted`_ private key of the
certificate defined in the ``local_cert`` option.

max_duration
............

**type**: ``float`` **default**: ``0``

The maximum execution time, in seconds, that the request and the response are
allowed to take. A value lower than or equal to 0 means it is unlimited.

.. _reference-http-client-max-host-connections:

max_host_connections
....................

**type**: ``integer`` **default**: ``6``

Defines the maximum number of simultaneously open connections to a single host
(considering a "host" the same as a "host name + port number" pair). This limit
also applies for proxy connections, where the proxy is considered to be the host
for which this limit is applied.

mock_response_factory
.....................

**type**: ``string``

The ID of the service that should generate mock responses. The service must
be either an invokable or an iterable.

max_redirects
.............

**type**: ``integer`` **default**: ``20``

The maximum number of redirects to follow. Use ``0`` to not follow any
redirection.

no_proxy
........

**type**: ``string`` | ``null`` **default**: ``null``

A comma separated list of hosts that do not require a proxy to be reached, even
if one is configured. Use the ``'*'`` wildcard to match all hosts and an empty
string to match none (disables the proxy).

passphrase
..........

**type**: ``string``

The passphrase used to encrypt the certificate stored in the file defined in the
``local_cert`` option.

peer_fingerprint
................

**type**: ``array``

When negotiating a TLS connection, the server sends a certificate
indicating its identity. A public key is extracted from this certificate and if
it does not exactly match any of the public keys provided in this option, the
connection is aborted before sending or receiving any data.

The value of this option is an associative array of ``algorithm => hash``
(e.g. ``['pin-sha256' => '...']``).

proxy
.....

**type**: ``string`` | ``null``

The HTTP proxy to use to make the requests. Leave it to ``null`` to detect the
proxy automatically based on your system configuration.

query
.....

**type**: ``array``

An associative array of the query string values added to the URL before making
the request. This value must use the format ``['parameter-name' => parameter-value, ...]``.

rate_limiter
............

**type**: ``string``

The service ID of the rate limiter used to limit the number of HTTP requests
within a certain period. The service must implement the
:class:`Symfony\\Component\\RateLimiter\\LimiterInterface`.

.. versionadded:: 7.1

    The ``rate_limiter`` option was introduced in Symfony 7.1.

resolve
.......

**type**: ``array``

A list of hostnames and their IP addresses to pre-populate the DNS cache used by
the HTTP client in order to avoid a DNS lookup for those hosts. This option is
useful to improve security when IPs are checked before the URL is passed to the
client and to make your tests easier.

The value of this option is an associative array of ``domain => IP address``
(e.g. ``['symfony.com' => '46.137.106.254', ...]``).

.. _reference-http-client-retry-failed:

retry_failed
............

**type**: ``array``

This option configures the behavior of the HTTP client when some request fails,
including which types of requests to retry and how many times. The behavior is
defined with the following options:

* :ref:`delay <reference-http-client-retry-delay>`
* :ref:`enabled <reference-http-client-retry-enabled>`
* :ref:`http_codes <reference-http-client-retry-http-codes>`
* :ref:`jitter <reference-http-client-retry-jitter>`
* :ref:`max_delay <reference-http-client-retry-max-delay>`
* :ref:`max_retries <reference-http-client-retry-max-retries>`
* :ref:`multiplier <reference-http-client-retry-multiplier>`
* :ref:`retry_strategy <reference-http-client-retry-retry-strategy>`

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            # ...
            default_options:
                retry_failed:
                    # retry_strategy: app.custom_strategy
                    http_codes:
                        0: ['GET', 'HEAD']   # retry network errors if request method is GET or HEAD
                        429: true            # retry all responses with 429 status code
                        500: ['GET', 'HEAD']
                    max_retries: 2
                    delay: 1000
                    multiplier: 3
                    max_delay: 5000
                    jitter: 0.3

            scoped_clients:
                my_api.client:
                    # ...
                    retry_failed:
                        max_retries: 4

.. _reference-http-client-retry-delay:

delay
"""""

**type**: ``integer`` **default**: ``1000``

The initial delay in milliseconds used to compute the waiting time between retries.

.. _reference-http-client-retry-enabled:

enabled
"""""""

**type**: ``boolean`` **default**: ``false``

Whether to enable support for retrying failed HTTP requests.
This setting is automatically set to true when one of the child settings is configured.

.. _reference-http-client-retry-http-codes:

http_codes
""""""""""

**type**: ``array`` **default**: :method:`Symfony\\Component\\HttpClient\\Retry\\GenericRetryStrategy::DEFAULT_RETRY_STATUS_CODES`

The list of HTTP status codes that triggers a retry of the request.

.. _reference-http-client-retry-jitter:

jitter
""""""

**type**: ``float`` **default**: ``0.1`` (must be between 0.0 and 1.0)

This option adds some randomness to the delay. It's useful to avoid sending
multiple requests to the server at the exact same time. The randomness is
calculated as ``delay * jitter``. For example: if delay is ``1000ms`` and jitter
is ``0.2``, the actual delay will be a number between ``800`` and ``1200`` (1000 +/- 20%).

.. _reference-http-client-retry-max-delay:

max_delay
"""""""""

**type**: ``integer`` **default**: ``0``

The maximum initial number of milliseconds to wait between retries.
Use ``0`` to not limit the duration.

.. _reference-http-client-retry-max-retries:

max_retries
"""""""""""

**type**: ``integer`` **default**: ``3``

The maximum number of retries for failing requests. When the maximum is reached,
the client returns the last received response.

.. _reference-http-client-retry-multiplier:

multiplier
""""""""""

**type**: ``float`` **default**: ``2``

This value is multiplied by the delay each time a retry occurs, to distribute
retries in time instead of making all of them sequentially.

.. _reference-http-client-retry-retry-strategy:

retry_strategy
""""""""""""""

**type**: ``string``

The service is used to decide if a request should be retried and to compute the
time to wait between retries. By default, it uses an instance of
:class:`Symfony\\Component\\HttpClient\\Retry\\GenericRetryStrategy` configured
with ``http_codes``, ``delay``, ``max_delay``, ``multiplier`` and ``jitter``
options. This class has to implement
:class:`Symfony\\Component\\HttpClient\\Retry\\RetryStrategyInterface`.

scope
.....

**type**: ``string``

For scoped clients only: the regular expression that the URL must match before
applying all other non-default options. By default, the scope is derived from
`base_uri`_.

timeout
.......

**type**: ``float`` **default**: depends on your PHP config

Time, in seconds, to wait for network activity. If the connection is idle for longer, a
:class:`Symfony\\Component\\HttpClient\\Exception\\TransportException` is thrown.
Its default value is the same as the value of PHP's `default_socket_timeout`_
config option.

verify_host
...........

**type**: ``boolean`` **default**: ``true``

If ``true``, the certificate sent by other servers is verified to ensure that
their common name matches the host included in the URL. This is usually
combined with ``verify_peer`` to also verify the certificate authenticity.

verify_peer
...........

**type**: ``boolean`` **default**: ``true``

If ``true``, the certificate sent by other servers when negotiating a TLS
connection is verified for authenticity. Authenticating the certificate is not
enough to be sure about the server, so you should combine this with the
``verify_host`` option.

 .. _configuration-framework-http_method_override:

.. _`RFC 3986`: https://www.ietf.org/rfc/rfc3986.txt
.. _`default_socket_timeout`: https://www.php.net/manual/en/filesystem.configuration.php#ini.default-socket-timeout
.. _`PEM formatted`: https://en.wikipedia.org/wiki/Privacy-Enhanced_Mail
.. _`Microsoft NTLM authentication protocol`: https://docs.microsoft.com/en-us/windows/win32/secauthn/microsoft-ntlm
.. _`shared cache`: https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Caching#shared_cache
.. _`private cache`: https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Caching#private_caches
