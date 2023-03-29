Varying the Response for HTTP Cache
===================================

So far, it's been assumed that each URI has exactly one representation of the
target resource. By default, HTTP caching is done by using the URI of the
resource as the cache key. If two people request the same URI of a cacheable
resource, the second person will receive the cached version.

Sometimes this isn't enough and different versions of the same URI need to
be cached based on one or more request header values. For instance, if you
compress pages when the client supports it, any given URI has two representations:
one when the client supports compression, and one when it does not. This
determination is done by the value of the ``Accept-Encoding`` request header.

In this case, you need the cache to store both a compressed and uncompressed
version of the response for the particular URI and return them based on the
request's ``Accept-Encoding`` value. This is done by using the ``Vary`` response
header, which is a comma-separated list of different headers whose values
trigger a different representation of the requested resource:

.. code-block:: text

    Vary: Accept-Encoding, User-Agent

.. tip::

    This particular ``Vary`` header would cache different versions of each
    resource based on the URI and the value of the ``Accept-Encoding`` and
    ``User-Agent`` request header.

The ``Response`` object offers a clean interface for managing the ``Vary``
header::

    // sets one vary header
    $response->setVary('Accept-Encoding');

    // sets multiple vary headers
    $response->setVary(['Accept-Encoding', 'User-Agent']);

The ``setVary()`` method takes a header name or an array of header names for
which the response varies.
