Prevent Multiple Executions of a Console Command
================================================

.. versionadded:: 3.2
    The ``LockableTrait`` was introduced in Symfony 3.2.

A simple but effective way to prevent multiple executions of the same command in
a single server is to use **file locks**. The Filesystem component provides a
:doc:`LockHandler </components/filesystem/lock_handler>` class that eases the
creation and release of these locks.

In addition, the Console component provides a PHP trait called ``LockableTrait``
that adds two convenient methods to lock and release commands::

    // ...
    use Symfony\Component\Console\Command\LockableTrait;

    class UpdateContentsCommand extends Command
    {
        use LockableTrait;

        // ...

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            if (!$this->lock()) {
                $output->writeln('The command is already running in another process.');

                return 0;
            }

            // If you prefer to wait until the lock is released, use this:
            // $this->lock(null, true);

            // ...

            // if not released explicitly, Symfony releases the lock
            // automatically when the execution of the command ends
            $this->release();
        }
    }
