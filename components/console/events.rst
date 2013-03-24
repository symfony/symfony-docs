.. index::
    single: Console; Events

.. versionadded:: 2.3
    The feature described in this chapter was added in 2.3.

Using Events
============

The Application class of the Console component allows you to optionally hook
into the lifecycle of a console application via events. Instead of reinventing
the wheel, it uses the Symfony EventDispatcher component to do the work::

    use Symfony\Component\Console\Application;
    use Symfony\Component\EventDispatcher\EventDispatcher;

    $application = new Application();
    $application->setDispatcher($dispatcher);
    $application->run();

The ``ConsoleEvents::COMMAND`` event
------------------------------------

**Typical Purposes**: Doing something before any command is run (like logging
which command is going to be executed), or displaying something about the event
to be executed.

Just before executing any command, the ``ConsoleEvents::COMMAND`` event is
dispatched. Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleCommandEvent` event::

    use Symfony\Component\Console\Event\ConsoleCommandEvent;
    use Symfony\Component\Console\ConsoleEvents;

    $dispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) {
        // get the input instance
        $input = $event->getInput();

        // get the output instance
        $output = $event->getOutput();

        // get the command to be executed
        $command = $event->getCommand();

        // write something about the command
        $output->writeln(sprintf('Before running command <info>%s</info>', $command->getName()));

        // get the application
        $application = $command->getApplication();
    });

The ``ConsoleEvents::TERMINATE`` event
--------------------------------------

**Typical Purposes**: To perform some cleanup actions after the command has
been executed.

After the command has been executed, the ``ConsoleEvents::TERMINATE`` event is
dispatched. It can be used to do any actions that need to be executed for all
commands or to cleanup what you initiated in the ``ConsoleEvents::COMMAND``
command (like sending logs, closing a database connection, sending emails,
...). A listener might also change the exit code.

Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleTerminateEvent` event::

    use Symfony\Component\Console\Event\ConsoleTerminateEvent;
    use Symfony\Component\Console\ConsoleEvents;

    $dispatcher->addListener(ConsoleEvents::TERMINATE, function (ConsoleTerminateEvent $event) {
        // get the output
        $output = $event->getOutput();

        // get the command that has been executed
        $command = $event->getCommand();

        // display something
        $output->writeln(sprintf('After running command <info>%s</info>', $command->getName()));

        // change the exit code
        $event->setExitCode(128);
    });

.. tip::

    This event is also dispatched when an exception is thrown by the command.
    It is then dispatched just before the ``ConsoleEvents::EXCEPTION`` event.
    The exit code received in this case is the exception code.

The ``ConsoleEvents::EXCEPTION`` event
--------------------------------------

**Typical Purposes**: Handle exceptions thrown during the execution of a
command.

Whenever an exception is thrown by a command, the ``ConsoleEvents::EXCEPTION``
command is dispatched. A listener can wrap or change the exception or do
anything useful before the exception is thrown by the application.

Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleForExceptionEvent` event::

    use Symfony\Component\Console\Event\ConsoleForExceptionEvent;
    use Symfony\Component\Console\ConsoleEvents;

    $dispatcher->addListener(ConsoleEvents::EXCEPTION, function (ConsoleForExceptionEvent $event) {
        $output = $event->getOutput();

        $output->writeln(sprintf('Oops, exception thrown while running command <info>%s</info>', $command->getName()));

        // get the current exit code (the exception code or the exit code set by a ConsoleEvents::TERMINATE event)
        $exitCode = $event->getExitCode();

        // change the exception to another one
        $event->setException(new \LogicException('Caught exception', $exitCode, $event->getException()));
    });
