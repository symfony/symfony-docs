How to Log Messages to different Files
======================================

The Symfony Framework organizes log messages into channels. By default, there
are several channels, including ``doctrine``, ``event``, ``security``, ``request``
and more. The channel is printed in the log message and can also be used
to direct different channels to different places/files.

By default, Symfony logs every message into a single file (regardless of
the channel).

.. note::

    Each channel corresponds to a different logger service (``monolog.logger.XXX``)
    Use the ``php bin/console debug:container monolog`` command to see a full
    list of services and learn :ref:`how to autowire monolog channels <monolog-autowire-channels>`.

.. _logging-channel-handler:

Switching a Channel to a different Handler
------------------------------------------

Now, suppose you want to log the ``security`` channel to a different file.
To do this, create a new handler and configure it to log only messages
from the ``security`` channel. The following example does that only in the
``prod`` :ref:`configuration environment <configuration-environments>` but you
can do it in any (or all) environments:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
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
                    # ...
                    # channels: ['!security']

    .. code-block:: xml

        <!-- config/packages/prod/monolog.xml-->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <monolog:handler name="security" type="stream" path="%kernel.logs_dir%/security.log">
                    <monolog:channels>
                        <monolog:channel>security</monolog:channel>
                    </monolog:channels>
                </monolog:handler>

                <monolog:handler name="main" type="stream" path="%kernel.logs_dir%/main.log">
                    <!-- ... -->
                    <monolog:channels>
                        <monolog:channel>!security</monolog:channel>
                    </monolog:channels>
                </monolog:handler>
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $monolog->handler('security')
                ->type('stream')
                ->path('%kernel.logs_dir%/security.log')
                ->channels()->elements(['security']);

            $monolog->handler('main')
                 // ...

                ->channels()->elements(['!security']);
        };

.. caution::

    The ``channels`` configuration only works for top-level handlers. Handlers
    that are nested inside a group, buffer, filter, fingers crossed or other
    such handler will ignore this configuration and will process every message
    passed to them.

.. _yaml-specification:

You can specify the configuration in different ways:

.. code-block:: yaml

    channels: ~    # Include all the channels

    channels: foo  # Include only channel 'foo'
    channels: '!foo' # Include all channels, except 'foo'

    channels: [foo, bar]   # Include only channels 'foo' and 'bar'
    channels: ['!foo', '!bar'] # Include all channels, except 'foo' and 'bar'

Creating your own Channel
-------------------------

You can change the channel Monolog logs to one service at a time. This is done
either via the :ref:`configuration <monolog-channels-config>` below
or by tagging your service with :ref:`monolog.logger <dic_tags-monolog>` and
specifying which channel the service should log to. With the tag, the logger
that is injected into that service is preconfigured to use the channel you've
specified.

.. _monolog-channels-config:

Configure Additional Channels without Tagged Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also configure additional channels without the need to tag your services:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            channels: ['foo', 'bar', 'foo_bar']

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
                <monolog:channel>foo</monolog:channel>
                <monolog:channel>bar</monolog:channel>
                <monolog:channel>foo_bar</monolog:channel>
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
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
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Starting from `MonologBundle`_ 3.5 you can autowire different Monolog channels
by type-hinting your service arguments with the following syntax:
``Psr\Log\LoggerInterface $<camelCased channel name> + Logger``. The ``<channel>``
must have been :ref:`predefined in your Monolog configuration <monolog-channels-config>`.

For example to inject the service related to the ``foo_bar`` logger channel,
change your constructor like this:

.. code-block:: diff

        public function __construct(
    -     LoggerInterface $logger,
    +     LoggerInterface $fooBarLogger,
        ) {
        }

.. _`MonologBundle`: https://github.com/symfony/monolog-bundle
