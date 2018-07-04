.. index::
    single: Messenger; Working with Doctrine

Working with Doctrine
=====================

If your message handlers writes to a database it is a good idea to wrap all those
writes in a single Doctrine transaction. This make sure that if one of your database
query fails, then all queries are rolled back and give you a change to handle the
exception knowing that your database was not changed by your message handler.

To make sure your message bus wraps the handler in one transaction you must first
register the middleware as a service.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            app.messenger.middleware_factory.transaction:
                class: Symfony\Bridge\Doctrine\Messenger\DoctrineTransactionMiddlewareFactory
                arguments: ['@doctrine']

            app.doctrine_transaction_middleware:
                class: Symfony\Bridge\Doctrine\Messenger\DoctrineTransactionMiddleware
                factory: ['@app.messenger.middleware_factory.transaction', 'createMiddleware']
                abstract: true
                arguments: ['default']

Next thing you need to do is to add the middleware to your bus configuration.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            # ...
            default_bus: messenger.bus.command
            buses:
                messenger.bus.command:
                    middleware:
                        - messenger.middleware.validation
                        - app.doctrine_transaction_middleware: ['default']
