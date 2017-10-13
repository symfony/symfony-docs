.. index::
    single: Console; Commands as Services

How to Define Commands as Services
==================================

If you're using the :ref:`default services.yml configuration <service-container-services-load-example>`,
your command classes are already registered as services. Great! This is the
recommended setup.

Symfony also looks in the ``Command/`` directory of each bundle for commands
non registered as a service and automatically registers those classes as
commands. However this auto-registration was deprecated in Symfony 3.4. In
Symfony 4.0, commands won't be auto-registered anymore.

.. note::

    You can also manually register your command as a service by configuring the service
    and :doc:`tagging it </service_container/tags>` with ``console.command``.

In either case, if your class extends :class:`Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand`,
you can access public services via ``$this->getContainer()->get('SERVICE_ID')``.

But if your class is registered as a service, you can instead access services by
using normal :ref:`dependency injection <services-constructor-injection>`.

For example, suppose you want to log something from within your command::

    namespace AppBundle\Command;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class SunshineCommand extends Command
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;

            // you *must* call the parent constructor
            parent::__construct();
        }

        protected function configure()
        {
            $this
                ->setName('app:sunshine')
                ->setDescription('Good morning!');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $this->logger->info('Waking up the sun');
            // ...
        }
    }

If you're using the :ref:`default services.yml configuration <service-container-services-load-example>`,
the command class will automatically be registered as a service and passed the ``$logger``
argument (thanks to autowiring). In other words, *just* by creating this class, everything
works! You can call the ``app:sunshine`` command and start logging.

.. caution::

    You *do* have access to services in ``configure()``. However, if your command is
    not :ref:`lazy <console-command-service-lazy-loading>`, try to avoid doing any
    work (e.g. making database queries), as that code will be run, even if you're using
    the console to execute a different command.

.. _console-command-service-lazy-loading:

Lazy Loading
------------

.. versionadded:: 3.4
    Support for command lazy loading was introduced in Symfony 3.4.

To make your command lazily loaded, either define its ``$defaultName`` static property::

    class SunshineCommand extends Command
    {
        protected static $defaultName = 'app:sunshine';

        // ...
    }

Or set the ``command`` attribute on the ``console.command`` tag in your service definition:

.. configuration-block::

    .. code-block:: yaml

        services:

            AppBundle\Command\SunshineCommand:
                tags:
                    - { name: 'console.command', command: 'app:sunshine' }
                # ...

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <service id="AppBundle\Command\SunshineCommand">
                     <tag name="console.command" command="app:sunshine" />
                </service>

            </services>
        </container>

    .. code-block:: php

        use AppBundle\Command\SunshineCommand;

        //...

        $container
            ->register(SunshineCommand::class)
            ->addTag('console.command', array('command' => 'app:sunshine'))
        ;

That's it. In one way or another, the ``SunshineCommand`` will be instantiated
only when the ``app:sunshine`` command is actually called.

.. note::

    You don't need to call ``setName()`` for configuring the command when it is lazy.

.. caution::

    Calling the ``list`` command requires to load all commands, lazy ones included.
