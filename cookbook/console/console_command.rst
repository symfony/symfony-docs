.. index::
   single: Console; Create commands

How to create a Console Command
===============================

The Console page of the Components section (:doc:`/components/console`) covers
how to create a Console command. This cookbook articles covers the differences
when creating Console commands within the Symfony2 framework.

Automatically Registering Commands
----------------------------------

To make the console commands available automatically with Symfony2, create a
``Command`` directory inside your bundle and create a php file suffixed with
``Command.php`` for each command that you want to provide. For example, if you
want to extend the ``AcmeDemoBundle`` (available in the Symfony Standard
Edition) to greet us from the command line, create ``GreetCommand.php`` and
add the following to it::

    // src/Acme/DemoBundle/Command/GreetCommand.php
    namespace Acme\DemoBundle\Command;

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
                ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
                ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
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

    $ app/console demo:greet Fabien

Testing Commands
----------------

When testing commands used as part of the full framework :class:`Symfony\\Bundle\\FrameworkBundle\\Console\\Application`
should be used instead of :class:`Symfony\\Component\\Console\\Application`::

    use Symfony\Component\Console\Tester\CommandTester;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Acme\DemoBundle\Command\GreetCommand;

    class ListCommandTest extends \PHPUnit_Framework_TestCase
    {
        public function testExecute()
        {
            // mock the Kernel or create one depending on your needs
            $application = new Application($kernel);
            $application->add(new GreetCommand());

            $command = $application->find('demo:greet');
            $commandTester = new CommandTester($command);
            $commandTester->execute(array('command' => $command->getName()));

            $this->assertRegExp('/.../', $commandTester->getDisplay());

            // ...
        }
    }

Getting Services from the Service Container
-------------------------------------------

By using :class:`Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand` 
as the base class for the command (instead of the more basic 
:class:`Symfony\\Component\\Console\\Command\\Command`), you have access to the 
service container. In other words, you have access to any configured service.
For example, you could easily extend the task to be translatable::

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $translator = $this->getContainer()->get('translator');
        if ($name) {
            $output->writeln($translator->trans('Hello %name%!', array('%name%' => $name)));
        } else {
            $output->writeln($translator->trans('Hello!'));
        }
    }
