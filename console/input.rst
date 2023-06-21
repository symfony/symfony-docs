Console Input (Arguments & Options)
===================================

The most interesting part of the commands are the arguments and options that
you can make available. These arguments and options allow you to pass dynamic
information from the terminal to the command.

Using Command Arguments
-----------------------

Arguments are the strings - separated by spaces - that
come after the command name itself. They are ordered, and can be optional
or required. For example, to add an optional ``last_name`` argument to the command
and make the ``name`` argument required::

    // ...
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;

    class GreetCommand extends Command
    {
        // ...

        protected function configure(): void
        {
            $this
                // ...
                ->addArgument('name', InputArgument::REQUIRED, 'Who do you want to greet?')
                ->addArgument('last_name', InputArgument::OPTIONAL, 'Your last name?')
            ;
        }
    }

You now have access to a ``last_name`` argument in your command::

    // ...
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class GreetCommand extends Command
    {
        // ...

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $text = 'Hi '.$input->getArgument('name');

            $lastName = $input->getArgument('last_name');
            if ($lastName) {
                $text .= ' '.$lastName;
            }

            $output->writeln($text.'!');

            return Command::SUCCESS;
        }
    }

The command can now be used in either of the following ways:

.. code-block:: terminal

    $ php bin/console app:greet Fabien
    Hi Fabien!

    $ php bin/console app:greet Fabien Potencier
    Hi Fabien Potencier!

It is also possible to let an argument take a list of values (imagine you want
to greet all your friends). Only the last argument can be a list::

    $this
        // ...
        ->addArgument(
            'names',
            InputArgument::IS_ARRAY,
            'Who do you want to greet (separate multiple names with a space)?'
        )
    ;

To use this, specify as many names as you want:

.. code-block:: terminal

    $ php bin/console app:greet Fabien Ryan Bernhard

You can access the ``names`` argument as an array::

    $names = $input->getArgument('names');
    if (count($names) > 0) {
        $text .= ' '.implode(', ', $names);
    }

There are three argument variants you can use:

``InputArgument::REQUIRED``
    The argument is mandatory. The command doesn't run if the argument isn't
    provided;

``InputArgument::OPTIONAL``
    The argument is optional and therefore can be omitted. This is the default
    behavior of arguments;

``InputArgument::IS_ARRAY``
    The argument can contain any number of values. For that reason, it must be
    used at the end of the argument list.

You can combine ``IS_ARRAY`` with ``REQUIRED`` or ``OPTIONAL`` like this::

    $this
        // ...
        ->addArgument(
            'names',
            InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            'Who do you want to greet (separate multiple names with a space)?'
        )
    ;

Using Command Options
---------------------

Unlike arguments, options are not ordered (meaning you can specify them in any
order) and are specified with two dashes (e.g. ``--yell``). Options are
*always* optional, and can be setup to accept a value (e.g. ``--dir=src``) or
as a boolean flag without a value (e.g.  ``--yell``).

For example, add a new option to the command that can be used to specify
how many times in a row the message should be printed::

    // ...
    use Symfony\Component\Console\Input\InputOption;

    $this
        // ...
        ->addOption(
            // this is the name that users must type to pass this option (e.g. --iterations=5)
            'iterations',
            // this is the optional shortcut of the option name, which usually is just a letter
            // (e.g. `i`, so users pass it as `-i`); use it for commonly used options
            // or options with long names
            null,
            // this is the type of option (e.g. requires a value, can be passed more than once, etc.)
            InputOption::VALUE_REQUIRED,
            // the option description displayed when showing the command help
            'How many times should the message be printed?',
            // the default value of the option (for those which allow to pass values)
            1
        )
    ;

Next, use this in the command to print the message multiple times::

    for ($i = 0; $i < $input->getOption('iterations'); $i++) {
        $output->writeln($text);
    }

Now, when you run the command, you can optionally specify a ``--iterations``
flag:

.. code-block:: terminal

    # no --iterations provided, the default (1) is used
    $ php bin/console app:greet Fabien
    Hi Fabien!

    $ php bin/console app:greet Fabien --iterations=5
    Hi Fabien!
    Hi Fabien!
    Hi Fabien!
    Hi Fabien!
    Hi Fabien!

    # the order of options isn't important
    $ php bin/console app:greet Fabien --iterations=5 --yell
    $ php bin/console app:greet Fabien --yell --iterations=5
    $ php bin/console app:greet --yell --iterations=5 Fabien

.. tip::

    You can also declare a one-letter shortcut that you can call with a single
    dash, like ``-i``::

        $this
            // ...
            ->addOption(
                'iterations',
                'i',
                InputOption::VALUE_REQUIRED,
                'How many times should the message be printed?',
                1
            )
        ;

Note that to comply with the `docopt standard`_, long options can specify their
values after a whitespace or an ``=`` sign (e.g. ``--iterations 5`` or
``--iterations=5``), but short options can only use whitespaces or no
separation at all (e.g. ``-i 5`` or ``-i5``).

.. caution::

    While it is possible to separate an option from its value with a whitespace,
    using this form leads to an ambiguity should the option appear before the
    command name. For example, ``php bin/console --iterations 5 app:greet Fabien``
    is ambiguous; Symfony would interpret ``5`` as the command name. To avoid
    this situation, always place options after the command name, or avoid using
    a space to separate the option name from its value.

There are five option variants you can use:

``InputOption::VALUE_IS_ARRAY``
    This option accepts multiple values (e.g. ``--dir=/foo --dir=/bar``);

``InputOption::VALUE_NONE``
    Do not accept input for this option (e.g. ``--yell``). The value returned
    from is a boolean (``false`` if the option is not provided).
    This is the default behavior of options;

``InputOption::VALUE_REQUIRED``
    This value is required (e.g. ``--iterations=5`` or ``-i5``), the option
    itself is still optional;

``InputOption::VALUE_OPTIONAL``
    This option may or may not have a value (e.g. ``--yell`` or
    ``--yell=loud``).

``InputOption::VALUE_NEGATABLE``
    Accept either the flag (e.g. ``--yell``) or its negation (e.g.
    ``--no-yell``).

You need to combine ``VALUE_IS_ARRAY`` with ``VALUE_REQUIRED`` or
``VALUE_OPTIONAL`` like this::

    $this
        // ...
        ->addOption(
            'colors',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Which colors do you like?',
            ['blue', 'red']
        )
    ;

Options with optional arguments
-------------------------------

There is nothing forbidding you to create a command with an option that
optionally accepts a value, but it's a bit tricky. Consider this example::

    // ...
    use Symfony\Component\Console\Input\InputOption;

    $this
        // ...
        ->addOption(
            'yell',
            null,
            InputOption::VALUE_OPTIONAL,
            'Should I yell while greeting?'
        )
    ;

This option can be used in 3 ways: ``greet --yell``, ``greet --yell=louder``,
and ``greet``. However, it's hard to distinguish between passing the option
without a value (``greet --yell``) and not passing the option (``greet``).

To solve this issue, you have to set the option's default value to ``false``::

    // ...
    use Symfony\Component\Console\Input\InputOption;

    $this
        // ...
        ->addOption(
            'yell',
            null,
            InputOption::VALUE_OPTIONAL,
            'Should I yell while greeting?',
            false // this is the new default value, instead of null
        )
    ;

Now it's possible to differentiate between not passing the option and not
passing any value for it::

    $optionValue = $input->getOption('yell');
    if (false === $optionValue) {
        // in this case, the option was not passed when running the command
        $yell = false;
        $yellLouder = false;
    } elseif (null === $optionValue) {
        // in this case, the option was passed when running the command
        // but no value was given to it
        $yell = true;
        $yellLouder = false;
    } else {
        // in this case, the option was passed when running the command and
        // some specific value was given to it
        $yell = true;
        if ('louder' === $optionValue) {
            $yellLouder = true;
        } else {
            $yellLouder = false;
        }
    }

The above code can be simplified as follows because ``false !== null``::

    $optionValue = $input->getOption('yell');
    $yell = ($optionValue !== false);
    $yellLouder = ($optionValue === 'louder');

Adding Argument/Option Value Completion
---------------------------------------

If :ref:`Console completion is installed <console-completion-setup>`,
command and option names will be auto completed by the shell. However, you
can also implement value completion for the input in your commands. For
instance, you may want to complete all usernames from the database in the
``name`` argument of your greet command.

To achieve this, use the 5th argument of ``addArgument()``/``addOption``::

    // ...
    use Symfony\Component\Console\Completion\CompletionInput;
    use Symfony\Component\Console\Completion\CompletionSuggestions;

    class GreetCommand extends Command
    {
        // ...
        protected function configure(): void
        {
            $this
                ->addArgument(
                    'names',
                    InputArgument::IS_ARRAY,
                    'Who do you want to greet (separate multiple names with a space)?',
                    null,
                    function (CompletionInput $input): array {
                        // the value the user already typed, e.g. when typing "app:greet Fa" before
                        // pressing Tab, this will contain "Fa"
                        $currentValue = $input->getCompletionValue();

                        // get the list of username names from somewhere (e.g. the database)
                        // you may use $currentValue to filter down the names
                        $availableUsernames = ...;

                        // then suggested the usernames as values
                        return $availableUsernames;
                    }
                )
            ;
        }
    }

.. versionadded:: 6.1

    The argument to ``addOption()``/``addArgument()`` was introduced in
    Symfony 6.1. Prior to this version, you had to override the
    ``complete()`` method of the command.

That's all you need! Assuming users "Fabien" and "Fabrice" exist, pressing
tab after typing ``app:greet Fa`` will give you these names as a suggestion.

.. tip::

    The shell script is able to handle huge amounts of suggestions and will
    automatically filter the suggested values based on the existing input
    from the user. You do not have to implement any filter logic in the
    command.

    You may use ``CompletionInput::getCompletionValue()`` to get the
    current input if that helps improving performance (e.g. by reducing the
    number of rows fetched from the database).

Testing the Completion script
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The Console component comes with a special
:class:`Symfony\\Component\\Console\\Tester\\CommandCompletionTester`` class
to help you unit test the completion logic::

    // ...
    use Symfony\Component\Console\Application;

    class GreetCommandTest extends TestCase
    {
        public function testComplete()
        {
            $application = new Application();
            $application->add(new GreetCommand());

            // create a new tester with the greet command
            $tester = new CommandCompletionTester($application->get('app:greet'));

            // complete the input without any existing input (the empty string represents
            // the position of the cursor)
            $suggestions = $tester->complete(['']);
            $this->assertSame(['Fabien', 'Fabrice', 'Wouter'], $suggestions);

            // If you filter the values inside your own code (not recommended, unless you
            // need to improve performance of e.g. a database query), you can test this
            // by passing the user input
            $suggestions = $tester->complete(['Fa']);
            $this->assertSame(['Fabien', 'Fabrice'], $suggestions);
        }
    }

.. _`docopt standard`: http://docopt.org/
