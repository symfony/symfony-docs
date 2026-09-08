Console Commands
================

The Symfony framework provides lots of commands through the ``bin/console`` script
(e.g. the well-known ``bin/console cache:clear`` command). These commands are
created with the Console component. You can also use it to create your own
commands, both in Symfony applications and in any other PHP project.

Installation
------------

Symfony applications include the Console component by default. In other PHP
projects, install it with Composer:

.. code-block:: terminal

    $ composer require symfony/console

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

Shortcut Syntax
~~~~~~~~~~~~~~~

You don't have to type the full command names. You can type the shortest
unambiguous name to run a command. When command names use ``:`` to define
namespaces, you only need to type the shortest unambiguous text for each part:

.. code-block:: terminal

    # runs the "cache:clear" command
    $ php bin/console ca:cl

    # as long as it's unambiguous, you can also mix upper and lower case
    $ php bin/console Ca:Cl

If the short name is ambiguous (i.e. more than one command matches it), no
command is run and Symfony displays the matching commands so you can choose
one of them.

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

.. _console-registering-commands:

Registering the Command
~~~~~~~~~~~~~~~~~~~~~~~

In Symfony applications, commands are registered automatically. If you're using
the :ref:`default services.yaml configuration <service-container-services-load-example>`,
your command classes are already registered as services and Symfony finds them
thanks to the ``#[AsCommand]`` attribute and :ref:`autoconfiguration <services-autoconfigure>`.
If you can't use PHP attributes, register the command as a service and
:doc:`tag it </service_container/tags>` with the ``console.command`` tag.

.. deprecated:: 8.1

    Registering console commands by overriding the ``Bundle::registerCommands()``
    method is deprecated. Use the ``#[AsCommand]`` attribute or the
    ``console.command`` service tag instead.

.. _console-command-service-lazy-loading:

Commands are loaded lazily: their classes are only instantiated when the
command is run. This is possible because Symfony reads the command name from
the ``#[AsCommand]`` attribute without instantiating the class. When registering
commands manually with the ``console.command`` tag, set the command name in the
``command`` attribute of the tag to get the same behavior:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Command\CreateUserCommand:
                tags:
                    - { name: 'console.command', command: 'app:create-user' }

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Command\CreateUserCommand;

        return App::config([
            'services' => [
                CreateUserCommand::class => [
                    'tags' => [
                        ['console.command' => ['command' => 'app:create-user']],
                    ],
                ],
            ],
        ]);

.. warning::

    Running the ``list`` command instantiates all commands, including lazy ones.
    The only exception is commands defined as
    :doc:`LazyCommand instances </console/lazy_commands>`, whose command factory
    is not executed.

In standalone applications, create the console application in a PHP script and
register your commands with the
:method:`Symfony\\Component\\Console\\Application::addCommand` method::

    #!/usr/bin/env php
    <?php
    // application.php

    require __DIR__.'/vendor/autoload.php';

    use App\Command\CreateUserCommand;
    use Symfony\Component\Console\Application;

    // the name and version are optional; they are displayed when running "--version"
    $application = new Application('Acme Console Application', '1.2');

    $application->addCommand(new CreateUserCommand());
    // ... register other commands

    $application->run();

You can also define commands inline with a closure, without creating a class.
Use the :method:`Symfony\\Component\\Console\\Application::register` method
and pass the closure to ``setCode()``. The closure accepts the same
``#[Argument]`` and ``#[Option]`` parameters as the ``__invoke()`` method::

    // ...
    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Output\OutputInterface;

    $application->register('app:create-user')
        ->setCode(function (#[Argument] string $username, OutputInterface $output): int {
            // ...

            return Command::SUCCESS;
        });

Running the Command
~~~~~~~~~~~~~~~~~~~

After configuring and registering the command, you can run it in the terminal:

.. code-block:: terminal

    $ php bin/console app:create-user

    # in standalone applications, run the script that creates the application
    $ php application.php app:create-user

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

    #[AsCommand(
        name: 'app:create-user|app:add-user|app:new-user',
        description: 'Creates a new user.',
    )]
    class CreateUserCommand
    {
        // ...
    }

.. _console-hidden-commands:

Hiding Commands
~~~~~~~~~~~~~~~

By default, all commands are listed when running the ``list`` command or when
running the console application without arguments. However, some commands are
not intended to be run by end-users; for example, commands for the legacy parts
of the application or commands only run by scheduled tasks.

In those cases, define the command as **hidden** by setting the ``hidden``
option of the ``#[AsCommand]`` attribute to ``true``::

    // src/Command/LegacyCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;

    #[AsCommand(name: 'app:legacy', hidden: true)]
    class LegacyCommand
    {
        // ...
    }

You can also hide a command using the pipe (``|``) syntax of command aliases:
use the command name as one of the aliases and leave the main command name (the
part before the ``|``) empty::

    // src/Command/LegacyCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;

    #[AsCommand(name: '|app:legacy')]
    class LegacyCommand
    {
        // ...
    }

.. note::

    Hidden commands are still available using the JSON or XML descriptor.

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

.. note::

    Standalone applications don't have a service container, so pass the
    dependencies yourself when registering the command:
    ``$application->addCommand(new CreateUserCommand($userManager))``.

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
    using it (or when testing a :ref:`single-command application <console-single-command-application>`),
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

    If you are using a :ref:`single-command application <console-single-command-application>`,
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

.. _console-logger:

Using the Console Logger
------------------------

In Symfony applications, commands log messages by injecting the ``LoggerInterface``
service. :doc:`Monolog's console handler </logging/monolog_console>` then displays
those messages in the console output according to the verbosity level.

Standalone applications and commands that don't use Monolog can use the
:class:`Symfony\\Component\\Console\\Logger\\ConsoleLogger` class instead. It's a
lightweight logger that complies with the `PSR-3`_ standard and its only
dependency is ``psr/log``. Depending on the verbosity level, it sends the log
messages to the :class:`Symfony\\Component\\Console\\Output\\OutputInterface`
instance passed to its constructor. Consider a service that requires a PSR-3 logger::

    namespace App\Service;

    use Psr\Log\LoggerInterface;

    class DataImporter
    {
        public function __construct(
            private LoggerInterface $logger,
        ) {
        }

        public function import(string $file): void
        {
            $this->logger->info('Importing data from '.$file);

            // ...
        }
    }

Create a ``ConsoleLogger`` inside the command and pass it to the service::

    namespace App\Command;

    use App\Service\DataImporter;
    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Logger\ConsoleLogger;
    use Symfony\Component\Console\Output\OutputInterface;

    #[AsCommand(
        name: 'app:import-data',
        description: 'Imports data from the given file'
    )]
    class ImportDataCommand
    {
        public function __invoke(#[Argument] string $file, OutputInterface $output): int
        {
            $logger = new ConsoleLogger($output);

            $importer = new DataImporter($logger);
            $importer->import($file);

            return Command::SUCCESS;
        }
    }

The log messages emitted by the service are displayed in the console output.

Verbosity
~~~~~~~~~

Depending on the verbosity level that the command is run, messages may or
may not be sent to the :class:`Symfony\\Component\\Console\\Output\\OutputInterface`
instance.

By default, the console logger behaves like the
:doc:`Monolog's Console Handler </logging/monolog_console>`.
The association between the log level and the verbosity can be configured
through the second parameter of the :class:`Symfony\\Component\\Console\\Logger\\ConsoleLogger`
constructor::

    use Psr\Log\LogLevel;
    // ...

    $verbosityLevelMap = [
        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
    ];

    $logger = new ConsoleLogger($output, $verbosityLevelMap);

Color
~~~~~

The logger outputs the log messages formatted with a color reflecting their
level. This behavior is configurable through the third parameter of the
constructor::

    // ...
    $formatLevelMap = [
        LogLevel::CRITICAL => ConsoleLogger::ERROR,
        LogLevel::DEBUG    => ConsoleLogger::INFO,
    ];

    $logger = new ConsoleLogger($output, [], $formatLevelMap);

Errors
~~~~~~

The console logger includes a ``hasErrored()`` method which returns ``true`` as
soon as any error message has been logged during the execution of the command.
This is useful to decide which status code to return as the result of executing
the command.

.. _console-events:

Using Console Events
--------------------

The console application dispatches several events during the lifecycle of a
command. Listen to them to run some logic before or after any command, to handle
errors globally, etc. Console events are dispatched with the
:doc:`EventDispatcher component </event_dispatcher>`, so their listeners are
regular event listeners:

.. configuration-block::

    .. code-block:: php-symfony

        // src/EventListener/BeforeCommandListener.php
        namespace App\EventListener;

        use Symfony\Component\Console\ConsoleEvents;
        use Symfony\Component\Console\Event\ConsoleCommandEvent;
        use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

        // console events are dispatched with the names defined in the
        // ConsoleEvents class, so the "event" argument is required
        #[AsEventListener(event: ConsoleEvents::COMMAND)]
        class BeforeCommandListener
        {
            public function __invoke(ConsoleCommandEvent $event): void
            {
                // ...
            }
        }

    .. code-block:: php-standalone

        use App\EventListener\BeforeCommandListener;
        use Symfony\Component\Console\Application;
        use Symfony\Component\Console\ConsoleEvents;
        use Symfony\Component\EventDispatcher\EventDispatcher;

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(ConsoleEvents::COMMAND, new BeforeCommandListener());

        $application = new Application();
        $application->setDispatcher($dispatcher);
        // ...
        $application->run();

.. warning::

    Console events are only triggered by the main command being executed.
    Commands called by the main command will not trigger any event, unless
    run by the application itself, see :doc:`/console/calling_commands`.

The following sections explain each console event and show the ``__invoke()``
method of a listener for it.

The ``ConsoleEvents::COMMAND`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: Doing something before any command is run (like logging
which command is going to be executed), or displaying something about the event
to be executed.

Just before executing any command, the ``ConsoleEvents::COMMAND`` event is
dispatched. Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleCommandEvent` event::

    use Symfony\Component\Console\Event\ConsoleCommandEvent;
    // ...

    public function __invoke(ConsoleCommandEvent $event): void
    {
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
    }

.. note::

    The command binds the input again before running, so any argument or
    option value that you set in a listener with ``setArgument()`` or
    ``setOption()`` is discarded. If you need to customize the input, change
    the command definition instead. For example, add an option with
    ``$command->addOption()`` and the command will parse it when it runs.

Using the
:method:`Symfony\\Component\\Console\\Event\\ConsoleCommandEvent::disableCommand`
method, you can disable a command inside a listener. The application
will then *not* execute the command, but instead will return the code ``113``
(defined in ``ConsoleCommandEvent::RETURN_CODE_DISABLED``). This code is one
of the `reserved exit codes`_ for console commands that conform with the
C/C++ standard::

    use Symfony\Component\Console\Event\ConsoleCommandEvent;
    // ...

    public function __invoke(ConsoleCommandEvent $event): void
    {
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
    }

The ``ConsoleEvents::ERROR`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: Handle exceptions thrown during the execution of a
command.

Whenever an exception is thrown by a command, including those triggered from
event listeners, the ``ConsoleEvents::ERROR`` event is dispatched. A listener
can wrap or change the exception or do anything useful before the exception is
thrown by the application.

Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleErrorEvent` event::

    use Symfony\Component\Console\Event\ConsoleErrorEvent;
    // ...

    public function __invoke(ConsoleErrorEvent $event): void
    {
        $output = $event->getOutput();

        $command = $event->getCommand();

        $output->writeln(sprintf('Oops, exception thrown while running command <info>%s</info>', $command->getName()));

        // gets the current exit code (the exception code)
        $exitCode = $event->getExitCode();

        // changes the exception to another one
        $event->setError(new \LogicException('Caught exception', $exitCode, $event->getError()));
    }

.. _console-events-terminate:

The ``ConsoleEvents::TERMINATE`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: To perform some cleanup actions after the command has
been executed.

After the command has been executed, the ``ConsoleEvents::TERMINATE`` event is
dispatched. It can be used to do any actions that need to be executed for all
commands or to cleanup what you initiated in a ``ConsoleEvents::COMMAND``
listener (like sending logs, closing a database connection, sending emails,
...). A listener might also change the exit code.

Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleTerminateEvent` event::

    use Symfony\Component\Console\Event\ConsoleTerminateEvent;
    // ...

    public function __invoke(ConsoleTerminateEvent $event): void
    {
        // gets the output
        $output = $event->getOutput();

        // gets the command that has been executed
        $command = $event->getCommand();

        // displays the given content
        $output->writeln(sprintf('After running command <info>%s</info>', $command->getName()));

        // changes the exit code
        $event->setExitCode(128);
    }

.. tip::

    This event is also dispatched when an exception is thrown by the command.
    It is then dispatched just after the ``ConsoleEvents::ERROR`` event.
    The exit code received in this case is the exception code.

    Additionally, the event is dispatched when the command is being exited on
    a signal. You can learn more about signals in the
    :ref:`the dedicated section <console-signal-handling>`.

.. _console-events_signal:

The ``ConsoleEvents::SIGNAL`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: To perform some actions after the command execution was interrupted.

When a command is interrupted, Symfony dispatches the ``ConsoleEvents::SIGNAL``
event. Listen to this event so you can perform some actions (e.g. logging some
results, cleaning some temporary files, etc.) before finishing the command execution.

Listeners receive a
:class:`Symfony\\Component\\Console\\Event\\ConsoleSignalEvent` event::

    use Symfony\Component\Console\Event\ConsoleSignalEvent;
    // ...

    public function __invoke(ConsoleSignalEvent $event): void
    {
        // gets the signal number
        $signal = $event->getHandlingSignal();

        // sets the exit code
        $event->setExitCode(0);

        if (\SIGINT === $signal) {
            echo "bye bye!";
        }
    }

It is also possible to abort the exit if you want the command to continue its
execution even after the event has been dispatched, thanks to the
:method:`Symfony\\Component\\Console\\Event\\ConsoleSignalEvent::abortExit`
method::

    use Symfony\Component\Console\Event\ConsoleSignalEvent;
    // ...

    public function __invoke(ConsoleSignalEvent $event): void
    {
        $event->abortExit();
    }

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

.. _the-consoleevents-alarm-event:

Each time the ``SIGALRM`` signal fires, the application also dispatches a
:class:`Symfony\\Component\\Console\\Event\\ConsoleAlarmEvent`. Listen to it to
run the periodic logic outside the command (e.g. in a listener shared by several
long-running commands)::

    // src/EventListener/ConsoleAlarmListener.php
    namespace App\EventListener;

    use Symfony\Component\Console\Event\ConsoleAlarmEvent;
    use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

    #[AsEventListener]
    class ConsoleAlarmListener
    {
        public function __invoke(ConsoleAlarmEvent $event): void
        {
            // e.g. ping the database to keep the connection alive

            // keep the command running after handling the alarm
            $event->abortExit();
        }
    }

.. note::

    Unlike the other :ref:`console events <console-events>`, this event is
    dispatched using its class name, so the ``event`` argument of
    ``#[AsEventListener]`` is not needed. In standalone applications, register
    the listener with ``$dispatcher->addListener(ConsoleAlarmEvent::class, $listener)``.

.. note::

    The alarm feature requires the ``pcntl`` PHP extension and is not available
    on Windows.

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

.. tip::

    If you run the command in verbose mode (adding the ``-v`` option), Symfony
    will display in the output a clickable link to the command profile (if your
    terminal supports links). If you run it in debug verbosity (``-vvv``) you'll
    also see the time and memory consumed by the command.

Among the collected data, the performance panel displays the duration of each
:doc:`argument value resolver </console/value_resolver>` used during the command
execution, which helps spot slow resolvers.

.. versionadded:: 8.1

    The tracing of console argument value resolvers in the profiler's
    performance panel was introduced in Symfony 8.1.

.. warning::

    When profiling the ``messenger:consume`` command from the :doc:`Messenger </messenger>`
    component, add the ``--no-reset`` option to the command or you won't get any
    profile. Moreover, consider using the ``--limit`` option to only process a few
    messages to make the profile more readable in the profiler.

.. _console-single-command-application:

Building a Single-Command Application
-------------------------------------

When building a command line tool, you may not need to provide several commands.
In that case, having to pass the command name each time is tedious. Use the
:class:`Symfony\\Component\\Console\\SingleCommandApplication` class to create
an application that runs a single command, without having to pass its name::

    #!/usr/bin/env php
    <?php
    require __DIR__.'/vendor/autoload.php';

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\Option;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\SingleCommandApplication;

    new SingleCommandApplication()
        ->setName('My Super Command') // optional
        ->setVersion('1.0.0') // optional
        ->setCode(function (OutputInterface $output, #[Argument] string $directory = '.', #[Option] string $format = 'txt'): int {
            // output arguments and options

            return Command::SUCCESS;
        })
        ->run();

.. _console-default-command:

Changing the Default Command
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The console application runs the ``list`` command when no command name is
passed. To change the default command, pass the name of your command to the
:method:`Symfony\\Component\\Console\\Application::setDefaultCommand` method::

    // application.php
    use App\Command\HelloWorldCommand;
    use Symfony\Component\Console\Application;

    $application = new Application();
    $application->addCommand(new HelloWorldCommand());
    $application->setDefaultCommand('hello:world');
    $application->run();

Now, running ``php application.php`` without arguments runs the ``hello:world``
command.

.. warning::

    This feature has a limitation: you cannot pass any argument or option to
    the default command because they are ignored.

Pass ``true`` as the second argument of ``setDefaultCommand()`` to turn the
application into a single-command application, like the ones created with
``SingleCommandApplication``. The default command is then always run, without
having to pass its name, and it accepts arguments and options::

    $application->setDefaultCommand('hello:world', true);

Using a PSR Container
---------------------

.. versionadded:: 8.1

    The ``$container`` parameter of the ``Application`` class was introduced
    in Symfony 8.1.

The ``Application`` class accepts an optional third argument, a PSR-11
``ContainerInterface``. When provided, the application automatically wires
several services from the container:

* ``event_dispatcher``: sets the event dispatcher via ``setDispatcher()``
* ``console.argument_resolver``: sets the argument resolver via ``setArgumentResolver()``
* ``console.command_loader``: sets the command loader via ``setCommandLoader()``
* ``console.command.ids``: eagerly loads commands registered in the container
* ``services_resetter``: resets services after ``run()`` completes

When using Symfony's :class:`Symfony\\Component\\DependencyInjection\\ContainerInterface`,
the ``kernel.environment`` and ``kernel.debug`` parameters are also displayed
in the application's long version output.

This makes it possible to build console applications with dependency injection
without requiring HttpKernel or FrameworkBundle. Passing no container preserves
all existing behavior::

    #!/usr/bin/env php
    <?php

    use Symfony\Component\Console\Application;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    require __DIR__.'/vendor/autoload.php';

    $container = new ContainerBuilder();
    // ... register your services, commands, etc.
    $container->compile();

    $application = new Application('my-cli', '1.0', $container);
    $application->run();

.. _console-bundle:

Using the ConsoleBundle
-----------------------

.. versionadded:: 8.1

    The ``ConsoleBundle`` was introduced in Symfony 8.1.

Building the container by hand as shown above works, but the Console component
also provides a ``ConsoleBundle`` that brings service autodiscovery,
autoconfiguration and autowiring to console applications, still without
requiring HttpKernel or FrameworkBundle.

The bundle needs a kernel to build the container. Use the kernel provided by
the DependencyInjection component (read
:ref:`Building HTTP-less Applications <building-http-less-applications>` for
more details)::

    // src/Kernel.php
    namespace App;

    use Symfony\Component\DependencyInjection\Kernel\AbstractKernel;
    use Symfony\Component\DependencyInjection\Kernel\KernelTrait;

    class Kernel extends AbstractKernel
    {
        use KernelTrait;
    }

Then, enable the bundle::

    // config/bundles.php
    return [
        Symfony\Component\Console\ConsoleBundle::class => ['all' => true],
    ];

Finally, create the console executable. This example uses the
:doc:`Runtime component </components/runtime>`, but you can also boot the
kernel yourself::

    #!/usr/bin/env php
    <?php

    use App\Kernel;
    use Symfony\Component\Console\Application;

    require __DIR__.'/vendor/autoload_runtime.php';

    return function (array $context) {
        $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
        $kernel->boot();

        return new Application('my-cli', '1.0', $kernel->getContainer());
    };

The kernel follows the same conventions as full Symfony applications:
``config/bundles.php`` to enable bundles, ``config/packages/`` to configure
them and ``config/services.yaml`` (or ``config/services.php``) to register your
own services and commands.

On top of that, ``ConsoleBundle`` provides the following:

* autoconfiguration for commands: any service extending
  :class:`Symfony\\Component\\Console\\Command\\Command` or using the
  ``#[AsCommand]`` attribute is added to the application and loaded lazily
  through the command loader;
* autoconfiguration for the
  :doc:`argument value resolvers </console/value_resolver>`, together with all
  the built-in resolvers;
* a listener that logs command errors and non-zero exit codes, when the
  EventDispatcher component is installed;
* the ``dotenv:debug`` command, when the Dotenv component is installed.

``ConsoleBundle`` declares ``ServicesBundle`` as a
:ref:`required bundle <bundles-required-bundles>`, so you don't need to enable
it yourself. That bundle provides the core services used by the container, such
as ``parameter_bag``, ``event_dispatcher``, ``filesystem`` and ``clock``.

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

.. _console-helpers:

Console Helpers
~~~~~~~~~~~~~~~

The Console component also contains a set of "helpers": small tools that ease
common tasks such as asking questions to the user, displaying progress bars and
tables, formatting the output or running external processes:

.. toctree::
    :maxdepth: 1
    :glob:

    console/helpers/*

.. _`exit status`: https://en.wikipedia.org/wiki/Exit_status
.. _`Signals`: https://en.wikipedia.org/wiki/Signal_(IPC)
.. _`constants of the PCNTL PHP extension`: https://www.php.net/manual/en/pcntl.constants.php
.. _`PSR-3`: https://www.php-fig.org/psr/psr-3/
.. _`reserved exit codes`: https://www.tldp.org/LDP/abs/html/exitcodes.html
