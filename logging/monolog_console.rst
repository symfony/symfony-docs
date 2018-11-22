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
levels, the `MonologBridge`_ provides a `ConsoleHandler`_ that listens to
console events and writes log messages to the console output depending on the
current log level and the console verbosity.

The example above could then be rewritten as::

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    // ...

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
            // ...
            $this->logger->notice('Some more info');
        }
    }

Depending on the verbosity level that the command is run in and the user's
configuration (see below), these messages may or may not be displayed to
the console. If they are displayed, they are timestamped and colored appropriately.
Additionally, error logs are written to the error output (php://stderr).
There is no need to conditionally handle the verbosity settings anymore.

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

        // config/packages/dev/monolog.php
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

.. _monolog-console-testing-commands:

Testing Monolog in Commands
----------------

Symfony provides the tool :class:`Monolog\\Handler\\Testhandler`
class to test Monolog logging in Commands. First, add a method to get your logger property from your command::

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    // ...

    class YourCommand extends Command
    {
        private $logger;

        //...
        /**
         * @return LoggerInterface
         */
        public function getLogger()
        {
            return $this->logger;
        }
    }

Second, add the :class:`Monolog\\Handler\\Testhandler` inside monolog config::

.. configuration-block::

    .. code-block:: yaml

        # config/packages/test/monolog.yaml
        monolog:
            handlers:
                # ...
                console:
                    type:   console
                    process_psr_3_messages: false
                    channels: ['!event', '!doctrine', '!console']
                test:
                    type:   test
                    level:  debug

                    # optionally configure the mapping between verbosity levels and log levels
                    # verbosity_levels:
                    #     VERBOSITY_NORMAL: NOTICE

    .. code-block:: xml

        <!-- config/packages/test/monolog.xml -->
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
                
                <monolog:handler name="test" type="test" level="debug" />
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/test/monolog.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'console' => array(
                   'type' => 'console',
                   'process_psr_3_messages' => false,
                   'channels' => array('!event', '!doctrine', '!console'),
                ),
                'test' => array(
                   'type' => 'test',
                   'level' => 'debug'
                ),
            ),
        ));

Finally, access the :class:`Monolog\\Handler\\Testhandler` and validate the log output::

    // tests/Command/CreateUserCommandTest.php
    namespace App\Tests\Command;

    use App\Command\CreateUserCommand;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
    use Symfony\Component\Console\Tester\CommandTester;
    use Monolog\Handler\TestHandler;

    class CreateUserCommandTest extends KernelTestCase
    {
        public function testExecute()
        {
            $kernel = static::createKernel();
            $application = new Application($kernel);

            $command = $application->find('app:create-user');
            $commandTester = new CommandTester($command);
            $commandTester->execute(array(
                'command'  => $command->getName(),

                // pass arguments to the helper
                'username' => 'Wouter',

                // prefix the key with two dashes when passing options,
                // e.g: '--some-option' => 'option_value',
            ));
            
            $logger = $command->getLogger();
            $handlers = $logger->getHandlers();
            
            $logs = null;
            foreach ($handlers as $handler) {
                if ($handler instanceof TestHandler) {
                    $logs = $handler;
                }
            }
            
            if ($logs instanceof TestHandler) {
                $this->assertTrue($logs->hasRecordThatContains('Some info', Logger::DEBUG), 'ASSERT_ERROR_MESSAGE_1');
                $this->assertTrue($logs->hasRecordThatContains('Some more info', Logger::NOTICE), 'ASSERT_ERROR_MESSAGE_2');
                $this->assertFalse($logs->hasRecordThatContains('Command Success', Logger::INFO), 'ASSERT_ERROR_MESSAGE_3');
            }
            
            // ...
        }
    }


.. _ConsoleHandler: https://github.com/symfony/MonologBridge/blob/master/Handler/ConsoleHandler.php
.. _MonologBridge: https://github.com/symfony/MonologBridge
