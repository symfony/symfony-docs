.. index::
   single: WebLink
   single: Components; WebLink

The WebLink Component
======================

    The WebLink component provides tools to manage the ``Link`` HTTP header needed
    for `Web Linking`_ when using `HTTP/2 Server Push`_ as well as `Resource Hints`_.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/web-link

.. include:: /components/require_autoload.rst.inc

Usage
-----

The following example shows the component in action::

    use Symfony\Component\WebLink\GenericLinkProvider;
    use Symfony\Component\WebLink\HttpHeaderSerializer;
    use Symfony\Component\WebLink\Link;

    $linkProvider = (new GenericLinkProvider())
        ->withLink(new Link('preload', '/bootstrap.min.css'));

    header('Link: '.(new HttpHeaderSerializer())->serialize($linkProvider->getLinks()));

    echo 'Hello';

Read the full :doc:`WebLink documentation </web_link>` to learn about all the
features of the component and its integration with the Symfony framework.

.. _`Web Linking`: https://tools.ietf.org/html/rfc5988
.. _`HTTP/2 Server Push`: https://tools.ietf.org/html/rfc7540#section-8.2
.. _`Resource Hints`: https://www.w3.org/TR/resource-hints/
