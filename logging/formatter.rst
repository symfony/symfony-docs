How to Define a Custom Logging Formatter
========================================

Each logging handler uses a ``Formatter`` to format the record before logging
it. All Monolog handlers use an instance of
``Monolog\Formatter\LineFormatter`` by default but you can replace it
easily. Your formatter must implement
``Monolog\Formatter\FormatterInterface``.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            my_formatter:
                class: Monolog\Formatter\JsonFormatter
        monolog:
            handlers:
                file:
                    type: stream
                    level: debug
                    formatter: my_formatter

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="my_formatter" class="Monolog\Formatter\JsonFormatter" />
            </services>

            <monolog:config>
                <monolog:handler
                    name="file"
                    type="stream"
                    level="debug"
                    formatter="my_formatter"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container
            ->register('my_formatter', 'Monolog\Formatter\JsonFormatter');

        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'file' => array(
                    'type'      => 'stream',
                    'level'     => 'debug',
                    'formatter' => 'my_formatter',
                ),
            ),
        ));
