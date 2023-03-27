Using the Logger
================

The Console component comes with a standalone logger complying with the
`PSR-3`_ standard. Depending on the verbosity setting, log messages will
be sent to the :class:`Symfony\\Component\\Console\\Output\\OutputInterface`
instance passed as a parameter to the constructor.

The logger does not have any external dependency except ``psr/log``.
This is useful for console applications and commands needing a lightweight
PSR-3 compliant logger::

    namespace Acme;

    use Psr\Log\LoggerInterface;

    class MyDependency
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        public function doStuff()
        {
            $this->logger->info('I love Tony Vairelles\' hairdresser.');
        }
    }

You can rely on the logger to use this dependency inside a command::

    namespace Acme\Console\Command;

    use Acme\MyDependency;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Logger\ConsoleLogger;
    use Symfony\Component\Console\Output\OutputInterface;

    class MyCommand extends Command
    {
        protected static $defaultName = 'my:command';

        protected function configure()
        {
            $this
                ->setDescription(
                    'Use an external dependency requiring a PSR-3 logger'
                )
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $logger = new ConsoleLogger($output);

            $myDependency = new MyDependency($logger);
            $myDependency->doStuff();
        }
    }

The dependency will use the instance of
:class:`Symfony\\Component\\Console\\Logger\\ConsoleLogger` as logger.
Log messages emitted will be displayed on the console output.

Verbosity
---------

Depending on the verbosity level that the command is run, messages may or
may not be sent to the :class:`Symfony\\Component\\Console\\Output\\OutputInterface`
instance.

By default, the console logger behaves like the
:doc:`Monolog's Console Handler </logging/monolog_console>`.
The association between the log level and the verbosity can be configured
through the second parameter of the :class:`Symfony\\Component\\Console\\Logger\\ConsoleLogger`
constructor::

    use Psr\Log\LogLevel;
    // ...

    $verbosityLevelMap = [
        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
    ];

    $logger = new ConsoleLogger($output, $verbosityLevelMap);

Color
-----

The logger outputs the log messages formatted with a color reflecting their
level. This behavior is configurable through the third parameter of the
constructor::

    // ...
    $formatLevelMap = [
        LogLevel::CRITICAL => ConsoleLogger::ERROR,
        LogLevel::DEBUG    => ConsoleLogger::INFO,
    ];

    $logger = new ConsoleLogger($output, [], $formatLevelMap);

Errors
------

The Console logger includes a ``hasErrored()`` method which returns ``true`` as
soon as any error message has been logged during the execution of the command.
This is useful to decide which status code to return as the result of executing
the command.

.. _PSR-3: https://www.php-fig.org/psr/psr-3/
