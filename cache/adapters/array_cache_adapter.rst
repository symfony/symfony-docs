Array Cache Adapter
===================

Generally, this adapter is useful for testing purposes, as its contents are stored in memory
and not persisted outside the running PHP process in any way. It can also be useful while
warming up caches, due to the :method:`Symfony\\Component\\Cache\\Adapter\\ArrayAdapter::getValues`
method::

    use Symfony\Component\Cache\Adapter\ArrayAdapter;

    $cache = new ArrayAdapter(

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the current PHP process finishes)
        $defaultLifetime = 0,

        // if true, the values saved in the cache are serialized before storing them
        $storeSerialized = true,

        // the maximum lifetime (in seconds) of the entire cache (after this time, the
        // entire cache is deleted to avoid stale data from consuming memory)
        $maxLifetime = 0,

        // the maximum number of items that can be stored in the cache. When the limit
        // is reached, cache follows the LRU model (least recently used items are deleted)
        $maxItems = 0,

        // optional implementation of the Psr\Clock\ClockInterface that will be used
        // to calculate the lifetime of cache items (for example to get predictable
        // lifetimes in tests)
        $clock = null,
    );
