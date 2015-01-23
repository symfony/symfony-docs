.. index::
    single: Cache; Varnish

How to Use Varnish to Speed up my Website
=========================================

Because Symfony's cache uses the standard HTTP cache headers, the
:ref:`symfony-gateway-cache` can easily be replaced with any other reverse
proxy. `Varnish`_ is a powerful, open-source, HTTP accelerator capable of serving
cached content fast and including support for :ref:`Edge Side Includes <edge-side-includes>`.

.. index::
    single: Varnish; configuration

Make Symfony Trust the Reverse Proxy
------------------------------------

For ESI to work correctly and for the :ref:`X-FORWARDED <varnish-x-forwarded-headers>`
headers to be used, you need to configure Varnish as a
:doc:`trusted proxy </cookbook/request/load_balancer_reverse_proxy>`.

.. _varnish-x-forwarded-headers:

Routing and X-FORWARDED Headers
-------------------------------

To ensure that the Symfony Router generates URLs correctly with Varnish,
a ``X-Forwarded-Port`` header must be present for Symfony to use the
correct port number.

This port depends on your setup. Lets say that external connections come in
on the default HTTP port 80. For HTTPS connections, there is another proxy
(as Varnish does not do HTTPS itself) on the default HTTPS port 443 that
handles the SSL termination and forwards the requests as HTTP requests to
Varnish with a ``X-Forwarded-Proto`` header. In this case, you need to add
the following configuration snippet:

.. code-block:: varnish4

    sub vcl_recv {
        if (req.http.X-Forwarded-Proto == "https" ) {
            set req.http.X-Forwarded-Port = "443";
        } else {
            set req.http.X-Forwarded-Port = "80";
        }
    }

.. note::

    Remember to configure :ref:`framework.trusted_proxies <reference-framework-trusted-proxies>`
    in the Symfony configuration so that Varnish is seen as a trusted proxy
    and the ``X-Forwarded-*`` headers are used.

    Varnish automatically forwards the IP as ``X-Forwarded-For`` and leaves
    the ``X-Forwarded-Proto`` header in the request. If you do not configure
    Varnish as trusted proxy, Symfony will see all requests as coming through
    insecure HTTP connections from the Varnish host instead of the real client.

If the ``X-Forwarded-Port`` header is not set correctly, Symfony will append
the port where the PHP application is running when generating absolute URLs,
e.g. ``http://example.com:8080/my/path``.

Ensure Consistent Caching Behaviour
-----------------------------------

Varnish uses the cache headers sent by your application to determine how
to cache content. However, versions prior to Varnish 4 did not respect
``Cache-Control: no-cache``, ``no-store`` and ``private``. To ensure
consistent behavior, use the following configuration if you are still
using Varnish 3:

.. configuration-block::

    .. code-block:: varnish3

        sub vcl_fetch {
            /* By default, Varnish3 ignores Cache-Control: no-cache and private
               https://www.varnish-cache.org/docs/3.0/tutorial/increasing_your_hitrate.html#cache-control
             */
            if (beresp.http.Cache-Control ~ "private" ||
                beresp.http.Cache-Control ~ "no-cache" ||
                beresp.http.Cache-Control ~ "no-store"
            ) {
                return (hit_for_pass);
            }
        }

.. tip::

    You can see the default behavior of Varnish in the form of a VCL file:
    `default.vcl`_ for Varnish 3, `builtin.vcl`_ for Varnish 4.

Enable Edge Side Includes (ESI)
-------------------------------

As explained in the :ref:`Edge Side Includes section<edge-side-includes>`,
Symfony detects whether it talks to a reverse proxy that understands ESI or
not. When you use the Symfony reverse proxy, you don't need to do anything.
But to make Varnish instead of Symfony resolve the ESI tags, you need some
configuration in Varnish. Symfony uses the ``Surrogate-Capability`` header
from the `Edge Architecture`_ described by Akamai.

.. note::

    Varnish only supports the ``src`` attribute for ESI tags (``onerror`` and
    ``alt`` attributes are ignored).

First, configure Varnish so that it advertises its ESI support by adding a
``Surrogate-Capability`` header to requests forwarded to the backend
application:

.. code-block:: varnish4

    sub vcl_recv {
        // Add a Surrogate-Capability header to announce ESI support.
        set req.http.Surrogate-Capability = "abc=ESI/1.0";
    }

.. note::

    The ``abc`` part of the header isn't important unless you have multiple "surrogates"
    that need to advertise their capabilities. See `Surrogate-Capability Header`_ for details.

Then, optimize Varnish so that it only parses the Response contents when there
is at least one ESI tag by checking the ``Surrogate-Control`` header that
Symfony adds automatically:

.. configuration-block::

    .. code-block:: varnish4

        sub vcl_backend_response {
            // Check for ESI acknowledgement and remove Surrogate-Control header
            if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
                unset beresp.http.Surrogate-Control;
                set beresp.do_esi = true;
            }
        }

    .. code-block:: varnish3

        sub vcl_fetch {
            // Check for ESI acknowledgement and remove Surrogate-Control header
            if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
                unset beresp.http.Surrogate-Control;
                set beresp.do_esi = true;
            }
        }

.. tip::

    If you followed the advice about ensuring a consistent caching
    behavior, those vcl functions already exist. Just append the code
    to the end of the function, they won't interfere with each other.

.. index::
    single: Varnish; Invalidation

Cache Invalidation
------------------

If you want to cache content that changes frequently and still serve
the most recent version to users, you need to invalidate that content.
While `cache invalidation`_ allows you to purge content from your
proxy before it has expired, it adds complexity to your caching setup.

.. tip::

    The open source `FOSHttpCacheBundle`_ takes the pain out of cache
    invalidation by helping you to organize your caching and
    invalidation setup.

    The documentation of the `FOSHttpCacheBundle`_ explains how to configure
    Varnish and other reverse proxies for cache invalidation.

.. _`Varnish`: https://www.varnish-cache.org
.. _`Edge Architecture`: http://www.w3.org/TR/edge-arch
.. _`GZIP and Varnish`: https://www.varnish-cache.org/docs/3.0/phk/gzip.html
.. _`Surrogate-Capability Header`: http://www.w3.org/TR/edge-arch
.. _`cache invalidation`: http://tools.ietf.org/html/rfc2616#section-13.10
.. _`FOSHttpCacheBundle`: http://foshttpcachebundle.readthedocs.org/
.. _`default.vcl`: https://www.varnish-cache.org/trac/browser/bin/varnishd/default.vcl?rev=3.0
.. _`builtin.vcl`: https://www.varnish-cache.org/trac/browser/bin/varnishd/builtin.vcl?rev=4.0
