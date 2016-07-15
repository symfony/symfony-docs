.. index::
   single: Console; Create commands

Console Commands
================

The Symfony framework provide lots of commands through the ``app/console`` file
(e.g. the common ``app/console cache:clear`` command). These commands are
created using the :doc:`Console component </components/console>`. This allows
you to add custom commands as well, for instance to manage admin users.

Creating a Command
------------------

Each command will have its own command class that manages the logic. It serves
as a controller for the console, except that it doesn't work with the HTTP
Request/Response flow, but with Input/Output streams.

Your command has to be in the ``Command`` namespace of your bundle (e.g.
``AppBundle\Command``) and the name has to end with ``Command``.

For instance, assume you create a command to generate new admin users (you'll
learn about the methods soon)::

    // src/AppBundle/Command/GenerateAdminCommand.php
    namespace AppBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class GenerateAdminCommand extends Command
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

First of all, you need to configure the name of the command in ``configure()``.
Besides the name, you can configure things like the help message and
:doc:`input options and arguments </console/input>`.

::

    // ...
    protected function configure()
    {
        $this
            // the name of the command (the part after "app/console")
            ->setName('app:generate-admin')

            // the shot description shown while running "php app/console list"
            ->setDescription('Generates new admin users.')

            // the help message shown when running the command with the
            // "--help" option
            ->setHelp(<<<EOT
    This command allows you to generate admins.

    ...
    EOT
            )
        ;
    }

Executing the Command
---------------------

After configuring, you can execute the command in the terminal:

.. code-block:: bash

    $ php app/console app:generate-admin

As you might expect, this command will do nothing as you didn't write any logic
yet. When running the command, the ``execute()`` method will be executed. This
method has access to the input stream (e.g. options and arguments) and the
output stream (to write messages to the console)::

    // ...
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console
        $output->writeln([
            'Admin Generator',
            '===============',
            '',
        ]);

        // output a single line
        $output->writeln('Whoa!');

        // output a message without moving to a new line (the message will
        // apear on one line)
        $output->write('You\'re about to');
        $output->write('generate an admin user.');
    }

Now, try executing the command:

.. code-block:: bash

    $ php app/console app:generate-admin
    Admin Generator
    ===============

    Whoa!
    You're about to generate an admin user.

Console Input
-------------

Use input options or arguments to pass information to the command::

    use Symfony\Component\Console\Input\InputArgument;

    // ...
    protected function configure()
    {
        $this
            // configure an argument
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the admin.')
            // ...
        ;
    }

    // ...
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Admin Generator',
            '===============',
            '',
        ]);

        // retrieve the argument value using getArgument()
        $this->writeln('Username: '.$input->getArgument('username'));
    }

Now, you can pass the username to the command:

.. code-block:: bash

    $ php app/console app:generate-admin Wouter
    Admin Generator
    ===============

    Username: Wouter

.. seealso::

    Read :doc:`/console/input` for more information about console options and
    arguments.

Getting Services from the Service Container
-------------------------------------------

To actually generate a new admin user, the command has to access some
:doc:`services </service_container>`. This can be done by extending
:class:`Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand`
instead::

    // ...
    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

    class GenerateAdminCommand extends ContainerAwareCommand
    {
        // ...

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            // ...

            // access the container using getContainer()
            $adminGenerator = $this->getContainer()->get('app.admin_generator');

            $generatedPassword = md5(uniqid());

            $output->writeln('Generated password: '.$generatedPassword);

            // for instance, generate an admin like this
            $adminGenerator->generate($input->getArgument('username'), $generatedPassword);

            $output->writeln('Admin successfully generated!');
        }
    }

Now, once you created the required services and logic, the command will execute
the ``generate()`` method of the ``app.admin_generator`` service and the admin
will be created.

Command Lifecycle
~~~~~~~~~~~~~~~~~

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

Testing Commands
----------------

When testing commands used as part of the full-stack framework,
:class:`Symfony\\Bundle\\FrameworkBundle\\Console\\Application <Symfony\\Bundle\\FrameworkBundle\\Console\\Application>`
should be used instead of
:class:`Symfony\\Component\\Console\\Application <Symfony\\Component\\Console\\Application>`::

    use Symfony\Component\Console\Tester\CommandTester;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use AppBundle\Command\GreetCommand;

    class ListCommandTest extends \PHPUnit_Framework_TestCase
    {
        public function testExecute()
        {
            // mock the Kernel or create one depending on your needs
            $application = new Application($kernel);
            $application->add(new GreetCommand());

            $command = $application->find('demo:greet');
            $commandTester = new CommandTester($command);
            $commandTester->execute(
                array(
                    'name'    => 'Fabien',
                    '--yell'  => true,
                )
            );

            $this->assertRegExp('/.../', $commandTester->getDisplay());

            // ...
        }
    }

.. note::

    In the specific case above, the ``name`` parameter and the ``--yell`` option
    are not mandatory for the command to work, but are shown so you can see
    how to customize them when calling the command.

To be able to use the fully set up service container for your console tests
you can extend your test from
:class:`Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase`::

    use Symfony\Component\Console\Tester\CommandTester;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
    use AppBundle\Command\GreetCommand;

    class ListCommandTest extends KernelTestCase
    {
        public function testExecute()
        {
            $kernel = $this->createKernel();
            $kernel->boot();

            $application = new Application($kernel);
            $application->add(new GreetCommand());

            $command = $application->find('demo:greet');
            $commandTester = new CommandTester($command);
            $commandTester->execute(
                array(
                    'name'    => 'Fabien',
                    '--yell'  => true,
                )
            );

            $this->assertRegExp('/.../', $commandTester->getDisplay());

            // ...
        }
    }
