.. index::
    single: Console; Commands as Services

How to Define Commands as Services
==================================

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
your command classes are already registered as services. Great! This is the
recommended setup.

.. note::

    You can also manually register your command as a service by configuring the service
    and :doc:`tagging it </service_container/tags>` with ``console.command``.

For example, suppose you want to log something from within your command::

    namespace App\Command;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    #[AsCommand(name: 'app:sunshine')]
    class SunshineCommand extends Command
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;

            // you *must* call the parent constructor
            parent::__construct();
        }

        protected function configure(): void
        {
            $this
                ->setDescription('Good morning!');
        }

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $this->logger->info('Waking up the sun');
            // ...

            return Command::SUCCESS;
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
the command class will automatically be registered as a service and passed the ``$logger``
argument (thanks to autowiring). In other words, you only need to create this
class and everything works automatically! You can call the ``app:sunshine``
command and start logging.

.. caution::

    You *do* have access to services in ``configure()``. However, if your command is
    not :ref:`lazy <console-command-service-lazy-loading>`, try to avoid doing any
    work (e.g. making database queries), as that code will be run, even if you're using
    the console to execute a different command.

.. _console-command-service-lazy-loading:

Lazy Loading
------------

To make your command lazily loaded, either define its name using the PHP
``AsCommand`` attribute::

    use Symfony\Component\Console\Attribute\AsCommand;
    // ...

    #[AsCommand(name: 'app:sunshine')]
    class SunshineCommand extends Command
    {
        // ...
    }

Or set the ``command`` attribute on the ``console.command`` tag in your service definition:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Command\SunshineCommand:
                tags:
                    - { name: 'console.command', command: 'app:sunshine' }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Command\SunshineCommand">
                    <tag name="console.command" command="app:sunshine"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Command\SunshineCommand;

        // ...
        $container->register(SunshineCommand::class)
            ->addTag('console.command', ['command' => 'app:sunshine'])
        ;

.. note::

    If the command defines aliases (using the
    :method:`Symfony\\Component\\Console\\Command\\Command::getAliases` method)
    you must add one ``console.command`` tag per alias.

That's it. One way or another, the ``SunshineCommand`` will be instantiated
only when the ``app:sunshine`` command is actually called.

.. note::

    You don't need to call ``setName()`` for configuring the command when it is lazy.

.. caution::

    Calling the ``list`` command will instantiate all commands, including lazy commands.
