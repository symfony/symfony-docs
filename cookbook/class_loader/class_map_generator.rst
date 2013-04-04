.. index::
    single: ClassLoader; ClassMapGenerator

ClassMapGenerator Why?
======================

Because Symfony2's cache uses the standard HTTP cache headers, the
:ref:`symfony-gateway-cache` can easily be replaced with any other reverse
proxy. Varnish is a powerful, open-source, HTTP accelerator capable of serving
cached content quickly and including support for :ref:`Edge Side
Includes<edge-side-includes>`.

.. note::

    Varnish only supports the ``src`` attribute for ESI tags (``onerror`` and
    ``alt`` attributes are ignored).

First, configure Varnish so that it advertises its ESI support by adding a
``Surrogate-Capability`` header to requests forwarded to the backend
application:

.. code-block:: text

    sub vcl_recv {
        set req.http.Surrogate-Capability = "abc=ESI/1.0";
    }

Then, optimize Varnish so that it only parses the Response contents when there
is at least one ESI tag by checking the ``Surrogate-Control`` header that
Symfony2 adds automatically:

.. caution::

    You must protect the ``PURGE`` HTTP method somehow to avoid random people
    purging your cached data.

.. _`Edge Architecture`: http://www.w3.org/TR/edge-arch
.. _`GZIP and Varnish`: https://www.varnish-cache.org/docs/3.0/phk/gzip.html