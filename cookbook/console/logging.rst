.. index::
   single: Console; Enabling logging

How to enable logging in Console Commands
=========================================

The Console component doesn't provide any logging capabilities out of the box.
Normally, you run console commands manually and observe the output, that's
why logging is not provided. However, there are cases when you might need
logging. For example, if you are running console commands unattended, such
as from cron jobs or deployment scripts it may be easier to use Symfony's
logging capabilities instead of configuring other tools to gather console
output and process it. This can be especially handful if you already have
some existing setup for aggregating and analyzing Symfony logs.

There are basically two logging cases you would need:
 * Manually logging some information from your command;
 * Logging uncaught Exceptions.

Manually logging from console command
-------------------------------------

This one is really simple. When you create console command within full framewok
as described :doc:`here</cookbook/console/console_command>`, your command
extends :class:`Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand`,
so you can simply access standard logger service through the container and
use it to do the logging::

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

Depending on the environment you run your command you will get the results
in ``app/dev.log`` or ``app/prod.log``.

Enabling automatic Exceptions logging
-------------------------------------

In order to enable console application to automatically log uncaught exceptions
for all commands you'd need to do something more.

First, you have to extend :class:`Symfony\\Bundle\\FrameworkBundle\\Console\\Application`
class to override its :method:`Symfony\\Bundle\\FrameworkBundle\\Console\\Application::run`
method, where exception handling should happen::

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
            //make parent method throw exceptions, so we can log it
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
            // parent property is private, so we need to intercept it in setter
            $this->originalAutoExit = (Boolean) $bool;
            parent::setAutoExit($bool);
        }

    }

What happens above is we disable exception catching, so that parent run method
would throw the exceptions. When exception is caught, we simple log it by
accessing the ``logger`` service from the service container and then handle
the rest in the same way parent run method does that (Since parent :method:`run<Symfony\\Bundle\\FrameworkBundle\\Console\\Application::run>`
method will not handle exceptions rendering and status code handling when
`catchExceptions` is set to false, it has to be done in the overridden
method).

For our extended Application class to work properly with console shell mode
we have to do a small trick to intercept ``autoExit`` setter, and store the
setting in a different property, since the parent property is private.

Now to be able to use our extended ``Application`` class we need to adjust
``app/console`` script to use our class instead of the default::

    // app/console

    // ...
    // replace the following line:
    // use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Acme\DemoBundle\Console\Application;

    // ...

That's it! Thanks to autoloader, our class will now be used instead of original
one.


Logging non-0 exit statuses
---------------------------

The logging capabilities of the console can be further extended by logging
non-0 exit statuses. This way you will know if a command had any errors, even
if no exceptions were thrown.

In order to do that, you'd have to modify ``run()`` method of your extended
`Application` class in the following way::

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        //make parent method throw exceptions, so we can log it
        $this->setCatchExceptions(false);

        // store autoExit value before resetting it - we'd need it later
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



