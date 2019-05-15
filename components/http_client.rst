.. index::
   single: HttpClient
   single: Components; HttpClient

The HttpClient Component
========================

    The HttpClient component is a low-level HTTP client with support for both
    PHP stream wrappers and cURL. It also provides utilities to consume APIs.

.. versionadded:: 4.3

    The HttpClient component was introduced in Symfony 4.3.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/http-client

.. include:: /components/require_autoload.rst.inc

Basic Usage
-----------

Use the :class:`Symfony\\Component\\HttpClient\\HttpClient` class to create the
low-level HTTP client that makes requests, like the following ``GET`` request::

    use Symfony\Component\HttpClient\HttpClient;

    $httpClient = HttpClient::create();
    $response = $httpClient->request('GET', 'https://api.github.com/repos/symfony/symfony-docs');

    $statusCode = $response->getStatusCode();
    // $statusCode = 200
    $contentType = $response->getHeaders()['content-type'][0];
    // $contentType = 'application/json'
    $content = $response->getContent();
    // $content = '{"id":521583, "name":"symfony-docs", ...}'
    $content = $response->toArray();
    // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

Enabling cURL Support
---------------------

This component supports both the native PHP streams and cURL to make the HTTP
requests. Although both are interchangeable and provide the same features,
including concurrent requests, HTTP/2 is only supported when using cURL.

``HttpClient::create()`` selects the cURL transport if the `cURL PHP extension`_
is enabled and falls back to PHP streams otherwise. If you prefer to select
the transport explicitly, use the following classes to create the client::

    use Symfony\Component\HttpClient\CurlHttpClient;
    use Symfony\Component\HttpClient\NativeHttpClient;

    // uses native PHP streams
    $httpClient = new NativeHttpClient();

    // uses the cURL PHP extension
    $httpClient = new CurlHttpClient();

When using this component in a full-stack Symfony application, this behavior is
not configurable and cURL will be used automatically if the cURL PHP extension
is installed and enabled. Otherwise, the native PHP streams will be used.

Enabling HTTP/2 Support
-----------------------

HTTP/2 is only supported when using the cURL-based transport and the libcurl
version is >= 7.36.0. If you meet these requirements, you can enable HTTP/2
explicitly via the ``http_version`` option::

    $httpClient = HttpClient::create(['http_version' => '2.0']);

If you don't set the HTTP version explicitly, Symfony will use ``'2.0'`` only
when the request protocol is ``https://`` (and the cURL requirements mentioned
earlier are met).

Making Requests
---------------

The client created with the ``HttpClient`` class provides a single ``request()``
method to perform all kinds of HTTP requests::

    $response = $httpClient->request('GET', 'https://...');
    $response = $httpClient->request('POST', 'https://...');
    $response = $httpClient->request('PUT', 'https://...');
    // ...

Responses are always asynchronous, so they are ready as soon as the response
HTTP headers are received, instead of waiting to receive the entire response
contents::

    $response = $httpClient->request('GET', 'http://releases.ubuntu.com/18.04.2/ubuntu-18.04.2-desktop-amd64.iso');

    // code execution continues immediately; it doesn't wait to receive the response
    // you can get the value of any HTTP response header
    $contentType = $response->getHeaders()['content-type'][0];

    // trying to get the response contents will block the execution until
    // the full response contents are received
    $contents = $response->getContent();

This component also supports :ref:`streaming responses <http-client-streaming-responses>`
for full asynchronous applications.

Authentication
~~~~~~~~~~~~~~

The HTTP client supports different authentication mechanisms. They can be
defined globally when creating the client (to apply it to all requests) and to
each request (which overrides any global authentication)::

    // Use the same authentication for all requests
    $httpClient = HttpClient::create([
        // HTTP Basic authentication with only the username and not a password
        'auth_basic' => ['the-username'],

        // HTTP Basic authentication with a username and a password
        'auth_basic' => ['the-username', 'the-password'],

        // HTTP Bearer authentication (also called token authentication)
        'auth_bearer' => 'the-bearer-token',
    ]);

    $response = $httpClient->request('GET', 'https://...', [
        // use a different HTTP Basic authentication only for this request
        'auth_basic' => ['the-username', 'the-password'],

        // ...
    ]);

Query String Parameters
~~~~~~~~~~~~~~~~~~~~~~~

You can either append them manually to the requested URL, or better, add them
as an associative array to the ``query`` option::

    // it makes an HTTP GET request to https://httpbin.org/get?token=...&name=...
    $response = $httpClient->request('GET', 'https://httpbin.org/get', [
        // these values are automatically encoded before including them in the URL
        'query' => [
            'token' => '...',
            'name' => '...',
        ],
    ]);

Headers
~~~~~~~

Use the ``headers`` option to define both the default headers added to all
requests and the specific headers for each request::

    // this header is added to all requests made by this client
    $httpClient = HttpClient::create(['headers' => [
        'Accept-Encoding' => 'gzip',
    ]]);

    // this header is only included in this request and overrides the value
    // of the same header if defined globally by the HTTP client
    $response = $httpClient->request('POST', 'https://...', [
        'headers' => [
            'Content-Type' => 'text/plain',
        ],
    ]);

Uploading Data
~~~~~~~~~~~~~~

This component provides several methods for uploading data using the ``body``
option. You can use regular strings, closures and resources and they'll be
processed automatically when making the requests::

    $response = $httpClient->request('POST', 'https://...', [
        // defining data using a regular string
        'body' => 'raw data',

        // defining data using an array of parameters
        'body' => ['parameter1' => 'value1', '...'],

        // using a closure to generate the uploaded data
        'body' => function () {
            // ...
        },

        // using a resource to get the data from it
        'body' => fopen('/path/to/file', 'r'),
    ]);

When uploading data with the ``POST`` method, if you don't define the
``Content-Type`` HTTP header explicitly, Symfony assumes that you're uploading
form data and adds the required
``'Content-Type: application/x-www-form-urlencoded'`` header for you.

When uploading JSON payloads, use the ``json`` option instead of ``body``. The
given content will be JSON-encoded automatically and the request will add the
``Content-Type: application/json`` automatically too::

    $response = $httpClient->request('POST', 'https://...', [
        'json' => ['param1' => 'value1', '...'],
    ]);

Cookies
~~~~~~~

The HTTP client provided by this component is stateless but handling cookies
requires a stateful storage (because responses can update cookies and they must
be used for subsequent requests). That's why this component doesn't handle
cookies automatically.

You can either handle cookies yourself using the ``Cookie`` HTTP header or use
the :doc:`BrowserKit component </components/browser_kit>` which provides this
feature and integrates seamlessly with the HttpClient component.

Redirects
~~~~~~~~~

By default, the HTTP client follows redirects, up to a maximum of 20, when
making a request. Use the ``max_redirects`` setting to configure this behavior
(if the number of redirects is higher than the configured value, you'll get a
:class:`Symfony\\Component\\HttpClient\\Exception\\RedirectionException`)::

    $response = $httpClient->request('GET', 'https://...', [
        // 0 means to not follow any redirect
        'max_redirects' => 0,
    ]);

.. Concurrent Requests
.. ~~~~~~~~~~~~~~~~~~~
..
..
.. TODO
..
..

Processing Responses
--------------------

The response returned by all HTTP clients is an object of type
:class:`Symfony\\Contracts\\HttpClient\\ResponseInterface` which provides the
following methods::

    $response = $httpClient->request('GET', 'https://...');

    // gets the HTTP status code of the response
    $statusCode = $response->getStatusCode();

    // gets the HTTP headers as string[][] with the header names lower-cased
    $headers = $response->getHeaders();

    // gets the response body as a string
    $content = $response->getContent();

    // returns info coming from the transport layer, such as "response_headers",
    // "redirect_count", "start_time", "redirect_url", etc.
    $httpInfo = $response->getInfo();
    // you can get individual info too
    $startTime = $response->getInfo('start_time');

.. _http-client-streaming-responses:

Streaming Responses
~~~~~~~~~~~~~~~~~~~

Call to the ``stream()`` method of the HTTP client to get *chunks* of the
response sequentially instead of waiting for the entire response::

    $url = 'https://releases.ubuntu.com/18.04.1/ubuntu-18.04.1-desktop-amd64.iso';
    $response = $httpClient->request('GET', $url, [
        // optional: if you don't want to buffer the response in memory
        'buffer' => false,
        // optional: to display details about the response progress
        'on_progress' => function (int $dlNow, int $dlSize, array $info): void {
            // ...
        },
    ]);

    // Responses are lazy: this code is executed as soon as headers are received
    if (200 !== $response->getStatusCode()) {
        throw new \Exception('...');
    }

    // get the response contents in chunk and save them in a file
    // response chunks implement Symfony\Contracts\HttpClient\ChunkInterface
    $fileHandler = fopen('/ubuntu.iso', 'w');
    foreach ($httpClient->stream($response) as $chunk) {
        fwrite($fileHandler, $chunk->getContent(););
    }

Handling Exceptions
~~~~~~~~~~~~~~~~~~~

When the HTTP status code of the response is not in the 200-299 range (i.e. 3xx,
4xx or 5xx) your code is expected to handle it. If you don't do that, the
``getHeaders()`` and ``getContent()`` methods throw an appropriate exception::

    // the response of this request will be a 403 HTTP error
    $response = $httpClient->request('GET', 'https://httpbin.org/status/403');

    // this code results in a Symfony\Component\HttpClient\Exception\ClientException
    // because it doesn't check the status code of the response
    $content = $response->getContent();

    // pass FALSE as the optional argument to not throw an exception and
    // return instead the response content even for errors
    $content = $response->getContent(false);

Caching Requests and Responses
------------------------------

This component provides a special HTTP client via the
:class:`Symfony\\Component\\HttpClient\\CachingHttpClient` class to cache
requests and their responses. The actual HTTP caching is implemented using the
:doc:`HttpKernel component </components/http_kernel>`, so make sure it's
installed in your application.

..
.. TODO:
.. Show some example of caching requests+responses
..
..

Scoping Client
--------------

It's common that some of the HTTP client options depend on the URL of the
request (e.g. you must set some headers when making requests to GitHub API but
not for other hosts). If that's your case, this component provides a special
HTTP client via the :class:`Symfony\\Component\\HttpClient\\ScopingHttpClient`
class to autoconfigure the HTTP client based on the requested URL::

    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\HttpClient\ScopingHttpClient;

    $client = HttpClient::create();
    $httpClient = new ScopingHttpClient($client, [
        // the key is a regexp which must match the beginning of the request URL
        'https://api\.github\.com/' => [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'token '.$githubToken,
            ],
        ],

        // use a '*' wildcard to apply some options to all requests
        '*' => [
            // ...
        ]
    ]);

If the request URL is relative (because you use the ``base_uri`` option), the
scoping HTTP client can't make a match. That's why you can define a third
optional argument in its constructor which will be considered the default
regular expression applied to relative URLs::

    // ...

    $httpClient = new ScopingHttpClient($client, [
        'https://api\.github\.com/' => [
            'base_uri' => 'https://api.github.com/',
            // ...
        ],

        '*' => [
            // ...
        ]
    ],
        // this is the regexp applied to all relative URLs
        'https://api\.github\.com/'
    );

PSR-7 and PSR-18 Compatibility
------------------------------

This component uses its own interfaces and exception classes different from the
ones defined in `PSR-7`_ (HTTP message interfaces) and `PSR-18`_ (HTTP Client).
However, it includes the :class:`Symfony\\Component\\HttpClient\\Psr18Client`
class, which is an adapter to turn a Symfony ``HttpClientInterface`` into a
PSR-18 ``ClientInterface``.

Before using it in your application, run the following commands to install the
required dependencies:

.. code-block:: terminal

    # installs the base ClientInterface
    $ composer require psr/http-client

    # installs an efficient implementation of response and stream factories
    # with autowiring aliases provided by Symfony Flex
    $ composer require nyholm/psr7

Now you can make HTTP requests with the PSR-18 client as follows::

    use Nyholm\Psr7\Factory\Psr17Factory;
    use Symfony\Component\HttpClient\Psr18Client;

    $psr17Factory = new Psr17Factory();
    $psr18Client = new Psr18Client();

    $url = 'https://symfony.com/versions.json';
    $request = $psr17Factory->createRequest('GET', $url);
    $response = $psr18Client->sendRequest($request);

    $content = json_decode($response->getBody()->getContents(), true);

Symfony Framework Integration
-----------------------------

When using this component in a full-stack Symfony application, you can configure
multiple clients with different configurations and inject them into your services.

Configuration
~~~~~~~~~~~~~

Use the ``framework.http_client`` key to configure the default HTTP client used
in the application. Check out the full
:ref:`http_client config reference <reference-http-client>` to learn about all
the available config options:

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            max_redirects: 7
            max_host_connections: 10

If you want to define multiple HTTP clients, use this other expanded configuration:

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            http_clients:
                crawler:
                    headers: [{ 'X-Powered-By': 'ACME App' }]
                    http_version: '1.0'
                default:
                    max_host_connections: 10
                    max_redirects: 7

Injecting the HTTP Client Into Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your application only defines one HTTP client, you can inject it into any
service by type-hinting a constructor argument with the
:class:`Symfony\\Contracts\\HttpClient\\HttpClientInterface`::

    use Symfony\Contracts\HttpClient\HttpClientInterface;

    class SomeService
    {
        private $httpClient;

        public function __construct(HttpClientInterface $httpClient)
        {
            $this->httpClient = $httpClient;
        }
    }

If you have several clients, you must use any of the methods defined by Symfony
to ref:`choose a specific service <services-wire-specific-service>`. Each client
has a unique service named after its configuration.

.. code-block:: yaml

    # config/services.yaml
    services:
        # ...

        # whenever a service type-hints HttpClientInterface, inject the GitHub client
        Symfony\Contracts\HttpClient\HttpClientInterface: '@api_client.github'

        # inject the HTTP client called 'crawler' into this argument of this service
        App\Some\Service:
            $someArgument: '@http_client.crawler'

Testing HTTP Clients and Responses
----------------------------------

This component includes the ``MockHttpClient`` and ``MockResponse`` classes to
use them in tests that need an HTTP client which doesn't make actual HTTP
requests.

The first way of using ``MockHttpClient`` is to configure the set of responses
to return using its constructor::

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

.. _`cURL PHP extension`: https://php.net/curl
.. _`PSR-7`: https://www.php-fig.org/psr/psr-7/
.. _`PSR-18`: https://www.php-fig.org/psr/psr-18/
