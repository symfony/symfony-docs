.. index::
   single: Logging; Console messages

How to Configure Monolog to Display Console Messages
====================================================

It is possible to use the console to print messages for certain
:doc:`verbosity levels </console/verbosity>` using the
:class:`Symfony\\Component\\Console\\Output\\OutputInterface` instance that
is passed when a command is run.

When a lot of logging has to happen, it is cumbersome to print information
depending on the verbosity settings (``-v``, ``-vv``, ``-vvv``) because the
calls need to be wrapped in conditions. For example::

    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->isDebug()) {
            $output->writeln('Some info');
        }

        if ($output->isVerbose()) {
            $output->writeln('Some more info');
        }
    }

Instead of using these semantic methods to test for each of the verbosity
levels, the `MonologBridge`_ provides a
:class:`Symfony\\Bridge\\Monolog\\Handler\\ConsoleHandler` that listens to
console events and writes log messages to the console output depending on
the current log level and the console verbosity.

The example above could then be rewritten as::

    // src/Command/YourCommand.php
    namespace App\Command;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class YourCommand extends Command
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $this->logger->debug('Some info');
            $this->logger->notice('Some more info');
        }
    }

Depending on the verbosity level that the command is run in and the user's
configuration (see below), these messages may or may not be displayed to
the console. If they are displayed, they are time-stamped and colored appropriately.
Additionally, error logs are written to the error output (``php://stderr``).
There is no need to conditionally handle the verbosity settings anymore.

===============  =======================================  ============
LoggerInterface  Verbosity                                Command line
===============  =======================================  ============
->error()        OutputInterface::VERBOSITY_QUIET         stderr
->warning()      OutputInterface::VERBOSITY_NORMAL        stdout
->notice()       OutputInterface::VERBOSITY_VERBOSE       -v
->info()         OutputInterface::VERBOSITY_VERY_VERBOSE  -vv
->debug()        OutputInterface::VERBOSITY_DEBUG         -vvv
===============  =======================================  ============

The Monolog console handler is enabled by default:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/monolog.yaml
        monolog:
            handlers:
                # ...
                console:
                    type:   console
                    process_psr_3_messages: false
                    channels: ['!event', '!doctrine', '!console']

                    # optionally configure the mapping between verbosity levels and log levels
                    # verbosity_levels:
                    #     VERBOSITY_NORMAL: NOTICE

    .. code-block:: xml

        <!-- config/packages/dev/monolog.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <monolog:config>
                <!-- ... -->

                <monolog:handler name="console" type="console" process-psr-3-messages="false">
                    <monolog:channels>
                        <monolog:channel>!event</monolog:channel>
                        <monolog:channel>!doctrine</monolog:channel>
                        <monolog:channel>!console</monolog:channel>
                    </monolog:channels>
                </monolog:handler>
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/dev/monolog.php
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog) {
            $monolog->handler('console')
                ->type('console')
                ->processPsr3Messages(false)
                ->channels()->elements(['!event', '!doctrine', '!console'])
            ;
        };

Now, log messages will be shown on the console based on the log levels and verbosity.
By default (normal verbosity level), warnings and higher will be shown. But in
:doc:`full verbosity mode </console/verbosity>`, all messages will be shown.

.. _MonologBridge: https://github.com/symfony/monolog-bridge
