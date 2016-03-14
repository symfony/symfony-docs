.. index::
   single: Console; Create commands

How to Create a Console Command
===============================

The Console page of the Components section (:doc:`/components/console/introduction`) covers
how to create a console command. This cookbook article covers the differences
when creating console commands within the Symfony Framework.

Automatically Registering Commands
----------------------------------

To make the console commands available automatically with Symfony, create a
``Command`` directory inside your bundle and create a PHP file suffixed with
``Command.php`` for each command that you want to provide. For example, if you
want to extend the AppBundle to greet you from the command line, create
``GreetCommand.php`` and add the following to it::

    // src/AppBundle/Command/GreetCommand.php
    namespace AppBundle\Command;

    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    class GreetCommand extends ContainerAwareCommand
    {
        protected function configure()
        {
            $this
                ->setName('demo:greet')
                ->setDescription('Greet someone')
                ->addArgument(
                    'name',
                    InputArgument::OPTIONAL,
                    'Who do you want to greet?'
                )
                ->addOption(
                    'yell',
                    null,
                    InputOption::VALUE_NONE,
                    'If set, the task will yell in uppercase letters'
                )
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $name = $input->getArgument('name');
            if ($name) {
                $text = 'Hello '.$name;
            } else {
                $text = 'Hello';
            }

            if ($input->getOption('yell')) {
                $text = strtoupper($text);
            }

            $output->writeln($text);
        }
    }

This command will now automatically be available to run:

.. code-block:: bash

    $ php bin/console demo:greet Fabien

.. _cookbook-console-dic:

Register Commands in the Service Container
-------------------------------------------

Just like controllers, commands can be declared as services. See the
:doc:`dedicated cookbook entry </cookbook/console/commands_as_services>`
for details.

Getting Services from the Service Container
-------------------------------------------

By using :class:`Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand`
as the base class for the command (instead of the more basic
:class:`Symfony\\Component\\Console\\Command\\Command`), you have access to the
service container. In other words, you have access to any configured service::

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $logger = $this->getContainer()->get('logger');

        $logger->info('Executing command for '.$name);
        // ...
    }

Invoking other Commands
-----------------------

See :ref:`calling-existing-command` if you need to implement a command that runs
other dependent commands.

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
