Logging with Monolog
====================

Symfony comes with an outside library - called Monolog_ - that allows you to create
logs that can be stored in a variety of different places.

Logging a Message
-----------------

To log a message, fetch the ``logger`` service from the container in
your controller::

    public function indexAction()
    {
        $logger = $this->get('logger');
        $logger->info('I just got the logger');
        $logger->error('An error occurred');
        
        $logger->critical('I left the oven on!', array(
            // include extra "context" info in your logs
            'cause' => 'in_hurry'
        ));

        // ...
    }

The ``logger`` service has different methods for different logging levels/priorities.
You can configure the logger to do different things based on the *level* of a message
(e.g. :doc:`email me when an error occurs </logging/monolog_email>`).

See LoggerInterface_ for a list of all of the methods on the logger.

Where Logs are Stored
---------------------

The configuration for *where* logs are stored lives in the specific
:doc:`environment </configuration/environments>` configuration files: ``config_dev.yml``
and ``config_prod.yml``.

By default, log entries are written to the ``app/logs/dev.log`` file when you're in
the ``dev`` environment. In the ``prod`` environment, logs are written to ``app/logs/prod.log``,
but *only* during a request where an error or high-priority log entry was made
(i.e. ``error()`` , ``critical()``, ``alert()`` or ``emergency()``).

To control this, you'll configure different *handlers* that handle log entries, sometimes
modify them, and ultimately store them.

Handlers: Writing Logs to different Locations
---------------------------------------------

The logger has a stack of *handlers*, and each can be used to write the log entries
to different locations (e.g. files, database, Slack, etc).

.. tip::

    You can *also* configure logging "channels", which are like categories. Each
    channel can have its *own* handlers, which means you can store different log
    messages in different places. See :doc:`/logging/channels_handlers`.

Symfony pre-configures some basic handlers in the ``config_dev.yml`` and ``config_prod.yml``
files. Check these out for some real-world examples.

This example uses *two* handlers: ``stream`` (to write to a file) and ``syslog``
to write logs using the :phpfunction:`syslog` function:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        monolog:
            handlers:
                # this "file_log" key could be anything
                file_log:
                    type: stream
                    # log to app/logs/(environment).log
                    path: "%kernel.logs_dir%/%kernel.environment%.log"
                    # log *all* messages (debug is lowest level)
                    level: debug

                syslog_handler:
                    type: syslog
                    # log error-level messages and higher
                    level: error

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

            <monolog:config>
                <monolog:handler
                    name="file_log"
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                />
                <monolog:handler
                    name="syslog_handler"
                    type="syslog"
                    level="error"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'file_log' => array(
                    'type'  => 'stream',
                    'path'  => '%kernel.logs_dir%/%kernel.environment%.log',
                    'level' => 'debug',
                ),
                'syslog_handler' => array(
                    'type'  => 'syslog',
                    'level' => 'error',
                ),
            ),
        ));

This defines a *stack* of handlers and each handler is called in the order that it's
defined.

Handlers that Modify Log Entries
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of writing log files somewhere, *some* handlers are used to filter or modify
log entries before sending them to *other* handlers. One powerful, built-in handler
called ``fingers_crossed`` is used in the ``prod`` environment by default. It stores
*all* log messages during a request but *only* passes them to a second handler if
one of the messages reaches an ``action_level``. Take this example:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        monolog:
            handlers:
                filter_for_errors:
                    type: fingers_crossed
                    # if *one* log is error or higher, pass *all* to file_log
                    action_level: error
                    handler: file_log

                # now passed *all* logs, but only if one log is error or higher
                file_log:
                    type: stream
                    path: "%kernel.logs_dir%/%kernel.environment%.log"

                # still passed *all* logs, and still only logs error or higher
                syslog_handler:
                    type: syslog
                    level: error

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

            <monolog:config>
                <monolog:handler
                    name="filter_for_errors"
                    type="fingers_crossed"
                    action-level="error"
                    handler="file_log"
                />
                <monolog:handler
                    name="file_log"
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                />
                <monolog:handler
                    name="syslog_handler"
                    type="syslog"
                    level="error"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'filter_for_errors' => array(
                    'type'         => 'fingers_crossed',
                    'action_level' => 'error',
                    'handler'      => 'file_log',
                ),
                'file_log' => array(
                    'type'  => 'stream',
                    'path'  => '%kernel.logs_dir%/%kernel.environment%.log',
                    'level' => 'debug',
                ),
                'syslog_handler' => array(
                    'type'  => 'syslog',
                    'level' => 'error',
                ),
            ),
        ));

Now, if even one log entry has an ``error`` level or higher, then *all* log entries
for that request are saved to a file via the ``file_log`` handler. That means that
your log file will contain *all* the details about the problematic request - making
debugging much easier!

.. tip::

    The handler named "file_log" will not be included in the stack itself as
    it is used as a nested handler of the ``fingers_crossed`` handler.

.. note::

    If you want to override the ``monolog`` configuration via in another config
    file, you will need to redefine the entire ``handlers`` stack. The configuration
    from the two files cannot be merged because the order matters and a merge does
    not allow to control the order.

All Built-in Handlers
---------------------

Monolog comes with *many* built-in handles for emailing logs, sending them to Loggly,
or notifying you in Slack. These are documented inside of MonologBundle itself. For
a full list, see `Monolog Configuration`_.

How to Rotate your Log Files
----------------------------

Over time, log files can grow to be *huge*, both while developing and on
production. One best-practice solution is to use a tool like the `logrotate`_
Linux command to rotate log files before they become too large.

Another option is to have Monolog rotate the files for you by using the
``rotating_file`` handler. This handler creates a new log file every day
and can also remove old files automatically. To use it, just set the ``type``
option of your handler to ``rotating_file``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        monolog:
            handlers:
                main:
                    type:  rotating_file
                    path:  '%kernel.logs_dir%/%kernel.environment%.log'
                    level: debug
                    # max number of log files to keep
                    # defaults to zero, which means infinite files
                    max_files: 10

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <!-- "max_files": max number of log files to keep
                     defaults to zero, which means infinite files -->
                <monolog:handler name="main"
                    type="rotating_file"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                    max_files="10"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config_dev.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'main' => array(
                    'type'  => 'rotating_file',
                    'path'  => '%kernel.logs_dir%/%kernel.environment%.log',
                    'level' => 'debug',
                    // max number of log files to keep
                    // defaults to zero, which means infinite files
                    'max_files' => 10,
                ),
            ),
        ));

Adding extra Data to each Log (e.g. a unique request token)
-----------------------------------------------------------

Monolog also supports *processors*: functions that can dynamically add extra
information to your log entries.

See :doc:`/logging/processors` for details.

.. toctree::
    :maxdepth: 1
    :glob:

    logging/*

.. _Monolog: https://github.com/Seldaek/monolog
.. _LoggerInterface: https://github.com/php-fig/log/blob/master/Psr/Log/LoggerInterface.php
.. _`logrotate`: https://fedorahosted.org/logrotate/
.. _`Monolog Configuration`: https://github.com/symfony/monolog-bundle/blob/master/DependencyInjection/Configuration.php#L25
