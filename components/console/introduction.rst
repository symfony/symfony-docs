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
* Use the official Git repository (https://github.com/symfony/Console).

Usage
-----

The backbone of a Symfony Console application is the
:class:`Symfony\\Component\\Console\\Application` class. This class will handle
the incomming arguments from the console interfaces, executed the matching
command and handle the output it creates.

To get started, you have to create your CLI front controller. For instance,
create a file named ``application.php``::

    #!/usr/bin/env php
    <?php
    // application.php

    require __DIR__.'/vendor/autoload.php';

    use Symfony\Component\Console\Application;

    $application = new Application();
    
    $application->run();

On line 1, a sebang is defined, which means unix PCs can just use
``./application.php`` instead of ``php ./application.php`` on the console
interface.

Then, you construct a new application and run it. The run method will start
handling the incomming arguments from the CLI.

You can now test this application by running it from the CLI:

.. code-block:: bash

    # on unix-based PCs
    $ ./application.php

    # on Windows
    $ php application.php

This will now give you the help output of the application. As you can see,
there are 2 default commands available: *list* (which lists all available
commands) and *help* (which displays help information about a command).

Creating Commands
~~~~~~~~~~~~~~~~~

The application isn't very usefull if you don't create your own commands. You
can create one by creating a command that extends
:class:`Symfony\\Component\\Console\\Command\\Command`::

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class HelloCommand extends Command
    {
        public function configure()
        {
            $this->setName('hello')
                ->addArgument('name')
            ;
        }

        public function execute(InputInterface $input, OutputInterface $output)
        {
            $name = $input->getArgument('name');
            if (null === $name) {
                // no name was giving
                $name = 'World';
            }

            $output->writeln('Hello '.$name);
        }
    }

This is a very basic command. In the
:method:`Symfony\\Component\\Console\\Command\\Command::configure` method, you
configure the command by defining its name (used to reference it in the CLI)
and an argument.

Then, when the command is matched, the
:method:`Symfony\\Component\\Console\\Command\\Command::execute` method is
called. As the console is a stream, it gets an ``$input`` and ``$output``. The
command reads the passed argument from the input and prints a message (and new
line feed) to the output.

Now the command is created, you can add it to the application::

    #!/usr/bin/env php
    <?php
    // application.php

    // ...
    $application->add(new HelloCommand());
    
    $application->run();

It's now ready to use!

.. code-block:: bash

    $ php application.php hello Wouter
    Hello Wouter

    $ php application.php hello
    Hello World

As you can see, the first argument passed to the file is the name of the
command. Everything after it is part of the input of the command.

Learning More
-------------

You've touched the top layer of the Console component. It can do lots of more
stuff. You can learn more in the next chapters:

* :doc:`/components/console/commands`
* :doc:`/components/console/usage`
* :doc:`/components/console/single_command_tool`
* :doc:`/components/console/events`
* :doc:`/components/console/console_arguments`

.. _Packagist: https://packagist.org/packages/symfony/console
