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

Usage
-----

.. note::
    The component only provides an abstract client and does not provide any "default" backend for the HTTP layer.

Making Request
~~~~~~~~~~~~~~

To make a request you use the client's request method. 
The first two arguments are for the HTTP method and the request url.

.. code-block:: php

    use ACME\Client;

    $client = new Client();
    $response = $client->request('GET', 'http://symfony.com');

Clicking Links
~~~~~~~~~~~~~~

Submiting Forms
~~~~~~~~~~~~~~~~

Creating a Client
-----------------

For a simple implementation of a browser based on an HTTP layer, have a look at Goutte_.

For an implementation based on HttpKernelInterface, have a look at the Client provided by the :doc:`/components/http_kernel/introduction`.

.. _Packagist: https://packagist.org/packages/symfony/event-dispatcher
.. _Goutte: https://github.com/fabpot/Goutte