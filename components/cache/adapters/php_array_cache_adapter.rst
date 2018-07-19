.. index::
    single: Cache Pool
    single: PHP Array Cache

Php Array Cache Adapter
=======================

This adapter is a high performance cache for static data (e.g. application configuration)
that is optimized and preloaded into OPcache memory storage::

    use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    // somehow, decide it's time to warm up the cache!
    if ($needsWarmup) {
        // some static values
        $values = array(
            'stats.products_count' => 4711,
            'stats.users_count' => 1356,
        );

        $cache = new PhpArrayAdapter(
            // single file where values are cached
            __DIR__ . '/somefile.cache',
            // a backup adapter, if you set values after warmup
            new FilesystemAdapter()
        );
        $cache->warmUp($values);
    }

    // ... then, use the cache!
    $cacheItem = $cache->getItem('stats.users_count');
    echo $cacheItem->get();

.. note::

    This adapter requires PHP 7.x and should be used with the php.ini setting
    ``opcache.enable`` on.
