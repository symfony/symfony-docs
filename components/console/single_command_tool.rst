.. index::
    single: Console; Single command application

Building a single Command Application
=====================================

When building a command line tool, you may not need to provide several commands.
In such case, having to pass the command name each time is tedious. Fortunately,
it is possible to remove this need by extending the application::

    namespace Acme\Tool;

    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Input\InputInterface;

    class MyApplication extends Application
    {
        /**
         * Gets the name of the command based on input.
         *
         * @param InputInterface $input The input interface
         *
         * @return string The command name
         */
        protected function getCommandName(InputInterface $input)
        {
            // This should return the name of your command.
            return 'my_command';
        }

        /**
         * Gets the default commands that should always be available.
         *
         * @return array An array of default Command instances
         */
        protected function getDefaultCommands()
        {
            // Keep the core default commands to have the HelpCommand
            // which is used when using the --help option
            $defaultCommands = parent::getDefaultCommands();

            $defaultCommands[] = new MyCommand();

            return $defaultCommands;
        }

        /**
         * Overridden so that the application doesn't expect the command
         * name to be the first argument.
         */
        public function getDefinition()
        {
            $inputDefinition = parent::getDefinition();
            // clear out the normal first argument, which is the command name
            $inputDefinition->setArguments();

            return $inputDefinition;
        }
    }

When calling your console script, the command ``MyCommand`` will then always
be used, without having to pass its name.

You can also simplify how you execute the application::

    #!/usr/bin/env php
    <?php
    // command.php

    use Acme\Tool\MyApplication;

    $application = new MyApplication();
    $application->run();
