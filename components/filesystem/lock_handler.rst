LockHandler
===========

What is a Lock?
---------------

File locking is a mechanism that restricts access to a computer file by allowing
only one user or process access at any specific time. This mechanism was
introduced a few decades ago for mainframes, but continues being useful for
modern applications.

Symfony provides a LockHelper to help you use locks in your project.

Usage
-----

.. caution::

    The lock handler only works if you're using just one server. If you have
    several hosts, you must not use this helper.

A lock can be used, for example, to allow only one instance of a command to run.

.. code-block:: php

    use Symfony\Component\Filesystem\LockHandler;

    $lockHandler = new LockHandler('hello.lock');
    if (!$lockHandler->lock()) {
        // the resource "hello" is already locked by another process

        return 0;
    }

The first argument of the constructor is a string that it will use as part of
the name of the file used to create the lock on the local filesystem. A best
practice for Symfony commands is to use the command name, such as ``acme:my-command``.
``LockHandler`` sanitizes the contents of the string before creating
the file, so you can pass any value for this argument.

.. tip::

    The ``.lock`` extension is optional, but it's a common practice to include
    it. This will make it easier to find lock files on the filesystem. Moreover,
    to avoid name collisions, ``LockHandler`` also appends a hash to the name of
    the lock file.

By default, the lock will be created in the temporary directory, but you can
optionally select the directory where locks are created by passing it as the
second argument of the constructor.

The :method:`Symfony\\Component\\Filesystem\\LockHandler::lock` method tries to
acquire the lock. If the lock is acquired, the method returns ``true``,
``false`` otherwise. If the ``lock`` method is called several times on the same
instance it will always return ``true`` if the lock was acquired on the first
call.

You can pass an optional blocking argument as the first argument to the
``lock()`` method, which defaults to ``false``. If this is set to ``true``, your
PHP code will wait indefinitely until the lock is released by another process.

.. caution::

    Be aware of the fact that the resource lock is automatically released as soon
    as PHP applies the garbage-collection process to the ``LockHandler`` object.
    This means that if you refactor the first example shown in this article as
    follows::

        use Symfony\Component\Filesystem\LockHandler;

         if (!(new LockHandler('hello.lock'))->lock()) {
            // the resource "hello" is already locked by another process

            return 0;
        }

    Now the code won't work as expected because PHP's garbage collection mechanism
    removes the reference to the ``LockHandler`` object and thus, the lock is released
    just after it's been created.

    Another alternative way to release the lock explicitly when needed is to use the
    :method:`Symfony\\Component\\Filesystem\\LockHandler::release` method.
