.. index::
   single: Lock
   single: Components; Lock

The Lock Component
==================

    The Lock Component creates and manages `locks`_, a mechanism to provide
    exclusive access to a shared resource.

.. versionadded:: 3.3
    The Lock component was introduced in Symfony 3.3.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/lock`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/lock).

.. include:: /components/require_autoload.rst.inc

Usage
-----

Locks are used to guarantee exclusive access to some shared resource. In
Symfony applications, you can use locks for example to ensure that a command is
not executed more than once at the same time (on the same or different servers).

In order to manage the state of locks, you first need to create a ``Store``
and then use the :class:`Symfony\\Component\\Lock\\Factory` class to actually
create the lock for some resource::

    use Symfony\Component\Lock\Factory;
    use Symfony\Component\Lock\Store\SemaphoreStore;

    $store = new SemaphoreStore();
    $factory = new Factory($store);

Then, call to the :method:`Symfony\\Component\\Lock\\LockInterface::acquire`
method to try to acquire the lock. Its first argument is an arbitrary string
that represents the locked resource::

    // ...
    $lock = $factory->createLock('pdf-invoice-generation');

    if ($lock->acquire()) {
        // The resource "pdf-invoice-generation" is locked.
        // You can compute and generate invoice safely here.

        $lock->release();
    }

If the lock can not be acquired, the method returns ``false``. You can safely
call the ``acquire()`` method repeatedly, even if you already acquired it.

.. note::

    Unlike other implementations, the Lock Component distinguishes locks
    instances even when they are created for the same resource. If you want to
    share a lock in several services, share the ``Lock`` instance returned by
    the ``Factory::createLock`` method.

Blocking Locks
--------------

By default, when a lock cannot be acquired, the ``acquire`` method returns
``false`` immediately. In case you want to wait (indefinitely) until the lock
can be created, pass ``false`` as the argument of the ``acquire()`` method. This
is called a **blocking lock** because the execution of your application stops
until the lock is acquired.

Some of the built-in ``Store`` classes support this feature. When they don't,
you can decorate them with the ``RetryTillSaveStore`` class::

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
fatal errors or segmentation faults, we can't guarantee that the ``release()``
method will be called, which would cause the resource to be locked infinitely.

The best solution in those cases is to create **expiring locks**, which are
released automatically after some amount of time has passed (called TTL for
*Time To Live*). This time, in seconds, is configured as the second argument of
the ``createLock()`` method. If needed, these locks can also be released early
with the ``release()`` method.

The trickiest part when working with expiring locks is choosing the right TTL.
If it's too short, other processes could acquire the lock before finishing your
work; it it's too long and the process crashes before calling the ``release()``
method, the resource will stay locked until the timeout::

    // ...
    // create a expiring lock that lasts 30 seconds
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

.. code-block:: php

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
:ref:`RedisStore <lock-store-redis>`          remote  no        yes
:ref:`SemaphoreStore <lock-store-semaphore>`  local   yes       no
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
    locking. In those cases, it's better to use a local file or a remote store
    based on Redis or Memcached.

.. _lock-store-memcached:

MemcachedStore
~~~~~~~~~~~~~~

The MemcachedStore saves locks on a Memcached server, so first you must create
a Memcached connection implements the ``\Memcached`` class. This store does not
support blocking, and expects a TTL to avoid stalled locks::

    use Symfony\Component\Lock\Store\MemcachedStore;

    $memcached = new \Memcached();
    $memcached->addServer('localhost', 11211);

    $store = new MemcachedStore($memcached);

.. note::

    Memcached does not support TTL lower than 1 second.

.. _lock-store-redis:

RedisStore
~~~~~~~~~~

The RedisStore saves locks on a Redis server, so first you must create a Redis
connection implements the ``\Redis``, ``\RedisArray``, ``\RedisCluster`` or
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
then the lock is considered as acquired; otherwise is not acquired::

    use Symfony\Component\Lock\Quorum\MajorityQuorum;
    use Symfony\Component\Lock\Store\CombinedStore;
    use Symfony\Component\Lock\Store\RedisStore;

    $stores = [];
    foreach (array('server1', 'server2', 'server3') as $server) {
        $redis= new \Redis();
        $redis->connect($server);

        $stores[] = new RedisStore($redis);
    }

    $store = new CombinedStore($stores, new MajorityQuorum());

Instead of the simple majority strategy (``MajorityQuorum``) you can use the
``UnanimousQuorum`` to require the lock to be acquired in all the stores.

.. _`locks`: https://en.wikipedia.org/wiki/Lock_(computer_science)
.. _Packagist: https://packagist.org/packages/symfony/lock
.. _`PHP semaphore functions`: http://php.net/manual/en/book.sem.php
