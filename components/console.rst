.. index::
    single: Console; CLI

The Console Component
=====================

    The Console component eases the creation of beautiful and testable command
    line interfaces.

Symfony2 ships with a Console component, which allows you to create
command-line commands. Your console commands can be used for any recurring
task, such as cronjobs, imports, or other batch jobs.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Console);
* Install it via PEAR ( `pear.symfony.com/Console`);
* Install it via Composer (`symfony/console` on Packagist).

Creating a basic Command
------------------------

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

Test the new console command by running the following

.. code-block:: bash

    app/console demo:greet Fabien

This will print the following to the command line:

.. code-block:: text

    Hello Fabien

You can also use the ``--yell`` option to make everything uppercase:

.. code-block:: bash

    app/console demo:greet Fabien --yell

This prints::

    HELLO FABIEN

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

Using Command Arguments
-----------------------

The most interesting part of the commands are the arguments and options that
you can make available. Arguments are the strings - separated by spaces - that
come after the command name itself. They are ordered, and can be optional
or required. For example, add an optional ``last_name`` argument to the command
and make the ``name`` argument required::

    $this
        // ...
        ->addArgument('name', InputArgument::REQUIRED, 'Who do you want to greet?')
        ->addArgument('last_name', InputArgument::OPTIONAL, 'Your last name?')
        // ...

You now have access to a ``last_name`` argument in your command::

    if ($lastName = $input->getArgument('last_name')) {
        $text .= ' '.$lastName;
    }

The command can now be used in either of the following ways:

.. code-block:: bash

    app/console demo:greet Fabien
    app/console demo:greet Fabien Potencier

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
        ->addOption('iterations', null, InputOption::VALUE_REQUIRED, 'How many times should the message be printed?', 1)

Next, use this in the command to print the message multiple times:

.. code-block:: php

    for ($i = 0; $i < $input->getOption('iterations'); $i++) {
        $output->writeln($text);
    }

Now, when you run the task, you can optionally specify a ``--iterations``
flag:

.. code-block:: bash

    app/console demo:greet Fabien

    app/console demo:greet Fabien --iterations=5

The first example will only print once, since ``iterations`` is empty and
defaults to ``1`` (the last argument of ``addOption``). The second example
will print five times.

Recall that options don't care about their order. So, either of the following
will work:

.. code-block:: bash

    app/console demo:greet Fabien --iterations=5 --yell
    app/console demo:greet Fabien --yell --iterations=5

There are 4 option variants you can use:

===========================  =====================================================
Option                       Value
===========================  =====================================================
InputOption::VALUE_IS_ARRAY  This option accepts multiple values
InputOption::VALUE_NONE      Do not accept input for this option (e.g. ``--yell``)
InputOption::VALUE_REQUIRED  This value is required (e.g. ``iterations=5``)
InputOption::VALUE_OPTIONAL  This value is optional
===========================  =====================================================

You can combine VALUE_IS_ARRAY with VALUE_REQUIRED or VALUE_OPTIONAL like this:

.. code-block:: php

    $this
        // ...
        ->addOption('iterations', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'How many times should the message be printed?', 1)

Asking the User for Information
-------------------------------

When creating commands, you have the ability to collect more information
from the user by asking him/her questions. For example, suppose you want
to confirm an action before actually executing it. Add the following to your
command::

    $dialog = $this->getHelperSet()->get('dialog');
    if (!$dialog->askConfirmation($output, '<question>Continue with this action?</question>', false)) {
        return;
    }

In this case, the user will be asked "Continue with this action", and unless
they answer with ``y``, the task will stop running. The third argument to
``askConfirmation`` is the default value to return if the user doesn't enter
any input.

You can also ask questions with more than a simple yes/no answer. For example,
if you needed to know the name of something, you might do the following::

    $dialog = $this->getHelperSet()->get('dialog');
    $name = $dialog->ask($output, 'Please enter the name of the widget', 'foo');

Testing Commands
----------------

Symfony2 provides several tools to help you test your commands. The most
useful one is the :class:`Symfony\\Component\\Console\\Tester\\CommandTester`
class. It uses special input and output classes to ease testing without a real
console::

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

The :method:`Symfony\\Component\\Console\\Tester\\CommandTester::getDisplay`
method returns what would have been displayed during a normal call from the
console.

.. tip::

    You can also test a whole console application by using
    :class:`Symfony\\Component\\Console\\Tester\\ApplicationTester`.

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

First, you :method:`Symfony\\Component\\Console\\Command\\Command::find` the
command you want to execute by passing the command name.

Then, you need to create a new
:class:`Symfony\\Component\\Console\\Input\\ArrayInput` with the arguments and
options you want to pass to the command.

Eventually, calling the ``run()`` method actually executes the command and
returns the returned code from the command (``0`` if everything went fine, any
other integer otherwise).

.. note::

    Most of the time, calling a command from code that is not executed on the
    command line is not a good idea for several reasons. First, the command's
    output is optimized for the console. But more important, you can think of
    a command as being like a controller; it should use the model to do
    something and display feedback to the user. So, instead of calling a
    command from the Web, refactor your code and move the logic to a new
    class.
