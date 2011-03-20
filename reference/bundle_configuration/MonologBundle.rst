.. index::
   pair: Monolog; Configuration Reference

Configuration Reference
=======================

.. configuration-block::

    .. code-block:: yaml

        monolog:
            handlers:
                syslog:
                    type: stream
                    path: /var/log/symfony.log
                    level: error
                    bubble: false
                    formatter: my_formatter
                    processors:
                        - some_callable
                main:
                    type: fingerscrossed
                    action_level: warning
                    buffer_size: 30
                    handler: custom
                custom:
                    type: service
                    id: my_handler
            processors:
                - @my_processor

    .. code-block:: xml

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/monolog http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <monolog:handler
                    name="syslog"
                    type="stream"
                    path="/var/log/symfony.log"
                    level="error"
                    bubble="false"
                    formatter="my_formatter"
                >
                    <monolog:processor callback="some_callable" />
                </monolog:handler />
                <monolog:handler
                    name="main"
                    type="fingerscrossed"
                    action-level="warning"
                    handler="custom"
                />
                <monolog:handler
                    name="custom"
                    type="service"
                    id="my_handler"
                />
                <monolog:processor callback="@my_processor" />
            </monolog:config>
        </container>

.. note::

    When the profiler is enabled, a handler is added to store the logs'
    messages in the profiler. The profiler uses the name "debug" so it
    will replace the handler defined in the config file if you use the
    name "debug" (and log the messages twice).
