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
to ``FlockStore`` otherwise. You can override this behavior by setting
a ``$lockFactory`` property with your own lock factory::

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

.. versionadded::  7.1

    The ``$lockFactory`` property was introduced in Symfony 7.1.

.. _`locks`: https://en.wikipedia.org/wiki/Lock_(computer_science)
