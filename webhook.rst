Webhook
=======

.. versionadded:: 6.3

    The Webhook component was introduced in Symfony 6.3.

The Webhook component is used to respond to remote webhooks to trigger actions
in your application. This document focuses on using webhooks to listen to remote
events in other Symfony components.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/webhook

Usage in combination with the Mailer component
----------------------------------------------

When using a third-party mailer, you can use the Webhook component to receive
webhook calls from the third-party mailer.

In this example Mailgun is used with ``'mailer_mailgun'`` as webhook type.
Any type name can be used as long as it's unique. Make sure to use it in the
routing configuration, the webhook URL and the RemoteEvent consumer.

Install the third party mailer as described in the documentation of the
:ref:`Mailer component <mailer_3rd_party_transport>`.

The Webhook component routing needs to be defined:

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

Currently, the following third-party services support webhooks:

======== ==========================================
Service  Parser service name
======== ==========================================
Brevo    ``mailer.webhook.request_parser.brevo``
Mailgun  ``mailer.webhook.request_parser.mailgun``
Mailjet  ``mailer.webhook.request_parser.mailjet``
Postmark ``mailer.webhook.request_parser.postmark``
Sendgrid ``mailer.webhook.request_parser.sendgrid``
Vonage   ``notifier.webhook.request_parser.vonage``
======== ==========================================

.. versionadded:: 6.3

    The support for Mailgun and Postmark was introduced in Symfony 6.3.

.. versionadded:: 6.4

    The support for Brevo, Mailjet, Sendgrid and Vonage was introduced in
    Symfony 6.4.

Set up the webhook in the third-party mailer. For Mailgun, you can do this
in the control panel. As URL, make sure to use the ``/webhook/mailer_mailgun``
path behind the domain you're using.

Mailgun will provide a secret for the webhook. Add this secret to your ``.env``
file:

.. code-block:: env

    MAILER_MAILGUN_SECRET=your_secret

With this done, you can now add a RemoteEvent consumer to react to the webhooks::

use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\Event\Mailer\MailerDeliveryEvent;
use Symfony\Component\RemoteEvent\Event\Mailer\MailerEngagementEvent;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('mailer_mailgun')]
final readonly class WebhookListener implements ConsumerInterface
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
