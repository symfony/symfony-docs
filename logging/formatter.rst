How to Define a Custom Logging Formatter
========================================

Each logging handler uses a ``Formatter`` to format the record before logging
it. All Monolog handlers use an instance of
``Monolog\Formatter\LineFormatter`` by default but you can replace it
easily. Your formatter must implement
``Monolog\Formatter\FormatterInterface``.

For example, to use the built-in ``JsonFormatter``, register it as a service then
configure your handler to use it:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # ...

            Monolog\Formatter\JsonFormatter: ~

        # app/config/config_prod.yml (and/or config_dev.yml)
        monolog:
            handlers:
                file:
                    type: stream
                    level: debug
                    formatter: Monolog\Formatter\JsonFormatter

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="Monolog\Formatter\JsonFormatter" />
            </services>

            <!-- app/config/config_prod.xml (and/or config_dev.xml) -->
            <monolog:config>
                <monolog:handler
                    name="file"
                    type="stream"
                    level="debug"
                    formatter="Monolog\Formatter\JsonFormatter"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        use Monolog\Formatter\JsonFormatter;

        // app/config/services.php
        $container->register(JsonFormatter::class);

        // app/config/config_prod.php (or config_dev.php)
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'file' => array(
                    'type'      => 'stream',
                    'level'     => 'debug',
                    'formatter' => JsonFormatter::class',
                ),
            ),
        ));
