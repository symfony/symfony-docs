.. index::
    single: Messenger; Multiple buses

Multiple Buses
==============

.. configuration-block::

    .. code-block:: yaml

        framework:
            messenger:
                transports:
                    default: enqueue://default
                default_bus: messenger.bus.command
                buses:
                    messenger.bus.command:
                        middleware:
                            #- messenger.middleware.exactly_one_handler
                            - messenger.middleware.validation
                            - messenger.middleware.handles_recorded_messages: ['@messenger.bus.event']
                            - doctrine_transaction_middleware: ['default']
                    messenger.bus.query:
                        middleware:
                            #- messenger.middleware.exactly_one_handler
                            - messenger.middleware.validation
                    messenger.bus.event:
                        middleware:
                            - messenger.middleware.allow_no_handler
                            - messenger.middleware.validation

                routing:
                    # Route your messages to the transports
                    # 'App\Message\YourMessage': amqp
