How to Create Console/Command-Line Commands
===========================================

Symfony2 ships with a console component, which allows you to create command-line
tasks inside a rich and powerful framework. Your console commands can be used
for any recurring task, such as cronjobs, imports or other batch jobs.

Creating a Basic Command
------------------------

To make the console commands available automatically with Symfony2, create
a ``Command`` directory inside your bundle and create a php file suffixed
with ``Command.php`` for each task that you want to provide. For example,
if you want to extend the ``AcmeDemoBundle`` (available in the Symfony Standard
Edition) to greet us from the command line, create ``GreetCommand.php`` and
add the following to it:

.. code-block:: php

    // src/Acme/DemoBundle/Command/GreetCommand.php
    namespace Acme\DemoBundle\Command;

    use Symfony\Bundle\FrameworkBundle\Command\Command;
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
                ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
                ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $name = $input->getArgument('name');
            if ($name) {
                $text = 'Hello ' . $name;
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

    $ php app/console demo:greet Fabien

This will print the following to the command line::

    Hello Fabien

You can also use the ``--yell`` option to make everything uppercase:

.. code-block:: bash

    $ php app/console demo:greet Fabien --yell

This prints::

    HELLO FABIEN

Command Arguments
~~~~~~~~~~~~~~~~~

The most interesting part of the commands are the arguments and options that
you can make available. Arguments are the strings - separated by spaces - that
come after the command name itself. They are ordered, and can be optional
or required. For example, add an optional ``last_name`` argument to the command
and make the ``name`` argument required:

.. code-block:: php

    $this
        // ...
        ->addArgument('name', InputArgument::REQUIRED, 'Who do you want to greet?')
        ->addArgument('last_name', InputArgument::OPTIONAL, 'Your last name?')
        // ...

You now have access to a ``last_name`` argument in your command:

.. code-block:: php

    if ($lastName = $input->getArgument('last_name')) {
        $text .= ' ' . $lastName;
    }

The command can now be used in either of the following ways:

.. code-block:: bash

    $ php app/console demo:greet Fabien
    $ php app/console demo:greet Fabien Potencier

Command Options
---------------

Unlike arguments, options are not ordered (meaning you can specify them in
any order) and are specified with two dashes (e.g. ``--yell``). Options are
*always* optional, and can be setup to accept a value (e.g. ``dir=src``)
or simply as a boolean flag without a value (e.g. ``yell``).

.. tip::

    It's also possible to make an option *optionally* accept a value (so
    that ``--yell`` or ``yell=loud`` work). Options can also be configured
    to accept an array of values.

For example, add a new option to the command that can be used to specify
how many times in a row the message should be printed:

.. code-block:: php

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

    $ php app/console demo:greet Fabien

    $ php app/console demo:greet Fabien --iterations=5

The first example will only print once, since ``iterations`` is empty and
defaults to ``1`` (the last argument of ``addOption``). The second example
will print five times.

Recall that options don't care about their order. So, either of the following
will work:

.. code-block:: bash

    $ php app/console demo:greet Fabien --iterations=5 --yell
    $ php app/console demo:greet Fabien --yell --iterations=5

Advanced Usage with the Service Container
-----------------------------------------

By Using ``Symfony\Bundle\FrameworkBundle\Command\Command`` as the base class
for the command (instead of the more basic ``Symfony\Component\Console\Command\Command``),
you have access to the service container. In other words, you have access
to use any service configured to work inside Symfony. For example, you could
easily extend the task to be translatable:

.. code-block:: php

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $translator = $this->container->get('translator');
        if ($name) {
            $output->writeln($translator->trans('Hello %name%!', array('%name%' => $name)));
        } else {
            $output->writeln($translator->trans('Hello!'));
        }
    }