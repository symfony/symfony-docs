HTTP Cache Expiration
=====================

The `expiration model`_ is the most efficient and straightforward of the two
caching models and should be used whenever possible. When a response is cached
with an expiration, the cache returns it directly without hitting the application
until the cached response expires.

The expiration model can be accomplished using one of two, nearly identical,
HTTP headers: ``Expires`` or ``Cache-Control``.

.. include:: /http_cache/_expiration-and-validation.rst.inc

Expiration with the ``Cache-Control`` Header
--------------------------------------------

Most of the time, you will use the ``Cache-Control`` header, which
is used to specify many different cache directives::

.. configuration-block::

    .. code-block:: php-attributes

        use Symfony\Component\HttpKernel\Attribute\Cache;
        // ...

        #[Cache(public: true, maxage: 600)]
        public function index(): Response
        {
            // ...
        }

    .. code-block:: php

        // sets the number of seconds after which the response
        // should no longer be considered fresh by shared caches
        $response->setPublic();
        $response->setMaxAge(600);

The ``Cache-Control`` header would take on the following format (it may have
additional directives):

.. code-block:: text

    Cache-Control: public, max-age=600

.. note::

    Using the ``setSharedMaxAge()`` method is not equivalent to using both
    ``setPublic()`` and ``setMaxAge()`` methods. According to the
    `Serving Stale Responses`_ section of RFC 7234, the ``s-maxage`` setting
    (added by ``setSharedMaxAge()`` method) prohibits a cache to use a stale
    response in ``stale-if-error`` scenarios. That's why it's recommended to use
    both ``public`` and ``max-age`` directives.

.. _http-cache-targeted-cache-control:

Targeting a Specific Cache
--------------------------

.. versionadded:: 8.2

    The ``Response::cacheControl()`` method was introduced in Symfony 8.2.

The ``Cache-Control`` header is read by every cache between your application and
the user: the browser, your reverse proxy, a CDN, etc. `RFC 9213`_ allows sending
different directives to a single cache using a header named after that cache.

For example, a cache that identifies itself as ``CDN`` reads the ``CDN-Cache-Control``
header and, when it's present, ignores the ``Cache-Control`` and ``Expires`` headers
entirely. All the other caches keep reading ``Cache-Control`` as usual. Cloudflare,
Fastly and Akamai support the ``CDN-Cache-Control`` header.

This is useful, for example, to keep a response in the CDN for one hour while
telling browsers to always revalidate it. Call the ``cacheControl()`` method
with the name of the target cache::

    // for browsers and any other cache
    $response->setPrivate();
    $response->setMaxAge(0);

    // for the CDN only
    $response->cacheControl('CDN')->setMaxAge(3600);

The response now contains both headers:

.. code-block:: text

    Cache-Control: max-age=0, private
    CDN-Cache-Control: max-age=3600

The object returned by ``cacheControl()`` provides the same methods as the
response itself: ``setPublic()``, ``setPrivate()``, ``setMaxAge()``, ``setNoStore()``,
``setImmutable()``, ``setStaleWhileRevalidate()`` and ``setStaleIfError()``. For
any other directive, use the ``set()``, ``get()``, ``has()``, ``remove()`` and
``all()`` methods. All these methods can be chained::

    $response->cacheControl('CDN')
        ->setMaxAge(3600)
        ->setStaleWhileRevalidate(60)
        ->set('some-proprietary-directive', 'value');

.. note::

    There is no ``setSharedMaxAge()`` method, so always use ``setMaxAge()``.
    The ``s-maxage`` directive exists to tell shared caches apart from browsers,
    which is not needed when the header already targets a single cache.
    Moreover, RFC 9213 requires caches to support ``max-age``, but ``s-maxage``
    is optional, so a cache that doesn't support it would get no freshness
    information at all.

.. caution::

    The ``#[Cache]`` attribute doesn't provide any option to set targeted
    directives, so you must set them on the ``Response`` object. Also,
    Symfony's :ref:`built-in reverse proxy <symfony-gateway-cache>` ignores
    targeted headers and only reads ``Cache-Control``.

Expiration with the ``Expires`` Header
--------------------------------------

An alternative to the ``Cache-Control`` header is ``Expires``. There's no advantage
or disadvantage to either.

According to the HTTP specification, "the ``Expires`` header field gives
the date/time after which the response is considered stale." The ``Expires``
header can be set with the ``expires`` option of the ``#[Cache]`` attribute or
the ``setExpires()`` ``Response`` method::

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

        $date = new DateTime();
        $date->modify('+600 seconds');

        $response->setExpires($date);

The resulting HTTP header will look like this:

.. code-block:: text

    Expires: Thu, 01 Mar 2011 16:00:00 GMT

.. note::

    The ``expires`` option and the ``setExpires()`` method automatically convert
    the date to the GMT timezone as required by the specification.

Note that in HTTP versions before 1.1 the origin server wasn't required to
send the ``Date`` header. Consequently, the cache (e.g. the browser) might
need to rely on the local clock to evaluate the ``Expires`` header making
the lifetime calculation vulnerable to clock skew. Another limitation
of the ``Expires`` header is that the specification states that "HTTP/1.1
servers should not send ``Expires`` dates more than one year in the future."

.. note::

    According to the `Calculating Freshness Lifetime`_ section of RFC 7234,
    the ``Expires`` header value is ignored when the ``s-maxage`` or ``max-age``
    directive of the ``Cache-Control`` header is defined.

Applying Cache Conditionally
----------------------------

.. versionadded:: 8.1

    The ``if`` option of the ``#[Cache]`` attribute was introduced in Symfony 8.1.

Use the ``if`` option to apply the ``#[Cache]`` attribute only when a given
condition is met. This option accepts a closure or an
:doc:`ExpressionLanguage </expression_language>` expression that
receives the ``Request`` object and the controller arguments and must return
a boolean value:

.. configuration-block::

    .. code-block:: php-attributes

        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\HttpKernel\Attribute\Cache;
        // ...

        // Using a closure
        #[Cache(
            public: true,
            maxage: 3600,
            if: static fn (Request $request): bool => $request->query->has('cache')
        )]
        public function index(Request $request): Response
        {
            // ...
        }

        // Using an expression
        #[Cache(
            public: true,
            maxage: 3600,
            if: "request.query.has('cache')"
        )]
        public function show(Request $request): Response
        {
            // ...
        }

When the condition evaluates to ``true``, the cache headers are applied; when
it evaluates to ``false``, they are not.

This is useful when you need to enable caching based on runtime conditions such
as user authentication state, feature flags, or request parameters. It is also
helpful when the controller does not return a ``Response`` object directly (e.g.
when using `FOSRestBundle`_ or other libraries that handle view rendering).

.. note::

    The ``#[Cache]`` attribute is repeatable. When multiple attributes are
    defined on the same controller, they are evaluated in order and the first
    one whose condition returns ``true`` is applied. If no condition matches,
    no cache headers are set by the attribute.

.. _`expiration model`: https://tools.ietf.org/html/rfc2616#section-13.2
.. _`RFC 9213`: https://www.rfc-editor.org/rfc/rfc9213.html
.. _`Calculating Freshness Lifetime`: https://tools.ietf.org/html/rfc7234#section-4.2.1
.. _`Serving Stale Responses`: https://tools.ietf.org/html/rfc7234#section-4.2.4
.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle
