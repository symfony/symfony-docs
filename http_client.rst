HTTP Client
===========

Installation
------------

The HttpClient component is a low-level HTTP client with support for both
PHP stream wrappers and cURL. It provides utilities to consume APIs and
supports synchronous and asynchronous operations. You can install it with:

.. code-block:: terminal

    $ composer require symfony/http-client

Basic Usage
-----------

Use the :class:`Symfony\\Component\\HttpClient\\HttpClient` class to make
requests. In the Symfony framework, this class is available as the
``http_client`` service. This service will be :doc:`autowired </service_container/autowiring>`
automatically when type-hinting for :class:`Symfony\\Contracts\\HttpClient\\HttpClientInterface`:

.. configuration-block::

    .. code-block:: php-symfony

        use Symfony\Contracts\HttpClient\HttpClientInterface;

        class SymfonyDocs
        {
            public function __construct(
                private HttpClientInterface $client,
            ) {
            }

            public function fetchGitHubInformation(): array
            {
                $response = $this->client->request(
                    'GET',
                    'https://api.github.com/repos/symfony/symfony-docs'
                );

                $statusCode = $response->getStatusCode();
                // $statusCode = 200
                $contentType = $response->getHeaders()['content-type'][0];
                // $contentType = 'application/json'
                $content = $response->getContent();
                // $content = '{"id":521583, "name":"symfony-docs", ...}'
                $content = $response->toArray();
                // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

                return $content;
            }
        }

    .. code-block:: php-standalone

        use Symfony\Component\HttpClient\HttpClient;

        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            'https://api.github.com/repos/symfony/symfony-docs'
        );

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

.. tip::

    The HTTP client is interoperable with many common HTTP client abstractions in
    PHP. You can also use any of these abstractions to profit from autowirings.
    See `Interoperability`_ for more information.

Configuration
-------------

The HTTP client contains many options you might need to take full control of
the way the request is performed, including DNS pre-resolution, SSL parameters,
public key pinning, etc. They can be defined globally in the configuration (to
apply it to all requests) and to each request (which overrides any global
configuration).

You can configure the global options using the ``default_options`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            http_client:
                default_options:
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
                <framework:http-client>
                    <framework:default-options max-redirects="7"/>
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->httpClient()
                ->defaultOptions()
                    ->maxRedirects(7)
            ;
        };

    .. code-block:: php-standalone

        $client = HttpClient::create([
             'max_redirects' => 7,
        ]);

You can also use the :method:`Symfony\\Contracts\\HttpClient\\HttpClientInterface::withOptions`
method to retrieve a new instance of the client with new default options::

    $this->client = $client->withOptions([
        'base_uri' => 'https://...',
        'headers' => ['header-name' => 'header-value'],
        'extra' => ['my-key' => 'my-value'],
    ]);

Some options are described in this guide:

* `Authentication`_
* `Query String Parameters`_
* `Headers`_
* `Redirects`_
* `Retry Failed Requests`_
* `HTTP Proxies`_
* `Using URI Templates`_

Check out the full :ref:`http_client config reference <reference-http-client>`
to learn about all the options.

The HTTP client also has one configuration option called
``max_host_connections``, this option can not be overridden by a request:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            http_client:
                max_host_connections: 10
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
                <framework:http-client max-host-connections="10">
                    <!-- ... -->
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->httpClient()
                ->maxHostConnections(10)
                // ...
            ;
        };

    .. code-block:: php-standalone

        $client = HttpClient::create([], 10);

Scoping Client
~~~~~~~~~~~~~~

It's common that some of the HTTP client options depend on the URL of the
request (e.g. you must set some headers when making requests to GitHub API but
not for other hosts). If that's your case, the component provides scoped
clients (using :class:`Symfony\\Component\\HttpClient\\ScopingHttpClient`) to
autoconfigure the HTTP client based on the requested URL:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            http_client:
                scoped_clients:
                    # only requests matching scope will use these options
                    github.client:
                        scope: 'https://api\.github\.com'
                        headers:
                            Accept: 'application/vnd.github.v3+json'
                            Authorization: 'token %env(GITHUB_API_TOKEN)%'
                        # ...

                    # using base_uri, relative URLs (e.g. request("GET", "/repos/symfony/symfony-docs"))
                    # will default to these options
                    github.client:
                        base_uri: 'https://api.github.com'
                        headers:
                            Accept: 'application/vnd.github.v3+json'
                            Authorization: 'token %env(GITHUB_API_TOKEN)%'
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
                    <!-- only requests matching scope will use these options -->
                    <framework:scoped-client name="github.client"
                        scope="https://api\.github\.com"
                    >
                        <framework:header name="Accept">application/vnd.github.v3+json</framework:header>
                        <framework:header name="Authorization">token %env(GITHUB_API_TOKEN)%</framework:header>
                    </framework:scoped-client>

                    <!-- using base-uri, relative URLs (e.g. request("GET", "/repos/symfony/symfony-docs"))
                         will default to these options -->
                    <framework:scoped-client name="github.client"
                        base-uri="https://api.github.com"
                    >
                        <framework:header name="Accept">application/vnd.github.v3+json</framework:header>
                        <framework:header name="Authorization">token %env(GITHUB_API_TOKEN)%</framework:header>
                    </framework:scoped-client>
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // only requests matching scope will use these options
            $framework->httpClient()->scopedClient('github.client')
                ->scope('https://api\.github\.com')
                ->header('Accept', 'application/vnd.github.v3+json')
                ->header('Authorization', 'token %env(GITHUB_API_TOKEN)%')
                // ...
            ;

            // using base_url, relative URLs (e.g. request("GET", "/repos/symfony/symfony-docs"))
            // will default to these options
            $framework->httpClient()->scopedClient('github.client')
                ->baseUri('https://api.github.com')
                ->header('Accept', 'application/vnd.github.v3+json')
                ->header('Authorization', 'token %env(GITHUB_API_TOKEN)%')
                // ...
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HttpClient\HttpClient;
        use Symfony\Component\HttpClient\ScopingHttpClient;

        $client = HttpClient::create();
        $client = new ScopingHttpClient($client, [
            // the options defined as values apply only to the URLs matching
            // the regular expressions defined as keys
            'https://api\.github\.com/' => [
                'headers' => [
                    'Accept' => 'application/vnd.github.v3+json',
                    'Authorization' => 'token '.$githubToken,
                ],
            ],
            // ...
        ]);

        // relative URLs will use the 2nd argument as base URI and use the options of the 3rd argument
        $client = ScopingHttpClient::forBaseUri($client, 'https://api.github.com/', [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'token '.$githubToken,
            ],
        ]);

You can define several scopes, so that each set of options is added only if a
requested URL matches one of the regular expressions set by the ``scope`` option.

If you use scoped clients in the Symfony framework, you must use any of the
methods defined by Symfony to :ref:`choose a specific service <services-wire-specific-service>`.
Each client has a unique service named after its configuration.

Each scoped client also defines a corresponding named autowiring alias.
If you use for example
``Symfony\Contracts\HttpClient\HttpClientInterface $githubClient``
as the type and name of an argument, autowiring will inject the ``github.client``
service into your autowired classes.

.. note::

    Read the :ref:`base_uri option docs <reference-http-client-base-uri>` to
    learn the rules applied when merging relative URIs into the base URI of the
    scoped client.

Making Requests
---------------

The HTTP client provides a single ``request()`` method to perform all kinds of
HTTP requests::

    $response = $client->request('GET', 'https://...');
    $response = $client->request('POST', 'https://...');
    $response = $client->request('PUT', 'https://...');
    // ...

    // you can add request options (or override global ones) using the 3rd argument
    $response = $client->request('GET', 'https://...', [
        'headers' => [
            'Accept' => 'application/json',
        ],
    ]);

Responses are always asynchronous, so that the call to the method returns
immediately instead of waiting to receive the response::

    // code execution continues immediately; it doesn't wait to receive the response
    $response = $client->request('GET', 'http://releases.ubuntu.com/18.04.2/ubuntu-18.04.2-desktop-amd64.iso');

    // getting the response headers waits until they arrive
    $contentType = $response->getHeaders()['content-type'][0];

    // trying to get the response content will block the execution until
    // the full response content is received
    $content = $response->getContent();

This component also supports :ref:`streaming responses <http-client-streaming-responses>`
for full asynchronous applications.

Authentication
~~~~~~~~~~~~~~

The HTTP client supports different authentication mechanisms. They can be
defined globally in the configuration (to apply it to all requests) and to
each request (which overrides any global authentication):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            http_client:
                scoped_clients:
                    example_api:
                        base_uri: 'https://example.com/'

                        # HTTP Basic authentication
                        auth_basic: 'the-username:the-password'

                        # HTTP Bearer authentication (also called token authentication)
                        auth_bearer: the-bearer-token

                        # Microsoft NTLM authentication
                        auth_ntlm: 'the-username:the-password'

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
                    <!-- Available authentication options:
                         auth-basic: HTTP Basic authentication
                         auth-bearer: HTTP Bearer authentication (also called token authentication)
                         auth-ntlm: Microsoft NTLM authentication -->
                    <framework:scoped-client name="example_api"
                        base-uri="https://example.com/"
                        auth-basic="the-username:the-password"
                        auth-bearer="the-bearer-token"
                        auth-ntlm="the-username:the-password"
                    />
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->httpClient()->scopedClient('example_api')
                ->baseUri('https://example.com/')
                // HTTP Basic authentication
                ->authBasic('the-username:the-password')

                // HTTP Bearer authentication (also called token authentication)
                ->authBearer('the-bearer-token')

                // Microsoft NTLM authentication
                ->authNtlm('the-username:the-password')
            ;
        };

    .. code-block:: php-standalone

        $client = HttpClient::createForBaseUri('https://example.com/', [
            // HTTP Basic authentication (there are multiple ways of configuring it)
            'auth_basic' => ['the-username'],
            'auth_basic' => ['the-username', 'the-password'],
            'auth_basic' => 'the-username:the-password',

            // HTTP Bearer authentication (also called token authentication)
            'auth_bearer' => 'the-bearer-token',

            // Microsoft NTLM authentication (there are multiple ways of configuring it)
            'auth_ntlm' => ['the-username'],
            'auth_ntlm' => ['the-username', 'the-password'],
            'auth_ntlm' => 'the-username:the-password',
        ]);

.. code-block:: php

    $response = $client->request('GET', 'https://...', [
        // use a different HTTP Basic authentication only for this request
        'auth_basic' => ['the-username', 'the-password'],

        // ...
    ]);

.. note::

    The NTLM authentication mechanism requires using the cURL transport.
    By using ``HttpClient::createForBaseUri()``, we ensure that the auth credentials
    won't be sent to any other hosts than https://example.com/.

Query String Parameters
~~~~~~~~~~~~~~~~~~~~~~~

You can either append them manually to the requested URL, or define them as an
associative array via the ``query`` option, that will be merged with the URL::

    // it makes an HTTP GET request to https://httpbin.org/get?token=...&name=...
    $response = $client->request('GET', 'https://httpbin.org/get', [
        // these values are automatically encoded before including them in the URL
        'query' => [
            'token' => '...',
            'name' => '...',
        ],
    ]);

Headers
~~~~~~~

Use the ``headers`` option to define the default headers added to all requests:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            http_client:
                default_options:
                    headers:
                        'User-Agent': 'My Fancy App'

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
                    <framework:default-options>
                        <framework:header name="User-Agent">My Fancy App</framework:header>
                    </framework:default-options>
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->httpClient()
                ->defaultOptions()
                    ->header('User-Agent', 'My Fancy App')
            ;
        };

    .. code-block:: php-standalone

        // this header is added to all requests made by this client
        $client = HttpClient::create([
            'headers' => [
                'User-Agent' => 'My Fancy App',
            ],
        ]);

You can also set new headers or override the default ones for specific requests::

    // this header is only included in this request and overrides the value
    // of the same header if defined globally by the HTTP client
    $response = $client->request('POST', 'https://...', [
        'headers' => [
            'Content-Type' => 'text/plain',
        ],
    ]);

Uploading Data
~~~~~~~~~~~~~~

This component provides several methods for uploading data using the ``body``
option. You can use regular strings, closures, iterables and resources and they'll be
processed automatically when making the requests::

    $response = $client->request('POST', 'https://...', [
        // defining data using a regular string
        'body' => 'raw data',

        // defining data using an array of parameters
        'body' => ['parameter1' => 'value1', '...'],

        // using a closure to generate the uploaded data
        'body' => function (int $size): string {
            // ...
        },

        // using a resource to get the data from it
        'body' => fopen('/path/to/file', 'r'),
    ]);

When uploading data with the ``POST`` method, if you don't define the
``Content-Type`` HTTP header explicitly, Symfony assumes that you're uploading
form data and adds the required
``'Content-Type: application/x-www-form-urlencoded'`` header for you.

When the ``body`` option is set as a closure, it will be called several times until
it returns the empty string, which signals the end of the body. Each time, the
closure should return a string smaller than the amount requested as argument.

A generator or any ``Traversable`` can also be used instead of a closure.

.. tip::

    When uploading JSON payloads, use the ``json`` option instead of ``body``. The
    given content will be JSON-encoded automatically and the request will add the
    ``Content-Type: application/json`` automatically too::

        $response = $client->request('POST', 'https://...', [
            'json' => ['param1' => 'value1', '...'],
        ]);

        $decodedPayload = $response->toArray();

To submit a form with file uploads, pass the file handle to the ``body`` option::

    $fileHandle = fopen('/path/to/the/file' 'r');
    $client->request('POST', 'https://...', ['body' => ['the_file' => $fileHandle]]);

By default, this code will populate the filename and content-type with the data
of the opened file, but you can configure both with the PHP streaming configuration::

    stream_context_set_option($fileHandle, 'http', 'filename', 'the-name.txt');
    stream_context_set_option($fileHandle, 'http', 'content_type', 'my/content-type');

.. versionadded:: 6.3

    The feature to upload files using handles was introduced in Symfony 6.3.
    In previous Symfony versions you had to encode the body contents according
    to the ``multipart/form-data`` content-type using the :doc:`Symfony Mime </components/mime>`
    component.

.. tip::

    When using multidimensional arrays the :class:`Symfony\\Component\\Mime\\Part\\Multipart\\FormDataPart`
    class automatically appends ``[key]`` to the name of the field::

        $formData = new FormDataPart([
            'array_field' => [
                'some value',
                'other value',
            ],
        ]);

        $formData->getParts(); // Returns two instances of TextPart
                               // with the names "array_field[0]" and "array_field[1]"

    This behavior can be bypassed by using the following array structure::

        $formData = new FormDataPart([
            ['array_field' => 'some value'],
            ['array_field' => 'other value'],
        ]);

        $formData->getParts(); // Returns two instances of TextPart both
                               // with the name "array_field"

By default, HttpClient streams the body contents when uploading them. This might
not work with all servers, resulting in HTTP status code 411 ("Length Required")
because there is no ``Content-Length`` header. The solution is to turn the body
into a string with the following method (which will increase memory consumption
when the streams are large)::

    $client->request('POST', 'https://...', [
        // ...
        'body' => $formData->bodyToString(),
    ]);

If you need to add a custom HTTP header to the upload, you can do::

    $headers = $formData->getPreparedHeaders()->toArray();
    $headers[] = 'X-Foo: bar';

Cookies
~~~~~~~

The HTTP client provided by this component is stateless but handling cookies
requires a stateful storage (because responses can update cookies and they must
be used for subsequent requests). That's why this component doesn't handle
cookies automatically.

You can either :ref:`send cookies with the BrowserKit component <component-browserkit-sending-cookies>`,
which integrates seamlessly with the HttpClient component, or manually setting
the ``Cookie`` HTTP header as follows::

    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\HttpFoundation\Cookie;

    $client = HttpClient::create([
        'headers' => [
            'Cookie' => new Cookie('flavor', 'chocolate', strtotime('+1 day')),

            // you can also pass the cookie contents as a string
            'Cookie' => 'flavor=chocolate; expires=Sat, 11 Feb 2023 12:18:13 GMT; Max-Age=86400; path=/'
        ],
    ]);

Redirects
~~~~~~~~~

By default, the HTTP client follows redirects, up to a maximum of 20, when
making a request. Use the ``max_redirects`` setting to configure this behavior
(if the number of redirects is higher than the configured value, you'll get a
:class:`Symfony\\Component\\HttpClient\\Exception\\RedirectionException`)::

    $response = $client->request('GET', 'https://...', [
        // 0 means to not follow any redirect
        'max_redirects' => 0,
    ]);

Retry Failed Requests
~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 6.4

    The ``max_retries`` feature was added in Symfony 6.4.

Sometimes, requests fail because of network issues or temporary server errors.
Symfony's HttpClient allows to retry failed requests automatically using the
:ref:`retry_failed option <reference-http-client-retry-failed>`.

By default, failed requests are retried up to 3 times, with an exponential delay
between retries (first retry = 1 second; third retry: 4 seconds) and only for
the following HTTP status codes: ``423``, ``425``, ``429``, ``502`` and ``503``
when using any HTTP method and ``500``, ``504``, ``507`` and ``510`` when using
an HTTP `idempotent method`_. Use the ``max_retries`` setting to configure the amount
of times a request is retried.

Check out the full list of configurable :ref:`retry_failed options <reference-http-client-retry-failed>`
to learn how to tweak each of them to fit your application needs.

When using the HttpClient outside of a Symfony application, use the
:class:`Symfony\\Component\\HttpClient\\RetryableHttpClient` class to wrap your
original HTTP client::

    use Symfony\Component\HttpClient\RetryableHttpClient;

    $client = new RetryableHttpClient(HttpClient::create());

The ``RetryableHttpClient`` uses a
:class:`Symfony\\Component\\HttpClient\\Retry\\RetryStrategyInterface` to
decide if the request should be retried, and to define the waiting time between
each retry.

Retry Over Several Base URIs
............................

.. versionadded:: 6.3

    The multiple ``base_uri`` feature was added in Symfony 6.3.

The ``RetryableHttpClient`` can be configured to use multiple base URIs. This
feature provides increased flexibility and reliability for making HTTP
requests. Pass an array of base URIs as option ``base_uri`` when making a
request::

    $response = $client->request('GET', 'some-page', [
        'base_uri' => [
            // first request will use this base URI
            'https://example.com/a/',
            // if first request fails, the following base URI will be used
            'https://example.com/b/',
        ],
    ]);

When the number of retries is higher than the number of base URIs, the
last base URI will be used for the remaining retries.

If you want to shuffle the order of base URIs for each retry attempt, nest the
base URIs you want to shuffle in an additional array::

    $response = $client->request('GET', 'some-page', [
        'base_uri' => [
            [
                // a single random URI from this array will be used for the first request
                'https://example.com/a/',
                'https://example.com/b/',
            ],
            // non-nested base URIs are used in order
            'https://example.com/c/',
        ],
    ]);

This feature allows for a more randomized approach to handling retries,
reducing the likelihood of repeatedly hitting the same failed base URI.

By using a nested array for the base URI, you can use this feature
to distribute the load among many nodes in a cluster of servers.

You can also configure the array of base URIs using the ``withOptions()``
method::

    $client = $client->withOptions(['base_uri' => [
        'https://example.com/a/',
        'https://example.com/b/',
    ]]);

HTTP Proxies
~~~~~~~~~~~~

By default, this component honors the standard environment variables that your
Operating System defines to direct the HTTP traffic through your local proxy.
This means there is usually nothing to configure to have the client work with
proxies, provided these env vars are properly configured.

You can still set or override these settings using the ``proxy`` and ``no_proxy``
options:

* ``proxy`` should be set to the ``http://...`` URL of the proxy to get through

* ``no_proxy`` disables the proxy for a comma-separated list of hosts that do not
  require it to get reached.

Progress Callback
~~~~~~~~~~~~~~~~~

By providing a callable to the ``on_progress`` option, one can track
uploads/downloads as they complete. This callback is guaranteed to be called on
DNS resolution, on arrival of headers and on completion; additionally it is
called when new data is uploaded or downloaded and at least once per second::

    $response = $client->request('GET', 'https://...', [
        'on_progress' => function (int $dlNow, int $dlSize, array $info): void {
            // $dlNow is the number of bytes downloaded so far
            // $dlSize is the total size to be downloaded or -1 if it is unknown
            // $info is what $response->getInfo() would return at this very time
        },
    ]);

Any exceptions thrown from the callback will be wrapped in an instance of
``TransportExceptionInterface`` and will abort the request.

HTTPS Certificates
~~~~~~~~~~~~~~~~~~

HttpClient uses the system's certificate store to validate SSL certificates
(while browsers use their own stores). When using self-signed certificates
during development, it's recommended to create your own certificate authority
(CA) and add it to your system's store.

Alternatively, you can also disable ``verify_host`` and ``verify_peer`` (see
:ref:`http_client config reference <reference-http-client>`), but this is not
recommended in production.

SSRF (Server-side request forgery) Handling
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

`SSRF`_ allows an attacker to induce the backend application to make HTTP
requests to an arbitrary domain. These attacks can also target the internal
hosts and IPs of the attacked server.

If you use an :class:`Symfony\\Component\\HttpClient\\HttpClient` together
with user-provided URIs, it is probably a good idea to decorate it with a
:class:`Symfony\\Component\\HttpClient\\NoPrivateNetworkHttpClient`. This will
ensure local networks are made inaccessible to the HTTP client::

    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;

    $client = new NoPrivateNetworkHttpClient(HttpClient::create());
    // nothing changes when requesting public networks
    $client->request('GET', 'https://example.com/');

    // however, all requests to private networks are now blocked by default
    $client->request('GET', 'http://localhost/');

    // the second optional argument defines the networks to block
    // in this example, requests from 104.26.14.0 to 104.26.15.255 will result in an exception
    // but all the other requests, including other internal networks, will be allowed
    $client = new NoPrivateNetworkHttpClient(HttpClient::create(), ['104.26.14.0/23']);

Profiling
~~~~~~~~~

When you are using the :class:`Symfony\\Component\\HttpClient\\TraceableHttpClient`,
responses content will be kept in memory and may exhaust it.

You can disable this behavior by setting the ``extra.trace_content`` option to ``false``
in your requests::

    $response = $client->request('GET', 'https://...', [
        'extra' => ['trace_content' => false],
    ]);

This setting won’t affect other clients.

Using URI Templates
~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\HttpClient\\UriTemplateHttpClient` provides
a client that eases the use of URI templates, as described in the `RFC 6570`_::

    $client = new UriTemplateHttpClient();

    // this will make a request to the URL http://example.org/users?page=1
    $client->request('GET', 'http://example.org/{resource}{?page}', [
        'vars' => [
            'resource' => 'users',
            'page' => 1,
        ],
    ]);

When using this client in the framework context, all existing HTTP clients
are decorated by the :class:`Symfony\\Component\\HttpClient\\UriTemplateHttpClient`.
This means that URI template feature is enabled by default for all HTTP clients
you may use in your application.

You can configure variables that will be replaced globally in all URI templates
of your application:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            http_client:
                default_options:
                    vars:
                        - secret: 'secret-token'

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
                    <framework:default-options>
                        <framework:vars name="secret">secret-token</framework:vars>
                    </framework:default-options>
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->httpClient()
                ->defaultOptions()
                    ->vars(['secret' => 'secret-token'])
            ;
        };

If you want to define your own logic to handle variables of URI templates, you
can do so by redefining the ``http_client.uri_template_expander`` alias. Your
service must be invokable.

.. versionadded:: 6.3

    The :class:`Symfony\\Component\\HttpClient\\UriTemplateHttpClient` was
    introduced in Symfony 6.3.

Performance
-----------

The component is built for maximum HTTP performance. By design, it is compatible
with HTTP/2 and with doing concurrent asynchronous streamed and multiplexed
requests/responses. Even when doing regular synchronous calls, this design
allows keeping connections to remote hosts open between requests, improving
performance by saving repetitive DNS resolution, SSL negotiation, etc.
To leverage all these design benefits, the cURL extension is needed.

Enabling cURL Support
~~~~~~~~~~~~~~~~~~~~~

This component supports both the native PHP streams and cURL to make the HTTP
requests. Although both are interchangeable and provide the same features,
including concurrent requests, HTTP/2 is only supported when using cURL.

``HttpClient::create()`` selects the cURL transport if the `cURL PHP extension`_
is enabled and falls back to PHP streams otherwise. If you prefer to select
the transport explicitly, use the following classes to create the client::

    use Symfony\Component\HttpClient\CurlHttpClient;
    use Symfony\Component\HttpClient\NativeHttpClient;

    // uses native PHP streams
    $client = new NativeHttpClient();

    // uses the cURL PHP extension
    $client = new CurlHttpClient();

When using this component in a full-stack Symfony application, this behavior is
not configurable and cURL will be used automatically if the cURL PHP extension
is installed and enabled. Otherwise, the native PHP streams will be used.

Configuring CurlHttpClient Options
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

PHP allows to configure lots of `cURL options`_ via the :phpfunction:`curl_setopt`
function. In order to make the component more portable when not using cURL, the
:class:`Symfony\\Component\\HttpClient\\CurlHttpClient` only uses some of those
options (and they are ignored in the rest of clients).

Add an ``extra.curl`` option in your configuration to pass those extra options::

    use Symfony\Component\HttpClient\CurlHttpClient;

    $client = new CurlHttpClient();

    $client->request('POST', 'https://...', [
        // ...
        'extra' => [
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V6,
            ],
        ],
    ]);

.. note::

    Some cURL options are impossible to override (e.g. because of thread safety)
    and you'll get an exception when trying to override them.

HTTP Compression
~~~~~~~~~~~~~~~~

The HTTP header ``Accept-Encoding: gzip`` is added automatically if:

* When using cURL client: cURL was compiled with ZLib support (see ``php --ri curl``)
* When using the native HTTP client: `Zlib PHP extension`_ is installed

If the server does respond with a gzipped response, it's decoded transparently.
To disable HTTP compression, send an ``Accept-Encoding: identity`` HTTP header.

Chunked transfer encoding is enabled automatically if both your PHP runtime and
the remote server supports it.

HTTP/2 Support
~~~~~~~~~~~~~~

When requesting an ``https`` URL, HTTP/2 is enabled by default if one of the
following tools is installed:

* The `libcurl`_ package version 7.36 or higher;
* The `amphp/http-client`_ Packagist package version 4.2 or higher.

To force HTTP/2 for ``http`` URLs, you need to enable it explicitly via the
``http_version`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            http_client:
                default_options:
                    http_version: '2.0'

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
                    <framework:default-options http-version="2.0"/>
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->httpClient()
                ->defaultOptions()
                    ->httpVersion('2.0')
            ;
        };

    .. code-block:: php-standalone

        $client = HttpClient::create(['http_version' => '2.0']);

Support for HTTP/2 PUSH works out of the box when libcurl >= 7.61 is used with
PHP >= 7.2.17 / 7.3.4: pushed responses are put into a temporary cache and are
used when a subsequent request is triggered for the corresponding URLs.

Processing Responses
--------------------

The response returned by all HTTP clients is an object of type
:class:`Symfony\\Contracts\\HttpClient\\ResponseInterface` which provides the
following methods::

    $response = $client->request('GET', 'https://...');

    // gets the HTTP status code of the response
    $statusCode = $response->getStatusCode();

    // gets the HTTP headers as string[][] with the header names lower-cased
    $headers = $response->getHeaders();

    // gets the response body as a string
    $content = $response->getContent();

    // casts the response JSON content to a PHP array
    $content = $response->toArray();

    // casts the response content to a PHP stream resource
    $content = $response->toStream();

    // cancels the request/response
    $response->cancel();

    // returns info coming from the transport layer, such as "response_headers",
    // "redirect_count", "start_time", "redirect_url", etc.
    $httpInfo = $response->getInfo();

    // you can get individual info too
    $startTime = $response->getInfo('start_time');
    // e.g. this returns the final response URL (resolving redirections if needed)
    $url = $response->getInfo('url');

    // returns detailed logs about the requests and responses of the HTTP transaction
    $httpLogs = $response->getInfo('debug');

.. note::

    ``$response->toStream()`` is part of :class:`Symfony\\Component\\HttpClient\\Response\\StreamableInterface`.

.. note::

    ``$response->getInfo()`` is non-blocking: it returns *live* information
    about the response. Some of them might not be known yet (e.g. ``http_code``)
    when you'll call it.

.. _http-client-streaming-responses:

Streaming Responses
~~~~~~~~~~~~~~~~~~~

Call the ``stream()`` method of the HTTP client to get *chunks* of the
response sequentially instead of waiting for the entire response::

    $url = 'https://releases.ubuntu.com/18.04.1/ubuntu-18.04.1-desktop-amd64.iso';
    $response = $client->request('GET', $url);

    // Responses are lazy: this code is executed as soon as headers are received
    if (200 !== $response->getStatusCode()) {
        throw new \Exception('...');
    }

    // get the response content in chunks and save them in a file
    // response chunks implement Symfony\Contracts\HttpClient\ChunkInterface
    $fileHandler = fopen('/ubuntu.iso', 'w');
    foreach ($client->stream($response) as $chunk) {
        fwrite($fileHandler, $chunk->getContent());
    }

.. note::

    By default, ``text/*``, JSON and XML response bodies are buffered in a local
    ``php://temp`` stream. You can control this behavior by using the ``buffer``
    option: set it to ``true``/``false`` to enable/disable buffering, or to a
    closure that should return the same based on the response headers it receives
    as an argument.

Canceling Responses
~~~~~~~~~~~~~~~~~~~

To abort a request (e.g. because it didn't complete in due time, or you want to
fetch only the first bytes of the response, etc.), you can either use the
``cancel()`` method of
:class:`Symfony\\Contracts\\HttpClient\\ResponseInterface`::

    $response->cancel();

Or throw an exception from a progress callback::

    $response = $client->request('GET', 'https://...', [
        'on_progress' => function (int $dlNow, int $dlSize, array $info): void {
            // ...

            throw new \MyException();
        },
    ]);

The exception will be wrapped in an instance of
:class:`Symfony\\Contracts\\HttpClient\\Exception\\TransportExceptionInterface`
and will abort the request.

In case the response was canceled using ``$response->cancel()``,
``$response->getInfo('canceled')`` will return ``true``.

Handling Exceptions
~~~~~~~~~~~~~~~~~~~

There are three types of exceptions, all of which implement the
:class:`Symfony\\Contracts\\HttpClient\\Exception\\ExceptionInterface`:

* Exceptions implementing the :class:`Symfony\\Contracts\\HttpClient\\Exception\\HttpExceptionInterface`
  are thrown when your code does not handle the status codes in the 300-599 range.

* Exceptions implementing the :class:`Symfony\\Contracts\\HttpClient\\Exception\\TransportExceptionInterface`
  are thrown when a lower level issue occurs.

* Exceptions implementing the :class:`Symfony\\Contracts\\HttpClient\\Exception\\DecodingExceptionInterface`
  are thrown when a content-type cannot be decoded to the expected representation.

When the HTTP status code of the response is in the 300-599 range (i.e. 3xx,
4xx or 5xx), the ``getHeaders()``, ``getContent()`` and ``toArray()`` methods
throw an appropriate exception, all of which implement the
:class:`Symfony\\Contracts\\HttpClient\\Exception\\HttpExceptionInterface`.

To opt-out from this exception and deal with 300-599 status codes on your own,
pass ``false`` as the optional argument to every call of those methods,
e.g. ``$response->getHeaders(false);``.

If you do not call any of these 3 methods at all, the exception will still be thrown
when the ``$response`` object is destructed.

Calling ``$response->getStatusCode()`` is enough to disable this behavior
(but then don't miss checking the status code yourself).

While responses are lazy, their destructor will always wait for headers to come
back. This means that the following request *will* complete; and if e.g. a 404
is returned, an exception will be thrown::

    // because the returned value is not assigned to a variable, the destructor
    // of the returned response will be called immediately and will throw if the
    // status code is in the 300-599 range
    $client->request('POST', 'https://...');

This in turn means that unassigned responses will fallback to synchronous requests.
If you want to make these requests concurrent, you can store their corresponding
responses in an array::

    $responses[] = $client->request('POST', 'https://.../path1');
    $responses[] = $client->request('POST', 'https://.../path2');
    // ...

    // This line will trigger the destructor of all responses stored in the array;
    // they will complete concurrently and an exception will be thrown in case a
    // status code in the 300-599 range is returned
    unset($responses);

This behavior provided at destruction-time is part of the fail-safe design of the
component. No errors will be unnoticed: if you don't write the code to handle
errors, exceptions will notify you when needed. On the other hand, if you write
the error-handling code (by calling ``$response->getStatusCode()``), you will
opt-out from these fallback mechanisms as the destructor won't have anything
remaining to do.

Concurrent Requests
-------------------

Thanks to responses being lazy, requests are always managed concurrently.
On a fast enough network, the following code makes 379 requests in less than
half a second when cURL is used::

    $responses = [];
    for ($i = 0; $i < 379; ++$i) {
        $uri = "https://http2.akamai.com/demo/tile-$i.png";
        $responses[] = $client->request('GET', $uri);
    }

    foreach ($responses as $response) {
        $content = $response->getContent();
        // ...
    }

As you can read in the first "for" loop, requests are issued but are not consumed
yet. That's the trick when concurrency is desired: requests should be sent
first and be read later on. This will allow the client to monitor all pending
requests while your code waits for a specific one, as done in each iteration of
the above "foreach" loop.

.. note::

    The maximum number of concurrent requests that you can perform depends on
    the resources of your machine (e.g. your operating system may limit the
    number of simultaneous reads of the file that stores the certificates
    file). Make your requests in batches to avoid these issues.

Multiplexing Responses
~~~~~~~~~~~~~~~~~~~~~~

If you look again at the snippet above, responses are read in requests' order.
But maybe the 2nd response came back before the 1st? Fully asynchronous operations
require being able to deal with the responses in whatever order they come back.

In order to do so, the ``stream()`` method of HTTP clients accepts a list of
responses to monitor. As mentioned :ref:`previously <http-client-streaming-responses>`,
this method yields response chunks as they arrive from the network. By replacing
the "foreach" in the snippet with this one, the code becomes fully async::

    foreach ($client->stream($responses) as $response => $chunk) {
        if ($chunk->isFirst()) {
            // headers of $response just arrived
            // $response->getHeaders() is now a non-blocking call
        } elseif ($chunk->isLast()) {
            // the full content of $response just completed
            // $response->getContent() is now a non-blocking call
        } else {
            // $chunk->getContent() will return a piece
            // of the response body that just arrived
        }
    }

.. tip::

    Use the ``user_data`` option combined with ``$response->getInfo('user_data')``
    to track the identity of the responses in your foreach loops.

Dealing with Network Timeouts
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This component allows dealing with both request and response timeouts.

A timeout can happen when e.g. DNS resolution takes too much time, when the TCP
connection cannot be opened in the given time budget, or when the response
content pauses for too long. This can be configured with the ``timeout`` request
option::

    // A TransportExceptionInterface will be issued if nothing
    // happens for 2.5 seconds when accessing from the $response
    $response = $client->request('GET', 'https://...', ['timeout' => 2.5]);

The ``default_socket_timeout`` PHP ini setting is used if the option is not set.

The option can be overridden by using the 2nd argument of the ``stream()`` method.
This allows monitoring several responses at once and applying the timeout to all
of them in a group. If all responses become inactive for the given duration, the
method will yield a special chunk whose ``isTimeout()`` will return ``true``::

    foreach ($client->stream($responses, 1.5) as $response => $chunk) {
        if ($chunk->isTimeout()) {
            // $response stale for more than 1.5 seconds
        }
    }

A timeout is not necessarily an error: you can decide to stream again the
response and get remaining contents that might come back in a new timeout, etc.

.. tip::

    Passing ``0`` as timeout allows monitoring responses in a non-blocking way.

.. note::

    Timeouts control how long one is willing to wait *while the HTTP transaction
    is idle*. Big responses can last as long as needed to complete, provided they
    remain active during the transfer and never pause for longer than specified.

    Use the ``max_duration`` option to limit the time a full request/response can last.

.. _http-client_network-errors:

Dealing with Network Errors
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Network errors (broken pipe, failed DNS resolution, etc.) are thrown as instances
of :class:`Symfony\\Contracts\\HttpClient\\Exception\\TransportExceptionInterface`.

First of all, you don't *have* to deal with them: letting errors bubble to your
generic exception-handling stack might be really fine in most use cases.

If you want to handle them, here is what you need to know:

To catch errors, you need to wrap calls to ``$client->request()`` but also calls
to any methods of the returned responses. This is because responses are lazy, so
that network errors can happen when calling e.g. ``getStatusCode()`` too::

    use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

    // ...
    try {
        // both lines can potentially throw
        $response = $client->request(/* ... */);
        $headers = $response->getHeaders();
        // ...
    } catch (TransportExceptionInterface $e) {
        // ...
    }

.. note::

    Because ``$response->getInfo()`` is non-blocking, it shouldn't throw by design.

When multiplexing responses, you can deal with errors for individual streams by
catching :class:`Symfony\\Contracts\\HttpClient\\Exception\\TransportExceptionInterface`
in the foreach loop::

    foreach ($client->stream($responses) as $response => $chunk) {
        try {
            if ($chunk->isTimeout()) {
                // ... decide what to do when a timeout occurs
                // if you want to stop a response that timed out, don't miss
                // calling $response->cancel() or the destructor of the response
                // will try to complete it one more time
            } elseif ($chunk->isFirst()) {
                // if you want to check the status code, you must do it when the
                // first chunk arrived, using $response->getStatusCode();
                // not doing so might trigger an HttpExceptionInterface
            } elseif ($chunk->isLast()) {
                // ... do something with $response
            }
        } catch (TransportExceptionInterface $e) {
            // ...
        }
    }

Caching Requests and Responses
------------------------------

This component provides a :class:`Symfony\\Component\\HttpClient\\CachingHttpClient`
decorator that allows caching responses and serving them from the local storage
for next requests. The implementation leverages the
:class:`Symfony\\Component\\HttpKernel\\HttpCache\\HttpCache` class under the hood
so that the :doc:`HttpKernel component </components/http_kernel>` needs to be
installed in your application::

    use Symfony\Component\HttpClient\CachingHttpClient;
    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\HttpKernel\HttpCache\Store;

    $store = new Store('/path/to/cache/storage/');
    $client = HttpClient::create();
    $client = new CachingHttpClient($client, $store);

    // this won't hit the network if the resource is already in the cache
    $response = $client->request('GET', 'https://example.com/cacheable-resource');

``CachingHttpClient`` accepts a third argument to set the options of the ``HttpCache``.

Consuming Server-Sent Events
----------------------------

`Server-sent events`_ is an Internet standard used to push data to web pages.
Its JavaScript API is built around an `EventSource`_ object, which listens to
the events sent from some URL. The events are a stream of data (served with the
``text/event-stream`` MIME type) with the following format:

.. code-block:: text

    data: This is the first message.

    data: This is the second message, it
    data: has two lines.

    data: This is the third message.

Symfony's HTTP client provides an EventSource implementation to consume these
server-sent events. Use the :class:`Symfony\\Component\\HttpClient\\EventSourceHttpClient`
to wrap your HTTP client, open a connection to a server that responds with a
``text/event-stream`` content type and consume the stream as follows::

    use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
    use Symfony\Component\HttpClient\EventSourceHttpClient;

    // the second optional argument is the reconnection time in seconds (default = 10)
    $client = new EventSourceHttpClient($client, 10);
    $source = $client->connect('https://localhost:8080/events');
    while ($source) {
        foreach ($client->stream($source, 2) as $r => $chunk) {
            if ($chunk->isTimeout()) {
                // ...
                continue;
            }

            if ($chunk->isLast()) {
                // ...

                return;
            }

            // this is a special ServerSentEvent chunk holding the pushed message
            if ($chunk instanceof ServerSentEvent) {
                // do something with the server event ...
            }
        }
    }

.. tip::

    If you know that the content of the ``ServerSentEvent`` is in the JSON format, you can
    use the :method:`Symfony\\Component\\HttpClient\\Chunk\\ServerSentEvent::getArrayData`
    method to directly get the decoded JSON as array.

.. versionadded:: 6.3

    The ``ServerSentEvent::getArrayData()`` method was introduced in Symfony 6.3.

Interoperability
----------------

The component is interoperable with four different abstractions for HTTP
clients: `Symfony Contracts`_, `PSR-18`_, `HTTPlug`_ v1/v2 and native PHP streams.
If your application uses libraries that need any of them, the component is compatible
with all of them. They also benefit from :ref:`autowiring aliases <service-autowiring-alias>`
when the :ref:`framework bundle <framework-bundle-configuration>` is used.

If you are writing or maintaining a library that makes HTTP requests, you can
decouple it from any specific HTTP client implementations by coding against
either Symfony Contracts (recommended), PSR-18 or HTTPlug v2.

Symfony Contracts
~~~~~~~~~~~~~~~~~

The interfaces found in the ``symfony/http-client-contracts`` package define
the primary abstractions implemented by the component. Its entry point is the
:class:`Symfony\\Contracts\\HttpClient\\HttpClientInterface`. That's the
interface you need to code against when a client is needed::

    use Symfony\Contracts\HttpClient\HttpClientInterface;

    class MyApiLayer
    {
        public function __construct(
            private HttpClientInterface $client,
        ) {
        }

        // [...]
    }

All request options mentioned above (e.g. timeout management) are also defined
in the wordings of the interface, so that any compliant implementations (like
this component) is guaranteed to provide them. That's a major difference with
the other abstractions, which provide none related to the transport itself.

Another major feature covered by the Symfony Contracts is async/multiplexing,
as described in the previous sections.

PSR-18 and PSR-17
~~~~~~~~~~~~~~~~~

This component implements the `PSR-18`_ (HTTP Client) specifications via the
:class:`Symfony\\Component\\HttpClient\\Psr18Client` class, which is an adapter
to turn a Symfony :class:`Symfony\\Contracts\\HttpClient\\HttpClientInterface`
into a PSR-18 ``ClientInterface``. This class also implements the relevant
methods of `PSR-17`_ to ease creating request objects.

To use it, you need the ``psr/http-client`` package and a `PSR-17`_ implementation:

.. code-block:: terminal

    # installs the PSR-18 ClientInterface
    $ composer require psr/http-client

    # installs an efficient implementation of response and stream factories
    # with autowiring aliases provided by Symfony Flex
    $ composer require nyholm/psr7

    # alternatively, install the php-http/discovery package to auto-discover
    # any already installed implementations from common vendors:
    # composer require php-http/discovery

Now you can make HTTP requests with the PSR-18 client as follows:

.. configuration-block::

    .. code-block:: php-symfony

        use Psr\Http\Client\ClientInterface;

        class Symfony
        {
            public function __construct(
                private ClientInterface $client,
            ) {
            }

            public function getAvailableVersions(): array
            {
                $request = $this->client->createRequest('GET', 'https://symfony.com/versions.json');
                $response = $this->client->sendRequest($request);

                return json_decode($response->getBody()->getContents(), true);
            }
        }

    .. code-block:: php-standalone

        use Symfony\Component\HttpClient\Psr18Client;

        $client = new Psr18Client();

        $request = $client->createRequest('GET', 'https://symfony.com/versions.json');
        $response = $client->sendRequest($request);

        $content = json_decode($response->getBody()->getContents(), true);

You can also pass a set of default options to your client thanks to the
``Psr18Client::withOptions()`` method::

    use Symfony\Component\HttpClient\Psr18Client;

    $client = (new Psr18Client())
        ->withOptions([
            'base_uri' => 'https://symfony.com',
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

    $request = $client->createRequest('GET', '/versions.json');

    // ...

.. versionadded:: 6.2

    The ``Psr18Client::withOptions()`` method was introduced in Symfony 6.2.

HTTPlug
~~~~~~~

The `HTTPlug`_ v1 specification was published before PSR-18 and is superseded by
it. As such, you should not use it in newly written code. The component is still
interoperable with libraries that require it thanks to the
:class:`Symfony\\Component\\HttpClient\\HttplugClient` class. Similarly to
:class:`Symfony\\Component\\HttpClient\\Psr18Client`, ``HttplugClient`` also
implements relevant parts of PSR-17.

If you'd like to work with promises, ``HttplugClient`` implements the
``HttpAsyncClient`` interface. To use it, you need to install the
``guzzlehttp/promises`` package:

.. code-block:: terminal

    $ composer require guzzlehttp/promises

Then you're ready to go::

    use Psr\Http\Message\ResponseInterface;
    use Symfony\Component\HttpClient\HttplugClient;

    $httpClient = new HttplugClient();
    $request = $httpClient->createRequest('GET', 'https://my.api.com/');
    $promise = $httpClient->sendAsyncRequest($request)
        ->then(
            function (ResponseInterface $response) {
                echo 'Got status '.$response->getStatusCode();

                return $response;
            },
            function (\Throwable $exception) {
                echo 'Error: '.$exception->getMessage();

                throw $exception;
            }
        );

    // after you're done with sending several requests,
    // you must wait for them to complete concurrently

    // wait for a specific promise to resolve while monitoring them all
    $response = $promise->wait();

    // wait maximum 1 second for pending promises to resolve
    $httpClient->wait(1.0);

    // wait for all remaining promises to resolve
    $httpClient->wait();

You can also pass a set of default options to your client thanks to the
``HttplugClient::withOptions()`` method::

    use Psr\Http\Message\ResponseInterface;
    use Symfony\Component\HttpClient\HttplugClient;

    $httpClient = (new HttplugClient())
        ->withOptions([
            'base_uri' => 'https://my.api.com',
        ]);
    $request = $httpClient->createRequest('GET', '/');

    // ...

.. versionadded:: 6.2

    The ``HttplugClient::withOptions()`` method was introduced in Symfony 6.2.

Native PHP Streams
~~~~~~~~~~~~~~~~~~

Responses implementing :class:`Symfony\\Contracts\\HttpClient\\ResponseInterface`
can be cast to native PHP streams with
:method:`Symfony\\Component\\HttpClient\\Response\\StreamWrapper::createResource`.
This allows using them where native PHP streams are needed::

    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\HttpClient\Response\StreamWrapper;

    $client = HttpClient::create();
    $response = $client->request('GET', 'https://symfony.com/versions.json');

    $streamResource = StreamWrapper::createResource($response, $client);

    // alternatively and contrary to the previous one, this returns
    // a resource that is seekable and potentially stream_select()-able
    $streamResource = $response->toStream();

    echo stream_get_contents($streamResource); // outputs the content of the response

    // later on if you need to, you can access the response from the stream
    $response = stream_get_meta_data($streamResource)['wrapper_data']->getResponse();

Extensibility
-------------

If you want to extend the behavior of a base HTTP client, you can use
:doc:`service decoration </service_container/service_decoration>`::

    class MyExtendedHttpClient implements HttpClientInterface
    {
        public function __construct(
            private HttpClientInterface $decoratedClient = null
        ) {
            $this->decoratedClient ??= HttpClient::create();
        }

        public function request(string $method, string $url, array $options = []): ResponseInterface
        {
            // process and/or change the $method, $url and/or $options as needed
            $response = $this->decoratedClient->request($method, $url, $options);

            // if you call here any method on $response, the HTTP request
            // won't be async; see below for a better way

            return $response;
        }

        public function stream($responses, float $timeout = null): ResponseStreamInterface
        {
            return $this->decoratedClient->stream($responses, $timeout);
        }
    }

A decorator like this one is useful in cases where processing the requests'
arguments is enough. By decorating the ``on_progress`` option, you can
even implement basic monitoring of the response. However, since calling
responses' methods forces synchronous operations, doing so inside ``request()``
will break async.

The solution is to also decorate the response object itself.
:class:`Symfony\\Component\\HttpClient\\TraceableHttpClient` and
:class:`Symfony\\Component\\HttpClient\\Response\\TraceableResponse` are good
examples as a starting point.

In order to help writing more advanced response processors, the component provides
an :class:`Symfony\\Component\\HttpClient\\AsyncDecoratorTrait`. This trait allows
processing the stream of chunks as they come back from the network::

    class MyExtendedHttpClient implements HttpClientInterface
    {
        use AsyncDecoratorTrait;

        public function request(string $method, string $url, array $options = []): ResponseInterface
        {
            // process and/or change the $method, $url and/or $options as needed

            $passthru = function (ChunkInterface $chunk, AsyncContext $context) {
                // do what you want with chunks, e.g. split them
                // in smaller chunks, group them, skip some, etc.

                yield $chunk;
            };

            return new AsyncResponse($this->client, $method, $url, $options, $passthru);
        }
    }

Because the trait already implements a constructor and the ``stream()`` method,
you don't need to add them. The ``request()`` method should still be defined;
it shall return an
:class:`Symfony\\Component\\HttpClient\\Response\\AsyncResponse`.

The custom processing of chunks should happen in ``$passthru``: this generator
is where you need to write your logic. It will be called for each chunk yielded
by the underlying client. A ``$passthru`` that does nothing would just ``yield
$chunk;``. You could also yield a modified chunk, split the chunk into many
ones by yielding several times, or even skip a chunk altogether by issuing a
``return;`` instead of yielding.

In order to control the stream, the chunk passthru receives an
:class:`Symfony\\Component\\HttpClient\\Response\\AsyncContext` as second
argument. This context object has methods to read the current state of the
response. It also allows altering the response stream with methods to create
new chunks of content, pause the stream, cancel the stream, change the info of
the response, replace the current request by another one or change the chunk
passthru itself.

Checking the test cases implemented in
:class:`Symfony\\Component\\HttpClient\\Tests\\AsyncDecoratorTraitTest`
might be a good start to get various working examples for a better understanding.
Here are the use cases that it simulates:

* retry a failed request;
* send a preflight request, e.g. for authentication needs;
* issue subrequests and include their content in the main response's body.

The logic in :class:`Symfony\\Component\\HttpClient\\Response\\AsyncResponse`
has many safety checks that will throw a ``LogicException`` if the chunk
passthru doesn't behave correctly; e.g. if a chunk is yielded after an ``isLast()``
one, or if a content chunk is yielded before an ``isFirst()`` one, etc.

Testing
-------

This component includes the :class:`Symfony\\Component\\HttpClient\\MockHttpClient`
and :class:`Symfony\\Component\\HttpClient\\Response\\MockResponse` classes to use
in tests that shouldn't make actual HTTP requests. Such tests can be useful, as they
will run faster and produce consistent results, since they're not dependent on an
external service. By not making actual HTTP requests there is no need to worry about
the service being online or the request changing state, for example deleting
a resource.

``MockHttpClient`` implements the ``HttpClientInterface``, just like any actual
HTTP client in this component. When you type-hint with ``HttpClientInterface``
your code will accept the real client outside tests, while replacing it with
``MockHttpClient`` in the test.

When the ``request`` method is used on ``MockHttpClient``, it will respond with
the supplied ``MockResponse``. There are a few ways to use it, as described
below.

HTTP Client and Responses
~~~~~~~~~~~~~~~~~~~~~~~~~

The first way of using ``MockHttpClient`` is to pass a list of responses to its
constructor. These will be yielded in order when requests are made::

    use Symfony\Component\HttpClient\MockHttpClient;
    use Symfony\Component\HttpClient\Response\MockResponse;

    $responses = [
        new MockResponse($body1, $info1),
        new MockResponse($body2, $info2),
    ];

    $client = new MockHttpClient($responses);
    // responses are returned in the same order as passed to MockHttpClient
    $response1 = $client->request('...'); // returns $responses[0]
    $response2 = $client->request('...'); // returns $responses[1]

Another way of using ``MockHttpClient`` is to pass a callback that generates the
responses dynamically when it's called::

    use Symfony\Component\HttpClient\MockHttpClient;
    use Symfony\Component\HttpClient\Response\MockResponse;

    $callback = function ($method, $url, $options) {
        return new MockResponse('...');
    };

    $client = new MockHttpClient($callback);
    $response = $client->request('...'); // calls $callback to get the response

You can also pass a list of callbacks if you need to perform specific
assertions on the request before returning the mocked response::

    $expectedRequests = [
        function ($method, $url, $options) {
            $this->assertSame('GET', $method);
            $this->assertSame('https://example.com/api/v1/customer', $url);

            return new MockResponse('...');
        },
        function ($method, $url, $options) {
            $this->assertSame('POST', $method);
            $this->assertSame('https://example.com/api/v1/customer/1/products', $url);

            return new MockResponse('...');
        },
    ];

    $client = new MockHttpClient($expectedRequest);

    // ...

.. tip::

    Instead of using the first argument, you can also set the (list of)
    responses or callbacks using the ``setResponseFactory()`` method::

        $responses = [
            new MockResponse($body1, $info1),
            new MockResponse($body2, $info2),
        ];

        $client = new MockHttpClient();
        $client->setResponseFactory($responses);

If you need to test responses with HTTP status codes different than 200,
define the ``http_code`` option::

    use Symfony\Component\HttpClient\MockHttpClient;
    use Symfony\Component\HttpClient\Response\MockResponse;

    $client = new MockHttpClient([
        new MockResponse('...', ['http_code' => 500]),
        new MockResponse('...', ['http_code' => 404]),
    ]);

    $response = $client->request('...');

The responses provided to the mock client don't have to be instances of
``MockResponse``. Any class implementing ``ResponseInterface`` will work (e.g.
``$this->createMock(ResponseInterface::class)``).

However, using ``MockResponse`` allows simulating chunked responses and timeouts::

    $body = function () {
        yield 'hello';
        // empty strings are turned into timeouts so that they are easy to test
        yield '';
        yield 'world';
    };

    $mockResponse = new MockResponse($body());

Finally, you can also create an invokable or iterable class that generates the
responses and use it as a callback in functional tests::

    namespace App\Tests;

    use Symfony\Component\HttpClient\Response\MockResponse;
    use Symfony\Contracts\HttpClient\ResponseInterface;

    class MockClientCallback
    {
        public function __invoke(string $method, string $url, array $options = []): ResponseInterface
        {
            // load a fixture file or generate data
            // ...
            return new MockResponse($data);
        }
    }

Then configure Symfony to use your callback:

.. configuration-block::

    .. code-block:: yaml

        # config/services_test.yaml
        services:
            # ...
            App\Tests\MockClientCallback: ~

        # config/packages/test/framework.yaml
        framework:
            http_client:
                mock_response_factory: App\Tests\MockClientCallback

    .. code-block:: xml

        <!-- config/services_test.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Tests\MockClientCallback"/>
            </services>
        </container>

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:http-client mock-response-factory="App\Tests\MockClientCallback">
                    <!-- ... -->
                </framework-http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->httpClient()
                ->mockResponseFactory(MockClientCallback::class)
            ;
        };

To return json, you would normally do::

    use Symfony\Component\HttpClient\Response\MockResponse;

    $response = new MockResponse(json_encode([
            'foo' => 'bar',
        ]), [
        'response_headers' => [
            'content-type' => 'application/json',
        ],
    ]);

You can use :class:`Symfony\\Component\\HttpClient\\Response\\JsonMockResponse` instead::

    use Symfony\Component\HttpClient\Response\JsonMockResponse;

    $response = new JsonMockResponse([
        'foo' => 'bar',
    ]);

.. versionadded:: 6.3

    The ``JsonMockResponse`` was introduced in Symfony 6.3.

Testing Request Data
~~~~~~~~~~~~~~~~~~~~

The ``MockResponse`` class comes with some helper methods to test the request:

* ``getRequestMethod()`` - returns the HTTP method;
* ``getRequestUrl()`` - returns the URL the request would be sent to;
* ``getRequestOptions()`` - returns an array containing other information about
  the request such as headers, query parameters, body content etc.

Usage example::

    $mockResponse = new MockResponse('', ['http_code' => 204]);
    $httpClient = new MockHttpClient($mockResponse, 'https://example.com');

    $response = $httpClient->request('DELETE', 'api/article/1337', [
        'headers' => [
            'Accept: */*',
            'Authorization: Basic YWxhZGRpbjpvcGVuc2VzYW1l',
        ],
    ]);

    $mockResponse->getRequestMethod();
    // returns "DELETE"

    $mockResponse->getRequestUrl();
    // returns "https://example.com/api/article/1337"

    $mockResponse->getRequestOptions()['headers'];
    // returns ["Accept: */*", "Authorization: Basic YWxhZGRpbjpvcGVuc2VzYW1l"]

Full Example
~~~~~~~~~~~~

The following standalone example demonstrates a way to use the HTTP client and
test it in a real application::

    // ExternalArticleService.php
    use Symfony\Contracts\HttpClient\HttpClientInterface;

    final class ExternalArticleService
    {
        public function __construct(
            private HttpClientInterface $httpClient,
        ) {
        }

        public function createArticle(array $requestData): array
        {
            $requestJson = json_encode($requestData, JSON_THROW_ON_ERROR);

            $response = $this->httpClient->request('POST', 'api/article', [
                'headers' => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                'body' => $requestJson,
            ]);

            if (201 !== $response->getStatusCode()) {
                throw new Exception('Response status code is different than expected.');
            }

            // ... other checks

            $responseJson = $response->getContent();
            $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

            return $responseData;
        }
    }

    // ExternalArticleServiceTest.php
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\HttpClient\MockHttpClient;
    use Symfony\Component\HttpClient\Response\MockResponse;

    final class ExternalArticleServiceTest extends TestCase
    {
        public function testSubmitData(): void
        {
            // Arrange
            $requestData = ['title' => 'Testing with Symfony HTTP Client'];
            $expectedRequestData = json_encode($requestData, JSON_THROW_ON_ERROR);

            $expectedResponseData = ['id' => 12345];
            $mockResponseJson = json_encode($expectedResponseData, JSON_THROW_ON_ERROR);
            $mockResponse = new MockResponse($mockResponseJson, [
                'http_code' => 201,
                'response_headers' => ['Content-Type: application/json'],
            ]);

            $httpClient = new MockHttpClient($mockResponse, 'https://example.com');
            $service = new ExternalArticleService($httpClient);

            // Act
            $responseData = $service->createArticle($requestData);

            // Assert
            self::assertSame('POST', $mockResponse->getRequestMethod());
            self::assertSame('https://example.com/api/article', $mockResponse->getRequestUrl());
            self::assertContains(
                'Content-Type: application/json',
                $mockResponse->getRequestOptions()['headers']
            );
            self::assertSame($expectedRequestData, $mockResponse->getRequestOptions()['body']);

            self::assertSame($responseData, $expectedResponseData);
        }
    }

Testing Network Transport Exceptions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As explained in the :ref:`Network Errors section <http-client_network-errors>`,
when making HTTP requests you might face errors at transport level.

That's why it's useful to test how your application behaves in case of a transport
error. :class:`Symfony\\Component\\HttpClient\\Response\\MockResponse` allows
you to do so, by yielding the exception from its body::

    // ExternalArticleServiceTest.php
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\HttpClient\MockHttpClient;
    use Symfony\Component\HttpClient\Response\MockResponse;

    final class ExternalArticleServiceTest extends TestCase
    {
        // ...

        public function testTransportLevelError(): void
        {
            $requestData = ['title' => 'Testing with Symfony HTTP Client'];
            $httpClient = new MockHttpClient([
                // You can create the exception directly in the body...
                new MockResponse([new \RuntimeException('Error at transport level')]),

                // ... or you can yield the exception from a callback
                new MockResponse((static function (): \Generator {
                    yield new TransportException('Error at transport level');
                })()),
            ]);

            $service = new ExternalArticleService($httpClient);

            try {
                $service->createArticle($requestData);

                // An exception should have been thrown in `createArticle()`, so this line should never be reached
                $this->fail();
            } catch (TransportException $e) {
                $this->assertEquals(new \RuntimeException('Error at transport level'), $e->getPrevious());
                $this->assertSame('Error at transport level', $e->getMessage());
            }
        }
    }

.. versionadded:: 6.1

    Being allowed to pass an exception directly to the body of a
    :class:`Symfony\\Component\\HttpClient\\Response\\MockResponse` was
    introduced in Symfony 6.1.

.. _`cURL PHP extension`: https://www.php.net/curl
.. _`Zlib PHP extension`: https://www.php.net/zlib
.. _`PSR-17`: https://www.php-fig.org/psr/psr-17/
.. _`PSR-18`: https://www.php-fig.org/psr/psr-18/
.. _`HTTPlug`: https://github.com/php-http/httplug/#readme
.. _`Symfony Contracts`: https://github.com/symfony/contracts
.. _`libcurl`: https://curl.haxx.se/libcurl/
.. _`amphp/http-client`: https://packagist.org/packages/amphp/http-client
.. _`cURL options`: https://www.php.net/manual/en/function.curl-setopt.php
.. _`Server-sent events`: https://html.spec.whatwg.org/multipage/server-sent-events.html
.. _`EventSource`: https://www.w3.org/TR/eventsource/#eventsource
.. _`idempotent method`: https://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol#Idempotent_methods
.. _`SSRF`: https://portswigger.net/web-security/ssrf
.. _`RFC 6570`: https://www.rfc-editor.org/rfc/rfc6570
