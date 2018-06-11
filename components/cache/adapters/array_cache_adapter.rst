.. index::
    single: Cache Pool
    single: Array Cache

Array Cache Adapter
===================

Generally, this adapter is useful for testing purposes, as its contents are stored in memory
and not persisted outside the running PHP process in any way. It can also be useful while
warming up caches, due to the :method:`Symfony\\Component\\Cache\\Adapter\\ArrayAdapter::getValues`
method.

This adapter can be passed a default cache lifetime as its first parameter, and a boolean that
toggles serialization as its second parameter::

    use Symfony\Component\Cache\Adapter\ArrayAdapter;

    $cache = new ArrayAdapter(

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the current PHP process finishes)
        $defaultLifetime = 0,

        // if ``true``, the values saved in the cache are serialized before storing them
        $storeSerialized = true
    );
