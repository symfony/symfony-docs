Webhook
=======

The Webhook component is used to respond to remote webhooks to trigger actions
in your application. This document focuses on using webhooks to listen to remote
events in other Symfony components.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/webhook

Usage in Combination with the Mailer Component
----------------------------------------------

.. admonition:: Screencast
    :class: screencast

    Like video tutorials? Check out the `Webhook Component for Email Events screencast`_.

When using a third-party mailer provider, you can use the Webhook component to
receive webhook calls from this provider.

Currently, the following third-party mailer providers support webhooks:

============== ============================================
Mailer Service Parser service name
============== ============================================
AhaSend        ``mailer.webhook.request_parser.ahasend``
Brevo          ``mailer.webhook.request_parser.brevo``
Mandrill       ``mailer.webhook.request_parser.mailchimp``
MailerSend     ``mailer.webhook.request_parser.mailersend``
Mailgun        ``mailer.webhook.request_parser.mailgun``
Mailjet        ``mailer.webhook.request_parser.mailjet``
Mailomat       ``mailer.webhook.request_parser.mailomat``
Mailtrap       ``mailer.webhook.request_parser.mailtrap``
Postmark       ``mailer.webhook.request_parser.postmark``
Resend         ``mailer.webhook.request_parser.resend``
Sendgrid       ``mailer.webhook.request_parser.sendgrid``
Sweego         ``mailer.webhook.request_parser.sweego``
============== ============================================

.. versionadded:: 7.1

    The support for ``Resend`` and ``MailerSend`` were introduced in Symfony 7.1.

.. versionadded:: 7.2

    The ``Mandrill``, ``Mailomat``, ``Mailtrap``, and ``Sweego`` integrations were introduced in
    Symfony 7.2.

.. versionadded:: 7.3

    The ``AhaSend`` integration was introduced in Symfony 7.3.

.. note::

    Install the third-party mailer provider you want to use as described in the
    documentation of the :ref:`Mailer component <mailer_3rd_party_transport>`.
    Mailgun is used as the provider in this document as an example.

To connect the provider to your application, you need to configure the Webhook
component routing:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            webhook:
                routing:
                    mailer_mailgun:
                        service: 'mailer.webhook.request_parser.mailgun'
                        secret: '%env(MAILER_MAILGUN_SECRET)%'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xmlns:framework="http://symfony.com/schema/dic/symfony"
                   xsi:schemaLocation="http://symfony.com/schema/dic/services
                        https://symfony.com/schema/dic/services/services-1.0.xsd
                        http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
            <framework:config>
                <framework:webhook enabled="true">
                    <framework:routing type="mailer_mailgun">
                        <framework:service>mailer.webhook.request_parser.mailgun</framework:service>
                        <framework:secret>%env(MAILER_MAILGUN_SECRET)%</framework:secret>
                    </framework:routing>
                </framework:webhook>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use App\Webhook\MailerWebhookParser;
        use Symfony\Config\FrameworkConfig;
        return static function (FrameworkConfig $frameworkConfig): void {
            $webhookConfig = $frameworkConfig->webhook();
            $webhookConfig
                ->routing('mailer_mailgun')
                ->service('mailer.webhook.request_parser.mailgun')
                ->secret('%env(MAILER_MAILGUN_SECRET)%')
            ;
        };

In this example, we are using ``mailer_mailgun`` as the webhook routing name.
The routing name must be unique as this is what connects the provider with your
webhook consumer code.

The webhook routing name is part of the URL you need to configure at the
third-party mailer provider. The URL is the concatenation of your domain name
and the routing name you chose in the configuration (like
``https://example.com/webhook/mailer_mailgun``).

For Mailgun, you will get a secret for the webhook. Store this secret as
MAILER_MAILGUN_SECRET (in the :doc:`secrets management system
</configuration/secrets>` or in a ``.env`` file).

When done, add a :class:`Symfony\\Component\\RemoteEvent\\RemoteEvent` consumer
to react to incoming webhooks (the webhook routing name is what connects your
class to the provider).

For mailer webhooks, react to the
:class:`Symfony\\Component\\RemoteEvent\\Event\\Mailer\\MailerDeliveryEvent` or
:class:`Symfony\\Component\\RemoteEvent\\Event\\Mailer\\MailerEngagementEvent`
events::

    use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
    use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
    use Symfony\Component\RemoteEvent\Event\Mailer\MailerDeliveryEvent;
    use Symfony\Component\RemoteEvent\Event\Mailer\MailerEngagementEvent;
    use Symfony\Component\RemoteEvent\RemoteEvent;

    #[AsRemoteEventConsumer('mailer_mailgun')]
    class WebhookListener implements ConsumerInterface
    {
        public function consume(RemoteEvent $event): void
        {
            if ($event instanceof MailerDeliveryEvent) {
                $this->handleMailDelivery($event);
            } elseif ($event instanceof MailerEngagementEvent) {
                $this->handleMailEngagement($event);
            } else {
                // This is not an email event
                return;
            }
        }

        private function handleMailDelivery(MailerDeliveryEvent $event): void
        {
            // Handle the mail delivery event
        }

        private function handleMailEngagement(MailerEngagementEvent $event): void
        {
            // Handle the mail engagement event
        }
    }

Usage in Combination with the Notifier Component
------------------------------------------------

The usage of the Webhook component when using a third-party transport in
the Notifier is very similar to the usage with the Mailer.

Currently, the following third-party SMS transports support webhooks:

============ ==========================================
SMS service  Parser service name
============ ==========================================
Twilio       ``notifier.webhook.request_parser.twilio``
Smsbox       ``notifier.webhook.request_parser.smsbox``
Sweego       ``notifier.webhook.request_parser.sweego``
Vonage       ``notifier.webhook.request_parser.vonage``
============ ==========================================

For SMS webhooks, react to the
:class:`Symfony\\Component\\RemoteEvent\\Event\\Sms\\SmsEvent` event::

    use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
    use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
    use Symfony\Component\RemoteEvent\Event\Sms\SmsEvent;
    use Symfony\Component\RemoteEvent\RemoteEvent;

    #[AsRemoteEventConsumer('notifier_twilio')]
    class WebhookListener implements ConsumerInterface
    {
        public function consume(RemoteEvent $event): void
        {
            if ($event instanceof SmsEvent) {
                $this->handleSmsEvent($event);
            } else {
                // This is not an SMS event
                return;
            }
        }

        private function handleSmsEvent(SmsEvent $event): void
        {
            // Handle the SMS event
        }
    }

Creating a Custom Webhook
-------------------------

.. tip::

    Starting in `MakerBundle`_ ``v1.58.0``, you can run ``php bin/console make:webhook``
    to generate the request parser and consumer files needed to create your own
    Webhook.

Sending Webhooks
----------------

The Webhook component can also send webhooks to external systems. A webhook is
sent as a POST request with a JSON body and the following headers:

* ``Webhook-Event``: the event name
* ``Webhook-Id``: a unique identifier for the event
* ``Webhook-Signature``: an HMAC-SHA256 signature of the payload, allowing the
  receiver to verify authenticity

To send a webhook, create a
:class:`Symfony\\Component\\Webhook\\Subscriber` (representing the remote
endpoint) and a :class:`Symfony\\Component\\RemoteEvent\\RemoteEvent`, then
dispatch a
:class:`Symfony\\Component\\Webhook\\Messenger\\SendWebhookMessage` via the
message bus::

    use Symfony\Component\Messenger\MessageBusInterface;
    use Symfony\Component\RemoteEvent\RemoteEvent;
    use Symfony\Component\Webhook\Messenger\SendWebhookMessage;
    use Symfony\Component\Webhook\Subscriber;

    class WebhookNotifier
    {
        public function __construct(
            private MessageBusInterface $bus,
        ) {
        }

        public function notifyExternalService(): void
        {
            $subscriber = new Subscriber(
                'https://example.com/webhook-endpoint',
                'your_shared_secret'
            );

            $event = new RemoteEvent('user.created', 'event-id-123', [
                'user_id' => 42,
                'email' => 'user@example.com',
            ]);

            $this->bus->dispatch(new SendWebhookMessage($subscriber, $event));
        }
    }

The message is handled asynchronously by the Messenger component. Make sure the
``webhook`` transport is configured in your messenger routing:

.. code-block:: yaml

    # config/packages/messenger.yaml
    framework:
        messenger:
            routing:
                'Symfony\Component\Webhook\Messenger\SendWebhookMessage': async

You can also send webhooks synchronously by injecting the
:class:`Symfony\\Component\\Webhook\\Server\\TransportInterface` directly::

    use Symfony\Component\RemoteEvent\RemoteEvent;
    use Symfony\Component\Webhook\Server\TransportInterface;
    use Symfony\Component\Webhook\Subscriber;

    class WebhookNotifier
    {
        public function __construct(
            private TransportInterface $webhookTransport,
        ) {
        }

        public function notifyExternalService(): void
        {
            $subscriber = new Subscriber(
                'https://example.com/webhook-endpoint',
                'your_shared_secret'
            );

            $event = new RemoteEvent('user.created', 'event-id-123', [
                'user_id' => 42,
                'email' => 'user@example.com',
            ]);

            $this->webhookTransport->send($subscriber, $event);
        }
    }

.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`Webhook Component for Email Events screencast`: https://symfonycasts.com/screencast/mailtrap/email-event-webhook
