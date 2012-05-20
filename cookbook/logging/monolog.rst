.. index::
   single: Logging

How to use Monolog to write Logs
================================

Monolog_ is a logging library for PHP 5.3 used by Symfony2. It is
inspired by the Python LogBook library.

Usage
-----

In Monolog each logger defines a logging channel. Each channel has a
stack of handlers to write the logs (the handlers can be shared).

.. tip::

    When injecting the logger in a service you can
    :ref:`use a custom channel<dic_tags-monolog>` to see easily which
    part of the application logged the message.

The basic handler is the ``StreamHandler`` which writes logs in a stream
(by default in the ``app/logs/prod.log`` in the prod environment and
``app/logs/dev.log`` in the dev environment).

Monolog comes also with a powerful built-in handler for the logging in
prod environment: ``FingersCrossedHandler``. It allows you to store the
messages in a buffer and to log them only if a message reaches the
action level (ERROR in the configuration provided in the standard
edition) by forwarding the messages to another handler.

To log a message simply get the logger service from the container in
your controller::

    $logger = $this->get('logger');
    $logger->info('We just got the logger');
    $logger->err('An error occurred');

.. tip::

    Using only the methods of the
    :class:`Symfony\\Component\\HttpKernel\\Log\\LoggerInterface` interface
    allows to change the logger implementation without changing your code.

Using several handlers
~~~~~~~~~~~~~~~~~~~~~~

The logger uses a stack of handlers which are called successively. This
allows you to log the messages in several ways easily.

.. configuration-block::

    .. code-block:: yaml

        monolog:
            handlers:
                syslog:
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
            </monolog:config>
        </container>

The above configuration defines a stack of handlers which will be called
in the order where they are defined.

.. tip::

    The handler named "file" will not be included in the stack itself as
    it is used as a nested handler of the ``fingers_crossed`` handler.

.. note::

    If you want to change the config of MonologBundle in another config
    file you need to redefine the whole stack. It cannot be merged
    because the order matters and a merge does not allow to control the
    order.

Changing the formatter
~~~~~~~~~~~~~~~~~~~~~~

The handler uses a ``Formatter`` to format the record before logging
it. All Monolog handlers use an instance of
``Monolog\Formatter\LineFormatter`` by default but you can replace it
easily. Your formatter must implement
``Monolog\Formatter\FormatterInterface``.

.. configuration-block::

    .. code-block:: yaml

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

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/monolog http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

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

Adding some extra data in the log messages
------------------------------------------

Monolog allows to process the record before logging it to add some
extra data. A processor can be applied for the whole handler stack or
only for a specific handler.

A processor is simply a callable receiving the record as its first argument.

Processors are configured using the ``monolog.processor`` DIC tag. See the
:ref:`reference about it<dic_tags-monolog-processor>`.

Adding a Session/Request Token
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes it is hard to tell which entries in the log belong to which session
and/or request. The following example will add a unique token for each request
using a processor.

.. code-block:: php

    namespace Acme\MyBundle;

    use Symfony\Component\HttpFoundation\Session;

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

        services:
            monolog.formatter.session_request:
                class: Monolog\Formatter\LineFormatter
                arguments:
                    - "[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%%\n"

            monolog.processor.session_request:
                class: Acme\MyBundle\SessionRequestProcessor
                arguments:  [ @session ]
                tags:
                    - { name: monolog.processor, method: processRecord }

        monolog:
            handlers:
                main:
                    type: stream
                    path: "%kernel.logs_dir%/%kernel.environment%.log"
                    level: debug
                    formatter: monolog.formatter.session_request

.. note::

    If you use several handlers, you can also register the processor at the
    handler level instead of globally.

.. _Monolog: https://github.com/Seldaek/monolog
