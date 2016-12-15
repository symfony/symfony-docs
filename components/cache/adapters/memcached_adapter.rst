.. index::
    single: Cache Pool
    single: Memcached Cache

Memcached Cache Adapter
=======================

.. versionadded:: 3.3

    The Memcached adapter was introduced in Symfony 3.3.


This adapter stores the values in-memory using one (or more) `Memcached server`_ instances.
Unlike the ACPu adapter, and similarly to the Redis adapter, it is not limited to the current
server's shared memory; you can store contents independent of your PHP environment.
The ability to utilize a cluster of servers to provide redundancy and/or fail-over is also
available.

.. caution::

    **Requirements:** The `Memcached extension`_ as well as a `Memcached server`_
    must be installed, active, and running to use this adapter.


This adapter expects a `Memcached`_ instance to be passed as the first parameter. A namespace
and default cache lifetime can optionally be passed as the second and third parameters::

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
    );

    // pass an array of DSN strings to register multiple servers with the client
    $client = MemcachedAdapter::createConnection(array(
        'memcached://10.0.0.100',
        'memcached://10.0.0.101',
        'memcached://10.0.0.102',
        // etc...
    ));

The DSN can specify either an IP/host (and an optional port) or a socket path, as well as a user
and password (for SASL authentication) and a weight (for multiple server prioritization).

.. note::

    A `Data Source Name (DSN)`_ for this adapter must use the following format.

    .. code-block:: text

        memcached://[user:pass@][ip|host|socket[:port]][?weight=int]


Below are common examples of valid DSNs showing a combination of available values::

    use Symfony\Component\Cache\Adapter\MemcachedAdapter;

    $client = MemcachedAdapter::createConnection(array(

        // host "my.server.com" and port "11211"
        'memcached://my.server.com:11211'

        // host "localhost" and SASL use "rmf" and pass "abcdef"
        'memcached://rmf:abcdef@localhost'

        // ip "127.0.0.1" and weight of "50"
        'memcached://127.0.0.1?weight=50'

        // socket "/var/run/memcached.sock" and SASL user "user1" and pass "bad-pass"
        'memcached://user1:bad-pass@/var/run/memcached.sock'

        // socket "/var/run/memcached.sock" and weight of "20"
        'memcached:///var/run/memcached.sock?weight=20'

    ));


.. tip::

    The **weight** option allows for prioritizing the registered servers. For example, you
    could set your local Memcached instance to "80" and a remote instance to "20" to ensure
    favoritizsm of the local instance. This option is always optional, regardless of the
    number of servers registered.


.. note::

    The **username** and **password** is used for SASL authentication; it requires that the
    memcached extension was compiled with ``--enable-memcached-sasl``.


Configure the Options
---------------------

The :method:`Symfony\\Component\\Cache\\Adapter\\MemcachedAdapter::createConnection` helper method
also accepts an array of options as its second argument. The expected format is an associative
array of ``key => value`` pairs representing option names and their respective values::

    use Symfony\Component\Cache\Adapter\MemcachedAdapter;

    $client = MemcachedAdapter::createConnection(

        // provide a string dsn or array of dsns
        array(),

        // associative array of configuration options
        array(
            'compression' => true,
            'libketama_compatible' => true,
            'serializer' => 'igbinary',
         )

    );


Available Options
~~~~~~~~~~~~~~~~~

:strong:`compression`: ``bool``
    Enables or disables payload compression, where item values longer than 100 bytes are compressed
    during storage and decompressed during retrieval.

    Valid option values include ``true`` and ``false``,
    with a default value of ``true``.


:strong:`compression_type:` ``string``
    Specifies the compression method used on value payloads. when the **compression** option is enabled.

    Valid option values include ``fastlz`` and ``zlib``,
    with a default value that *varies based on flags used at compilation*.


:strong:`serializer:` ``string``
    Specifies the serializer to use for serializing non-scalar values. The ``igbinary`` options requires
    the igbinary PHP extension to be enabled, as well as the memcached extension to have been compiled with
    support for it.

    Valid option values include ``php`` and ``igbinary``,
    with a default value of ``php``.


:strong:`distribution:` ``string``
    Specifies the item key distribution method amoung the servers. Consistent hashing delivers
    better distribution and allows servers to be added to the cluster with minimal cache losses.

    Valid option values include ``modula``, ``consistent``, and ``virtual_bucket``,
    with a default value of ``consistent``.


:strong:`hash:` ``string``
    Specifies the hashing algorithm used for item keys. Each hash algorithm has its advantages
    and its disadvantages. The default is suggested for comptability with other clients.

    Valid option values include ``default``, ``md5``, ``crc``, ``fnv1_64``, ``fnv1a_64``,
    ``fnv1_32``, ``fnv1a_32``, ``hsieh``, and ``murmur``,
    with a default value of ``md5``.


:strong:`prefix_key:` ``string``
    Specifies a "domain" (or "namespace") prepended to your keys. It cannot be longer than 128
    characters and reduces the maximum key size.

    Valid option values include *any alphanumeric string*,
    with a default value of *an empty string*.


:strong:`server_failure_limit:` ``int``
    Specifies the failure limit for server connection attempts before marking the server as "dead".
    The server will remaining in the server pool unless ``auto_eject_hosts`` is enabled.

    Valid option values include *any positive integer*,
    with a default value of ``0``.


:strong:`auto_eject_hosts:` ``bool``
    Enables or disables a constant, automatic, re-balancing of the cluster by auto-ejecting hosts
    that have exceeded the configured ``server_failure_limit``.

    Valid option values include ``true`` and ``false``,
    with a default value of ``false``.


:strong:`verify_key:` ``bool``
    Enables or disables testing and verifying of all keys used to ensure they are valid and fit within
    the design of the protocol being used.

    Valid option values include ``true`` and ``false``,
    with a default value of ``false``.


:strong:`randomize_replica_read:` ``bool``
    Enables or disables randomization of the replica reads starting point. Normally the read is done from
    primary server and in case of a miss the read is done from "primary+1", then "primary+2", all the way
    to "n" replicas. This option sets the replica reads as randomized between all available servers; it
    allows distributing read load to multiple servers with the expense of more write traffic.

    Valid option values include ``true`` and ``false``,
    with a default value of ``false``.


:strong:`number_of_replicas:` ``int``
    Specifies the number of replicas that should be stored for each item (on different servers). This does
    not dedicate certain memcached servers to store the replicas in, but instead stores the replicas together
    with all of the other objects (on the "n" next servers registered).

    Valid option values include *any positive integer*,
    with a default value of ``0``.


:strong:`libketama_compatible:` ``bool``
    Enables or disables "libketama" compatible behavior, enabling other libketama-based clients to access
    the keys stored by client instance transparently (like Python and Ruby). Enabling this option sets
    the ``hash`` option to ``md5`` and the ``distribution`` option to ``consistent``.

    Valid option values include ``true`` and ``false``,
    with a default value of ``true``.


:strong:`buffer_writes:` ``bool``
    Enables or disables buffered input/output operations, causing storage commands to buffer instead of
    being immediately sent to the remote server(s). Any action that retrieves data, quits the connection,
    or closes down the connection will cause the buffer to be committed.

    Valid option values include ``true`` and ``false``,
    with a default value of ``false``.


:strong:`no_block:` ``bool``
    Enables or disables asynchronous input and output operations. This is the fastest transport option
    available for storage functions.

    Valid option values include ``true`` and ``false``,
    with a default value of ``true``.


:strong:`tcp_nodelay:` ``bool``
    Enables or disables the "`no-delay`_" (Nagle's algorithm) `Transmission Control Protocol (TCP)`_
    algorithm, which is a mechanism intended to improve the efficiency of networks by reducing the
    overhead of TCP headers by combining a number of small outgoing messages and sending them all at
    once.

    Valid option values include ``true`` and ``false``,
    with a default value of ``false``.


:strong:`tcp_keepalive:` ``bool``
    Enables or disables the "`keep-alive`_" `Transmission Control Protocol (TCP)`_ feature, which is a
    feature that helps to determine whether the other end has stopped responding by sending probes to
    the network peer after an idle period and closing or persisting the socket based on the response
    (or lack thereof).

    Valid option values include ``true`` and ``false``,
    with a default value of ``false``.


:strong:`use_udp:` ``bool``
    Enables or disabled the use of `User Datagram Protocol (UDP)`_ mode (instead of
    `Transmission Control Protocol (TCP)`_ mode), where all operations are executed in a
    "fire-and-forget" manner; no attempt to ensure the operation has been received or acted
    on will be made once the client has executed it.

    Valid option values include ``true`` and ``false``,
    with a default value of ``false``.

    .. caution::

        **Caution:**
        Not all library operations are tested in this mode. Mixed TCP and UDP servers are not allowed.


:strong:`socket_send_size:` ``int``
    Specified the maximum buffer size (in bytes) in the context of outgoing (send) socket connection data.

    Valid option values include *any positive integer*,
    with a default value that *varies by platform and kernel configuration*.


:strong:`socket_recv_size:` ``int``
    Specified the maximum buffer size (in bytes) in the context of incomming (recieve) socket connection data.

    Valid option values include *any positive integer*,
    with a default value that *varies by platform and kernel configuration*.


:strong:`connect_timeout:` ``int``
    Specifies the timeout (in milliseconds) of socket connection operations when the ``no_block`` option
    is enabled.

    Valid option values include *any positive integer*,
    with a default value of ``1000``.


:strong:`retry_timeout:` ``int``
    Specifies the amount of time (in seconds) before timing out and retrying a connection attempt.

    Valid option values include *any positive integer*,
    with a default value of ``0``.


:strong:`send_timeout:` ``int``
    Specifies the amount of time (in microseconds) before timing out during an incomming socket (send) operation.
    When the ``no_block`` option isn't enabled, this will allow you to still have timeouts on the sending of data.

    Valid option values include ``0`` or *any positive integer*,
    with a default value of ``0``.


:strong:`recv_timeout:` ``int``
    Specifies he amount of time (in microseconds) before timing out during an outgoing socket (read) operation.
    When the ``no_block`` option isn't enabled, this will allow you to still have timeouts on the reading of data.

    Valid option values include ``0`` or *any positive integer*,
    with a default value of ``0``.


:strong:`poll_timeout:` ``int``
    Specifies the amount of time (in seconds) before
    The amount of time (in seconds) before timing out during a socket polling operation.

    Valid option values include *any positive integer*,
    with a default value of ``1000``.


.. tip::
    Reference the `Memcached extension`_'s `predefined constants`_ documentation for
    additional information about the available options.


.. _`Transmission Control Protocol (TCP)`: https://en.wikipedia.org/wiki/Transmission_Control_Protocol
.. _`User Datagram Protocol (UDP)`: https://en.wikipedia.org/wiki/User_Datagram_Protocol
.. _`no-delay`: https://en.wikipedia.org/wiki/TCP_NODELAY
.. _`keep-alive`: https://en.wikipedia.org/wiki/Keepalive
.. _`Memcached extension`: http://php.net/manual/en/book.memcached.php
.. _`predefined constants`: http://php.net/manual/en/memcached.constants.php
.. _`Memcached server`: https://memcached.org/
.. _`Memcached`: http://php.net/manual/en/class.memcached.php
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
.. _`Domain Name System (DNS)`: https://en.wikipedia.org/wiki/Domain_Name_System
