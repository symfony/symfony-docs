HTTP Cache
==========

The nature of rich web applications means that they're dynamic. No matter
how efficient your application, each request will always contain more overhead
than serving a static file. Usually, that's fine. But when you need your requests
to be lightning fast, you need HTTP caching.

.. seealso::

    This article explains how to cache entire HTTP responses. If you want to
    cache arbitrary data in your application code (e.g. the result of database
    queries or heavy computations), read the :doc:`Cache </cache>` article.

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
validation and expiration caching models, you'll be ready to understand the Symfony
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
It's written in a concise way, so you can record the
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
    be able to switch to something more reliable - like Varnish - without any problems.
    See :doc:`How to use Varnish </http_cache/varnish>`.

.. _http-cache-introduction:

Making your Responses HTTP Cacheable
------------------------------------

Once you've added a reverse proxy cache (e.g. the Symfony reverse proxy or Varnish),
you're ready to cache your responses. To do that, you need to *communicate* to your
cache *which* responses are cacheable and for how long. This is done by setting HTTP
cache headers on the response.

HTTP defines two caching models and each of them uses two response headers:

.. _http-expiration-validation:
.. _http-expiration-and-validation:

#. :ref:`Expiration caching <http-cache-expiration-intro>` caches the entire
   response for a specific amount of time (e.g. 24 hours). It's simple, but
   cache invalidation is more difficult. It uses these headers:

   * :ref:`Cache-Control <http-cache-cache-control>`
   * :ref:`Expires <http-cache-expires>`

#. :ref:`Validation caching <http-cache-validation-intro>` is more complex, but
   it allows you to dynamically invalidate the response as soon as your content
   changes. It uses these headers:

   * :ref:`ETag <http-cache-etag>`
   * :ref:`Last-Modified <http-cache-last-modified>`

You can use both models in the same response: expiration wins over validation,
so the cache serves the cached content until it expires and then checks with
your application that the content is still valid.

.. sidebar:: Reading the HTTP Specification

    All of the HTTP headers you'll read about are *not* invented by Symfony! They're
    part of an HTTP specification that's used by sites all over the web. To dig deeper
    into HTTP Caching, check out the documents `RFC 7234 - Caching`_ and
    `RFC 7232 - Conditional Requests`_.

    As a web developer, you are strongly urged to read the specification. Its
    clarity and power - even more than fifteen years after its creation - are
    invaluable. Don't be put off by the appearance of the spec - its contents
    are much more beautiful than its cover!

.. _http-cache-expiration-intro:

Expiration Caching
~~~~~~~~~~~~~~~~~~

The `expiration model`_ is the more efficient of the two caching models and you
should use it whenever possible. The cache returns the cached response without
hitting your application until that response expires. You can define the
expiration with one of two HTTP headers: ``Cache-Control`` or ``Expires``.

.. _http-cache-cache-control:

Expiration with the ``Cache-Control`` Header
............................................

Most of the time, you will use the ``Cache-Control`` header, which can specify
many different cache directives. The *easiest* way to cache a response is by
caching it for a specific amount of time::

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

    Cache-Control: public, max-age=3600, must-revalidate

.. tip::

    When both are used, the cache headers defined in the controller take
    precedence over those configured with the ``#[Cache]`` attribute.

This tells your HTTP reverse proxy to cache this response for 3600 seconds. If *anyone*
requests this URL again before 3600 seconds, your application *won't* be hit at all.
If you're using the Symfony reverse proxy, look at the ``X-Symfony-Cache`` header
for debugging information about cache hits and misses.

.. tip::

    The URI of the request is used as the cache key (unless you
    :ref:`vary the response <http-cache-vary>`).

This provides great performance and is simple to use. But, cache *invalidation*
is not supported. If your content changes, you'll need to wait until your cache
expires for the page to update.

.. tip::

    Actually, you *can* manually invalidate your cache, but it's not part of the
    HTTP Caching spec. See :ref:`http-cache-invalidation`.

If you need to set cache headers for many different controller actions, check out
`FOSHttpCacheBundle`_. It provides a way to define cache headers based on the URL
pattern and other request properties.

.. note::

    Using the ``setSharedMaxAge()`` method is not equivalent to using both
    ``setPublic()`` and ``setMaxAge()`` methods. According to the
    `Serving Stale Responses`_ section of RFC 7234, the ``s-maxage`` setting
    (added by ``setSharedMaxAge()`` method) prohibits a cache from using a stale
    response in ``stale-if-error`` scenarios. That's why it's recommended to use
    both ``public`` and ``max-age`` directives.

.. _http-cache-expires:

Expiration with the ``Expires`` Header
......................................

An alternative to the ``Cache-Control`` header is ``Expires``. According to the
HTTP specification, "the ``Expires`` header field gives the date/time after
which the response is considered stale." Set it with the ``expires`` option of
the ``#[Cache]`` attribute or with the ``setExpires()`` method of the
``Response``::

.. configuration-block::

    .. code-block:: php-attributes

        use Symfony\Component\HttpKernel\Attribute\Cache;
        // ...

        #[Cache(expires: '+600 seconds')]
        public function index(): Response
        {
            // ...
        }

    .. code-block:: php

        $response->setExpires(new \DateTime('+600 seconds'));

The resulting HTTP header will look like this:

.. code-block:: text

    Expires: Thu, 01 Mar 2011 16:00:00 GMT

.. note::

    The ``expires`` option and the ``setExpires()`` method automatically convert
    the date to the GMT timezone as required by the specification.

Note that in HTTP versions before 1.1 the origin server wasn't required to
send the ``Date`` header. Consequently, the cache (e.g. the browser) might
need to rely on the local clock to evaluate the ``Expires`` header, making
the lifetime calculation vulnerable to clock skew. Another limitation
of the ``Expires`` header is that the specification states that "HTTP/1.1
servers should not send ``Expires`` dates more than one year in the future."
That's why ``Cache-Control`` is the preferred header.

.. note::

    According to the `Calculating Freshness Lifetime`_ section of RFC 7234,
    the ``Expires`` header value is ignored when the ``s-maxage`` or ``max-age``
    directive of the ``Cache-Control`` header is defined.

.. _http-cache-validation-intro:

Validation Caching
~~~~~~~~~~~~~~~~~~

With expiration caching, you say "cache for 3600 seconds!". But, when someone
updates cached content, you won't see that content on your site until the cache
expires.

If you need to see updated content *immediately*, you either need to
:ref:`invalidate <http-cache-invalidation>` your cache *or* use the validation
caching model.

In the `validation model`_, the cache still stores responses, but for each
request it asks the application if the cached response is still valid or if it
needs to be regenerated. If the cached response *is* still valid, your
application should return a 304 status code and no content. This tells the
cache that it's OK to return the cached response.

Under this model, you only save CPU if you're able to determine that the
cached response is still valid by doing *less* work than generating the whole
page again (see below for an implementation example).

.. tip::

    The 304 status code means "Not Modified". It's important because with
    this status code the response does *not* contain the actual content being
    requested. Instead, the response only consists of the response headers that
    tell the cache that it can use its stored version of the content.

Like with expiration, there are two different HTTP headers that can be used
to implement the validation model: ``ETag`` and ``Last-Modified``.

.. _http-cache-etag:

Validation with the ``ETag`` Header
...................................

The `HTTP ETag`_ ("entity-tag") header is an optional HTTP header whose value is
an arbitrary string that uniquely identifies one representation of the target
resource. It's entirely generated and set by your application so that you can
tell, for example, if the ``/about`` resource that's stored by the cache is
up-to-date with what your application would return.

An ``ETag`` is like a fingerprint and is used to quickly compare if two
different versions of a resource are equivalent. Like fingerprints, each
``ETag`` must be unique across all representations of the same resource.

In the following example, the ``ETag`` is the ``md5`` hash of the content::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class DefaultController extends AbstractController
    {
        public function homepage(Request $request): Response
        {
            $response = $this->render('static/homepage.html.twig');
            $response->setEtag(md5($response->getContent()));
            $response->setPublic(); // make sure the response is public/cacheable
            $response->isNotModified($request);

            return $response;
        }
    }

The :method:`Symfony\\Component\\HttpFoundation\\Response::isNotModified`
method compares the ``If-None-Match`` header with the ``ETag`` response header.
If the two match, the method automatically sets the ``Response`` status code
to 304.

.. note::

    When using ``mod_deflate`` or ``mod_brotli`` in Apache 2.4, the original
    ``ETag`` value is modified (e.g. if ``ETag`` was ``foo``, Apache turns it
    into ``foo-gzip`` or ``foo-br``), which breaks the ``ETag``-based validation.

    You can control this behavior with the `DeflateAlterETag`_ and `BrotliAlterETag`_
    directives. Alternatively, you can use the following Apache configuration to
    keep both the original ``ETag`` and the modified one when compressing responses:

    .. code-block:: apache

        RequestHeader edit "If-None-Match" '^"((.*)-(gzip|br))"$' '"$1", "$2"'

.. note::

    The cache sets the ``If-None-Match`` header on the request to the ``ETag``
    of the original cached response before sending the request back to the
    app. This is how the cache and server communicate with each other and
    decide whether or not the resource has been updated since it was cached.

This algorithm works and is very generic, but you need to create the whole
``Response`` before being able to compute the ``ETag``, which is sub-optimal.
In other words, it saves on bandwidth, but not CPU cycles.

In the :ref:`optimizing-cache-validation` section, you'll see how validation
can be used more intelligently to determine the validity of a cache without
doing so much work.

.. tip::

    Symfony also supports weak ``ETag`` values by passing ``true`` as the second
    argument to the
    :method:`Symfony\\Component\\HttpFoundation\\Response::setEtag` method.

.. _http-cache-last-modified:

Validation with the ``Last-Modified`` Header
............................................

The ``Last-Modified`` header is the second form of validation. According
to the HTTP specification, "The ``Last-Modified`` header field indicates
the date and time at which the origin server believes the representation
was last modified." In other words, the application compares this date with
the date of the cached response to decide if the content has changed since the
response was cached.

For instance, you can use the latest update date for all the objects needed to
compute the resource representation as the value for the ``Last-Modified``
header::

    // src/Controller/ArticleController.php
    namespace App\Controller;

    // ...
    use App\Entity\Article;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class ArticleController extends AbstractController
    {
        public function show(Article $article, Request $request): Response
        {
            // use the most recent update date of the article and its author
            $lastModifiedAt = max(
                $article->getUpdatedAt(),
                $article->getAuthor()->getUpdatedAt()
            );

            $response = new Response();
            $response->setLastModified($lastModifiedAt);
            // Set response as public. Otherwise it will be private by default.
            $response->setPublic();

            if ($response->isNotModified($request)) {
                return $response;
            }

            // ... do more work to populate the response with the full content

            return $response;
        }
    }

The :method:`Symfony\\Component\\HttpFoundation\\Response::isNotModified`
method compares the ``If-Modified-Since`` header with the ``Last-Modified``
response header. If they are equivalent, the ``Response`` will be set to a
304 status code.

.. note::

    The cache sets the ``If-Modified-Since`` header on the request to the ``Last-Modified``
    of the original cached response before sending the request back to the
    app. This is how the cache and server communicate with each other and
    decide whether or not the resource has been updated since it was cached.

.. _optimizing-cache-validation:

Optimizing your Code with Validation
....................................

The main goal of any caching strategy is to lighten the load on the application.
Put another way, the less you do in your application to return a 304 response,
the better. The ``Response::isNotModified()`` method does exactly that::

    // src/Controller/ArticleController.php
    namespace App\Controller;

    // ...
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class ArticleController extends AbstractController
    {
        public function show(string $articleSlug, Request $request): Response
        {
            // Get the minimum information to compute
            // the ETag or the Last-Modified value
            // (based on the Request, data is retrieved from
            // a database or a key-value store for instance)
            $article = ...;

            // create a Response with an ETag and/or a Last-Modified header
            $response = new Response();
            $response->setEtag($article->computeETag());
            $response->setLastModified($article->getPublishedAt());

            // Set response as public. Otherwise it will be private by default.
            $response->setPublic();

            // Check that the Response is not modified for the given Request
            if ($response->isNotModified($request)) {
                // return the 304 Response immediately
                return $response;
            }

            // do more work here - like retrieving more data
            $comments = ...;

            // or render a template with the $response you've already started
            return $this->render('article/show.html.twig', [
                'article' => $article,
                'comments' => $comments,
            ], $response);
        }
    }

When the ``Response`` is not modified, the ``isNotModified()`` method
automatically sets the response status code to ``304``, removes the content,
and removes some headers that must not be present for ``304`` responses (see
:method:`Symfony\\Component\\HttpFoundation\\Response::setNotModified`).

.. _http-cache-vary:

Varying the Response for HTTP Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the cache uses the URI of the request as the cache key, so all
requests to the same URI receive the same cached response. Sometimes, the same
URI must have different representations depending on some request headers.
For instance, if you compress pages when the client supports it, any given URI
has two representations: one for clients that support compression and another
one for clients that don't. The ``Accept-Encoding`` request header tells which
representation the client needs.

In this case, the cache must store both a compressed and an uncompressed
version of the response for that URI and return one of them based on the
``Accept-Encoding`` value of the request. Use the ``Vary`` response header to
do that. Its value is a comma-separated list of the request headers whose
values change the representation of the requested resource:

.. code-block:: text

    Vary: Accept-Encoding, User-Agent

With this header, the cache stores a different version of each resource
depending on the URI and the values of the ``Accept-Encoding`` and
``User-Agent`` request headers.

Set the ``Vary`` header with the ``vary`` option of the ``#[Cache]`` attribute
or with the ``setVary()`` method of the ``Response``::

.. configuration-block::

    .. code-block:: php-attributes

        use Symfony\Component\HttpKernel\Attribute\Cache;
        // ...

        // the option takes an array with the names of the request headers
        // for which the response varies
        #[Cache(vary: ['Accept-Encoding'])]
        public function index(): Response
        {
            // ...
        }

        #[Cache(vary: ['Accept-Encoding', 'User-Agent'])]
        public function show(): Response
        {
            // ...
        }

    .. code-block:: php

        // this method takes a header name or an array of header names for
        // which the response varies
        $response->setVary('Accept-Encoding');
        $response->setVary(['Accept-Encoding', 'User-Agent']);

.. _safe-methods-only-caching-get-or-head-requests:

Safe Methods: Only caching GET, HEAD or QUERY requests
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

HTTP caching only works for "safe" HTTP methods (GET, HEAD and QUERY). This means
three things:

* Don't try to cache PUT or DELETE requests. It won't work and with good reason.
  These methods are meant to be used when mutating the state of your application
  (e.g. deleting a blog post). Caching them would prevent certain requests from hitting
  and mutating your application.

* POST requests are generally considered uncacheable, but `they can be cached`_
  when they include explicit freshness information. However, POST caching is not
  widely implemented, so you should avoid it if possible.

* You should *never* change the state of your application (e.g. update a blog post)
  when responding to a GET, HEAD or QUERY request. If those requests are cached, future
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

    All these options are also available when using the ``#[Cache]`` attribute.

.. _http-cache-invalidation:

Cache Invalidation
------------------

    "There are only two hard things in Computer Science: cache invalidation
    and naming things." -- Phil Karlton

Cache invalidation is *not* part of the HTTP specification. Still, it can be really
useful to delete various HTTP cache entries as soon as some content on your site
is updated.

Once a URL is cached by a gateway cache, the cache will not ask the
application for that content anymore. This allows the cache to provide fast
responses and reduces the load on your application. However, you risk
delivering outdated content. A way out of this dilemma is to use long
cache lifetimes, but to actively notify the gateway cache when content
changes. Reverse proxies usually provide a channel to receive such
notifications, typically through special HTTP requests.

.. warning::

    While cache invalidation is powerful, avoid it when possible. If you fail
    to invalidate something, outdated caches will be served for a potentially
    long time. Instead, use short cache lifetimes or use the validation model,
    and adjust your controllers to perform efficient validation checks as
    explained in :ref:`optimizing-cache-validation`.

    Also, since invalidation is a topic specific to each type of reverse
    proxy, using this concept will tie you to a specific reverse proxy or
    require additional effort to support different proxies.

Sometimes, however, you need that extra performance you can get when
explicitly invalidating. For invalidation, your application needs to detect
when content changes and tell the cache to remove the URLs which contain
that data from its cache.

.. tip::

    If you want to use cache invalidation, have a look at the
    `FOSHttpCacheBundle`_. This bundle provides services to help with various
    cache invalidation concepts and also documents the configuration for a
    couple of common caching proxies.

If each piece of content corresponds to one URL, the ``PURGE`` model works well.
You send a request to the cache proxy with the HTTP method ``PURGE`` (using
the word "PURGE" is a convention; technically this can be any string) instead
of ``GET`` and make the cache proxy detect this and remove the data from the
cache instead of going to the application to get a response.

Here is how you can configure the :ref:`Symfony reverse proxy <symfony-gateway-cache>`
to support the ``PURGE`` HTTP method. First create a caching kernel that overrides the
:method:`Symfony\\Component\\HttpKernel\\HttpCache\\HttpCache::invalidate` method::

    // src/CacheKernel.php
    namespace App;

    use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    class CacheKernel extends HttpCache
    {
        protected function invalidate(Request $request, bool $catch = false): Response
        {
            if ('PURGE' !== $request->getMethod()) {
                return parent::invalidate($request, $catch);
            }

            if ('127.0.0.1' !== $request->getClientIp()) {
                return new Response('Forbidden', Response::HTTP_FORBIDDEN);
            }

            $response = new Response();
            if ($this->getStore()->purge($request->getUri())) {
                $response->setStatusCode(Response::HTTP_OK, 'Purged');
            } else {
                $response->setStatusCode(Response::HTTP_NOT_FOUND, 'Not found');
            }

            return $response;
        }
    }

Then, register the class as a service that :doc:`decorates </service_container/decoration>`
``http_cache``::

.. configuration-block::

    .. code-block:: php-attributes

        // src/CacheKernel.php
        namespace App;

        // ...
        use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
        use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

        #[Autoconfigure(bind: ['$surrogate' => '@?esi'])]
        #[AsDecorator(decorates: 'http_cache')]
        class CacheKernel extends HttpCache
        {
            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\CacheKernel:
                decorates: http_cache
                arguments:
                    - '@kernel'
                    - '@http_cache.store'
                    - '@?esi'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <services>
                <service id="App\CacheKernel" decorates="http_cache">
                    <argument type="service" id="kernel"/>
                    <argument type="service" id="http_cache.store"/>
                    <argument type="service" id="esi" on-invalid="null"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\CacheKernel;

        return function (ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(CacheKernel::class)
                ->decorate('http_cache')
                ->args([
                    service('kernel'),
                    service('http_cache.store'),
                    service('esi')->nullOnInvalid(),
                ])
            ;
        };

.. danger::

    You must protect the ``PURGE`` HTTP method somehow to avoid random people
    purging your cached data.

**Purge** instructs the cache to drop a resource in *all its variants*
(according to the ``Vary`` header, see
:ref:`Varying the Response for HTTP Cache <http-cache-vary>`). An alternative
to purging is **refreshing** the content. Refreshing means that the caching
proxy discards its local cache and fetches the content again. This way,
the new content is already available in the cache. The drawback of refreshing
is that variants are not invalidated.

In many applications, the same piece of content is used on various pages with
different URLs. More flexible concepts exist for those cases:

* **Banning** invalidates responses matching regular expressions on the
  URL or other criteria;
* **Cache tagging** lets you add a tag for each piece of content used in a
  response so that you can invalidate all URLs containing that content.

Using Edge Side Includes
------------------------

When pages contain dynamic parts, you may not be able to cache entire pages,
but only parts of them. Read :doc:`/http_cache/esi` to find out how to configure
different cache strategies for specific parts of your page.

HTTP Caching and User Sessions
------------------------------

Whenever the session is started during a request, Symfony turns the response
into a private non-cacheable response. This is the best default behavior to not
cache private user information (e.g. a shopping cart, a user's profile details,
etc.) and expose it to other visitors. To make sure that a page you want to
cache doesn't use the session by mistake, mark its route as
:ref:`stateless <stateless-routing>`.

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
.. _`expiration model`: https://tools.ietf.org/html/rfc2616#section-13.2
.. _`Calculating Freshness Lifetime`: https://tools.ietf.org/html/rfc7234#section-4.2.1
.. _`Serving Stale Responses`: https://tools.ietf.org/html/rfc7234#section-4.2.4
.. _`validation model`: https://tools.ietf.org/html/rfc2616#section-13.3
.. _`HTTP ETag`: https://en.wikipedia.org/wiki/HTTP_ETag
.. _`DeflateAlterETag`: https://httpd.apache.org/docs/trunk/mod/mod_deflate.html#deflatealteretag
.. _`BrotliAlterETag`: https://httpd.apache.org/docs/2.4/mod/mod_brotli.html#brotlialteretag
