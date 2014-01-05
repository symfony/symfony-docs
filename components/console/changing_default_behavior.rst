.. index::
   single: Console; Changing the Default Behavior

Changing the Default Behavior
=============================

When building a command line tool, you may need to customize it to fit your needs.
Probably you want to change the default command that the Application runs or
maybe you just want to run a single command instead of have to pass the command
name each time. Fortunately, it is possible to do both.

Changing the Default Command
----------------------------

.. versionadded:: 2.5,
    The :method:`Symfony\\Component\\Console\\Application::setDefaultCommand`
    method was introduced in version 2.5.

By default the Application will always run the ListCommand. In order to change
the default command you just need to pass the command name you want to run by
default to the ``setDefaultCommand`` method::

    #!/usr/bin/env php
    <?php
    // application.php

    use Acme\Command\GreetCommand;
    use Symfony\Component\Console\Application;

    $command = new GreetCommand();
    $application = new Application();
    $application->add($command);
    $application->setDefaultCommand($command->getName());
    $application->run()

Test the new console command by running the following

.. code-block:: bash

    $ app/console Fabien

This will print the following to the command line:

.. code-block:: text

    Hello Fabien

Building a Single Command Application
-------------------------------------

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

When calling your console script, the ``MyCommand`` command will then always
be used, without having to pass its name.

Executing the application can also be simplified::

    #!/usr/bin/env php
    <?php
    // command.php
    use Acme\Tool\MyApplication;

    $application = new MyApplication();
    $application->run();
