Logging
=======

Symfony comes with two minimalist `PSR-3`_ loggers: :class:`Symfony\\Component\\HttpKernel\\Log\\Logger`
for the HTTP context and :class:`Symfony\\Component\\Console\\Logger\\ConsoleLogger` for the
CLI context. Following `the twelve-factor app methodology`_, they send messages
starting from the ``WARNING`` level to `stderr`_.

The minimum log level can be changed by setting the ``SHELL_VERBOSITY`` environment variable:

=========================  =================
``SHELL_VERBOSITY`` value  Minimum log level
=========================  =================
``-1``                     ``ERROR``
``1``                      ``NOTICE``
``2``                      ``INFO``
``3``                      ``DEBUG``
=========================  =================

The minimum log level, the default output and the log format can also be changed by
passing the appropriate arguments to the constructor of :class:`Symfony\\Component\\HttpKernel\\Log\\Logger`
and :class:`Symfony\\Component\\Console\\Logger\\ConsoleLogger`.

The :class:`Symfony\\Component\\HttpKernel\\Log\\Logger` class is available through the ``logger`` service.
To pass your configuration, you can :ref:`override the "logger" service definition <service-psr4-loader>`.

For more information about ``ConsoleLogger``, see :ref:`the console logger <console-logger>`.

Logging a Message
-----------------

To log a message, inject the default logger in your controller or service::

    use Psr\Log\LoggerInterface;
    // ...

    public function index(LoggerInterface $logger): Response
    {
        $logger->info('I just got the logger');
        $logger->error('An error occurred');

        // log messages can also contain placeholders, which are variable names
        // wrapped in braces whose values are passed as the second argument
        $logger->debug('User {userId} has logged in', [
            'userId' => $this->getUserId(),
        ]);

        $logger->critical('I left the oven on!', [
            // include extra "context" info in your logs
            'cause' => 'in_hurry',
        ]);

        // ...
    }

Adding placeholders to log messages is recommended because:

* It's easier to check log messages because many logging tools group log messages
  that are the same except for some variable values inside them;
* It's much easier to translate those log messages;
* It's better for security, because escaping can then be done by the
  implementation in a way that depends on the context.

The ``logger`` service has different methods for different logging levels/priorities.
See `LoggerInterface`_ for a list of all of the methods on the logger.

Monolog
-------

Symfony integrates with `Monolog`_, the most popular PHP logging
library, to create and store log messages in a variety of different places
and trigger various actions.

For instance, using Monolog you can configure the logger to do different things based on the
*level* of a message (e.g. :doc:`send an email when an error occurs </logging/monolog_email>`).

Run this command to install the Monolog-based logger before using it:

.. code-block:: terminal

    $ composer require symfony/monolog-bundle

The following sections assume that Monolog is installed.

Where Logs Are Stored
---------------------

By default, log entries are written to the ``var/log/dev.log`` file when you're
in the ``dev`` environment.

In the ``prod`` environment, logs are written to the `STDERR PHP stream`_, which
works best in modern containerized applications deployed to servers without
disk write permissions.

If you prefer to store production logs in a file, set the ``path`` option of your
log handler(s) to the desired file path (e.g. ``var/log/prod.log``). Monolog
creates the log directory automatically if it doesn't exist.

.. versionadded:: 7.3

    Deferring the log directory creation to Monolog was introduced in Symfony 7.3.
    Previously, the Kernel always created the log directory at build time, even
    when not using file-based logging (which caused issues on read-only filesystems).

Handlers: Writing Logs to Different Locations
---------------------------------------------

The logger has a stack of *handlers*, and each can be used to write the log entries
to different locations (e.g. files, database, Slack, etc.).

Symfony pre-configures some basic handlers in the default ``monolog.yaml``
config files. Check these files for some real-world examples.

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
        use Psr\Log\LogLevel;
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            // this "file_log" key could be anything
            $monolog->handler('file_log')
                ->type('stream')
                // log to var/logs/(environment).log
                ->path('%kernel.logs_dir%/%kernel.environment%.log')
                // log *all* messages (LogLevel::DEBUG is lowest level)
                ->level(LogLevel::DEBUG);

            $monolog->handler('syslog_handler')
                ->type('syslog')
                // log error-level messages and higher
                ->level(LogLevel::ERROR);
        };

This defines a stack of handlers. Each handler can define a ``priority``
(default ``0``) to control its position in the stack. Handlers with a higher
priority are called first, while those with the same priority keep the order in
which they are defined:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                file_log:
                    type: stream
                    path: "%kernel.logs_dir%/%kernel.environment%.log"

                syslog_handler:
                    type: syslog
                    priority: 10 # called first

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
                <monolog:handler name="file_log"
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                />

                <!-- called first -->
                <monolog:handler name="syslog_handler"
                    type="syslog"
                    priority="10"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        use Psr\Log\LogLevel;
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $monolog->handler('file_log')
                ->type('stream')
                ->path('%kernel.logs_dir%/%kernel.environment%.log')
            ;

            $monolog->handler('syslog_handler')
                ->type('syslog')
                ->priority(10) // called first
            ;
        };

.. note::

    When adding handlers in other configuration files, it's recommended to set
    an explicit priority to ensure they are ordered as expected.

.. tip::

    Use the ``enabled`` option to enable or disable a handler without removing
    its configuration:

    .. code-block:: yaml

        monolog:
            handlers:
                file_log:
                    type: stream
                    path: "%kernel.logs_dir%/%kernel.environment%.log"
                    level: debug
                    enabled: false
                # ...

    When ``enabled`` is set to ``false``, the handler is completely ignored.

    .. versionadded:: 3.11.0

        The ``enabled`` option was introduced in Monolog 3.11.0.

.. _logging-handler-fingers_crossed:

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
        use Psr\Log\LogLevel;
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $monolog->handler('filter_for_errors')
                ->type('fingers_crossed')
                // if *one* log is error or higher, pass *all* to file_log
                ->actionLevel(LogLevel::ERROR)
                ->handler('file_log')
            ;

            // now passed *all* logs, but only if one log is error or higher
            $monolog->handler('file_log')
                ->type('stream')
                ->path('%kernel.logs_dir%/%kernel.environment%.log')
                ->level(LogLevel::DEBUG)
            ;

            // still passed *all* logs, and still only logs error or higher
            $monolog->handler('syslog_handler')
                ->type('syslog')
                ->level(LogLevel::ERROR)
            ;
        };

Now, if even one log entry has a ``LogLevel::ERROR`` level or higher, then *all* log entries
for that request are saved to a file via the ``file_log`` handler. That means that
your log file will contain *all* the details about the problematic request - making
debugging much easier! To avoid triggering it for errors like 404 or 403, see
:ref:`how to exclude specific HTTP codes from the log <logging-excluded-http-codes>`.

.. tip::

    The handler named "file_log" will not be included in the stack itself as
    it is used as a nested handler of the ``fingers_crossed`` handler.

.. _logging-excluded-http-codes:

Excluding Specific HTTP Codes from the Log
..........................................

Some HTTP errors, such as 403 and 404, are usually not worth logging and can
flood your logs. When using a ``fingers_crossed`` handler, add those HTTP codes
to the ``excluded_http_codes`` option so they don't activate the handler:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                main:
                    # ...
                    type: fingers_crossed
                    handler: ...
                    # 400 errors are only excluded for the URL paths that match
                    # any of the given regular expressions
                    excluded_http_codes: [403, 404, { 400: ['^/foo', '^/bar'] }]

    .. code-block:: xml

        <!-- config/packages/prod/monolog.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <monolog:handler type="fingers_crossed" name="main" handler="...">
                    <!-- ... -->
                    <monolog:excluded-http-code code="403"/>
                    <monolog:excluded-http-code code="404"/>
                    <!-- 400 errors are only excluded for the URL paths that match
                         any of the given regular expressions -->
                    <monolog:excluded-http-code code="400">
                        <monolog:url>^/foo</monolog:url>
                        <monolog:url>^/bar</monolog:url>
                    </monolog:excluded-http-code>
                </monolog:handler>
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $mainHandler = $monolog->handler('main')
                // ...
                ->type('fingers_crossed')
                ->handler('...')
            ;

            $mainHandler->excludedHttpCode()->code(403);
            $mainHandler->excludedHttpCode()->code(404);
            // 400 errors are only excluded for the URL paths that match
            // any of the given regular expressions
            $mainHandler->excludedHttpCode()->code(400)->urls(['^/foo', '^/bar']);
        };

.. warning::

    Combining ``excluded_http_codes`` with a ``passthru_level`` lower than
    ``error`` (i.e. ``debug``, ``info``, ``notice`` or ``warning``) will not
    actually exclude log messages for those HTTP codes because they are logged
    with a level of ``error`` or higher and ``passthru_level`` takes precedence
    over the HTTP codes being listed in ``excluded_http_codes``.

.. _logging-formatter:

Changing the Format of Log Entries
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Each logging handler uses a *formatter* to turn the log record into the text
that is finally logged. Most handlers use ``Monolog\Formatter\LineFormatter``
by default, but you can replace it with any service that implements
``Monolog\Formatter\FormatterInterface``.

For example, to log entries in JSON format, set the ``formatter`` option of the
handler to the ID of the built-in ``JsonFormatter`` service:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml (and/or config/packages/dev/monolog.yaml)
        monolog:
            handlers:
                file:
                    type: stream
                    level: debug
                    formatter: 'monolog.formatter.json'

    .. code-block:: xml

        <!-- config/packages/prod/monolog.xml (and/or config/packages/dev/monolog.xml) -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <monolog:handler
                    name="file"
                    type="stream"
                    level="debug"
                    formatter="monolog.formatter.json"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php (and/or config/packages/dev/monolog.php)
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $monolog->handler('file')
                ->type('stream')
                ->level('debug')
                ->formatter('monolog.formatter.json')
            ;
        };

Monolog provides many built-in `formatters`_. Most of them are registered as
services whose IDs start with ``monolog.formatter.`` (e.g.
``monolog.formatter.html``, ``monolog.formatter.logstash``), so you can use
them in the ``formatter`` option.

.. _logging-channels:

Logging Channels
----------------

The Symfony Framework organizes log messages into channels. By default, there
are several channels, including ``app``, ``doctrine``, ``event``, ``security``, ``request``
and more. The channel is printed in the log message and can also be used
to direct different channels to different places/files.

By default, Symfony logs every message into a single file (regardless of
the channel).

.. note::

    Each channel corresponds to a different logger service (``monolog.logger.XXX``).
    Use the ``php bin/console debug:container monolog`` command to see a full
    list of services and learn :ref:`how to autowire monolog channels <monolog-autowire-channels>`.

.. _logging-channel-handler:

Switching a Channel to a Different Handler
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to log the ``security`` channel to a different file.
To do this, create a new handler and configure it to log only messages
from the ``security`` channel. The following example does that only in the
``prod`` :ref:`configuration environment <configuration-environments>`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/monolog.yaml
        when@prod:
            monolog:
                handlers:
                    security:
                        # log all messages (since debug is the lowest level)
                        level:    debug
                        type:     stream
                        path:     '%kernel.logs_dir%/security.log'
                        channels: [security]

                    # an example of *not* logging security channel messages for this handler
                    main:
                        type:     stream
                        path:     '%kernel.logs_dir%/%kernel.environment%.log'
                        channels: ['!security']

    .. code-block:: xml

        <!-- config/packages/monolog.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <when env="prod">
                <monolog:config>
                    <!-- log all messages (since debug is the lowest level) -->
                    <monolog:handler name="security" type="stream" level="debug"
                        path="%kernel.logs_dir%/security.log"
                    >
                        <monolog:channels>
                            <monolog:channel>security</monolog:channel>
                        </monolog:channels>
                    </monolog:handler>

                    <!-- an example of *not* logging security channel messages for this handler -->
                    <monolog:handler name="main" type="stream"
                        path="%kernel.logs_dir%/%kernel.environment%.log"
                    >
                        <monolog:channels>
                            <monolog:channel>!security</monolog:channel>
                        </monolog:channels>
                    </monolog:handler>
                </monolog:config>
            </when>
        </container>

    .. code-block:: php

        // config/packages/monolog.php
        use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog, ContainerConfigurator $container): void {
            if ('prod' === $container->env()) {
                $monolog->handler('security')
                    // log all messages (since debug is the lowest level)
                    ->level('debug')
                    ->type('stream')
                    ->path('%kernel.logs_dir%/security.log')
                    ->channels()->elements(['security']);

                // an example of *not* logging security channel messages for this handler
                $monolog->handler('main')
                    ->type('stream')
                    ->path('%kernel.logs_dir%/%kernel.environment%.log')
                    ->channels()->elements(['!security']);
            }
        };

.. warning::

    The ``channels`` configuration only works for top-level handlers. Handlers
    that are nested inside a group, buffer, filter, fingers crossed or other
    such handler will ignore this configuration and will process every message
    passed to them.

.. _yaml-specification:

You can specify the configuration in different ways:

.. code-block:: yaml

    # omit the 'channels' option to include all channels

    channels: foo  # Include only channel 'foo'
    channels: '!foo' # Include all channels, except 'foo'

    channels: [foo, bar]   # Include only channels 'foo' and 'bar'
    channels: ['!foo', '!bar'] # Include all channels, except 'foo' and 'bar'

Creating Your Own Channel
~~~~~~~~~~~~~~~~~~~~~~~~~

You can change the channel that Monolog uses for each service. Do this either
via the :ref:`configuration <monolog-channels-config>` below or by tagging
your service with :ref:`monolog.logger <dic_tags-monolog>` and specifying
which channel the service should log to. With the tag, the logger injected
into that service is preconfigured to use the channel you've specified.

.. _monolog-channels-config:

Configure Additional Channels without Tagged Services
.....................................................

You can also configure additional channels without the need to tag your services:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/monolog.yaml
        monolog:
            channels: ['foo', 'bar', 'foo_bar']

    .. code-block:: xml

        <!-- config/packages/monolog.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <monolog:channel>foo</monolog:channel>
                <monolog:channel>bar</monolog:channel>
                <monolog:channel>foo_bar</monolog:channel>
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/monolog.php
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $monolog->channels(['foo', 'bar', 'foo_bar']);
        };

Symfony automatically registers one service per channel (in this example, the
channel ``foo`` creates a service called ``monolog.logger.foo``). In order to
inject this service into others, you must update the service configuration to
:ref:`choose the specific service to inject <services-wire-specific-service>`.

.. _monolog-autowire-channels:

How to Autowire Logger Channels
...............................

You can autowire different Monolog channels by type-hinting your service
arguments with the following syntax:
``Psr\Log\LoggerInterface $<camelCased channel name> + Logger``. The ``<channel>``
must have been :ref:`predefined in your Monolog configuration <monolog-channels-config>`.

For example, to inject the service related to the ``foo_bar`` logger channel,
change your constructor like this:

.. code-block:: diff

        public function __construct(
    -     LoggerInterface $logger,
    +     LoggerInterface $fooBarLogger,
        ) {
        }

Configure Logger Channels with Attributes
.........................................

You can also configure the logger channel by using the ``#[WithMonologChannel]``
attribute directly on your service class::

    // src/Service/MyFixtureService.php
    namespace App\Service;

    use Monolog\Attribute\WithMonologChannel;
    use Psr\Log\LoggerInterface;

    #[WithMonologChannel('fixtures')]
    class MyFixtureService
    {
        public function __construct(LoggerInterface $logger)
        {
            // ...
        }
    }

This way you can avoid declaring your service manually to use a specific
channel.

.. versionadded:: 3.5

    The ``#[WithMonologChannel]`` attribute was introduced in Monolog 3.5.0.

When a service needs loggers from different channels, add the attribute to
each constructor argument instead of the class::

    // src/Service/MyFixtureService.php
    namespace App\Service;

    use Monolog\Attribute\WithMonologChannel;
    use Psr\Log\LoggerInterface;

    class MyFixtureService
    {
        public function __construct(
            #[WithMonologChannel('fixtures')]
            private LoggerInterface $fixtureLogger,
            #[WithMonologChannel('doctrine')]
            private LoggerInterface $databaseLogger,
            // arguments without the attribute get the logger of the channel
            // defined on the class (if any) or the default logger
            private LoggerInterface $logger,
        ) {
        }
    }

.. versionadded:: 4.1

    Using the ``#[WithMonologChannel]`` attribute on constructor arguments
    was introduced in MonologBundle 4.1.

All Built-in Handlers
---------------------

Monolog comes with *many* built-in handlers for emailing logs, sending them to Loggly,
or notifying you in Slack. These are documented inside of MonologBundle itself. For
a full list, see `Monolog Configuration`_.

.. _logging-elasticsearch-handler:

Sending Logs to Elasticsearch
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony provides the
:class:`Symfony\\Bridge\\Monolog\\Handler\\ElasticsearchLogstashHandler`, which
communicates directly with the HTTP interface of Elasticsearch. This means it
will slow down your application if Elasticsearch takes time to answer, even if
all HTTP calls are done asynchronously.

To use it, declare it as a service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # the constructor arguments are optional (shown values are the defaults)
            Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler:
                arguments:
                    $endpoint: 'http://127.0.0.1:9200'
                    $index: 'monolog'
                    $client: null
                    $level: !php/enum Monolog\Level::Debug
                    $bubble: true
                    $elasticsearchVersion: '1.0.0'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <!-- the constructor arguments are optional (shown values are the defaults) -->
                <service id="Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler">
                    <argument key="$endpoint">http://127.0.0.1:9200</argument>
                    <argument key="$index">monolog</argument>
                    <argument key="$client">null</argument>
                    <argument key="$level" type="constant">Monolog\Level::Debug</argument>
                    <argument key="$bubble">true</argument>
                    <argument key="$elasticsearchVersion">1.0.0</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Monolog\Level;
        use Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler;

        // the constructor arguments are optional (shown values are the defaults)
        $container->register(ElasticsearchLogstashHandler::class)
            ->setArguments([
                '$endpoint' => 'http://127.0.0.1:9200',
                '$index' => 'monolog',
                '$client' => null,
                '$level' => Level::Debug,
                '$bubble' => true,
                '$elasticsearchVersion' => '1.0.0',
            ])
        ;

Then reference it in the Monolog configuration. Without buffering, each log
entry makes an HTTP request to Elasticsearch, which is fine in a development
environment. In a production environment, it's highly recommended to wrap this
handler in a handler with buffering capabilities (like the
`FingersCrossedHandler`_ or `BufferHandler`_) in order to call Elasticsearch
only once with a bulk push. For even better performance and fault tolerance, a
proper `ELK stack`_ is recommended:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                main:
                    type: fingers_crossed
                    handler: es

                es:
                    type: service
                    id: Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler

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
                <monolog:handler
                    name="main"
                    type="fingers_crossed"
                    handler="es"
                />
                <monolog:handler
                    name="es"
                    type="service"
                    id="Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        use Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler;
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $monolog->handler('main')
                ->type('fingers_crossed')
                ->handler('es')
            ;
            $monolog->handler('es')
                ->type('service')
                ->id(ElasticsearchLogstashHandler::class)
            ;
        };

How to Rotate your Log Files
----------------------------

Over time, log files can grow to be *huge*, both while developing and in
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
        use Psr\Log\LogLevel;
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $monolog->handler('main')
                ->type('rotating_file')
                ->path('%kernel.logs_dir%/%kernel.environment%.log')
                ->level(LogLevel::DEBUG)
                // max number of log files to keep
                // defaults to zero, which means infinite files
                ->maxFiles(10);
        };

Using a Logger inside a Service
-------------------------------

If your application uses :ref:`service autoconfiguration <services-autoconfigure>`,
any service whose class implements ``Psr\Log\LoggerAwareInterface`` will
receive a call to its method ``setLogger()`` with the default logger service
passed as a service.

If you want to use a pre-configured logger which uses a specific channel
(``app`` by default) in your own services, you can either
:ref:`autowire monolog channels <monolog-autowire-channels>` or use the
``monolog.logger`` tag with the ``channel`` property as explained in the
:ref:`Dependency Injection reference <dic_tags-monolog>`.

.. _logging-processors:

Adding Extra Data to Log Messages via Processors
------------------------------------------------

Monolog *processors* modify every record before it's logged, usually to add
some extra data to it. A processor can be applied to the whole handler stack
or only to a specific handler or channel.

A processor is a callable that receives the record as its first argument and
returns it. Processors are registered using the
:ref:`monolog.processor <dic_tags-monolog-processor>` service tag.

Adding a Session/Request Token
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes it is hard to tell which entries in the log belong to which session
and/or request. The following example will add a unique token for each request
using a processor::

    // src/Logger/SessionRequestProcessor.php
    namespace App\Logger;

    use Monolog\LogRecord;
    use Monolog\Processor\ProcessorInterface;
    use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
    use Symfony\Component\HttpFoundation\RequestStack;

    class SessionRequestProcessor implements ProcessorInterface
    {
        public function __construct(
            private RequestStack $requestStack,
        ) {
        }

        // method is called for each log record; optimize it to not hurt performance
        public function __invoke(LogRecord $record): LogRecord
        {
            try {
                $session = $this->requestStack->getSession();
            } catch (SessionNotFoundException $e) {
                return $record;
            }
            if (!$session->isStarted()) {
                return $record;
            }

            $sessionId = substr($session->getId(), 0, 8) ?: '????????';

            $record->extra['token'] = $sessionId.'-'.substr(uniqid('', true), -8);

            return $record;
        }
    }

Next, register your class as a service, as well as a formatter that uses the extra
information:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            monolog.formatter.session_request:
                class: Monolog\Formatter\LineFormatter
                arguments:
                    - "[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n"

            App\Logger\SessionRequestProcessor:
                tags:
                    - { name: monolog.processor }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="monolog.formatter.session_request"
                    class="Monolog\Formatter\LineFormatter">

                    <argument>[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%&#xA;</argument>
                </service>

                <service id="App\Logger\SessionRequestProcessor">
                    <tag name="monolog.processor"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Logger\SessionRequestProcessor;
        use Monolog\Formatter\LineFormatter;

        $container
            ->register('monolog.formatter.session_request', LineFormatter::class)
            ->addArgument('[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n');

        $container
            ->register(SessionRequestProcessor::class)
            ->addTag('monolog.processor');

Finally, set the formatter to be used on any handler you want:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                main:
                    type: stream
                    path: '%kernel.logs_dir%/%kernel.environment%.log'
                    level: debug
                    formatter: monolog.formatter.session_request

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
                <monolog:handler
                    name="main"
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                    formatter="monolog.formatter.session_request"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $monolog->handler('main')
                ->type('stream')
                ->path('%kernel.logs_dir%/%kernel.environment%.log')
                ->level('debug')
                ->formatter('monolog.formatter.session_request')
            ;
        };

If you use several handlers, you can also register a processor at the
handler level or at the channel level instead of registering it globally
(see the following sections).

When registering a new processor, instead of adding the tag manually in your
configuration files, you can use the ``#[AsMonologProcessor]`` attribute to
apply it on the processor class::

    // src/Logger/SessionRequestProcessor.php
    namespace App\Logger;

    use Monolog\Attribute\AsMonologProcessor;

    #[AsMonologProcessor]
    class SessionRequestProcessor
    {
        // ...
    }

The ``#[AsMonologProcessor]`` attribute takes these optional arguments:

* ``channel``: the logging channel the processor should be pushed to;
* ``handler``: the handler the processor should be pushed to;
* ``method``: the method that processes the records (useful when applying
  the attribute to the entire class instead of a single method).

Symfony's MonologBridge provides the following processors, which you can
register in your application:

:class:`Symfony\\Bridge\\Monolog\\Processor\\DebugProcessor`
    Collects the log records (and counts the errors among them) so they can be
    displayed in the Symfony profiler.

:class:`Symfony\\Bridge\\Monolog\\Processor\\TokenProcessor`
    Adds information from the current user's token to the record: the
    username, roles and whether the user is authenticated.

:class:`Symfony\\Bridge\\Monolog\\Processor\\SwitchUserTokenProcessor`
    Adds information about the user who is impersonating the logged-in user:
    username, roles and whether the user is authenticated.

:class:`Symfony\\Bridge\\Monolog\\Processor\\WebProcessor`
    Overrides data from the request using the data inside Symfony's request
    object.

:class:`Symfony\\Bridge\\Monolog\\Processor\\RouteProcessor`
    Adds information about the current route (controller, action, route parameters).

:class:`Symfony\\Bridge\\Monolog\\Processor\\ConsoleCommandProcessor`
    Adds information about the current console command.

.. seealso::

    Check out the `built-in Monolog processors`_ to learn more about how to
    create these processors.

Registering Processors per Handler
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can register a processor per handler using the ``handler`` option of
the ``monolog.processor`` tag:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Logger\SessionRequestProcessor:
                tags:
                    - { name: monolog.processor, handler: main }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="App\Logger\SessionRequestProcessor">
                    <tag name="monolog.processor" handler="main"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php

        // ...
        $container
            ->register(SessionRequestProcessor::class)
            ->addTag('monolog.processor', ['handler' => 'main']);

Registering Processors per Channel
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, processors are applied to all channels. Add the ``channel`` option
to the ``monolog.processor`` tag to only apply a processor to the given channel:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Logger\SessionRequestProcessor:
                tags:
                    - { name: monolog.processor, channel: 'app' }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="App\Logger\SessionRequestProcessor">
                    <tag name="monolog.processor" channel="app"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php

        // ...
        $container
            ->register(SessionRequestProcessor::class)
            ->addTag('monolog.processor', ['channel' => 'app']);

Handling Logs in Long-Running Processes
---------------------------------------

During long-running processes, logs can be accumulated into Monolog and cause some
buffer overflows, memory increases or even illogical logs. Monolog in-memory data
can be cleared using the ``reset()`` method on a ``Monolog\Logger`` instance.
This should typically be called between every job or task that a long-running process
is processing.

Learn More
----------

.. toctree::
    :maxdepth: 1

    logging/monolog_email

.. _`the twelve-factor app methodology`: https://12factor.net/logs
.. _`PSR-3`: https://www.php-fig.org/psr/psr-3/
.. _`stderr`: https://en.wikipedia.org/wiki/Standard_streams#Standard_error_(stderr)
.. _`Monolog`: https://github.com/Seldaek/monolog
.. _`LoggerInterface`: https://github.com/php-fig/log/blob/master/src/LoggerInterface.php
.. _`logrotate`: https://github.com/logrotate/logrotate
.. _`Monolog Configuration`: https://github.com/symfony/monolog-bundle/blob/4.x/src/DependencyInjection/Configuration.php
.. _`STDERR PHP stream`: https://www.php.net/manual/en/features.commandline.io-streams.php
.. _`formatters`: https://github.com/Seldaek/monolog/tree/main/src/Monolog/Formatter
.. _`FingersCrossedHandler`: https://github.com/Seldaek/monolog/blob/main/src/Monolog/Handler/FingersCrossedHandler.php
.. _`BufferHandler`: https://github.com/Seldaek/monolog/blob/main/src/Monolog/Handler/BufferHandler.php
.. _`ELK stack`: https://www.elastic.co/what-is/elk-stack
.. _`built-in Monolog processors`: https://github.com/Seldaek/monolog/tree/main/src/Monolog/Processor
