Console Commands
================

Symfony2 ships with a console component. The framework offers a simple convention that allows
you to register your own console commands for any recurring task, cronjobs, imports or other batch-jobs.

To make the console commands available automatically with Symfony2 you have to create a ``Command``
directory inside your bundle and create a php file suffixed with ``Command.php`` for each
task that you want to provide. If we want to to extend the HelloBundle to greet us from the commandline
aswell we get this simple block of necessary code:

.. code-block: php

    <?php
    // lib/Acme/DemoBundle/Command/GreetCommand.php
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
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $name = $input->getArgument('name');
            if ($name) {
                $output->write('Hello ' . $name . '!');
            } else {
                $output->write('Hello!');
            }
        }
    }

We can now test the greeting by calling:

.. code-block:

    $> ./app/console demo:greet Fabien
    Hello Fabien!

Advanced Usage
--------------

Using the Symfony\Bundle\FrameworkBundle\Command\Command as base class we also have access to
the dependency injection container. As an example we could easily extend our task to be translatable:

.. code-block: php

    <?php
    // lib/Acme/DemoBundle/Command/GreetCommand.php

    // ...
    class GreetCommand extends Command
    {
        //...
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $name = $input->getArgument('name');
            $translator = $this->container->get('translator');
            if ($name) {
                $output->write($translator->trans('Hello ' . $name . '!'));
            } else {
                $output->write($translator->trans('Hello!'));
            }
        }
    }