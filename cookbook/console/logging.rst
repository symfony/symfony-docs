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
This means that you can simply access the standard logger service through the
container and use it to do the logging::

    // src/Acme/DemoBundle/Command/GreetCommand.php
    namespace Acme\DemoBundle\Command;

    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Psr\Log\LoggerInterface;

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
                $logger->warning('Yelled: '.$text);
            } else {
                $logger->info('Greeted: '.$text);
            }

            $output->writeln($text);
        }
    }

Depending on the environment in which you run your command (and your logging
setup), you should see the logged entries in ``app/logs/dev.log`` or ``app/logs/prod.log``.

Enabling automatic Exceptions logging
-------------------------------------

To get your console application to automatically log uncaught exceptions for
all of your commands, you can use :doc:`console events</components/console/events>`.

.. versionadded:: 2.3
    Console events were introduced in Symfony 2.3.

First configure a listener for console exception events in the service container:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            kernel.listener.command_dispatch:
                class: Acme\DemoBundle\EventListener\ConsoleExceptionListener
                arguments:
                    logger: "@logger"
                tags:
                    - { name: kernel.event_listener, event: console.exception }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="console_exception_listener.class">Acme\DemoBundle\EventListener\ConsoleExceptionListener</parameter>
            </parameters>

            <services>
                <service id="kernel.listener.command_dispatch" class="%console_exception_listener.class%">
                    <argument type="service" id="logger"/>
                    <tag name="kernel.event_listener" event="console.exception" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setParameter(
            'console_exception_listener.class',
            'Acme\DemoBundle\EventListener\ConsoleExceptionListener'
        );
        $definitionConsoleExceptionListener = new Definition(
            '%console_exception_listener.class%',
            array(new Reference('logger'))
        );
        $definitionConsoleExceptionListener->addTag(
            'kernel.event_listener',
            array('event' => 'console.exception')
        );
        $container->setDefinition(
            'kernel.listener.command_dispatch',
            $definitionConsoleExceptionListener
        );

Then implement the actual listener::

    // src/Acme/DemoBundle/EventListener/ConsoleExceptionListener.php
    namespace Acme\DemoBundle\EventListener;

    use Symfony\Component\Console\Event\ConsoleExceptionEvent;
    use Psr\Log\LoggerInterface;

    class ConsoleExceptionListener
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        public function onConsoleException(ConsoleExceptionEvent $event)
        {
            $command = $event->getCommand();
            $exception = $event->getException();

            $message = sprintf(
                '%s: %s (uncaught exception) at %s line %s while running console command `%s`',
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $command->getName()
            );

            $this->logger->error($message);
        }
    }

In the code above, when any command throws an exception, the listener will
receive an event. You can simply log it by passing the logger service via the
service configuration. Your method receives a
:class:`Symfony\\Component\\Console\\Event\\ConsoleExceptionEvent` object,
which has methods to get information about the event and the exception.

Logging non-0 exit statuses
---------------------------

The logging capabilities of the console can be further extended by logging
non-0 exit statuses. This way you will know if a command had any errors, even
if no exceptions were thrown.

First configure a listener for console terminate events in the service container:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            kernel.listener.command_dispatch:
                class: Acme\DemoBundle\EventListener\ConsoleTerminateListener
                arguments:
                    logger: "@logger"
                tags:
                    - { name: kernel.event_listener, event: console.terminate }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="console_terminate_listener.class">Acme\DemoBundle\EventListener\ConsoleExceptionListener</parameter>
            </parameters>

            <services>
                <service id="kernel.listener.command_dispatch" class="%console_terminate_listener.class%">
                    <argument type="service" id="logger"/>
                    <tag name="kernel.event_listener" event="console.terminate" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setParameter(
            'console_terminate_listener.class',
            'Acme\DemoBundle\EventListener\ConsoleExceptionListener'
        );
        $definitionConsoleExceptionListener = new Definition(
            '%console_terminate_listener.class%',
            array(new Reference('logger'))
        );
        $definitionConsoleExceptionListener->addTag(
            'kernel.event_listener',
            array('event' => 'console.terminate')
        );
        $container->setDefinition(
            'kernel.listener.command_dispatch',
            $definitionConsoleExceptionListener
        );

Then implement the actual listener::

    // src/Acme/DemoBundle/EventListener/ConsoleExceptionListener.php
    namespace Acme\DemoBundle\EventListener;

    use Symfony\Component\Console\Event\ConsoleTerminateEvent;
    use Psr\Log\LoggerInterface;

    class ConsoleTerminateListener
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        public function onConsoleTerminate(ConsoleTerminateEvent $event)
        {
            $statusCode = $event->getExitCode();
            $command = $event->getCommand();

            if ($statusCode === 0) {
                return;
            }

            if ($statusCode > 255) {
                $statusCode = 255;
                $event->setExitCode($statusCode);
            }

            $this->logger->warning(sprintf(
                'Command `%s` exited with status code %d',
                $command->getName(),
                $statusCode
            ));
        }
    }
