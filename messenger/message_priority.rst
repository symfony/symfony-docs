Message Priority
================

By default messenger uses the first in, first out principle: messages will be
received in the same order they were sent (except the delayed ones).

Basic priority can be achieved by using :doc:`prioritized transports </messenger>`,
but only for different message types.

With AMQP and Beanstalkd transports, you can have a priority queue for messages
of the same type::

    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\Stamp\PriorityStamp;

    $bus->dispatch(
        (new Envelope($message))->with(new PriorityStamp(255))
    );

.. tip::

    Priorities between ``0`` (lowest) and ``255`` (highest) are supported.

Priority can be used together with delay::

    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\Stamp\DelayStamp;
    use Symfony\Component\Messenger\Stamp\PriorityStamp;

    $bus->dispatch(
        (new Envelope($message))->with(
            new PriorityStamp(255),
            new DelayStamp(5000)
        )
    );

When the time comes, if other messages in queue have lower priority, this one
will be delivered first.

Using with Beanstalkd transport
-------------------------------

Beanstalkd does not require any additional configuration. You can start using priority
stamp right away.

.. note::

    Internally Beanstalkd uses a different priority system, where priority
    can take values between ``2^32 - 1`` and ``0``, with zero being the highest priority.
    Messenger handles the transformation internally.

Using with AMQP transport
-------------------------

With AMQP transport, you need to enable priority for the queue in configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async:
                        dsn: "%env(MESSENGER_TRANSPORT_DSN)%"
                        options:
                            queues:
                                messenger:
                                    arguments:
                                        x-max-priority: 255

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xmlns:framework="http://symfony.com/schema/dic/symfony"
                   xsi:schemaLocation="http://symfony.com/schema/dic/services
                        https://symfony.com/schema/dic/services/services-1.0.xsd
                        http://symfony.com/schema/dic/symfony
                        https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:transport name="async"
                                         dsn="%env(MESSENGER_TRANSPORT_DSN)%"
                    >
                        <framework:options>
                            <framework:queues>
                                <framework:messenger>
                                    <framework:arguments>
                                        <framework:x-max-priority>255</framework:x-max-priority>
                                    </framework:arguments>
                                </framework:messenger>
                            </framework:queues>
                        </framework:options>
                    </framework:transport>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $messenger = $framework->messenger();

            $messenger->transport('async')
                ->dsn('%env(MESSENGER_TRANSPORT_DSN)%')
                ->options([
                    'queues' => [
                        'messenger' => [
                            'arguments' => [
                                'x-max-priority' => 255,
                            ],
                        ]
                    ]
                ]);
        };

.. caution::

    It's not safe to set ``x-max-priority`` for an existing queue.
    RabbitMQ can not change existing queue's configuration. Messenger will
    fail to auto-setup and priorities won't work.

.. note::

    `RabbitMQ manual recommends`_ using up to 10 different priority levels.
    For example, you may use 255 for high, 127 for medium and 0 for low priority.
    Having more levels may have an impact on performance.

.. _`RabbitMQ manual recommends`: https://www.rabbitmq.com/priority.html
