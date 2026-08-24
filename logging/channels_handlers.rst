How to Log Messages to different Files
======================================

The Symfony Framework organizes log messages into channels. By default, there
are several channels, including ``app``, ``doctrine``, ``event``, ``security``, ``request``
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
                        # ...
                        # channels: ['!security']

    .. code-block:: php

        // config/packages/monolog.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'when@prod' => [
                'monolog' => [
                    'handlers' => [
                        'security' => [
                            // log all messages (since debug is the lowest level)
                            'level' => 'debug',
                            'type' => 'stream',
                            'path' => '%kernel.logs_dir%/security.log',
                            'channels' => ['security'],
                        ],
                        // an example of *not* logging security channel messages for this handler
                        'main' => [
                            // 'channels' => ['!security'],
                        ],
                    ],
                ],
            ],
        ]);

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

        # config/packages/monolog.yaml
        monolog:
            channels: ['foo', 'bar', 'foo_bar']

    .. code-block:: php

        // config/packages/monolog.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'monolog' => [
                'channels' => ['foo', 'bar', 'foo_bar'],
            ],
        ]);

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

Configure Logger Channels with Attributes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Starting from `Monolog`_ 3.5 you can also configure the logger channel
by using the ``#[WithMonologChannel]`` attribute directly on your service
class::

    // src/Service/MyFixtureService.php
    namespace App\Service;

    use Monolog\Attribute\WithMonologChannel;
    use Psr\Log\LoggerInterface;
    use Symfony\Bridge\Monolog\Logger;

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

.. _`MonologBundle`: https://github.com/symfony/monolog-bundle
.. _`Monolog`: https://github.com/Seldaek/monolog
