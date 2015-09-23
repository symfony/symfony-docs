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

Varnish automatically forwards the IP as ``X-Forwarded-For`` and leaves the
``X-Forwarded-Proto`` header in the request. If you do not configure Varnish as
trusted proxy, Symfony will see all requests as coming through insecure HTTP
connections from the Varnish host instead of the real client.

Remember to configure :ref:`framework.trusted_proxies <reference-framework-trusted-proxies>`
in the Symfony configuration so that Varnish is seen as a trusted proxy and the
:ref:`X-Forwarded <varnish-x-forwarded-headers>` headers are used.

.. _varnish-x-forwarded-headers:

Routing and X-FORWARDED Headers
-------------------------------

To ensure that the Symfony Router generates URLs correctly with Varnish,
an ``X-Forwarded-Port`` header must be present for Symfony to use the
correct port number.

This port number corresponds to the port your setup is using to receive external
connections (``80`` is the default value for HTTP connections). If the application
also accepts HTTPS connections, there could be another proxy (as Varnish does
not do HTTPS itself) on the default HTTPS port 443 that handles the SSL termination
and forwards the requests as HTTP requests to Varnish with an ``X-Forwarded-Proto``
header. In this case, you need to add the following configuration snippet:

.. code-block:: varnish4

    sub vcl_recv {
        if (req.http.X-Forwarded-Proto == "https" ) {
            set req.http.X-Forwarded-Port = "443";
        } else {
            set req.http.X-Forwarded-Port = "80";
        }
    }

Cookies and Caching
-------------------

By default, a sane caching proxy does not cache anything when a request is sent
with :ref:`cookies or a basic authentication header <http-cache-introduction>`.
This is because the content of the page is supposed to depend on the cookie
value or authentication header.

If you know for sure that the backend never uses sessions or basic
authentication, have Varnish remove the corresponding header from requests to
prevent clients from bypassing the cache. In practice, you will need sessions
at least for some parts of the site, e.g. when using forms with
:ref:`CSRF Protection <forms-csrf>`. In this situation, make sure to
:doc:`only start a session when actually needed </cookbook/session/avoid_session_start>`
and clear the session when it is no longer needed. Alternatively, you can look
into :doc:`/cookbook/cache/form_csrf_caching`.

Cookies created in JavaScript and used only in the frontend, e.g. when using
Google Analytics, are nonetheless sent to the server. These cookies are not
relevant for the backend and should not affect the caching decision. Configure
your Varnish cache to `clean the cookies header`_. You want to keep the
session cookie, if there is one, and get rid of all other cookies so that pages
are cached if there is no active session. Unless you changed the default
configuration of PHP, your session cookie has the name ``PHPSESSID``:

.. configuration-block::

    .. code-block:: varnish4

        sub vcl_recv {
            // Remove all cookies except the session ID.
            if (req.http.Cookie) {
                set req.http.Cookie = ";" + req.http.Cookie;
                set req.http.Cookie = regsuball(req.http.Cookie, "; +", ";");
                set req.http.Cookie = regsuball(req.http.Cookie, ";(PHPSESSID)=", "; \1=");
                set req.http.Cookie = regsuball(req.http.Cookie, ";[^ ][^;]*", "");
                set req.http.Cookie = regsuball(req.http.Cookie, "^[; ]+|[; ]+$", "");
    
                if (req.http.Cookie == "") {
                    // If there are no more cookies, remove the header to get page cached.
                    unset req.http.Cookie;
                }
            }
        }

    .. code-block:: varnish3

        sub vcl_recv {
            // Remove all cookies except the session ID.
            if (req.http.Cookie) {
                set req.http.Cookie = ";" + req.http.Cookie;
                set req.http.Cookie = regsuball(req.http.Cookie, "; +", ";");
                set req.http.Cookie = regsuball(req.http.Cookie, ";(PHPSESSID)=", "; \1=");
                set req.http.Cookie = regsuball(req.http.Cookie, ";[^ ][^;]*", "");
                set req.http.Cookie = regsuball(req.http.Cookie, "^[; ]+|[; ]+$", "");
    
                if (req.http.Cookie == "") {
                    // If there are no more cookies, remove the header to get page cached.
                    remove req.http.Cookie;
                }
            }
        }

.. tip::

    If content is not different for every user, but depends on the roles of a
    user, a solution is to separate the cache per group. This pattern is
    implemented and explained by the FOSHttpCacheBundle_ under the name
    `User Context`_.

Ensure Consistent Caching Behavior
----------------------------------

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

As explained in the :ref:`Edge Side Includes section <edge-side-includes>`,
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

    The ``abc`` part of the header isn't important unless you have multiple
    "surrogates" that need to advertise their capabilities. See
    `Surrogate-Capability Header`_ for details.

Then, optimize Varnish so that it only parses the response contents when there
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
    behavior, those VCL functions already exist. Just append the code
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
.. _`Clean the cookies header`: https://www.varnish-cache.org/trac/wiki/VCLExampleRemovingSomeCookies
.. _`Surrogate-Capability Header`: http://www.w3.org/TR/edge-arch
.. _`cache invalidation`: http://tools.ietf.org/html/rfc2616#section-13.10
.. _`FOSHttpCacheBundle`: http://foshttpcachebundle.readthedocs.org/
.. _`default.vcl`: https://www.varnish-cache.org/trac/browser/bin/varnishd/default.vcl?rev=3.0
.. _`builtin.vcl`: https://www.varnish-cache.org/trac/browser/bin/varnishd/builtin.vcl?rev=4.0
.. _`User Context`: http://foshttpcachebundle.readthedocs.org/en/latest/features/user-context.html
