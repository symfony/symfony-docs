.. index::
   single: Lock
   single: Components; Lock

The Lock Component
==================

    The Lock Component provides a mechanism to guarantee an exclusive access
    into a critical section. The component ships with ready to use stores for
    the most common backends.

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

In order to centralize state of locks, you first need to create a ``Store``.
Then, you can use the :class:`Symfony\\Component\\Lock\\Factory` to create a
Lock for your ``resource``.

The :method:`Symfony\\Component\\Lock\\LockInterface::acquire` method tries to
acquire the lock. If the lock can not be acquired, the method returns ``false``.
You can safely call the ``acquire()`` method several times, even if you already
acquired it.

.. code-block:: php

    use Symfony\Component\Lock\Factory;
    use Symfony\Component\Lock\Store\SemaphoreStore;

    $store = new SemaphoreStore();
    $factory = new Factory($store);
    $lock = $factory->createLock('invoice-pdf-generation');

    if ($lock->acquire()) {
        // the resource "invoice-pdf-generation" is locked.

        // You can compute and generate invoice safely here.

        $lock->release();
    }

The first argument of ``createLock`` is a string representation of the
``resource`` to lock.

.. note::

    In opposition to some other implementations, the Lock Component
    distinguishes locks instances, even when they are created from the same
    ``resource``.
    If you want to share a lock in several services. You have to share the
    instance of Lock returned by the ``Factory::createLock`` method.

Blocking Locks
--------------

You can pass an optional blocking argument as the first argument to the
:method:`Symfony\\Component\\Lock\\LockInterface::acquire` method, which
defaults to ``false``. If this is set to ``true``, your PHP code will wait
infinitely until the lock is released by another process.

Some ``Store`` (but not all) natively supports this feature. When they don't,
you can decorate it with the ``RetryTillSaveStore``.

.. code-block:: php

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

Working with a remote ``Store`` is hard. There is no way for the remote
``Store`` to know if the locker process is still alive.
Due to bugs, fatal errors or segmentation fault, we can't guarantee that the
``release()`` method will be called, which would cause a ``resource`` to be locked
infinitely.

To fill this gap, the remote ``Store`` provide an expiration mechanism: The
lock is acquired for a defined amount of time (named TTL for Time To Live).
When the timeout occurs, the lock is automatically released even if the locker
don't call the ``release()`` method.

That's why, when you create a lock on an expiring ``Store``, you have to choose
carefully the correct TTL. When too low, you take the risk to "loose" the lock
(and someone else acquire it) whereas you don't finish your task.
When too high and the process crash before you call the ``release()`` method,
the ``resource`` will stay lock till the timeout.

.. code-block:: php

    use Symfony\Component\Lock\Factory;
    use Symfony\Component\Lock\Store\RedisStore;

    $store = new RedisStore(new \Predis\Client('tcp://localhost:6379'));

    $factory = new Factory($store);
    $lock = $factory->createLock('charts-generation', 30);

    $lock->acquire();
    try {
        // perfom a job during less than 30 seconds
    } finally {
        $lock->release();
    }

.. tip::

    To avoid letting the Lock in a locking state, try to always release an
    expiring lock by wrapping the job in a try/catch block for instance.

When you have to work on a really long task, you should not set the TTL to
overlap the duration of this task. Instead, the Lock Component exposes a
:method:`Symfony\\Component\\Lock\\LockInterface::refresh` method in order to
put off the TTL of the Lock. Thereby you can choose a small initial TTL, and
regularly refresh the lock.

.. code-block:: php

    use Symfony\Component\Lock\Factory;
    use Symfony\Component\Lock\Store\RedisStore;

    $store = new RedisStore(new \Predis\Client('tcp://localhost:6379'));

    $factory = new Factory($store);
    $lock = $factory->createLock('charts-generation', 30);

    $lock->acquire();
    try {
        while (!$finished) {
            // perform a small part of the job.

            $lock->refresh();
            // resource is locked for 30 more seconds.
        }
    } finally {
        $lock->release();
    }

Available Stores
----------------

``Stores`` are classes that implement :class:`Symfony\\Component\\Lock\\StoreInterface`.
This component provides several adapters ready to use in your applications.

Here is a small summary of available ``Stores`` and their capabilities.

+----------------------------------------------+--------+----------+----------+
| Store                                        | Scope  | Blocking | Expiring |
+==============================================+========+==========+==========+
| :ref:`FlockStore <lock-store-flock>`         | local  | yes      | no       |
+----------------------------------------------+--------+----------+----------+
| :ref:`MemcachedStore <lock-store-memcached>` | remote | no       | yes      |
+----------------------------------------------+--------+----------+----------+
| :ref:`RedisStore <lock-store-redis>`         | remote | no       | yes      |
+----------------------------------------------+--------+----------+----------+
| :ref:`SemaphoreStore <lock-store-semaphore>` | local  | yes      | no       |
+----------------------------------------------+--------+----------+----------+

.. tip::

    Calling the :method:`Symfony\\Component\\Lock\\LockInterface::refresh`
    method on a Lock created from a non expiring ``Store`` like
    :ref:`FlockStore <lock-store-flock>` will do nothing.

.. _lock-store-flock:

FlockStore
~~~~~~~~~~

The FlockStore uses the fileSystem on the local computer to lock and store the
``resource``. It does not support expiration, but the lock is automatically
released when the PHP process is terminated.

.. code-block:: php

    use Symfony\Component\Lock\Store\FlockStore;

    $store = new FlockStore(sys_get_temp_dir());

The first argument of the constructor is the path to the directory where the
file will be created.

.. caution::

    Beware, some filesystems (like some version of NFS) do not support locking.
    We suggest to use local file, or to use a Store dedicated to remote usage
    like Redis or Memcached.

.. _Packagist: https://packagist.org/packages/symfony/lock

.. _lock-store-memcached:

MemcachedStore
~~~~~~~~~~~~~~

The MemcachedStore stores state of ``resource`` in a Memcached server. This
``Store`` does not support blocking, and expects a TLL to avoid infinity locks.

.. note::

    Memcached does not support TTL lower than 1 second.

It requires to have installed Memcached and have created a connection that
implements the ``\Memcached`` class.

.. code-block:: php

    use Symfony\Component\Lock\Store\MemcachedStore;

    $memcached = new \Memcached();
    $memcached->addServer('localhost', 11211);

    $store = new MemcachedStore($memcached);

.. _lock-store-redis:

RedisStore
~~~~~~~~~~

The RedisStore uses an instance of Redis to store the state of the ``resource``.
This ``Store`` does not support blocking, and expect a TLL to avoid infinity
locks.

It requires to have installed Redis and have created a connection that
implements the ``\Redis``, ``\RedisArray``, ``\RedisCluster`` or ``\Predis``
classes

.. code-block:: php

    use Symfony\Component\Lock\Store\RedisStore;

    $redis = new \Redis();
    $redis->connect('localhost');

    $store = new RedisStore($redis);

.. _lock-store-semaphore:

SemaphoreStore
~~~~~~~~~~~~~~

The SemaphoreStore uses the PHP semaphore functions to lock a ``resources``.

.. code-block:: php

    use Symfony\Component\Lock\Store\SemaphoreStore;

    $store = new SemaphoreStore();

.. _lock-store-combined:

CombinedStore
~~~~~~~~~~~~~

The CombinedStore synchronize several ``Stores`` together. When it's used to
acquired a Lock, it forwards the call to the managed ``Stores``, and regarding
the result, uses a quorum to decide whether or not the lock is acquired.

.. note::

    This ``Store`` is useful for High availability application. You can provide
    several Redis Server, and use theses server to manage the Lock. A
    MajorityQuorum is enough to safely acquire a lock while it allow some Redis
    server failure.

.. code-block:: php

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

.. tip::

    You can use the CombinedStore with the UnanimousQuorum to implement chained
    ``Stores``. It'll allow you to acquire easy local locks before asking for a
    remote lock

.. _Packagist: https://packagist.org/packages/symfony/lock
