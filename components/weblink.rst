.. index::
   single: WebLink
   single: Components; WebLink

The WebLink Component
======================

   The WebLink component provides tools to create `Web Links`_.
   It allows to easily leverage `HTTP/2 Server Push`_ as well as `Resource Hints`_.

.. versionadded:: 3.3
    The WebLink component was introduced in Symfony 3.3.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/weblink

Alternatively, you can clone the `<https://github.com/symfony/weblink>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Basic usage::

   use Fig\Link\GenericLinkProvider;
   use Fig\Link\Link;
   use Symfony\Component\WebLink\HttpHeaderSerializer;

   $linkProvider = (new GenericLinkProvider())
       ->withLink(new Link('preload', '/bootstrap.min.css'));

   header('Link: '.(new HttpHeaderSerializer())->serialize($linkProvider->getLinks()));

   echo 'Hello';


.. seealso::

    Read the :doc:`WebLink documentation </weblink>`_ to learn how
    to use the features implemented by this component.

.. _`Web Links`: https://tools.ietf.org/html/rfc5988
.. _`HTTP/2 Server Push`: https://tools.ietf.org/html/rfc7540#section-8.2
.. _`Resource Hints`: https://www.w3.org/TR/resource-hints/
