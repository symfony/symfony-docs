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

Executing the Command
---------------------

Symfony registers any PHP class extending :class:`Symfony\\Component\\Console\\Command\\Command`
as a console command automatically. So you can now execute this command in the
terminal:

.. code-block:: terminal

    $ php bin/console app:create-user

.. note::

    If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
    your command classes are automatically registered as services.

    You can also manually register your command as a service by configuring the service
    and :doc:`tagging it </service_container/tags>` with ``console.command``.

As you might expect, this command will do nothing as you didn't write any logic
yet. Add your own logic inside the ``execute()`` method, which has access to the
input stream (e.g. options and arguments) and the output stream (to write
messages to the console)::

    // ...
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        // outputs a message followed by a "\n"
        $output->writeln('Whoa!');

        // outputs a message without adding a "\n" at the end of the line
        $output->write('You are about to ');
        $output->write('create a user.');
    }

Now, try executing the command:

.. code-block:: terminal

    $ php bin/console app:create-user
    User Creator
    ============

    Whoa!
    You are about to create a user.

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
            $this->userManager = $userManager;
            
            parent::__construct();
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
            $application->add(new CreateUserCommand());

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
