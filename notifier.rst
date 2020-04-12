.. index::
   single: Notifier

Creating and Sending Notifications
==================================

.. versionadded:: 5.0

    The Notifier component was introduced in Symfony 5.0 as an
    :doc:`experimental feature </contributing/code/experimental>`.

Installation
------------

Current web applications use many different channels to send messages to
the users (e.g. SMS, Slack messages, emails, push notifications, etc.). The
Notifier component in Symfony is an abstraction on top of all these
channels. It provides a dynamic way to manage how the messages are sent.
Get the Notifier installed using:

.. code-block:: terminal

    $ composer require symfony/notifier

Channels: Chatters, Texters, Email and Browser
----------------------------------------------

The notifier component can send notifications to different channels. Each
channel can integrate with different providers (e.g. Slack or Twilio SMS)
by using transports.

The notifier component supports the following channels:

* `SMS <SMS Channel>`_ sends notifications to phones via SMS messages
* `Chat <Chat Channel>`_ sends notifications to chat services like Slack
  and Telegram;
* `Email <Email Channel>`_ integrates the :doc:`Symfony Mailer </mailer>`;
* Browser uses :ref:`flash messages <flash-messages>`.

.. tip::

    Use :doc:`secrets </configuration/secrets>` to securily store your
    API's tokens.

.. _notifier-texter-dsn:

SMS Channel
~~~~~~~~~~~

The SMS channel uses :class:`Symfony\\Component\\Notifier\\Texter` classes
to send SMS messages to mobile phones. This feature requires subscribing to
a third-party service that sends SMS messages. Symfony provides integration
with a couple popular SMS services:

========  =============================  ===========================================
Service   Package                        DSN
========  =============================  ===========================================
Twilio    ``symfony/twilio-notifier``    ``twilio://SID:TOKEN@default?from=FROM``
Nexmo     ``symfony/nexmo-notifier``     ``nexmo://KEY:SECRET@default?from=FROM``
OvhCloud  ``symfony/ovhcloud-notifier``  ``ovhcloud://KEY:SECRET@default?from=FROM``
Sinch     ``symfony/sinch-notifier``     ``sinch://ACCOUNT_ID:AUTH_TOKEN@default?from=FROM``
========  =============================  ===========================================

.. versionadded:: 5.1

    The OvhCloud and Sinch integrations were introduced in Symfony 5.1.

To enable a texter, add the correct DSN in your ``.env`` file and
configure the ``texter_transports``:

.. code-block:: bash

    # .env
    TWILIO_DSN=twilio://SID:TOKEN@default?from=FROM

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                texter_transports:
                    twilio: '%env(TWILIO_DSN)%'

    .. code-block:: xml

        <!-- config/packages/notifier.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:notifier>
                    <framework:texter-transport name="twilio">
                        %env(TWILIO_DSN)%
                    </framework:texter-transport>
                </framework:notifier>
            </framework:config>
        </container>

    .. code-block:: php

        # config/packages/notifier.php
        $container->loadFromExtension('framework', [
            'notifier' => [
                'texter_transports' => [
                    'twilio' => '%env(TWILIO_DSN)%',
                ],
            ],
        ]);

.. _notifier-chatter-dsn:

Chat Channel
~~~~~~~~~~~~

The chat channel is used to send chat messages to users by using
:class:`Symfony\\Component\\Notifier\\Chatter` classes. Symfony provides
integration with these chat services:

==========  ===============================  ============================================
Service     Package                          DSN
==========  ===============================  ============================================
Slack       ``symfony/slack-notifier``       ``slack://default/ID``
Telegram    ``symfony/telegram-notifier``    ``telegram://TOKEN@default?channel=CHAT_ID``
Mattermost  ``symfony/mattermost-notifier``  ``mattermost://TOKEN@ENDPOINT?channel=CHANNEL``
RocketChat  ``symfony/rocketchat-notifier``  ``rocketchat://TOKEN@ENDPOINT?channel=CHANNEL``
==========  ===============================  ============================================

.. versionadded:: 5.1

    The Mattermost and RocketChat integrations were introduced in Symfony
    5.1. The Slack DSN changed in Symfony 5.1 to use Slack Incoming
    Webhooks instead of legacy tokens.

Chatters are configured using the ``chatter_transports`` setting:

.. code-block:: bash

    # .env
    SLACK_DSN=slack://default/ID

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                chatter_transports:
                    slack: '%env(SLACK_DSN)%'

    .. code-block:: xml

        <!-- config/packages/notifier.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:notifier>
                    <framework:chatter-transport name="slack">
                        %env(SLACK_DSN)%
                    </framework:chatter-transport>
                </framework:notifier>
            </framework:config>
        </container>

    .. code-block:: php

        # config/packages/notifier.php
        $container->loadFromExtension('framework', [
            'notifier' => [
                'chatter_transports' => [
                    'slack' => '%env(SLACK_DSN)%',
                ],
            ],
        ]);

Email Channel
~~~~~~~~~~~~~

The email channel uses the :doc:`Symfony Mailer </mailer>` to send
notifications using the special
:class:`Symfony\\Bridge\\Twig\\Mime\\NotificationEmail`. It is
required to install the Twig bridge along with the Inky and CSS Inliner
Twig extensions:

.. code-block:: terminal

    $ composer require symfony/twig-pack twig/cssinliner-extra twig/inky-extra

After this, :ref:`configure the mailer <mailer-transport-setup>`. You can
also set the default "from" email address that should be used to send the
notification emails:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        framework:
            mailer:
                dsn: '%env(MAILER_DSN)%'
                envelope:
                    sender: 'notifications@example.com'

    .. code-block:: xml

        <!-- config/packages/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:mailer
                    dsn="%env(MAILER_DSN)%"
                >
                    <framework:envelope
                        sender="notifications@example.com"
                    />
                </framework:mailer>
            </framework:config>
        </container>

    .. code-block:: php

        # config/packages/mailer.php
        $container->loadFromExtension('framework', [
            'mailer' => [
                'dsn' => '%env(MAILER_DSN)%',
                'envelope' => [
                    'sender' => 'notifications@example.com',
                ],
            ],
        ]);

Configure to use Failover or Round-Robin Transports
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Besides configuring one or more separate transports, you can also use the
special ``||`` and ``&&`` characters to implement a failover or round-robin
transport:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                chatter_transports:
                    # Send notifications to Slack and use Telegram if
                    # Slack errored
                    main: '%env(SLACK_DSN)% || %env(TELEGRAM_DSN)%'

                    # Send notifications to the next scheduled transport calculated by round robin
                    roundrobin: '%env(SLACK_DSN)% && %env(TELEGRAM_DSN)%'

    .. code-block:: xml

        <!-- config/packages/notifier.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:notifier>
                    <!-- Send notifications to Slack and use Telegram if
                         Slack errored -->
                    <framework:chatter-transport name="slack">
                        %env(SLACK_DSN)% || %env(TELEGRAM_DSN)%
                    </framework:chatter-transport>

                    <!-- Send notifications to the next scheduled transport 
                         calculated by round robin -->
                    <framework:chatter-transport name="slack">
                        %env(SLACK_DSN)% && %env(TELEGRAM_DSN)%
                    </framework:chatter-transport>
                </framework:notifier>
            </framework:config>
        </container>

    .. code-block:: php

        # config/packages/notifier.php
        $container->loadFromExtension('framework', [
            'notifier' => [
                'chatter_transports' => [
                    // Send notifications to Slack and use Telegram if
                    // Slack errored
                    'main' => '%env(SLACK_DSN)% || %env(TELEGRAM_DSN)%',

                    // Send notifications to the next scheduled transport calculated by round robin
                    'roundrobin' => '%env(SLACK_DSN)% && %env(TELEGRAM_DSN)%',
                ],
            ],
        ]);

Creating & Sending Notifications
--------------------------------

To send a notification, autowire the
:class:`Symfony\\Component\\Notifier\\NotifierInterface` (service ID
``notifier``). This class has a ``send()`` method that allows you to send a
:class:`Symfony\\Component\\Notifier\\Notification\\Notification` to a
:class:`Symfony\\Component\\Notifier\\Recipient\\Recipient`::

    // src/Controller/InvoiceController.php
    namespace App\Controller;

    use Symfony\Component\Notifier\Notification\Notification;
    use Symfony\Component\Notifier\NotifierInterface;
    use Symfony\Component\Notifier\Recipient\AdminRecipient;

    class InvoiceController extends AbstractController
    {
        /**
         * @Route("/invoice/create")
         */
        public function create(NotifierInterface $notifier)
        {
            // ...

            // Create a Notification that has to be sent
            // using the "email" channel
            $notification = (new Notification('New Invoice', ['email']))
                ->content('You got a new invoice for 15 EUR.');

            // The receiver of the Notification
            $recipient = new AdminRecipient(
                $user->getEmail(),
                $user->getPhonenumber()
            );

            // Send the notification to the recipient
            $notifier->send($notification, $recipient);

            // ...
        }
    }

The ``Notification`` is created by using two arguments: the subject and
channels. The channels specify which channel (or transport) should be used
to send the notification. For instance, ``['email', 'sms']`` will send
both an email and sms notification to the user. It is required to specify
the transport when using chatters (e.g. ``['email', 'chat/telegram']``).

The default notification also has a ``content()`` and ``emoji()`` method to
set the notification content and icon.

Symfony provides three types of recipients:

:class:`Symfony\\Component\\Notifier\\Recipient\\NoRecipient`
    This is the default and is useful when there is no need to have
    information about the receiver. For example, the browser channel uses
    the current requests's :ref:`session flashbag <flash-messages>`;

:class:`Symfony\\Component\\Notifier\\Recipient\\Recipient`
    This contains only the email address of the user and can be used for
    messages on the email and browser channel;

:class:`Symfony\\Component\\Notifier\\Recipient\\AdminRecipient`
    This can contain both email address and phonenumber of the user. This
    recipient can be used for all channels (depending on whether they are
    actually set).

Configuring Channel Policies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of specifying the target channels on creation, Symfony also allows
you to use notification importance levels. Update the configuration to
specify what channels should be used for specific levels (using
``channel_policy``):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                # ...
                channel_policy:
                    # Use SMS, Slack and email for urgent notifications
                    urgent: ['sms', 'chat/slack', 'email']

                    # Use Slack for highly important notifications
                    high: ['chat/slack']

                    # Use browser for medium and low notifications
                    medium: ['browser']
                    low: ['browser']

    .. code-block:: xml

        <!-- config/packages/notifier.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:notifier>
                    <!-- ... -->

                    <framework:channel-policy>
                        <!-- Use SMS, Slack and Email for urgent notifications -->
                        <framework:urgent>sms</framework:urgent>
                        <framework:urgent>chat/slack</framework:urgent>
                        <framework:urgent>email</framework:urgent>

                        <!-- Use Slack for highly important notifications -->
                        <framework:high>chat/slack</framework:high>

                        <!-- Use browser for medium and low notifications -->
                        <framework:medium>browser</framework:medium>
                        <framework:low>browser</framework:low>
                    </framework:channel-policy>
                </framework:notifier>
            </framework:config>
        </container>

    .. code-block:: php

        # config/packages/notifier.php
        $container->loadFromExtension('framework', [
            'notifier' => [
                // ...
                'channel_policy' => [
                    // Use SMS, Slack and email for urgent notifications
                    'urgent' => ['sms', 'chat/slack', 'email'],

                    // Use Slack for highly important notifications
                    'high' => ['chat/slack'],

                    // Use browser for medium and low notifications
                    'medium' => ['browser'],
                    'low' => ['browser'],
                ],
            ],
        ]);

Now, whenever the notification's importance is set to "high", it will be
sent using the Slack transport::

    // ...
    class InvoiceController extends AbstractController
    {
        /**
         * @Route("/invoice/create")
         */
        public function invoice(NotifierInterface $notifier)
        {
            // ...

            $notification = (new Notification('New Invoice'))
                ->content('You got a new invoice for 15 EUR.')
                ->importance(Notification::IMPORTANCE_HIGH);

            $notifier->send($notification, new Recipient('wouter@wouterj.nl'));

            // ...
        }
    }

Customize Notifications
-----------------------

You can extend the ``Notification`` or ``Recipient`` base classes to
customize their behavior. For instance, you can overwrite the
``getChannels()`` method to only return ``sms`` if the invoice price is
very high and the recipient has a phone number::

    namespace App\Notifier;

    use Symfony\Component\Notifier\Notification\Notification;
    use Symfony\Component\Notifier\Recipient\Recipient;

    class InvoiceNotification extends Notification
    {
        private $price;

        public function __construct(int $price)
        {
            $this->price = $price;
        }

        public function getChannels(Recipient $recipient)
        {
            if (
                $this->price > 10000
                && $recipient instanceof AdminRecipient
                && null !== $recipient->getPhone()
            ) {
                return ['sms'];
            }

            return ['email'];
        }
    }

Customize Notification Messages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Each channel has its own notification interface that you can implement to
customize the notification message. For instance, if you want to modify the
message based on the chat service, implement
:class:`Symfony\\Component\\Notifier\\Notification\\ChatNotificationInterface`
and its ``asChatMessage()`` method::

    // src/Notifier/InvoiceNotification.php
    namespace App\Notifier;

    use Symfony\Component\Notifier\Message\ChatMessage;
    use Symfony\Component\Notifier\Notification\ChatNotificationInterface;
    use Symfony\Component\Notifier\Notification\Notification;
    use Symfony\Component\Notifier\Recipient\Recipient;

    class InvoiceNotification extends Notification implements ChatNotificationInterface
    {
        private $price;

        public function __construct(int $price)
        {
            $this->price = $price;
        }

        public function asChatMessage(Recipient $recipient, string $transport = null): ?ChatMessage
        {
            // Add a custom emoji if the message is sent to Slack
            if ('slack' === $transport) {
                return (new ChatMessage('You\'re invoiced '.$this->price.' EUR.'))
                    ->emoji('money');
            }

            // If you return null, the Notifier will create the ChatMessage
            // based on this notification as it would without this method.
            return null;
        }
    }

The
:class:`Symfony\\Component\\Notifier\\Notification\\SmsNotificationInterface`
and
:class:`Symfony\\Component\\Notifier\\Notification\\EmailNotificationInterface`
also exists to modify messages send to those channels.

Disabling Delivery
------------------

While developing (or testing), you may want to disable delivery of notifications
entirely. You can do this by forcing Notifier to use the ``NullTransport`` for
all configured texter and chatter transports only in the ``dev`` (and/or
``test``) environment:

.. code-block:: yaml

    # config/packages/dev/notifier.yaml
    framework:
        notifier:
            texter_transports:
                twilio: 'null://null'
            chatter_transports:
                slack: 'null://null'

.. TODO
    - Using the message bus for asynchronous notification
    - Describe notifier monolog handler
    - Describe notification_on_failed_messages integration

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    notifier/*
