.. index::
   single: BrowserKit
   single: Components; BrowserKit

The BrowserKit Component
========================

    The BrowserKit component simulates the behavior of a web browser.

The BrowserKit component allows you to make web request, click on links and submit forms. 

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/browser-kit`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/BrowserKit).

Basic Usage
-----------

.. note::
    The component only provides an abstract client and does not provide any "default" backend for the HTTP layer.

Creating a Client
-----------------

To create your own client you must extend the abstract client class and implement the doRequest method.
This method accepts a request and should return a response.

.. code-block:: php

    namespace ACME;

    use Symfony\Component\BrowserKit\Client as BaseClient;
    use Symfony\Component\BrowserKit\Response;

    class Client extends BaseClient {
        protected function doRequest($request) {
            // convert request into a response
            // ...
            return new Response($content, $status, $headers);
        }
    }

For a simple implementation of a browser based on an HTTP layer, have a look at Goutte_.

For an implementation based on HttpKernelInterface, have a look at the Client provided by the :doc:`/components/http_kernel/introduction`.


Making Request
~~~~~~~~~~~~~~

To make a request you use the client's request method. 
The first two arguments are for the HTTP method and the request url.
The request method will return a crawler object.

.. code-block:: php

    use ACME\Client;

    $client = new Client();
    $crawler = $client->request('GET', 'http://symfony.com');

Clicking Links
~~~~~~~~~~~~~~

Select a link with the crawler and pass it to the click method to click on the link.

.. code-block:: php

    use ACME\Client;

    $client = new Client();
    $crawler = $client->request('GET', 'http://symfony.com');
    $link = $crawler->selectLink('Go elsewhere...')->link();
    $client->click($link);

Submiting Forms
~~~~~~~~~~~~~~~

You can submit forms with the submit method which takes a form object.
You can get the form object by using the crawler to select the button and running the form method.

.. code-block:: php

    use ACME\Client;

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

Retreiving Cookies
~~~~~~~~~~~~~~~~~~ 

The Crawler has a cookieJar which is a container for storing and recieving cookies.

.. code-block:: php

    use ACME\Client;

    // Make a request
    $client = new Client();
    $crawler = $client->request('GET', 'http://symfony.com');

    // Get the cookie Jar
    $cookieJar = $crawler->getCookieJar();

    // Get a cookie by name
    $flavor = $cookieJar->get('flavor');

    // Get cookie data
    $name = $flavor->getName();
    $value = $flavor->getValue();
    $raw = $flavor->getRawValue();
    $secure = $flavor->isSecure();
    $isHttpOnly = $flavor->isHttpOnly();
    $isExpired = $flavor->isExpired();
    $expires = $flavor->getExpiresTime();
    $path = $flavor->getPath();
    $domain = $flavor->getDomain();

Looping Through Cookies
~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    use ACME\Client;

    // Make a request
    $client = new Client();
    $crawler = $client->request('GET', 'http://symfony.com');

    // Get the cookie Jar
    $cookieJar = $crawler->getCookieJar();

    // Get array with all cookies
    $cookies = $cookieJar->all();
    foreach($cookies as $cookie) 
    {
        // ...
    }

    // Get all values
    $values = $cookieJar->allValues('http://symfony.com');
    foreach($values as $value)
    {
        // ...
    }

    // Get all raw values
    $rawValues = $cookieJar->allRawValues('http://symfony.com');
    foreach($rawValues as $rawValue)
    {
        // ...
    }

.. note::
    These cookie jar methods only return cookies that have not expired.

Setting Cookies
~~~~~~~~~~~~~~~

You can define create cookies and add them to a cookie jar that can be injected it into the client constructor. 

.. code-block:: php

    use ACME\Client;

    // create cookies and add to cookie jar
    $expires = new \DateTime();
    $expires->add(new \DateInterval('P1D'));
    $cookie = new Cookie(
        'flavor',
        'chocolate chip',
        $now->getTimestamp()
    );

    // create a client and set the cookies
    $client = new Client(array(), array(), $cookieJar);
    // ...

History
-------

The client stores all your request allowing you to go back and forward in your history.

.. code-block:: php

    use ACME\Client;

    // make a real request to an external site
    $client = new Client();
    $home_crawler = $client->request('GET', 'http://symfony.com');

    // select and click on a link
    $doc_link = $crawler->selectLink('Documentation')->link();
    $doc_crawler = $client->click($link);

    // go back to home page
    $home_crawler = $client->back();

    // go forward to documentation page
    $doc_crawler = $client->forward();

You can restart the clients history with the restart method. This will also clear out the CookieJar.

.. code-block:: php

    use ACME\Client;

    // make a real request to an external site
    $client = new Client();
    $home_crawler = $client->request('GET', 'http://symfony.com');

    // restart history
    $client->restart();


.. _Packagist: https://packagist.org/packages/symfony/browser-kit
.. _Goutte: https://github.com/fabpot/Goutte