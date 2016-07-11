.. index::
   single: PSR-7

The PSR-7 Bridge
================

    The PSR-7 bridge converts :doc:`HttpFoundation </components/http_foundation/index>`
    objects from and to objects implementing HTTP message interfaces defined
    by the `PSR-7`_.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (`symfony/psr-http-message-bridge on Packagist <https://packagist.org/packages/symfony/psr-http-message-bridge>`_);
* Use the official Git repository (https://github.com/symfony/psr-http-message-bridge).

The bridge also needs a PSR-7 implementation to allow converting HttpFoundation
objects to PSR-7 objects. It provides native support for `Zend Diactoros`_.
Use Composer (`zendframework/zend-diactoros on Packagist <https://packagist.org/packages/zendframework/zend-diactoros>`_)
or refer to the project documentation to install it.

Usage
-----

Converting from HttpFoundation Objects to PSR-7
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The bridge provides an interface of a factory called
:class:`Symfony\\Bridge\\PsrHttpMessage\\HttpMessageFactoryInterface`
that builds objects implementing PSR-7 interfaces from HttpFoundation objects.
It also provide a default implementation using Zend Diactoros internally.

The following code snippet explain how to convert a :class:`Symfony\\Component\\HttpFoundation\\Request`
to a Zend Diactoros :class:`Zend\\Diactoros\\ServerRequest` implementing the
:class:`Psr\\Http\\Message\\ServerRequestInterface` interface::

    use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
    use Symfony\Component\HttpFoundation\Request;

    $symfonyRequest = new Request(array(), array(), array(), array(), array(), array('HTTP_HOST' => 'dunglas.fr'), 'Content');
    // The HTTP_HOST server key must be set to avoid an unexpected error

    $psr7Factory = new DiactorosFactory();
    $psrRequest = $psr7Factory->createRequest($symfonyRequest);

And now from a :class:`Symfony\\Component\\HttpFoundation\\Response` to a Zend
Diactoros :class:`Zend\\Diactoros\\Response` implementing the :class:`Psr\\Http\\Message\\ResponseInterface`
interface::

    use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
    use Symfony\Component\HttpFoundation\Response;

    $symfonyResponse = new Response('Content');

    $psr7Factory = new DiactorosFactory();
    $psrResponse = $psr7Factory->createResponse($symfonyResponse);

Converting Objects implementing PSR-7 Interfaces to HttpFoundation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On the other hand, the bridge provide a factory interface called
:class:`Symfony\\Bridge\\PsrHttpMessage\\HttpFoundationFactoryInterface`
that builds HttpFoundation objects from objects implementing PSR-7 interfaces.

The next snippet explain how to convert an object implementing the :class:`Psr\\Http\\Message\\ServerRequestInterface`
interface to a :class:`Symfony\\Component\\HttpFoundation\\Request` instance::

    use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

    // $psrRequest is an instance of Psr\Http\Message\ServerRequestInterface

    $httpFoundationFactory = new HttpFoundationFactory();
    $symfonyRequest = $httpFoundationFactory->createRequest($psrRequest);

From an object implementing the :class:`Psr\\Http\\Message\\ResponseInterface`
to a :class:`Symfony\\Component\\HttpFoundation\\Response` instance::

    use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

    // $psrResponse is an instance of Psr\Http\Message\ResponseInterface

    $httpFoundationFactory = new HttpFoundationFactory();
    $symfonyResponse = $httpFoundationFactory->createResponse($psrResponse);

.. _`PSR-7`: http://www.php-fig.org/psr/psr-7/
.. _`Zend Diactoros`: https://github.com/zendframework/zend-diactoros
