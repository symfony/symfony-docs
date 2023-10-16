HTTP Cache
==========

The nature of rich web applications means that they're dynamic. No matter
how efficient your application, each request will always contain more overhead
than serving a static file. Usually, that's fine. But when you need your requests
to be lightning fast, you need HTTP caching.

Caching on the Shoulders of Giants
----------------------------------

With HTTP Caching, you cache the full output of a page (i.e. the response) and bypass
your application *entirely* on subsequent requests. Caching entire responses
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

.. _`symfony-gateway-cache`:
.. _symfony2-reverse-proxy:

Symfony Reverse Proxy
~~~~~~~~~~~~~~~~~~~~~

Symfony comes with a reverse proxy (i.e. gateway cache) written in PHP.
:ref:`It's not a fully-featured reverse proxy cache like Varnish <http-cache-symfony-versus-varnish>`,
but it is a great way to start.

.. tip::

    For details on setting up Varnish, see :doc:`/http_cache/varnish`.

Use the ``framework.http_cache`` option to enable the proxy for the
:ref:`prod environment <configuration-environments>`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        when@prod:
            framework:
                http_cache: true

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <when env="prod">
              <framework:config>
                  <!-- ... -->
                  <framework:http-cache enabled="true"/>
              </framework:config>
            </when>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework, string $env): void {
            if ('prod' === $env) {
                $framework->httpCache()->enabled(true);
            }
        };

The kernel will immediately act as a reverse proxy: caching responses
from your application and returning them to the client.

The proxy has a sensible default configuration, but it can be
finely tuned via :ref:`a set of options <configuration-framework-http_cache>`.

When in :ref:`debug mode <debug-mode>`, Symfony automatically adds an
``X-Symfony-Cache`` header to the response. You can also use the ``trace_level``
config option and set it to either ``none``, ``short`` or ``full`` to add this
information.

``short`` will add the information for the main request only.
It's written in a concise way that makes it easy to record the
information in your server log files. For example, in Apache you can
use ``%{X-Symfony-Cache}o`` in ``LogFormat`` format statements.
This information can be used to extract general information about
cache efficiency of your routes.

.. tip::

    You can change the name of the header used for the trace
    information using the ``trace_header`` config option.

.. _http-cache-symfony-versus-varnish:

.. sidebar:: Changing from one Reverse Proxy to another

    The Symfony reverse proxy is a great tool to use when developing your
    website or when you deploy your website to a shared host where you cannot
    install anything beyond PHP code. But being written in PHP, it cannot
    be as fast as a proxy written in C.

    Fortunately, since all reverse proxies are effectively the same, you should
    be able to switch to something more robust - like Varnish - without any problems.
    See :doc:`How to use Varnish </http_cache/varnish>`

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

.. _http-cache-expiration-intro:

Expiration Caching
~~~~~~~~~~~~~~~~~~

The *easiest* way to cache a response is by caching it for a specific amount of time::

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        use Symfony\Component\HttpKernel\Attribute\Cache;
        // ...

        #[Cache(public: true, maxage: 3600, mustRevalidate: true)]
        public function index(): Response
        {
            return $this->render('blog/index.html.twig', []);
        }

    .. code-block:: php

        // src/Controller/BlogController.php
        use Symfony\Component\HttpFoundation\Response;

        public function index(): Response
        {
            // somehow create a Response object, like by rendering a template
            $response = $this->render('blog/index.html.twig', []);

            // cache publicly for 3600 seconds
            $response->setPublic();
            $response->setMaxAge(3600);

            // (optional) set a custom Cache-Control directive
            $response->headers->addCacheControlDirective('must-revalidate', true);

            return $response;
        }

Thanks to this new code, your HTTP response will have the following header:

.. code-block:: text

    Cache-Control: public, maxage=3600, must-revalidate

This tells your HTTP reverse proxy to cache this response for 3600 seconds. If *anyone*
requests this URL again before 3600 seconds, your application *won't* be hit at all.
If you're using the Symfony reverse proxy, look at the ``X-Symfony-Cache`` header
for debugging information about cache hits and misses.

.. tip::

    The URI of the request is used as the cache key (unless you :doc:`vary </http_cache/cache_vary>`).

This provides great performance and is simple to use. But, cache *invalidation*
is not supported. If your content change, you'll need to wait until your cache
expires for the page to update.

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

With expiration caching, you say "cache for 3600 seconds!". But, when someone
updates cached content, you won't see that content on your site until the cache
expires.

If you need to see updated content *immediately*, you either need to
:ref:`invalidate <http-cache-invalidation>` your cache *or* use the validation
caching model.

For details, see :doc:`/http_cache/validation`.

Safe Methods: Only caching GET or HEAD requests
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

HTTP caching only works for "safe" HTTP methods (like GET and HEAD). This means
three things:

* Don't try to cache PUT or DELETE requests. It won't work and with good reason.
  These methods are meant to be used when mutating the state of your application
  (e.g. deleting a blog post). Caching them would prevent certain requests from hitting
  and mutating your application.

* POST requests are generally considered uncacheable, but `they can be cached`_
  when they include explicit freshness information. However, POST caching is not
  widely implemented, so you should avoid it if possible.

* You should *never* change the state of your application (e.g. update a blog post)
  when responding to a GET or HEAD request. If those requests are cached, future
  requests may not actually hit your server.

More Response Methods
~~~~~~~~~~~~~~~~~~~~~

The Response class provides many more methods related to the cache. Here are
the most useful ones::

    // marks the Response stale
    $response->expire();

    // forces the response to return a proper 304 response with no content
    $response->setNotModified();

Additionally, most cache-related HTTP headers can be set via the single
:method:`Symfony\\Component\\HttpFoundation\\Response::setCache` method::

    // use this method to set several cache settings in one call
    // (this example lists all the available cache settings)
    $response->setCache([
        'must_revalidate'  => false,
        'no_cache'         => false,
        'no_store'         => false,
        'no_transform'     => false,
        'public'           => true,
        'private'          => false,
        'proxy_revalidate' => false,
        'max_age'          => 600,
        's_maxage'         => 600,
        'immutable'        => true,
        'last_modified'    => new \DateTime(),
        'etag'             => 'abcdef'
    ]);

.. tip::

    All these options are also available when using the ``#[Cache()]`` attribute.

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

HTTP Caching and User Sessions
------------------------------

Whenever the session is started during a request, Symfony turns the response
into a private non-cacheable response. This is the best default behavior to not
cache private user information (e.g. a shopping cart, a user profile details,
etc.) and expose it to other visitors.

However, even requests making use of the session can be cached under some
circumstances. For example, information related to some user group could be
cached for all the users belonging to that group. Handling these advanced
caching scenarios is out of the scope of Symfony, but they can be solved with
the `FOSHttpCacheBundle`_.

In order to disable the default Symfony behavior that makes requests using the
session uncacheable, add the following internal header to your response and
Symfony won't modify it::

    use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

    $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');

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

.. _`Things Caches Do`: https://tomayko.com/blog/2008/things-caches-do
.. _`Cache Tutorial`: https://www.mnot.net/cache_docs/
.. _`Varnish`: https://varnish-cache.org/
.. _`Squid in reverse proxy mode`: https://wiki.squid-cache.org/SquidFaq/ReverseProxy
.. _`RFC 7234 - Caching`: https://tools.ietf.org/html/rfc7234
.. _`RFC 7232 - Conditional Requests`: https://tools.ietf.org/html/rfc7232
.. _`FOSHttpCacheBundle`: https://foshttpcachebundle.readthedocs.org/
.. _`they can be cached`: https://tools.ietf.org/html/draft-ietf-httpbis-p2-semantics-20#section-2.3.4
