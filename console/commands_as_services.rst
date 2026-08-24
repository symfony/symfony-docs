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

    #[AsCommand(name: 'app:sunshine', description: 'Good morning!')]
    class SunshineCommand
    {
        public function __construct(
            private LoggerInterface $logger,
        ) {
        }

        public function __invoke(): int
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

.. warning::

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
    class SunshineCommand
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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Command\SunshineCommand;

        return App::config([
            'services' => [
                SunshineCommand::class => [
                    'tags' => [
                        ['console.command' => ['command' => 'app:sunshine']],
                    ],
                ],
            ],
        ]);

.. note::

    If the command defines aliases (using the
    :method:`Symfony\\Component\\Console\\Command\\Command::getAliases` method)
    you must add one ``console.command`` tag per alias.

That's it. One way or another, the ``SunshineCommand`` will be instantiated
only when the ``app:sunshine`` command is actually called.

.. note::

    You don't need to call ``setName()`` for configuring the command when it is lazy.

.. warning::

    Calling the ``list`` command will instantiate all commands, including lazy commands.
    However, if the command is a ``Symfony\Component\Console\Command\LazyCommand``, then
    the underlying command factory will not be executed.
