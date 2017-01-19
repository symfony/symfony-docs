.. index::
    single: Cache Pool
    single: Doctrine Cache

.. _`doctrine-adapter`:

Doctrine Cache Adapter
======================

This adapter wraps any class extending the `Doctrine Cache`_ abstract provider, allowing
you to use these providers in your application as if they were Symfony Cache adapters.

This adapter expects a ``\Doctrine\Common\Cache\CacheProvider`` instance as its first
parameter, and optionally a namespace and default cache lifetime as its second and
third parameters::

    use Doctrine\Common\Cache\CacheProvider;
    use Doctrine\Common\Cache\SQLite3Cache;
    use Symfony\Component\Cache\Adapter\DoctrineAdapter;

    $provider = new SQLite3Cache(new \SQLite3(__DIR__.'/cache/data.sqlite'), 'youTableName');

    $symfonyCache = new DoctrineAdapter(

        // a cache provider instance
        CacheProvider $provider,

        // a string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the database table is truncated or its rows are otherwise deleted)
        $defaultLifetime = 0
    );

.. _`Doctrine Cache`: https://github.com/doctrine/cache
