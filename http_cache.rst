.. index::
   single: Cache

HTTP Cache
==========

The nature of rich web applications means that they're dynamic. No matter
how efficient your application, each request will always contain more overhead
than serving a static file. Usually, that's fine. But when you need your requests
to be lightning fast, you need HTTP caching.

Caching on the Shoulders of Giants
----------------------------------

With HTTP Caching, you cache the full output of a page (i.e. the response) and bypass
your application *entirely* on subsequent requests. Of course, caching entire responses
isn't always possible for highly dynamic sites, or is it? With
:doc:`Edge Side Includes (ESI) </http_cache/esi>`, you can use the power of HTTP caching
on only *fragments* of your site.

The Symfony cache system is different because it relies on the simplicity
and power of the HTTP cache as defined in `RFC 7234 - Caching`_. Instead of
reinventing a caching methodology, Symfony embraces the standard that defines
basic communication on the Web. Once you understand the fundamental HTTP
validation and expiration caching models, you'll be ready to master the Symfony
cache system.

Since caching with HTTP isn't unique to Symfony, many articles already exist
on the topic. If you're new to HTTP caching, Ryan Tomayko's article
`Things Caches Do`_ is *highly* recommended. Another in-depth resource is Mark
Nottingham's `Cache Tutorial`_.

.. index::
   single: Cache; Proxy
   single: Cache; Reverse proxy
   single: Cache; Gateway

.. _gateway-caches:

Caching with a Gateway Cache
----------------------------

When caching with HTTP, the *cache* is separated from your application entirely
and sits between your application and the client making the request.

The job of the cache is to accept requests from the client and pass them
back to your application. The cache will also receive responses back from
your application and forward them on to the client. The cache is the "middle-man"
of the request-response communication between the client and your application.

Along the way, the cache will store each response that is deemed "cacheable"
(See :ref:`http-cache-introduction`). If the same resource is requested again,
the cache sends the cached response to the client, ignoring your application
entirely.

This type of cache is known as an HTTP gateway cache and many exist such
as `Varnish`_, `Squid in reverse proxy mode`_, and the Symfony reverse proxy.

.. tip::

    Gateway caches are sometimes referred to as reverse proxy caches,
    surrogate caches, or even HTTP accelerators.

.. index::
   single: Cache; Symfony reverse proxy

.. _`symfony-gateway-cache`:
.. _symfony2-reverse-proxy:

Symfony Reverse Proxy
~~~~~~~~~~~~~~~~~~~~~

Symfony comes with a reverse proxy (i.e. gateway cache) written in PHP.
:ref:`It's not a fully-featured reverse proxy cache like Varnish <http-cache-symfony-versus-varnish>`,
but is a great way to start.

.. tip::

    For details on setting up Varnish, see :doc:`/http_cache/varnish`.

Enabling the proxy is easy: each application comes with a caching kernel (``AppCache``)
that wraps the default one (``AppKernel``). The caching Kernel *is* the reverse
proxy.

To enable caching, modify the code of your front controller. You can also make these
changes to ``app_dev.php`` to add caching to the ``dev`` environment::

    // web/app.php
    // ...

    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();

    // add (or uncomment) this new line!
    // wrap the default AppKernel with the AppCache one
    $kernel = new AppCache($kernel);

    $request = Request::createFromGlobals();
    // ...

The caching kernel will immediately act as a reverse proxy: caching responses
from your application and returning them to the client.

.. caution::

    If you're using the :ref:`framework.http_method_override <configuration-framework-http_method_override>`
    option to read the HTTP method from a ``_method`` parameter, see the
    above link for a tweak you need to make.

.. tip::

    The cache kernel has a special ``getLog()`` method that returns a string
    representation of what happened in the cache layer. In the development
    environment, use it to debug and validate your cache strategy::

        error_log($kernel->getLog());

The ``AppCache`` object has a sensible default configuration, but it can be
finely tuned via a set of options you can set by overriding the
:method:`Symfony\\Bundle\\FrameworkBundle\\HttpCache\\HttpCache::getOptions`
method::

    // app/AppCache.php
    use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;

    class AppCache extends HttpCache
    {
        protected function getOptions()
        {
            return array(
                'default_ttl' => 0,
                // ...
            );
        }
    }

For a full list of the options and their meaning, see the
:method:`HttpCache::__construct() documentation <Symfony\\Component\\HttpKernel\\HttpCache\\HttpCache::__construct>`.

When you're in debug mode (either because your booting a ``debug`` kernel, like
in ``app_dev.php`` *or* you manually set the ``debug`` option to true), Symfony
automatically adds an ``X-Symfony-Cache`` header to the response. Use this to get
information about cache hits and misses.

.. _http-cache-symfony-versus-varnish:

.. sidebar:: Changing from one Reverse Proxy to another

    The Symfony reverse proxy is a great tool to use when developing your
    website or when you deploy your website to a shared host where you cannot
    install anything beyond PHP code. But being written in PHP, it cannot
    be as fast as a proxy written in C.
    
    Fortunately, since all reverse proxies are effectively the same, you should
    be able to switch to something more robust - like Varnish - without any problems.
    See :doc:`How to use Varnish </http_cache/varnish>`

.. index::
   single: Cache; HTTP

.. _http-cache-introduction:

Making your Responses HTTP Cacheable
------------------------------------

Once you've added a reverse proxy cache (e.g. like the Symfony reverse proxy or Varnish),
you're ready to cache your responses. To do that, you need to *communicate* to your
cache *which* responses are cacheable and for how long. This is done by setting HTTP
cache headers on the response.

HTTP specifies four response cache headers that you can set to enable caching:

* ``Cache-Control``
* ``Expires``
* ``ETag``
* ``Last-Modified``

These four headers are used to help cache your responses via *two* different models:

.. _http-expiration-validation:
.. _http-expiration-and-validation:

#. :ref:`Expiration Caching <http-cache-expiration-intro>`
   Used to cache your entire response for a specific amount of time (e.g. 24 hours).
   Simple, but cache invalidation is more difficult.

#. :ref:`Validation Caching <http-cache-validation-intro>`
   More complex: used to cache your response, but allows you to dynamically invalidate
   it as soon as your content changes.

.. sidebar:: Reading the HTTP Specification

    All of the HTTP headers you'll read about are *not* invented by Symfony! They're
    part of an HTTP specification that's used by sites all over the web. To dig deeper
    into HTTP Caching, check out the documents `RFC 7234 - Caching`_ and 
    `RFC 7232 - Conditional Requests`_.

    As a web developer, you are strongly urged to read the specification. Its
    clarity and power - even more than fifteen years after its creation - is
    invaluable. Don't be put-off by the appearance of the spec - its contents
    are much more beautiful than its cover!

.. index::
   single: Cache; Expiration

.. _http-cache-expiration-intro:

Expiration Caching
~~~~~~~~~~~~~~~~~~

The *easiest* way to cache a response is by caching it for a specific amount of time::

    // src/AppBundle/Controller/BlogController.php
    use Symfony\Component\HttpFoundation\Response;
    // ...

    public function indexAction()
    {
        // somehow create a Response object, like by rendering a template
        $response = $this->render('blog/index.html.twig', []);

        // cache for 3600 seconds
        $response->setSharedMaxAge(3600);

        // (optional) set a custom Cache-Control directive
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

Thanks to this new code, your HTTP response will have the following header:

.. code-block:: text

    Cache-Control: public, s-maxage=3600, must-revalidate

This tells your HTTP reverse proxy to cache this response for 3600 seconds. If *anyone*
requests this URL again before 3600 seconds, your application *won't* be hit at all.
If you're using the Symfony reverse proxy, look at the ``X-Symfony-Cache`` header
for debugging information about cache hits and misses.

.. tip::

    The URI of the request is used as the cache key (unless you :doc:`vary </http_cache/cache_vary>`).

This is *super* performant and simple to use. But, cache *invalidation* is not supported.
If your content change, you'll need to wait until your cache expires for the page
to update.

.. tip::

    Actually, you *can* manually invalidate your cache, but it's not part of the
    HTTP Caching spec. See :ref:`http-cache-invalidation`.

If you need to set cache headers for many different controller actions, check out
`FOSHttpCacheBundle`_. It provides a way to define cache headers based on the URL
pattern and other request properties.

Finally, for more information about expiration caching, see :doc:`/http_cache/expiration`.

.. _http-cache-validation-intro:

Validation Caching
~~~~~~~~~~~~~~~~~~

.. index::
   single: Cache; Cache-Control header
   single: HTTP headers; Cache-Control

With expiration caching, you simply say "cache for 3600 seconds!". But, when someone
updates cached content, you won't see that content on your site until the cache
expires.

If you need to see updated content *immediately*, you either need to
:ref:`invalidate <http-cache-invalidation>` your cache *or* use the validation
caching model.

For details, see :doc:`/http_cache/validation`.

.. index::
   single: Cache; Safe methods

Safe Methods: Only caching GET or HEAD requests
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

HTTP caching only works for "safe" HTTP methods (like GET and HEAD). This means
two things:

* Don't try to cache PUT, POST or DELETE requests. It won't work and with good
  reason. These methods are meant to be used when mutating the state of your application
  (e.g. deleting a blog post). Caching them would prevent certain requests from hitting
  and mutating your application. (Technically caching POST requests is possible however this is very narrow case `Caching POST`_

* You should *never* change the state of your application (e.g. update a blog post)
  when responding to a GET or HEAD request. If those requests are cached, future
  requests may not actually hit your server.

.. index::
   pair: Cache; Configuration

More Response Methods
~~~~~~~~~~~~~~~~~~~~~

The Response class provides many more methods related to the cache. Here are
the most useful ones::

    // Marks the Response stale
    $response->expire();

    // Force the response to return a proper 304 response with no content
    $response->setNotModified();

Additionally, most cache-related HTTP headers can be set via the single
:method:`Symfony\\Component\\HttpFoundation\\Response::setCache` method::

    // Set cache settings in one call
    $response->setCache(array(
        'etag'          => $etag,
        'last_modified' => $date,
        'max_age'       => 10,
        's_maxage'      => 10,
        'public'        => true,
        // 'private'    => true,
    ));

Cache Invalidation
------------------

Cache invalidation is *not* part of the HTTP specification. Still, it can be really
useful to delete various HTTP cache entries as soon as some content on your site
is updated.

For details, see :doc:`/http_cache/cache_invalidation`.

Using Edge Side Includes
------------------------

When pages contain dynamic parts, you may not be able to cache entire pages,
but only parts of it. Read :doc:`/http_cache/esi` to find out how to configure
different cache strategies for specific parts of your page.

Summary
-------

Symfony was designed to follow the proven rules of the road: HTTP. Caching
is no exception. Mastering the Symfony cache system means becoming familiar
with the HTTP cache models and using them effectively. This means that, instead
of relying only on Symfony documentation and code examples, you have access
to a world of knowledge related to HTTP caching and gateway caches such as
Varnish.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    http_cache/*

.. _`Things Caches Do`: http://2ndscale.com/writings/things-caches-do
.. _`Cache Tutorial`: http://www.mnot.net/cache_docs/
.. _`Varnish`: https://www.varnish-cache.org/
.. _`Squid in reverse proxy mode`: http://wiki.squid-cache.org/SquidFaq/ReverseProxy
.. _`HTTP Bis`: http://tools.ietf.org/wg/httpbis/
.. _`RFC 7234 - Caching`: https://tools.ietf.org/html/rfc7234
.. _`RFC 7232 - Conditional Requests`: https://tools.ietf.org/html/rfc7232
.. _`FOSHttpCacheBundle`: http://foshttpcachebundle.readthedocs.org/
.. _`Caching POST`: https://www.mnot.net/blog/2012/09/24/caching_POST
