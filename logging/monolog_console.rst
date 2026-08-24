How to Configure Monolog to Display Console Messages
====================================================

It is possible to use the console to print messages for certain
:doc:`verbosity levels </console/verbosity>` using the
:class:`Symfony\\Component\\Console\\Output\\OutputInterface` instance that
is passed when a command is run.

When a lot of logging has to happen, it's cumbersome to print information
depending on the verbosity settings (``-v``, ``-vv``, ``-vvv``) because the
calls need to be wrapped in conditions. For example::

    use Symfony\Component\Console\Output\OutputInterface;

    public function __invoke(OutputInterface $output): int
    {
        if ($output->isDebug()) {
            $output->writeln('Some info');
        }

        if ($output->isVerbose()) {
            $output->writeln('Some more info');
        }

        // ...
    }

Instead of using these semantic methods to test for each of the verbosity
levels, the `MonologBridge`_ provides a
:class:`Symfony\\Bridge\\Monolog\\Handler\\ConsoleHandler` that listens to
console events and writes log messages to the console output depending on
the current log level and the console verbosity.

The example above could then be rewritten as::

    // src/Command/MyCommand.php
    namespace App\Command;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:my-command')]
    class MyCommand
    {
        public function __construct(
            private LoggerInterface $logger,
        ) {
        }

        public function __invoke(): int
        {
            $this->logger->debug('Some info');
            $this->logger->notice('Some more info');

            return Command::SUCCESS;
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

        # config/packages/monolog.yaml
        when@dev:
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

    .. code-block:: php

        // config/packages/monolog.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'when@dev' => [
                'monolog' => [
                    'handlers' => [
                        'console' => [
                            'type' => 'console',
                            'process_psr_3_messages' => false,
                            'channels' => ['!event', '!doctrine', '!console'],

                            // optionally configure the mapping between verbosity levels and log levels
                            // 'verbosity_levels' => [
                            //     'VERBOSITY_NORMAL' => 'NOTICE',
                            // ],
                        ],
                    ],
                ],
            ],
        ]);

.. note::

    In this configuration, ``console`` is an arbitrary handler name and can be
    any string. The ``type: console`` option selects the
    :class:`Symfony\\Bridge\\Monolog\\Handler\\ConsoleHandler`, which writes log
    messages to the command output.

    The ``channels`` option uses the ``!`` prefix to exclude specific
    :doc:`channels </logging/channels_handlers>`. The ``console`` channel is
    excluded to reduce noise. This is the channel where Symfony logs command
    lifecycle events (e.g. *Command "{command}" exited with code "{code}"*).
    Excluding this channel does **not** affect any log messages you write yourself
    inside your commands, which use different channels and will still appear normally.

Now, log messages will be shown on the console based on the log levels and verbosity.
By default (normal verbosity level), warnings and higher will be shown. But in
:doc:`full verbosity mode </console/verbosity>`, all messages will be shown.

Limiting Output to Interactive Mode
-----------------------------------

In automated environments like CI/CD pipelines or cron jobs, console log output
may interfere with command output or create unnecessary clutter. You can configure
the console handler to only output logs when the console is interactive:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/monolog.yaml
        monolog:
            handlers:
                console:
                    type: console
                    process_psr_3_messages: false
                    channels: ['!event', '!doctrine', '!console']
                    interactive_only: true

    .. code-block:: php

        // config/packages/dev/monolog.php
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $monolog->handler('console')
                ->type('console')
                ->processPsr3Messages(false)
                ->interactiveOnly(true)
                ->channels()->elements(['!event', '!doctrine', '!console'])
            ;
        };

When ``interactive_only`` is set to ``true``, the console handler will only
output logs and prevent propagation to other handlers when the command is
running in an interactive terminal. In non-interactive mode (e.g., when using
the ``--no-interaction`` option or in automated scripts), logs will be
propagated to other handlers instead.

.. _MonologBridge: https://github.com/symfony/monolog-bridge
