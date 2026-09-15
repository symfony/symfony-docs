Prevent Running the Same Console Command Multiple Times
=======================================================

You can use `locks`_ to prevent the same command from running multiple times on
the same server. The :doc:`Lock component </lock>` provides multiple
classes to create locks based on the filesystem (:ref:`FlockStore <lock-store-flock>`),
shared memory (:ref:`SemaphoreStore <lock-store-semaphore>`) and even databases
and Redis servers.

In addition, the Console component provides a PHP trait called ``LockableTrait``
that adds two convenient methods to lock and release commands::

    // ...
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Command\LockableTrait;
    use Symfony\Component\Console\Style\SymfonyStyle;

    #[AsCommand(name: 'contents:update')]
    class UpdateContentsCommand
    {
        use LockableTrait;

        public function __invoke(SymfonyStyle $io): int
        {
            if (!$this->lock()) {
                $io->writeln('The command is already running in another process.');

                return Command::SUCCESS;
            }

            // If you prefer to wait until the lock is released, use this:
            // $this->lock(null, true);

            // ...

            // if not released explicitly, Symfony releases the lock
            // automatically when the execution of the command ends
            $this->release();

            return Command::SUCCESS;
        }
    }

The LockableTrait will use the ``SemaphoreStore`` if available and will default
to ``FlockStore`` otherwise. These stores are not scoped to your project, so two
projects (or two parallel test processes) running a command with the same name
on the same server share the same lock.

In Symfony applications, you can change the store used by all commands that
use the ``LockableTrait`` by configuring a :ref:`named lock <lock-named-locks>`
called ``console``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/lock.yaml
        framework:
            lock:
                console: '%env(LOCK_DSN)%'

    .. code-block:: php

        // config/packages/lock.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'lock' => [
                    'console' => '%env(LOCK_DSN)%',
                ],
            ],
        ]);

The lock factory of this named lock is injected into the commands through the
``setLockFactory()`` method of the trait. If there is no lock named ``console``
(e.g. when only the default lock is configured), the commands keep using the
stores mentioned above.

.. versionadded:: 8.2

    The ``setLockFactory()`` method and the autowiring of the ``console``
    named lock were introduced in Symfony 8.2.

You can also set a ``$lockFactory`` property with your own lock factory. It
takes precedence over the ``console`` named lock::

    // ...
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Command\LockableTrait;
    use Symfony\Component\Lock\LockFactory;

    #[AsCommand(name: 'contents:update')]
    class UpdateContentsCommand
    {
        use LockableTrait;

        // don't use PHP constructor property promotion here because the
        // LockableTrait already defines the `$lockFactory` property in this class
        public function __construct(LockFactory $lockFactory)
        {
            $this->lockFactory = $lockFactory;
        }

        // ...
    }

.. _`locks`: https://en.wikipedia.org/wiki/Lock_(computer_science)
