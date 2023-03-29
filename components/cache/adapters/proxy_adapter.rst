Proxy Cache Adapter
===================

This adapter wraps a `PSR-6`_ compliant `cache item pool interface`_. It is used to integrate
your application's cache item pool implementation with the Symfony :ref:`Cache Component <cache-component>`
by consuming any implementation of ``Psr\Cache\CacheItemPoolInterface``.

It can also be used to prefix all keys automatically before storing items in the decorated pool,
effectively allowing the creation of several namespaced pools out of a single one.

This adapter expects a ``Psr\Cache\CacheItemPoolInterface`` instance as its first parameter,
and optionally a namespace and default cache lifetime as its second and third parameters::

    use Psr\Cache\CacheItemPoolInterface;
    use Symfony\Component\Cache\Adapter\ProxyAdapter;

    // create your own cache pool instance that implements
    // the PSR-6 CacheItemPoolInterface
    $psr6CachePool = ...

    $cache = new ProxyAdapter(

        // a cache pool instance
        CacheItemPoolInterface $psr6CachePool,

        // a string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the cache is cleared)
        $defaultLifetime = 0
    );

.. _`PSR-6`: https://www.php-fig.org/psr/psr-6/
.. _`cache item pool interface`: https://www.php-fig.org/psr/psr-6/#cacheitempoolinterface
