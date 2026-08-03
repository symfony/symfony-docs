Console Commands
================

The Symfony framework provides lots of commands through the ``bin/console`` script
(e.g. the well-known ``bin/console cache:clear`` command). These commands are
created with the :doc:`Console component </components/console>`. You can also
use it to create your own commands.

Running Commands
----------------

Each Symfony application comes with a large set of commands. You can use
the ``list`` command to view all available commands in the application:

.. code-block:: terminal

    $ php bin/console list
    ...

    Available commands:
      about             Display information about the current project
      completion        Dump the shell completion script
      help              Display help for a command
      list              List commands
     assets
      assets:install    Install bundle's web assets under a public directory
     cache
      cache:clear       Clear the cache
    ...

.. note::

    ``list`` is the default command, so running ``php bin/console`` is the same.

If you find the command you need, you can run it with the ``--help`` option
to view the command's documentation:

.. code-block:: terminal

    $ php bin/console assets:install --help

.. note::

    ``--help`` is one of the built-in global options from the Console component,
    which are available for all commands, including those you can create.
    To learn more about them, you can read
    :ref:`this section <console-global-options>`.

APP_ENV & APP_DEBUG
~~~~~~~~~~~~~~~~~~~

Console commands run in the :ref:`environment <config-dot-env>` defined in the ``APP_ENV``
variable of the ``.env`` file, which is ``dev`` by default. It also reads the ``APP_DEBUG``
value to turn "debug" mode on or off (it defaults to ``1``, which is on).

To run the command in another environment or debug mode, edit the value of ``APP_ENV``
and ``APP_DEBUG``. You can also define these env vars when running the
command, for instance:

.. code-block:: terminal

    # clears the cache for the prod environment
    $ APP_ENV=prod php bin/console cache:clear

.. _console-completion-setup:

Console Completion
~~~~~~~~~~~~~~~~~~

If you are using the Bash, Zsh or Fish shell, you can install Symfony's
completion script to get auto completion when typing commands in the
terminal. All commands support name and option completion, and some can
even complete values.

.. image:: /_images/components/console/completion.gif
    :alt: The terminal completes the command name "secrets:remove" and the argument "SOME_OTHER_SECRET".

First, you have to install the completion script *once*. Run
``bin/console completion --help`` for the installation instructions for
your shell.

.. note::

    When using Bash, make sure you installed and setup the "bash completion"
    package for your OS (typically named ``bash-completion``).

After installing and restarting your terminal, you're all set to use
completion (by default, by pressing the Tab key).

.. tip::

    Many PHP tools are built using the Symfony Console component (e.g.
    Composer, PHPStan and Behat). If they are using version 5.4 or higher,
    you can also install their completion script to enable console completion:

    .. code-block:: terminal

        $ php vendor/bin/phpstan completion --help
        $ composer completion --help

.. tip::

    If you are using the :doc:`Symfony CLI </setup/symfony_cli>` tool, follow
    :ref:`these instructions <symfony-cli-autocompletion>` to enable autocompletion.

.. _console_creating-command:

Creating a Command
------------------

Commands are defined in classes and auto-registered using the ``#[AsCommand]``
attribute. For example, you may want a command to create a user::

    // src/Command/CreateUserCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;

    // the name of the command is what users type after "php bin/console"
    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand
    {
        public function __invoke(): int
        {
            // ... put here the code to create the user

            // this method must return an integer number with the "exit status code"
            // of the command. You can also use these constants to make code more readable

            // return this if there was no problem running the command
            // (it's equivalent to returning int(0))
            return Command::SUCCESS;

            // or return this if some error happened during the execution
            // (it's equivalent to returning int(1))
            // return Command::FAILURE;

            // or return this to indicate incorrect command usage; e.g. invalid options
            // or missing arguments (it's equivalent to returning int(2))
            // return Command::INVALID
        }
    }

If you can't use PHP attributes, register the command as a service and
:doc:`tag it </service_container/tags>` with the ``console.command`` tag. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.

.. deprecated:: 8.1

    Registering console commands by overriding the ``Bundle::registerCommands()``
    method is deprecated. Use the ``#[AsCommand]`` attribute or the
    ``console.command`` service tag instead.

You can also use ``#[AsCommand]`` to add a description, usage examples, and
longer help text for the command::

    #[AsCommand(
        name: 'app:create-user',
        // this short description is shown when running "php bin/console list"
        description: 'Creates a new user.',
        // this is shown when running the command with the "--help" option
        help: 'This command allows you to create a user...',
        // this allows you to show one or more usage examples (no need to add the command name)
        usages: ['bob', 'alice --as-admin'],
    )]
    class CreateUserCommand
    {
        public function __invoke(): int
        {
            // ...
        }
    }

Additionally, you can extend the :class:`Symfony\\Component\\Console\\Command\\Command` class to
leverage advanced features like lifecycle hooks (e.g. :method:`Symfony\\Component\\Console\\Command\\Command::initialize`
and :method:`Symfony\\Component\\Console\\Command\\Command::interact`)::

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand extends Command
    {
        public function initialize(InputInterface $input, OutputInterface $output): void
        {
            // ...
        }

        public function interact(InputInterface $input, OutputInterface $output): void
        {
            // ...
        }

        public function __invoke(): int
        {
            // ...
        }
    }

.. _console-method-based-commands:

Method-based Commands
~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.1

    Support for method-based console commands was introduced in Symfony 8.1.

Instead of creating one class per command, you can define multiple commands
in the same class by adding the ``#[AsCommand]`` attribute to individual
public methods. This is useful to group related commands that share the
same dependencies::

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Output\OutputInterface;

    class UserCommands
    {
        #[AsCommand('app:user:create')]
        public function create(OutputInterface $output): int
        {
            // ...

            return Command::SUCCESS;
        }

        #[AsCommand('app:user:delete')]
        public function delete(OutputInterface $output): int
        {
            // ...

            return Command::SUCCESS;
        }
    }

Each annotated method becomes an independent command that can be run, listed
and tested separately.

.. note::

    When using the Console component without the service container, you can
    register method-based commands manually::

        $commands = new UserCommands($userRepository);

        $application = new Application();
        // the "..." is PHP's first-class callable syntax, which turns each method into a callable
        $application->addCommand($commands->create(...));
        $application->addCommand($commands->delete(...));

Running the Command
~~~~~~~~~~~~~~~~~~~

After configuring and registering the command, you can run it in the terminal:

.. code-block:: terminal

    $ php bin/console app:create-user

As you might expect, this command will do nothing as you didn't write any logic
yet. Add your own logic inside the ``__invoke()`` method.

.. _command-aliases:

Command Aliases
~~~~~~~~~~~~~~~

You can define alternative names (aliases) for a command directly in its name
using a pipe (``|``) separator. The first name in the list becomes the actual
command name; the others are aliases that can also be used to run the command::

    // src/Command/CreateUserCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(
        name: 'app:create-user|app:add-user|app:new-user',
        description: 'Creates a new user.',
    )]
    class CreateUserCommand extends Command
    {
        // ...
    }

Console Output
--------------

The ``__invoke()`` method has access to the output stream to write messages to
the console::

    // ...
    public function __invoke(OutputInterface $output): int
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        // the value returned by someMethod() can be an iterator (https://php.net/iterator)
        // that generates and returns the messages with the 'yield' PHP keyword
        $output->writeln($this->someMethod());

        // outputs a message followed by a "\n"
        $output->writeln('Whoa!');

        // outputs a message without adding a "\n" at the end of the line
        $output->write('You are about to ');
        $output->write('create a user.');

        return Command::SUCCESS;
    }

Now, try executing the command:

.. code-block:: terminal

    $ php bin/console app:create-user
    User Creator
    ============

    Whoa!
    You are about to create a user.

.. _console-output-sections:

Output Sections
~~~~~~~~~~~~~~~

The regular console output can be divided into multiple independent regions
called "output sections". Create one or more of these sections when you need to
clear and overwrite the output information.

Sections are created with the
:method:`ConsoleOutput::section() <Symfony\\Component\\Console\\Output\\ConsoleOutput::section>`
method, which returns an instance of
:class:`Symfony\\Component\\Console\\Output\\ConsoleSectionOutput`::

    // ...
    use Symfony\Component\Console\Output\ConsoleOutputInterface;

    #[AsCommand(name: 'app:my-command')]
    class MyCommand
    {
        public function __invoke(OutputInterface $output): int
        {
            if (!$output instanceof ConsoleOutputInterface) {
                throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
            }

            $section1 = $output->section();
            $section2 = $output->section();

            $section1->writeln('Hello');
            $section2->writeln('World!');
            sleep(1);
            // Output displays "Hello\nWorld!\n"

            // overwrite() replaces all the existing section contents with the given content
            $section1->overwrite('Goodbye');
            sleep(1);
            // Output now displays "Goodbye\nWorld!\n"

            // clear() deletes all the section contents...
            $section2->clear();
            sleep(1);
            // Output now displays "Goodbye\n"

            // ...but you can also delete a given number of lines
            // (this example deletes the last two lines of the section)
            $section1->clear(2);
            sleep(1);
            // Output is now completely empty!

            // setting the max height of a section will make new lines replace the old ones
            $section1->setMaxHeight(2);
            $section1->writeln('Line1');
            $section1->writeln('Line2');
            $section1->writeln('Line3');

            return Command::SUCCESS;
        }
    }

.. note::

    A new line is appended automatically when displaying information in a section.

Output sections let you manipulate the Console output in advanced ways, such as
:ref:`displaying multiple progress bars <console-multiple-progress-bars>` which
are updated independently and :ref:`appending rows to tables <console-modify-rendered-tables>`
that have already been rendered.

.. warning::

    Terminals only allow overwriting the visible content, so you must take into
    account the console height when trying to write/overwrite section contents.

Console Input
-------------

Use input options or arguments to pass information to the command::

    use Symfony\Component\Console\Attribute\Argument;

    // The #[Argument] attribute configures $username as a
    // required input argument and its value is automatically
    // passed to this parameter
    public function __invoke(#[Argument('The username of the user.')] string $username, OutputInterface $output): int
    {
        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        $output->writeln('Username: '.$username);

        return Command::SUCCESS;
    }

Now, you can pass the username to the command:

.. code-block:: terminal

    $ php bin/console app:create-user Wouter
    User Creator
    ============

    Username: Wouter

.. seealso::

    Read :doc:`/console/input` for more information about console options and
    arguments.

Getting Services from the Service Container
-------------------------------------------

To actually create a new user, the command has to access some
:doc:`services </service_container>`. Since your command is already registered
as a service, you can use normal dependency injection. Imagine you have a
``App\Service\UserManager`` service that you want to access::

    // ...
    use App\Service\UserManager;
    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;

    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand
    {
        public function __construct(
            private UserManager $userManager
        ) {
        }

        public function __invoke(#[Argument] string $username, OutputInterface $output): int
        {
            // ...

            $this->userManager->create($username);

            $output->writeln('User successfully generated!');

            return Command::SUCCESS;
        }
    }

.. seealso::

    Read :doc:`/console/value_resolver` for more information about advanced
    service injection features (such as ``#[Autowire]``, ``#[Target]``, and
    custom value resolvers).

Command Lifecycle
-----------------

Commands have three lifecycle methods that are invoked when running the
command:

:method:`Symfony\\Component\\Console\\Command\\Command::initialize` *(optional)*
    This method is executed before the ``interact()`` and the ``execute()``
    methods. Its main purpose is to initialize variables used in the rest of
    the command methods.

:method:`Symfony\\Component\\Console\\Command\\Command::interact` *(optional)*
    This method is executed after ``initialize()`` and before ``execute()``.
    Its purpose is to check if some of the options/arguments are missing
    and interactively ask the user for those values. This is the last place
    where you can ask for missing required options/arguments. This method is
    called before validating the input.
    Note that it will not be called when the command is run without interaction
    (e.g. when passing the ``--no-interaction`` global option flag).

``__invoke()`` (or :method:`Symfony\\Component\\Console\\Command\\Command::execute`) *(required)*
    This method is executed after ``interact()`` and ``initialize()``.
    It contains the logic you want the command to execute and it must
    return an integer which will be used as the command `exit status`_.

.. _console-testing-commands:

Testing Commands
----------------

In test classes extending :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase`,
Symfony provides the ``runCommand()`` method to run console commands and inspect
their results. Some assertions are also provided by the :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\ConsoleCommandAssertionsTrait`
to check the command execution status::

    // tests/Command/CreateUserCommandTest.php
    namespace App\Tests\Command;

    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

    class CreateUserCommandTest extends KernelTestCase
    {
        public function testExecute(): void
        {
            $result = static::runCommand('app:create-user', [
                // pass arguments to the helper
                'username' => 'Wouter',

                // prefix the key with two dashes when passing options,
                // e.g: '--some-option' => 'option_value',
                // use brackets for testing array value,
                // e.g: '--some-option' => ['option_value'],
                // use true for options that accept no value (InputOption::VALUE_NONE),
                // e.g: '--some-option' => true,
            ]);

            $this->assertCommandIsSuccessful($result);

            // you can also check for a failed or invalid command:
            // $this->assertCommandFailed($result);
            // $this->assertCommandIsInvalid($result);

            // the output of the command in the console
            $output = $result->getOutput();
            $this->assertStringContainsString('Username: Wouter', $output);

            // ...
        }
    }

.. versionadded:: 8.1

    The ``runCommand()`` method and the ``ExecutionResult`` class were
    introduced in Symfony 8.1.

The ``ExecutionResult`` object gives access to stdout, stderr and the combined
display separately::

    // stdout only
    $result->getOutput();

    // stderr only
    $result->getErrorOutput();

    // the combined output (stdout + stderr interleaved)
    $result->getDisplay();

    // the exit code
    $result->statusCode;

You can also assert multiple expectations at once using ``assertCommandResultEquals()``::

    $this->assertCommandResultEquals(
        $result,
        expectedStatusCode: 0,
        expectedOutput: 'User "Wouter" was created.',
    );

If your command requires interactive inputs, pass them as the third argument::

    $result = static::runCommand('app:create-user', [], ['Wouter', 'yes']);

.. tip::

    You can also test a whole console application by using
    :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester`. When
    using it (or when testing a :doc:`single-command application </components/console/single_command_tool>`),
    disable the auto exit flag::

        $application = new Application();
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

.. warning::

    When testing commands using the ``CommandTester`` class, console events are
    not dispatched. If you need to test those events, use the
    :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester` instead.

.. note::

    When using the Console component in a standalone project (without the
    Symfony framework), extend ``\PHPUnit\Framework\TestCase`` instead of
    ``KernelTestCase`` and use
    :class:`Symfony\\Component\\Console\\Application` instead of the
    FrameworkBundle one.

Testing Console Applications
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to testing single commands, you can test full console applications
with the :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester` class::

    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
    use Symfony\Component\Console\Tester\ApplicationTester;

    class WelcomeCommandTest extends KernelTestCase
    {
        public function testPerson(): void
        {
            self::bootKernel();
            $application = new Application(self::$kernel);
            // this is key: don't terminate the PHP process after running the command
            $application->setAutoExit(false);

            $applicationTester = new ApplicationTester($application);
            $applicationTester->run([
                'command' => 'app:welcome-person',
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'hobbies' => ['reading', 'dancing']
            ]);

            $applicationTester->assertCommandIsSuccessful();

            $output = $applicationTester->getDisplay();
            $this->assertStringContainsString('Jane Smith', $output);
            $this->assertStringContainsString('reading and dancing', $output);
        }
    }

.. note::

    Don't forget to call ``setAutoExit(false)`` on the application before passing
    it to ``ApplicationTester``. Without it, the application calls ``exit()``
    after running the command, which would terminate the PHPUnit process.

Legacy Command Tester
~~~~~~~~~~~~~~~~~~~~~

Using the ``runCommand()`` method from ``KernelTestCase`` is the recommended way
of testing commands in Symfony applications. However, you can also use the
:class:`Symfony\\Component\\Console\\Tester\\CommandTester` class directly,
which is useful when testing commands outside the Symfony framework or when
you need lower-level control over the testing setup::

    // tests/Command/CreateUserCommandTest.php
    namespace App\Tests\Command;

    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
    use Symfony\Component\Console\Tester\CommandTester;

    class CreateUserCommandTest extends KernelTestCase
    {
        public function testExecute(): void
        {
            self::bootKernel();
            $application = new Application(self::$kernel);

            $command = $application->find('app:create-user');
            $commandTester = new CommandTester($command);
            $commandTester->execute([
                // pass arguments to the helper
                'username' => 'Wouter',

                // prefix the key with two dashes when passing options,
                // e.g: '--some-option' => 'option_value',
                // use brackets for testing array value,
                // e.g: '--some-option' => ['option_value'],
                // use true for options that accept no value (InputOption::VALUE_NONE),
                // e.g: '--some-option' => true,
            ]);

            $commandTester->assertCommandIsSuccessful();

            // the output of the command in the console
            $output = $commandTester->getDisplay();
            $this->assertStringContainsString('Username: Wouter', $output);

            // ...
        }
    }

.. note::

    If you are using a :doc:`single-command application </components/console/single_command_tool>`,
    call ``setAutoExit(false)`` on the application to get the command result in ``CommandTester``.

.. tip::

    Use PHP's first-class callable syntax to test
    :ref:`method-based commands <console-method-based-commands>`::

        $commands = new UserCommands($userRepository);

        $tester = new CommandTester($commands->create(...));
        $tester->execute([]);

.. warning::

    When testing ``InputOption::VALUE_NONE`` command options, you must pass ``true``
    to them::

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--some-option' => true]);

.. note::

    The ``CommandTester`` class does not implement ``ConsoleOutputInterface``,
    so methods like ``section()`` are not directly accessible. To test them,
    use the ``capture_stderr_separately`` option of the ``execute()`` method::

        $commandTester->execute([], ['capture_stderr_separately' => true]);

Getting Terminal Information
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When testing your commands, it could be useful to understand how your command
reacts on different settings like the width and the height of the terminal, or
even the color mode being used. You have access to such information thanks to the
:class:`Symfony\\Component\\Console\\Terminal` class::

    use Symfony\Component\Console\Terminal;

    $terminal = new Terminal();

    // gets the number of lines available
    $height = $terminal->getHeight();

    // gets the number of columns available
    $width = $terminal->getWidth();

    // gets the color mode
    $colorMode = $terminal->getColorMode();

    // changes the color mode
    $colorMode = $terminal->setColorMode(AnsiColorMode::Ansi24);

Logging Command Errors
----------------------

Whenever an exception is thrown while running commands, Symfony adds a log
message for it including the entire failing command. In addition, Symfony
registers an :doc:`event subscriber </event_dispatcher>` to listen to the
:ref:`ConsoleEvents::TERMINATE event <console-events-terminate>` and adds a log
message whenever a command doesn't finish with the ``0`` `exit status`_.

Using Console Events
--------------------

When a command is running, many events are dispatched, one of them allows you to
react to signals, read more in :doc:`this section </components/console/events>`.

.. _console-signal-handling:

Handling Signals
----------------

`Signals`_ are asynchronous notifications sent to a process in order to notify
it of an event that occurred. For example, when you press ``Ctrl + C`` in a
command, the operating system sends the ``SIGINT`` signal to it.

.. tip::

    All the available signals (``SIGINT``, ``SIGQUIT``, etc.) are defined as
    `constants of the PCNTL PHP extension`_. The extension has to be installed
    for these constants to be available.

In a Symfony application, a command can handle these signals by implementing the
:class:`Symfony\\Component\\Console\\Command\\SignalableCommandInterface` and
subscribing to one or more signals::

    // src/Command/MyCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\SignalableCommandInterface;

    #[AsCommand(name: 'app:my-command')]
    class MyCommand implements SignalableCommandInterface
    {
        // ...

        public function getSubscribedSignals(): array
        {
            // return here any of the constants defined by PCNTL extension
            return [\SIGINT, \SIGTERM];
        }

        public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
        {
            if (\SIGINT === $signal) {
                // ...
            }

            // ...

            // return an integer to set the exit code, or
            // false to continue normal execution
            return 0;
        }
    }

This registers a signal handler for that command only. To run the same handler
for all commands, use :ref:`events <console-events_signal>` instead.

You can also use :method:`Symfony\\Component\\Console\\Application::setAlarmInterval`
to trigger a recurring alarm interval, causing the OS to deliver a ``SIGALRM`` signal
at the given interval (in seconds). Use these signals to perform periodic tasks during
long-running commands::

    // src/Command/LongRunningCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Command\SignalableCommandInterface;

    #[AsCommand(name: 'app:long-running')]
    class LongRunningCommand implements SignalableCommandInterface
    {
        public function __invoke(Application $application): int
        {
            // trigger an alarm every 10 seconds
            $application->setAlarmInterval(10);

            // long-running processing...

            return Command::SUCCESS;
        }

        public function getSubscribedSignals(): array
        {
            return [\SIGALRM];
        }

        public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
        {
            // e.g. ping the database to keep the connection alive

            return false;
        }
    }

Symfony doesn't handle any signal received by the command (not even ``SIGKILL``,
``SIGTERM``, etc). This behavior is intended, as it gives you the flexibility to
handle all signals e.g. to do some tasks before terminating the command.

.. tip::

    If you need to fetch the signal name from its integer value (e.g. for logging),
    you can use the
    :method:`Symfony\\Component\\Console\\SignalRegistry\\SignalMap::getSignalName`
    method.

Profiling Commands
------------------

Symfony allows you to profile the execution of any command, including yours. First,
make sure that the :ref:`debug mode <debug-mode>` and the :doc:`profiler </profiler>`
are enabled. Then, add the ``--profile`` option when running the command:

.. code-block:: terminal

    $ php bin/console --profile app:my-command

Symfony will now collect data about the command execution, which is helpful to
debug errors or check other issues. When the command execution is over, the
profile is accessible through the web page of the profiler.

Among the collected data, the performance panel displays the duration of each
:doc:`argument value resolver </console/value_resolver>` used during the command
execution, which helps spot slow resolvers.

.. versionadded:: 8.1

    The tracing of console argument value resolvers in the profiler's
    performance panel was introduced in Symfony 8.1.

.. tip::

    If you run the command in verbose mode (adding the ``-v`` option), Symfony
    will display in the output a clickable link to the command profile (if your
    terminal supports links). If you run it in debug verbosity (``-vvv``) you'll
    also see the time and memory consumed by the command.

.. warning::

    When profiling the ``messenger:consume`` command from the :doc:`Messenger </messenger>`
    component, add the ``--no-reset`` option to the command or you won't get any
    profile. Moreover, consider using the ``--limit`` option to only process a few
    messages to make the profile more readable in the profiler.

Legacy Syntax to Define Commands
--------------------------------

Instead of using invokable commands, you can also define commands by extending
the :class:`Symfony\\Component\\Console\\Command\\Command` class. Both syntaxes
are supported, but invokable commands are recommended::

    // src/Command/CreateUserCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    // the name of the command is what users type after "php bin/console"
    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand extends Command
    {
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            // ... put here the code to create the user
        }
    }

You can optionally define a description, help message and the input options and
arguments by overriding the ``configure()`` method::

    // src/Command/CreateUserCommand.php

    // ...
    class CreateUserCommand extends Command
    {
        // ...
        protected function configure(): void
        {
            $this
                // the command description shown when running "php bin/console list"
                ->setDescription('Creates a new user.')
                // the command help shown when running the command with the "--help" option
                ->setHelp('This command allows you to create a user...')
                // add an argument required to your command
                ->addArgument('username', InputArgument::REQUIRED, 'How the user should be named?')
            ;
        }
    }

.. tip::

    Using the ``#[AsCommand]`` attribute to define a description instead of
    the ``setDescription()`` method retrieves the command description without
    instantiating its class, which makes the ``php bin/console list`` command
    run much faster.

The ``configure()`` method is called automatically at the end of the command
constructor. If your command defines its own constructor, set the properties
first and then call to the parent constructor, to make those properties
available in the ``configure()`` method::

    // src/Command/CreateUserCommand.php

    // ...
    class CreateUserCommand extends Command
    {
        // ...

        public function __construct(bool $requirePassword = false)
        {
            // best practices recommend to call the parent constructor first and
            // then set your own properties. That wouldn't work in this case
            // because configure() needs the properties set in this constructor
            $this->requirePassword = $requirePassword;

            parent::__construct();
        }

        protected function configure(): void
        {
            $this
                // ...
                ->addArgument('password', $this->requirePassword ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'User password')
            ;
        }
    }

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    console/*

The console component also contains a set of "helpers" - different small
tools capable of helping you with different tasks:

* :doc:`/components/console/helpers/questionhelper`: interactively ask the user for information
* :doc:`/components/console/helpers/formatterhelper`: customize the output colorization
* :doc:`/components/console/helpers/progressbar`: shows a progress bar
* :doc:`/components/console/helpers/progressindicator`: shows a progress indicator
* :doc:`/components/console/helpers/table`: displays tabular data as a table
* :doc:`/components/console/helpers/debug_formatter`: provides functions to
  output debug information when running an external program
* :doc:`/components/console/helpers/processhelper`: allows you to run processes using ``DebugFormatterHelper``
* :doc:`/components/console/helpers/cursor`: allows you to manipulate the cursor in the terminal
* :doc:`/components/console/helpers/tree`: displays tree-like structures

.. _`exit status`: https://en.wikipedia.org/wiki/Exit_status
.. _`Signals`: https://en.wikipedia.org/wiki/Signal_(IPC)
.. _`constants of the PCNTL PHP extension`: https://www.php.net/manual/en/pcntl.constants.php
