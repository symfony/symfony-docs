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

Command Lifecycle
~~~~~~~~~~~~~~~~~

Commands have three lifecycle methods:

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


.. _verbosity-levels:

Verbosity Levels
~~~~~~~~~~~~~~~~

.. versionadded:: 2.3
   The ``VERBOSITY_VERY_VERBOSE`` and ``VERBOSITY_DEBUG`` constants were introduced
   in version 2.3

The console has five verbosity levels. These are defined in the
:class:`Symfony\\Component\\Console\\Output\\OutputInterface`:

===========================================  ==================================  =====================
Value                                        Meaning                             Console option
===========================================  ==================================  =====================
``OutputInterface::VERBOSITY_QUIET``         Do not output any messages          ``-q`` or ``--quiet``
``OutputInterface::VERBOSITY_NORMAL``        The default verbosity level         (none)
``OutputInterface::VERBOSITY_VERBOSE``       Increased verbosity of messages     ``-v``
``OutputInterface::VERBOSITY_VERY_VERBOSE``  Informative non essential messages  ``-vv``
``OutputInterface::VERBOSITY_DEBUG``         Debug messages                      ``-vvv``
===========================================  ==================================  =====================

.. tip::

    The full exception stacktrace is printed if the ``VERBOSITY_VERBOSE``
    level or above is used.

It is possible to print a message in a command for only a specific verbosity
level. For example::

    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
        $output->writeln(...);
    }

There are also more semantic methods you can use to test for each of the
verbosity levels::

    if ($output->isQuiet()) {
        // ...
    }

    if ($output->isVerbose()) {
        // ...
    }

    if ($output->isVeryVerbose()) {
        // ...
    }

    if ($output->isDebug()) {
        // ...
    }

.. note::

    These semantic methods are defined in the ``OutputInterface`` starting from
    Symfony 3.0. In previous Symfony versions they are defined in the different
    implementations of the interface (e.g. :class:`Symfony\\Component\\Console\\Output\\Output`)
    in order to keep backwards compatibility.

When the quiet level is used, all output is suppressed as the default
:method:`Symfony\\Component\\Console\\Output\\Output::write` method returns
without actually printing.

.. tip::

    The MonologBridge provides a :class:`Symfony\\Bridge\\Monolog\\Handler\\ConsoleHandler`
    class that allows you to display messages on the console. This is cleaner
    than wrapping your output calls in conditions. For an example use in
    the Symfony Framework, see :doc:`/logging/monolog_console`.


Console Helpers
---------------

The console component also contains a set of "helpers" - different small
tools capable of helping you with different tasks:

* :doc:`/components/console/helpers/questionhelper`: interactively ask the user for information
* :doc:`/components/console/helpers/formatterhelper`: customize the output colorization
* :doc:`/components/console/helpers/progressbar`: shows a progress bar
* :doc:`/components/console/helpers/table`: displays tabular data as a table

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

.. _calling-existing-command:

Calling an Existing Command
---------------------------

If a command depends on another one being run before it, instead of asking the
user to remember the order of execution, you can call it directly yourself.
This is also useful if you want to create a "meta" command that just runs a
bunch of other commands (for instance, all commands that need to be run when
the project's code has changed on the production servers: clearing the cache,
generating Doctrine2 proxies, dumping Assetic assets, ...).

Calling a command from another one is straightforward::

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('demo:greet');

        $arguments = array(
            'command' => 'demo:greet',
            'name'    => 'Fabien',
            '--yell'  => true,
        );

        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);

        // ...
    }

First, you :method:`Symfony\\Component\\Console\\Application::find` the
command you want to execute by passing the command name. Then, you need to create
a new :class:`Symfony\\Component\\Console\\Input\\ArrayInput` with the arguments
and options you want to pass to the command.

Eventually, calling the ``run()`` method actually executes the command and
returns the returned code from the command (return value from command's
``execute()`` method).

.. tip::

    If you want to suppress the output of the executed command, pass a
    :class:`Symfony\\Component\\Console\\Output\\NullOutput` as the second
    argument to ``$command->run()``.

.. caution::

    Note that all the commands will run in the same process and some of Symfony's
    built-in commands may not work well this way. For instance, the ``cache:clear``
    and ``cache:warmup`` commands change some class definitions, so running
    something after them is likely to break.

.. note::

    Most of the time, calling a command from code that is not executed on the
    command line is not a good idea for several reasons. First, the command's
    output is optimized for the console. But more important, you can think of
    a command as being like a controller; it should use the model to do
    something and display feedback to the user. So, instead of calling a
    command from the Web, refactor your code and move the logic to a new
    class.

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    console/*
    console/helpers/index

.. _Packagist: https://packagist.org/packages/symfony/console
.. _Cmder: http://cmder.net/
.. _ConEmu: https://conemu.github.io/
.. _ANSICON: https://github.com/adoxa/ansicon/releases
.. _Mintty: https://mintty.github.io/
