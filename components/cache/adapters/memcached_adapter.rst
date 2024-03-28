Memcached Cache Adapter
=======================

This adapter stores the values in-memory using one (or more) `Memcached server`_
instances. Unlike the :doc:`APCu adapter </components/cache/adapters/apcu_adapter>`, and similarly to the
:doc:`Redis adapter </components/cache/adapters/redis_adapter>`, it is not limited to the current server's
shared memory; you can store contents independent of your PHP environment.
The ability to utilize a cluster of servers to provide redundancy and/or fail-over
is also available.

.. caution::

    **Requirements:** The `Memcached PHP extension`_ as well as a `Memcached server`_
    must be installed, active, and running to use this adapter. Version ``2.2`` or
    greater of the `Memcached PHP extension`_ is required for this adapter.

This adapter expects a `Memcached`_ instance to be passed as the first
parameter. A namespace and default cache lifetime can optionally be passed as
the second and third parameters::

    use Symfony\Component\Cache\Adapter\MemcachedAdapter;

    $cache = new MemcachedAdapter(
        // the client object that sets options and adds the server instance(s)
        \Memcached $client,

        // a string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until MemcachedAdapter::clear() is invoked or the server(s) are restarted)
        $defaultLifetime = 0
    );

Configure the Connection
------------------------

The :method:`Symfony\\Component\\Cache\\Adapter\\MemcachedAdapter::createConnection`
helper method allows creating and configuring a `Memcached`_ class instance using a
`Data Source Name (DSN)`_ or an array of DSNs::

    use Symfony\Component\Cache\Adapter\MemcachedAdapter;

    // pass a single DSN string to register a single server with the client
    $client = MemcachedAdapter::createConnection(
        'memcached://localhost'
        // the DSN can include config options (pass them as a query string):
        // 'memcached://localhost:11222?retry_timeout=10'
        // 'memcached://localhost:11222?socket_recv_size=1&socket_send_size=2'
    );

    // pass an array of DSN strings to register multiple servers with the client
    $client = MemcachedAdapter::createConnection([
        'memcached://10.0.0.100',
        'memcached://10.0.0.101',
        'memcached://10.0.0.102',
        // etc.
    ]);

    // a single DSN can define multiple servers using the following syntax:
    // host[hostname-or-IP:port] (where port is optional). Sockets must include a trailing ':'
    $client = MemcachedAdapter::createConnection(
        'memcached:?host[localhost]&host[localhost:12345]&host[/some/memcached.sock:]=3'
    );

The `Data Source Name (DSN)`_ for this adapter must use the following format:

.. code-block:: text

    memcached://[user:pass@][ip|host|socket[:port]][?weight=int]

The DSN must include a IP/host (and an optional port) or a socket path, an
optional username and password (for SASL authentication; it requires that the
memcached extension was compiled with ``--enable-memcached-sasl``) and an
optional weight (for prioritizing servers in a cluster; its value is an integer
between ``0`` and ``100`` which defaults to ``null``; a higher value means more
priority).

Below are common examples of valid DSNs showing a combination of available values::

    use Symfony\Component\Cache\Adapter\MemcachedAdapter;

    $client = MemcachedAdapter::createConnection([
        // hostname + port
        'memcached://my.server.com:11211'

        // hostname without port + SASL username and password
        'memcached://rmf:abcdef@localhost'

        // IP address instead of hostname + weight
        'memcached://127.0.0.1?weight=50'

        // socket instead of hostname/IP + SASL username and password
        'memcached://janesmith:mypassword@/var/run/memcached.sock'

        // socket instead of hostname/IP + weight
        'memcached:///var/run/memcached.sock?weight=20'
    ]);

Configure the Options
---------------------

The :method:`Symfony\\Component\\Cache\\Adapter\\MemcachedAdapter::createConnection`
helper method also accepts an array of options as its second argument. The
expected format is an associative array of ``key => value`` pairs representing
option names and their respective values::

    use Symfony\Component\Cache\Adapter\MemcachedAdapter;

    $client = MemcachedAdapter::createConnection(
        // a DSN string or an array of DSN strings
        [],

        // associative array of configuration options
        [
            'libketama_compatible' => true,
            'serializer' => 'igbinary',
        ]
    );

Available Options
~~~~~~~~~~~~~~~~~

``auto_eject_hosts`` (type: ``bool``, default: ``false``)
    Enables or disables a constant, automatic, re-balancing of the cluster by
    auto-ejecting hosts that have exceeded the configured ``server_failure_limit``.

``buffer_writes`` (type: ``bool``, default: ``false``)
    Enables or disables buffered input/output operations, causing storage
    commands to buffer instead of being immediately sent to the remote
    server(s). Any action that retrieves data, quits the connection, or closes
    down the connection will cause the buffer to be committed.

``connect_timeout`` (type: ``int``, default: ``1000``)
    Specifies the timeout (in milliseconds) of socket connection operations when
    the ``no_block`` option is enabled.

    Valid option values include *any positive integer*.

``distribution`` (type: ``string``, default: ``consistent``)
    Specifies the item key distribution method among the servers. Consistent
    hashing delivers better distribution and allows servers to be added to the
    cluster with minimal cache losses.

    Valid option values include ``modula``, ``consistent``, and ``virtual_bucket``.

``hash`` (type: ``string``, default: ``md5``)
    Specifies the hashing algorithm used for item keys. Each hash algorithm has
    its advantages and its disadvantages. The default is suggested for compatibility
    with other clients.

    Valid option values include ``default``, ``md5``, ``crc``, ``fnv1_64``,
    ``fnv1a_64``, ``fnv1_32``, ``fnv1a_32``, ``hsieh``, and ``murmur``.

``libketama_compatible`` (type: ``bool``, default: ``true``)
    Enables or disables "libketama" compatible behavior, enabling other
    libketama-based clients to access the keys stored by client instance
    transparently (like Python and Ruby). Enabling this option sets the ``hash``
    option to ``md5`` and the ``distribution`` option to ``consistent``.

``no_block`` (type: ``bool``, default: ``true``)
    Enables or disables asynchronous input and output operations. This is the
    fastest transport option available for storage functions.

``number_of_replicas`` (type: ``int``, default: ``0``)
    Specifies the number of replicas that should be stored for each item (on
    different servers). This does not dedicate certain memcached servers to
    store the replicas in, but instead stores the replicas together with all of
    the other objects (on the "n" next servers registered).

    Valid option values include *any positive integer*.

``prefix_key`` (type: ``string``, default: an empty string)
    Specifies a "domain" (or "namespace") prepended to your keys. It cannot be
    longer than 128 characters and reduces the maximum key size.

    Valid option values include *any alphanumeric string*.

``poll_timeout`` (type: ``int``, default: ``1000``)
    Specifies the amount of time (in seconds) before timing out during a socket
    polling operation.

    Valid option values include *any positive integer*.

``randomize_replica_read`` (type: ``bool``, type: ``false``)
    Enables or disables randomization of the replica reads starting point.
    Normally the read is done from primary server and in case of a miss the read
    is done from "primary+1", then "primary+2", all the way to "n" replicas.
    This option sets the replica reads as randomized between all available
    servers; it allows distributing read load to multiple servers with the
    expense of more write traffic.

``recv_timeout`` (type: ``int``, default: ``0``)
    Specifies the amount of time (in microseconds) before timing out during an outgoing socket (read) operation.
    When the ``no_block`` option isn't enabled, this will allow you to still have timeouts on the reading of data.

    Valid option values include ``0`` or *any positive integer*.

``retry_timeout`` (type: ``int``, default: ``0``)
    Specifies the amount of time (in seconds) before timing out and retrying a
    connection attempt.

    Valid option values include *any positive integer*.

``send_timeout`` (type: ``int``, default: ``0``)
    Specifies the amount of time (in microseconds) before timing out during an
    incoming socket (send) operation. When the ``no_block`` option isn't enabled,
    this will allow you to still have timeouts on the sending of data.

    Valid option values include ``0`` or *any positive integer*.

``serializer`` (type: ``string``, default: ``php``)
    Specifies the serializer to use for serializing non-scalar values. The
    ``igbinary`` options requires the igbinary PHP extension to be enabled, as
    well as the memcached extension to have been compiled with support for it.

    Valid option values include ``php`` and ``igbinary``.

``server_failure_limit`` (type: ``int``, default: ``0``)
    Specifies the failure limit for server connection attempts before marking
    the server as "dead". The server will remain in the server pool unless
    ``auto_eject_hosts`` is enabled.

    Valid option values include *any positive integer*.

``socket_recv_size`` (type: ``int``)
    Specified the maximum buffer size (in bytes) in the context of incoming
    (receive) socket connection data.

    Valid option values include *any positive integer*, with a default value
    that *varies by platform and kernel configuration*.

``socket_send_size`` (type: ``int``)
    Specified the maximum buffer size (in bytes) in the context of outgoing (send)
    socket connection data.

    Valid option values include *any positive integer*, with a default value
    that *varies by platform and kernel configuration*.

``tcp_keepalive`` (type: ``bool``, default: ``false``)
    Enables or disables the "`keep-alive`_" `Transmission Control Protocol (TCP)`_
    feature, which is a feature that helps to determine whether the other end
    has stopped responding by sending probes to the network peer after an idle
    period and closing or persisting the socket based on the response (or lack thereof).

``tcp_nodelay`` (type: ``bool``, default: ``false``)
    Enables or disables the "`no-delay`_" (Nagle's algorithm) `Transmission Control Protocol (TCP)`_
    algorithm, which is a mechanism intended to improve the efficiency of
    networks by reducing the overhead of TCP headers by combining a number of
    small outgoing messages and sending them all at once.

``use_udp`` (type: ``bool``, default: ``false``)
    Enables or disables the use of `User Datagram Protocol (UDP)`_ mode (instead
    of `Transmission Control Protocol (TCP)`_ mode), where all operations are
    executed in a "fire-and-forget" manner; no attempt to ensure the operation
    has been received or acted on will be made once the client has executed it.

    .. caution::

        Not all library operations are tested in this mode. Mixed TCP and UDP
        servers are not allowed.

``verify_key`` (type: ``bool``, default: ``false``)
    Enables or disables testing and verifying of all keys used to ensure they
    are valid and fit within the design of the protocol being used.

.. tip::

    Reference the `Memcached`_ extension's `predefined constants`_ documentation
    for additional information about the available options.

.. _`Transmission Control Protocol (TCP)`: https://en.wikipedia.org/wiki/Transmission_Control_Protocol
.. _`User Datagram Protocol (UDP)`: https://en.wikipedia.org/wiki/User_Datagram_Protocol
.. _`no-delay`: https://en.wikipedia.org/wiki/TCP_NODELAY
.. _`keep-alive`: https://en.wikipedia.org/wiki/Keepalive
.. _`Memcached PHP extension`: https://www.php.net/manual/en/book.memcached.php
.. _`predefined constants`: https://www.php.net/manual/en/memcached.constants.php
.. _`Memcached server`: https://memcached.org/
.. _`Memcached`: https://www.php.net/manual/en/class.memcached.php
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
