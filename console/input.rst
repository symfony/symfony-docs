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
    use Symfony\Component\Console\Input\InputArgument;

    class GreetCommand extends Command
    {
        // ...

        protected function configure()
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
    class GreetCommand extends Command
    {
        // ...

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $text = 'Hi '.$input->getArgument('name');

            $lastName = $input->getArgument('last_name');
            if ($lastName) {
                $text .= ' '.$lastName;
            }

            $output->writeln($text.'!');
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
        );

To use this, just specify as many names as you want:

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
order) and are specified with two dashes (e.g. ``--yell``). Options are
*always* optional, and can be setup to accept a value (e.g. ``--dir=src``) or
simply as a boolean flag without a value (e.g.  ``--yell``).

For example, add a new option to the command that can be used to specify
how many times in a row the message should be printed::

    // ...
    use Symfony\Component\Console\Input\InputOption;

    $this
        // ...
        ->addOption(
            'iterations',
            null,
            InputOption::VALUE_REQUIRED,
            'How many times should the message be printed?',
            1
        );

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
    Hi Fabien
    Hi Fabien
    Hi Fabien
    Hi Fabien
    Hi Fabien

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
            );

There are four option variants you can use:

``InputOption::VALUE_IS_ARRAY``
    This option accepts multiple values (e.g. ``--dir=/foo --dir=/bar``);

``InputOption::VALUE_NONE``
    Do not accept input for this option (e.g. ``--yell``). This is the default
    behavior of options;

``InputOption::VALUE_REQUIRED``
    This value is required (e.g. ``--iterations=5``), the option itself is
    still optional;

``InputOption::VALUE_OPTIONAL``
    This option may or may not have a value (e.g. ``--yell`` or
    ``--yell=loud``).

You can combine ``VALUE_IS_ARRAY`` with ``VALUE_REQUIRED`` or
``VALUE_OPTIONAL`` like this::

    $this
        // ...
        ->addOption(
            'colors',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Which colors do you like?',
            array('blue', 'red')
        );

.. tip::

    There is nothing forbidding you to create a command with an option that
    optionally accepts a value. However, there is no way you can distinguish
    when the option was used without a value (``command --language``) or when
    it wasn't used at all (``command``). In both cases, the value retrieved for
    the option will be ``null``.

    Similarly, due to a PHP limitation, there is no way to pass an empty string
    as the value of an option. In ``command --prefix`` and ``command --prefix=''``
    cases, the value of the ``prefix`` option will be ``null``.
