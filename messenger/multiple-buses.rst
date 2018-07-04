.. index::
    single: Messenger; Multiple buses

Multiple Buses
==============

A common architecture when building application is to separate commands from
queries. Commands are actions that do something and queries fetches data. This
is called CQRS (Command Query Responsibility Segregation). See Martin Fowler's
`article about CQRS`_ to learn more. This architecture could be used together
with the messenger component by defining multiple buses.

A **command bus** is a little different form a **query bus**. As example,
command buses must not return anything and query buses are rarely asynchronous.
You can configure these buses and their rules by using middlewares.

It might also be a good idea to separate actions from reactions by introducing
an **event bus**. The event bus could have zero or more subscribers.

.. configuration-block::

    .. code-block:: yaml

        framework:
            messenger:
                default_bus: messenger.bus.command
                buses:
                    messenger.bus.command:
                        middleware:
                            - messenger.middleware.validation
                            - messenger.middleware.handles_recorded_messages: ['@messenger.bus.event']
                            - doctrine_transaction_middleware: ['default']
                    messenger.bus.query:
                        middleware:
                            - messenger.middleware.validation
                    messenger.bus.event:
                        middleware:
                            - messenger.middleware.allow_no_handler
                            - messenger.middleware.validation

.. _article about CQRS: https://martinfowler.com/bliki/CQRS.html
