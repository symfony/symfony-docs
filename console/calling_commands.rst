How to Call Other Commands
==========================

If a command depends on another one being run before it you can call in the
console command itself. This is useful if a command depends on another command
or if you want to create a "meta" command that runs a bunch of other commands
(for instance, all commands that need to be run when the project's code has
changed on the production servers: clearing the cache, generating Doctrine
proxies, dumping web assets, ...).

Use the :method:`Symfony\\Component\\Console\\Application::doRun`. Then, create
a new :class:`Symfony\\Component\\Console\\Input\\ArrayInput` with the
arguments and options you want to pass to the command. The command name must be
the first argument.

Eventually, calling the ``doRun()`` method actually runs the command and returns
the returned code from the command (return value from command ``execute()``
method)::

    // ...
    use Symfony\Component\Console\Command;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class CreateUserCommand extends Command
    {
        // ...

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $greetInput = new ArrayInput([
                // the command name is passed as first argument
                'command' => 'demo:greet',
                'name'    => 'Fabien',
                '--yell'  => true,
            ]);

            $returnCode = $this->getApplication()->doRun($greetInput, $output);

            // ...
        }
    }

.. tip::

    If you want to suppress the output of the executed command, pass a
    :class:`Symfony\\Component\\Console\\Output\\NullOutput` as the second
    argument to ``$application->doRun()``.

.. note::

    Using ``doRun()`` instead of ``run()`` prevents autoexiting and allows to
    return the exit code instead.

    Also, using ``$this->getApplication()->doRun()`` instead of
    ``$this->getApplication()->find('demo:greet')->run()`` will allow proper
    events to be dispatched for that inner command as well.

.. caution::

    Note that all the commands will run in the same process and some of Symfony's
    built-in commands may not work well this way. For instance, the ``cache:clear``
    and ``cache:warmup`` commands change some class definitions, so running
    something after them is likely to break.

.. note::

    Most of the times, calling a command from code that is not executed on the
    command line is not a good idea. The main reason is that the command's
    output is optimized for the console and not to be passed to other commands.
