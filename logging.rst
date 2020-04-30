Logging
=======

Symfony comes with a minimalist `PSR-3`_ logger: :class:`Symfony\\Component\\HttpKernel\\Log\\Logger`.
In conformance with `the twelve-factor app methodology`_, it sends messages starting from the
``WARNING`` level to `stderr`_.

The minimal log level can be changed by setting the ``SHELL_VERBOSITY`` environment variable:

=========================  =================
``SHELL_VERBOSITY`` value  Minimum log level
=========================  =================
``-1``                     ``ERROR``
``1``                      ``NOTICE``
``2``                      ``INFO``
``3``                      ``DEBUG``
=========================  =================

The minimum log level, the default output and the log format can also be changed by
passing the appropriate arguments to the constructor of :class:`Symfony\\Component\\HttpKernel\\Log\\Logger`.
To do so, :ref:`override the "logger" service definition <service-psr4-loader>`.

Logging a Message
-----------------

To log a message, inject the default logger in your controller::

    use Psr\Log\LoggerInterface;

    public function index(LoggerInterface $logger)
    {
        $logger->info('I just got the logger');
        $logger->error('An error occurred');

        $logger->critical('I left the oven on!', [
            // include extra "context" info in your logs
            'cause' => 'in_hurry',
        ]);

        // ...
    }

The ``logger`` service has different methods for different logging levels/priorities.
See `LoggerInterface`_ for a list of all of the methods on the logger.

Monolog
-------

Symfony integrates seamlessly with `Monolog`_, the most popular PHP logging
library, to create and store log messages in a variety of different places
and trigger various actions.

For instance, using Monolog you can configure the logger to do different things based on the
*level* of a message (e.g. :doc:`send an email when an error occurs </logging/monolog_email>`).

Run this command to install the Monolog based logger before using it:

.. code-block:: terminal

    $ composer require symfony/monolog-bundle

The following sections assume that Monolog is installed.

Where Logs are Stored
---------------------

By default, log entries are written to the ``var/log/dev.log`` file when you're in
the ``dev`` environment. In the ``prod`` environment, logs are written to ``var/log/prod.log``,
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

Symfony pre-configures some basic handlers in the default ``monolog.yaml``
config files. Check these out for some real-world examples.

This example uses *two* handlers: ``stream`` (to write to a file) and ``syslog``
to write logs using the :phpfunction:`syslog` function:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                # this "file_log" key could be anything
                file_log:
                    type: stream
                    # log to var/log/(environment).log
                    path: "%kernel.logs_dir%/%kernel.environment%.log"
                    # log *all* messages (debug is lowest level)
                    level: debug

                syslog_handler:
                    type: syslog
                    # log error-level messages and higher
                    level: error

    .. code-block:: xml

        <!-- config/packages/prod/monolog.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <!-- this "file_log" key could be anything -->
                <monolog:handler name="file_log"
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"/><!-- log *all* messages (debug is lowest level) -->

                <monolog:handler name="syslog_handler"
                    type="syslog"
                    level="error"/><!-- log error-level messages and higher -->
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        $container->loadFromExtension('monolog', [
            'handlers' => [
                // this "file_log" key could be anything
                'file_log' => [
                    'type'  => 'stream',
                    // log to var/logs/(environment).log
                    'path'  => '%kernel.logs_dir%/%kernel.environment%.log',
                    // log *all* messages (debug is lowest level)
                    'level' => 'debug',
                ],
                'syslog_handler' => [
                    'type'  => 'syslog',
                    // log error-level messages and higher
                    'level' => 'error',
                ],
            ],
        ]);

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

        # config/packages/prod/monolog.yaml
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

        <!-- config/packages/prod/monolog.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <!-- if *one* log is error or higher, pass *all* to file_log -->
                <monolog:handler name="filter_for_errors"
                    type="fingers_crossed"
                    action-level="error"
                    handler="file_log"
                />

                <!-- now passed *all* logs, but only if one log is error or higher -->
                <monolog:handler name="file_log"
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                />

                <!-- still passed *all* logs, and still only logs error or higher -->
                <monolog:handler name="syslog_handler"
                    type="syslog"
                    level="error"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        $container->loadFromExtension('monolog', [
            'handlers' => [
                'filter_for_errors' => [
                    'type'         => 'fingers_crossed',
                    // if *one* log is error or higher, pass *all* to file_log
                    'action_level' => 'error',
                    'handler'      => 'file_log',
                ],

                // now passed *all* logs, but only if one log is error or higher
                'file_log' => [
                    'type'  => 'stream',
                    'path'  => '%kernel.logs_dir%/%kernel.environment%.log',
                    'level' => 'debug',
                ],

                // still passed *all* logs, and still only logs error or higher
                'syslog_handler' => [
                    'type'  => 'syslog',
                    'level' => 'error',
                ],
            ],
        ]);

Now, if even one log entry has an ``error`` level or higher, then *all* log entries
for that request are saved to a file via the ``file_log`` handler. That means that
your log file will contain *all* the details about the problematic request - making
debugging much easier!

.. tip::

    The handler named "file_log" will not be included in the stack itself as
    it is used as a nested handler of the ``fingers_crossed`` handler.

.. note::

    If you want to override the ``monolog`` configuration via another config
    file, you will need to redefine the entire ``handlers`` stack. The configuration
    from the two files cannot be merged because the order matters and a merge does
    not allow to control the order.

All Built-in Handlers
---------------------

Monolog comes with *many* built-in handlers for emailing logs, sending them to Loggly,
or notifying you in Slack. These are documented inside of MonologBundle itself. For
a full list, see `Monolog Configuration`_.

How to Rotate your Log Files
----------------------------

Over time, log files can grow to be *huge*, both while developing and on
production. One best-practice solution is to use a tool like the `logrotate`_
Linux command to rotate log files before they become too large.

Another option is to have Monolog rotate the files for you by using the
``rotating_file`` handler. This handler creates a new log file every day
and can also remove old files automatically. To use it, set the ``type``
option of your handler to ``rotating_file``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
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

        <!-- config/packages/prod/monolog.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <!-- "max-files": max number of log files to keep
                     defaults to zero, which means infinite files -->
                <monolog:handler name="main"
                    type="rotating_file"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                    max-files="10"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        $container->loadFromExtension('monolog', [
            'handlers' => [
                'main' => [
                    'type'  => 'rotating_file',
                    'path'  => '%kernel.logs_dir%/%kernel.environment%.log',
                    'level' => 'debug',
                    // max number of log files to keep
                    // defaults to zero, which means infinite files
                    'max_files' => 10,
                ],
            ],
        ]);

Using a Logger inside a Service
-------------------------------

If your application uses :ref:`service autoconfiguration <services-autoconfigure>`,
any service whose class implements ``Psr\Log\LoggerAwareInterface`` will
receive a call to its method ``setLogger()`` with the default logger service
passed as a service.

If you want to use in your own services a pre-configured logger which uses a
specific channel (``app`` by default), you can either :ref:`autowire monolog channels <monolog-autowire-channels>`
or use the ``monolog.logger`` tag  with the ``channel`` property as explained in the
:ref:`Dependency Injection reference <dic_tags-monolog>`.

Adding Stacktraces from Exceptions
----------------------------------

To include stacktraces to your logs, set the ``include_stacktraces`` option on the "stream" handler to true and include the exception key in your logging statement::


```php
$logger->error($exception->getMessage(), ['exception' => $exception]);
```

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
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
                    include_stacktraces: true
                    path: "%kernel.logs_dir%/%kernel.environment%.log"

                # still passed *all* logs, and still only logs error or higher
                syslog_handler:
                    type: syslog
                    level: error

    .. code-block:: xml

        <!-- config/packages/prod/monolog.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <!-- if *one* log is error or higher, pass *all* to file_log -->
                <monolog:handler name="filter_for_errors"
                    type="fingers_crossed"
                    action-level="error"
                    handler="file_log"
                />

                <!-- now passed *all* logs, but only if one log is error or higher -->
                <monolog:handler name="file_log"
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                    include-stacktraces="true"
                />

                <!-- still passed *all* logs, and still only logs error or higher -->
                <monolog:handler name="syslog_handler"
                    type="syslog"
                    level="error"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        $container->loadFromExtension('monolog', [
            'handlers' => [
                'filter_for_errors' => [
                    'type'         => 'fingers_crossed',
                    // if *one* log is error or higher, pass *all* to file_log
                    'action_level' => 'error',
                    'handler'      => 'file_log',
                ],

                // now passed *all* logs, but only if one log is error or higher
                'file_log' => [
                    'type'  => 'stream',
                    'path'  => '%kernel.logs_dir%/%kernel.environment%.log',
                    'level' => 'debug',
                    'include_stacktraces' => true
                ],

                // still passed *all* logs, and still only logs error or higher
                'syslog_handler' => [
                    'type'  => 'syslog',
                    'level' => 'error',
                ],
            ],
        ]);


Adding extra Data to each Log (e.g. a unique request token)
-----------------------------------------------------------

Monolog also supports *processors*: functions that can dynamically add extra
information to your log entries.

See :doc:`/logging/processors` for details.

Learn more
----------

.. toctree::
    :maxdepth: 1

    logging/monolog_email
    logging/channels_handlers
    logging/formatter
    logging/processors
    logging/handlers
    logging/monolog_exclude_http_codes
    logging/monolog_console

.. toctree::
    :hidden:

    logging/monolog_regex_based_excludes

.. _`the twelve-factor app methodology`: https://12factor.net/logs
.. _PSR-3: https://www.php-fig.org/psr/psr-3/
.. _`stderr`: https://en.wikipedia.org/wiki/Standard_streams#Standard_error_(stderr)
.. _Monolog: https://github.com/Seldaek/monolog
.. _LoggerInterface: https://github.com/php-fig/log/blob/master/Psr/Log/LoggerInterface.php
.. _`logrotate`: https://github.com/logrotate/logrotate
.. _`Monolog Configuration`: https://github.com/symfony/monolog-bundle/blob/master/DependencyInjection/Configuration.php#L25
