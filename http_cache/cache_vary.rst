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

Set the ``Vary`` header via the ``Response`` object methods or the ``#[Cache]``
attribute::

.. configuration-block::

    .. code-block:: php-attributes

        // this attribute takes an array with the name of the header(s)
        // names for which the response varies
        use Symfony\Component\HttpKernel\Attribute\Cache;
        // ...

        #[Cache(vary: ['Accept-Encoding'])]
        #[Cache(vary: ['Accept-Encoding', 'User-Agent'])]
        public function index(): Response
        {
            // ...
        }

    .. code-block:: php

        // this method takes a header name or an array of header names for
        // which the response varies
        $response->setVary('Accept-Encoding');
        $response->setVary(['Accept-Encoding', 'User-Agent']);
