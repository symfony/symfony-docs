.. index::
   single: Console; Enabling logging

How to enable logging in Console Commands
=========================================

The Console component doesn't provide any logging capabilities out of the box.
Normally, you run console commands manually and observe the output, which is
why logging is not provided. However, there are cases when you might need
logging. For example, if you are running console commands unattended, such
as from cron jobs or deployment scripts, it may be easier to use Symfony's
logging capabilities instead of configuring other tools to gather console
output and process it. This can be especially handful if you already have
some existing setup for aggregating and analyzing Symfony logs.

There are basically two logging cases you would need:
 * Manually logging some information from your command;
 * Logging uncaught Exceptions.

Manually logging from a console Command
---------------------------------------

This one is really simple. When you create a console command within the full
framework as described in ":doc:`/cookbook/console/console_command`", your command
extends :class:`Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand`.
This means that you  can simply access the standard logger service through the
container and use it to do the logging::

    // src/Acme/DemoBundle/Command/GreetCommand.php
    namespace Acme\DemoBundle\Command;

    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\HttpKernel\Log\LoggerInterface;

    class GreetCommand extends ContainerAwareCommand
    {
        // ...

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            /** @var $logger LoggerInterface */
            $logger = $this->getContainer()->get('logger');

            $name = $input->getArgument('name');
            if ($name) {
                $text = 'Hello '.$name;
            } else {
                $text = 'Hello';
            }

            if ($input->getOption('yell')) {
                $text = strtoupper($text);
                $logger->warn('Yelled: '.$text);
            }
            else {
                $logger->info('Greeted: '.$text);
            }

            $output->writeln($text);
        }
    }

Depending on the environment in which you run your command (and your logging
setup), you should see the logged entries in ``app/logs/dev.log`` or ``app/logs/prod.log``.

Enabling automatic Exceptions logging
-------------------------------------

To get your console application to automatically log uncaught exceptions
for all of your commands, you'll need to do a little bit more work.

First, create a new sub-class of :class:`Symfony\\Bundle\\FrameworkBundle\\Console\\Application`
and override its :method:`Symfony\\Bundle\\FrameworkBundle\\Console\\Application::run`
method, where exception handling should happen:

.. caution::

    Due to the nature of the core :class:`Symfony\\Component\\Console\\Application`
    class, much of the :method:`run<Symfony\\Bundle\\FrameworkBundle\\Console\\Application::run>`
    method has to be duplicated and even a private property ``originalAutoExit``
    re-implemented. This serves as an example of what you *could* do in your
    code, though there is a high risk that something may break when upgrading
    to future versions of Symfony.

.. code-block:: php

    // src/Acme/DemoBundle/Console/Application.php
    namespace Acme\DemoBundle\Console;

    use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Output\ConsoleOutputInterface;
    use Symfony\Component\HttpKernel\Log\LoggerInterface;
    use Symfony\Component\HttpKernel\KernelInterface;
    use Symfony\Component\Console\Output\ConsoleOutput;
    use Symfony\Component\Console\Input\ArgvInput;

    class Application extends BaseApplication
    {
        private $originalAutoExit;

        public function __construct(KernelInterface $kernel)
        {
            parent::__construct($kernel);
            $this->originalAutoExit = true;
        }

        /**
         * Runs the current application.
         *
         * @param InputInterface  $input  An Input instance
         * @param OutputInterface $output An Output instance
         *
         * @return integer 0 if everything went fine, or an error code
         *
         * @throws \Exception When doRun returns Exception
         *
         * @api
         */
        public function run(InputInterface $input = null, OutputInterface $output = null)
        {
            // make the parent method throw exceptions, so you can log it
            $this->setCatchExceptions(false);

            if (null === $input) {
                $input = new ArgvInput();
            }

            if (null === $output) {
                $output = new ConsoleOutput();
            }

            try {
                $statusCode = parent::run($input, $output);
            } catch (\Exception $e) {

                /** @var $logger LoggerInterface */
                $logger = $this->getKernel()->getContainer()->get('logger');

                $message = sprintf(
                    '%s: %s (uncaught exception) at %s line %s while running console command `%s`',
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $this->getCommandName($input)
                );
                $logger->crit($message);

                if ($output instanceof ConsoleOutputInterface) {
                    $this->renderException($e, $output->getErrorOutput());
                } else {
                    $this->renderException($e, $output);
                }
                $statusCode = $e->getCode();

                $statusCode = is_numeric($statusCode) && $statusCode ? $statusCode : 1;
            }

            if ($this->originalAutoExit) {
                if ($statusCode > 255) {
                    $statusCode = 255;
                }
                // @codeCoverageIgnoreStart
                exit($statusCode);
                // @codeCoverageIgnoreEnd
            }

            return $statusCode;
        }

        public function setAutoExit($bool)
        {
            // parent property is private, so we need to intercept it in a setter
            $this->originalAutoExit = (Boolean) $bool;
            parent::setAutoExit($bool);
        }

    }

In the code above, you disable exception catching so the parent ``run`` method
will throw all exceptions. When an exception is caught, you simple log it by
accessing the ``logger`` service from the service container and then handle
the rest of the logic in the same way that the parent ``run`` method does
(specifically, since the parent :method:`run<Symfony\\Bundle\\FrameworkBundle\\Console\\Application::run>`
method will not handle exceptions rendering and status code handling when
``catchExceptions`` is set to false, it has to be done in the overridden
method).

For the extended Application class to work properly with in console shell mode,
you have to do a small trick to intercept the ``autoExit`` setter and store the
setting in a different property, since the parent property is private.

Now to be able to use your extended ``Application`` class you need to adjust
the ``app/console`` script to use the new class instead of the default::

    // app/console

    // ...
    // replace the following line:
    // use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Acme\DemoBundle\Console\Application;

    // ...

That's it! Thanks to autoloader, your class will now be used instead of original
one.

Logging non-0 exit statuses
---------------------------

The logging capabilities of the console can be further extended by logging
non-0 exit statuses. This way you will know if a command had any errors, even
if no exceptions were thrown.

In order to do that, you'd have to modify the ``run()`` method of your extended
``Application`` class in the following way::

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        // make the parent method throw exceptions, so you can log it
        $this->setCatchExceptions(false);

        // store the autoExit value before resetting it - you'll need it later
        $autoExit = $this->originalAutoExit;
        $this->setAutoExit(false);

        // ...

        if ($autoExit) {
            if ($statusCode > 255) {
                $statusCode = 255;
            }

            // log non-0 exit codes along with command name
            if ($statusCode !== 0) {
                /** @var $logger LoggerInterface */
                $logger = $this->getKernel()->getContainer()->get('logger');
                $logger->warn(sprintf('Command `%s` exited with status code %d', $this->getCommandName($input), $statusCode));
            }

            // @codeCoverageIgnoreStart
            exit($statusCode);
            // @codeCoverageIgnoreEnd
        }

        return $statusCode;
    }
