.. index::
   single: BrowserKit
   single: Components; BrowserKit

The BrowserKit Component
========================

    The BrowserKit component simulates the behavior of a web browser, allowing
    you to make requests, click on links and submit forms programmatically.

.. note::

    In Symfony versions prior to 4.3, the BrowserKit component could only make
    internal requests to your application. Starting from Symfony 4.3, this
    component can also :ref:`make HTTP requests to any public site <component-browserkit-external-requests>`
    when using it in combination with the :doc:`HttpClient component </http_client>`.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/browser-kit

.. include:: /components/require_autoload.rst.inc

Basic Usage
-----------

.. seealso::

    This article explains how to use the BrowserKit features as an independent
    component in any PHP application. Read the :ref:`Symfony Functional Tests <functional-tests>`
    article to learn about how to use it in Symfony applications.

Creating a Client
~~~~~~~~~~~~~~~~~

The component only provides an abstract client and does not provide any backend
ready to use for the HTTP layer. To create your own client, you must extend the
``AbstractBrowser`` class and implement the
:method:`Symfony\\Component\\BrowserKit\\AbstractBrowser::doRequest` method.
This method accepts a request and should return a response::

    namespace Acme;

    use Symfony\Component\BrowserKit\AbstractBrowser;
    use Symfony\Component\BrowserKit\Response;

    class Client extends AbstractBrowser
    {
        protected function doRequest($request)
        {
            // ... convert request into a response

            return new Response($content, $status, $headers);
        }
    }

For a simple implementation of a browser based on the HTTP layer, have a look
at the :class:`Symfony\\Component\\BrowserKit\\HttpBrowser` provided by
:ref:`this component <component-browserkit-external-requests>`. For an implementation based
on ``HttpKernelInterface``, have a look at the :class:`Symfony\\Component\\HttpKernel\\HttpClientKernel`
provided by the :doc:`HttpKernel component </components/http_kernel>`.

Making Requests
~~~~~~~~~~~~~~~

Use the :method:`Symfony\\Component\\BrowserKit\\AbstractBrowser::request` method to
make HTTP requests. The first two arguments are the HTTP method and the requested
URL::

    use Acme\Client;

    $client = new Client();
    $crawler = $client->request('GET', '/');

The value returned by the ``request()`` method is an instance of the
:class:`Symfony\\Component\\DomCrawler\\Crawler` class, provided by the
:doc:`DomCrawler component </components/dom_crawler>`, which allows accessing
and traversing HTML elements programmatically.

The :method:`Symfony\\Component\\BrowserKit\\AbstractBrowser::jsonRequest` method,
which defines the same arguments as the ``request()`` method, is a shortcut to
convert the request parameters into a JSON string and set the needed HTTP headers::

    use Acme\Client;

    $client = new Client();
    // this encodes parameters as JSON and sets the required CONTENT_TYPE and HTTP_ACCEPT headers
    $crawler = $client->jsonRequest('GET', '/', ['some_parameter' => 'some_value']);

.. versionadded:: 5.3

    The ``jsonRequest()`` method was introduced in Symfony 5.3.

The :method:`Symfony\\Component\\BrowserKit\\AbstractBrowser::xmlHttpRequest` method,
which defines the same arguments as the ``request()`` method, is a shortcut to
make AJAX requests::

    use Acme\Client;

    $client = new Client();
    // the required HTTP_X_REQUESTED_WITH header is added automatically
    $crawler = $client->xmlHttpRequest('GET', '/');

Clicking Links
~~~~~~~~~~~~~~

The ``AbstractBrowser`` is capable of simulating link clicks. Pass the text
content of the link and the client will perform the needed HTTP GET request to
simulate the link click::

    use Acme\Client;

    $client = new Client();
    $client->request('GET', '/product/123');

    $crawler = $client->clickLink('Go elsewhere...');

If you need the :class:`Symfony\\Component\\DomCrawler\\Link` object that
provides access to the link properties (e.g. ``$link->getMethod()``,
``$link->getUri()``), use this other method::

    // ...
    $crawler = $client->request('GET', '/product/123');
    $link = $crawler->selectLink('Go elsewhere...')->link();
    $client->click($link);

Submitting Forms
~~~~~~~~~~~~~~~~

The ``AbstractBrowser`` is also capable of submitting forms. First, select the
form using any of its buttons and then override any of its properties (method,
field values, etc.) before submitting it::

    use Acme\Client;

    $client = new Client();
    $crawler = $client->request('GET', 'https://github.com/login');

    // find the form with the 'Log in' button and submit it
    // 'Log in' can be the text content, id, value or name of a <button> or <input type="submit">
    $client->submitForm('Log in');

    // the second optional argument lets you override the default form field values
    $client->submitForm('Log in', [
        'login' => 'my_user',
        'password' => 'my_pass',
        // to upload a file, the value must be the absolute file path
        'file' => __FILE__,
    ]);

    // you can override other form options too
    $client->submitForm(
        'Log in',
        ['login' => 'my_user', 'password' => 'my_pass'],
        // override the default form HTTP method
        'PUT',
        // override some $_SERVER parameters (e.g. HTTP headers)
        ['HTTP_ACCEPT_LANGUAGE' => 'es']
    );

If you need the :class:`Symfony\\Component\\DomCrawler\\Form` object that
provides access to the form properties (e.g. ``$form->getUri()``,
``$form->getValues()``, ``$form->getFields()``), use this other method::

    // ...

    // select the form and fill in some values
    $form = $crawler->selectButton('Log in')->form();
    $form['login'] = 'symfonyfan';
    $form['password'] = 'anypass';

    // submit that form
    $crawler = $client->submit($form);

Custom Header Handling
~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.2

    The ``getHeaders()`` method was introduced in Symfony 5.2.

The optional HTTP headers passed to the ``request()`` method follows the FastCGI
request format (uppercase, underscores instead of dashes and prefixed with ``HTTP_``).
Before saving those headers to the request, they are lower-cased, with ``HTTP_``
stripped, and underscores converted into dashes.

If you're making a request to an application that has special rules about header
capitalization or punctuation, override the ``getHeaders()`` method, which must
return an associative array of headers::

    protected function getHeaders(Request $request): array
    {
        $headers = parent::getHeaders($request);
        if (isset($request->getServer()['api_key'])) {
            $headers['api_key'] = $request->getServer()['api_key'];
        }

        return $headers;
    }

Cookies
-------

Retrieving Cookies
~~~~~~~~~~~~~~~~~~

The ``AbstractBrowser`` implementation exposes cookies (if any) through a
:class:`Symfony\\Component\\BrowserKit\\CookieJar`, which allows you to store and
retrieve any cookie while making requests with the client::

    use Acme\Client;

    // Make a request
    $client = new Client();
    $crawler = $client->request('GET', '/');

    // Get the cookie Jar
    $cookieJar = $client->getCookieJar();

    // Get a cookie by name
    $cookie = $cookieJar->get('name_of_the_cookie');

    // Get cookie data
    $name       = $cookie->getName();
    $value      = $cookie->getValue();
    $rawValue   = $cookie->getRawValue();
    $isSecure   = $cookie->isSecure();
    $isHttpOnly = $cookie->isHttpOnly();
    $isExpired  = $cookie->isExpired();
    $expires    = $cookie->getExpiresTime();
    $path       = $cookie->getPath();
    $domain     = $cookie->getDomain();
    $sameSite   = $cookie->getSameSite();

.. note::

    These methods only return cookies that have not expired.

Looping Through Cookies
~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    use Acme\Client;

    // Make a request
    $client = new Client();
    $crawler = $client->request('GET', '/');

    // Get the cookie Jar
    $cookieJar = $client->getCookieJar();

    // Get array with all cookies
    $cookies = $cookieJar->all();
    foreach ($cookies as $cookie) {
        // ...
    }

    // Get all values
    $values = $cookieJar->allValues('http://symfony.com');
    foreach ($values as $value) {
        // ...
    }

    // Get all raw values
    $rawValues = $cookieJar->allRawValues('http://symfony.com');
    foreach ($rawValues as $rawValue) {
        // ...
    }

Setting Cookies
~~~~~~~~~~~~~~~

You can also create cookies and add them to a cookie jar that can be injected
into the client constructor::

    use Acme\Client;

    // create cookies and add to cookie jar
    $cookie = new Cookie('flavor', 'chocolate', strtotime('+1 day'));
    $cookieJar = new CookieJar();
    $cookieJar->set($cookie);

    // create a client and set the cookies
    $client = new Client([], null, $cookieJar);
    // ...

.. _component-browserkit-sending-cookies:

Sending Cookies
~~~~~~~~~~~~~~~

Requests can include cookies. To do so, use the ``serverParameters`` argument of
the :method:`Symfony\\Component\\BrowserKit\\AbstractBrowser::request` method
to set the ``Cookie`` header value::

    $client->request('GET', '/', [], [], [
        'HTTP_COOKIE' => new Cookie('flavor', 'chocolate', strtotime('+1 day')),

        // you can also pass the cookie contents as a string
        'HTTP_COOKIE' => 'flavor=chocolate; expires=Sat, 11 Feb 2023 12:18:13 GMT; Max-Age=86400; path=/'
    ]);

.. note::

    All HTTP headers set with the ``serverParameters`` argument must be
    prefixed by ``HTTP_``.

History
-------

The client stores all your requests allowing you to go back and forward in your
history::

    use Acme\Client;

    $client = new Client();
    $client->request('GET', '/');

    // select and click on a link
    $link = $crawler->selectLink('Documentation')->link();
    $client->click($link);

    // go back to home page
    $crawler = $client->back();

    // go forward to documentation page
    $crawler = $client->forward();

You can delete the client's history with the ``restart()`` method. This will
also delete all the cookies::

    use Acme\Client;

    $client = new Client();
    $client->request('GET', '/');

    // reset the client (history and cookies are cleared too)
    $client->restart();

.. _component-browserkit-external-requests:

Making External HTTP Requests
-----------------------------

So far, all the examples in this article have assumed that you are making
internal requests to your own application. However, you can run the exact same
examples when making HTTP requests to external web sites and applications.

First, install and configure the :doc:`HttpClient component </http_client>`.
Then, use the :class:`Symfony\\Component\\BrowserKit\\HttpBrowser` to create
the client that will make the external HTTP requests::

    use Symfony\Component\BrowserKit\HttpBrowser;
    use Symfony\Component\HttpClient\HttpClient;

    $browser = new HttpBrowser(HttpClient::create());

You can now use any of the methods shown in this article to extract information,
click links, submit forms, etc. This means that you no longer need to use a
dedicated web crawler or scraper such as `Goutte`_::

    $browser = new HttpBrowser(HttpClient::create());

    $browser->request('GET', 'https://github.com');
    $browser->clickLink('Sign in');
    $browser->submitForm('Sign in', ['login' => '...', 'password' => '...']);
    $openPullRequests = trim($browser->clickLink('Pull requests')->filter(
        '.table-list-header-toggle a:nth-child(1)'
    )->text());

.. tip::

    You can also use HTTP client options like ``ciphers``, ``auth_basic`` and
    ``query``. They have to be passed as the default options argument to the
    client which is used by the HTTP browser.

Dealing with HTTP responses
~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using the BrowserKit component, you may need to deal with responses of
the requests you made. To do so, call the ``getResponse()`` method of the
``HttpBrowser`` object. This method returns the last response the browser received::

    $browser = new HttpBrowser(HttpClient::create());

    $browser->request('GET', 'https://foo.com');
    $response = $browser->getResponse();

Learn more
----------

* :doc:`/testing`
* :doc:`/components/css_selector`
* :doc:`/components/dom_crawler`

.. _`Goutte`: https://github.com/FriendsOfPHP/Goutte
