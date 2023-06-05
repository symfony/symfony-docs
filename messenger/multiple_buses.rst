Multiple Buses
==============

A common architecture when building applications is to separate commands from
queries. Commands are actions that do something and queries fetch data. This
is called CQRS (Command Query Responsibility Segregation). See Martin Fowler's
`article about CQRS`_ to learn more. This architecture could be used together
with the Messenger component by defining multiple buses.

A **command bus** is a little different from a **query bus**. For example, command
buses usually don't provide any results and query buses are rarely asynchronous.
You can configure these buses and their rules by using middleware.

It might also be a good idea to separate actions from reactions by introducing
an **event bus**. The event bus could have zero or more subscribers.

.. configuration-block::

    .. code-block:: yaml

        framework:
            messenger:
                # The bus that is going to be injected when injecting MessageBusInterface
                default_bus: command.bus
                buses:
                    command.bus:
                        middleware:
                            - validation
                            - doctrine_transaction
                    query.bus:
                        middleware:
                            - validation
                    event.bus:
                        default_middleware:
                            enabled: true
                            # set "allow_no_handlers" to true (default is false) to allow having
                            # no handler configured for this bus without throwing an exception
                            allow_no_handlers: false
                            # set "allow_no_senders" to false (default is true) to throw an exception
                            # if no sender is configured for this bus
                            allow_no_senders: true
                        middleware:
                            - validation

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- The bus that is going to be injected when injecting MessageBusInterface -->
                <framework:messenger default-bus="command.bus">
                    <framework:bus name="command.bus">
                        <framework:middleware id="validation"/>
                        <framework:middleware id="doctrine_transaction"/>
                    </framework:bus>
                    <framework:bus name="query.bus">
                        <framework:middleware id="validation"/>
                    </framework:bus>
                    <framework:bus name="event.bus">
                        <!-- set "allow-no-handlers" to true (default is false) to allow having
                              no handler configured for this bus without throwing an exception -->
                        <!-- set "allow-no-senders" to false (default is true) to throw an exception
                             if no sender is configured for this bus -->
                        <framework:default-middleware enabled="true" allow-no-handlers="false" allow-no-senders="true"/>
                        <framework:middleware id="validation"/>
                    </framework:bus>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // The bus that is going to be injected when injecting MessageBusInterface
            $framework->messenger()->defaultBus('command.bus');

            $commandBus = $framework->messenger()->bus('command.bus');
            $commandBus->middleware()->id('validation');
            $commandBus->middleware()->id('doctrine_transaction');

            $queryBus = $framework->messenger()->bus('query.bus');
            $queryBus->middleware()->id('validation');

            $eventBus = $framework->messenger()->bus('event.bus');
            $eventBus->defaultMiddleware()
                ->enabled(true)
                // set "allowNoHandlers" to true (default is false) to allow having
                // no handler configured for this bus without throwing an exception
                ->allowNoHandlers(false)
                // set "allowNoSenders" to false (default is true) to throw an exception
                // if no sender is configured for this bus
                ->allowNoSenders(true)
            ;
            $eventBus->middleware()->id('validation');
        };

.. versionadded:: 6.2

    The ``allow_no_senders`` option was introduced in Symfony 6.2.

This will create three new services:

* ``command.bus``: autowireable with the :class:`Symfony\\Component\\Messenger\\MessageBusInterface`
  type-hint (because this is the ``default_bus``);

* ``query.bus``: autowireable with ``MessageBusInterface $queryBus``;

* ``event.bus``: autowireable with ``MessageBusInterface $eventBus``.

Restrict Handlers per Bus
-------------------------

By default, each handler will be available to handle messages on *all*
of your buses. To prevent dispatching a message to the wrong bus without an error,
you can restrict each handler to a specific bus using the ``messenger.message_handler`` tag:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\MessageHandler\SomeCommandHandler:
                tags: [{ name: messenger.message_handler, bus: command.bus }]
                # prevent handlers from being registered twice (or you can remove
                # the MessageHandlerInterface that autoconfigure uses to find handlers)
                autoconfigure: false

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\MessageHandler\SomeCommandHandler">
                    <tag name="messenger.message_handler" bus="command.bus"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        $container->services()
            ->set(App\MessageHandler\SomeCommandHandler::class)
            ->tag('messenger.message_handler', ['bus' => 'command.bus']);

This way, the ``App\MessageHandler\SomeCommandHandler`` handler will only be
known by the ``command.bus`` bus.

You can also automatically add this tag to a number of classes by using
the :ref:`_instanceof service configuration <di-instanceof>`. Using this,
you can determine the message bus based on an implemented interface:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            _instanceof:
                # all services implementing the CommandHandlerInterface
                # will be registered on the command.bus bus
                App\MessageHandler\CommandHandlerInterface:
                    tags:
                        - { name: messenger.message_handler, bus: command.bus }

                # while those implementing QueryHandlerInterface will be
                # registered on the query.bus bus
                App\MessageHandler\QueryHandlerInterface:
                    tags:
                        - { name: messenger.message_handler, bus: query.bus }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- all services implementing the CommandHandlerInterface
                     will be registered on the command.bus bus -->
                <instanceof id="App\MessageHandler\CommandHandlerInterface">
                    <tag name="messenger.message_handler" bus="command.bus"/>
                </instanceof>

                <!-- while those implementing QueryHandlerInterface will be
                     registered on the query.bus bus -->
                <instanceof id="App\MessageHandler\QueryHandlerInterface">
                    <tag name="messenger.message_handler" bus="query.bus"/>
                </instanceof>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\MessageHandler\CommandHandlerInterface;
        use App\MessageHandler\QueryHandlerInterface;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            // ...

            // all services implementing the CommandHandlerInterface
            // will be registered on the command.bus bus
            $services->instanceof(CommandHandlerInterface::class)
                ->tag('messenger.message_handler', ['bus' => 'command.bus']);

            // while those implementing QueryHandlerInterface will be
            // registered on the query.bus bus
            $services->instanceof(QueryHandlerInterface::class)
                ->tag('messenger.message_handler', ['bus' => 'query.bus']);
        };

Debugging the Buses
-------------------

The ``debug:messenger`` command lists available messages & handlers per bus.
You can also restrict the list to a specific bus by providing its name as an argument.

.. code-block:: terminal

    $ php bin/console debug:messenger

      Messenger
      =========

      command.bus
      -----------

       The following messages can be dispatched:

       ---------------------------------------------------------------------------------------
        App\Message\DummyCommand
            handled by App\MessageHandler\DummyCommandHandler
        App\Message\MultipleBusesMessage
            handled by App\MessageHandler\MultipleBusesMessageHandler
       ---------------------------------------------------------------------------------------

      query.bus
      ---------

       The following messages can be dispatched:

       ---------------------------------------------------------------------------------------
        App\Message\DummyQuery
            handled by App\MessageHandler\DummyQueryHandler
        App\Message\MultipleBusesMessage
            handled by App\MessageHandler\MultipleBusesMessageHandler
       ---------------------------------------------------------------------------------------

.. tip::

    Since Symfony 5.1, the command will also show the PHPDoc description of
    the message and handler classes.

.. _article about CQRS: https://martinfowler.com/bliki/CQRS.html
