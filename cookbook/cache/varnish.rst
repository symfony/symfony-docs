.. index::
    single: Cache; Varnish

How to use Varnish to speedup my Website
========================================

Because Symfony2's cache uses the standard HTTP cache headers, the
:ref:`symfony-gateway-cache` can easily be replaced with any other reverse
proxy. Varnish is a powerful, open-source, HTTP accelerator capable of serving
cached content quickly and including support for :ref:`Edge Side
Includes<edge-side-includes>`.

.. index::
    single: Varnish; configuration

Configuration
-------------

As seen previously, Symfony2 is smart enough to detect whether it talks to a
reverse proxy that understands ESI or not. It works out of the box when you
use the Symfony2 reverse proxy, but you need a special configuration to make
it work with Varnish. Thankfully, Symfony2 relies on yet another standard
written by Akamaï (`Edge Architecture`_), so the configuration tips in this
chapter can be useful even if you don't use Symfony2.

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

.. code-block:: text

    sub vcl_fetch {
        if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
            unset beresp.http.Surrogate-Control;
            esi;
        }
    }

.. caution::

    Don't use compression with ESI as Varnish won't be able to parse the
    response content. If you want to use compression, put a web server in
    front of Varnish to do the job.

.. index::
    single: Varnish; Invalidation

Cache Invalidation
------------------

You should never need to invalidate cached data because invalidation is already
taken into account natively in the HTTP cache models (see :ref:`http-cache-invalidation`).

Still, Varnish can be configured to accept a special HTTP ``PURGE`` method
that will invalidate the cache for a given resource:

.. code-block:: text

    sub vcl_hit {
        if (req.request == "PURGE") {
            set obj.ttl = 0s;
            error 200 "Purged";
        }
    }

    sub vcl_miss {
        if (req.request == "PURGE") {
            error 404 "Not purged";
        }
    }

.. caution::

    You must protect the ``PURGE`` HTTP method somehow to avoid random people
    purging your cached data.

.. _`Edge Architecture`: http://www.w3.org/TR/edge-arch