Dealing with Concurrency with Locks
===================================

When a program runs concurrently, some parts of code that modify shared
resources should not be accessed by multiple processes at the same time.
Symfony's Lock component creates and manages `locks`_, a mechanism to provide
exclusive access to a shared resource, ensuring that only one process runs a
critical section of code at any point in time to prevent race conditions.
Locks are useful for example to ensure that a command is not executed more
than once at the same time, on the same or different servers.

The following example shows a typical usage of the lock::

    $lock = $lockFactory->createLock('pdf-creation');
    if (!$lock->acquire()) {
        return;
    }

    // critical section of code
    $service->method();

    $lock->release();

.. _installing:

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the Lock component:

.. code-block:: terminal

    $ composer require symfony/lock

.. _configuring:
.. _lock-configuration:

Configuration
-------------

By default, Symfony provides a :ref:`Semaphore <lock-store-semaphore>`
when available, or a :ref:`Flock <lock-store-flock>` otherwise. You can configure
this behavior by using the ``lock`` key as follows:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/lock.yaml
        framework:
            lock: ~
            lock: 'flock'
            lock: 'flock:///path/to/file'
            lock: 'semaphore'
            lock: 'memcached://m1.docker'
            lock: ['memcached://m1.docker', 'memcached://m2.docker']
            lock: 'redis://r1.docker'
            lock: ['redis://r1.docker', 'redis://r2.docker']
            lock: 'rediss://r1.docker?ssl[verify_peer]=1&ssl[cafile]=...'
            lock: 'zookeeper://z1.docker'
            lock: 'zookeeper://z1.docker,z2.docker'
            lock: 'zookeeper://localhost01,localhost02:2181'
            lock: 'sqlite:///%kernel.project_dir%/var/lock.db'
            lock: 'mysql:host=127.0.0.1;dbname=app'
            lock: 'mysql+advisory:host=127.0.0.1;dbname=app'
            lock: 'pgsql:host=127.0.0.1;dbname=app'
            lock: 'pgsql+advisory:host=127.0.0.1;dbname=app'
            lock: 'sqlsrv:server=127.0.0.1;Database=app'
            lock: 'oci:host=127.0.0.1;dbname=app'
            lock: 'mongodb://127.0.0.1/app?collection=lock'
            lock: 'dynamodb://127.0.0.1/lock'
            lock: '%env(LOCK_DSN)%'
            # using an existing service
            lock: 'snc_redis.default'

    .. code-block:: php

        // config/packages/lock.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'lock' => null,
                'lock' => 'flock',
                'lock' => 'flock:///path/to/file',
                'lock' => 'semaphore',
                'lock' => 'memcached://m1.docker',
                'lock' => 'memcached://m1.docker,memcached://m2.docker',
                'lock' => 'redis://r1.docker',
                'lock' => 'redis://r1.docker,redis://r2.docker',
                'lock' => 'rediss://r1.docker?ssl[verify_peer]=1&ssl[cafile]=...',
                'lock' => 'zookeeper://z1.docker',
                'lock' => 'zookeeper://z1.docker,z2.docker',
                'lock' => 'zookeeper://localhost01,localhost02:2181',
                'lock' => 'sqlite:///%kernel.project_dir%/var/lock.db',
                'lock' => 'mysql:host=127.0.0.1;dbname=app',
                'lock' => 'mysql+advisory:host=127.0.0.1;dbname=app',
                'lock' => 'pgsql:host=127.0.0.1;dbname=app',
                'lock' => 'pgsql+advisory:host=127.0.0.1;dbname=app',
                'lock' => 'sqlsrv:server=127.0.0.1;Database=app',
                'lock' => 'oci:host=127.0.0.1;dbname=app',
                'lock' => 'mongodb://127.0.0.1/app?collection=lock',
                'lock' => 'dynamodb://127.0.0.1/lock',
                'lock' => env('LOCK_DSN'),
                // using an existing service
                'lock' => 'snc_redis.default',
            ],
        ]);

    .. code-block:: php-standalone

        use Symfony\Component\Lock\LockFactory;
        use Symfony\Component\Lock\Store\SemaphoreStore;

        // when using the component in standalone PHP applications, create the
        // store explicitly and pass it to the lock factory (see the available
        // stores later in this article)
        $store = new SemaphoreStore();
        $factory = new LockFactory($store);

.. note::

    The ``flock`` and ``semaphore`` stores are automatically scoped by
    ``kernel.project_id``, preventing lock collisions between multiple
    applications running on the same server.

    .. versionadded:: 8.1

        Automatic scoping of ``flock`` and ``semaphore`` stores was introduced
        in Symfony 8.1.

In addition to the default lock, Symfony applications can define several
:ref:`named locks <lock-named-locks>` to use different stores for different
parts of the application.

Locking a Resource
------------------

Locks are created with a :class:`Symfony\\Component\\Lock\\LockFactory`. In
Symfony applications, autowire it anywhere you need a lock; in standalone
applications, create the factory yourself, passing the store that persists
the locks:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Controller/PdfController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Lock\LockFactory;

        class PdfController extends AbstractController
        {
            #[Route('/download/terms-of-use.pdf')]
            public function downloadPdf(LockFactory $factory, MyPdfGeneratorService $pdf): Response
            {
                $lock = $factory->createLock('pdf-creation');
                $lock->acquire(true);

                // heavy computation
                $myPdf = $pdf->getOrCreatePdf();

                $lock->release();

                // ...
            }
        }

    .. code-block:: php-standalone

        use Symfony\Component\Lock\LockFactory;
        use Symfony\Component\Lock\Store\SemaphoreStore;

        $store = new SemaphoreStore();
        $factory = new LockFactory($store);

        $lock = $factory->createLock('pdf-creation');

        if ($lock->acquire()) {
            // the resource "pdf-creation" is locked, you can compute
            // and generate the PDF file safely here

            $lock->release();
        }

The first argument of ``createLock()`` is an arbitrary string that represents
the locked resource. If the lock can not be acquired (e.g. because another
process already acquired it), the ``acquire()`` method returns ``false``
immediately; pass ``true`` to it to wait until the lock is available (see
:ref:`blocking locks <lock-blocking-locks>`). The ``acquire()`` method can be
safely called repeatedly, even if the lock is already acquired.

.. warning::

    The Lock component distinguishes lock instances even when they are created
    for the same resource, so calling ``acquire()`` multiple times on the same
    ``Lock`` instance inside the same process won't block. That's why, when
    several services need exclusive access to the same resource, each of them
    must inject the ``LockFactory`` and create its own ``Lock`` instance. Only
    when several services have to cooperate on the same lock (e.g. one service
    acquires it and another one releases it), they should share the ``Lock``
    instance returned by the ``createLock()`` method.

If you don't release the lock explicitly, it will be released automatically
upon instance destruction; see :ref:`lock-automatic-release` to learn more
about this behavior and how to disable it.

Locking a Dynamic Resource
--------------------------

Sometimes the application is able to cut the resource into small pieces in
order to lock a small subset of processes and let others through. The previous
example showed how to lock the ``$pdf->getOrCreatePdf()`` call for everybody;
now let's see how to lock a ``$pdf->getOrCreatePdf($version)`` call only for
processes asking for the same ``$version``::

    // src/Controller/PdfController.php

    // ...
    #[Route('/download/{version}/terms-of-use.pdf')]
    public function downloadPdf($version, LockFactory $lockFactory, MyPdfGeneratorService $pdf): Response
    {
        $lock = $lockFactory->createLock('pdf-creation-'.$version);
        $lock->acquire(true);

        // heavy computation
        $myPdf = $pdf->getOrCreatePdf($version);

        $lock->release();

        // ...
    }

.. _lock-named-locks:

Naming Locks
------------

If the application needs different kinds of stores alongside each other, Symfony
provides :ref:`named locks <reference-lock-resources-name>`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/lock.yaml
        framework:
            lock:
                invoice: ['semaphore', 'redis://r2.docker']
                report: 'semaphore'

    .. code-block:: php

        // config/packages/lock.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'lock' => [
                    'invoice' => ['semaphore', 'redis://r2.docker'],
                    'report' => 'semaphore',
                ],
            ],
        ]);

After having configured one or more named locks, use the ``#[Target]``
attribute to inject a specific lock factory in any service or controller.
Symfony creates a target with the same name as the lock.

For example, to inject the ``invoice`` lock defined earlier::

    use Symfony\Component\DependencyInjection\Attribute\Target;
    use Symfony\Component\Lock\LockFactory;

    class SomeService
    {
        public function __construct(
            #[Target('invoice')] private LockFactory $lockFactory
        ) {
            // ...
        }
    }

Named locks are only available in Symfony applications. When using the Lock
component as standalone, create a different ``LockFactory`` for each store
instead.

.. _lock-blocking-locks:

Blocking Locks
--------------

By default, when a lock cannot be acquired, the ``acquire()`` method returns
``false`` immediately. To wait until the lock can be created, pass ``true`` as
the argument of the ``acquire()`` method. This is called a **blocking lock**
because the execution of your application stops until the lock is acquired::

    $lock = $factory->createLock('pdf-creation');
    $lock->acquire(true);

When the store does not support blocking locks by implementing the
:class:`Symfony\\Component\\Lock\\BlockingStoreInterface` interface (see
:ref:`lock stores <lock-stores>` for supported stores), the ``Lock`` class
will retry to acquire the lock in a non-blocking way until the lock is
acquired.

If the lock cannot be acquired despite blocking (e.g. because the store's
blocking mechanism failed while the lock is still held by another process), a
:class:`Symfony\\Component\\Lock\\Exception\\LockConflictedException` is thrown.

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

.. _lock-automatic-release:

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

.. note::

    In order for the above example to work, the `PCNTL`_ extension must be
    installed.

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

Locks that are acquired for the first time are
:ref:`owned <lock-owner-technical-details>` by the ``Lock`` instance that
acquired it. If you need to check whether the current ``Lock`` instance is
(still) the owner of a lock, you can use the ``isAcquired()`` method::

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

.. warning::

    A common pitfall might be to use the ``isAcquired()`` method to check if a
    lock has already been acquired by any process. As you can see in this
    example, you have to use ``acquire()`` for this. The ``isAcquired()``
    method is used to check if the lock has been acquired by the **current
    process** only.

.. _lock-owner-technical-details:

.. note::

    Technically, the true owners of the lock are the ones that share the same
    instance of ``Key``, not ``Lock``. But from a user perspective, ``Key`` is
    internal and you will likely only be working with the ``Lock`` instance,
    so it's easier to think of the ``Lock`` instance as being the one that is
    the owner of the lock.

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

    $key = new Key('article.'.$article->getId());
    $lock = $factory->createLockFromKey(
        $key,
        300,  // ttl
        false // autoRelease
    );
    $lock->acquire(true);

    $this->bus->dispatch(new RefreshTaxonomy($article, $key));

.. note::

    Don't forget to set the ``autoRelease`` argument to ``false`` in the
    ``Lock`` instantiation to avoid releasing the lock when the destructor is
    called (see :ref:`lock-automatic-release`).

Not all stores are compatible with serialization and cross-process locking: for
example, the kernel will automatically release semaphores acquired by the
:ref:`SemaphoreStore <lock-store-semaphore>` store. If you use an incompatible
store (see :ref:`lock stores <lock-stores>` for supported stores), an
exception will be thrown when the application tries to serialize the key.

Locks can be serialized using both the native PHP serialization system
and its :phpfunction:`serialize` function, or using the Serializer
component.

.. _lock-stores:

Available Stores
----------------

Locks are created and managed in ``Stores``, which are classes that implement
:class:`Symfony\\Component\\Lock\\PersistingStoreInterface` and, optionally,
:class:`Symfony\\Component\\Lock\\BlockingStoreInterface`.

The component includes the following built-in store types:

==========================================================  ======  ========  ======== ======= =============
Store                                                       Scope   Blocking  Expiring Sharing Serialization
==========================================================  ======  ========  ======== ======= =============
:ref:`DoctrineDbalPostgreSqlStore <lock-store-dbal-pgsql>`  remote  yes       no       yes     no
:ref:`DoctrineDbalStore <lock-store-dbal>`                  remote  retry     yes      no      yes
:ref:`DynamoDbStore <lock-store-dynamodb>`                  remote  retry     yes      no      yes
:ref:`FlockStore <lock-store-flock>`                        local   yes       no       yes     no
:ref:`MemcachedStore <lock-store-memcached>`                remote  retry     yes      no      yes
:ref:`MongoDbStore <lock-store-mongodb>`                    remote  retry     yes      no      yes
:ref:`PdoStore <lock-store-pdo>`                            remote  retry     yes      no      yes
:ref:`PostgreSqlStore <lock-store-pgsql>`                   remote  yes       no       yes     no
:ref:`RedisStore <lock-store-redis>`                        remote  retry     yes      yes     yes
:ref:`SemaphoreStore <lock-store-semaphore>`                local   yes       no       no      no
:ref:`ZookeeperStore <lock-store-zookeeper>`                remote  retry     no       no      no
==========================================================  ======  ========  ======== ======= =============

Stores that show ``retry`` in the ``Blocking`` column don't support blocking
locks natively, so the ``Lock`` class retries acquiring them in a non-blocking
way until the lock is acquired (see :ref:`blocking locks <lock-blocking-locks>`).
Stores that show ``yes`` in the ``Expiring`` column expect a TTL when creating
the lock to avoid stalled locks.

.. tip::

    Symfony includes two other special stores that are mostly useful for testing:

    * ``InMemoryStore`` (``LOCK_DSN=in-memory``), which saves locks in memory during a process;
    * ``NullStore`` (``LOCK_DSN=null``) which doesn't persist anything.

The following sections show how to create each store when using the component
as standalone. In Symfony applications, select the store to use with a DSN in
the :ref:`lock configuration <lock-configuration>` shown earlier in this
article.

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

.. note::

    When the Lock component is used in a full Symfony application, the ``FlockStore``
    is automatically scoped to a project-specific subdirectory (based on ``kernel.project_id``)
    to prevent lock collisions between multiple applications on the same server.

.. versionadded:: 8.1

    Automatic project-scoped lock directories for ``FlockStore`` were introduced
    in Symfony 8.1.

.. warning::

    Beware that some file systems (such as some types of NFS) do not support
    locking. In those cases, it's better to use a directory on a local disk
    drive or a remote store.

This store is reliable as long as all concurrent processes use the same
physical file system: they must run on the same machine, virtual machine or
container and use the same absolute path to the lock directory. Be careful
when updating a Kubernetes or Swarm service because, for a short period of
time, there can be two containers running in parallel. Be careful also with
symlinks that could change at any time (symlink-based release strategies and
blue/green deployments often use that trick) and, in general, whenever the
path to the lock directory changes between two deployments.

Using a ``FlockStore`` in an HTTP context is incompatible with multiple front
servers, unless you ensure that the same resource will always be locked on the
same machine or you use a well configured shared file system.

Files on the file system can be removed during a maintenance operation (for
instance, when cleaning up the ``/tmp`` directory or after a reboot of the
machine when the directory uses ``tmpfs``). That's not an issue if the lock is
released when the process ends, but it is for ``Lock`` instances reused
between requests.

.. danger::

    Do not store locks on a volatile file system if they have to be reused in
    several requests.

.. _lock-store-memcached:

MemcachedStore
~~~~~~~~~~~~~~

The MemcachedStore saves locks on a Memcached server. It requires a Memcached
connection implementing the ``\Memcached`` class::

    use Symfony\Component\Lock\Store\MemcachedStore;

    $memcached = new \Memcached();
    $memcached->addServer('localhost', 11211);

    $store = new MemcachedStore($memcached);

.. note::

    Memcached does not support TTL lower than 1 second.

Memcached stores its items in memory, so locks are not persisted and may
disappear by mistake at any time. If the Memcached service or the machine
hosting it restarts, every lock is lost without notifying the running
processes. To avoid that someone else acquires a lock after a restart, delay
the service start for at least as long as the longest lock TTL.

By default, Memcached uses a LRU mechanism to remove old entries when the
service needs space to add new items. Keep the number of items stored in
Memcached under control; if that's not possible, disable LRU or store the
locks in a dedicated Memcached service away from the cache.

.. danger::

    When the Memcached service is shared and used by multiple applications,
    locks could be removed by mistake. For instance, some implementations of
    the PSR-6 ``clear()`` method call Memcached's ``flush()`` method, which
    purges and removes everything. Never call the ``flush()`` method, or store
    the locks in a dedicated Memcached service away from the cache.

.. _lock-store-mongodb:

MongoDbStore
~~~~~~~~~~~~

The MongoDbStore saves locks on a MongoDB server (``>= 2.2``). It requires a
``\MongoDB\Collection`` or ``\MongoDB\Client`` from `mongodb/mongodb`_ or a
`MongoDB Connection String`_::

    use Symfony\Component\Lock\Store\MongoDbStore;

    $mongo = 'mongodb://localhost/database?collection=lock';
    $options = [
        'gcProbability' => 0.001,
        'database' => 'myapp',
        'collection' => 'lock',
        'uriOptions' => [],
        'driverOptions' => [],
    ];
    $store = new MongoDbStore($mongo, $options);

The ``MongoDbStore`` takes the following ``$options`` (depending on the first parameter type):

==============  ================================================================================================
Option          Description
==============  ================================================================================================
collection      The name of the collection
database        The name of the database
driverOptions   Array of driver options for `MongoDBClient::__construct`_
gcProbability   Should a TTL Index be created expressed as a probability from 0.0 to 1.0 (Defaults to ``0.001``)
uriOptions      Array of URI options for `MongoDBClient::__construct`_
==============  ================================================================================================

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

The locked resource name is indexed in the ``_id`` field of the lock
collection. Beware that in MongoDB an indexed field's value can be
`a maximum of 1024 bytes in length`_, including the structural overhead.

A TTL index must be used to automatically clean up expired locks. Such an
index can be created manually:

.. code-block:: javascript

    db.lock.createIndex(
        { "expires_at": 1 },
        { "expireAfterSeconds": 0 }
    )

Alternatively, the ``MongoDbStore::createTtlIndex(int $expireAfterSeconds = 0)``
method can be called once to create the TTL index during database setup. Read
more about `Expire Data from Collections by Setting TTL`_ in MongoDB.

.. tip::

    ``MongoDbStore`` will attempt to automatically create a TTL index. It's
    recommended to set the constructor option ``gcProbability`` to ``0.0`` to
    disable this behavior if you have manually dealt with TTL index creation.

This store relies on all PHP application and database nodes having
synchronized clocks for lock expiry to occur at the correct time. To ensure
locks don't expire prematurely, the lock TTL should be set with enough extra
time in ``expireAfterSeconds`` to account for any clock drift between nodes.

``writeConcern`` and ``readConcern`` are not specified by MongoDbStore,
meaning the collection's settings will take effect. ``readPreference`` is
``primary`` for all queries. Read more about
`Replica Set Read and Write Semantics`_ in MongoDB.

.. _lock-store-pdo:

PdoStore
~~~~~~~~

The PdoStore saves locks in an SQL database. It requires a `PDO`_ connection
or a `Data Source Name (DSN)`_::

    use Symfony\Component\Lock\Store\PdoStore;

    // a PDO instance or DSN for lazy connecting through PDO
    $databaseConnectionOrDSN = 'mysql:host=127.0.0.1;dbname=app';
    $store = new PdoStore($databaseConnectionOrDSN, ['db_username' => 'myuser', 'db_password' => 'mypassword']);

.. note::

    This store does not support TTL lower than 1 second.

The table where values are stored is created automatically on the first call to
the :method:`Symfony\\Component\\Lock\\Store\\PdoStore::save` method.
You can also create this table explicitly by calling the
:method:`Symfony\\Component\\Lock\\Store\\PdoStore::createTable` method in
your code.

The PdoStore relies on the `ACID`_ properties of the SQL engine. In a cluster
configured with multiple primaries, ensure writes are synchronously propagated
to every node, or always use the same node. Some SQL engines like MySQL allow
disabling the unique constraint check; ensure that this is not the case with
``SET unique_checks=1;``.

In order to purge old locks, this store uses a current datetime to define an
expiration date reference. This mechanism relies on all server nodes having
synchronized clocks. To ensure locks don't expire prematurely, the TTLs should
be set with enough extra time to account for any clock drift between nodes.

.. _lock-store-dbal:

DoctrineDbalStore
~~~~~~~~~~~~~~~~~

The DoctrineDbalStore saves locks in an SQL database. It is identical to
PdoStore but requires a `Doctrine DBAL Connection`_, or a `Doctrine DBAL URL`_::

    use Symfony\Component\Lock\Store\DoctrineDbalStore;

    // a Doctrine DBAL connection or DSN
    $connectionOrURL = 'mysql://myuser:mypassword@127.0.0.1/app';
    $store = new DoctrineDbalStore($connectionOrURL);

.. note::

    This store does not support TTL lower than 1 second.

The table where values are stored will be automatically generated when your run
the command:

.. code-block:: terminal

    $ php bin/console make:migration

If you prefer to create the table yourself and it has not already been created, you can
create this table explicitly by calling the
:method:`Symfony\\Component\\Lock\\Store\\DoctrineDbalStore::createTable` method.
You can also add this table to your schema by calling
:method:`Symfony\\Component\\Lock\\Store\\DoctrineDbalStore::configureSchema` method
in your code. If the table has not been created upstream, it will be created
automatically on the first call to the
:method:`Symfony\\Component\\Lock\\Store\\DoctrineDbalStore::save` method.

The same reliability considerations as for the :ref:`PdoStore <lock-store-pdo>`
apply to this store.

.. _lock-store-pgsql:

PostgreSqlStore
~~~~~~~~~~~~~~~

The PostgreSqlStore uses `Advisory Locks`_ provided by PostgreSQL. It requires a
`PDO`_ connection or a `Data Source Name (DSN)`_. It supports native blocking,
as well as sharing locks::

    use Symfony\Component\Lock\Store\PostgreSqlStore;

    // a PDO instance or DSN for lazy connecting through PDO
    $databaseConnectionOrDSN = 'pgsql:host=localhost;port=5634;dbname=app';
    $store = new PostgreSqlStore($databaseConnectionOrDSN, ['db_username' => 'myuser', 'db_password' => 'mypassword']);

Unlike the ``PdoStore``, the ``PostgreSqlStore`` does not need a table to store
locks and the locks do not expire. Instead, locks are automatically released at
the end of the session, e.g. in case the client cannot unlock for any reason.
Keep in mind that if the PostgreSQL service or the machine hosting it restarts,
every lock is lost without notifying the running processes. Likewise, if the
TCP connection is lost, PostgreSQL may release locks without notifying the
application.

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

The same behavior and reliability considerations as for the
:ref:`PostgreSqlStore <lock-store-pgsql>` apply to this store.

.. _lock-store-redis:

RedisStore
~~~~~~~~~~

The RedisStore saves locks on a Redis server. It requires a Redis connection
implementing the ``\Redis``, ``\RedisArray``, ``\RedisCluster``, ``\Relay\Relay``,
``\Relay\Cluster`` or ``\Predis`` classes::

    use Symfony\Component\Lock\Store\RedisStore;

    $redis = new \Redis();
    $redis->connect('localhost');

    $store = new RedisStore($redis);

Redis stores its items in memory, so locks are not persisted and may disappear
by mistake at any time. If the Redis service or the machine hosting it
restarts, every lock is lost without notifying the running processes. To avoid
that someone else acquires a lock after a restart, delay the service start for
at least as long as the longest lock TTL. Redis can also be configured to
persist items on disk, but this option slows down writes on the service and
could go against other uses of the server.

.. danger::

    When the Redis service is shared and used for multiple usages, locks could
    be removed by mistake. Never call the ``FLUSHDB`` command, or store the
    locks in a dedicated Redis service away from the cache.

.. _lock-store-semaphore:

SemaphoreStore
~~~~~~~~~~~~~~

The SemaphoreStore uses the `PHP semaphore functions`_ to create the locks::

    use Symfony\Component\Lock\Store\SemaphoreStore;

    $store = new SemaphoreStore();

    // optionally, pass a project identifier to scope locks and avoid
    // collisions between multiple applications on the same server
    $store = new SemaphoreStore($projectId);

.. versionadded:: 8.1

    The ``$projectId`` parameter was introduced in Symfony 8.1.

Semaphores are handled by the kernel, so all concurrent processes must run on
the same machine, virtual machine or container. Be careful when updating a
Kubernetes or Swarm service because, for a short period of time, there can be
two containers running in parallel. Before starting a concurrent process on a
new machine, check that the other processes are stopped on the old one.

.. warning::

    When running on systemd with a non-system user and the option
    ``RemoveIPC=yes`` (the default value), locks are deleted by systemd when
    that user logs out. Check that the process is run with a system user
    (UID <= SYS_UID_MAX) with ``SYS_UID_MAX`` defined in ``/etc/login.defs``,
    or set the option ``RemoveIPC=off`` in ``/etc/systemd/logind.conf``.

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

.. warning::

    In order to get high availability when using the ``ConsensusStrategy``, the
    minimum cluster size must be three servers. This allows the cluster to keep
    working when a single server fails (because this strategy requires that the
    lock is acquired for more than half of the servers).

It's a common mistake to think that this store makes the lock mechanism more
reliable. The ``CombinedStore`` is, at best, as reliable as the least reliable
of all the managed stores: as soon as one managed store returns erroneous
information, the ``CombinedStore`` won't be reliable. All concurrent processes
must use the same configuration, with the same number of managed stores and
the same endpoints. Also, instead of using a cluster of Redis or Memcached
servers, it's better to use a ``CombinedStore`` with a single server per
managed store.

.. _lock-store-zookeeper:

ZookeeperStore
~~~~~~~~~~~~~~

The ZookeeperStore saves locks on a `ZooKeeper`_ server. It requires a ZooKeeper
connection implementing the ``\Zookeeper`` class. This store does not support
blocking and expiration, but the lock is automatically released when the PHP
process is terminated::

    use Symfony\Component\Lock\Store\ZookeeperStore;

    $zookeeper = new \Zookeeper('localhost:2181');
    // use the following to define a high-availability cluster:
    // $zookeeper = new \Zookeeper('localhost1:2181,localhost2:2181,localhost3:2181');

    $store = new ZookeeperStore($zookeeper);

ZooKeeper does not require a TTL because it maintains the locks as ephemeral
nodes, which are automatically released at the end of the session (e.g. in case
the client cannot unlock for any reason). However, if the ZooKeeper service or
the machine hosting it restarts, every lock is lost without notifying the
running processes. Also, since the clean up of intermediate nodes becomes an
overhead, this store does not support multi-level node locks and all locks are
maintained at the root level.

To use ZooKeeper's high-availability feature, you can set up a cluster of
multiple servers so that, in case one of the servers goes down, the majority
will still be up and serving the requests. All the available servers in the
cluster will see the same state.

.. _lock-store-dynamodb:

DynamoDbStore
~~~~~~~~~~~~~

The DynamoDbStore saves locks on an Amazon DynamoDB table. Install it by running:

.. code-block:: terminal

    $ composer require symfony/amazon-dynamo-db-lock

It requires a `DynamoDbClient`_ instance or a `Data Source Name (DSN)`_::

    use Symfony\Component\Lock\Bridge\DynamoDb\Store\DynamoDbStore;

    // a DynamoDbClient instance or DSN
    $dynamoDbClientOrDSN = 'dynamodb://default/lock';
    $store = new DynamoDbStore($dynamoDbClientOrDSN);

The table where values are stored is created automatically on the first call to
the :method:`Symfony\\Component\\Lock\\Bridge\\DynamoDb\\Store\\DynamoDbStore::save` method.
You can also create this table explicitly by calling the
:method:`Symfony\\Component\\Lock\\Bridge\\DynamoDb\\Store\\DynamoDbStore::createTable` method in
your code.

Reliability
-----------

The component guarantees that the same resource can't be locked twice as long as
the component is used in the following way.

Remote Stores
~~~~~~~~~~~~~

Remote stores (see the ``Scope`` column in the table of
:ref:`available stores <lock-stores>`) use a unique token to recognize the true
owner of the lock. This token is stored in the
:class:`Symfony\\Component\\Lock\\Key` object and is used internally by
the ``Lock``.

Every concurrent process must store the ``Lock`` on the same server. Otherwise two
different machines may allow two different processes to acquire the same ``Lock``.

.. warning::

    To guarantee that the same server will always be safe, do not use Memcached
    behind a LoadBalancer, a cluster or round-robin DNS. Even if the main server
    is down, the calls must not be forwarded to a backup or failover server.

Expiring Stores
~~~~~~~~~~~~~~~

Expiring stores (see the ``Expiring`` column in the table of
:ref:`available stores <lock-stores>`) guarantee that the lock is acquired
only for the defined duration of time. If the task takes longer to be
accomplished, then the lock can be released by the store and acquired by
someone else.

When you can't be sure that the task will finish within the TTL, the safest
solution is to check the remaining lifetime of the lock before each step of
the job and handle the unexpected expiration explicitly::

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

        // Perform the task whose duration MUST be less than 5 seconds
    }

Choose wisely the lifetime of the ``Lock`` and check whether its remaining
time to live is enough to perform the task. Keep in mind that storing a
``Lock`` usually takes a few milliseconds, but network conditions may increase
that time a lot (up to a few seconds); take that into account when choosing
the right TTL. In addition, locks are stored on servers with a defined
lifetime, so if the date or time of the machine changes, a lock could be
released sooner than expected. To guarantee that the date won't change, the
NTP service should be disabled and the date should be updated only when the
service is stopped.

Changing the Store Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Changing the configuration of stores should be done very carefully. For
instance, during the deployment of a new version, processes with the new
configuration must not be started while old processes with the old
configuration are still running.

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
.. _`PCNTL`: https://www.php.net/manual/book.pcntl.php
.. _`DynamoDbClient`: https://async-aws.com/clients/dynamodb.html
