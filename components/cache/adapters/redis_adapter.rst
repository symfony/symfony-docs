.. index::
    single: Cache Pool
    single: Redis Cache

Redis Cache Adapter
===================

This adapter stores the contents in the memory of a Redis server. Unlike the APCu
adapter, it's not limited to the shared memory of the current server, so you can
store contents in a cluster of servers if needed.

It requires to have installed Redis and have created a connection that implements
the ``\Redis``, ``\RedisArray``, ``\RedisCluster`` or ``\Predis`` classes::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    $cache = new RedisAdapter(
        // the object that stores a valid connection to your Redis system
        \Redis $redisConnection,
        // the string prefixed to the keys of the items stored in this cache
        $namespace = '',
        // in seconds; applied to cache items that don't define their own lifetime
        // 0 means to store the cache items indefinitely (i.e. until the Redis memory is deleted)
        $defaultLifetime = 0
    );

The :method:`Symfony\\Component\\Cache\\Adapter\\RedisAdapter::createConnection`
helper method allows creating a connection to a Redis server using a `Data Source Name (DSN)`_::

    $redisConnection = RedisAdapter::createConnection('redis://localhost');

See the method's docblock for more options.

.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
