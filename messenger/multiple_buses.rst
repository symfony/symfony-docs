.. index::
    single: Messenger; Multiple buses

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
                        default_middleware: allow_no_handlers
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
                    <framework:bus>
                    <framework:bus name="query.bus">
                        <framework:middleware id="validation"/>
                    <framework:bus>
                    <framework:bus name="event.bus" default-middleware="allow_no_handlers">
                        <framework:middleware id="validation"/>
                    <framework:bus>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                // The bus that is going to be injected when injecting MessageBusInterface
                'default_bus' => 'command.bus',
                'buses' => [
                    'command.bus' => [
                        'middleware' => [
                            'validation',
                            'doctrine_transaction',
                        ],
                    ],
                    'query.bus' => [
                        'middleware' => [
                            'validation',
                        ],
                    ],
                    'event.bus' => [
                        'default_middleware' => 'allow_no_handlers',
                        'middleware' => [
                            'validation',
                        ],
                    ],
                ],
            ],
        ]);

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

You can also automatically add this tag to a number of classes by following
a naming convention and registering all of the handler services by name with
the correct tag:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml

        # put this after the "App\" line that registers all your services
        command_handlers:
            namespace: App\MessageHandler\
            resource: '%kernel.project_dir%/src/MessageHandler/*CommandHandler.php'
            autoconfigure: false
            tags:
                - { name: messenger.message_handler, bus: command.bus }

        query_handlers:
            namespace: App\MessageHandler\
            resource: '%kernel.project_dir%/src/MessageHandler/*QueryHandler.php'
            autoconfigure: false
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
                <!-- command handlers -->
                <prototype namespace="App\MessageHandler\" resource="%kernel.project_dir%/src/MessageHandler/*CommandHandler.php" autoconfigure="false">
                    <tag name="messenger.message_handler" bus="command.bus"/>
                </prototype>
                <!-- query handlers -->
                <prototype namespace="App\MessageHandler\" resource="%kernel.project_dir%/src/MessageHandler/*QueryHandler.php" autoconfigure="false">
                    <tag name="messenger.message_handler" bus="query.bus"/>
                </prototype>
            </services>
        </container>

    .. code-block:: php

        // config/services.php

        // Command handlers
        $container->services()
            ->load('App\MessageHandler\\', '%kernel.project_dir%/src/MessageHandler/*CommandHandler.php')
            ->autoconfigure(false)
            ->tag('messenger.message_handler', ['bus' => 'command.bus']);

        // Query handlers
        $container->services()
            ->load('App\MessageHandler\\', '%kernel.project_dir%/src/MessageHandler/*QueryHandler.php')
            ->autoconfigure(false)
            ->tag('messenger.message_handler', ['bus' => 'query.bus']);

Debugging the Buses
-------------------

The ``debug:messenger`` command lists available messages & handlers per bus.
You can also restrict the list to a specific bus by providing its name as argument.

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

.. _article about CQRS: https://martinfowler.com/bliki/CQRS.html
