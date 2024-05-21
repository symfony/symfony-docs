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

.. _`expiration model`: https://tools.ietf.org/html/rfc2616#section-13.2
.. _`Calculating Freshness Lifetime`: https://tools.ietf.org/html/rfc7234#section-4.2.1
.. _`Serving Stale Responses`: https://tools.ietf.org/html/rfc7234#section-4.2.4
