.. index::
   single: Lock
   single: Components; Lock

The Lock Component
==================

    The Lock Component creates and manages `locks`_, a mechanism to provide
    exclusive access to a shared resource.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/lock

Alternatively, you can clone the `<https://github.com/symfony/lock>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

Locks are used to guarantee exclusive access to some shared resource. In
Symfony applications, you can use locks for example to ensure that a command is
not executed more than once at the same time (on the same or different servers).

Locks are created using a :class:`Symfony\\Component\\Lock\\Factory` class,
which in turn requires another class to manage the storage of locks::

    use Symfony\Component\Lock\Factory;
    use Symfony\Component\Lock\Store\SemaphoreStore;

    $store = new SemaphoreStore();
    $factory = new Factory($store);

The lock is created by calling the :method:`Symfony\\Component\\Lock\\Factory::createLock`
method. Its first argument is an arbitrary string that represents the locked
resource. Then, a call to the :method:`Symfony\\Component\\Lock\\LockInterface::acquire`
method will try to acquire the lock::

    // ...
    $lock = $factory->createLock('pdf-invoice-generation');

    if ($lock->acquire()) {
        // The resource "pdf-invoice-generation" is locked.
        // You can compute and generate invoice safely here.

        $lock->release();
    }

If the lock can not be acquired, the method returns ``false``. The ``acquire()``
method can be safely called repeatedly, even if the lock is already acquired.

.. note::

    Unlike other implementations, the Lock Component distinguishes locks
    instances even when they are created for the same resource. If a lock has
    to be used by several services, they should share the same ``Lock`` instance
    returned by the ``Factory::createLock`` method.

.. tip::

    If you don't release the lock explicitly, it will be released automatically
    on instance destruction. In some cases, it can be useful to lock a resource
    across several requests. To disable the automatic release behavior, set the
    third argument of the ``createLock()`` method to ``false``.

Blocking Locks
--------------

By default, when a lock cannot be acquired, the ``acquire`` method returns
``false`` immediately. To wait (indefinitely) until the lock
can be created, pass ``true`` as the argument of the ``acquire()`` method. This
is called a **blocking lock** because the execution of your application stops
until the lock is acquired.

Some of the built-in ``Store`` classes support this feature. When they don't,
they can be decorated with the ``RetryTillSaveStore`` class::

    use Symfony\Component\Lock\Factory;
    use Symfony\Component\Lock\Store\RedisStore;
    use Symfony\Component\Lock\Store\RetryTillSaveStore;

    $store = new RedisStore(new \Predis\Client('tcp://localhost:6379'));
    $store = new RetryTillSaveStore($store);
    $factory = new Factory($store);

    $lock = $factory->createLock('notification-flush');
    $lock->acquire(true);

Expiring Locks
--------------

Locks created remotely are difficult to manage because there is no way for the
remote ``Store`` to know if the locker process is still alive. Due to bugs,
fatal errors or segmentation faults, it cannot be guaranteed that ``release()``
method will be called, which would cause the resource to be locked infinitely.

The best solution in those cases is to create **expiring locks**, which are
released automatically after some amount of time has passed (called TTL for
*Time To Live*). This time, in seconds, is configured as the second argument of
the ``createLock()`` method. If needed, these locks can also be released early
with the ``release()`` method.

The trickiest part when working with expiring locks is choosing the right TTL.
If it's too short, other processes could acquire the lock before finishing the
job; it it's too long and the process crashes before calling the ``release()``
method, the resource will stay locked until the timeout::

    // ...
    // create an expiring lock that lasts 30 seconds
    $lock = $factory->createLock('charts-generation', 30);

    $lock->acquire();
    try {
        // perform a job during less than 30 seconds
    } finally {
        $lock->release();
    }

.. tip::

    To avoid letting the lock in a locking state, it's recommended to wrap the
    job in a try/catch/finally block to always try to release the expiring lock.

In case of long-running tasks, it's better to start with a not too long TTL and
then use the :method:`Symfony\\Component\\Lock\\LockInterface::refresh` method
to reset the TTL to its original value::

    // ...
    $lock = $factory->createLock('charts-generation', 30);

    $lock->acquire();
    try {
        while (!$finished) {
            // perform a small part of the job.

            // renew the lock for 30 more seconds.
            $lock->refresh();
        }
    } finally {
        $lock->release();
    }

.. tip::

    Another useful technique for long-running tasks is to pass a custom TTL as
    an argument of the ``refresh()`` method to change the default lock TTL::

        $lock = $factory->createLock('charts-generation', 30);
        // ...
        // refresh the lock for 30 seconds
        $lock->refresh();
        // ...
        // refresh the lock for 600 seconds (next refresh() call will be 30 seconds again)
        $lock->refresh(600);

    .. versionadded:: 4.1
        The feature to pass a custom TTL as an argument of the ``refresh()``
        method was introduced in Symfony 4.1.

Available Stores
----------------

Locks are created and managed in ``Stores``, which are classes that implement
:class:`Symfony\\Component\\Lock\\StoreInterface`. The component includes the
following built-in store types:

============================================  ======  ========  ========
Store                                         Scope   Blocking  Expiring
============================================  ======  ========  ========
:ref:`FlockStore <lock-store-flock>`          local   yes       no
:ref:`MemcachedStore <lock-store-memcached>`  remote  no        yes
:ref:`PdoStore <lock-store-pdo>`              remote  no        yes
:ref:`RedisStore <lock-store-redis>`          remote  no        yes
:ref:`SemaphoreStore <lock-store-semaphore>`  local   yes       no
:ref:`ZookeeperStore <lock-store-zookeeper>`  remote  no        no
============================================  ======  ========  ========

.. _lock-store-flock:

FlockStore
~~~~~~~~~~

The FlockStore uses the file system on the local computer to create the locks.
It does not support expiration, but the lock is automatically released when the
PHP process is terminated::

    use Symfony\Component\Lock\Store\FlockStore;

    // the argument is the path of the directory where the locks are created
    $store = new FlockStore(sys_get_temp_dir());

.. caution::

    Beware that some file systems (such as some types of NFS) do not support
    locking. In those cases, it's better to use a directory on a local disk
    drive or a remote store based on PDO, Redis or Memcached.

.. _lock-store-memcached:

MemcachedStore
~~~~~~~~~~~~~~

The MemcachedStore saves locks on a Memcached server, it requires a Memcached
connection implementing the ``\Memcached`` class. This store does not
support blocking, and expects a TTL to avoid stalled locks::

    use Symfony\Component\Lock\Store\MemcachedStore;

    $memcached = new \Memcached();
    $memcached->addServer('localhost', 11211);

    $store = new MemcachedStore($memcached);

.. note::

    Memcached does not support TTL lower than 1 second.

.. _lock-store-pdo:

PdoStore
~~~~~~~~

.. versionadded:: 4.2
    The PdoStore was introduced Symfony 4.2.

The PdoStore saves locks in an SQL database. It requires a `PDO`_ connection, a
`Doctrine DBAL Connection`_, or a `Data Source Name (DSN)`_. This store does not
support blocking, and expects a TTL to avoid stalled locks::

    use Symfony\Component\Lock\Store\PdoStore;

    // a PDO, a Doctrine DBAL connection or DSN for lazy connecting through PDO
    $databaseConnectionOrDSN = 'mysql:host=127.0.0.1;dbname=lock';
    $store = new PdoStore($databaseConnectionOrDSN, ['db_username' => 'myuser', 'db_password' => 'mypassword']);

.. note::

    This store does not support TTL lower than 1 second.

Before storing locks in the database, you must create the table that stores
the information. The store provides a method called
:method:`Symfony\\Component\\Lock\\Store\\PdoStore::createTable`
to set up this table for you according to the database engine used::

    try {
        $store->createTable();
    } catch (\PDOException $exception) {
        // the table could not be created for some reason
    }

A great way to set up the table in production is to call the ``createTable()``
method in your local computer and then generate a
:ref:`database migration <doctrine-creating-the-database-tables-schema>`:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:diff
    $ php bin/console doctrine:migrations:migrate

.. _lock-store-redis:

RedisStore
~~~~~~~~~~

The RedisStore saves locks on a Redis server, it requires a Redis connection
implementing the ``\Redis``, ``\RedisArray``, ``\RedisCluster`` or
``\Predis`` classes. This store does not support blocking, and expects a TTL to
avoid stalled locks::

    use Symfony\Component\Lock\Store\RedisStore;

    $redis = new \Redis();
    $redis->connect('localhost');

    $store = new RedisStore($redis);

.. _lock-store-semaphore:

SemaphoreStore
~~~~~~~~~~~~~~

The SemaphoreStore uses the `PHP semaphore functions`_ to create the locks::

    use Symfony\Component\Lock\Store\SemaphoreStore;

    $store = new SemaphoreStore();

.. _lock-store-combined:

CombinedStore
~~~~~~~~~~~~~

The CombinedStore is designed for High Availability applications because it
manages several stores in sync (for example, several Redis servers). When a lock
is being acquired, it forwards the call to all the managed stores, and it
collects their responses. If a simple majority of stores have acquired the lock,
then the lock is considered as acquired; otherwise as not acquired::

    use Symfony\Component\Lock\Strategy\ConsensusStrategy;
    use Symfony\Component\Lock\Store\CombinedStore;
    use Symfony\Component\Lock\Store\RedisStore;

    $stores = [];
    foreach (array('server1', 'server2', 'server3') as $server) {
        $redis = new \Redis();
        $redis->connect($server);

        $stores[] = new RedisStore($redis);
    }

    $store = new CombinedStore($stores, new ConsensusStrategy());

Instead of the simple majority strategy (``ConsensusStrategy``) an
``UnanimousStrategy`` can be used to require the lock to be acquired in all
the stores.

.. caution::

    In order to get high availability when using the ``ConsensusStrategy``, the
    minimum cluster size must be three servers. This allows the cluster to keep
    working when a single server fails (because this strategy requires that the
    lock is acquired in more than half of the servers).

.. _lock-store-zookeeper:

ZookeeperStore
~~~~~~~~~~~~~~

.. versionadded:: 4.2
    The ZookeeperStore was introduced in Symfony 4.2.

The ZookeeperStore saves locks on a `ZooKeeper`_ server. It requires a ZooKeeper
connection implementing the ``\Zookeeper`` class. This store does not
support blocking and expiration but the lock is automatically released when the
PHP process is terminated::

    use Symfony\Component\Lock\Store\ZookeeperStore;

    $zookeeper = new \Zookeeper('localhost:2181');
    // use the following to define a high-availability cluster:
    // $zookeeper = new \Zookeeper('localhost1:2181,localhost2:2181,localhost3:2181');

    $store = new ZookeeperStore($zookeeper);

.. note::

    Zookeeper does not require a TTL as the nodes used for locking are ephemeral
    and die when the PHP process is terminated.

Reliability
-----------

The component guarantees that the same resource can't be lock twice as long as
the component is used in the following way.

Remote Stores
~~~~~~~~~~~~~

Remote stores (:ref:`MemcachedStore <lock-store-memcached>`,
:ref:`PdoStore <lock-store-pdo>`, :ref:`RedisStore <lock-store-redis>`, and
:ref:`ZookeeperStore <lock-store-zookeeper>`) use a unique token to recognize
the true owner of the lock. This token is stored in the
:class:`Symfony\\Component\\Lock\\Key` object and is used internally by
the ``Lock``, therefore this key must not be shared between processes (session,
caching, fork, ...).

.. caution::

    Do not share a key between processes.

Every concurrent process must store the ``Lock`` in the same server. Otherwise two
different machines may allow two different processes to acquire the same ``Lock``.

.. caution::

    To guarantee that the same server will always be safe, do not use Memcached
    behind a LoadBalancer, a cluster or round-robin DNS. Even if the main server
    is down, the calls must not be forwarded to a backup or failover server.

Expiring Stores
~~~~~~~~~~~~~~~

Expiring stores (:ref:`MemcachedStore <lock-store-memcached>`,
:ref:`PdoStore <lock-store-pdo>` and :ref:`RedisStore <lock-store-redis>`)
guarantee that the lock is acquired only for the defined duration of time. If
the task takes longer to be accomplished, then the lock can be released by the
store and acquired by someone else.

The ``Lock`` provides several methods to check its health. The ``isExpired()``
method checks whether or not it lifetime is over and the ``getRemainingLifetime()``
method returns its time to live in seconds.

Using the above methods, a more robust code would be::

    // ...
    $lock = $factory->createLock('invoice-publication', 30);

    $lock->acquire();
    while (!$finished) {
        if ($lock->getRemainingLifetime() <= 5) {
            if ($lock->isExpired()) {
                // lock was lost, perform a rollback or send a notification
                throw new \RuntimeException('Lock lost during the overall process');
            }

            $lock->refresh();
        }

        // Perform the task whose duration MUST be less than 5 minutes
    }

.. caution::

    Choose wisely the lifetime of the ``Lock`` and check whether its remaining
    time to leave is enough to perform the task.

.. caution::

    Storing a ``Lock`` usually takes a few milliseconds, but network conditions
    may increase that time a lot (up to a few seconds). Take that into account
    when choosing the right TTL.

By design, locks are stored in servers with a defined lifetime. If the date or
time of the machine changes, a lock could be released sooner than expected.

.. caution::

    To guarantee that date won't change, the NTP service should be disabled
    and the date should be updated when the service is stopped.

FlockStore
~~~~~~~~~~

By using the file system, this ``Store`` is reliable as long as concurrent
processes use the same physical directory to stores locks.

Processes must run on the same machine, virtual machine or container.
Be careful when updating a Kubernetes or Swarm service because for a short
period of time, there can be two running containers in parallel.

The absolute path to the directory must remain the same. Be careful of symlinks
that could change at anytime: Capistrano and blue/green deployment often use
that trick. Be careful when the path to that directory changes between two
deployments.

Some file systems (such as some types of NFS) do not support locking.

.. caution::

    All concurrent processes must use the same physical file system by running
    on the same machine and using the same absolute path to locks directory.

    By definition, usage of ``FlockStore`` in an HTTP context is incompatible
    with multiple front servers, unless to ensure that the same resource will
    always be locked on the same machine or to use a well configured shared file
    system.

Files on file system can be removed during a maintenance operation. For instance
to cleanup the ``/tmp`` directory or after a reboot of the machine when directory
uses tmpfs. It's not an issue if the lock is released when the process ended, but
it is in case of ``Lock`` reused between requests.

.. caution::

    Do not store locks on a volatile file system if they have to be reused in
    several requests.

MemcachedStore
~~~~~~~~~~~~~~

The way Memcached works is to store items in memory. That means that by using
the :ref:`MemcachedStore <lock-store-memcached>` the locks are not persisted
and may disappear by mistake at anytime.

If the Memcached service or the machine hosting it restarts, every lock would
be lost without notifying the running processes.

.. caution::

    To avoid that someone else acquires a lock after a restart, it's recommended
    to delay service start and wait at least as long as the longest lock TTL.

By default Memcached uses a LRU mechanism to remove old entries when the service
needs space to add new items.

.. caution::

    Number of items stored in the Memcached must be under control. If it's not
    possible, LRU should be disabled and Lock should be stored in a dedicated
    Memcached service away from Cache.

When the Memcached service is shared and used for multiple usage, Locks could be
removed by mistake. For instance some implementation of the PSR-6 ``clear()``
method uses the Memcached's ``flush()`` method which purges and removes everything.

.. caution::

    The method ``flush()`` must not be called, or locks should be stored in a
    dedicated Memcached service away from Cache.

PdoStore
~~~~~~~~~~

The PdoStore relies on the `ACID`_ properties of the SQL engine.

.. caution::

    In a cluster configured with multiple primaries, ensure writes are
    synchronously propagated to every nodes, or always use the same node.

.. caution::

    Some SQL engines like MySQL allow to disable the unique constraint check.
    Ensure that this is not the case ``SET unique_checks=1;``.

In order to purge old locks, this store uses a current datetime to define an
expiration date reference. This mechanism relies on all server nodes to
have synchronized clocks.

.. caution::

    To ensure locks don't expire prematurely; the TTLs should be set with
    enough extra time to account for any clock drift between nodes.

RedisStore
~~~~~~~~~~

The way Redis works is to store items in memory. That means that by using
the :ref:`RedisStore <lock-store-redis>` the locks are not persisted
and may disappear by mistake at anytime.

If the Redis service or the machine hosting it restarts, every locks would
be lost without notifying the running processes.

.. caution::

    To avoid that someone else acquires a lock after a restart, it's recommended
    to delay service start and wait at least as long as the longest lock TTL.

.. tip::

    Redis can be configured to persist items on disk, but this option would
    slow down writes on the service. This could go against other uses of the
    server.

When the Redis service is shared and used for multiple usages, locks could be
removed by mistake.

.. caution::

    The command ``FLUSHDB`` must not be called, or locks should be stored in a
    dedicated Redis service away from Cache.

CombinedStore
~~~~~~~~~~~~~

Combined stores allow to store locks across several backends. It's a common
mistake to think that the lock mechanism will be more reliable. This is wrong
The ``CombinedStore`` will be, at best, as reliable as the least reliable of
all managed stores. As soon as one managed store returns erroneous information,
the ``CombinedStore`` won't be reliable.

.. caution::

    All concurrent processes must use the same configuration, with the same
    amount of managed stored and the same endpoint.

.. tip::

    Instead of using a cluster of Redis or Memcached servers, it's better to use
    a ``CombinedStore`` with a single server per managed store.

SemaphoreStore
~~~~~~~~~~~~~~

Semaphores are handled by the Kernel level. In order to be reliable, processes
must run on the same machine, virtual machine or container. Be careful when
updating a Kubernetes or Swarm service because for a short period of time, there
can be two running containers in parallel.

.. caution::

    All concurrent processes must use the same machine. Before starting a
    concurrent process on a new machine, check that other process are stopped
    on the old one.

ZookeeperStore
~~~~~~~~~~~~~~

The way ZookeeperStore works is by maintaining locks as ephemeral nodes on the
server. That means that by using :ref:`ZookeeperStore <lock-store-zookeeper>`
the locks will be automatically released at the end of the session in case the
client cannot unlock for any reason.

If the ZooKeeper service or the machine hosting it restarts, every lock would
be lost without notifying the running processes.

.. tip::

    To use ZooKeeper's high-availability feature, you can setup a cluster of
    multiple servers so that in case one of the server goes down, the majority
    will still be up and serving the requests. All the available servers in the
    cluster will see the same state.

.. note::

    As this store does not support multi-level node locks, since the clean up of
    intermediate nodes becomes an overhead, all locks are maintained at the root
    level.

Overall
~~~~~~~

Changing the configuration of stores should be done very carefully. For
instance, during the deployment of a new version. Processes with new
configuration must not be started while old processes with old configuration
are still running.

.. _`ACID`: https://en.wikipedia.org/wiki/ACID
.. _`locks`: https://en.wikipedia.org/wiki/Lock_(computer_science)
.. _Packagist: https://packagist.org/packages/symfony/lock
.. _`PHP semaphore functions`: http://php.net/manual/en/book.sem.php
.. _`PDO`: https://php.net/pdo
.. _`Doctrine DBAL Connection`: https://github.com/doctrine/dbal/blob/master/lib/Doctrine/DBAL/Connection.php
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
.. _`ZooKeeper`: https://zookeeper.apache.org/
