.. _doctrine-adapter:

Doctrine Cache Adapter
======================

.. deprecated:: 5.4

    The ``DoctrineAdapter`` and ``DoctrineProvider`` classes were deprecated in Symfony 5.4
    and it will be removed in Symfony 6.0.

This adapter wraps any class extending the `Doctrine Cache`_ abstract provider, allowing
you to use these providers in your application as if they were Symfony Cache adapters.

This adapter expects a ``\Doctrine\Common\Cache\CacheProvider`` instance as its first
parameter, and optionally a namespace and default cache lifetime as its second and
third parameters::

    use Doctrine\Common\Cache\CacheProvider;
    use Doctrine\Common\Cache\SQLite3Cache;
    use Symfony\Component\Cache\Adapter\DoctrineAdapter;

    $provider = new SQLite3Cache(new \SQLite3(__DIR__.'/cache/data.sqlite'), 'youTableName');

    $cache = new DoctrineAdapter(

        // a cache provider instance
        CacheProvider $provider,

        // a string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the database table is truncated or its rows are otherwise deleted)
        $defaultLifetime = 0
    );

.. tip::

    A :class:`Symfony\\Component\\Cache\\DoctrineProvider` class is also provided by the
    component to use any PSR6-compatible implementations with Doctrine-compatible classes.

.. _`Doctrine Cache`: https://github.com/doctrine/cache
