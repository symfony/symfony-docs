Verbosity Levels
================

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

It is possible to print a message in a command for only a specific verbosity
level. For example::

    // ...
    class CreateUserCommand extends Command
    {
        // ...

        public function execute(InputInterface $input, OutputInterface $output)
        {
            $user = new User(...);

            $output->writeln(array(
                'Username: '.$input->getArgument('username'),
                'Password: '.$input->getArgument('password'),
            ));

            // the user class is only printed when the verbose verbosity level is used
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln('User class: '.get_class($user));
            }

            // alternatively you can pass the verbosity level to writeln()
            $output->writeln(
                'Will only be printed in verbose mode or higher',
                OutputInterface::VERBOSITY_VERBOSE
            );
        }
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

.. tip::

    The full exception stacktrace is printed if the ``VERBOSITY_VERBOSE``
    level or above is used.
