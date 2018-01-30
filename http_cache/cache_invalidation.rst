.. index::
   single: Cache; Invalidation

.. _http-cache-invalidation:

Cache Invalidation
~~~~~~~~~~~~~~~~~~

    "There are only two hard things in Computer Science: cache invalidation
    and naming things." -- Phil Karlton

Once an URL is cached by a gateway cache, the cache will not ask the
application for that content anymore. This allows the cache to provide fast
responses and reduces the load on your application. However, you risk
delivering outdated content. A way out of this dilemma is to use long
cache lifetimes, but to actively notify the gateway cache when content
changes. Reverse proxies usually provide a channel to receive such
notifications, typically through special HTTP requests.

.. caution::

    While cache invalidation is powerful, avoid it when possible. If you fail
    to invalidate something, outdated caches will be served for a potentially
    long time. Instead, use short cache lifetimes or use the validation model,
    and adjust your controllers to perform efficient validation checks as
    explained in :ref:`optimizing-cache-validation`.

    Furthermore, since invalidation is a topic specific to each type of reverse
    proxy, using this concept will tie you to a specific reverse proxy or need
    additional efforts to support different proxies.

Sometimes, however, you need that extra performance you can get when
explicitly invalidating. For invalidation, your application needs to detect
when content changes and tell the cache to remove the URLs which contain
that data from its cache.

.. tip::

    If you want to use cache invalidation, have a look at the
    `FOSHttpCacheBundle`_. This bundle provides services to help with various
    cache invalidation concepts and also documents the configuration for a
    couple of common caching proxies.

If one content corresponds to one URL, the ``PURGE`` model works well.
You send a request to the cache proxy with the HTTP method ``PURGE`` (using
the word "PURGE" is a convention, technically this can be any string) instead
of ``GET`` and make the cache proxy detect this and remove the data from the
cache instead of going to the application to get a response.

Here is how you can configure the Symfony reverse proxy (See :doc:`/http_cache`)
to support the ``PURGE`` HTTP method::

    // src/CacheKernel.php

    use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    class CacheKernel extends HttpCache
    {
        protected function invalidate(Request $request, $catch = false)
        {
            if ('PURGE' !== $request->getMethod()) {
                return parent::invalidate($request, $catch);
            }

            if ('127.0.0.1' !== $request->getClientIp()) {
                return new Response(
                    'Invalid HTTP method',
                    Response::HTTP_BAD_REQUEST
                );
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

.. caution::

    You must protect the ``PURGE`` HTTP method somehow to avoid random people
    purging your cached data.

**Purge** instructs the cache to drop a resource in *all its variants*
(according to the ``Vary`` header, see above). An alternative to purging is
**refreshing** a content. Refreshing means that the caching proxy is
instructed to discard its local cache and fetch the content again. This way,
the new content is already available in the cache. The drawback of refreshing
is that variants are not invalidated.

In many applications, the same content bit is used on various pages with
different URLs. More flexible concepts exist for those cases:

* **Banning** invalidates responses matching regular expressions on the
  URL or other criteria;
* **Cache tagging** lets you add a tag for each content used in a response
  so that you can invalidate all URLs containing a certain content.

.. _`FOSHttpCacheBundle`: http://foshttpcachebundle.readthedocs.org/
