.. index::
    single: Cache Pool
    single: Redis Cache

.. _`redis-adapter`:

Redis Cache Adapter
===================

This adapter stores the values in-memory using  one (or more) `Redis server`_ instances.
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

The DSN can specify either an IP/host (and an optional port) or a socket path, as well as a user
and password and a database index.

.. note::

    A `Data Source Name (DSN)`_ for this adapter must use the following format.

    .. code-block:: text

        redis://[user:pass@][ip|host|socket[:port]][/db-index]

Below are common examples of valid DSNs showing a combination of available values::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    // host "my.server.com" and port "6379"
    RedisAdapter::createConnection('redis://my.server.com:6379');

    // host "my.server.com" and port "6379" and database index "20"
    RedisAdapter::createConnection('redis://my.server.com:6379/20');

    // host "localhost" and SASL use "rmf" and pass "abcdef"
    RedisAdapter::createConnection('redis://rmf:abcdef@localhost');

    // socket "/var/run/redis.sock" and SASL user "user1" and pass "bad-pass"
    RedisAdapter::createConnection('redis://user1:bad-pass@/var/run/redis.sock');

Configure the Options
---------------------

The :method:`Symfony\\Component\\Cache\\Adapter\\RedisAdapter::createConnection` helper method
also accepts an array of options as its second argument. The expected format is an associative
array of ``key => value`` pairs representing option names and their respective values::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    $client = RedisAdapter::createConnection(

        // provide a string dsn
        'redis://localhost:6739',

        // associative array of configuration options
        array(
            'persistent' => 0,
            'persistent_id' => null,
            'timeout' => 30,
            'read_timeout' => 0,
            'retry_interval' => 0,
         )

    );

Available Options
~~~~~~~~~~~~~~~~~

``class`` (type: ``string``)
    Specifies the connection library to return, either ``\Redis`` or ``\Predis\Client``.
    If none is specified, it will return ``\Redis`` if the ``redis`` extension is
    available, and ``\Predis\Client`` otherwise.

``persistent`` (type: ``int``, default: ``0``)
    Enables or disables use of persistent connections. A value of ``0`` disables persistent
    connections, and a value of ``1`` enables them.

``persistent_id`` (type: ``string|null``, default: ``null``)
    Specifies the persistent id string to use for a persistent connection.

``read_timeout`` (type: ``int``, default: ``0``)
    Specifies the time (in seconds) used when performing read operations on the underlying
    network resource before the operation times out.

``retry_interval`` (type: ``int``, default: ``0``)
    Specifies the delay (in milliseconds) between reconnection attempts in case the client
    loses connection with the server.

``timeout`` (type: ``int``, default: ``30``)
    Specifies the time (in seconds) used to connect to a Redis server before the
    connection attempt times out.

.. note::
    When using the `Predis`_ library some additional Predis-specific options are available.
    Reference the `Predis Connection Parameters`_ documentation for more information.

.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
.. _`Redis server`: https://redis.io/
.. _`Redis`: https://github.com/phpredis/phpredis
.. _`RedisArray`: https://github.com/phpredis/phpredis/blob/master/arrays.markdown#readme
.. _`RedisCluster`: https://github.com/phpredis/phpredis/blob/master/cluster.markdown#readme
.. _`Predis`: https://packagist.org/packages/predis/predis
.. _`Predis Connection Parameters`: https://github.com/nrk/predis/wiki/Connection-Parameters#list-of-connection-parameters
