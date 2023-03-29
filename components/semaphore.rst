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

.. _`semaphores`: https://en.wikipedia.org/wiki/Semaphore_(programming)
