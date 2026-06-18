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
.. _channels-chatters-texters-email-browser-and-push:

Channels
--------

Channels refer to the different mediums through which notifications can be delivered.
These channels include email, SMS, chat services, push notifications, etc. Each
channel can integrate with different providers (e.g. Slack or Twilio SMS) by
using transports.

The notifier component supports the following channels:

* :ref:`SMS channel <notifier-sms-channel>` sends notifications to phones via
  SMS messages;
* :ref:`Chat channel <notifier-chat-channel>` sends notifications to chat
  services like Slack and Telegram;
* :ref:`Email channel <notifier-email-channel>` integrates the :doc:`Symfony Mailer </mailer>`;
* Browser channel uses :ref:`flash messages <flash-messages>`.
* :ref:`Push channel <notifier-push-channel>` sends notifications to phones and
  browsers via push notifications.
* :ref:`Desktop channel <notifier-desktop-channel>` displays desktop notifications
  on the same host machine.

.. _notifier-sms-channel:

SMS Channel
~~~~~~~~~~~

The SMS channel uses :class:`Symfony\\Component\\Notifier\\Texter` classes
to send SMS messages to mobile phones. This feature requires subscribing to
a third-party service that sends SMS messages. Symfony provides integration
with a couple popular SMS services:

.. warning::

    If any of the DSN values contains any character considered special in a
    URI (such as ``: / ? # [ ] @ ! $ & ' ( ) * + , ; =``), you must
    encode them. See `RFC 3986`_ for the full list of reserved characters or use the
    :phpfunction:`urlencode` function to encode them.

==================  ====================================================================================================================================
Service
==================  ====================================================================================================================================
`46elks`_           **Install**: ``composer require symfony/forty-six-elks-notifier`` \
                    **DSN**: ``forty-six-elks://API_USERNAME:API_PASSWORD@default?from=FROM`` \
                    **Webhook support**: No
`AllMySms`_         **Install**: ``composer require symfony/all-my-sms-notifier`` \
                    **DSN**: ``allmysms://LOGIN:APIKEY@default?from=FROM`` \
                    **Webhook support**: No
                    **Extra properties in SentMessage**: ``nbSms``, ``balance``, ``cost``
`AmazonSns`_        **Install**: ``composer require symfony/amazon-sns-notifier`` \
                    **DSN**: ``sns://ACCESS_KEY:SECRET_KEY@default?region=REGION`` \
                    **Webhook support**: No
`Bandwidth`_        **Install**: ``composer require symfony/bandwidth-notifier`` \
                    **DSN**: ``bandwidth://USERNAME:PASSWORD@default?from=FROM&account_id=ACCOUNT_ID&application_id=APPLICATION_ID&priority=PRIORITY`` \
                    **Webhook support**: No
`Brevo`_            **Install**: ``composer require symfony/brevo-notifier`` \
                    **DSN**: ``brevo://API_KEY@default?sender=SENDER`` \
                    **Webhook support**: No
`Clickatell`_       **Install**: ``composer require symfony/clickatell-notifier`` \
                    **DSN**: ``clickatell://ACCESS_TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`ContactEveryone`_  **Install**: ``composer require symfony/contact-everyone-notifier`` \
                    **DSN**: ``contact-everyone://TOKEN@default?&diffusionname=DIFFUSION_NAME&category=CATEGORY`` \
                    **Webhook support**: No
`Esendex`_          **Install**: ``composer require symfony/esendex-notifier`` \
                    **DSN**: ``esendex://USER_NAME:PASSWORD@default?accountreference=ACCOUNT_REFERENCE&from=FROM`` \
                    **Webhook support**: No
`FakeSms`_          **Install**: ``composer require symfony/fake-sms-notifier`` \
                    **DSN**: ``fakesms+email://MAILER_SERVICE_ID?to=TO&from=FROM`` or ``fakesms+logger://default`` \
                    **Webhook support**: No
`FreeMobile`_       **Install**: ``composer require symfony/free-mobile-notifier`` \
                    **DSN**: ``freemobile://LOGIN:API_KEY@default?phone=PHONE`` \
                    **Webhook support**: No
`GatewayApi`_       **Install**: ``composer require symfony/gateway-api-notifier`` \
                    **DSN**: ``gatewayapi://TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`GoIP`_             **Install**: ``composer require symfony/go-ip-notifier`` \
                    **DSN**: ``goip://USERNAME:PASSWORD@HOST:80?sim_slot=SIM_SLOT`` \
                    **Webhook support**: No
`Infobip`_          **Install**: ``composer require symfony/infobip-notifier`` \
                    **DSN**: ``infobip://AUTH_TOKEN@HOST?from=FROM`` \
                    **Webhook support**: No
`Iqsms`_            **Install**: ``composer require symfony/iqsms-notifier`` \
                    **DSN**: ``iqsms://LOGIN:PASSWORD@default?from=FROM`` \
                    **Webhook support**: No
`iSendPro`_         **Install**: ``composer require symfony/isendpro-notifier`` \
                    **DSN**: ``isendpro://ACCOUNT_KEY_ID@default?from=FROM&no_stop=NO_STOP&sandbox=SANDBOX`` \
                    **Webhook support**: No
`KazInfoTeh`_       **Install**: ``composer require symfony/kaz-info-teh-notifier`` \
                    **DSN**: ``kaz-info-teh://USERNAME:PASSWORD@default?sender=FROM`` \
                    **Webhook support**: No
`LightSms`_         **Install**: ``composer require symfony/light-sms-notifier`` \
                    **DSN**: ``lightsms://LOGIN:TOKEN@default?from=PHONE`` \
                    **Webhook support**: No
`LOX24`_            **Install**: ``composer require symfony/lox24-notifier`` \
                    **DSN**: ``lox24://USER:TOKEN@default?from=FROM`` \
                    **Webhook support**: Yes
`Mailjet`_          **Install**: ``composer require symfony/mailjet-notifier`` \
                    **DSN**: ``mailjet://TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`MessageBird`_      **Install**: ``composer require symfony/message-bird-notifier`` \
                    **DSN**: ``messagebird://TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`MessageMedia`_     **Install**: ``composer require symfony/message-media-notifier`` \
                    **DSN**: ``messagemedia://API_KEY:API_SECRET@default?from=FROM`` \
                    **Webhook support**: No
`Mobyt`_            **Install**: ``composer require symfony/mobyt-notifier`` \
                    **DSN**: ``mobyt://USER_KEY:ACCESS_TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`Octopush`_         **Install**: ``composer require symfony/octopush-notifier`` \
                    **DSN**: ``octopush://USERLOGIN:APIKEY@default?from=FROM&type=TYPE`` \
                    **Webhook support**: No
`OrangeSms`_        **Install**: ``composer require symfony/orange-sms-notifier`` \
                    **DSN**: ``orange-sms://CLIENT_ID:CLIENT_SECRET@default?from=FROM&sender_name=SENDER_NAME`` \
                    **Webhook support**: No
`OvhCloud`_         **Install**: ``composer require symfony/ovh-cloud-notifier`` \
                    **DSN**: ``ovhcloud://APPLICATION_KEY:APPLICATION_SECRET@default?consumer_key=CONSUMER_KEY&service_name=SERVICE_NAME`` \
                    **Webhook support**: No
                    **Extra properties in SentMessage**: ``totalCreditsRemoved``
`Plivo`_            **Install**: ``composer require symfony/plivo-notifier`` \
                    **DSN**: ``plivo://AUTH_ID:AUTH_TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`Primotexto`_       **Install**: ``composer require symfony/primotexto-notifier`` \
                    **DSN**: ``primotexto://API_KEY@default?from=FROM`` \
                    **Webhook support**: No
`Redlink`_          **Install**: ``composer require symfony/redlink-notifier`` \
                    **DSN**: ``redlink://API_KEY:APP_KEY@default?from=SENDER_NAME&version=API_VERSION`` \
                    **Webhook support**: No
`RingCentral`_      **Install**: ``composer require symfony/ring-central-notifier`` \
                    **DSN**: ``ringcentral://API_TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`Sendberry`_        **Install**: ``composer require symfony/sendberry-notifier`` \
                    **DSN**: ``sendberry://USERNAME:PASSWORD@default?auth_key=AUTH_KEY&from=FROM`` \
                    **Webhook support**: No
`Sendinblue`_       **Install**: ``composer require symfony/sendinblue-notifier`` \
                    **DSN**: ``sendinblue://API_KEY@default?sender=PHONE`` \
                    **Webhook support**: No
`SimpleTextin`_     **Install**: ``composer require symfony/simple-textin-notifier`` \
                    **DSN**: ``simpletextin://API_KEY@default?from=FROM`` \
                    **Webhook support**: No
`Sinch`_            **Install**: ``composer require symfony/sinch-notifier`` \
                    **DSN**: ``sinch://ACCOUNT_ID:AUTH_TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`Sipgate`_          **Install**: ``composer require symfony/sipgate-notifier`` \
                    **DSN**: ``sipgate://TOKEN_ID:TOKEN@default?senderId=SENDER_ID`` \
                    **Webhook support**: No
`SmsSluzba`_        **Install**: ``composer require symfony/sms-sluzba-notifier`` \
                    **DSN**: ``sms-sluzba://USERNAME:PASSWORD@default`` \
                    **Webhook support**: No
`Smsapi`_           **Install**: ``composer require symfony/smsapi-notifier`` \
                    **DSN**: ``smsapi://TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`Smsbox`_           **Install**: ``composer require symfony/smsbox-notifier`` \
                    **DSN**: ``smsbox://APIKEY@default?mode=MODE&strategy=STRATEGY&sender=SENDER`` \
                    **Webhook support**: Yes
`SmsBiuras`_        **Install**: ``composer require symfony/sms-biuras-notifier`` \
                    **DSN**: ``smsbiuras://UID:API_KEY@default?from=FROM&test_mode=0`` \
                    **Webhook support**: No
`Smsc`_             **Install**: ``composer require symfony/smsc-notifier`` \
                    **DSN**: ``smsc://LOGIN:PASSWORD@default?from=FROM`` \
                    **Webhook support**: No
`SMSense`_          **Install**: ``composer require smsense-notifier`` \
                    **DSN**: ``smsense://API_TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`SMSFactor`_        **Install**: ``composer require symfony/sms-factor-notifier`` \
                    **DSN**: ``sms-factor://TOKEN@default?sender=SENDER&push_type=PUSH_TYPE`` \
                    **Webhook support**: No
`SpotHit`_          **Install**: ``composer require symfony/spot-hit-notifier`` \
                    **DSN**: ``spothit://TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`Sweego`_           **Install**: ``composer require symfony/sweego-notifier`` \
                    **DSN**: ``sweego://API_KEY@default?region=REGION&campaign_type=CAMPAIGN_TYPE`` \
                    **Webhook support**: Yes
`Telnyx`_           **Install**: ``composer require symfony/telnyx-notifier`` \
                    **DSN**: ``telnyx://API_KEY@default?from=FROM&messaging_profile_id=MESSAGING_PROFILE_ID`` \
                    **Webhook support**: No
`TurboSms`_         **Install**: ``composer require symfony/turbo-sms-notifier`` \
                    **DSN**: ``turbosms://AUTH_TOKEN@default?from=FROM`` \
                    **Webhook support**: No
`Twilio`_           **Install**: ``composer require symfony/twilio-notifier`` \
                    **DSN**: ``twilio://SID:TOKEN@default?from=FROM`` \
                    **Webhook support**: Yes
`Unifonic`_         **Install**: ``composer require symfony/unifonic-notifier`` \
                    **DSN**: ``unifonic://APP_SID@default?from=FROM`` \
                    **Webhook support**: No
`Vonage`_           **Install**: ``composer require symfony/vonage-notifier`` \
                    **DSN**: ``vonage://KEY:SECRET@default?from=FROM`` \
                    **Webhook support**: Yes
`Yunpian`_          **Install**: ``composer require symfony/yunpian-notifier`` \
                    **DSN**: ``yunpian://APIKEY@default`` \
                    **Webhook support**: No
==================  ====================================================================================================================================

.. tip::

    Use :doc:`Symfony configuration secrets </configuration/secrets>` to securely
    store your API tokens.

.. tip::

    Some third party transports, when using the API, support status callbacks
    via webhooks. See the :doc:`Webhook documentation </webhook>` for more
    details.

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

    .. code-block:: php

        // config/packages/notifier.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'notifier' => [
                    'texter_transports' => [
                        'twilio' => env('TWILIO_DSN'),
                    ],
                ],
            ],
        ]);

.. _sending-sms:

The :class:`Symfony\\Component\\Notifier\\TexterInterface` class allows you to
send SMS messages::

    // src/Controller/SecurityController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Notifier\Message\SmsMessage;
    use Symfony\Component\Notifier\TexterInterface;
    use Symfony\Component\Routing\Attribute\Route;

    class SecurityController
    {
        #[Route('/login/success')]
        public function loginSuccess(TexterInterface $texter): Response
        {
            $options = new ProviderOptions()
                ->setPriority('high')
            ;

            $sms = new SmsMessage(
                // the phone number to send the SMS message to
                '+1411111111',
                // the message
                'A new login was detected!',
                // optionally, you can override default "from" defined in transports
                '+1422222222',
                // you can also add options object implementing MessageOptionsInterface
                $options
            );

            $sentMessage = $texter->send($sms);

            // ...
        }
    }

The ``send()`` method returns a variable of type
:class:`Symfony\\Component\\Notifier\\Message\\SentMessage` which provides
information such as the message ID and the original message contents.

.. _notifier-chat-channel:

Chat Channel
~~~~~~~~~~~~

.. warning::

    If any of the DSN values contains any character considered special in a
    URI (such as ``: / ? # [ ] @ ! $ & ' ( ) * + , ; =``), you must
    encode them. See `RFC 3986`_ for the full list of reserved characters or use the
    :phpfunction:`urlencode` function to encode them.

The chat channel is used to send chat messages to users by using
:class:`Symfony\\Component\\Notifier\\Chatter` classes. Symfony provides
integration with these chat services:

======================================   =====================================================================================
Service
======================================   =====================================================================================
`AmazonSns`_                             **Install**: ``composer require symfony/amazon-sns-notifier`` \
                                         **DSN**: ``sns://ACCESS_KEY:SECRET_KEY@default?region=REGION``
`Bluesky`_                               **Install**: ``composer require symfony/bluesky-notifier`` \
                                         **DSN**: ``bluesky://USERNAME:PASSWORD@default``
                                         **Extra properties in SentMessage**: ``cid``
`Chatwork`_                              **Install**: ``composer require symfony/chatwork-notifier`` \
                                         **DSN**: ``chatwork://API_TOKEN@default?room_id=ID``
`Discord`_                               **Install**: ``composer require symfony/discord-notifier`` \
                                         **DSN**: ``discord://TOKEN@default?webhook_id=ID``
`FakeChat`_                              **Install**: ``composer require symfony/fake-chat-notifier`` \
                                         **DSN**: ``fakechat+email://default?to=TO&from=FROM`` or ``fakechat+logger://default``
`Firebase`_                              **Install**: ``composer require symfony/firebase-notifier`` \
                                         **DSN**: ``firebase://USERNAME:PASSWORD@default``
`GoogleChat`_                            **Install**: ``composer require symfony/google-chat-notifier`` \
                                         **DSN**: ``googlechat://ACCESS_KEY:ACCESS_TOKEN@default/SPACE?thread_key=THREAD_KEY``
`LINE Bot`_                              **Install**: ``composer require symfony/line-bot-notifier`` \
                                         **DSN**: ``linebot://TOKEN@default?receiver=RECEIVER``
`LINE Notify`_                           **Install**: ``composer require symfony/line-notify-notifier`` \
                                         **DSN**: ``linenotify://TOKEN@default``
`LinkedIn`_                              **Install**: ``composer require symfony/linked-in-notifier`` \
                                         **DSN**: ``linkedin://TOKEN:USER_ID@default``
`Mastodon`_                              **Install**: ``composer require symfony/mastodon-notifier`` \
                                         **DSN**: ``mastodon://ACCESS_TOKEN@HOST``
`Matrix`_                                **Install**: ``composer require symfony/matrix-notifier`` \
                                         **DSN**: ``matrix://HOST:PORT/?accessToken=ACCESSTOKEN&ssl=SSL``
`Mattermost`_                            **Install**: ``composer require symfony/mattermost-notifier`` \
                                         **DSN**: ``mattermost://ACCESS_TOKEN@HOST/PATH?channel=CHANNEL``
`Mercure`_                               **Install**: ``composer require symfony/mercure-notifier`` \
                                         **DSN**: ``mercure://HUB_ID?topic=TOPIC``
`MicrosoftTeams`_                        **Install**: ``composer require symfony/microsoft-teams-notifier`` \
                                         **DSN**: ``microsoftteams://default/PATH``
`RocketChat`_                            **Install**: ``composer require symfony/rocket-chat-notifier`` \
                                         **DSN**: ``rocketchat://TOKEN@ENDPOINT?channel=CHANNEL``
`Slack`_                                 **Install**: ``composer require symfony/slack-notifier`` \
                                         **DSN**: ``slack://TOKEN@default?channel=CHANNEL``
`Telegram`_                              **Install**: ``composer require symfony/telegram-notifier`` \
                                         **DSN**: ``telegram://TOKEN@default?channel=CHAT_ID``
`Twitter`_                               **Install**: ``composer require symfony/twitter-notifier`` \
                                         **DSN**: ``twitter://API_KEY:API_SECRET:ACCESS_TOKEN:ACCESS_SECRET@default``
`Zendesk`_                               **Install**: ``composer require symfony/zendesk-notifier`` \
                                         **DSN**: ``zendesk://EMAIL:TOKEN@SUBDOMAIN``
`Zulip`_                                 **Install**: ``composer require symfony/zulip-notifier`` \
                                         **DSN**: ``zulip://EMAIL:TOKEN@HOST?channel=CHANNEL``
======================================   =====================================================================================

.. warning::

    By default, if you have the :doc:`Messenger component </messenger>` installed,
    the notifications will be sent through the MessageBus. If you don't have a
    message consumer running, messages will never be sent.

    To change this behavior, add the following configuration to send messages
    directly via the transport:

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                message_bus: false

.. tip::

    When Messenger is enabled, each channel (email, SMS, chat, etc.) dispatches
    its own message independently on the bus. If one channel fails and you have
    configured a :ref:`failure transport <messenger-failure-transport>`, only
    that specific channel will be retried; the other channels won't be sent
    again. This is the recommended setup for multi-channel notifications.

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

    .. code-block:: php

        // config/packages/notifier.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'notifier' => [
                    'chatter_transports' => [
                        'slack' => env('SLACK_DSN'),
                    ],
                ],
            ],
        ]);

.. _sending-chat-messages:

The :class:`Symfony\\Component\\Notifier\\ChatterInterface` class allows
you to send messages to chat services::

    // src/Controller/CheckoutController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Notifier\ChatterInterface;
    use Symfony\Component\Notifier\Message\ChatMessage;
    use Symfony\Component\Routing\Attribute\Route;

    class CheckoutController extends AbstractController
    {
        #[Route('/checkout/thankyou')]
        public function thankyou(ChatterInterface $chatter): Response
        {
            $message = new ChatMessage('You got a new invoice for 15 EUR.')
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

    .. code-block:: php

        // config/packages/mailer.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'mailer' => [
                    'dsn' => env('MAILER_DSN'),
                    'envelope' => [
                        'sender' => 'notifications@example.com',
                    ],
                ],
            ],
        ]);

.. _notifier-push-channel:

Push Channel
~~~~~~~~~~~~

.. warning::

    If any of the DSN values contains any character considered special in a
    URI (such as ``: / ? # [ ] @ ! $ & ' ( ) * + , ; =``), you must
    encode them. See `RFC 3986`_ for the full list of reserved characters or use the
    :phpfunction:`urlencode` function to encode them.

The push channel is used to send notifications to users by using
:class:`Symfony\\Component\\Notifier\\Texter` classes. Symfony provides
integration with these push services:

===============  =======================================================================================
Service
===============  =======================================================================================
`Engagespot`_    **Install**: ``composer require symfony/engagespot-notifier`` \
                 **DSN**: ``engagespot://API_KEY@default?campaign_name=CAMPAIGN_NAME``
`Expo`_          **Install**: ``composer require symfony/expo-notifier`` \
                 **DSN**: ``expo://TOKEN@default``
`Novu`_          **Install**: ``composer require symfony/novu-notifier`` \
                 **DSN**: ``novu://API_KEY@default``
`Ntfy`_          **Install**: ``composer require symfony/ntfy-notifier`` \
                 **DSN**: ``ntfy://default/TOPIC``
`OneSignal`_     **Install**: ``composer require symfony/one-signal-notifier`` \
                 **DSN**: ``onesignal://APP_ID:API_KEY@default?defaultRecipientId=DEFAULT_RECIPIENT_ID``
`PagerDuty`_     **Install**: ``composer require symfony/pager-duty-notifier`` \
                 **DSN**: ``pagerduty://TOKEN@SUBDOMAIN``
`Pushover`_      **Install**: ``composer require symfony/pushover-notifier`` \
                 **DSN**: ``pushover://USER_KEY:APP_TOKEN@default``
`Pushy`_         **Install**: ``composer require symfony/pushy-notifier`` \
                 **DSN**: ``pushy://API_KEY@default``
===============  =======================================================================================

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

    .. code-block:: php

        // config/packages/notifier.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'notifier' => [
                    'texter_transports' => [
                        'expo' => env('EXPO_DSN'),
                    ],
                ],
            ],
        ]);

.. _notifier-desktop-channel:

Desktop Channel
~~~~~~~~~~~~~~~

The desktop channel is used to display local desktop notifications on the same
host machine using :class:`Symfony\\Component\\Notifier\\Texter` classes. Currently,
Symfony is integrated with the following providers:

===============  ================================================  ==============================================================================
Provider         Install                                           DSN
===============  ================================================  ==============================================================================
`JoliNotif`_     ``composer require symfony/joli-notif-notifier``  ``jolinotif://default``
===============  ================================================  ==============================================================================

If you are using :ref:`Symfony Flex <symfony-flex>`, installing that package will
also create the necessary environment variable and configuration. Otherwise, you'll
need to add the following manually:

1) Add the correct DSN in your ``.env`` file:

.. code-block:: bash

    # .env
    JOLINOTIF=jolinotif://default

2) Update the Notifier configuration to add a new texter transport:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                texter_transports:
                    jolinotif: '%env(JOLINOTIF)%'

    .. code-block:: php

        // config/packages/notifier.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'notifier' => [
                    'texter_transports' => [
                        'jolinotif' => env('JOLINOTIF'),
                    ],
                ],
            ],
        ]);

Now you can send notifications to your desktop as follows::

    // src/Notifier/SomeService.php
    use Symfony\Component\Notifier\Message\DesktopMessage;
    use Symfony\Component\Notifier\TexterInterface;
    // ...

    class SomeService
    {
        public function __construct(
            private TexterInterface $texter,
        ) {
        }

        public function notifyNewSubscriber(User $user): void
        {
            $message = new DesktopMessage(
                'New subscription! 🎉',
                sprintf('%s is a new subscriber', $user->getFullName())
            );

            $this->texter->send($message);
        }
    }

These notifications can be customized further, and depending on your operating system,
they may support features like custom sounds, icons, and more::

    use Symfony\Component\Notifier\Bridge\JoliNotif\JoliNotifOptions;
    // ...

    $options = new JoliNotifOptions()
        ->setIconPath('/path/to/icons/error.png')
        ->setExtraOption('sound', 'sosumi')
        ->setExtraOption('url', 'https://example.com');

    $message = new DesktopMessage('Production is down', <<<CONTENT
        ❌ Server prod-1 down
        ❌ Server prod-2 down
        ✅ Network is up
        CONTENT, $options);

    $texter->send($message);

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

    .. code-block:: php

        // config/packages/notifier.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'notifier' => [
                    'chatter_transports' => [
                        // Send notifications to Slack and use Telegram if
                        // Slack errored
                        'main' => env('SLACK_DSN').' || '.env('TELEGRAM_DSN'),

                        // Send notifications to the next scheduled transport calculated by round robin
                        'roundrobin' => env('SLACK_DSN').' && '.env('TELEGRAM_DSN'),
                    ],
                ],
            ]
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

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Notifier\Notification\Notification;
    use Symfony\Component\Notifier\NotifierInterface;
    use Symfony\Component\Notifier\Recipient\Recipient;

    class InvoiceController extends AbstractController
    {
        #[Route('/invoice/create')]
        public function create(NotifierInterface $notifier): Response
        {
            // ...

            // Create a Notification that has to be sent
            // using the "email" channel
            $notification = new Notification('New Invoice', ['email'])
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
    the current request's :ref:`session flashbag <flash-messages>`;

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

    .. code-block:: php

        // config/packages/notifier.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
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
            ],
        ]);

Now, whenever the notification's importance is set to "high", it will be
sent using the Slack transport::

    // ...
    class InvoiceController extends AbstractController
    {
        #[Route('/invoice/create')]
        public function invoice(NotifierInterface $notifier): Response
        {
            // ...

            $notification = new Notification('New Invoice')
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

        public function getChannels(RecipientInterface $recipient): array
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

        public function asChatMessage(RecipientInterface $recipient, ?string $transport = null): ?ChatMessage
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
:class:`Symfony\\Component\\Notifier\\Notification\\EmailNotificationInterface`,
:class:`Symfony\\Component\\Notifier\\Notification\\PushNotificationInterface`
and
:class:`Symfony\\Component\\Notifier\\Notification\\DesktopNotificationInterface`
also exists to modify messages sent to those channels.

Customize Browser Notifications (Flash Messages)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default behavior for browser channel notifications is to add a
:ref:`flash message <flash-messages>` with ``notification`` as its key.

However, you might prefer to map the importance level of the notification to the
type of flash message, so you can tweak their style.

You can do that by overriding the default ``notifier.flash_message_importance_mapper``
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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Notifier\FlashMessage\BootstrapFlashMessageImportanceMapper;

        return App::config([
            'services' => [
                'flash_message_importance_mapper' => [
                    'class' => BootstrapFlashMessageImportanceMapper::class,
                ],
            ],
        ]);

Testing Notifier
----------------

Symfony provides a :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\NotificationAssertionsTrait`
which provide useful methods for testing your Notifier implementation.
You can benefit from this class by using it directly or extending the
:class:`Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase`.

See :ref:`testing documentation <notifier-assertions>` for the list of available assertions.

Disabling Delivery
------------------

While developing (or testing), you may want to disable delivery of notifications
entirely. You can do this by forcing Notifier to use the ``NullTransport`` for
all configured texter and chatter transports only in the ``dev`` (and/or
``test``) environment:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        when@dev:
            framework:
                notifier:
                    texter_transports:
                        twilio: 'null://null'
                    chatter_transports:
                        slack: 'null://null'

    .. code-block:: php

        // config/packages/notifier.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'when@dev' => [
                'framework' => [
                    'notifier' => [
                        'texter_transports' => [
                            'twilio' => 'null://null',
                        ],
                        'chatter_transports' => [
                            'slack' => 'null://null',
                        ],
                    ],
                ],
            ],
        ]);

.. _notifier-events:

Using Events
------------

The :class:`Symfony\\Component\\Notifier\\Transport` class of the Notifier component
allows you to optionally hook into the lifecycle via events.

The ``MessageEvent`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: Doing something before the message is sent, like logging
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
        $message = $event->getMessage();

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
.. _`Bluesky`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Bluesky/README.md
.. _`Brevo`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Brevo/README.md
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
.. _`GoIP`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/GoIP/README.md
.. _`GoogleChat`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/GoogleChat/README.md
.. _`Infobip`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Infobip/README.md
.. _`Iqsms`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Iqsms/README.md
.. _`iSendPro`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Isendpro/README.md
.. _`JoliNotif`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/JoliNotif/README.md
.. _`KazInfoTeh`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/KazInfoTeh/README.md
.. _`LINE Bot`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/LineBot/README.md
.. _`LINE Notify`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/LineNotify/README.md
.. _`LightSms`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/LightSms/README.md
.. _`LinkedIn`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/LinkedIn/README.md
.. _`LOX24`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Lox24/README.md
.. _`Mailjet`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Mailjet/README.md
.. _`Mastodon`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Mastodon/README.md
.. _`Matrix`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Matrix/README.md
.. _`Mattermost`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Mattermost/README.md
.. _`Mercure`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Mercure/README.md
.. _`MessageBird`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/MessageBird/README.md
.. _`MessageMedia`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/MessageMedia/README.md
.. _`MicrosoftTeams`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/MicrosoftTeams/README.md
.. _`Mobyt`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Mobyt/README.md
.. _`Novu`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Novu/README.md
.. _`Ntfy`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Ntfy/README.md
.. _`Octopush`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Octopush/README.md
.. _`OneSignal`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/OneSignal/README.md
.. _`OrangeSms`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/OrangeSms/README.md
.. _`OvhCloud`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/OvhCloud/README.md
.. _`PagerDuty`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/PagerDuty/README.md
.. _`Plivo`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Plivo/README.md
.. _`Primotexto`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Primotexto/README.md
.. _`Pushover`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Pushover/README.md
.. _`Pushy`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Pushy/README.md
.. _`Redlink`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Redlink/README.md
.. _`RFC 3986`: https://www.ietf.org/rfc/rfc3986.txt
.. _`RingCentral`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/RingCentral/README.md
.. _`RocketChat`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/RocketChat/README.md
.. _`SMSFactor`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/SmsFactor/README.md
.. _`Sendberry`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Sendberry/README.md
.. _`Sendinblue`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Sendinblue/README.md
.. _`SimpleTextin`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/SimpleTextin/README.md
.. _`Sinch`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Sinch/README.md
.. _`Sipgate`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Sipgate/README.md
.. _`Slack`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Slack/README.md
.. _`SmsBiuras`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/SmsBiuras/README.md
.. _`Smsbox`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Smsbox/README.md
.. _`Smsapi`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Smsapi/README.md
.. _`Smsc`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Smsc/README.md
.. _`SMSense`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/SMSense/README.md
.. _`SmsSluzba`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/SmsSluzba/README.md
.. _`SpotHit`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/SpotHit/README.md
.. _`Sweego`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Sweego/README.md
.. _`Telegram`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Telegram/README.md
.. _`Telnyx`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Telnyx/README.md
.. _`TurboSms`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/TurboSms/README.md
.. _`Twilio`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Twilio/README.md
.. _`Twitter`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Twitter/README.md
.. _`Unifonic`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Unifonic/README.md
.. _`Vonage`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Vonage/README.md
.. _`Yunpian`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Yunpian/README.md
.. _`Zendesk`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Zendesk/README.md
.. _`Zulip`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Notifier/Bridge/Zulip/README.md
