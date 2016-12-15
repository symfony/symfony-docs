.. index::
    single: Cache Pool
    single: Array Cache

Array Cache Adapter
===================

This adapter is only useful for testing purposes because contents are stored in
memory and not persisted in any way. Besides, some features explained later are
not available, such as the deferred saves::

    use Symfony\Component\Cache\Adapter\ArrayAdapter;

    $cache = new ArrayAdapter(
        // in seconds; applied to cache items that don't define their own lifetime
        // 0 means to store the cache items indefinitely (i.e. until the current PHP process finishes)
        $defaultLifetime = 0,
        // if ``true``, the values saved in the cache are serialized before storing them
        $storeSerialized = true
    );
