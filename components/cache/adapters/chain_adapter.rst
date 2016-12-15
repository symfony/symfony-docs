.. index::
    single: Cache Pool
    single: Chain Cache

Chain Cache Adapter
===================

This adapter allows to combine any number of the previous adapters. Cache items
are fetched from the first adapter which contains them. Besides, cache items are
saved in all the given adapters, so this is a simple way of creating a cache
replication::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;
    use Symfony\Component\Cache\Adapter\ChainAdapter;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $apcCache = new ApcuAdapter();
    $fileCache = new FilesystemAdapter();

    $cache = new ChainAdapter(array($apcCache, $fileCache));

When an item is not found in the first adapters but is found in the next ones,
the ``ChainAdapter`` ensures that the fetched item is saved in all the adapters
where it was missing. Since it's not possible to know the expiry date and time
of a cache item, the second optional argument of ``ChainAdapter`` is the default
lifetime applied to those cache items (by default it's ``0``).
