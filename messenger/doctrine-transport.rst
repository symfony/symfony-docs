.. index::
   single: Messenger; Use Doctrine as Transport

Use Doctrine as transport
=========================

The Messenger component comes with a Doctrine transport. This lets Doctrine handle the storage of your messages. To use it you need to define the transport as the following:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    doctrine: doctrine://default

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
                    <framework:transport name="doctrine" dsn="doctrine://default"/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'doctrine' => 'doctrine://default',
                ],
            ],
        ]);

The format of the DSN is ``doctrine://<connection_name>``. The connection name is the name you used in the doctrine configuration. If you only use one connection then use ``default``.

If you have multiple Doctrine connections defined you can choose the desired one. If you have a connection named ``legacy`` the you should use the following DSN : ``doctrine://legacy``.

Customize Table Name
--------------------

By default the transport will create a table named ``messenger_messages`` but you can configure it per transport:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    doctrine:
                        dsn: doctrine://default?table_name=custom_table_name_for_messages

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
                    <framework:transport name="doctrine" dsn="doctrine://default?table_name=custom_table_name_for_messages"/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'doctrine' => [
                        'dsn' => 'doctrine://default?table_name=custom_table_name_for_messages',
                    ],
                ],
            ],
        ]);

Use the same table for different messages
-----------------------------------------

If you want to store the messages in the same table you can configure the ``queue_name`` option.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    doctrine:
                        dsn: doctrine://default?queue_name=custom_queue

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
                    <framework:transport name="doctrine" dsn="doctrine://default?queue_name=custom_queue"/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'doctrine' => 'doctrine://default?queue_name=custom_queue',
                ],
            ],
        ]);

Available options
-----------------

The transport can be configured via DSN or as options.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    doctrine_short: doctrine://default?queue_name=custom_queue
                    doctrine_full:
                        dsn: doctrine://default
                        options:
                            queue_name: custom_queue

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
                    <framework:transport name="doctrine_short" dsn="doctrine://default?queue_name=custom_queue"/>
                    <framework:transport name="doctrine_full" dsn="doctrine://default">
                        <framework:option queue_name="custom_queue"/>
                    </framework:transport>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'doctrine_short' => 'dsn' => 'doctrine://default?queue_name=custom_queue',
                    'doctrine_full' => [
                        'dsn' => 'doctrine://default',
                        'options' => [
                            'queue_name' => 'custom_queue'
                        ]
                    ],
                ],
            ],
        ]);

Options defined in the options transport takes precedence over the ones defined in the DSN.

+-------------------+--------------------------------------------------------------------------------------------------------------------------+--------------------+
| Option            + Description                                                                                                              | Default            |
+-------------------+--------------------------------------------------------------------------------------------------------------------------+--------------------+
| table_name        | Name of the table                                                                                                        | messenger_messages |
| queue_name        | Name of the queue                                                                                                        | default            |
| redeliver_timeout | Timeout before redeliver messages still in handling state (i.e: delivered_at is not null and message is still in table). | 3600               |
| auto_setup        | Whether the table should be created automatically during send / get.                                                     | true               |
+-------------------+--------------------------------------------------------------------------------------------------------------------------+--------------------+
