.. index::
    single: Console; Commands

Creating a Basic Command
========================

To make a command that can be used in a Symfony Console application, create a
class that extends :class:`Symfony\\Component\\Console\\Command\\Command`::

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class CalculateCommand extends Command
    {
        protected function configure()
        {
            $this
                ->setName('calculate')
                ->setDescription('Doing simple math actions')
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
        }
    }

The most simple command contains 2 methods:

:method:`Symfony\\Component\\Console\\Command\\Command::configure`
    This method configures the command. It's used by the application to display
    help information and to match it to the incomming arguments.

:method:`Symfony\\Component\\Console\\Command\\Command::execute`
    This method contains the actual code to run when commands are executed.

Registering a Command
---------------------

To make a command work, register it in the application::

    // application.php

    // ...
    $application->add(new CalculateCommand());
    // ...
    
.. tip::

    When using the full stack framework, commands are automatically registered
    when placed in the ``Command`` namespace of a bundle.

Specifying the Input Definition
-------------------------------

Commands can get input. For instance, the calculator command needs 2 numbers
and the operation (e.g. "sum"). This input is defined in the so-called
*input definition* of a command. This specifies the arguments and options for a
command.

The input definition can be set using
:method:`Symfony\\Component\\Console\\Command\\Command::setDefinition`. However,
it's often much easier to use
:method:`Symfony\\Component\\Console\\Command\\Command::addArgument` and
:method:`Symfony\\Component\\Console\\Command\\Command::addOption`::

    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputOption;

    // ...
    class CalculatorCommand extends command
    {
        public function configure()
        {
            $this
                // ...
                ->addArgument('number1', InputArgument::REQUIRED, 'The first number')
                ->addArgument('number2', InputArgument::REQUIRED, 'The second number')
                ->addOption('operation', 'o', InputOption::VALUE_REQUIRED, 'The operation', 'sum')
        }

        // ...
    }

This code registers 2 arguments (the numbers) and an option (the operation).

Using Command Arguments
~~~~~~~~~~~~~~~~~~~~~~~

Arguments are the strings - separated by spaces - that come after the command
name itself. They are ordered and can be optional or required. Arguments are
added using
:method:`Symfony\\Component\\Console\\Command\\Command::addArgument`, which has
the following arguments:

``$name``
    The name of the argument.
``$mode`` (optional)
    The argument mode: ``InputArgument::REQUIRED``, ``InputArgument::OPTIONAL``
    and ``InputArgument::IS_ARRAY``.
``$description`` (optional)
    A short description of the command, which is used in the help message.
``$default`` (optional)
    The default value of the command, this defaults to ``null``.

There are 3 modes available:

+-------------------------+--------------------------------------------------------+
| Mode                    | Value                                                  |
+=========================+========================================================+
| InputArgument::REQUIRED | The argument is required.                              |
+-------------------------+--------------------------------------------------------+
| InputArgument::OPTIONAL | The argument is optional and therefore can be omitted. |
+-------------------------+--------------------------------------------------------+
| InputArgument::IS_ARRAY | The argument can contain an indefinite number of       |
|                         | arguments and must be used at the end of the argument  |
|                         | list.                                                  |
+-------------------------+--------------------------------------------------------+

You can combine ``IS_ARRAY`` with ``REQUIRED`` or ``OPTIONAL`` like this::

    $this
        // ...
        ->addArgument(
            'numbers',
            InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            'The numbers used (separate multiple numbers with a space)'
        );

You can get commands in the ``execute()`` method by using
:method:`InputInterface::getArgument() <Symfony\\Component\\Console\\Input\\InputInterface::getArgument>`::

    // ...
    class CalculatorCommand extends Command
    {
        public function configure()
        {
            $this
                ->setName('calculate')
                ->setDescription('Doing simple math actions')
                ->addArgument('numbers', InputArgumment::IS_ARRAY | InputArgument::REQUIRED)
        }

        public function execute(InputInterface $input, OutputInterface $output)
        {
            $numbers = $input->getArgument('numbers');

            $result = 0;
            foreach ($numbers as $number) {
                $result += intval($number);
            }

            $output->writeln('The result is: '.$result);
        }
    }

Now, you can use this command to get the sum of some numbers:

.. code-block:: bash

    $ php application.php calculate 5 4
    The result is: 9

    $ php application.php calculate 5 4 3 2 1
    The result is: 15

Using Command Options
---------------------

Unlike arguments, options are not ordered (meaning you can specify them in any
order) and are specified with two dashes (e.g. ``--in-words``) you can also
declare a one-letter shortcut that you can call with a single dash like
``-w``). Options are *always* optional and can be setup to accept a value
(e.g. ``--operation=sum`` / ``--operation sum``) or simply as a boolean flag
without a value (e.g. ``--in-words``).

.. tip::

    It is also possible to make an option *optionally* accept a value (so that
    ``--in-words``, ``--in-words=fr`` or ``--in-words fr`` work). Options can
    also be configured to accept an array of values, meaning it can be used
    multiple times.

The math operation is an example of a command option::

    $this
        // ...
        ->addOption('operation', 'o', InputOption::VALUE_REQUIRED, 'The operation', 'sum')

Next, use this in the command to use the operation on the numbers::

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $numbers = $input->getArgument('numbers');

        switch ($input->getOption('operation')) {
            case 'sum':
                $result = 0;
                foreach ($numbers as $number) {
                    $result += intval($number);
                }
                break;

            case 'divide':
                $result = array_shift($numbers);
                foreach ($numbers as $number) {
                    $result /= intval($number);
                }
                break;

            default:
                throw new \InvalidArgumentException(
                    'Unknown operation specified. Known operations are: sum, divide'
                );
        }

        $output->writeln('The result is: '.$result);
    }

Now, when you run the task, you can optionally specify an ``--operation``
option:

.. code-block:: bash

    $ php application.php calculate 4 2
    The result is: 6

    $ php application.php calculate 4 2 --operation=divide
    The result is: 2

As the default for ``--operation`` is sum, the first call will return ``4 +
2``. In the second call, the operation is specified and ``4 / 2`` is returned.

Recall that options don't care about their order. So, either of the following
will work (assuming the command has an ``--in-words`` option):

.. code-block:: bash

    $ php application.php calculate 4 2 --operation=divide --in-words
    $ php application.php calculate 4 2 --in-words --operation=divide

There are 4 option variants you can use:

+-----------------------------+---------------------------------------------------------+
| Option                      | Value                                                   |
+=============================+=========================================================+
| InputOption::VALUE_IS_ARRAY | This option accepts multiple values (e.g.               |
|                             | ``--dir=/foo --dir=/bar``).                             |
+-----------------------------+---------------------------------------------------------+
| InputOption::VALUE_NONE     | Do not accept input for this option (e.g. ``--print``). |
+-----------------------------+---------------------------------------------------------+
| InputOption::VALUE_REQUIRED | This value is required (e.g. ``--operation=sum``),      |
|                             | the option itself is still optional.                    |
+-----------------------------+---------------------------------------------------------+
| InputOption::VALUE_OPTIONAL | This option may or may not have a value (e.g.           |
|                             | ``--in-words`` or ``--in-words=fr``).                   |
+-----------------------------+---------------------------------------------------------+

You can combine ``VALUE_IS_ARRAY`` with ``VALUE_REQUIRED`` or ``VALUE_OPTIONAL`` like this::

    $this
        // ...
        ->addOption(
            'colors',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Which colors do you like?',
            array('blue', 'red')
        );

.. _components-console-coloring:

Coloring the Output
-------------------

Whenever you output text, you can surround the text with tags to color its
output. For example::

    // green text
    $output->writeln('<info>foo</info>');

    // yellow text
    $output->writeln('<comment>foo</comment>');

    // black text on a cyan background
    $output->writeln('<question>foo</question>');

    // white text on a red background
    $output->writeln('<error>foo</error>');

It is possible to define your own styles using the class
:class:`Symfony\\Component\\Console\\Formatter\\OutputFormatterStyle`::

    use Symfony\Component\Console\Formatter\OutputFormatterStyle;

    // ...
    $style = new OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
    $output->getFormatter()->setStyle('fire', $style);

    $output->writeln('<fire>foo</fire>');

Available foreground and background colors are: ``black``, ``red``, ``green``,
``yellow``, ``blue``, ``magenta``, ``cyan`` and ``white``.

And available options are: ``bold``, ``underscore``, ``blink``, ``reverse`` and
``conceal``.

You can also set these colors and options inside the tagname::

    // green text
    $output->writeln('<fg=green>foo</fg=green>');

    // black text on a cyan background
    $output->writeln('<fg=black;bg=cyan>foo</fg=black;bg=cyan>');

    // bold text on a yellow background
    $output->writeln('<bg=yellow;options=bold>foo</bg=yellow;options=bold>');

.. tip::

    As these tags can create a little mess, you can use just ``</>`` as close
    tag instead of repeating all text again. This will make things a bit easier
    to read in the source code.

.. note::

    Windows does not support ANSI colors by default so the Console component detects and
    disables colors where Windows does not have support. However, if Windows is not
    configured with an ANSI driver and your console commands invoke other scripts which
    emit ANSI color sequences, they will be shown as raw escape characters.

    To enable ANSI color support for Windows, you can install tools like
    ANSICON_ or ConEmu_.

Verbosity Levels
----------------

.. versionadded:: 2.3
   The ``VERBOSITY_VERY_VERBOSE`` and ``VERBOSITY_DEBUG`` constants were introduced
   in version 2.3

The console has 5 levels of verbosity. These are defined in the
:class:`Symfony\\Component\\Console\\Output\\OutputInterface`:

=======================================  ==================================
Mode                                     Value
=======================================  ==================================
OutputInterface::VERBOSITY_QUIET         Do not output any messages
OutputInterface::VERBOSITY_NORMAL        The default verbosity level
OutputInterface::VERBOSITY_VERBOSE       Increased verbosity of messages
OutputInterface::VERBOSITY_VERY_VERBOSE  Informative non essential messages
OutputInterface::VERBOSITY_DEBUG         Debug messages
=======================================  ==================================

You can specify the quiet verbosity level with the ``--quiet`` or ``-q``
option. The ``--verbose``, ``--verbose=1``, etc. or ``-v``, ``-vv``, etc.
options are used when you want an increased level of verbosity.

.. tip::

    The full exception stacktrace is printed if the ``VERBOSITY_DEBUG`` level
    or above is used.

It is possible to print a message in a command for only a specific verbosity
level. For example::

    if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
        $output->writeln(...);
    }

When the quiet level is used, all output is suppressed as the default
:method:`Symfony\\Component\\Console\\Output\\Output::write` method returns
without actually printing.

Console Helpers
---------------

The console component also contains a set of "helpers" - different small
tools capable of helping you with different tasks:

* :doc:`/components/console/helpers/dialoghelper`: interactively ask the user for information
* :doc:`/components/console/helpers/formatterhelper`: customize the output colorization
* :doc:`/components/console/helpers/progresshelper`: shows a progress bar
* :doc:`/components/console/helpers/tablehelper`: displays tabular data as a table

.. _component-console-testing-commands:

Testing Commands
----------------

Symfony provides several tools to help you test your commands. The most
useful one is the :class:`Symfony\\Component\\Console\\Tester\\CommandTester`
class. It uses special input and output classes to ease testing without a real
console::

    use Acme\Console\Command\CalculateCommand;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Tester\CommandTester;

    class CalculateCommandTest extends \PHPUnit_Framework_TestCase
    {
        private $application;
        private $command;
        private $commandTester;

        public function setUp()
        {
            $this->application = new Application();
            $this->application->add(new CalculateCommand());

            $this->command = $application->find('calculate');
            $this->commandTester = new CommandTester($command);
        }

        public function testOutputsHelpMessageWhenCalledWithoutArguments()
        {
            $this->commandTester->execute(array('command' => $command->getName()));

            $this->assertRegExp('/.../', $this->commandTester->getDisplay());

            // ...
        }
    }

The :method:`Symfony\\Component\\Console\\Tester\\CommandTester::getDisplay`
method returns what would have been displayed during a normal call from the
console.

You can test sending arguments and options to the command by passing them as an
array to the
:method:`Symfony\\Component\\Console\\Tester\\CommandTester::execute` method::

    // ...
    class CalculateCommandTest extends \PHPUnit_Framework_TestCase
    {
        // ...

        public function testOutputsResultOfDivideOperation()
        {
            $this->commandTester->execute(array(
                'command'     => $command->getName(),
                'numbers'     => array(4, 2),
                '--operation' => 'divide',
            ));

            $this->assertRegExp('/The result is: 6/', $commandTester->getDisplay());
        }
    }

.. tip::

    You can also test a whole console application by using
    :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester`.

.. you left the action here

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

        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        // ...
    }

First, you :method:`Symfony\\Component\\Console\\Application::find` the
command you want to execute by passing the command name.

Then, you need to create a new
:class:`Symfony\\Component\\Console\\Input\\ArrayInput` with the arguments and
options you want to pass to the command.

Eventually, calling the ``run()`` method actually executes the command and
returns the returned code from the command (return value from command's
``execute()`` method).

.. note::

    Most of the time, calling a command from code that is not executed on the
    command line is not a good idea for several reasons. First, the command's
    output is optimized for the console. But more important, you can think of
    a command as being like a controller; it should use the model to do
    something and display feedback to the user. So, instead of calling a
    command from the Web, refactor your code and move the logic to a new
    class.

.. _ANSICON: https://github.com/adoxa/ansicon/releases
.. _ConEmu: http://conemu.github.io/
