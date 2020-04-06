.. index::
   single: HttpClient
   single: Components; HttpClient

The HttpClient Component
========================

    The HttpClient component is a low-level HTTP client with support for both
    PHP stream wrappers and cURL. It provides utilities to consume APIs and
    supports synchronous and asynchronous operations.

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

    $client = HttpClient::create();
    $response = $client->request('GET', 'https://api.github.com/repos/symfony/symfony-docs');

    $statusCode = $response->getStatusCode();
    // $statusCode = 200
    $contentType = $response->getHeaders()['content-type'][0];
    // $contentType = 'application/json'
    $content = $response->getContent();
    // $content = '{"id":521583, "name":"symfony-docs", ...}'
    $content = $response->toArray();
    // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

Performance
-----------

The component is built for maximum HTTP performance. By design, it is compatible
with HTTP/2 and with doing concurrent asynchronous streamed and multiplexed
requests/responses. Even when doing regular synchronous calls, this design
allows keeping connections to remote hosts open between requests, improving
performance by saving repetitive DNS resolution, SSL negotiation, etc.

Enabling cURL Support
~~~~~~~~~~~~~~~~~~~~~

This component supports both the native PHP streams and cURL to make the HTTP
requests. Both are interchangeable and provide the same features, including
concurrent requests and HTTP/2 support.

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

HTTP/2 Support
~~~~~~~~~~~~~~

.. versionadded:: 5.1

    Integration with ``amphp/http-client`` was introduced in Symfony 5.1.
    Prior to this version, HTTP/2 was only supported when ``libcurl`` was
    installed.

The component supports HTTP/2 if one of the following tools is
installed:

* The `libcurl`_ package version 7.36 or higher;
* The `amphp/http-client`_ Packagist package version 4.2 or higher.

When requesting an ``https`` URL and HTTP/2 is supported by your server,
HTTP/2 is enabled by default. To force HTTP/2 for ``http`` URLs, you need
to enable it explicitly via the ``http_version`` option::

    $client = HttpClient::create(['http_version' => '2.0']);

Support for HTTP/2 PUSH works out of the box when libcurl >= 7.61 is used with
PHP >= 7.2.17 / 7.3.4: pushed responses are put into a temporary cache and are
used when a subsequent request is triggered for the corresponding URLs.

Making Requests
---------------

The client created with the ``HttpClient`` class provides a single ``request()``
method to perform all kinds of HTTP requests::

    $response = $client->request('GET', 'https://...');
    $response = $client->request('POST', 'https://...');
    $response = $client->request('PUT', 'https://...');
    // ...

Responses are always asynchronous, so that the call to the method returns
immediately instead of waiting to receive the response::

    // code execution continues immediately; it doesn't wait to receive the response
    $response = $client->request('GET', 'http://releases.ubuntu.com/18.04.2/ubuntu-18.04.2-desktop-amd64.iso');

    // getting the response headers waits until they arrive
    $contentType = $response->getHeaders()['content-type'][0];

    // trying to get the response contents will block the execution until
    // the full response contents are received
    $contents = $response->getContent();

This component also supports :ref:`streaming responses <http-client-streaming-responses>`
for full asynchronous applications.

.. note::

    HTTP compression and chunked transfer encoding are automatically enabled when
    both your PHP runtime and the remote server support them.

Authentication
~~~~~~~~~~~~~~

The HTTP client supports different authentication mechanisms. They can be
defined globally when creating the client (to apply it to all requests) and to
each request (which overrides any global authentication)::

    // Use the same authentication for all requests to https://example.com/
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

Use the ``headers`` option to define both the default headers added to all
requests and the specific headers for each request::

    // this header is added to all requests made by this client
    $client = HttpClient::create(['headers' => [
        'User-Agent' => 'My Fancy App',
    ]]);

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

To submit a form with file uploads, it is your responsibility to encode the body
according to the ``multipart/form-data`` content-type. The
:doc:`Symfony Mime </components/mime>` component makes it a few lines of code::

    use Symfony\Component\Mime\Part\DataPart;
    use Symfony\Component\Mime\Part\Multipart\FormDataPart;

    $formFields = [
        'regular_field' => 'some value',
        'file_field' => DataPart::fromPath('/path/to/uploaded/file'),
    ];
    $formData = new FormDataPart($formFields);
    $client->request('POST', 'https://...', [
        'headers' => $formData->getPreparedHeaders()->toArray(),
        'body' => $formData->bodyToIterable(),
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

    $response = $client->request('GET', 'https://...', [
        // 0 means to not follow any redirect
        'max_redirects' => 0,
    ]);

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

Advanced Options
~~~~~~~~~~~~~~~~

The :class:`Symfony\\Contracts\\HttpClient\\HttpClientInterface` defines all the
options you might need to take full control of the way the request is performed,
including DNS pre-resolution, SSL parameters, public key pinning, etc.

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

    // casts the response JSON contents to a PHP array
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

    // returns detailed logs about the requests and responses of the HTTP transaction
    $httpLogs = $response->getInfo('debug');

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

    // get the response contents in chunk and save them in a file
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
    as argument.

Canceling Responses
~~~~~~~~~~~~~~~~~~~

To abort a request (e.g. because it didn't complete in due time, or you want to
fetch only the first bytes of the response, etc.), you can either use the
``cancel()`` method of ``ResponseInterface``::

    $response->cancel()

Or throw an exception from a progress callback::

    $response = $client->request('GET', 'https://...', [
        'on_progress' => function (int $dlNow, int $dlSize, array $info): void {
            // ...

            throw new \MyException();
        },
    ]);

The exception will be wrapped in an instance of ``TransportExceptionInterface``
and will abort the request.

In case the response was canceled using ``$response->cancel()``,
``$response->getInfo('canceled')`` will return ``true``.

Handling Exceptions
~~~~~~~~~~~~~~~~~~~

When the HTTP status code of the response is in the 300-599 range (i.e. 3xx,
4xx or 5xx) your code is expected to handle it. If you don't do that, the
``getHeaders()`` and ``getContent()`` methods throw an appropriate exception, all of
which implement the :class:`Symfony\\Contracts\\HttpClient\\Exception\\HttpExceptionInterface`::

    // the response of this request will be a 403 HTTP error
    $response = $client->request('GET', 'https://httpbin.org/status/403');

    // this code results in a Symfony\Component\HttpClient\Exception\ClientException
    // because it doesn't check the status code of the response
    $content = $response->getContent();

    // pass FALSE as the optional argument to not throw an exception and return
    // instead the original response content (even if it's an error message)
    $content = $response->getContent(false);

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
the error-handling code, you will opt-out from these fallback mechanisms as the
destructor won't have anything remaining to do.

There are three types of exceptions:

* Exceptions implementing the :class:`Symfony\\Contracts\\HttpClient\\Exception\\HttpExceptionInterface`
  are thrown when your code does not handle the status codes in the 300-599 range.

* Exceptions implementing the :class:`Symfony\\Contracts\\HttpClient\\Exception\\TransportExceptionInterface`
  are thrown when a lower level issue occurs.

* Exceptions implementing the :class:`Symfony\\Contracts\\HttpClient\\Exception\\DecodingExceptionInterface`
  are thrown when a content-type cannot be decoded to the expected representation.

Concurrent Requests
-------------------

Thanks to responses being lazy, requests are always managed concurrently.
On a fast enough network, the following code makes 379 requests in less than
half a second when cURL is used::

    use Symfony\Component\HttpClient\CurlHttpClient;

    $client = new CurlHttpClient();

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
            // $response staled for more than 1.5 seconds
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

    try {
        // both lines can potentially throw
        $response = $client->request(...);
        $headers = $response->getHeaders();
        // ...
    } catch (TransportExceptionInterface $e) {
        // ...
    }

.. note::

    Because ``$response->getInfo()`` is non-blocking, it shouldn't throw by design.

When multiplexing responses, you can deal with errors for individual streams by
catching ``TransportExceptionInterface`` in the foreach loop::

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

You can define several scopes, so that each set of options is added only if a
requested URL matches one of the regular expressions provided as keys.

If the request URL is relative (because you use the ``base_uri`` option), the
scoping HTTP client can't make a match. That's why you can define a third
optional argument in its constructor which will be considered the default
regular expression applied to relative URLs::

    // ...

    $client = new ScopingHttpClient($client,
        [
            'https://api\.github\.com/' => [
                'base_uri' => 'https://api.github.com/',
                // ...
            ],
        ],
        // this is the index in the previous array that defines
        // the base URI that shoud be used to resolve relative URLs
        'https://api\.github\.com/'
    );

The above example can be reduced to a simpler call::

    // ...

    $client = ScopingHttpClient::forBaseUri($client, 'https://api.github.com/', [
        // ...
    ]);

This way, the provided options will be used only if the requested URL is relative
or if it matches the ``https://api.github.com/`` base URI.

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
        private $client;

        public function __construct(HttpClientInterface $client)
        {
            $this->client = $client;
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
to turn a Symfony ``HttpClientInterface`` into a PSR-18 ``ClientInterface``.
This class also implements the relevant methods of `PSR-17`_ to ease creating
request objects.

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

Now you can make HTTP requests with the PSR-18 client as follows::

    use Symfony\Component\HttpClient\Psr18Client;

    $client = new Psr18Client();

    $url = 'https://symfony.com/versions.json';
    $request = $client->createRequest('GET', $url);
    $response = $client->sendRequest($request);

    $content = json_decode($response->getBody()->getContents(), true);

HTTPlug
~~~~~~~

The `HTTPlug`_ v1 specification was published before PSR-18 and is superseded by
it. As such, you should not use it in newly written code. The component is still
interoperable with libraries that require it thanks to the
:class:`Symfony\\Component\\HttpClient\\HttplugClient` class. Similarly to
``Psr18Client`` implementing relevant parts of PSR-17, ``HttplugClient`` also
implements the factory methods defined in the related ``php-http/message-factory``
package.

.. code-block:: terminal

    # Let's suppose php-http/httplug is already required by the lib you want to use

    # installs an efficient implementation of response and stream factories
    # with autowiring aliases provided by Symfony Flex
    $ composer require nyholm/psr7

    # alternatively, install the php-http/discovery package to auto-discover
    # any already installed implementations from common vendors:
    # composer require php-http/discovery

Let's say you want to instantiate a class with the following constructor,
that requires HTTPlug dependencies::

    use Http\Client\HttpClient;
    use Http\Message\RequestFactory;
    use Http\Message\StreamFactory;

    class SomeSdk
    {
        public function __construct(
            HttpClient $httpClient,
            RequestFactory $requestFactory,
            StreamFactory $streamFactory
        )
        // [...]
    }

Because ``HttplugClient`` implements the three interfaces, you can use it this way::

    use Symfony\Component\HttpClient\HttplugClient;

    $httpClient = new HttplugClient();
    $apiClient = new SomeSdk($httpClient, $httpClient, $httpClient);

If you'd like to work with promises, ``HttplugClient`` also implements the
``HttpAsyncClient`` interface. To use it, you need to install the
``guzzlehttp/promises`` package:

.. code-block:: terminal

    $ composer require guzzlehttp/promises

Then you're ready to go::

    use Psr\Http\Message\ResponseInterface;
    use Symfony\Component\HttpClient\HttplugClient;

    $httpClient = new HttplugClient();
    $request = $httpClient->createRequest('GET', 'https://my.api.com/');
    $promise = $httpClient->sendRequest($request)
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
            max_host_connections: 10
            default_options:
                max_redirects: 7

If you want to define multiple HTTP clients, use this other expanded configuration:

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            scoped_clients:
                crawler.client:
                    headers: { 'X-Powered-By': 'ACME App' }
                    http_version: '1.0'
                some_api.client:
                    max_redirects: 5

Injecting the HTTP Client into Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your application only needs one HTTP client, you can inject the default one
into any services by type-hinting a constructor argument with the
:class:`Symfony\\Contracts\\HttpClient\\HttpClientInterface`::

    use Symfony\Contracts\HttpClient\HttpClientInterface;

    class SomeService
    {
        private $client;

        public function __construct(HttpClientInterface $client)
        {
            $this->client = $client;
        }
    }

If you have several clients, you must use any of the methods defined by Symfony
to :ref:`choose a specific service <services-wire-specific-service>`. Each client
has a unique service named after its configuration.

Each scoped client also defines a corresponding named autowiring alias.
If you use for example
``Symfony\Contracts\HttpClient\HttpClientInterface $myApiClient``
as the type and name of an argument, autowiring will inject the ``my_api.client``
service into your autowired classes.

Testing HTTP Clients and Responses
----------------------------------

This component includes the ``MockHttpClient`` and ``MockResponse`` classes to
use them in tests that need an HTTP client which doesn't make actual HTTP
requests.

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

.. _`cURL PHP extension`: https://www.php.net/curl
.. _`PSR-17`: https://www.php-fig.org/psr/psr-17/
.. _`PSR-18`: https://www.php-fig.org/psr/psr-18/
.. _`HTTPlug`: https://github.com/php-http/httplug/#readme
.. _`Symfony Contracts`: https://github.com/symfony/contracts
.. _`libcurl`: https://curl.haxx.se/libcurl/
.. _`amphp/http-client`: https://packagist.org/packages/amphp/http-client
