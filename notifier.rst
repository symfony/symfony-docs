Creating and Sending Notifications
==================================

Installation
------------

Current web applications use many different channels to send messages to
the users (e.g. SMS, Slack messages, emails, push notifications, etc.). The
Notifier component in Symfony is an abstraction on top of all these
channels. It provides a dynamic way to manage how the messages are sent.
Get the Notifier installed using:

.. code-block:: terminal

    $ composer require symfony/notifier

.. _channels-chatters-texters-email-and-browser:

Channels: Chatters, Texters, Email, Browser and Push
----------------------------------------------------

The notifier component can send notifications to different channels. Each
channel can integrate with different providers (e.g. Slack or Twilio SMS)
by using transports.

The notifier component supports the following channels:

* :ref:`SMS channel <notifier-sms-channel>` sends notifications to phones via
  SMS messages;
* :ref:`Chat channel <notifier-chat-channel>` sends notifications to chat
  services like Slack and Telegram;
* :ref:`Email channel <notifier-email-channel>` integrates the :doc:`Symfony Mailer </mailer>`;
* Browser channel uses :ref:`flash messages <flash-messages>`.
* :ref:`Push channel <notifier-push-channel>` sends notifications to phones and browsers via push notifications.

.. tip::

    Use :doc:`secrets </configuration/secrets>` to securely store your
    API tokens.

.. _notifier-sms-channel:

SMS Channel
~~~~~~~~~~~

.. caution::

    If any of the DSN values contains any character considered special in a
    URI (such as ``+``, ``@``, ``$``, ``#``, ``/``, ``:``, ``*``, ``!``), you must
    encode them. See `RFC 3986`_ for the full list of reserved characters or use the
    :phpfunction:`urlencode` function to encode them.

The SMS channel uses :class:`Symfony\\Component\\Notifier\\Texter` classes
to send SMS messages to mobile phones. This feature requires subscribing to
a third-party service that sends SMS messages. Symfony provides integration
with a couple popular SMS services:

==================  =====================================  ===========================================================================
Service             Package                                DSN
==================  =====================================  ===========================================================================
`46elks`_           ``symfony/forty-six-elks-notifier``    ``forty-six-elks://API_USERNAME:API_PASSWORD@default?from=FROM``
`AllMySms`_         ``symfony/all-my-sms-notifier``        ``allmysms://LOGIN:APIKEY@default?from=FROM``
`AmazonSns`_        ``symfony/amazon-sns-notifier``        ``sns://ACCESS_KEY:SECRET_KEY@default?region=REGION``
`Bandwidth`_        ``symfony/bandwidth-notifier``         ``bandwidth://USERNAME:PASSWORD@default?from=FROM&account_id=ACCOUNT_ID&application_id=APPLICATION_ID&priority=PRIORITY``
`Clickatell`_       ``symfony/clickatell-notifier``        ``clickatell://ACCESS_TOKEN@default?from=FROM``
`ContactEveryone`_  ``symfony/contact-everyone-notifier``  ``contact-everyone://TOKEN@default?&diffusionname=DIFFUSION_NAME&category=CATEGORY``
`Esendex`_          ``symfony/esendex-notifier``           ``esendex://USER_NAME:PASSWORD@default?accountreference=ACCOUNT_REFERENCE&from=FROM``
`FakeSms`_          ``symfony/fake-sms-notifier``          ``fakesms+email://MAILER_SERVICE_ID?to=TO&from=FROM`` or ``fakesms+logger://default``
`FreeMobile`_       ``symfony/free-mobile-notifier``       ``freemobile://LOGIN:API_KEY@default?phone=PHONE``
`GatewayApi`_       ``symfony/gateway-api-notifier``       ``gatewayapi://TOKEN@default?from=FROM``
`Infobip`_          ``symfony/infobip-notifier``           ``infobip://AUTH_TOKEN@HOST?from=FROM``
`Iqsms`_            ``symfony/iqsms-notifier``             ``iqsms://LOGIN:PASSWORD@default?from=FROM``
`iSendPro`_         ``symfony/isendpro-notifier``          ``isendpro://ACCOUNT_KEY_ID@default?from=FROM&no_stop=NO_STOP&sandbox=SANDBOX``
`KazInfoTeh`_       ``symfony/kaz-info-teh-notifier``      ``kaz-info-teh://USERNAME:PASSWORD@default?sender=FROM``
`LightSms`_         ``symfony/light-sms-notifier``         ``lightsms://LOGIN:TOKEN@default?from=PHONE``
`Mailjet`_          ``symfony/mailjet-notifier``           ``mailjet://TOKEN@default?from=FROM``
`MessageBird`_      ``symfony/message-bird-notifier``      ``messagebird://TOKEN@default?from=FROM``
`MessageMedia`_     ``symfony/message-media-notifier``     ``messagemedia://API_KEY:API_SECRET@default?from=FROM``
`Mobyt`_            ``symfony/mobyt-notifier``             ``mobyt://USER_KEY:ACCESS_TOKEN@default?from=FROM``
`Nexmo`_            ``symfony/nexmo-notifier``             Abandoned in favor of Vonage (symfony/vonage-notifier).
`Octopush`_         ``symfony/octopush-notifier``          ``octopush://USERLOGIN:APIKEY@default?from=FROM&type=TYPE``
`OrangeSms`_        ``symfony/orange-sms-notifier``        ``orange-sms://CLIENT_ID:CLIENT_SECRET@default?from=FROM&sender_name=SENDER_NAME``
`OvhCloud`_         ``symfony/ovh-cloud-notifier``         ``ovhcloud://APPLICATION_KEY:APPLICATION_SECRET@default?consumer_key=CONSUMER_KEY&service_name=SERVICE_NAME``
`Plivo`_            ``symfony/plivo-notifier``             ``plivo://AUTH_ID:AUTH_TOKEN@default?from=FROM``
`RingCentral`_      ``symfony/ring-central-notifier``      ``ringcentral://API_TOKEN@default?from=FROM``
`Sendberry`_        ``symfony/sendberry-notifier``         ``sendberry://USERNAME:PASSWORD@default?auth_key=AUTH_KEY&from=FROM``
`Sendinblue`_       ``symfony/sendinblue-notifier``        ``sendinblue://API_KEY@default?sender=PHONE``
`Sms77`_            ``symfony/sms77-notifier``             ``sms77://API_KEY@default?from=FROM``
`SimpleTextin`_     ``symfony/simple-textin-notifier``     ``simpletextin://API_KEY@default?from=FROM``
`Sinch`_            ``symfony/sinch-notifier``             ``sinch://ACCOUNT_ID:AUTH_TOKEN@default?from=FROM``
`Smsapi`_           ``symfony/smsapi-notifier``            ``smsapi://TOKEN@default?from=FROM``
`SmsBiuras`_        ``symfony/sms-biuras-notifier``        ``smsbiuras://UID:API_KEY@default?from=FROM&test_mode=0``
`Smsc`_             ``symfony/smsc-notifier``              ``smsc://LOGIN:PASSWORD@default?from=FROM``
`SMSFactor`_        ``symfony/sms-factor-notifier``        ``sms-factor://TOKEN@default?sender=SENDER&push_type=PUSH_TYPE``
`SpotHit`_          ``symfony/spot-hit-notifier``          ``spothit://TOKEN@default?from=FROM``
`Telnyx`_           ``symfony/telnyx-notifier``            ``telnyx://API_KEY@default?from=FROM&messaging_profile_id=MESSAGING_PROFILE_ID``
`TurboSms`_         ``symfony/turbo-sms-notifier``         ``turbosms://AUTH_TOKEN@default?from=FROM``
`Twilio`_           ``symfony/twilio-notifier``            ``twilio://SID:TOKEN@default?from=FROM``
`Vonage`_           ``symfony/vonage-notifier``            ``vonage://KEY:SECRET@default?from=FROM``
`Yunpian`_          ``symfony/yunpian-notifier``           ``yunpian://APIKEY@default``
==================  =====================================  ===========================================================================

.. versionadded:: 6.1

    The 46elks, OrangeSms, KazInfoTeh and Sendberry integrations were introduced in Symfony 6.1.
    The ``no_stop_clause`` option in ``OvhCloud`` DSN was introduced in Symfony 6.1.
    The ``test`` option in ``Smsapi`` DSN was introduced in Symfony 6.1.

.. versionadded:: 6.2

    The ContactEveryone and SMSFactor integrations were introduced in Symfony 6.2.

.. versionadded:: 6.3

    The Bandwith, iSendPro, Plivo, RingCentral, SimpleTextin and Termii integrations
    were introduced in Symfony 6.3.
    The ``from`` option in ``Smsapi`` DSN is optional since Symfony 6.3.

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

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->texterTransport('twilio', env('TWILIO_DSN'))
            ;
        };

.. _sending-sms:

The :class:`Symfony\\Component\\Notifier\\TexterInterface` class allows you to
send SMS messages::

    // src/Controller/SecurityController.php
    namespace App\Controller;

    use Symfony\Component\Notifier\Message\SmsMessage;
    use Symfony\Component\Notifier\TexterInterface;
    use Symfony\Component\Routing\Annotation\Route;

    class SecurityController
    {
        #[Route('/login/success')]
        public function loginSuccess(TexterInterface $texter)
        {
            $sms = new SmsMessage(
                // the phone number to send the SMS message to
                '+1411111111',
                // the message
                'A new login was detected!',
                // optionally, you can override default "from" defined in transports
                '+1422222222',
            );

            $sentMessage = $texter->send($sms);

            // ...
        }
    }

.. versionadded:: 6.2

    The 3rd argument of ``SmsMessage`` (``$from``) was introduced in Symfony 6.2.

The ``send()`` method returns a variable of type
:class:`Symfony\\Component\\Notifier\\Message\\SentMessage` which provides
information such as the message ID and the original message contents.

.. _notifier-chat-channel:

Chat Channel
~~~~~~~~~~~~

.. caution::

    If any of the DSN values contains any character considered special in a
    URI (such as ``+``, ``@``, ``$``, ``#``, ``/``, ``:``, ``*``, ``!``), you must
    encode them. See `RFC 3986`_ for the full list of reserved characters or use the
    :phpfunction:`urlencode` function to encode them.

The chat channel is used to send chat messages to users by using
:class:`Symfony\\Component\\Notifier\\Chatter` classes. Symfony provides
integration with these chat services:

=======================================  ====================================  =============================================================================
Service                                  Package                               DSN
=======================================  ====================================  =============================================================================
`AmazonSns`_                             ``symfony/amazon-sns-notifier``       ``sns://ACCESS_KEY:SECRET_KEY@default?region=REGION``
`Chatwork`_                              ``symfony/chatwork-notifier``         ``chatwork://API_TOKEN@default?room_id=ID``
`Discord`_                               ``symfony/discord-notifier``          ``discord://TOKEN@default?webhook_id=ID``
`FakeChat`_                              ``symfony/fake-chat-notifier``        ``fakechat+email://default?to=TO&from=FROM`` or ``fakechat+logger://default``
`Firebase`_                              ``symfony/firebase-notifier``         ``firebase://USERNAME:PASSWORD@default``
`Gitter`_                                ``symfony/gitter-notifier``           ``gitter://TOKEN@default?room_id=ROOM_ID``
`GoogleChat`_                            ``symfony/google-chat-notifier``      ``googlechat://ACCESS_KEY:ACCESS_TOKEN@default/SPACE?thread_key=THREAD_KEY``
`LINE Notify`_                           ``symfony/line-notify-notifier``      ``linenotify://TOKEN@default``
`LinkedIn`_                              ``symfony/linked-in-notifier``        ``linkedin://TOKEN:USER_ID@default``
`Mastodon`_                              ``symfony/mastodon-notifier``         ``mastodon://ACCESS_TOKEN@HOST``
`Mattermost`_                            ``symfony/mattermost-notifier``       ``mattermost://ACCESS_TOKEN@HOST/PATH?channel=CHANNEL``
`Mercure`_                               ``symfony/mercure-notifier``          ``mercure://HUB_ID?topic=TOPIC``
`MicrosoftTeams`_                        ``symfony/microsoft-teams-notifier``  ``microsoftteams://default/PATH``
`RocketChat`_                            ``symfony/rocket-chat-notifier``      ``rocketchat://TOKEN@ENDPOINT?channel=CHANNEL``
`Slack`_                                 ``symfony/slack-notifier``            ``slack://TOKEN@default?channel=CHANNEL``
`Telegram`_                              ``symfony/telegram-notifier``         ``telegram://TOKEN@default?channel=CHAT_ID``
`Twitter`_                               ``symfony/twitter-notifier``          ``twitter://API_KEY:API_SECRET:ACCESS_TOKEN:ACCESS_SECRET@default``
`Zendesk`_                               ``symfony/zendesk-notifier``          ``zendesk://EMAIL:TOKEN@SUBDOMAIN``
`Zulip`_                                 ``symfony/zulip-notifier``            ``zulip://EMAIL:TOKEN@HOST?channel=CHANNEL``
======================================   ====================================  =============================================================================

.. versionadded:: 6.2

    The Zendesk and Chatwork integration were introduced in Symfony 6.2.

.. versionadded:: 6.3

    The LINE Notify, Mastodon and Twitter integrations were introduced in Symfony 6.3.

Chatters are configured using the ``chatter_transports`` setting:

.. code-block:: bash

    # .env
    SLACK_DSN=slack://TOKEN@default?channel=CHANNEL

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

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->chatterTransport('slack', env('SLACK_DSN'))
            ;
        };

.. _sending-chat-messages:

The :class:`Symfony\\Component\\Notifier\\ChatterInterface` class allows
you to send messages to chat services::

    // src/Controller/CheckoutController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Notifier\ChatterInterface;
    use Symfony\Component\Notifier\Message\ChatMessage;
    use Symfony\Component\Routing\Annotation\Route;

    class CheckoutController extends AbstractController
    {
        /**
         * @Route("/checkout/thankyou")
         */
        public function thankyou(ChatterInterface $chatter)
        {
            $message = (new ChatMessage('You got a new invoice for 15 EUR.'))
                // if not set explicitly, the message is sent to the
                // default transport (the first one configured)
                ->transport('slack');

            $sentMessage = $chatter->send($message);

            // ...
        }
    }

The ``send()`` method returns a variable of type
:class:`Symfony\\Component\\Notifier\\Message\\SentMessage` which provides
information such as the message ID and the original message contents.

.. _notifier-email-channel:

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

        // config/packages/mailer.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->mailer()
                ->dsn(env('MAILER_DSN'))
                ->envelope()
                    ->sender('notifications@example.com')
            ;
        };

.. _notifier-push-channel:

Push Channel
~~~~~~~~~~~~

.. caution::

    If any of the DSN values contains any character considered special in a
    URI (such as ``+``, ``@``, ``$``, ``#``, ``/``, ``:``, ``*``, ``!``), you must
    encode them. See `RFC 3986`_ for the full list of reserved characters or use the
    :phpfunction:`urlencode` function to encode them.

The push channel is used to send notifications to users by using
:class:`Symfony\\Component\\Notifier\\Texter` classes. Symfony provides
integration with these push services:

===============  ====================================  ==============================================================================
Service          Package                               DSN
===============  ====================================  ==============================================================================
`Engagespot`_    ``symfony/engagespot-notifier``       ``engagespot://API_KEY@default?campaign_name=CAMPAIGN_NAME``
`Expo`_          ``symfony/expo-notifier``             ``expo://Token@default``
`Novu`_          ``symfony/novu-notifier``             ``novu://API_KEY@default``
`OneSignal`_     ``symfony/one-signal-notifier``       ``onesignal://APP_ID:API_KEY@default?defaultRecipientId=DEFAULT_RECIPIENT_ID``
`PagerDuty`_     ``symfony/pager-duty-notifier``       ``pagerduty://TOKEN@SUBDOMAIN``
`Pushover`_      ``symfony/pushover-notifier``         ``pushover://USER_KEY:APP_TOKEN@default``
===============  ====================================  ==============================================================================

.. versionadded:: 6.1

    The Engagespot integration was introduced in Symfony 6.1.

.. versionadded:: 6.3

    The PagerDuty and Pushover integrations were introduced in Symfony 6.3.

.. versionadded:: 6.4

    The Novu integration was introduced in Symfony 6.4.

To enable a texter, add the correct DSN in your ``.env`` file and
configure the ``texter_transports``:

.. code-block:: bash

    # .env
    EXPO_DSN=expo://TOKEN@default

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                texter_transports:
                    expo: '%env(EXPO_DSN)%'

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
                    <framework:texter-transport name="expo">
                        %env(EXPO_DSN)%
                    </framework:texter-transport>
                </framework:notifier>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->texterTransport('expo', env('EXPO_DSN'))
            ;
        };

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
                    <framework:chatter-transport name="slack"><![CDATA[
                        %env(SLACK_DSN)% && %env(TELEGRAM_DSN)%
                    ]]></framework:chatter-transport>
                </framework:notifier>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                // Send notifications to Slack and use Telegram if
                // Slack errored
                ->chatterTransport('main', env('SLACK_DSN').' || '.env('TELEGRAM_DSN'))

                // Send notifications to the next scheduled transport calculated by round robin
                ->chatterTransport('roundrobin', env('SLACK_DSN').' && '.env('TELEGRAM_DSN'))
            ;
        };

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
    use Symfony\Component\Notifier\Recipient\Recipient;

    class InvoiceController extends AbstractController
    {
        #[Route('/invoice/create')]
        public function create(NotifierInterface $notifier)
        {
            // ...

            // Create a Notification that has to be sent
            // using the "email" channel
            $notification = (new Notification('New Invoice', ['email']))
                ->content('You got a new invoice for 15 EUR.');

            // The receiver of the Notification
            $recipient = new Recipient(
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
both an email and sms notification to the user.

The default notification also has a ``content()`` and ``emoji()`` method to
set the notification content and icon.

Symfony provides the following recipients:

:class:`Symfony\\Component\\Notifier\\Recipient\\NoRecipient`
    This is the default and is useful when there is no need to have
    information about the receiver. For example, the browser channel uses
    the current requests' :ref:`session flashbag <flash-messages>`;

:class:`Symfony\\Component\\Notifier\\Recipient\\Recipient`
    This can contain both the email address and the phone number of the user. This
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

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // ...
            $framework->notifier()
                // Use SMS, Slack and email for urgent notifications
                ->channelPolicy('urgent', ['sms', 'chat/slack', 'email'])
                // Use Slack for highly important notifications
                ->channelPolicy('high', ['chat/slack'])
                // Use browser for medium and low notifications
                ->channelPolicy('medium', ['browser'])
                ->channelPolicy('low', ['browser'])
            ;
        };

Now, whenever the notification's importance is set to "high", it will be
sent using the Slack transport::

    // ...
    class InvoiceController extends AbstractController
    {
        #[Route('/invoice/create')]
        public function invoice(NotifierInterface $notifier)
        {
            // ...

            $notification = (new Notification('New Invoice'))
                ->content('You got a new invoice for 15 EUR.')
                ->importance(Notification::IMPORTANCE_HIGH);

            $notifier->send($notification, new Recipient('wouter@example.com'));

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
    use Symfony\Component\Notifier\Recipient\RecipientInterface;
    use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;

    class InvoiceNotification extends Notification
    {
        public function __construct(
            private int $price,
        ) {
        }

        public function getChannels(RecipientInterface $recipient)
        {
            if (
                $this->price > 10000
                && $recipient instanceof SmsRecipientInterface
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
    use Symfony\Component\Notifier\Recipient\RecipientInterface;

    class InvoiceNotification extends Notification implements ChatNotificationInterface
    {
        public function __construct(
            private int $price,
        ) {
        }

        public function asChatMessage(RecipientInterface $recipient, string $transport = null): ?ChatMessage
        {
            // Add a custom subject and emoji if the message is sent to Slack
            if ('slack' === $transport) {
                $this->subject('You\'re invoiced '.strval($this->price).' EUR.');
                $this->emoji("money");
                return ChatMessage::fromNotification($this);
            }

            // If you return null, the Notifier will create the ChatMessage
            // based on this notification as it would without this method.
            return null;
        }
    }

The
:class:`Symfony\\Component\\Notifier\\Notification\\SmsNotificationInterface`,
:class:`Symfony\\Component\\Notifier\\Notification\\EmailNotificationInterface`
and
:class:`Symfony\\Component\\Notifier\\Notification\\PushNotificationInterface`
also exists to modify messages sent to those channels.

Customize Browser Notifications (Flash Messages)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 6.1

    Support for customizing importance levels was introduced in Symfony 6.1.

The default behavior for browser channel notifications is to add a
:ref:`flash message <flash-messages>` with ``notification`` as its key.

However, you might prefer to map the importance level of the notification to the
type of flash message, so you can tweak their style.

you can do that by overriding the default ``notifier.flash_message_importance_mapper``
service with your own implementation of
:class:`Symfony\\Component\\Notifier\\FlashMessage\\FlashMessageImportanceMapperInterface`
where you can provide your own "importance" to "alert level" mapping.

Symfony currently provides an implementation for the Bootstrap CSS framework's
typical alert levels, which you can implement immediately using:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            notifier.flash_message_importance_mapper:
                class: Symfony\Component\Notifier\FlashMessage\BootstrapFlashMessageImportanceMapper

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="notifier.flash_message_importance_mapper" class="Symfony\Component\Notifier\FlashMessage\BootstrapFlashMessageImportanceMapper"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Notifier\FlashMessage\BootstrapFlashMessageImportanceMapper;

        return function(ContainerConfigurator $containerConfigurator) {
            $containerConfigurator->services()
                ->set('notifier.flash_message_importance_mapper', BootstrapFlashMessageImportanceMapper::class)
            ;
        };

Testing Notifier
----------------

Symfony provides a :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\NotificationAssertionsTrait`
which provide useful methods for testing your Notifier implementation.
You can benefit from this class by using it directly or extending the
:class:`Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase`.

See :ref:`testing documentation <notifier-assertions>` for the list of available assertions.

.. versionadded:: 6.2

    The :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\NotificationAssertionsTrait`
    was introduced in Symfony 6.2.

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

.. _notifier-events:

Using Events
------------

The :class:`Symfony\\Component\\Notifier\\Transport`` class of the Notifier component
allows you to optionally hook into the lifecycle via events.

The ``MessageEvent::class`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: Doing something before the message is sent (like logging
which message is going to be sent, or displaying something about the event
to be executed.

Just before sending the message, the event class ``MessageEvent`` is
dispatched. Listeners receive a
:class:`Symfony\\Component\\Notifier\\Event\\MessageEvent` event::

    use Symfony\Component\Notifier\Event\MessageEvent;

    $dispatcher->addListener(MessageEvent::class, function (MessageEvent $event): void {
        // gets the message instance
        $message = $event->getMessage();

        // log something
        $this->logger(sprintf('Message with subject: %s will be send to %s', $message->getSubject(), $message->getRecipientId()));
    });

The ``FailedMessageEvent`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: Doing something before the exception is thrown
(Retry to send the message or log additional information).

Whenever an exception is thrown while sending the message, the event class
``FailedMessageEvent`` is dispatched. A listener can do anything useful before
the exception is thrown.

Listeners receive a
:class:`Symfony\\Component\\Notifier\\Event\\FailedMessageEvent` event::

    use Symfony\Component\Notifier\Event\FailedMessageEvent;

    $dispatcher->addListener(FailedMessageEvent::class, function (FailedMessageEvent $event): void {
        // gets the message instance
        $message = $event->getMessage();

        // gets the error instance
        $error = $event->getError();

        // log something
        $this->logger(sprintf('The message with subject: %s has not been sent successfully. The error is: %s', $message->getSubject(), $error->getMessage()));
    });

The ``SentMessageEvent`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: To perform some action when the message is successfully
sent (like retrieve the id returned when the message is sent).

After the message has been successfully sent, the event class ``SentMessageEvent``
is dispatched. Listeners receive a
:class:`Symfony\\Component\\Notifier\\Event\\SentMessageEvent` event::

    use Symfony\Component\Notifier\Event\SentMessageEvent;

    $dispatcher->addListener(SentMessageEvent::class, function (SentMessageEvent $event): void {
        // gets the message instance
        $message = $event->getOriginalMessage();

        // log something
        $this->logger(sprintf('The message has been successfully sent and has id: %s', $message->getMessageId()));
    });

.. TODO
..    - Using the message bus for asynchronous notification
..    - Describe notifier monolog handler
..    - Describe notification_on_failed_messages integration

.. _`46elks`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/FortySixElks/README.md
.. _`AllMySms`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/AllMySms/README.md
.. _`AmazonSns`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/AmazonSns/README.md
.. _`Bandwidth`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Bandwidth/README.md
.. _`Chatwork`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Chatwork/README.md
.. _`Clickatell`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Clickatell/README.md
.. _`ContactEveryone`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/ContactEveryone/README.md
.. _`Discord`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Discord/README.md
.. _`Engagespot`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Engagespot/README.md
.. _`Esendex`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Esendex/README.md
.. _`Expo`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Expo/README.md
.. _`FakeChat`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/FakeChat/README.md
.. _`FakeSms`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/FakeSms/README.md
.. _`Firebase`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Firebase/README.md
.. _`FreeMobile`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/FreeMobile/README.md
.. _`GatewayApi`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/GatewayApi/README.md
.. _`Gitter`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Gitter/README.md
.. _`GoogleChat`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/GoogleChat/README.md
.. _`Infobip`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Infobip/README.md
.. _`Iqsms`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Iqsms/README.md
.. _`iSendPro`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Isendpro/README.md
.. _`KazInfoTeh`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/KazInfoTeh/README.md
.. _`LINE Notify`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/LineNotify/README.md
.. _`LightSms`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/LightSms/README.md
.. _`LinkedIn`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/LinkedIn/README.md
.. _`Mailjet`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Mailjet/README.md
.. _`Mastodon`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Mastodon/README.md
.. _`Mattermost`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Mattermost/README.md
.. _`Mercure`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Mercure/README.md
.. _`MessageBird`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/MessageBird/README.md
.. _`MessageMedia`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/MessageMedia/README.md
.. _`MicrosoftTeams`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/MicrosoftTeams/README.md
.. _`Mobyt`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Mobyt/README.md
.. _`Nexmo`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Nexmo/README.md
.. _`Novu`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Novu/README.md
.. _`Octopush`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Octopush/README.md
.. _`OneSignal`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/OneSignal/README.md
.. _`OrangeSms`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/OrangeSms/README.md
.. _`OvhCloud`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/OvhCloud/README.md
.. _`PagerDuty`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/PagerDuty/README.md
.. _`Plivo`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Plivo/README.md
.. _`Pushover`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Pushover/README.md
.. _`RFC 3986`: https://www.ietf.org/rfc/rfc3986.txt
.. _`RingCentral`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/RingCentral/README.md
.. _`RocketChat`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/RocketChat/README.md
.. _`SMSFactor`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/SmsFactor/README.md
.. _`Sendberry`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Sendberry/README.md
.. _`Sendinblue`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Sendinblue/README.md
.. _`SimpleTextin`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/SimpleTextin/README.md
.. _`Sinch`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Sinch/README.md
.. _`Slack`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Slack/README.md
.. _`Sms77`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Sms77/README.md
.. _`SmsBiuras`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/SmsBiuras/README.md
.. _`Smsapi`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Smsapi/README.md
.. _`Smsc`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Smsc/README.md
.. _`SpotHit`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/SpotHit/README.md
.. _`Telegram`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Telegram/README.md
.. _`Telnyx`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Telnyx/README.md
.. _`TurboSms`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/TurboSms/README.md
.. _`Twilio`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Twilio/README.md
.. _`Twitter`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Twitter/README.md
.. _`Vonage`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Vonage/README.md
.. _`Yunpian`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Yunpian/README.md
.. _`Zendesk`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Zendesk/README.md
.. _`Zulip`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Zulip/README.md
