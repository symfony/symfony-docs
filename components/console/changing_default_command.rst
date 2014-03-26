.. index::
    single: Console; Changing the Default Command

Changing the Default Command
============================

.. versionadded:: 2.5
    The :method:`Symfony\\Component\\Console\\Application::setDefaultCommand`
    method was introduced in Symfony 2.5.

will always run the ``ListCommand`` when no command name is passed. In order to change
the default command you just need to pass the command name you want to run by
default to the ``setDefaultCommand`` method::

    namespace Acme\Console\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class HelloWorldCommand extends Command
    {
        protected function configure()
        {
            $this->setName('hello:world')
                ->setDescription('Outputs \'Hello World\'');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $output->writeln('Hello World');
        }
    }

Executing the application and changing the default Command::

    // application.php

    use Acme\Console\Command\HelloWorldCommand;
    use Symfony\Component\Console\Application;

    $command = new HelloWorldCommand();
    $application = new Application();
    $application->add($command);
    $application->setDefaultCommand($command->getName());
    $application->run();

Test the new default console command by running the following:

.. code-block:: bash

    $ php application.php

This will print the following to the command line:

.. code-block:: text

    Hello Fabien

.. tip::

    This feature has a limitation: you cannot use it with any Command arguments.

Learn More!
-----------

* :doc:`/components/console/single_command_tool`
