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

* Use the official Git repository (https://github.com/symfony/Console);
* :doc:`Install it via Composer </components/using_components>` (``symfony/console`` on `Packagist`_).

.. note::

    Windows does not support ANSI colors by default so the Console Component detects and
    disables colors where Windows does not have support. However, if Windows is not
    configured with an ANSI driver and your console commands invoke other scripts which
    emit ANSI color sequences, they will be shown as raw escape characters.

    To enable ANSI colour support for Windows, please install `ANSICON`_.

Creating a basic Command
------------------------

To make a console command that greets you from the command line, create ``GreetCommand.php``
and add the following to it::

    namespace Acme\DemoBundle\Command;

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
    // app/console

    use Acme\DemoBundle\Command\GreetCommand;
    use Symfony\Component\Console\Application;

    $application = new Application();
    $application->add(new GreetCommand);
    $application->run();

Test the new console command by running the following

.. code-block:: bash

    $ app/console demo:greet Fabien

This will print the following to the command line:

.. code-block:: text

    Hello Fabien

You can also use the ``--yell`` option to make everything uppercase:

.. code-block:: bash

    $ app/console demo:greet Fabien --yell

This prints::

    HELLO FABIEN

.. _components-console-coloring:

Coloring the Output
~~~~~~~~~~~~~~~~~~~

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

    $style = new OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
    $output->getFormatter()->setStyle('fire', $style);
    $output->writeln('<fire>foo</fire>');

Available foreground and background colors are: ``black``, ``red``, ``green``,
``yellow``, ``blue``, ``magenta``, ``cyan`` and ``white``.

And available options are: ``bold``, ``underscore``, ``blink``, ``reverse`` and ``conceal``.

You can also set these colors and options inside the tagname::

    // green text
    $output->writeln('<fg=green>foo</fg=green>');

    // black text on a cyan background
    $output->writeln('<fg=black;bg=cyan>foo</fg=black;bg=cyan>');

    // bold text on a yellow background
    $output->writeln('<bg=yellow;options=bold>foo</bg=yellow;options=bold>');

Verbosity Levels
~~~~~~~~~~~~~~~~

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
option. The ``--verbose`` or ``-v`` option is used when you want an increased
level of verbosity.

.. tip::

    The full exception stacktrace is printed if the ``VERBOSITY_VERBOSE``
    level or above is used.

It is possible to print a message in a command for only a specific verbosity
level. For example::

    if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
        $output->writeln(...);
    }

When the quiet level is used, all output is suppressed as the default
:method:`Symfony\Component\Console\Output::write<Symfony\\Component\\Console\\Output::write>`
method returns without actually printing.

Using Command Arguments
-----------------------

The most interesting part of the commands are the arguments and options that
you can make available. Arguments are the strings - separated by spaces - that
come after the command name itself. They are ordered, and can be optional
or required. For example, add an optional ``last_name`` argument to the command
and make the ``name`` argument required::

    $this
        // ...
        ->addArgument(
            'name',
            InputArgument::REQUIRED,
            'Who do you want to greet?'
        )
        ->addArgument(
            'last_name',
            InputArgument::OPTIONAL,
            'Your last name?'
        );

You now have access to a ``last_name`` argument in your command::

    if ($lastName = $input->getArgument('last_name')) {
        $text .= ' '.$lastName;
    }

The command can now be used in either of the following ways:

.. code-block:: bash

    $ app/console demo:greet Fabien
    $ app/console demo:greet Fabien Potencier

It is also possible to let an argument take a list of values (imagine you want
to greet all your friends). For this it must be specified at the end of the
argument list::

    $this
        // ...
        ->addArgument(
            'names',
            InputArgument::IS_ARRAY,
            'Who do you want to greet (separate multiple names with a space)?'
        );

To use this, just specify as many names as you want:

.. code-block:: bash

    $ app/console demo:greet Fabien Ryan Bernhard

You can access the ``names`` argument as an array::

    if ($names = $input->getArgument('names')) {
        $text .= ' '.implode(', ', $names);
    }

There are 3 argument variants you can use:

===========================  ===============================================================================================================
Mode                         Value
===========================  ===============================================================================================================
InputArgument::REQUIRED      The argument is required
InputArgument::OPTIONAL      The argument is optional and therefore can be omitted
InputArgument::IS_ARRAY      The argument can contain an indefinite number of arguments and must be used at the end of the argument list
===========================  ===============================================================================================================

You can combine ``IS_ARRAY`` with ``REQUIRED`` and ``OPTIONAL`` like this::

    $this
        // ...
        ->addArgument(
            'names',
            InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            'Who do you want to greet (separate multiple names with a space)?'
        );

Using Command Options
---------------------

Unlike arguments, options are not ordered (meaning you can specify them in any
order) and are specified with two dashes (e.g. ``--yell`` - you can also
declare a one-letter shortcut that you can call with a single dash like
``-y``). Options are *always* optional, and can be setup to accept a value
(e.g. ``dir=src``) or simply as a boolean flag without a value (e.g.
``yell``).

.. tip::

    It is also possible to make an option *optionally* accept a value (so that
    ``--yell`` or ``yell=loud`` work). Options can also be configured to
    accept an array of values.

For example, add a new option to the command that can be used to specify
how many times in a row the message should be printed::

    $this
        // ...
        ->addOption(
            'iterations',
            null,
            InputOption::VALUE_REQUIRED,
            'How many times should the message be printed?',
            1
        );

Next, use this in the command to print the message multiple times:

.. code-block:: php

    for ($i = 0; $i < $input->getOption('iterations'); $i++) {
        $output->writeln($text);
    }

Now, when you run the task, you can optionally specify a ``--iterations``
flag:

.. code-block:: bash

    $ app/console demo:greet Fabien
    $ app/console demo:greet Fabien --iterations=5

The first example will only print once, since ``iterations`` is empty and
defaults to ``1`` (the last argument of ``addOption``). The second example
will print five times.

Recall that options don't care about their order. So, either of the following
will work:

.. code-block:: bash

    $ app/console demo:greet Fabien --iterations=5 --yell
    $ app/console demo:greet Fabien --yell --iterations=5

There are 4 option variants you can use:

===========================  =====================================================================================
Option                       Value
===========================  =====================================================================================
InputOption::VALUE_IS_ARRAY  This option accepts multiple values (e.g. ``--dir=/foo --dir=/bar``)
InputOption::VALUE_NONE      Do not accept input for this option (e.g. ``--yell``)
InputOption::VALUE_REQUIRED  This value is required (e.g. ``--iterations=5``), the option itself is still optional
InputOption::VALUE_OPTIONAL  This option may or may not have a value (e.g. ``yell`` or ``yell=loud``)
===========================  =====================================================================================

You can combine ``VALUE_IS_ARRAY`` with ``VALUE_REQUIRED`` or ``VALUE_OPTIONAL`` like this:

.. code-block:: php

    $this
        // ...
        ->addOption(
            'iterations',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'How many times should the message be printed?',
            1
        );

Console Helpers
---------------

The console component also contains a set of "helpers" - different small
tools capable of helping you with different tasks:

* :doc:`/components/console/helpers/dialoghelper`: interactively ask the user for information
* :doc:`/components/console/helpers/formatterhelper`: customize the output colorization
* :doc:`/components/console/helpers/progresshelper`: shows a progress bar

Testing Commands
----------------

Symfony2 provides several tools to help you test your commands. The most
useful one is the :class:`Symfony\\Component\\Console\\Tester\\CommandTester`
class. It uses special input and output classes to ease testing without a real
console::

    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Tester\CommandTester;
    use Acme\DemoBundle\Command\GreetCommand;

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

    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Tester\CommandTester;
    use Acme\DemoBundle\Command\GreetCommand;

    class ListCommandTest extends \PHPUnit_Framework_TestCase
    {
        // ...

        public function testNameIsOutput()
        {
            $application = new Application();
            $application->add(new GreetCommand());

            $command = $application->find('demo:greet');
            $commandTester = new CommandTester($command);
            $commandTester->execute(
                array('command' => $command->getName(), 'name' => 'Fabien')
            );

            $this->assertRegExp('/Fabien/', $commandTester->getDisplay());
        }
    }

.. tip::

    You can also test a whole console application by using
    :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester`.

Calling an existing Command
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

Learn More!
-----------

* :doc:`/components/console/usage`
* :doc:`/components/console/single_command_tool`

.. _Packagist: https://packagist.org/packages/symfony/console
.. _ANSICON: http://adoxa.3eeweb.com/ansicon/
