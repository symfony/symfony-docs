.. index::
    single: Cache Pool
    single: Chain Cache

Chain Cache Adapter
===================

This adapter allows to combine any number of the other available cache adapters.
Cache items are fetched from the first adapter which contains them and cache items are
saved in all the given adapters. This offers a simple way of creating a layered cache.

This adapter expects an array of adapters as its first parameter, and optionally a
maximum cache lifetime as its second parameter::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;

    $cache = new ChainAdapter(array(

        // The ordered list of adapters used to fetch cached items
        array $adapters,

        // The max lifetime of items propagated from lower adapters to upper ones
        $maxLifetime = 0
    ));

.. note::

    When an item is not found in the first adapter but is found in the next ones, this
    adapter ensures that the fetched item is saved in all the adapters where it was
    previously missing.

The following example shows how to create a chain adapter instance using the fastest and
slowest storage engines, :class:`Symfony\\Component\\Cache\\Adapter\\ApcuAdapter` and
:class:`Symfony\\Component\\Cache\\Adapter\\FilesystemAdapter`, respectfully::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;
    use Symfony\Component\Cache\Adapter\ChainAdapter;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new ChainAdapter(array(
        new ApcuAdapter(),
        new FilesystemAdapter(),
    ));
