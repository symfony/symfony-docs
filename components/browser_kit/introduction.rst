.. index::
   single: BrowserKit
   single: Components; BrowserKit

The BrowserKit Component
========================

    The BrowserKit component simulates the behavior of a web browser, allowing
    you to make requests, click on links and submit forms programmatically.

Installation
------------

You can install the component in two different ways:

* :doc:`Install it via Composer </components/using_components>`
  (``symfony/browser-kit`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/browser-kit).

Basic Usage
-----------

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
the :doc:`HttpKernel component </components/http_kernel/introduction>`.

Making Requests
~~~~~~~~~~~~~~~

Use the :method:`Symfony\\Component\\BrowserKit\\Client::request` method to
make HTTP requests. The first two arguments are the HTTP method and the requested
URL::

    use Acme\Client;

    $client = new Client();
    $crawler = $client->request('GET', 'http://symfony.com');

The value returned by the ``request()`` method is an instance of the
:class:`Symfony\\Component\\DomCrawler\\Crawler` class, provided by the
:doc:`DomCrawler component </components/dom_crawler>`, which allows accessing
and traversing HTML elements programmatically.

Clicking Links
~~~~~~~~~~~~~~

The ``Crawler`` object is capable of simulating link clicks. First, pass the
text content of the link to the ``selectLink()`` method, which returns a
``Link`` object. Then, pass this object to the ``click()`` method, which
performs the needed HTTP GET request to simulate the link click::

    use Acme\Client;

    $client = new Client();
    $crawler = $client->request('GET', 'http://symfony.com');
    $link = $crawler->selectLink('Go elsewhere...')->link();
    $client->click($link);

Submitting Forms
~~~~~~~~~~~~~~~~

The ``Crawler`` object is also capable of selecting forms. First, select any of
the form's buttons with the ``selectButton()`` method. Then, use the ``form()``
method to select the form which the button belongs to.

After selecting the form, fill in its data and send it using the ``submit()``
method (which makes the needed HTTP POST request to submit the form contents)::

    use Acme\Client;

    // make a real request to an external site
    $client = new Client();
    $crawler = $client->request('GET', 'https://github.com/login');

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

The ``Crawler`` object exposes cookies (if any) through a
:class:`Symfony\\Component\\BrowserKit\\CookieJar`, which allows you to store and
retrieve any cookie while making requests with the client::

    use Acme\Client;

    // Make a request
    $client = new Client();
    $crawler = $client->request('GET', 'http://symfony.com');

    // Get the cookie Jar
    $cookieJar = $crawler->getCookieJar();

    // Get a cookie by name
    $cookie = $cookieJar->get('name_of_the_cookie');

    // Get cookie data
    $name       = $cookie->getName();
    $value      = $cookie->getValue();
    $raw        = $cookie->getRawValue();
    $secure     = $cookie->isSecure();
    $isHttpOnly = $cookie->isHttpOnly();
    $isExpired  = $cookie->isExpired();
    $expires    = $cookie->getExpiresTime();
    $path       = $cookie->getPath();
    $domain     = $cookie->getDomain();

.. note::

    These methods only return cookies that have not expired.

Looping Through Cookies
~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    use Acme\Client;

    // Make a request
    $client = new Client();
    $crawler = $client->request('GET', 'http://symfony.com');

    // Get the cookie Jar
    $cookieJar = $crawler->getCookieJar();

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
    $cookieJar = new Cookie('flavor', 'chocolate', strtotime('+1 day'));

    // create a client and set the cookies
    $client = new Client(array(), array(), $cookieJar);
    // ...

History
-------

The client stores all your requests allowing you to go back and forward in your
history::

    use Acme\Client;

    // make a real request to an external site
    $client = new Client();
    $client->request('GET', 'http://symfony.com');

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

    // make a real request to an external site
    $client = new Client();
    $client->request('GET', 'http://symfony.com');

    // delete history
    $client->restart();

.. _`Packagist`: https://packagist.org/packages/symfony/browser-kit
.. _`Goutte`: https://github.com/fabpot/Goutte
