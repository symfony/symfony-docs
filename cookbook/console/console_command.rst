.. index::
   single: Console; Create commands

How to create a Console Command
===============================

The Console page of the Components section (:doc:`/components/console/introduction`) covers
how to create a console command. This cookbook article covers the differences
when creating console commands within the Symfony2 framework.

Automatically Registering Commands
----------------------------------

To make the console commands available automatically with Symfony2, create a
``Command`` directory inside your bundle and create a PHP file suffixed with
``Command.php`` for each command that you want to provide. For example, if you
want to extend the AcmeDemoBundle (available in the Symfony Standard
Edition) to greet you from the command line, create ``GreetCommand.php`` and
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

However, due to the `container scopes </cookbook/service_container/scopes>`_ this
code doesn't work for some services. For instance, if you try to get the ``request``
service or any other service related to it, you'll get the following error:

.. code-block:: text

    You cannot create a service ("request") of an inactive scope ("request").

Consider the following example that uses the ``translator`` service to
translate some contents using a console command::

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

If you dig into the Translator component classes, you'll see that the ``request``
service is required to get the locale into which the contents are translated::

    // vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Translation/Translator.php
    public function getLocale()
    {
        if (null === $this->locale && $this->container->isScopeActive('request')
            && $this->container->has('request')) {
            $this->locale = $this->container->get('request')->getLocale();
        }

        return $this->locale;
    }

Therefore, when using the ``translator`` service inside a command, you'll get the
previous *"You cannot create a service of an inactive scope"* error message.
The solution in this case is as easy as setting the locale value explicitly
before translating contents::

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $locale = $input->getArgument('locale');

        $translator = $this->getContainer()->get('translator');
        $translator->setLocale($locale);

        if ($name) {
            $output->writeln($translator->trans('Hello %name%!', array('%name%' => $name)));
        } else {
            $output->writeln($translator->trans('Hello!'));
        }
    }

However for other services the solution might be more complex. For more details,
see :doc:`/cookbook/service_container/scopes`.

Testing Commands
----------------

When testing commands used as part of the full framework
:class:`Symfony\\Bundle\\FrameworkBundle\\Console\\Application <Symfony\\Bundle\\FrameworkBundle\\Console\\Application>` should be used
instead of
:class:`Symfony\\Component\\Console\\Application <Symfony\\Component\\Console\\Application>`::

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

.. versionadded:: 2.4
    Since Symfony 2.4, the ``CommandTester`` automatically detects the name of
    the command to execute. Prior to Symfony 2.4, you need to pass it via the
    ``command`` key.

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
    use Acme\DemoBundle\Command\GreetCommand;

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

.. versionadded:: 2.5
    :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase` was
    extracted from :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\WebTestCase`
    in Symfony 2.5. ``WebTestCase`` inherits from ``KernelTestCase``. The
    ``WebTestCase`` creates an instance of
    :class:`Symfony\\Bundle\\FrameworkBundle\\Client` via ``createClient()``,
    while ``KernelTestCase`` creates an instance of
    :class:`Symfony\\Component\\HttpKernel\\KernelInterface` via
    ``createKernel()``.
