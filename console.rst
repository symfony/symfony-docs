.. index::
   single: Console; Create commands

Console Commands
================

The Symfony framework provide lots of commands through the ``bin/console`` script
(e.g. the well-known ``bin/console cache:clear`` command). These commands are
created with the :doc:`Console component </components/console>`. You can also
use it to create your own commands.

Creating a Command
------------------

Commands are defined in classes which must be created in the ``Command`` namespace
of your bundle (e.g. ``AppBundle\Command``) and their names must end with the
``Command`` suffix.

For example, a command called ``CreateUser`` must follow this structure::

    // src/AppBundle/Command/CreateUserCommand.php
    namespace AppBundle\Command;

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
            ->setName('app:create-users')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates new users.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command allows you to create users...")
        ;
    }

Executing the Command
---------------------

After configuring the command, you can execute it in the terminal:

.. code-block:: bash

    $ php bin/console app:create-users

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

.. code-block:: bash

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

.. code-block:: bash

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
:doc:`services </service_container>`. This can be done by making the command
extend the :class:`Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand`
instead::

    // ...
    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

    class CreateUserCommand extends ContainerAwareCommand
    {
        // ...

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            // ...

            // access the container using getContainer()
            $userManager = $this->getContainer()->get('app.user_manager');
            $userManager->create($input->getArgument('username'));

            $output->writeln('User successfully generated!');
        }
    }

Now, once you created the required services and logic, the command will execute
the ``create()`` method of the ``app.user_manager`` service and the user will
be created.

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

    // tests/AppBundle/Command/CreateUserCommandTest.php
    namespace Tests\AppBundle\Command;

    use AppBundle\Command\CreateUserCommand;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
    use Symfony\Component\Console\Tester\CommandTester;

    class CreateUserCommandTest extends KernelTestCase
    {
        public function testExecute()
        {
            self::bootKernel();
            $application = new Application(self::$kernel);

            $application->add(new CreateUserCommand());

            $command = $application->find('app:create-user');
            $commandTester = new CommandTester($command);
            $commandTester->execute(array(
                'command'  => $command->getName(),

                // pass arguments to the helper
                'username' => 'Wouter',

                // prefix the key with a double slash when passing options,
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
    and extend the normal ``\PHPUnit_Framework_TestCase``.

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
            $kernel = $this->createKernel();
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
