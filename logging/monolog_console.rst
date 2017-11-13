.. index::
   single: Logging; Console messages

How to Configure Monolog to Display Console Messages
====================================================

It is possible to use the console to print messages for certain
:doc:`verbosity levels </console/verbosity>` using the
:class:`Symfony\\Component\\Console\\Output\\OutputInterface` instance that
is passed when a command gets executed.

When a lot of logging has to happen, it's cumbersome to print information
depending on the verbosity settings (``-v``, ``-vv``, ``-vvv``) because the
calls need to be wrapped in conditions. The code quickly gets verbose or dirty.
For example::

    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $output->writeln('Some info');
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('Some more info');
        }
    }

Instead of using these semantic methods to test for each of the verbosity
levels, the `MonologBridge`_ provides a `ConsoleHandler`_ that listens to
console events and writes log messages to the console output depending on the
current log level and the console verbosity.

The example above could then be rewritten as::

    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // assuming the Command extends ContainerAwareCommand...
        $logger = $this->getContainer()->get('logger');
        $logger->debug('Some info');

        $logger->notice('Some more info');
    }

Depending on the verbosity level that the command is run in and the user's
configuration (see below), these messages may or may not be displayed to
the console. If they are displayed, they are timestamped and colored appropriately.
Additionally, error logs are written to the error output (php://stderr).
There is no need to conditionally handle the verbosity settings anymore.

The Monolog console handler is enabled by default in the Symfony Framework. For
example, in ``config_dev.yml``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
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

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

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

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'console' => array(
                   'type' => 'console',
                   'process_psr_3_messages' => false,
                   'channels' => array('!event', '!doctrine', '!console'),
                ),
            ),
        ));

Now, log messages will be shown on the console based on the log levels and verbosity.
By default (normal verbosity level), warnings and higher will be shown. But in
:doc:`full verbosity mode </console/verbosity>`, all messages will be shown.

.. _ConsoleHandler: https://github.com/symfony/MonologBridge/blob/master/Handler/ConsoleHandler.php
.. _MonologBridge: https://github.com/symfony/MonologBridge
