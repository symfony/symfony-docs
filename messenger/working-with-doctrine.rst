.. index::
    single: Messenger; Working with Doctrine

Working with Doctrine
=====================

If your message handlers writes to a database it is a good idea to wrap all those
writes in a single Doctrine transaction. This make sure that if one of your database
query fails, then all queries are rolled back and give you a change to handle the
exception knowing that your database was not changed by your message handler.

Next thing you need to do is to add the middleware to your bus configuration.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            # ...
            buses:
                messenger.bus.command:
                    middleware:
                        - validation
                        - doctrine_transaction

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
                <framework:messenger>
                    <framework:bus name="messenger.bus.commands">
                        <framework:middleware id="validation"/>
                        <framework:middleware id="doctrine_transaction"/>
                    <framework:bus>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'buses' => [
                    'messenger.bus.commands' => [
                        'middleware' => [
                            'validation',
                            'doctrine_transaction',
                        ],
                    ],
                ],
            ],
        ]);

