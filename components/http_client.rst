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

Alternatively, you can clone the `<https://github.com/symfony/http-client>`_
repository.

.. include:: /components/require_autoload.rst.inc

Basic Usage
-----------

Use the :class:`Symfony\Component\HttpClient\HttpClient` class to create the
low-level HTTP client that makes requests, like the following ``GET`` request::

    use Symfony\Component\HttpClient\HttpClient;

    $httpClient = HttpClient::create();
    $response = $httpClient->request('GET', 'https://api.github.com/repos/symfony/symfony-docs');

    $statusCode = $response->getStatusCode();
    // $statusCode = 200
    $contentType = $response->getHeaders()['content-type'][0];
    // $contentType = 'application/json'
    $content = $response->getContent();
    // $content = '{"id":521583,"name":"symfony-docs",...}'

If you are consuming APIs, you should use instead the
:class:`Symfony\\Component\\HttpClient\\ApiClient` class, which includes
shortcuts and utilities for common operations::

    use Symfony\Component\HttpClient\ApiClient;
    use Symfony\Component\HttpClient\HttpClient;

    $httpClient = HttpClient::create();
    $apiClient = new ApiClient($httpClient);

    $response = $apiClient->post('https://api.github.com/gists', [
        // this is transformed into the proper Basic authorization header
        'auth' => 'username:password',
        // this PHP array is encoded as JSON and added to the request body
        'json' => [
            'description' => 'Created by Symfony HttpClient',
            'public' => true,
            'files' => [
                'article.txt' => ['content' => 'Lorem Ipsum ...'],
            ],
        ],
    ]);

    // decodes the JSON body of the response into a PHP array
    $result = $response->asArray();
    // $result = [
    //     'id' => '11b5f...023cf9',
    //     'url' => 'https://api.github.com/gists/11b5f...023cf9',
    //     ...
    // ]

Enabling cURL Support
---------------------

This component supports both the native PHP streams and cURL to make the HTTP
requests. Although both are interchangeable and provide the same features,
including concurrent requests, HTTP/2 is only supported when using cURL (and if
the installed cURL version supports it).

``HttpClient::create()`` selects the cURL transport if the `cURL PHP extension`_
is enabled and falls back to PHP streams otherwise. If you prefer to select
the transport explicitly, use the following classes to create the client::

    use Symfony\Component\HttpClient\NativeHttpClient;
    use Symfony\Component\HttpClient\CurlHttpClient;

    // uses native PHP streams
    $httpClient = new NativeHttpClient();

    // uses the cURL PHP extension
    $httpClient = new CurlHttpClient();

When using this component in a full-stack Symfony application, this behavior is
not configurable and cURL will be used automatically if the cURL PHP extension
is installed and enabled. Otherwise, the native PHP streams will be used.

Making Requests
---------------

The client created with the ``HttpClient`` class provides a single ``request()``
method to perform all HTTP requests, whereas the client created with the
``ApiClient`` class provides a method for each HTTP verb::

    $response = $httpClient->request('GET', 'https://');
    $response = $httpClient->request('POST', 'https://');
    $response = $httpClient->request('PUT', 'https://');
    // ...

    $response = $apiClient->get('https://');
    $response = $apiClient->post('https://');
    $response = $apiClient->put('https://');
    // ...

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
        'Authorization' => 'Bearer ...',
    ]]);

    // this header is only included in this request
    $response = $httpClient->request('POST', 'https://...', [
        'headers' => [
            'Content-type' => 'text/plain',
        ],
    ]);

Uploading Data
~~~~~~~~~~~~~~

This component provides several methods for uploading data using the ``body``
option. You can use regular strings, closures and resources and they'll be
processing automatically when making the requests::

    $response = $httpClient->request('POST', 'https://...', [
        // defining data using a regular string
        'body' => 'raw data',

        // using a closure to generate the uploaded data
        'body' => function () {
            // ...
        },

        // using a resource to get the data from it
        'body' => fopen('/path/to/file', 'r'),
    ]);

When uploading data with the ``POST`` method, if you don't define the
``Content-Type`` HTTP header explicitly, Symfony adds the required
``'Content-Type: application/x-www-form-urlencoded'`` header for you.

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

Concurrent Requests
~~~~~~~~~~~~~~~~~~~


.. TODO


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

    // returns info coming from the transport layer, such as "raw_headers",
    // "redirect_count", "start_time", "redirect_url", etc.
    $httpInfo = $response->getInfo();

Streaming Responses
~~~~~~~~~~~~~~~~~~~


.. TODO:


Handling Exceptions
~~~~~~~~~~~~~~~~~~~

When an HTTP error happens (status code 3xx, 4xx or 5xx) your code is expected
to handle it by checking the status code of the response. If you don't do that,
an appropriate exception is thrown::

    $response = $httpClient->request('GET', 'https://httpbin.org/status/403');

    // this code results in a Symfony\Component\HttpClient\Exception\ClientException
    // because it doesn't check the status code of the response
    $content = $response->getContent();

    // this code doesn't throw an exception because it handles the HTTP error itself
    $content = 403 !== $reponse->getStatusCode() ? $response->getContent() : null;

PSR-7 and PSR-18 Compatibility
------------------------------

This component uses its own interfaces and exception classes different from the
ones defined in `PSR-7`_ (HTTP message interfaces) and `PSR-18`_ (HTTP Client).
However, it includes the :class:`Symfony\\Component\\HttpClient\\Psr18Client`
class, which is an adapter to turn a Symfony ``HttpClientInterface`` into a
PSR-18 ``ClientInterface``.

Before using it in your app, run the following commands to install the required
dependencies:

.. code-block:: terminal

    # installs the base ClientInterface
    $ composer require psr/http-client

    # installs an efficient implementation of response and stream factories
    # with autowiring aliases provided by Symfony Flex
    $ composer require nyholm/psr7

Symfony Framework Integration
-----------------------------

When using this component in a full-stack Symfony application, you can configure
multiple clients with different configurations and inject them in your services.

Configuration
~~~~~~~~~~~~~

Use the ``framework.http_client`` option to configure the default HTTP client
used in the application:

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            max_redirects: 7
            max_host_connections: 10

If you want to define multiple HTTP and API clients, use this other expanded
configuration:

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

            api_clients:
                github:
                    base_uri: 'https://api.github.com'
                    headers: [{ 'Accept': 'application/vnd.github.v3+json' }]

Injecting the HTTP Client Into Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your application only defines one HTTP client, you can inject it in any
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
has a service associated with it whose name follows the pattern
``client type + client name`` (e.g. ``http_client.crawler``, ``api_client.github``).

.. code-block:: yaml

    # config/services.yaml
    services:
        # ...

        # whenever a service type-hints ApiClientInterface, inject the GitHub client
        Symfony\Contracts\HttpClient\ApiClientInterface: '@api_client.github'

        # inject the HTTP client called 'crawler' in this argument of this service
        App\Some\Service:
            $someArgument: '@http_client.crawler'

.. _`cURL PHP extension`: https://php.net/curl
.. _`PSR-7`: https://www.php-fig.org/psr/psr-7/
.. _`PSR-18`: https://www.php-fig.org/psr/psr-18/
