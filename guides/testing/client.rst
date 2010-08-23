.. index::
   single: Tests; Client

The Test Client
===============

The test Client simulates an HTTP client like a browser.

.. note::
   The test Client is based on the ``BrowserKit`` and the ``Crawler`` components.

Making Requests
---------------

The client knows how make requests to a Symfony2 application::

    $crawler = $client->request('GET', '/hello/Fabien');

The ``request()`` method takes the HTTP method and a URL as arguments and
returns a ``Crawler`` instance.

Use the Crawler to find DOM elements in the Response. These elements can then
be used to click on links and submit forms::

    $link = $crawler->selectLink('Go elsewhere...')->link();
    $crawler = $client->click($link);

    $form = $crawler->selectButton('validate')->form();
    $crawler = $client->submit($form, array('name' => 'Fabien'));

The ``click()`` and ``submit()`` methods both return a ``Crawler`` object. These
methods is the best way to browse an application as it hides a lot of details.
For instance, when you submit a form, it automatically detects the HTTP method
and the form URL, it gives you a nice API to upload files, and it merges the
submitted values with the form default ones, and more.

.. tip::
   The Crawler is documented in its own :doc:`section <crawler>`. Read it to learn more about
   the ``Link`` and ``Form`` objects.

But you can also simulate form submissions and complex requests with the
additional arguments of the ``request()`` method::

    // Form submission
    $client->request('POST', '/submit', array('name' => 'Fabien'));

    // Form submission with a file upload
    $client->request('POST', '/submit', array('name' => 'Fabien'), array('photo' => '/path/to/photo'));

    // Specify HTTP headers
    $client->request('DELETE', '/post/12', array(), array(), array('PHP_AUTH_USER' => 'username', 'PHP_AUTH_PW' => 'pa$$word'));

When a request returns a redirect response, the client automatically follows
it. This behavior can be changed with the ``followRedirects()`` method::

    $client->followRedirects(false);

When the client does not follow redirects, you can force the redirection with
the ``followRedirect()`` method::

    $crawler = $client->followRedirect();

Last but not the least, you can force each request to be executed in its own
PHP process to avoid any side-effects when working with several clients in the
same script::

    $client->insulate();

Browsing
--------

The Client supports many operations that can be done in a real browser::

    $client->back();
    $client->forward();
    $client->reload();

    // Clears all cookies and the history
    $client->restart();

Accessing Internal Objects
--------------------------

If you use the client to test your application, you might want to access the
client internal objects::

    $history = $client->getHistory();
    $cookieJar = $client->getCookieJar();

You can also get the objects related to the latest request::

    $request = $client->getRequest();
    $response = $client->getResponse();
    $crawler = $client->getCrawler();
    $profiler = $client->getProfiler();

If your requests are not insulated, you can also access the ``Container`` and
the ``Kernel``::

    $container = $client->getContainer();
    $kernel = $client->getKernel();
