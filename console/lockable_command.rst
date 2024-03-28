Prevent Running the Same Console Command Multiple Times
=======================================================

You can use `locks`_ to prevent the same command from running multiple times on
the same server. The :doc:`Lock component </components/lock>` provides multiple
classes to create locks based on the filesystem (:ref:`FlockStore <lock-store-flock>`),
shared memory (:ref:`SemaphoreStore <lock-store-semaphore>`) and even databases
and Redis servers.

Using the ``#[AsLockedCommand]`` attribute
------------------------------------------

The Console component provides an attribute called ``#[AsLockedCommand]`` that automatically
sets a lock on your command, and release it when the command ends::

    // ...
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\AsLockedCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    #[AsCommand(name: 'app:database:purge')]
    #[AsLockedCommand]
    class PurgeDatabaseCommand extends Command
    {
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $output->writeln('Purging the database...');

            return Command::SUCCESS;
        }
    }

.. versionadded:: 7.1

    The ``#[AsLockedCommand]`` attribute was introduced in Symfony 7.1.

By default, the command name will be use as a key for the lock, but you can customize it::

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\AsLockedCommand;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:database:purge')]
    #[AsLockedCommand(lock: 'my-custom-lock-key')]
    class PurgeDatabaseCommand extends Command
    {
        // ...
    }

If, for any reason, you need to define the lock key at runtime, you can define it with a callable::

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\AsLockedCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;

    #[AsCommand(name: 'app:database:purge')]
    #[AsLockedCommand(lock: [self::class, 'getLockKey'])]
    class PurgeDatabaseCommand extends Command
    {
        // ...

        public static function getLockKey(InputInterface $input): string
        {
            return $input->getArgument('lock-key');
        }
    }

Using the LockableTrait
-----------------------

In addition, the Console component provides a PHP :class:`Symfony\\Component\\Console\\Command\\LockableTrait`
that adds two convenient methods to lock and release commands::

    // ...
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Command\LockableTrait;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class UpdateContentsCommand extends Command
    {
        use LockableTrait;

        // ...

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            if (!$this->lock()) {
                $output->writeln('The command is already running in another process.');

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

.. _`locks`: https://en.wikipedia.org/wiki/Lock_(computer_science)
