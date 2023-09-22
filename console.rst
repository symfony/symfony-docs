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
      about                                      Display information about the current project
      completion                                 Dump the shell completion script
      help                                       Display help for a command
      list                                       List commands
     assets
      assets:install                             Install bundle's web assets under a public directory
     cache
      cache:clear                                Clear the cache
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
and ``APP_DEBUG``. You can also define this env vars when running the
command, for instance:

.. code-block:: terminal

    # clears the cache for the prod environment
    $ APP_ENV=prod php bin/console cache:clear

.. _console-completion-setup:

Console Completion
~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.4

    Console completion for Bash was introduced in Symfony 5.4.

If you are using the Bash shell, you can install Symfony's completion
script to get auto completion when typing commands in the terminal. All
commands support name and option completion, and some can even complete
values.

.. image:: /_images/components/console/completion.gif
    :alt: The terminal completes the command name "secrets:remove" and the argument "SOME_OTHER_SECRET".

First, make sure you installed and setup the "bash completion" package for
your OS (typically named ``bash-completion``). Then, install the Symfony
completion bash script *once* by running the ``completion`` command in a
Symfony app installed on your computer:

.. code-block:: terminal

    $ php bin/console completion bash | sudo tee /etc/bash_completion.d/console-events-terminate
    # after the installation, restart the shell

Now you are all set to use the auto completion for all Symfony Console
applications on your computer. By default, you can get a list of complete
options by pressing the Tab key.

.. tip::

    Many PHP tools are built using the Symfony Console component (e.g.
    Composer, PHPstan and Behat). If they are using version 5.4 or higher,
    you can also install their completion script to enable console completion:

    .. code-block:: terminal

        $ php vendor/bin/phpstan completion bash | sudo tee /etc/bash_completion.d/phpstan

Creating a Command
------------------

Commands are defined in classes extending
:class:`Symfony\\Component\\Console\\Command\\Command`. For example, you may
want a command to create a user::

    // src/Command/CreateUserCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class CreateUserCommand extends Command
    {
        // the name of the command (the part after "bin/console")
        protected static $defaultName = 'app:create-user';

        protected function execute(InputInterface $input, OutputInterface $output): int
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

.. versionadded:: 5.1

    The ``Command::SUCCESS`` and ``Command::FAILURE`` constants were introduced
    in Symfony 5.1.

.. versionadded:: 5.3

    The ``Command::INVALID`` constant was introduced in Symfony 5.3

Configuring the Command
~~~~~~~~~~~~~~~~~~~~~~~

You can optionally define a description, help message and the
:doc:`input options and arguments </console/input>` by overriding the
``configure()`` method::

    // src/Command/CreateUserCommand.php

    // ...
    class CreateUserCommand extends Command
    {
        // the command description shown when running "php bin/console list"
        protected static $defaultDescription = 'Creates a new user.';

        // ...
        protected function configure(): void
        {
            $this
                // the command help shown when running the command with the "--help" option
                ->setHelp('This command allows you to create a user...')
            ;
        }
    }

.. tip::

    Defining the ``$defaultDescription`` static property instead of using the
    ``setDescription()`` method allows to get the command description without
    instantiating its class. This makes the ``php bin/console list`` command run
    much faster.

    If you want to always run the ``list`` command fast, add the ``--short`` option
    to it (``php bin/console list --short``). This will avoid instantiating command
    classes, but it won't show any description for commands that use the
    ``setDescription()`` method instead of the static property.

.. versionadded:: 5.3

    The ``$defaultDescription`` static property and the ``--short`` option
    were introduced in Symfony 5.3.

The ``configure()`` method is called automatically at the end of the command
constructor. If your command defines its own constructor, set the properties
first and then call to the parent constructor, to make those properties
available in the ``configure()`` method::

    // ...
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;

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

.. _console_registering-the-command:

Registering the Command
~~~~~~~~~~~~~~~~~~~~~~~

In PHP 8 and newer versions, you can register the command by adding the
``AsCommand`` attribute to it::

    // src/Command/CreateUserCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;

    // the "name" and "description" arguments of AsCommand replace the
    // static $defaultName and $defaultDescription properties
    #[AsCommand(
        name: 'app:create-user',
        description: 'Creates a new user.',
        hidden: false,
        aliases: ['app:add-user']
    )]
    class CreateUserCommand extends Command
    {
        // ...
    }

.. versionadded:: 5.3

    The ability to use PHP attributes to configure commands was introduced in
    Symfony 5.3.

If you can't use PHP attributes, register the command as a service and
:doc:`tag it </service_container/tags>` with the ``console.command`` tag. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.

Running the Command
~~~~~~~~~~~~~~~~~~~

After configuring and registering the command, you can run it in the terminal:

.. code-block:: terminal

    $ php bin/console app:create-user

As you might expect, this command will do nothing as you didn't write any logic
yet. Add your own logic inside the ``execute()`` method.

Console Output
--------------

The ``execute()`` method has access to the output stream to write messages to
the console::

    // ...
    protected function execute(InputInterface $input, OutputInterface $output): int
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

    class MyCommand extends Command
    {
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            if (!$output instanceof ConsoleOutputInterface) {
                throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
            }

            $section1 = $output->section();
            $section2 = $output->section();

            $section1->writeln('Hello');
            $section2->writeln('World!');
            // Output displays "Hello\nWorld!\n"

            // overwrite() replaces all the existing section contents with the given content
            $section1->overwrite('Goodbye');
            // Output now displays "Goodbye\nWorld!\n"

            // clear() deletes all the section contents...
            $section2->clear();
            // Output now displays "Goodbye\n"

            // ...but you can also delete a given number of lines
            // (this example deletes the last two lines of the section)
            $section1->clear(2);
            // Output is now completely empty!

            return Command::SUCCESS;
        }
    }

.. note::

    A new line is appended automatically when displaying information in a section.

Output sections let you manipulate the Console output in advanced ways, such as
:ref:`displaying multiple progress bars <console-multiple-progress-bars>` which
are updated independently and :ref:`appending rows to tables <console-modify-rendered-tables>`
that have already been rendered.

.. caution::

    Terminals only allow overwriting the visible content, so you must take into
    account the console height when trying to write/overwrite section contents.

Console Input
-------------

Use input options or arguments to pass information to the command::

    use Symfony\Component\Console\Input\InputArgument;

    // ...
    protected function configure(): void
    {
        $this
            // configure an argument
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
            // ...
        ;
    }

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        // retrieve the argument value using getArgument()
        $output->writeln('Username: '.$input->getArgument('username'));

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
    use Symfony\Component\Console\Command\Command;

    class CreateUserCommand extends Command
    {
        private $userManager;

        public function __construct(UserManager $userManager)
        {
            $this->userManager = $userManager;

            parent::__construct();
        }

        // ...

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            // ...

            $this->userManager->create($input->getArgument('username'));

            $output->writeln('User successfully generated!');

            return Command::SUCCESS;
        }
    }

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

:method:`Symfony\\Component\\Console\\Command\\Command::execute` *(required)*
    This method is executed after ``interact()`` and ``initialize()``.
    It contains the logic you want the command to execute and it must
    return an integer which will be used as the command `exit status`_.

.. _console-testing-commands:

Testing Commands
----------------

Symfony provides several tools to help you test your commands. The most
useful one is the :class:`Symfony\\Component\\Console\\Tester\\CommandTester`
class. It uses special input and output classes to ease testing without a real
console::

    // tests/Command/CreateUserCommandTest.php
    namespace App\Tests\Command;

    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
    use Symfony\Component\Console\Tester\CommandTester;

    class CreateUserCommandTest extends KernelTestCase
    {
        public function testExecute()
        {
            $kernel = self::bootKernel();
            $application = new Application($kernel);

            $command = $application->find('app:create-user');
            $commandTester = new CommandTester($command);
            $commandTester->execute([
                // pass arguments to the helper
                'username' => 'Wouter',

                // prefix the key with two dashes when passing options,
                // e.g: '--some-option' => 'option_value',
                // use brackets for testing array value,
                // e.g: '--some-option' => ['option_value'],
            ]);

            $commandTester->assertCommandIsSuccessful();

            // the output of the command in the console
            $output = $commandTester->getDisplay();
            $this->assertStringContainsString('Username: Wouter', $output);

            // ...
        }
    }

If you are using a :doc:`single-command application </components/console/single_command_tool>`,
call ``setAutoExit(false)`` on it to get the command result in ``CommandTester``.

.. versionadded:: 5.2

    The ``setAutoExit()`` method for single-command applications was introduced
    in Symfony 5.2.

.. versionadded:: 5.4

    The ``assertCommandIsSuccessful()`` method was introduced in Symfony 5.4.

.. tip::

    You can also test a whole console application by using
    :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester`.

.. caution::

    When testing commands using the ``CommandTester`` class, console events are
    not dispatched. If you need to test those events, use the
    :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester` instead.

.. caution::

    When testing commands using the :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester`
    class, don't forget to disable the auto exit flag::

        $application = new Application();
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);


.. caution::

    When testing ``InputOption::VALUE_NONE`` command options, you must pass an
    empty value to them::

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--some-option' => '']);

.. note::

    When using the Console component in a standalone project, use
    :class:`Symfony\\Component\\Console\\Application`
    and extend the normal ``\PHPUnit\Framework\TestCase``.

When testing your commands, it could be useful to understand how your command
reacts on different settings like the width and the height of the terminal.
Thanks to the :class:`Symfony\\Component\\Console\\Terminal` class, you can
have access to such information::

    use Symfony\Component\Console\Terminal;

    $terminal = new Terminal();

    // access to number of lines available
    $height = $terminal->getHeight();

    // access to number of columns available
    $width = $terminal->getWidth();

By default, the ``Terminal`` class uses the values of the
``LINES`` and ``COLUMNS`` system environment variables.

Logging Command Errors
----------------------

Whenever an exception is thrown while running commands, Symfony adds a log
message for it including the entire failing command. In addition, Symfony
registers an :doc:`event subscriber </event_dispatcher>` to listen to the
:ref:`ConsoleEvents::TERMINATE event <console-events-terminate>` and adds a log
message whenever a command doesn't finish with the ``0`` `exit status`_.

Using Events And Handling Signals
---------------------------------

When a command is running, many events are dispatched, one of them allows to
react to signals, read more in :doc:`this section </components/console/events>`.

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
* :doc:`/components/console/helpers/processhelper`: allows to run processes using ``DebugFormatterHelper``
* :doc:`/components/console/helpers/cursor`: allows to manipulate the cursor in the terminal

.. _`exit status`: https://en.wikipedia.org/wiki/Exit_status
