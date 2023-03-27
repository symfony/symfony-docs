Using Events
============

The Application class of the Console component allows you to optionally hook
into the lifecycle of a console application via events. Instead of reinventing
the wheel, it uses the Symfony EventDispatcher component to do the work::

    use Symfony\Component\Console\Application;
    use Symfony\Component\EventDispatcher\EventDispatcher;

    $dispatcher = new EventDispatcher();

    $application = new Application();
    $application->setDispatcher($dispatcher);
    $application->run();

.. caution::

    Console events are only triggered by the main command being executed.
    Commands called by the main command will not trigger any event.

The ``ConsoleEvents::COMMAND`` Event
------------------------------------

**Typical Purposes**: Doing something before any command is run (like logging
which command is going to be executed), or displaying something about the event
to be executed.

Just before executing any command, the ``ConsoleEvents::COMMAND`` event is
dispatched. Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleCommandEvent` event::

    use Symfony\Component\Console\ConsoleEvents;
    use Symfony\Component\Console\Event\ConsoleCommandEvent;

    $dispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) {
        // gets the input instance
        $input = $event->getInput();

        // gets the output instance
        $output = $event->getOutput();

        // gets the command to be executed
        $command = $event->getCommand();

        // writes something about the command
        $output->writeln(sprintf('Before running command <info>%s</info>', $command->getName()));

        // gets the application
        $application = $command->getApplication();
    });

Disable Commands inside Listeners
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using the
:method:`Symfony\\Component\\Console\\Event\\ConsoleCommandEvent::disableCommand`
method, you can disable a command inside a listener. The application
will then *not* execute the command, but instead will return the code ``113``
(defined in ``ConsoleCommandEvent::RETURN_CODE_DISABLED``). This code is one
of the `reserved exit codes`_ for console commands that conform with the
C/C++ standard::

    use Symfony\Component\Console\ConsoleEvents;
    use Symfony\Component\Console\Event\ConsoleCommandEvent;

    $dispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) {
        // gets the command to be executed
        $command = $event->getCommand();

        // ... check if the command can be executed

        // disables the command, this will result in the command being skipped
        // and code 113 being returned from the Application
        $event->disableCommand();

        // it is possible to enable the command in a later listener
        if (!$event->commandShouldRun()) {
            $event->enableCommand();
        }
    });

The ``ConsoleEvents::ERROR`` Event
----------------------------------

**Typical Purposes**: Handle exceptions thrown during the execution of a
command.

Whenever an exception is thrown by a command, including those triggered from
event listeners, the ``ConsoleEvents::ERROR`` event is dispatched. A listener
can wrap or change the exception or do anything useful before the exception is
thrown by the application.

Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleErrorEvent` event::

    use Symfony\Component\Console\ConsoleEvents;
    use Symfony\Component\Console\Event\ConsoleErrorEvent;

    $dispatcher->addListener(ConsoleEvents::ERROR, function (ConsoleErrorEvent $event) {
        $output = $event->getOutput();

        $command = $event->getCommand();

        $output->writeln(sprintf('Oops, exception thrown while running command <info>%s</info>', $command->getName()));

        // gets the current exit code (the exception code)
        $exitCode = $event->getExitCode();

        // changes the exception to another one
        $event->setError(new \LogicException('Caught exception', $exitCode, $event->getError()));
    });

.. _console-events-terminate:

The ``ConsoleEvents::TERMINATE`` Event
--------------------------------------

**Typical Purposes**: To perform some cleanup actions after the command has
been executed.

After the command has been executed, the ``ConsoleEvents::TERMINATE`` event is
dispatched. It can be used to do any actions that need to be executed for all
commands or to cleanup what you initiated in a ``ConsoleEvents::COMMAND``
listener (like sending logs, closing a database connection, sending emails,
...). A listener might also change the exit code.

Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleTerminateEvent` event::

    use Symfony\Component\Console\ConsoleEvents;
    use Symfony\Component\Console\Event\ConsoleTerminateEvent;

    $dispatcher->addListener(ConsoleEvents::TERMINATE, function (ConsoleTerminateEvent $event) {
        // gets the output
        $output = $event->getOutput();

        // gets the command that has been executed
        $command = $event->getCommand();

        // displays the given content
        $output->writeln(sprintf('After running command <info>%s</info>', $command->getName()));

        // changes the exit code
        $event->setExitCode(128);
    });

.. tip::

    This event is also dispatched when an exception is thrown by the command.
    It is then dispatched just after the ``ConsoleEvents::ERROR`` event.
    The exit code received in this case is the exception code.

The ``ConsoleEvents::SIGNAL`` Event
-----------------------------------

**Typical Purposes**: To perform some actions after the command execution was interrupted.

`Signals`_ are asynchronous notifications sent to a process in order to notify
it of an event that occurred. For example, when you press ``Ctrl + C`` in a
command, the operating system sends the ``SIGINT`` signal to it.

When a command is interrupted, Symfony dispatches the ``ConsoleEvents::SIGNAL``
event. Listen to this event so you can perform some actions (e.g. logging some
results, cleaning some temporary files, etc.) before finishing the command execution.

Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleSignalEvent` event::

    use Symfony\Component\Console\ConsoleEvents;
    use Symfony\Component\Console\Event\ConsoleSignalEvent;

    $dispatcher->addListener(ConsoleEvents::SIGNAL, function (ConsoleSignalEvent $event) {
       
        // gets the signal number
        $signal = $event->getHandlingSignal();
        
        if (\SIGINT === $signal) {
            echo "bye bye!";
        }
    });

.. tip::

    All the available signals (``SIGINT``, ``SIGQUIT``, etc.) are defined as
    `constants of the PCNTL PHP extension`_.

If you use the Console component inside a Symfony application, commands can
handle signals themselves. To do so, implement the
:class:`Symfony\\Component\\Console\\Command\\SignalableCommandInterface` and subscribe to one or more signals::

    // src/Command/SomeCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Command\SignalableCommandInterface;

    class SomeCommand extends Command implements SignalableCommandInterface
    {
        // ...

        public function getSubscribedSignals(): array
        {
            // return here any of the constants defined by PCNTL extension
            return [\SIGINT, \SIGTERM];
        }

        public function handleSignal(int $signal): void
        {
            if (\SIGINT === $signal) {
                // ...
            }

            // ...
        }
    }

.. versionadded:: 5.2

    The ``ConsoleSignalEvent`` and ``SignalableCommandInterface`` classes were
    introduced in Symfony 5.2.

.. _`reserved exit codes`: https://www.tldp.org/LDP/abs/html/exitcodes.html
.. _`Signals`: https://en.wikipedia.org/wiki/Signal_(IPC)
.. _`constants of the PCNTL PHP extension`: https://www.php.net/manual/en/pcntl.constants.php
