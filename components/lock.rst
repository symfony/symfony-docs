.. index::
   single: Lock
   single: Components; Lock

The Lock Component
==================

    The Lock Component creates and manages `locks`_, a mechanism to provide
    exclusive access to a shared resource.

If you're using the Symfony Framework, read the
:doc:`Symfony Framework Lock documentation </lock>`.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/lock

.. include:: /components/require_autoload.rst.inc

Usage
-----

Locks are used to guarantee exclusive access to some shared resource. In
Symfony applications, you can use locks for example to ensure that a command is
not executed more than once at the same time (on the same or different servers).

Locks are created using a :class:`Symfony\\Component\\Lock\\LockFactory` class,
which in turn requires another class to manage the storage of locks::

    use Symfony\Component\Lock\LockFactory;
    use Symfony\Component\Lock\Store\SemaphoreStore;

    $store = new SemaphoreStore();
    $factory = new LockFactory($store);

The lock is created by calling the :method:`Symfony\\Component\\Lock\\LockFactory::createLock`
method. Its first argument is an arbitrary string that represents the locked
resource. Then, a call to the :method:`Symfony\\Component\\Lock\\LockInterface::acquire`
method will try to acquire the lock::

    // ...
    $lock = $factory->createLock('pdf-creation');

    if ($lock->acquire()) {
        // The resource "pdf-creation" is locked.
        // You can compute and generate the invoice safely here.

        $lock->release();
    }

If the lock can not be acquired, the method returns ``false``. The ``acquire()``
method can be safely called repeatedly, even if the lock is already acquired.

.. note::

    Unlike other implementations, the Lock Component distinguishes lock
    instances even when they are created for the same resource. It means that for
    a given scope and resource one lock instance can be acquired multiple times.
    If a lock has to be used by several services, they should share the same ``Lock``
    instance returned by the ``LockFactory::createLock`` method.

.. tip::

    If you don't release the lock explicitly, it will be released automatically
    upon instance destruction. In some cases, it can be useful to lock a resource
    across several requests. To disable the automatic release behavior, set the
    third argument of the ``createLock()`` method to ``false``.

Serializing Locks
-----------------

The :class:`Symfony\\Component\\Lock\\Key` contains the state of the
:class:`Symfony\\Component\\Lock\\Lock` and can be serialized. This
allows the user to begin a long job in a process by acquiring the lock, and
continue the job in another process using the same lock.

First, you may create a serializable class containing the resource and the
key of the lock::

    // src/Lock/RefreshTaxonomy.php
    namespace App\Lock;

    use Symfony\Component\Lock\Key;

    class RefreshTaxonomy
    {
        public function __construct(
            private object $article,
            private Key $key,
        ) {
        }

        public function getArticle(): object
        {
            return $this->article;
        }

        public function getKey(): Key
        {
            return $this->key;
        }
    }

Then, you can use this class to dispatch all that's needed for another process
to handle the rest of the job::

    use App\Lock\RefreshTaxonomy;
    use Symfony\Component\Lock\Key;
    use Symfony\Component\Lock\Lock;

    $key = new Key('article.'.$article->getId());
    $lock = new Lock(
        $key,
        $this->store,
        300,  // ttl
        false // autoRelease
    );
    $lock->acquire(true);

    $this->bus->dispatch(new RefreshTaxonomy($article, $key));

.. note::

    Don't forget to set the ``autoRelease`` argument to ``false`` in the
    ``Lock`` constructor to avoid releasing the lock when the destructor is
    called.

Not all stores are compatible with serialization and cross-process locking: for
example, the kernel will automatically release semaphores acquired by the
:ref:`SemaphoreStore <lock-store-semaphore>` store. If you use an incompatible
store (see :ref:`lock stores <lock-stores>` for supported stores), an
exception will be thrown when the application tries to serialize the key.

.. _lock-blocking-locks:

Blocking Locks
--------------

By default, when a lock cannot be acquired, the ``acquire`` method returns
``false`` immediately. To wait (indefinitely) until the lock can be created,
pass ``true`` as the argument of the ``acquire()`` method. This is called a
**blocking lock** because the execution of your application stops until the
lock is acquired::

    use Symfony\Component\Lock\LockFactory;
    use Symfony\Component\Lock\Store\RedisStore;

    $store = new RedisStore(new \Predis\Client('tcp://localhost:6379'));
    $factory = new LockFactory($store);

    $lock = $factory->createLock('pdf-creation');
    $lock->acquire(true);

When the store does not support blocking locks by implementing the
:class:`Symfony\\Component\\Lock\\BlockingStoreInterface` interface (see
:ref:`lock stores <lock-stores>` for supported stores), the ``Lock`` class
will retry to acquire the lock in a non-blocking way until the lock is
acquired.

Expiring Locks
--------------

Locks created remotely are difficult to manage because there is no way for the
remote ``Store`` to know if the locker process is still alive. Due to bugs,
fatal errors or segmentation faults, it cannot be guaranteed that the
``release()`` method will be called, which would cause the resource to be
locked infinitely.

The best solution in those cases is to create **expiring locks**, which are
released automatically after some amount of time has passed (called TTL for
*Time To Live*). This time, in seconds, is configured as the second argument of
the ``createLock()`` method. If needed, these locks can also be released early
with the ``release()`` method.

The trickiest part when working with expiring locks is choosing the right TTL.
If it's too short, other processes could acquire the lock before finishing the
job; if it's too long and the process crashes before calling the ``release()``
method, the resource will stay locked until the timeout::

    // ...
    // create an expiring lock that lasts 30 seconds (default is 300.0)
    $lock = $factory->createLock('pdf-creation', ttl: 30);

    if (!$lock->acquire()) {
        return;
    }
    try {
        // perform a job during less than 30 seconds
    } finally {
        $lock->release();
    }

.. tip::

    To avoid leaving the lock in a locked state, it's recommended to wrap the
    job in a try/catch/finally block to always try to release the expiring lock.

In case of long-running tasks, it's better to start with a not too long TTL and
then use the :method:`Symfony\\Component\\Lock\\LockInterface::refresh` method
to reset the TTL to its original value::

    // ...
    $lock = $factory->createLock('pdf-creation', ttl: 30);

    if (!$lock->acquire()) {
        return;
    }
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

        $lock = $factory->createLock('pdf-creation', ttl: 30);
        // ...
        // refresh the lock for 30 seconds
        $lock->refresh();
        // ...
        // refresh the lock for 600 seconds (next refresh() call will be 30 seconds again)
        $lock->refresh(600);

This component also provides two useful methods related to expiring locks:
``getRemainingLifetime()`` (which returns ``null`` or a ``float``
as seconds) and ``isExpired()`` (which returns a boolean).

Automatically Releasing The Lock
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Locks are automatically released when their Lock objects are destroyed. This is
an implementation detail that is important when sharing Locks between
processes. In the example below, ``pcntl_fork()`` creates two processes and the
Lock will be released automatically as soon as one process finishes::

    // ...
    $lock = $factory->createLock('pdf-creation');
    if (!$lock->acquire()) {
        return;
    }

    $pid = pcntl_fork();
    if (-1 === $pid) {
        // Could not fork
        exit(1);
    } elseif ($pid) {
        // Parent process
        sleep(30);
    } else {
        // Child process
        echo 'The lock will be released now.';
        exit(0);
    }
    // ...

To disable this behavior, set the ``autoRelease`` argument of
``LockFactory::createLock()`` to ``false``. That will make the lock acquired
for 3600 seconds or until ``Lock::release()`` is called::

    $lock = $factory->createLock(
        'pdf-creation',
        3600, // ttl
        false // autoRelease
    );

Shared Locks
------------

A shared or `readers-writer lock`_ is a synchronization primitive that allows
concurrent access for read-only operations, while write operations require
exclusive access. This means that multiple threads can read the data in parallel
but an exclusive lock is needed for writing or modifying data. They are used for
example for data structures that cannot be updated atomically and are invalid
until the update is complete.

Use the :method:`Symfony\\Component\\Lock\\SharedLockInterface::acquireRead`
method to acquire a read-only lock, and
:method:`Symfony\\Component\\Lock\\LockInterface::acquire` method to acquire a
write lock::

    $lock = $factory->createLock('user-'.$user->id);
    if (!$lock->acquireRead()) {
        return;
    }

Similar to the ``acquire()`` method, pass ``true`` as the argument of ``acquireRead()``
to acquire the lock in a blocking mode::

    $lock = $factory->createLock('user-'.$user->id);
    $lock->acquireRead(true);

.. note::

    The `priority policy`_ of Symfony's shared locks depends on the underlying
    store (e.g. Redis store prioritizes readers vs writers).

When a read-only lock is acquired with the ``acquireRead()`` method, it's
possible to **promote** the lock, and change it to a write lock, by calling the
``acquire()`` method::

    $lock = $factory->createLock('user-'.$userId);
    $lock->acquireRead(true);

    if (!$this->shouldUpdate($userId)) {
        return;
    }

    $lock->acquire(true); // Promote the lock to a write lock
    $this->update($userId);

In the same way, it's possible to **demote** a write lock, and change it to a
read-only lock by calling the ``acquireRead()`` method.

When the provided store does not implement the
:class:`Symfony\\Component\\Lock\\SharedLockStoreInterface` interface (see
:ref:`lock stores <lock-stores>` for supported stores), the ``Lock`` class
will fallback to a write lock by calling the ``acquire()`` method.

The Owner of The Lock
---------------------

Locks that are acquired for the first time are :ref:`owned <lock-owner-technical-details>` by the ``Lock`` instance that acquired
it. If you need to check whether the current ``Lock`` instance is (still) the owner of
a lock, you can use the ``isAcquired()`` method::

    if ($lock->isAcquired()) {
        // We (still) own the lock
    }

Because some lock stores have expiring locks, it is possible for an instance to
lose the lock it acquired automatically::

    // If we cannot acquire ourselves, it means some other process is already working on it
    if (!$lock->acquire()) {
        return;
    }

    $this->beginTransaction();

    // Perform a very long process that might exceed TTL of the lock

    if ($lock->isAcquired()) {
        // Still all good, no other instance has acquired the lock in the meantime, we're safe
        $this->commit();
    } else {
        // Bummer! Our lock has apparently exceeded TTL and another process has started in
        // the meantime so it's not safe for us to commit.
        $this->rollback();
        throw new \Exception('Process failed');
    }

.. caution::

    A common pitfall might be to use the ``isAcquired()`` method to check if
    a lock has already been acquired by any process. As you can see in this example
    you have to use ``acquire()`` for this. The ``isAcquired()`` method is used to check
    if the lock has been acquired by the **current process** only.

.. _lock-owner-technical-details:

.. note::

    Technically, the true owners of the lock are the ones that share the same instance of ``Key``,
    not ``Lock``. But from a user perspective, ``Key`` is internal and you will likely only be working
    with the ``Lock`` instance so it's easier to think of the ``Lock`` instance as being the one that
    is the owner of the lock.

.. _lock-stores:

Available Stores
----------------

Locks are created and managed in ``Stores``, which are classes that implement
:class:`Symfony\\Component\\Lock\\PersistingStoreInterface` and, optionally,
:class:`Symfony\\Component\\Lock\\BlockingStoreInterface`.

The component includes the following built-in store types:

==========================================================  ======  ========  ======== =======
Store                                                       Scope   Blocking  Expiring Sharing
==========================================================  ======  ========  ======== =======
:ref:`FlockStore <lock-store-flock>`                        local   yes       no       yes
:ref:`MemcachedStore <lock-store-memcached>`                remote  no        yes      no
:ref:`MongoDbStore <lock-store-mongodb>`                    remote  no        yes      no
:ref:`PdoStore <lock-store-pdo>`                            remote  no        yes      no
:ref:`DoctrineDbalStore <lock-store-dbal>`                  remote  no        yes      no
:ref:`PostgreSqlStore <lock-store-pgsql>`                   remote  yes       no       yes
:ref:`DoctrineDbalPostgreSqlStore <lock-store-dbal-pgsql>`  remote  yes       no       yes
:ref:`RedisStore <lock-store-redis>`                        remote  no        yes      yes
:ref:`SemaphoreStore <lock-store-semaphore>`                local   yes       no       no
:ref:`ZookeeperStore <lock-store-zookeeper>`                remote  no        no       no
==========================================================  ======  ========  ======== =======

.. tip::

    A special ``InMemoryStore`` is available for saving locks in memory during
    a process, and can be useful for testing.

.. _lock-store-flock:

FlockStore
~~~~~~~~~~

The FlockStore uses the file system on the local computer to create the locks.
It does not support expiration, but the lock is automatically released when the
lock object goes out of scope and is freed by the garbage collector (for example
when the PHP process ends)::

    use Symfony\Component\Lock\Store\FlockStore;

    // the argument is the path of the directory where the locks are created
    // if none is given, sys_get_temp_dir() is used internally.
    $store = new FlockStore('/var/stores');

.. caution::

    Beware that some file systems (such as some types of NFS) do not support
    locking. In those cases, it's better to use a directory on a local disk
    drive or a remote store.

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

.. _lock-store-mongodb:

MongoDbStore
~~~~~~~~~~~~

The MongoDbStore saves locks on a MongoDB server ``>=2.2``, it requires a
``\MongoDB\Collection`` or ``\MongoDB\Client`` from `mongodb/mongodb`_ or a
`MongoDB Connection String`_.
This store does not support blocking and expects a TTL to
avoid stalled locks::

    use Symfony\Component\Lock\Store\MongoDbStore;

    $mongo = 'mongodb://localhost/database?collection=lock';
    $options = [
        'gcProbablity' => 0.001,
        'database' => 'myapp',
        'collection' => 'lock',
        'uriOptions' => [],
        'driverOptions' => [],
    ];
    $store = new MongoDbStore($mongo, $options);

The ``MongoDbStore`` takes the following ``$options`` (depending on the first parameter type):

=============  ================================================================================================
Option         Description
=============  ================================================================================================
gcProbablity   Should a TTL Index be created expressed as a probability from 0.0 to 1.0 (Defaults to ``0.001``)
database       The name of the database
collection     The name of the collection
uriOptions     Array of URI options for `MongoDBClient::__construct`_
driverOptions  Array of driver options for `MongoDBClient::__construct`_
=============  ================================================================================================

When the first parameter is a:

``MongoDB\Collection``:

- ``$options['database']`` is ignored
- ``$options['collection']`` is ignored

``MongoDB\Client``:

- ``$options['database']`` is mandatory
- ``$options['collection']`` is mandatory

MongoDB Connection String:

- ``$options['database']`` is used otherwise ``/path`` from the DSN, at least one is mandatory
- ``$options['collection']`` is used otherwise ``?collection=`` from the DSN, at least one is mandatory

.. note::

    The ``collection`` querystring parameter is not part of the `MongoDB Connection String`_ definition.
    It is used to allow constructing a ``MongoDbStore`` using a `Data Source Name (DSN)`_ without ``$options``.

.. _lock-store-pdo:

PdoStore
~~~~~~~~

The PdoStore saves locks in an SQL database. It is identical to DoctrineDbalStore
but requires a `PDO`_ connection or a `Data Source Name (DSN)`_. This store does
not support blocking, and expects a TTL to avoid stalled locks::

    use Symfony\Component\Lock\Store\PdoStore;

    // a PDO or DSN for lazy connecting through PDO
    $databaseConnectionOrDSN = 'mysql:host=127.0.0.1;dbname=app';
    $store = new PdoStore($databaseConnectionOrDSN, ['db_username' => 'myuser', 'db_password' => 'mypassword']);

.. note::

    This store does not support TTL lower than 1 second.

The table where values are stored is created automatically on the first call to
the :method:`Symfony\\Component\\Lock\\Store\\PdoStore::save` method.
You can also create this table explicitly by calling the
:method:`Symfony\\Component\\Lock\\Store\\PdoStore::createTable` method in
your code.

.. _lock-store-dbal:

DoctrineDbalStore
~~~~~~~~~~~~~~~~~

The DoctrineDbalStore saves locks in an SQL database. It is identical to PdoStore
but requires a `Doctrine DBAL Connection`_, or a `Doctrine DBAL URL`_. This store
does not support blocking, and expects a TTL to avoid stalled locks::

    use Symfony\Component\Lock\Store\DoctrineDbalStore;

    // a Doctrine DBAL connection or DSN
    $connectionOrURL = 'mysql://myuser:mypassword@127.0.0.1/app';
    $store = new DoctrineDbalStore($connectionOrURL);

.. note::

    This store does not support TTL lower than 1 second.

The table where values are stored is created automatically on the first call to
the :method:`Symfony\\Component\\Lock\\Store\\DoctrineDbalStore::save` method.
You can also add this table to your schema by calling
:method:`Symfony\\Component\\Lock\\Store\\DoctrineDbalStore::configureSchema` method
in your code or create this table explicitly by calling the
:method:`Symfony\\Component\\Lock\\Store\\DoctrineDbalStore::createTable` method.

.. _lock-store-pgsql:

PostgreSqlStore
~~~~~~~~~~~~~~~

The PostgreSqlStore and DoctrineDbalPostgreSqlStore uses `Advisory Locks`_ provided by PostgreSQL.
It is identical to DoctrineDbalPostgreSqlStore but requires `PDO`_ connection or
a `Data Source Name (DSN)`_. It supports native blocking, as well as sharing
locks::

    use Symfony\Component\Lock\Store\PostgreSqlStore;

    // a PDO instance or DSN for lazy connecting through PDO
    $databaseConnectionOrDSN = 'pgsql:host=localhost;port=5634;dbname=app';
    $store = new PostgreSqlStore($databaseConnectionOrDSN, ['db_username' => 'myuser', 'db_password' => 'mypassword']);

In opposite to the ``PdoStore``, the ``PostgreSqlStore`` does not need a table to
store locks and it does not expire.

.. _lock-store-dbal-pgsql:

DoctrineDbalPostgreSqlStore
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The DoctrineDbalPostgreSqlStore uses `Advisory Locks`_ provided by PostgreSQL.
It is identical to PostgreSqlStore but requires a `Doctrine DBAL Connection`_ or
a `Doctrine DBAL URL`_. It supports native blocking, as well as sharing locks::

    use Symfony\Component\Lock\Store\DoctrineDbalPostgreSqlStore;

    // a Doctrine Connection or DSN
    $databaseConnectionOrDSN = 'postgresql+advisory://myuser:mypassword@127.0.0.1:5634/lock';
    $store = new DoctrineDbalPostgreSqlStore($databaseConnectionOrDSN);

In opposite to the ``DoctrineDbalStore``, the ``DoctrineDbalPostgreSqlStore`` does not need a table to
store locks and does not expire.

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
manages several stores in sync (for example, several Redis servers). When a
lock is acquired, it forwards the call to all the managed stores, and it
collects their responses. If a simple majority of stores have acquired the
lock, then the lock is considered acquired::

    use Symfony\Component\Lock\Store\CombinedStore;
    use Symfony\Component\Lock\Store\RedisStore;
    use Symfony\Component\Lock\Strategy\ConsensusStrategy;

    $stores = [];
    foreach (['server1', 'server2', 'server3'] as $server) {
        $redis = new \Redis();
        $redis->connect($server);

        $stores[] = new RedisStore($redis);
    }

    $store = new CombinedStore($stores, new ConsensusStrategy());

Instead of the simple majority strategy (``ConsensusStrategy``) an
``UnanimousStrategy`` can be used to require the lock to be acquired in all
the stores::

    use Symfony\Component\Lock\Store\CombinedStore;
    use Symfony\Component\Lock\Strategy\UnanimousStrategy;

    $store = new CombinedStore($stores, new UnanimousStrategy());

.. caution::

    In order to get high availability when using the ``ConsensusStrategy``, the
    minimum cluster size must be three servers. This allows the cluster to keep
    working when a single server fails (because this strategy requires that the
    lock is acquired for more than half of the servers).

.. _lock-store-zookeeper:

ZookeeperStore
~~~~~~~~~~~~~~

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

The component guarantees that the same resource can't be locked twice as long as
the component is used in the following way.

Remote Stores
~~~~~~~~~~~~~

Remote stores (:ref:`MemcachedStore <lock-store-memcached>`,
:ref:`MongoDbStore <lock-store-mongodb>`,
:ref:`PdoStore <lock-store-pdo>`,
:ref:`PostgreSqlStore <lock-store-pgsql>`,
:ref:`RedisStore <lock-store-redis>` and
:ref:`ZookeeperStore <lock-store-zookeeper>`) use a unique token to recognize
the true owner of the lock. This token is stored in the
:class:`Symfony\\Component\\Lock\\Key` object and is used internally by
the ``Lock``.

Every concurrent process must store the ``Lock`` on the same server. Otherwise two
different machines may allow two different processes to acquire the same ``Lock``.

.. caution::

    To guarantee that the same server will always be safe, do not use Memcached
    behind a LoadBalancer, a cluster or round-robin DNS. Even if the main server
    is down, the calls must not be forwarded to a backup or failover server.

Expiring Stores
~~~~~~~~~~~~~~~

Expiring stores (:ref:`MemcachedStore <lock-store-memcached>`,
:ref:`MongoDbStore <lock-store-mongodb>`,
:ref:`PdoStore <lock-store-pdo>` and
:ref:`RedisStore <lock-store-redis>`)
guarantee that the lock is acquired only for the defined duration of time. If
the task takes longer to be accomplished, then the lock can be released by the
store and acquired by someone else.

The ``Lock`` provides several methods to check its health. The ``isExpired()``
method checks whether or not its lifetime is over and the ``getRemainingLifetime()``
method returns its time to live in seconds.

Using the above methods, a robust code would be::

    // ...
    $lock = $factory->createLock('pdf-creation', 30);

    if (!$lock->acquire()) {
        return;
    }
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
    time to live is enough to perform the task.

.. caution::

    Storing a ``Lock`` usually takes a few milliseconds, but network conditions
    may increase that time a lot (up to a few seconds). Take that into account
    when choosing the right TTL.

By design, locks are stored on servers with a defined lifetime. If the date or
time of the machine changes, a lock could be released sooner than expected.

.. caution::

    To guarantee that date won't change, the NTP service should be disabled
    and the date should be updated when the service is stopped.

FlockStore
~~~~~~~~~~

By using the file system, this ``Store`` is reliable as long as concurrent
processes use the same physical directory to store locks.

Processes must run on the same machine, virtual machine or container.
Be careful when updating a Kubernetes or Swarm service because, for a short
period of time, there can be two containers running in parallel.

The absolute path to the directory must remain the same. Be careful of symlinks
that could change at anytime: Capistrano and blue/green deployment often use
that trick. Be careful when the path to that directory changes between two
deployments.

Some file systems (such as some types of NFS) do not support locking.

.. caution::

    All concurrent processes must use the same physical file system by running
    on the same machine and using the same absolute path to the lock directory.

    Using a ``FlockStore`` in an HTTP context is incompatible with multiple
    front servers, unless to ensure that the same resource will always be
    locked on the same machine or to use a well configured shared file system.

Files on the file system can be removed during a maintenance operation. For
instance, to clean up the ``/tmp`` directory or after a reboot of the machine
when a directory uses ``tmpfs``. It's not an issue if the lock is released when
the process ended, but it is in case of ``Lock`` reused between requests.

.. caution::

    Do not store locks on a volatile file system if they have to be reused in
    several requests.

MemcachedStore
~~~~~~~~~~~~~~

The way Memcached works is to store items in memory. That means that by using
the :ref:`MemcachedStore <lock-store-memcached>` the locks are not persisted
and may disappear by mistake at any time.

If the Memcached service or the machine hosting it restarts, every lock would
be lost without notifying the running processes.

.. caution::

    To avoid that someone else acquires a lock after a restart, it's recommended
    to delay service start and wait at least as long as the longest lock TTL.

By default Memcached uses a LRU mechanism to remove old entries when the service
needs space to add new items.

.. caution::

    The number of items stored in Memcached must be under control. If it's not
    possible, LRU should be disabled and Lock should be stored in a dedicated
    Memcached service away from Cache.

When the Memcached service is shared and used for multiple usage, Locks could be
removed by mistake. For instance some implementation of the PSR-6 ``clear()``
method uses the Memcached's ``flush()`` method which purges and removes everything.

.. caution::

    The method ``flush()`` must not be called, or locks should be stored in a
    dedicated Memcached service away from Cache.

MongoDbStore
~~~~~~~~~~~~

.. caution::

    The locked resource name is indexed in the ``_id`` field of the lock
    collection. Beware that an indexed field's value in MongoDB can be
    `a maximum of 1024 bytes in length`_ including the structural overhead.

A TTL index must be used to automatically clean up expired locks.
Such an index can be created manually:

.. code-block:: javascript

    db.lock.createIndex(
        { "expires_at": 1 },
        { "expireAfterSeconds": 0 }
    )

Alternatively, the method ``MongoDbStore::createTtlIndex(int $expireAfterSeconds = 0)``
can be called once to create the TTL index during database setup. Read more
about `Expire Data from Collections by Setting TTL`_ in MongoDB.

.. tip::

    ``MongoDbStore`` will attempt to automatically create a TTL index. It's
    recommended to set constructor option ``gcProbablity`` to ``0.0`` to
    disable this behavior if you have manually dealt with TTL index creation.

.. caution::

    This store relies on all PHP application and database nodes to have
    synchronized clocks for lock expiry to occur at the correct time. To ensure
    locks don't expire prematurely; the lock TTL should be set with enough extra
    time in ``expireAfterSeconds`` to account for any clock drift between nodes.

``writeConcern`` and ``readConcern`` are not specified by MongoDbStore meaning
the collection's settings will take effect.
``readPreference`` is ``primary`` for all queries.
Read more about `Replica Set Read and Write Semantics`_ in MongoDB.

PdoStore
~~~~~~~~

The PdoStore relies on the `ACID`_ properties of the SQL engine.

.. caution::

    In a cluster configured with multiple primaries, ensure writes are
    synchronously propagated to every node, or always use the same node.

.. caution::

    Some SQL engines like MySQL allow to disable the unique constraint check.
    Ensure that this is not the case ``SET unique_checks=1;``.

In order to purge old locks, this store uses a current datetime to define an
expiration date reference. This mechanism relies on all server nodes to
have synchronized clocks.

.. caution::

    To ensure locks don't expire prematurely; the TTLs should be set with
    enough extra time to account for any clock drift between nodes.

PostgreSqlStore
~~~~~~~~~~~~~~~

The PdoStore relies on the `Advisory Locks`_ properties of the PostgreSQL
database. That means that by using :ref:`PostgreSqlStore <lock-store-pgsql>`
the locks will be automatically released at the end of the session in case the
client cannot unlock for any reason.

If the PostgreSQL service or the machine hosting it restarts, every lock would
be lost without notifying the running processes.

If the TCP connection is lost, the PostgreSQL may release locks without
notifying the application.

RedisStore
~~~~~~~~~~

The way Redis works is to store items in memory. That means that by using
the :ref:`RedisStore <lock-store-redis>` the locks are not persisted
and may disappear by mistake at any time.

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

Combined stores allow the storage of locks across several backends. It's a common
mistake to think that the lock mechanism will be more reliable. This is wrong.
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
    concurrent process on a new machine, check that other processes are stopped
    on the old one.

.. caution::

    When running on systemd with non-system user and option ``RemoveIPC=yes``
    (default value), locks are deleted by systemd when that user logs out.
    Check that process is run with a system user (UID <= SYS_UID_MAX) with
    ``SYS_UID_MAX`` defined in ``/etc/login.defs``, or set the option
    ``RemoveIPC=off`` in ``/etc/systemd/logind.conf``.

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

.. _`a maximum of 1024 bytes in length`: https://docs.mongodb.com/manual/reference/limits/#Index-Key-Limit
.. _`ACID`: https://en.wikipedia.org/wiki/ACID
.. _`Advisory Locks`: https://www.postgresql.org/docs/current/explicit-locking.html
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
.. _`Doctrine DBAL Connection`: https://github.com/doctrine/dbal/blob/master/src/Connection.php
.. _`Doctrine DBAL URL`: https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
.. _`Expire Data from Collections by Setting TTL`: https://docs.mongodb.com/manual/tutorial/expire-data/
.. _`locks`: https://en.wikipedia.org/wiki/Lock_(computer_science)
.. _`MongoDB Connection String`: https://docs.mongodb.com/manual/reference/connection-string/
.. _`mongodb/mongodb`: https://packagist.org/packages/mongodb/mongodb
.. _`MongoDBClient::__construct`: https://docs.mongodb.com/php-library/current/reference/method/MongoDBClient__construct/
.. _`PDO`: https://www.php.net/pdo
.. _`PHP semaphore functions`: https://www.php.net/manual/en/book.sem.php
.. _`Replica Set Read and Write Semantics`: https://docs.mongodb.com/manual/applications/replication/
.. _`ZooKeeper`: https://zookeeper.apache.org/
.. _`readers-writer lock`: https://en.wikipedia.org/wiki/Readers%E2%80%93writer_lock
.. _`priority policy`: https://en.wikipedia.org/wiki/Readers%E2%80%93writer_lock#Priority_policies
