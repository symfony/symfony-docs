.. _configuration-framework-http_cache:

http_cache
~~~~~~~~~~

allow_reload
............

**type**: ``boolean`` **default**: ``false``

Specifies whether the client can force a cache reload by including a
Cache-Control "no-cache" directive in the request. Set it to ``true``
for compliance with RFC 2616.

allow_revalidate
................

**type**: ``boolean`` **default**: ``false``

Specifies whether the client can force a cache revalidate by including a
Cache-Control "max-age=0" directive in the request. Set it to ``true``
for compliance with RFC 2616.

debug
.....

**type**: ``boolean`` **default**: ``%kernel.debug%``

If true, exceptions are thrown when things go wrong. Otherwise, the cache will
try to carry on and deliver a meaningful response.

default_ttl
...........

**type**: ``integer`` **default**: ``0``

The number of seconds that a cache entry should be considered fresh when no
explicit freshness information is provided in a response. Explicit
Cache-Control or Expires headers override this value.

enabled
.......

**type**: ``boolean`` **default**: ``false``

private_headers
...............

**type**: ``array`` **default**: ``['Authorization', 'Cookie']``

Set of request headers that trigger "private" cache-control behavior on responses
that don't explicitly state whether the response is public or private via a
Cache-Control directive.

skip_response_headers
.....................

**type**: ``array`` **default**: ``Set-Cookie``

Set of response headers that will never be cached even when the response is cacheable
and public.

stale_if_error
..............

**type**: ``integer`` **default**: ``60``

Specifies the default number of seconds (the granularity is the second) during
which the cache can serve a stale response when an error is encountered.
This setting is overridden by the stale-if-error HTTP
Cache-Control extension (see RFC 5861).

stale_while_revalidate
......................

**type**: ``integer`` **default**: ``2``

Specifies the default number of seconds (the granularity is the second as the
Response TTL precision is a second) during which the cache can immediately return
a stale response while it revalidates it in the background.
This setting is overridden by the stale-while-revalidate HTTP Cache-Control
extension (see RFC 5861).

terminate_on_cache_hit
......................

**type**: ``boolean``

If ``true``, the :ref:`kernel.terminate <component-http-kernel-kernel-terminate>`
event is dispatched even when the cache is hit.

Unless your application needs to process events on cache hits, it's recommended
to set this to ``false`` to improve performance, because it avoids having to
bootstrap the Symfony framework on a cache hit.

.. deprecated:: 7.0

    The underlying ``HttpCache`` option was removed in Symfony 7.0. This
    configuration option has no effect and will be removed in a future version.

trace_header
............

**type**: ``string`` **default**: ``'X-Symfony-Cache'``

Header name to use for traces.

trace_level
...........

**type**: ``string`` **possible values**: ``'none'``, ``'short'`` or ``'full'``

For 'short', a concise trace of the main request will be added as an HTTP header.
'full' will add traces for all requests (including ESI subrequests).
(default: ``'full'`` if in debug; ``'none'`` otherwise)
