The Semaphore Component
=======================

    The Semaphore Component manages `semaphores`_, a mechanism to provide
    exclusive access to a shared resource.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/semaphore

.. include:: /components/require_autoload.rst.inc

Usage
-----

In computer science, a semaphore is a variable or abstract data type used to
control access to a common resource by multiple processes in a concurrent
system such as a multitasking operating system. The main difference
with :doc:`locks </lock>` is that semaphores allow more than one process to
access a resource, whereas locks only allow one process.

Create semaphores with the :class:`Symfony\\Component\\Semaphore\\SemaphoreFactory`
class, which in turn requires another class to manage the storage::

    use Symfony\Component\Semaphore\SemaphoreFactory;
    use Symfony\Component\Semaphore\Store\RedisStore;

    $redis = new Redis();
    $redis->connect('172.17.0.2');

    $store = new RedisStore($redis);
    $factory = new SemaphoreFactory($store);

The semaphore is created by calling the
:method:`Symfony\\Component\\Semaphore\\SemaphoreFactory::createSemaphore`
method. Its first argument is an arbitrary string that represents the locked
resource. Its second argument is the maximum number of processes allowed. Then, a
call to the :method:`Symfony\\Component\\Semaphore\\SemaphoreInterface::acquire`
method will try to acquire the semaphore::

    // ...
    $semaphore = $factory->createSemaphore('pdf-invoice-generation', 2);

    if ($semaphore->acquire()) {
        // The resource "pdf-invoice-generation" is locked.
        // Here you can safely compute and generate the invoice.

        $semaphore->release();
    }

If the semaphore can not be acquired, the method returns ``false``. The
``acquire()`` method can be safely called repeatedly, even if the semaphore is
already acquired.

.. note::

    Unlike other implementations, the Semaphore component distinguishes
    semaphores instances even when they are created for the same resource. If a
    semaphore has to be used by several services, they should share the same
    ``Semaphore`` instance returned by the ``SemaphoreFactory::createSemaphore``
    method.

.. tip::

    If you don't release the semaphore explicitly, it will be released
    automatically on instance destruction. In some cases, it can be useful to
    lock a resource across several requests. To disable the automatic release
    behavior, set the fifth argument of the ``createSemaphore()`` method to ``false``.

Using Locks as Semaphore Stores
-------------------------------

You can also use the :class:`Symfony\\Component\\Semaphore\\Store\\LockStore`
to use any :doc:`Lock component </lock>` backend (flock, Redis, PDO, etc.)
as a semaphore store::

    use Symfony\Component\Lock\LockFactory;
    use Symfony\Component\Lock\Store\FlockStore;
    use Symfony\Component\Semaphore\SemaphoreFactory;
    use Symfony\Component\Semaphore\Store\LockStore;

    $lockFactory = new LockFactory(new FlockStore());
    $store = new LockStore($lockFactory);
    $factory = new SemaphoreFactory($store);

When using the FrameworkBundle, you can configure this with the ``lock://`` DSN:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/semaphore.yaml
        framework:
            lock: 'flock'
            semaphore: 'lock://'

    .. code-block:: php

        // config/packages/semaphore.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'lock' => 'flock',
                'semaphore' => 'lock://',
            ],
        ]);

You can also reference a specific named lock resource using ``lock://name``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/semaphore.yaml
        framework:
            lock:
                default: 'flock'
                my_locks: '%env(REDIS_DSN)%'
            semaphore:
                default: 'lock://'          # uses lock.default.factory
                other: 'lock://my_locks'    # uses lock.my_locks.factory

    .. code-block:: php

        // config/packages/semaphore.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'lock' => [
                    'default' => 'flock',
                    'my_locks' => env('REDIS_DSN'),
                ],
                'semaphore' => [
                    'default' => 'lock://',         // uses lock.default.factory
                    'other' => 'lock://my_locks',   // uses lock.my_locks.factory
                ],
            ],
        ]);

.. versionadded:: 8.1

    The ``LockStore`` was introduced in Symfony 8.1.

Serializing Semaphores
----------------------

.. versionadded:: 8.1

    Support for serializing semaphores was introduced in Symfony 8.1.

The :class:`Symfony\\Component\\Semaphore\\Key` contains the state of the
:class:`Symfony\\Component\\Semaphore\\Semaphore` and can be serialized. This
allows acquiring a semaphore in one process and releasing it in another, which
is useful for workflows, message queues, or any scenario where the acquire and
release happen in different contexts.

First, create the semaphore and acquire a slot::

    use Symfony\Component\Semaphore\Key;

    $key = new Key('pdf-generation', 5);  // 5 concurrent slots available
    $semaphore = $factory->createSemaphoreFromKey(
        $key,
        300,   // ttl
        false  // autoRelease
    );

    if ($semaphore->acquire()) {
        // dispatch to worker, passing $key
        $this->bus->dispatch(new GeneratePdfMessage($document, $key));
    }

Then, in the worker, use the key to reconstruct and release the semaphore::

    $semaphore = $factory->createSemaphoreFromKey($key, 300, false);

    try {
        // do work
    } finally {
        $semaphore->release();
    }

.. note::

    Don't forget to set the ``autoRelease`` argument to ``false`` in the
    ``Semaphore`` instantiation to avoid releasing the semaphore when the
    destructor is called.

Semaphores can be serialized using both the native PHP serialization system
and its :phpfunction:`serialize` function, or using the Serializer component.

.. _`semaphores`: https://en.wikipedia.org/wiki/Semaphore_(programming)
