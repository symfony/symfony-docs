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

History
-------

Insulated Request
-----------------

.. _Packagist: https://packagist.org/packages/symfony/browser-kit
.. _Goutte: https://github.com/fabpot/Goutte