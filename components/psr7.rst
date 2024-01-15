The PSR-7 Bridge
================

    The PSR-7 bridge converts :doc:`HttpFoundation </components/http_foundation>`
    objects from and to objects implementing HTTP message interfaces defined
    by the `PSR-7`_.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/psr-http-message-bridge

.. include:: /components/require_autoload.rst.inc

The bridge also needs a PSR-7 and `PSR-17`_ implementation to convert
HttpFoundation objects to PSR-7 objects. The following command installs the
``nyholm/psr7`` library, a lightweight and fast PSR-7 implementation, but you
can use any of the `libraries that implement psr/http-factory-implementation`_:

.. code-block:: terminal

    $ composer require nyholm/psr7

Usage
-----

Converting from HttpFoundation Objects to PSR-7
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The bridge provides an interface of a factory called
`HttpMessageFactoryInterface`_ that builds objects implementing PSR-7
interfaces from HttpFoundation objects.

The following code snippet explains how to convert a :class:`Symfony\\Component\\HttpFoundation\\Request`
to a ``Nyholm\Psr7\ServerRequest`` class implementing the
``Psr\Http\Message\ServerRequestInterface`` interface::

    use Nyholm\Psr7\Factory\Psr17Factory;
    use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
    use Symfony\Component\HttpFoundation\Request;

    $symfonyRequest = new Request([], [], [], [], [], ['HTTP_HOST' => 'dunglas.fr'], 'Content');
    // The HTTP_HOST server key must be set to avoid an unexpected error

    $psr17Factory = new Psr17Factory();
    $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
    $psrRequest = $psrHttpFactory->createRequest($symfonyRequest);

And now from a :class:`Symfony\\Component\\HttpFoundation\\Response` to a
``Nyholm\Psr7\Response`` class implementing the
``Psr\Http\Message\ResponseInterface`` interface::

    use Nyholm\Psr7\Factory\Psr17Factory;
    use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
    use Symfony\Component\HttpFoundation\Response;

    $symfonyResponse = new Response('Content');

    $psr17Factory = new Psr17Factory();
    $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
    $psrResponse = $psrHttpFactory->createResponse($symfonyResponse);

Converting Objects implementing PSR-7 Interfaces to HttpFoundation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On the other hand, the bridge provide a factory interface called
`HttpFoundationFactoryInterface`_ that builds HttpFoundation objects from
objects implementing PSR-7 interfaces.

The next snippet explain how to convert an object implementing the
``Psr\Http\Message\ServerRequestInterface`` interface to a
:class:`Symfony\\Component\\HttpFoundation\\Request` instance::

    use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

    // $psrRequest is an instance of Psr\Http\Message\ServerRequestInterface

    $httpFoundationFactory = new HttpFoundationFactory();
    $symfonyRequest = $httpFoundationFactory->createRequest($psrRequest);

From an object implementing the ``Psr\Http\Message\ResponseInterface``
to a :class:`Symfony\\Component\\HttpFoundation\\Response` instance::

    use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

    // $psrResponse is an instance of Psr\Http\Message\ResponseInterface

    $httpFoundationFactory = new HttpFoundationFactory();
    $symfonyResponse = $httpFoundationFactory->createResponse($psrResponse);

.. _`PSR-7`: https://www.php-fig.org/psr/psr-7/
.. _`PSR-17`: https://www.php-fig.org/psr/psr-17/
.. _`libraries that implement psr/http-factory-implementation`: https://packagist.org/providers/psr/http-factory-implementation
.. _`HttpMessageFactoryInterface`: https://github.com/symfony/psr-http-message-bridge/blob/main/HttpMessageFactoryInterface.php
.. _`HttpFoundationFactoryInterface`: https://github.com/symfony/psr-http-message-bridge/blob/main/HttpFoundationFactoryInterface.php
