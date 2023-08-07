Chain Cache Adapter
===================

This adapter allows combining any number of the other
:ref:`available cache adapters <component-cache-creating-cache-pools>`. Cache items are
fetched from the first adapter containing them and cache items are saved to all the
given adapters. This exposes a simple and efficient method for creating a layered cache.

The ChainAdapter must be provided an array of adapters and optionally a default cache
lifetime as its constructor arguments::

    use Symfony\Component\Cache\Adapter\ChainAdapter;

    $cache = new ChainAdapter(
        // The ordered list of adapters used to fetch cached items
        array $adapters,

        // The default lifetime of items propagated from lower adapters to upper ones
        $defaultLifetime = 0
    );

.. note::

    When an item is not found in the first adapter but is found in the next ones, this
    adapter ensures that the fetched item is saved to all the adapters where it was
    previously missing.

The following example shows how to create a chain adapter instance using the fastest and
slowest storage engines, :class:`Symfony\\Component\\Cache\\Adapter\\ApcuAdapter` and
:class:`Symfony\\Component\\Cache\\Adapter\\FilesystemAdapter`, respectfully::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;
    use Symfony\Component\Cache\Adapter\ChainAdapter;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new ChainAdapter([
        new ApcuAdapter(),
        new FilesystemAdapter(),
    ]);

When calling this adapter's :method:`Symfony\\Component\\Cache\\Adapter\\ChainAdapter::prune` method,
the call is delegated to all its compatible cache adapters. It is safe to mix both adapters
that *do* and do *not* implement :class:`Symfony\\Component\\Cache\\PruneableInterface`, as
incompatible adapters are silently ignored::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;
    use Symfony\Component\Cache\Adapter\ChainAdapter;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new ChainAdapter([
        new ApcuAdapter(),        // does NOT implement PruneableInterface
        new FilesystemAdapter(),  // DOES implement PruneableInterface
    ]);

    // prune will proxy the call to FilesystemAdapter while silently skip ApcuAdapter
    $cache->prune();
