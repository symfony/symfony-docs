.. _redis-adapter:

Redis Cache Adapter
===================

.. seealso::

    This article explains how to configure the Redis adapter when using the
    Cache as an independent component in any PHP application. Read the
    :ref:`Symfony Cache configuration <cache-configuration-with-frameworkbundle>`
    article if you are using it in a Symfony application.

This adapter stores the values in-memory using one (or more) `Redis server`_ instances.

Unlike the :ref:`APCu adapter <apcu-adapter>`, and similarly to the
:ref:`Memcached adapter <memcached-adapter>`, it is not limited to the current server's
shared memory; you can store contents independent of your PHP environment. The ability
to utilize a cluster of servers to provide redundancy and/or fail-over is also available.

.. caution::

    **Requirements:** At least one `Redis server`_ must be installed and running to use this
    adapter. Additionally, this adapter requires a compatible extension or library that implements
    ``\Redis``, ``\RedisArray``, ``RedisCluster``, or ``\Predis``.

This adapter expects a `Redis`_, `RedisArray`_, `RedisCluster`_, or `Predis`_ instance to be
passed as the first parameter. A namespace and default cache lifetime can optionally be passed
as the second and third parameters::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    $cache = new RedisAdapter(

        // the object that stores a valid connection to your Redis system
        \Redis $redisConnection,

        // the string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until RedisAdapter::clear() is invoked or the server(s) are purged)
        $defaultLifetime = 0
    );

Configure the Connection
------------------------

The :method:`Symfony\\Component\\Cache\\Adapter\\RedisAdapter::createConnection`
helper method allows creating and configuring the Redis client class instance using a
`Data Source Name (DSN)`_::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    // pass a single DSN string to register a single server with the client
    $client = RedisAdapter::createConnection(
        'redis://localhost'
    );

The DSN can specify either an IP/host (and an optional port) or a socket path, as well as a
password and a database index. To enable TLS for connections, the scheme ``redis`` must be
replaced by ``rediss`` (the second ``s`` means "secure").

.. note::

    A `Data Source Name (DSN)`_ for this adapter must use either one of the following formats.

    .. code-block:: text

        redis[s]://[pass@][ip|host|socket[:port]][/db-index]

    .. code-block:: text

        redis[s]:[[user]:pass@]?[ip|host|socket[:port]][&params]

    Values for placeholders ``[user]``, ``[:port]``, ``[/db-index]`` and ``[&params]`` are optional.

Below are common examples of valid DSNs showing a combination of available values::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    // host "my.server.com" and port "6379"
    RedisAdapter::createConnection('redis://my.server.com:6379');

    // host "my.server.com" and port "6379" and database index "20"
    RedisAdapter::createConnection('redis://my.server.com:6379/20');

    // host "localhost", auth "abcdef" and timeout 5 seconds
    RedisAdapter::createConnection('redis://abcdef@localhost?timeout=5');

    // socket "/var/run/redis.sock" and auth "bad-pass"
    RedisAdapter::createConnection('redis://bad-pass@/var/run/redis.sock');

    // host "redis1" (docker container) with alternate DSN syntax and selecting database index "3"
    RedisAdapter::createConnection('redis:?host[redis1:6379]&dbindex=3');

    // providing credentials with alternate DSN syntax
    RedisAdapter::createConnection('redis:default:verysecurepassword@?host[redis1:6379]&dbindex=3');

    // a single DSN can also define multiple servers
    RedisAdapter::createConnection(
        'redis:?host[localhost]&host[localhost:6379]&host[/var/run/redis.sock:]&auth=my-password&redis_cluster=1'
    );

`Redis Sentinel`_, which provides high availability for Redis, is also supported
when using the PHP Redis Extension v5.2+ or the Predis library. Use the ``redis_sentinel``
parameter to set the name of your service group::

    RedisAdapter::createConnection(
        'redis:?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster'
    );

    // providing credentials
    RedisAdapter::createConnection(
        'redis:default:verysecurepassword@?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster'
    );

    // providing credentials and selecting database index "3"
    RedisAdapter::createConnection(
        'redis:default:verysecurepassword@?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster&dbindex=3'
    );

.. note::

    See the :class:`Symfony\\Component\\Cache\\Traits\\RedisTrait` for more options
    you can pass as DSN parameters.

Configure the Options
---------------------

The :method:`Symfony\\Component\\Cache\\Adapter\\RedisAdapter::createConnection` helper method
also accepts an array of options as its second argument. The expected format is an associative
array of ``key => value`` pairs representing option names and their respective values::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    $client = RedisAdapter::createConnection(

        // provide a string dsn
        'redis://localhost:6379',

        // associative array of configuration options
        [
            'class' => null,
            'persistent' => 0,
            'persistent_id' => null,
            'timeout' => 30,
            'read_timeout' => 0,
            'retry_interval' => 0,
            'tcp_keepalive' => 0,
            'lazy' => null,
            'redis_cluster' => false,
            'redis_sentinel' => null,
            'dbindex' => 0,
            'failover' => 'none',
            'ssl' => null,
        ]

    );

Available Options
~~~~~~~~~~~~~~~~~

``class`` (type: ``string``, default: ``null``)
    Specifies the connection library to return, either ``\Redis`` or ``\Predis\Client``.
    If none is specified, it will return ``\Redis`` if the ``redis`` extension is
    available, and ``\Predis\Client`` otherwise. Explicitly set this to ``\Predis\Client`` for Sentinel if you are
    running into issues when retrieving master information.

``persistent`` (type: ``int``, default: ``0``)
    Enables or disables use of persistent connections. A value of ``0`` disables persistent
    connections, and a value of ``1`` enables them.

``persistent_id`` (type: ``string|null``, default: ``null``)
    Specifies the persistent id string to use for a persistent connection.

``timeout`` (type: ``int``, default: ``30``)
    Specifies the time (in seconds) used to connect to a Redis server before the
    connection attempt times out.

``read_timeout`` (type: ``int``, default: ``0``)
    Specifies the time (in seconds) used when performing read operations on the underlying
    network resource before the operation times out.

``retry_interval`` (type: ``int``, default: ``0``)
    Specifies the delay (in milliseconds) between reconnection attempts in case the client
    loses connection with the server.

``tcp_keepalive`` (type: ``int``, default: ``0``)
    Specifies the `TCP-keepalive`_ timeout (in seconds) of the connection. This
    requires phpredis v4 or higher and a TCP-keepalive enabled server.

``lazy`` (type: ``bool``, default: ``null``)
    Enables or disables lazy connections to the backend. It's ``false`` by
    default when using this as a stand-alone component and ``true`` by default
    when using it inside a Symfony application.

``redis_cluster`` (type: ``bool``, default: ``false``)
    Enables or disables redis cluster. The actual value passed is irrelevant as long as it passes loose comparison
    checks: `redis_cluster=1` will suffice.

``redis_sentinel`` (type: ``string``, default: ``null``)
    Specifies the master name connected to the sentinels.

``dbindex`` (type: ``int``, default: ``0``)
    Specifies the database index to select.

``failover`` (type: ``string``, default: ``none``)
    Specifies failover for cluster implementations. For ``\RedisCluster`` valid options are ``none`` (default),
    ``error``, ``distribute`` or ``slaves``.  For ``\Predis\ClientInterface`` valid options are ``slaves``
    or ``distribute``.

``ssl`` (type: ``bool``, default: ``null``)
    SSL context options. See `php.net/context.ssl`_ for more information.

.. note::

    When using the `Predis`_ library some additional Predis-specific options are available.
    Reference the `Predis Connection Parameters`_ documentation for more information.

.. _redis-tag-aware-adapter:

Working with Tags
-----------------

In order to use tag-based invalidation, you can wrap your adapter in :class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`, but when Redis is used as backend, it's often more interesting to use the dedicated :class:`Symfony\\Component\\Cache\\Adapter\\RedisTagAwareAdapter`. Since tag invalidation logic is implemented in Redis itself, this adapter offers better performance when using tag-based invalidation::

    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

    $client = RedisAdapter::createConnection('redis://localhost');
    $cache = new RedisTagAwareAdapter($client);

Configuring Redis
~~~~~~~~~~~~~~~~~

When using Redis as cache, you should configure the ``maxmemory`` and ``maxmemory-policy``
settings. By setting ``maxmemory``, you limit how much memory Redis is allowed to consume.
If the amount is too low, Redis will drop entries that would still be useful and you benefit
less from your cache. Setting the ``maxmemory-policy`` to ``allkeys-lru`` tells Redis that
it is ok to drop data when it runs out of memory, and to first drop the oldest entries (least
recently used). If you do not allow Redis to drop entries, it will return an error when you
try to add data when no memory is available. An example setting could look as follows:

.. code-block:: ini

    maxmemory 100mb
    maxmemory-policy allkeys-lru

Read more about this topic in the official `Redis LRU Cache Documentation`_.

.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
.. _`Redis server`: https://redis.io/
.. _`Redis`: https://github.com/phpredis/phpredis
.. _`RedisArray`: https://github.com/phpredis/phpredis/blob/master/arrays.markdown#readme
.. _`RedisCluster`: https://github.com/phpredis/phpredis/blob/master/cluster.markdown#readme
.. _`Predis`: https://packagist.org/packages/predis/predis
.. _`Predis Connection Parameters`: https://github.com/nrk/predis/wiki/Connection-Parameters#list-of-connection-parameters
.. _`TCP-keepalive`: https://redis.io/topics/clients#tcp-keepalive
.. _`Redis Sentinel`: https://redis.io/topics/sentinel
.. _`Redis LRU Cache Documentation`: https://redis.io/topics/lru-cache
.. _`php.net/context.ssl`: https://php.net/context.ssl
