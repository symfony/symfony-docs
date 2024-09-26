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

When using a third-party mailer provider, you can use the Webhook component to
receive webhook calls from this provider.

Currently, the following third-party mailer providers support webhooks:

============== ============================================
Mailer Service Parser service name
============== ============================================
Brevo          ``mailer.webhook.request_parser.brevo``
MailerSend     ``mailer.webhook.request_parser.mailersend``
Mailgun        ``mailer.webhook.request_parser.mailgun``
Mailjet        ``mailer.webhook.request_parser.mailjet``
Postmark       ``mailer.webhook.request_parser.postmark``
Resend         ``mailer.webhook.request_parser.resend``
Sendgrid       ``mailer.webhook.request_parser.sendgrid``
============== ============================================

.. versionadded:: 7.1

    The support for ``Resend`` and ``MailerSend`` were introduced in Symfony 7.1.

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
``https://example.com/webhook/mailer_mailgun``.

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

.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
