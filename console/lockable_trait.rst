Prevent Multiple Executions of a Console Command
================================================

A simple but effective way to prevent multiple executions of the same command in
a single server is to use `locks`_. The :doc:`Lock component </components/lock>`
provides multiple classes to create locks based on the filesystem (:ref:`FlockStore <lock-store-flock>`),
shared memory (:ref:`SemaphoreStore <lock-store-semaphore>`) and even databases
and Redis servers.

In addition, the Console component provides a PHP trait called ``LockableTrait``
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

        protected function execute(InputInterface $input, OutputInterface $output)
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
        }
    }

.. versionadded:: 5.1

    The ``Command::SUCCESS`` constant was introduced in Symfony 5.1.

.. _`locks`: https://en.wikipedia.org/wiki/Lock_(computer_science)
