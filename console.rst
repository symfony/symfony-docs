.. index::
   single: Console; Create commands

Console Commands
================

The Symfony framework provides lots of commands through the ``bin/console`` script
(e.g. the well-known ``bin/console cache:clear`` command). These commands are
created with the :doc:`Console component </components/console>`. You can also
use it to create your own commands.

The Console: APP_ENV & APP_DEBUG
---------------------------------

Console commands run in the :ref:`environment <config-dot-env>` defined in the ``APP_ENV``
variable of the ``.env`` file, which is ``dev`` by default. It also reads the ``APP_DEBUG``
value to turn "debug" mode on or off (it defaults to ``1``, which is on).

To run the command in another environment or debug mode, edit the value of ``APP_ENV``
and ``APP_DEBUG``.

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
        protected function configure()
        {
            // ...
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            // ...
        }
    }

Configuring the Command
-----------------------

First of all, you must configure the name of the command in the ``configure()``
method. Then you can optionally define a help message and the
:doc:`input options and arguments </console/input>`::

    // ...
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:create-user')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new user.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a user...')
        ;
    }

Registering the Command
-----------------------

Symfony commands must be registered as services and :doc:`tagged </service_container/tags>`
with the ``console.command`` tag. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.

Executing the Command
---------------------

After configuring and registering the command, you can execute it in the terminal:

.. code-block:: terminal

    $ php bin/console app:create-user

As you might expect, this command will do nothing as you didn't write any logic
yet. Add your own logic inside the ``execute()`` method.

Console Output
--------------

The ``execute()`` method has access to the output stream to write messages to
the console::

    // ...
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        // the value returned by someMethod() can be an iterator (https://secure.php.net/iterator)
        // that generates and returns the messages with the 'yield' PHP keyword
        $output->writeln($this->someMethod());

        // outputs a message followed by a "\n"
        $output->writeln('Whoa!');

        // outputs a message without adding a "\n" at the end of the line
        $output->write('You are about to ');
        $output->write('create a user.');
    }

.. versionadded:: 4.1
    The support of PHP iterators in the ``write()`` and ``writeln()`` methods
    was introduced in Symfony 4.1.

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

.. versionadded:: 4.1
    Output sections were introduced in Symfony 4.1.

The regular console output can be divided into multiple independent regions
called "output sections". Create one or more of these sections when you need to
clear and overwrite the output information.

Sections are created with the
:method:`Symfony\\Component\\Console\\Output\\ConsoleOutput::section` method,
which returns an instance of
:class:`Symfony\\Component\\Console\\Output\\ConsoleSectionOutput`::

    class MyCommand extends Command
    {
        protected function execute(InputInterface $input, OutputInterface $output)
        {
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
        }
    }

.. note::

    A new line is appended automatically when displaying information in a section.

Output sections let you manipulate the Console output in advanced ways, such as
:ref:`displaying multiple progress bars <console-multiple-progress-bars>` which
are updated independently and :ref:`appending rows to tables <console-modify-rendered-tables>`
that have already been rendered.

Console Input
-------------

Use input options or arguments to pass information to the command::

    use Symfony\Component\Console\Input\InputArgument;

    // ...
    protected function configure()
    {
        $this
            // configure an argument
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
            // ...
        ;
    }

    // ...
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        // retrieve the argument value using getArgument()
        $output->writeln('Username: '.$input->getArgument('username'));
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

To actually create a new user, the command has to access to some
:doc:`services </service_container>`. Since your command is already registered
as a service, you can use normal dependency injection. Imagine you have a
``App\Service\UserManager`` service that you want to access::

    // ...
    use Symfony\Component\Console\Command\Command;
    use App\Service\UserManager;

    class CreateUserCommand extends Command
    {
        private $userManager;

        public function __construct(UserManager $userManager)
        {
            parent::__construct();

            $this->userManager = $userManager;
        }

        // ...

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            // ...

            $this->userManager->create($input->getArgument('username'));

            $output->writeln('User successfully generated!');
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
    where you can ask for missing options/arguments. After this command,
    missing options/arguments will result in an error.

:method:`Symfony\\Component\\Console\\Command\\Command::execute` *(required)*
    This method is executed after ``interact()`` and ``initialize()``.
    It contains the logic you want the command to execute.

.. _console-testing-commands:

Testing Commands
----------------

Symfony provides several tools to help you test your commands. The most
useful one is the :class:`Symfony\\Component\\Console\\Tester\\CommandTester`
class. It uses special input and output classes to ease testing without a real
console::

    // tests/Command/CreateUserCommandTest.php
    namespace App\Tests\Command;

    use App\Command\CreateUserCommand;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
    use Symfony\Component\Console\Tester\CommandTester;

    class CreateUserCommandTest extends KernelTestCase
    {
        public function testExecute()
        {
            $kernel = self::bootKernel();
            $application = new Application($kernel);

            $application->add(new CreateUserCommand());

            $command = $application->find('app:create-user');
            $commandTester = new CommandTester($command);
            $commandTester->execute(array(
                'command'  => $command->getName(),

                // pass arguments to the helper
                'username' => 'Wouter',

                // prefix the key with two dashes when passing options,
                // e.g: '--some-option' => 'option_value',
            ));

            // the output of the command in the console
            $output = $commandTester->getDisplay();
            $this->assertContains('Username: Wouter', $output);

            // ...
        }
    }

.. tip::

    You can also test a whole console application by using
    :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester`.

.. note::

    When using the Console component in a standalone project, use
    :class:`Symfony\\Component\\Console\\Application <Symfony\\Component\\Console\\Application>`
    and extend the normal ``\PHPUnit\Framework\TestCase``.

To be able to use the fully set up service container for your console tests
you can extend your test from
:class:`Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase`::

    // ...
    use Symfony\Component\Console\Tester\CommandTester;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

    class CreateUserCommandTest extends KernelTestCase
    {
        public function testExecute()
        {
            $kernel = static::createKernel();
            $kernel->boot();

            $application = new Application($kernel);

            $command = $application->find('app:create-user');
            $commandTester = new CommandTester($command);
            $commandTester->execute(array(
                'command'  => $command->getName(),
                'username' => 'Wouter',
            ));

            $output = $commandTester->getDisplay();
            $this->assertContains('Username: Wouter', $output);

            // ...
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
* :doc:`/components/console/helpers/table`: displays tabular data as a table
