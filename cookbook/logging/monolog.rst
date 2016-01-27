.. index::
   single: Logging

How to Use Monolog to Write Logs
================================

Monolog_ is a logging library for PHP used by Symfony. It is inspired by the
Python LogBook library.

Usage
-----

To log a message simply get the ``logger`` service from the container in
your controller::

    public function indexAction()
    {
        $logger = $this->get('logger');
        $logger->info('I just got the logger');
        $logger->error('An error occurred');

        // ...
    }

The ``logger`` service has different methods for different logging levels.
See LoggerInterface_ for details on which methods are available.

Handlers and Channels: Writing Logs to different Locations
----------------------------------------------------------

In Monolog each logger defines a logging channel, which organizes your log
messages into different "categories". Then, each channel has a stack of handlers
to write the logs (the handlers can be shared).

.. tip::

    When injecting the logger in a service you can
    :ref:`use a custom channel <dic_tags-monolog>` control which "channel"
    the logger will log to.

The basic handler is the ``StreamHandler`` which writes logs in a stream
(by default in the ``var/logs/prod.log`` in the prod environment and
``var/logs/dev.log`` in the dev environment).

Monolog comes also with a powerful built-in handler for the logging in
prod environment: ``FingersCrossedHandler``. It allows you to store the
messages in a buffer and to log them only if a message reaches the
action level (``error`` in the configuration provided in the Symfony Standard
Edition) by forwarding the messages to another handler.

Using several Handlers
~~~~~~~~~~~~~~~~~~~~~~

The logger uses a stack of handlers which are called successively. This
allows you to log the messages in several ways easily.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        monolog:
            handlers:
                applog:
                    type: stream
                    path: /var/log/symfony.log
                    level: error
                main:
                    type: fingers_crossed
                    action_level: warning
                    handler: file
                file:
                    type: stream
                    level: debug
                syslog:
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
                    name="applog"
                    type="stream"
                    path="/var/log/symfony.log"
                    level="error"
                />
                <monolog:handler
                    name="main"
                    type="fingers_crossed"
                    action-level="warning"
                    handler="file"
                />
                <monolog:handler
                    name="file"
                    type="stream"
                    level="debug"
                />
                <monolog:handler
                    name="syslog"
                    type="syslog"
                    level="error"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'applog' => array(
                    'type'  => 'stream',
                    'path'  => '/var/log/symfony.log',
                    'level' => 'error',
                ),
                'main' => array(
                    'type'         => 'fingers_crossed',
                    'action_level' => 'warning',
                    'handler'      => 'file',
                ),
                'file' => array(
                    'type'  => 'stream',
                    'level' => 'debug',
                ),
                'syslog' => array(
                    'type'  => 'syslog',
                    'level' => 'error',
                ),
            ),
        ));

The above configuration defines a stack of handlers which will be called
in the order they are defined.

.. tip::

    The handler named "file" will not be included in the stack itself as
    it is used as a nested handler of the ``fingers_crossed`` handler.

.. note::

    If you want to change the config of MonologBundle in another config
    file you need to redefine the whole stack. It cannot be merged
    because the order matters and a merge does not allow to control the
    order.

Changing the Formatter
~~~~~~~~~~~~~~~~~~~~~~

The handler uses a ``Formatter`` to format the record before logging
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

Adding some extra Data in the Log Messages
------------------------------------------

Monolog allows you to process the record before logging it to add some
extra data. A processor can be applied for the whole handler stack or
only for a specific handler.

A processor is simply a callable receiving the record as its first argument.
Processors are configured using the ``monolog.processor`` DIC tag. See the
:ref:`reference about it <dic_tags-monolog-processor>`.

Adding a Session/Request Token
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes it is hard to tell which entries in the log belong to which session
and/or request. The following example will add a unique token for each request
using a processor.

.. code-block:: php

    namespace AppBundle;

    use Symfony\Component\HttpFoundation\Session\Session;

    class SessionRequestProcessor
    {
        private $session;
        private $token;

        public function __construct(Session $session)
        {
            $this->session = $session;
        }

        public function processRecord(array $record)
        {
            if (null === $this->token) {
                try {
                    $this->token = substr($this->session->getId(), 0, 8);
                } catch (\RuntimeException $e) {
                    $this->token = '????????';
                }
                $this->token .= '-' . substr(uniqid(), -8);
            }
            $record['extra']['token'] = $this->token;

            return $record;
        }
    }

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            monolog.formatter.session_request:
                class: Monolog\Formatter\LineFormatter
                arguments:
                    - "[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n"

            monolog.processor.session_request:
                class: AppBundle\SessionRequestProcessor
                arguments:  ['@session']
                tags:
                    - { name: monolog.processor, method: processRecord }

        monolog:
            handlers:
                main:
                    type: stream
                    path: '%kernel.logs_dir%/%kernel.environment%.log'
                    level: debug
                    formatter: monolog.formatter.session_request

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
                <service id="monolog.formatter.session_request"
                    class="Monolog\Formatter\LineFormatter">

                    <argument>[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%&#xA;</argument>
                </service>

                <service id="monolog.processor.session_request"
                    class="AppBundle\SessionRequestProcessor">

                    <argument type="service" id="session" />
                    <tag name="monolog.processor" method="processRecord" />
                </service>
            </services>

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

        // app/config/config.php
        $container
            ->register(
                'monolog.formatter.session_request',
                'Monolog\Formatter\LineFormatter'
            )
            ->addArgument('[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n');

        $container
            ->register(
                'monolog.processor.session_request',
                'AppBundle\SessionRequestProcessor'
            )
            ->addArgument(new Reference('session'))
            ->addTag('monolog.processor', array('method' => 'processRecord'));

        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'main' => array(
                    'type'      => 'stream',
                    'path'      => '%kernel.logs_dir%/%kernel.environment%.log',
                    'level'     => 'debug',
                    'formatter' => 'monolog.formatter.session_request',
                ),
            ),
        ));

.. note::

    If you use several handlers, you can also register a processor at the
    handler level or at the channel level instead of registering it globally
    (see the following sections).

Registering Processors per Handler
----------------------------------

You can register a processor per handler using the ``handler`` option of
the ``monolog.processor`` tag:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            monolog.processor.session_request:
                class: AppBundle\SessionRequestProcessor
                arguments:  ['@session']
                tags:
                    - { name: monolog.processor, method: processRecord, handler: main }

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
                <service id="monolog.processor.session_request"
                    class="AppBundle\SessionRequestProcessor">

                    <argument type="service" id="session" />
                    <tag name="monolog.processor" method="processRecord" handler="main" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container
            ->register(
                'monolog.processor.session_request',
                'AppBundle\SessionRequestProcessor'
            )
            ->addArgument(new Reference('session'))
            ->addTag('monolog.processor', array('method' => 'processRecord', 'handler' => 'main'));

Registering Processors per Channel
----------------------------------

You can register a processor per channel using the ``channel`` option of
the ``monolog.processor`` tag:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            monolog.processor.session_request:
                class: AppBundle\SessionRequestProcessor
                arguments:  ['@session']
                tags:
                    - { name: monolog.processor, method: processRecord, channel: main }

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
                <service id="monolog.processor.session_request"
                    class="AppBundle\SessionRequestProcessor">

                    <argument type="service" id="session" />
                    <tag name="monolog.processor" method="processRecord" channel="main" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container
            ->register(
                'monolog.processor.session_request',
                'AppBundle\SessionRequestProcessor'
            )
            ->addArgument(new Reference('session'))
            ->addTag('monolog.processor', array('method' => 'processRecord', 'channel' => 'main'));

.. _Monolog: https://github.com/Seldaek/monolog
.. _LoggerInterface: https://github.com/php-fig/log/blob/master/Psr/Log/LoggerInterface.php
.. _`logrotate`: https://fedorahosted.org/logrotate/
