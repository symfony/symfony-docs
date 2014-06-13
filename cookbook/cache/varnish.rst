.. index::
    single: Cache; Varnish

How to use Varnish to speed up my Website
=========================================

Because Symfony2's cache uses the standard HTTP cache headers, the
:ref:`symfony-gateway-cache` can easily be replaced with any other reverse
proxy. `Varnish`_ is a powerful, open-source, HTTP accelerator capable of serving
cached content quickly and including support for :ref:`Edge Side Includes <edge-side-includes>`.

.. index::
    single: Varnish; configuration

Configuration
-------------

As seen previously, Symfony2 is smart enough to detect whether it talks to a
reverse proxy that understands ESI or not. It works out of the box when you
use the Symfony2 reverse proxy, but you need a special configuration to make
it work with Varnish. Thankfully, Symfony2 relies on yet another standard
written by Akamai (`Edge Architecture`_), so the configuration tips in this
chapter can be useful even if you don't use Symfony2.

.. note::

    Varnish only supports the ``src`` attribute for ESI tags (``onerror`` and
    ``alt`` attributes are ignored).

First, configure Varnish so that it advertises its ESI support by adding a
``Surrogate-Capability`` header to requests forwarded to the backend
application:

.. code-block:: text

    sub vcl_recv {
        // Add a Surrogate-Capability header to announce ESI support.
        set req.http.Surrogate-Capability = "abc=ESI/1.0";
    }

Then, optimize Varnish so that it only parses the Response contents when there
is at least one ESI tag by checking the ``Surrogate-Control`` header that
Symfony2 adds automatically:

.. code-block:: text

    sub vcl_fetch {
        /*
        Check for ESI acknowledgement
        and remove Surrogate-Control header
        */
        if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
            unset beresp.http.Surrogate-Control;

            // For Varnish >= 3.0
            set beresp.do_esi = true;
            // For Varnish < 3.0
            // esi;
        }
        /* By default Varnish ignores Cache-Control: nocache 
        (https://www.varnish-cache.org/docs/3.0/tutorial/increasing_your_hitrate.html#cache-control),
        so in order avoid caching it has to be done explicitly */
        if (beresp.http.Pragma ~ "no-cache" ||
             beresp.http.Cache-Control ~ "no-cache" ||
             beresp.http.Cache-Control ~ "private") {
            return (hit_for_pass);
        }
    }

.. caution::

    Compression with ESI was not supported in Varnish until version 3.0
    (read `GZIP and Varnish`_). If you're not using Varnish 3.0, put a web
    server in front of Varnish to perform the compression.

.. index::
    single: Varnish; Invalidation

Cache Invalidation
------------------

You should never need to invalidate cached data because invalidation is already
taken into account natively in the HTTP cache models (see :ref:`http-cache-invalidation`).

Still, Varnish can be configured to accept a special HTTP ``PURGE`` method
that will invalidate the cache for a given resource:

.. code-block:: text

    /*
     Connect to the backend server
     on the local machine on port 8080
     */
    backend default {
        .host = "127.0.0.1";
        .port = "8080";
    }

    sub vcl_recv {
        /*
        Varnish default behavior doesn't support PURGE.
        Match the PURGE request and immediately do a cache lookup,
        otherwise Varnish will directly pipe the request to the backend
        and bypass the cache
        */
        if (req.request == "PURGE") {
            return(lookup);
        }
    }

    sub vcl_hit {
        // Match PURGE request
        if (req.request == "PURGE") {
            // Force object expiration for Varnish < 3.0
            set obj.ttl = 0s;
            // Do an actual purge for Varnish >= 3.0
            // purge;
            error 200 "Purged";
        }
    }

    sub vcl_miss {
        /*
        Match the PURGE request and
        indicate the request wasn't stored in cache.
        */
        if (req.request == "PURGE") {
            error 404 "Not purged";
        }
    }

.. caution::

    You must protect the ``PURGE`` HTTP method somehow to avoid random people
    purging your cached data. You can do this by setting up an access list:

    .. code-block:: text

        /*
         Connect to the backend server
         on the local machine on port 8080
         */
        backend default {
            .host = "127.0.0.1";
            .port = "8080";
        }

        // ACL's can contain IP's, subnets and hostnames
        acl purge {
            "localhost";
            "192.168.55.0"/24;
        }

        sub vcl_recv {
            // Match PURGE request to avoid cache bypassing
            if (req.request == "PURGE") {
                // Match client IP to the ACL
                if (!client.ip ~ purge) {
                    // Deny access
                    error 405 "Not allowed.";
                }
                // Perform a cache lookup
                return(lookup);
            }
        }

        sub vcl_hit {
            // Match PURGE request
            if (req.request == "PURGE") {
                // Force object expiration for Varnish < 3.0
                set obj.ttl = 0s;
                // Do an actual purge for Varnish >= 3.0
                // purge;
                error 200 "Purged";
            }
        }

        sub vcl_miss {
            // Match PURGE request
            if (req.request == "PURGE") {
                // Indicate that the object isn't stored in cache
                error 404 "Not purged";
            }
        }

Routing and X-FORWARDED Headers
-------------------------------

To ensure that the Symfony Router generates URLs correctly with Varnish,
proper ```X-Forwarded``` headers must be added so that Symfony is aware of
the original port number of the request. Exactly how this is done depends
on your setup. As a simple example, Varnish and your web server are on the
same machine and that Varnish is listening on one port (e.g. 80) and Apache
on another (e.g. 8080). In this situation, Varnish should add the ``X-Forwarded-Port``
header so that the Symfony application knows that the original port number
is 80 and not 8080.

If this header weren't set properly, Symfony may append ``8080`` when generating
absolute URLs:

.. code-block:: text

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
    and the ``X-Forwarded-`` headers are used.

.. _`Varnish`: https://www.varnish-cache.org
.. _`Edge Architecture`: http://www.w3.org/TR/edge-arch
.. _`GZIP and Varnish`: https://www.varnish-cache.org/docs/3.0/phk/gzip.html
