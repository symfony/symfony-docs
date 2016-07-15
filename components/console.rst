.. index::
    single: Console; CLI
    single: Components; Console

The Console Component
=====================

    The Console component eases the creation of beautiful and testable command
    line interfaces.

The Console component allows you to create command-line commands. Your console
commands can be used for any recurring task, such as cronjobs, imports, or
other batch jobs.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/console`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/console).

.. include:: /components/require_autoload.rst.inc

Creating a basic Command
------------------------

To make a console command that greets you from the command line, create ``GreetCommand.php``
and add the following to it::

    namespace Acme\Console\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    class GreetCommand extends Command
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

You also need to create the file to run at the command line which creates
an ``Application`` and adds commands to it::

    #!/usr/bin/env php
    <?php
    // application.php

    require __DIR__.'/vendor/autoload.php';

    use Acme\Console\Command\GreetCommand;
    use Symfony\Component\Console\Application;

    $application = new Application();
    $application->add(new GreetCommand());
    $application->run();

Test the new console command by running the following

.. code-block:: bash

    $ php application.php demo:greet Fabien

This will print the following to the command line:

.. code-block:: text

    Hello Fabien

You can also use the ``--yell`` option to make everything uppercase:

.. code-block:: bash

    $ php application.php demo:greet Fabien --yell

This prints::

    HELLO FABIEN

.. _component-console-testing-commands:

Testing Commands
----------------

Symfony provides several tools to help you test your commands. The most
useful one is the :class:`Symfony\\Component\\Console\\Tester\\CommandTester`
class. It uses special input and output classes to ease testing without a real
console::

    use Acme\Console\Command\GreetCommand;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Tester\CommandTester;

    class ListCommandTest extends \PHPUnit_Framework_TestCase
    {
        public function testExecute()
        {
            $application = new Application();
            $application->add(new GreetCommand());

            $command = $application->find('demo:greet');
            $commandTester = new CommandTester($command);
            $commandTester->execute(array('command' => $command->getName()));

            $this->assertRegExp('/.../', $commandTester->getDisplay());

            // ...
        }
    }

The :method:`Symfony\\Component\\Console\\Tester\\CommandTester::getDisplay`
method returns what would have been displayed during a normal call from the
console.

You can test sending arguments and options to the command by passing them
as an array to the :method:`Symfony\\Component\\Console\\Tester\\CommandTester::execute`
method::

    use Acme\Console\Command\GreetCommand;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Tester\CommandTester;

    class ListCommandTest extends \PHPUnit_Framework_TestCase
    {
        // ...

        public function testNameIsOutput()
        {
            $application = new Application();
            $application->add(new GreetCommand());

            $command = $application->find('demo:greet');
            $commandTester = new CommandTester($command);
            $commandTester->execute(array(
                'command'      => $command->getName(),
                'name'         => 'Fabien',
                '--iterations' => 5,
            ));

            $this->assertRegExp('/Fabien/', $commandTester->getDisplay());
        }
    }

.. tip::

    You can also test a whole console application by using
    :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester`.

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    console/*
    console/helpers/index

.. _Packagist: https://packagist.org/packages/symfony/console
