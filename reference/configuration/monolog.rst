.. index::
    pair: Monolog; Configuration reference

MonologBundle Configuration ("monolog")
=======================================

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        monolog:
            handlers:

                # Examples:
                syslog:
                    type:                stream
                    path:                /var/log/symfony.log
                    level:               ERROR
                    bubble:              false
                    formatter:           my_formatter
                main:
                    type:                fingers_crossed
                    action_level:        WARNING
                    buffer_size:         30
                    handler:             custom
                console:
                    type:                console
                    verbosity_levels:
                        VERBOSITY_NORMAL:       WARNING
                        VERBOSITY_VERBOSE:      NOTICE
                        VERBOSITY_VERY_VERBOSE: INFO
                        VERBOSITY_DEBUG:        DEBUG
                custom:
                    type:                service
                    id:                  my_handler

                # Default options and values for some "my_custom_handler"
                # Note: many of these options are specific to the "type".
                # For example, the "service" type doesn't use any options
                # except id and channels
                my_custom_handler:
                    type:                 ~ # Required
                    id:                   ~
                    priority:             0
                    level:                DEBUG
                    bubble:               true
                    path:                 "%kernel.logs_dir%/%kernel.environment%.log"
                    ident:                false
                    facility:             user
                    max_files:            0
                    action_level:         WARNING
                    activation_strategy:  ~
                    stop_buffering:       true
                    buffer_size:          0
                    handler:              ~
                    members:              []
                    channels:
                        type:     ~
                        elements: ~
                    from_email:           ~
                    to_email:             ~
                    subject:              ~
                    mailer:               ~
                    email_prototype:
                        id:                   ~ # Required (when the email_prototype is used)
                        method:               ~
                    formatter:            ~

    .. code-block:: xml

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd"
        >

            <monolog:config>
                <monolog:handler
                    name="syslog"
                    type="stream"
                    path="/var/log/symfony.log"
                    level="error"
                    bubble="false"
                    formatter="my_formatter"
                />
                <monolog:handler
                    name="main"
                    type="fingers_crossed"
                    action-level="warning"
                    handler="custom"
                />
                <monolog:handler
                    name="console"
                    type="console"
                />
                <monolog:handler
                    name="custom"
                    type="service"
                    id="my_handler"
                />
            </monolog:config>
        </container>

.. note::

    When the profiler is enabled, a handler is added to store the logs'
    messages in the profiler. The profiler uses the name "debug" so it
    is reserved and cannot be used in the configuration.
