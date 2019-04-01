.. index::
    single: Cache; HTTP expiration

HTTP Cache Expiration
=====================

The `expiration model`_ is the most efficient and straightforward of the two
caching models and should be used whenever possible. When a response is cached
with an expiration, the cache returns it directly without hitting the application
until the cached response expires.

The expiration model can be accomplished using one of two, nearly identical,
HTTP headers: ``Expires`` or ``Cache-Control``.

.. include:: /http_cache/_expiration-and-validation.rst.inc

.. index::
    single: Cache; Cache-Control header
    single: HTTP headers; Cache-Control

Expiration with the ``Cache-Control`` Header
--------------------------------------------

Most of the time, you will use the ``Cache-Control`` header. Recall that the
``Cache-Control`` header is used to specify many different cache directives::

    // sets the number of seconds after which the response
    // should no longer be considered fresh by shared caches
    $response->setSharedMaxAge(600);

The ``Cache-Control`` header would take on the following format (it may have
additional directives):

.. code-block:: text

    Cache-Control: public, s-maxage=600

.. index::
    single: Cache; Expires header
    single: HTTP headers; Expires

Expiration with the ``Expires`` Header
--------------------------------------

An alternative to the ``Cache-Control`` header is ``Expires``. There's no advantage
or disadvantage to either: they're just different ways to set expiration caching
on your response.

According to the HTTP specification, "the ``Expires`` header field gives
the date/time after which the response is considered stale." The ``Expires``
header can be set with the ``setExpires()`` ``Response`` method. It takes a
``DateTime`` instance as an argument::

    $date = new DateTime();
    $date->modify('+600 seconds');

    $response->setExpires($date);

The resulting HTTP header will look like this:

.. code-block:: text

    Expires: Thu, 01 Mar 2011 16:00:00 GMT

.. note::

    The ``setExpires()`` method automatically converts the date to the GMT
    timezone as required by the specification.

Note that in HTTP versions before 1.1 the origin server wasn't required to
send the ``Date`` header. Consequently, the cache (e.g. the browser) might
need to rely on the local clock to evaluate the ``Expires`` header making
the lifetime calculation vulnerable to clock skew. Another limitation
of the ``Expires`` header is that the specification states that "HTTP/1.1
servers should not send ``Expires`` dates more than one year in the future."

.. note::

    According to `RFC 7234 - Caching`_, the ``Expires`` header value is ignored
    when the ``s-maxage`` or ``max-age`` directive of the ``Cache-Control``
    header is defined.

.. _`expiration model`: http://tools.ietf.org/html/rfc2616#section-13.2
.. _`RFC 7234 - Caching`: https://tools.ietf.org/html/rfc7234#section-4.2.1
