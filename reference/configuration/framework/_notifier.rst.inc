notifier
~~~~~~~~

chatter_transports
..................

**type**: ``array`` **default**: ``[]``

Configures the chatter transports used for chat notifications (e.g. Slack, Telegram).
The transport name is the key and the DSN is the value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                chatter_transports:
                    slack: '%env(SLACK_DSN)%'
                    telegram: '%env(TELEGRAM_DSN)%'

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->chatterTransport('slack', '%env(SLACK_DSN)%')
                ->chatterTransport('telegram', '%env(TELEGRAM_DSN)%')
            ;
        };

texter_transports
.................

**type**: ``array`` **default**: ``[]``

Configures the texter transports used for SMS notifications (e.g. Twilio, Vonage).
The transport name is the key and the DSN is the value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                texter_transports:
                    twilio: '%env(TWILIO_DSN)%'

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->texterTransport('twilio', '%env(TWILIO_DSN)%')
            ;
        };

channel_policy
..............

**type**: ``array`` **default**: ``[]``

Defines which channels are used for each notification importance level.
The key is the importance level (``urgent``, ``high``, ``medium``, ``low``)
and the value is an array of channel names:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                channel_policy:
                    urgent: ['sms', 'chat/slack', 'email']
                    high: ['chat/slack']
                    medium: ['browser']
                    low: ['browser']

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->channelPolicy('urgent', ['sms', 'chat/slack', 'email'])
                ->channelPolicy('high', ['chat/slack'])
                ->channelPolicy('medium', ['browser'])
                ->channelPolicy('low', ['browser'])
            ;
        };

admin_recipients
................

**type**: ``array`` **default**: ``[]``

Configures the recipients of notifications sent to administrators. Each recipient
can have an ``email`` and/or a ``phone`` number:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                admin_recipients:
                    - { email: 'admin@example.com' }
                    - { email: 'lead@example.com', phone: '+1555123456' }

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->adminRecipient()
                    ->email('admin@example.com')
            ;
            $framework->notifier()
                ->adminRecipient()
                    ->email('lead@example.com')
                    ->phone('+1555123456')
            ;
        };

message_bus
...........

**type**: ``string`` | ``false`` **default**: ``null`` or default bus if Messenger component is installed

Defines which message bus is used to dispatch notification messages. Set to
``false`` to call the notifier transport directly instead of dispatching
through the bus:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                message_bus: false

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->messageBus(false)
            ;
        };

.. seealso::

    For more details, see the :doc:`Notifier component </notifier>` documentation.
