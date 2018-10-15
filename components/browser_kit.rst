.. index::
   single: BrowserKit
   single: Components; BrowserKit

The BrowserKit Component
========================

    The BrowserKit component simulates the behavior of a web browser, allowing
    you to make requests, click on links and submit forms programmatically.

.. note::

    The BrowserKit component can only make internal requests to your application.
    If you need to make requests to external sites and applications, consider
    using `Goutte`_, a simple web scraper based on Symfony Components.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/browser-kit

Alternatively, you can clone the `<https://github.com/symfony/browser-kit>`_ repository.

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
ready to use for the HTTP layer.

To create your own client, you must extend the abstract ``Client`` class and
implement the :method:`Symfony\\Component\\BrowserKit\\Client::doRequest` method.
This method accepts a request and should return a response::

    namespace Acme;

    use Symfony\Component\BrowserKit\Client as BaseClient;
    use Symfony\Component\BrowserKit\Response;

    class Client extends BaseClient
    {
        protected function doRequest($request)
        {
            // ... convert request into a response

            return new Response($content, $status, $headers);
        }
    }

For a simple implementation of a browser based on the HTTP layer, have a look
at `Goutte`_. For an implementation based on ``HttpKernelInterface``, have
a look at the :class:`Symfony\\Component\\HttpKernel\\Client` provided by
the :doc:`HttpKernel component </components/http_kernel>`.

Making Requests
~~~~~~~~~~~~~~~

Use the :method:`Symfony\\Component\\BrowserKit\\Client::request` method to
make HTTP requests. The first two arguments are the HTTP method and the requested
URL::

    use Acme\Client;

    $client = new Client();
    $crawler = $client->request('GET', '/');

The value returned by the ``request()`` method is an instance of the
:class:`Symfony\\Component\\DomCrawler\\Crawler` class, provided by the
:doc:`DomCrawler component </components/dom_crawler>`, which allows accessing
and traversing HTML elements programmatically.

The :method:`Symfony\\Component\\BrowserKit\\Client::xmlHttpRequest` method,
which defines the same arguments as the ``request()`` method, is a shortcut to
make AJAX requests::

    use Acme\Client;

    $client = new Client();
    // the required HTTP_X_REQUESTED_WITH header is added automatically
    $crawler = $client->xmlHttpRequest('GET', '/');

.. versionadded:: 4.1
    The ``xmlHttpRequest()`` method was introduced in Symfony 4.1.

Clicking Links
~~~~~~~~~~~~~~

The ``Client`` object is capable of simulating link clicks. Pass the text
content of the link and the client will perform the needed HTTP GET request to
simulate the link click::

    use Acme\Client;

    $client = new Client();
    $client->request('GET', '/product/123');

    $crawler = $client->clickLink('Go elsewhere...');

.. versionadded:: 4.2
    The ``clickLink()`` method was introduced in Symfony 4.2.

If you need the :class:`Symfony\\Component\\DomCrawler\\Link` object that
provides access to the link properties (e.g. ``$link->getMethod()``,
``$link->getUri()``), use this other method:

    // ...
    $crawler = $client->request('GET', '/product/123');
    $link = $crawler->selectLink('Go elsewhere...')->link();
    $client->click($link);

Submitting Forms
~~~~~~~~~~~~~~~~

The ``Client`` object is also capable of submitting forms. First, select the
form using any of its buttons and then override any of its properties (method,
field values, etc.) before submitting it::

    use Acme\Client;

    $client = new Client();
    $crawler = $client->request('GET', 'https://github.com/login');

    // find the form with the 'Log in' button and submit it
    // 'Log in' can be the text content, id, value or name of a <button> or <input type="submit">
    $client->submitForm('Log in');

    // the second optional argument lets you override the default form field values
    $client->submitForm('Log in', array(
        'login' => 'my_user',
        'password' => 'my_pass',
        // to upload a file, the value must be the absolute file path
        'file' => __FILE__,
    ));

    // you can override other form options too
    $client->submitForm(
        'Log in',
        array('login' => 'my_user', 'password' => 'my_pass'),
        // override the default form HTTP method
        'PUT',
        // override some $_SERVER parameters (e.g. HTTP headers)
        array('HTTP_ACCEPT_LANGUAGE' => 'es')
    );

.. versionadded:: 4.2
    The ``submitForm()`` method was introduced in Symfony 4.2.

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

Cookies
-------

Retrieving Cookies
~~~~~~~~~~~~~~~~~~

The ``Client`` implementation exposes cookies (if any) through a
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
    $client = new Client(array(), null, $cookieJar);
    // ...

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

Learn more
----------

* :doc:`/testing`
* :doc:`/components/css_selector`
* :doc:`/components/dom_crawler`

.. _`Packagist`: https://packagist.org/packages/symfony/browser-kit
.. _`Goutte`: https://github.com/FriendsOfPHP/Goutte
