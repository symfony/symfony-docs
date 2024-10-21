Webhook
=======

.. versionadded:: 6.3

    The Webhook component was introduced in Symfony 6.3.

Essentially, webhooks serve as event notification mechanisms, typically via HTTP POST requests, enabling real-time updates.

The Webhook component has two primary functions:
1. Consumer: Listen and respond to remote webhooks dispatched by 3rd party services.
2. Provider: Dispatch webhooks to 3rd party services.

This document provides guidance on utilizing the Webhook component within the context of a full-stack Symfony application.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/webhook


Consuming Webhooks
------------------

Consider a third-party API that allows you to track stock levels for various products and can send webhooks to your
Symfony application.

From the perspective of your application (the *consumer*), which receives the webhook, three primary phases need to be anticipated:

1) Receiving the webhook

2) Verifying the webhook and constructing the corresponding Remote Event

3) Using the received data.

Symfony Webhook, when used alongside Symfony RemoteEvent, streamlines the management of these fundamental phases.

A Single Entry Endpoint: Receive
--------------------------------

Through the built-in :class:`Symfony\\Component\\Webhook\\Controller\\WebhookController`, a unique entry point is offered to manage all webhooks
that our application may receive, whether from the Twilio API, a custom API, or other sources.

By default, any URL prefixed with ``/webhook`` will be routed to this :class:`Symfony\\Component\\Webhook\\Controller\\WebhookController`.
Additionally, you have the flexibility to customize this URL prefix and rename it according to your preferences.

.. code-block:: yaml

    # config/routes/webhook.yaml
    webhook:
        resource: '@FrameworkBundle/Resources/config/routing/webhook.xml'
        prefix: /webhook # or possible to customize

Additionally, you must specify the parser service responsible for analyzing and parsing incoming webhooks.
It's crucial to understand that the :class:`Symfony\\Component\\Webhook\\Controller\\WebhookController` itself remains provider-agnostic, utilizing
a routing mechanism to determine which parser should handle incoming webhooks for analysis.

As mentioned earlier, incoming webhooks require a specific prefix to be directed to the :class:`Symfony\\Component\\Webhook\\Controller\\WebhookController`.
This prefix forms the initial part of the URL following the domain name.
The subsequent part of the URL, following this prefix, should correspond to the routing name chosen in your configuration.

The routing name must be unique as this is what connects the provider with your
webhook consumer code.

.. code-block:: yaml

    # config/webhook.yaml
    # e.g https://example.com/webhook/my_first_parser

    framework:
      webhook:
        routing:
          my_first_parser: # routing name
            service: App\Webhook\ExampleRequestParser
          # secret: your_secret_here # optionally

At this point in the configuration, you can also define a secret for webhooks that require one.

All parser services defined for each routing name of incoming webhooks will be injected into the :class:`Symfony\\Component\\Webhook\\Controller\\WebhookController`.


A Service Parser: Verifying and Constructing the Corresponding Remote Event
---------------------------------------------------------------------------

It's important to note that Symfony provides built-in parser services.
In such cases, configuring the service name and optionally the required secret in the configuration is sufficient; there's no need to create your own parser.

Usage in Combination with the Mailer Component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using a third-party mailer provider, you can use the Webhook component to
receive webhook calls from this provider.

Currently, the following third-party mailer providers support webhooks:

============== ==========================================
Mailer Service Parser service name
============== ==========================================
Brevo          ``mailer.webhook.request_parser.brevo``
Mailgun        ``mailer.webhook.request_parser.mailgun``
Mailjet        ``mailer.webhook.request_parser.mailjet``
Postmark       ``mailer.webhook.request_parser.postmark``
Sendgrid       ``mailer.webhook.request_parser.sendgrid``
============== ==========================================

.. versionadded:: 6.4

    The support for Brevo, Mailjet and Sendgrid was introduced in Symfony 6.4.

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

The webhook routing name is part of the URL you need to configure at the
third-party mailer provider. The URL is the concatenation of your domain name
and the routing name you chose in the configuration (like
``https://example.com/webhook/mailer_mailgun``.

For Mailgun, you will get a secret for the webhook. Store this secret as
MAILER_MAILGUN_SECRET (in the :doc:`secrets management system
</configuration/secrets>` or in a ``.env`` file).

Usage in Combination with the Notifier Component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The usage of the Webhook component when using a third-party transport in
the Notifier is very similar to the usage with the Mailer.

Currently, the following third-party SMS transports support webhooks:

============ ==========================================
SMS service  Parser service name
============ ==========================================
Twilio       ``notifier.webhook.request_parser.twilio``
Vonage       ``notifier.webhook.request_parser.vonage``
============ ==========================================

A custom Parser
~~~~~~~~~~~~~~~

However, if your webhook, as illustrated in the example discussed, originates from a custom API,
you will need to create a parser service that implements :class:`Symfony\\Component\\Webhook\\Client\\RequestParserInterface` or extends :class:`Symfony\\Component\\Webhook\\Client\\AbstractRequestParser`.

By extending the :class:`Symfony\\Component\\Webhook\\Client\\AbstractRequestParser`, you'll inherit a predefined structure for the incoming webhook analysis step. You'll only need to implement the
:method:`Symfony\\Component\\Webhook\\Client\\AbstractRequestParser::doParse` method and specify any RequestMatcher(s) you want to apply to the incoming webhooks in the `Symfony\\Component\\Webhook\\Client\\AbstractRequestParser::getRequestMatcher` method.

This process can be simplified using a command:

.. code-block:: terminal

    $ php bin/console make:webhook

.. tip::

    Starting in `MakerBundle`_ ``v1.58.0``, you can run ``php bin/console make:webhook``
    to generate the request parser and consumer files needed to create your own
    Webhook.

Depending on the routing name provided to this command, which corresponds, as discussed earlier,
to the second and final part of the incoming webhook URL, the command will generate the parser service responsible for parsing your webhook.

Additionally, it allows you to specify which RequestMatcher(s) from the HttpFoundation component should be applied to the incoming webhook request.
This constitutes the initial step of your gateway process, ensuring that the format of the incoming webhook is validated before proceeding to its thorough analysis.

Furthermore, the command will create the RemoteEvent consumer class implementing the :class:`Symfony\\Component\\RemoteEvent\\Consumer\\ConsumerInterface`, which manages the remote event returned by the parser.

Moreover, this command will automatically update the previously discussed configuration with the webhook's routing name.
This ensures that not only are the parser and consumer generated, but also that the configuration is seamlessly updated::

    // src/Webhook/ExampleRequestParser.php
    final class ExampleRequestParser extends AbstractRequestParser
    {
        protected function getRequestMatcher(): RequestMatcherInterface
        {
            return new ChainRequestMatcher([
                new IsJsonRequestMatcher(),
                new MethodRequestMatcher('POST'),
                new HostRequestMatcher('regex'),
                new ExpressionRequestMatcher(new ExpressionLanguage(), new Expression('expression')),
                new PathRequestMatcher('regex'),
                new IpsRequestMatcher(['127.0.0.1']),
                new PortRequestMatcher(443),
                new SchemeRequestMatcher('https'),
            ]);
        }

        /**
         * @throws JsonException
         */
        protected function doParse(Request $request, #[\SensitiveParameter] string $secret): ?RemoteEvent
        {
            // Adapt or replace the content of this method to fit your need.
            // e.g Validate the request against $secret and/or Validate the request payload
            // and/or Parse the request payload and return a RemoteEvent object or throw an exception

            return new RemoteEvent(
                $payload['name'],
                $payload['id'],
                $payload,
            );
        }
    }


Now, imagine that in your case, you receive a notification of a product stock outage, and the received JSON contains details about the affected product and the severity of the outage.
Depending on the specific product and the severity of the stock outage, your application can trigger different remote events.

For instance, you might define ``HighPriorityStockRefillEvent``, ``MediumPriorityStockRefillEvent`` and ``LowPriorityStockRefillEvent``.


By implementing the :class:`Symfony\\Component\\RemoteEvent\\PayloadConverterInterface` and its :method:`Symfony\\Component\\RemoteEvent\\PayloadConverterInterface::convert` method, you can encapsulate all the business logic
involved in creating the appropriate remote event. This converter will be invoked by your parser.

For inspiration, you can refer to :class:`Symfony\\Component\\Mailer\\Bridge\\Mailgun\\RemoteEvent\\MailGunPayloadConverter`::

    // src/Webhook/ExampleRequestParser.php
    final class ExampleRequestParser extends AbstractRequestParser
    {
        protected function getRequestMatcher(): RequestMatcherInterface
        {
            ...
        }

        /**
         * @throws JsonException
         */
        protected function doParse(Request $request, #[\SensitiveParameter] string $secret): ?RemoteEvent
        {
            // Adapt or replace the content of this method to fit your need.
            // e.g Validate the request against $secret and/or Validate the request payload
            // and/or Parse the request payload and return a RemoteEvent object or throw an exception

            try {
                return $this->converter->convert($content['...']);
            } catch (ParseException $e) {
                throw new RejectWebhookException(406, $e->getMessage(), $e);
            }
        }
    }

    // src/RemoteEvent/ExamplePayloadConverter.php
    final class ExamplePayloadConverter implements PayloadConverterInterface
    {
        public function convert(array $payload): AbstractPriorityStockRefillEvent
        {
            ...

            if (....) {
                $event = new HighPriorityStockRefillEvent($name, $payload['id]', $payload])
            } elseif {
                $event = new MediumPriorityStockRefillEvent($name, $payload['id]', $payload])
            } else {
                $event = new LowPriorityStockRefillEvent($name, $payload['id]', $payload])
            }

            ....

            return $event;
        }
    }

From this, we can see that the RemoteEvent component is highly beneficial for handling webhooks.
It enables you to convert the incoming webhook data into validated objects that can be efficiently manipulated and utilized according to your requirements.

Remote Event Consumer: Handling and Manipulating The Received Data
------------------------------------------------------------------

It is important to note that when the incoming webhook is processed by the :class:`Symfony\\Component\\Webhook\\Controller\\WebhookController`, you have the option to handle the consumption of remote events asynchronously.
Indeed, this can be configured using a bus, with the default setting pointing to the Messenger component's default bus.
For more details, refer to the :doc:`Symfony Messenger </components/messenger>` documentation


Whether the remote event is processed synchronously or asynchronously, you'll need a consumer that implements the :class:`Symfony\\Component\\RemoteEvent\\Consumer\\ConsumerInterface`.
If you used the command to set this up, it was created automatically

.. code-block:: terminal

    $ php bin/console make:webhook

Otherwise, you'll need to manually add it with the ``AsRemoteEventConsumer`` attribute which will allow you to designate this class as a consumer implementing :class:`Symfony\\Component\\RemoteEvent\\Consumer\\ConsumerInterface`,
making it recognizable to the RemoteEvent component so it can pass the converted object to it.
Additionally, the name passed to your attribute is critical; it must match the configuration entry under routing that you specified in the ``webhook.yaml`` file, which in your case is ``my_first_parser``.

In the :method:`Symfony\\Component\\RemoteEvent\\Consumer\\ConsumerInterface::consume` method,
you can access your object containing the event data that triggered the webhook, allowing you to respond appropriately.

For example, you can use Mercure to broadcast updates to clients of the hub, among other actions ...::

    // src/Webhook/ExampleRequestParser.php
    #[AsRemoteEventConsumer('my_first_parser')] # routing name
    final class ExampleWebhookConsumer implements ConsumerInterface
    {
        public function __construct()
        {
        }

        public function consume(RemoteEvent $event): void
        {
            // Implement your own logic here
        }
    }


If you are using it alongside other components that already include built-in parsers,
you will need to configure the settings (as mentioned earlier) and also create your own consumer.
This is necessary because it involves your own business logic and your specific reactions to the remote event(s) that may be received from the built-in parsers.

Usage in Combination with the Mailer Component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can add a :class:`Symfony\\Component\\RemoteEvent\\RemoteEvent` consumer
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
    class MailerWebhookConsumer implements ConsumerInterface
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
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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


Providing Webhooks
------------------

Continuing with our example, but this time from the provider's perspective rather than the consumer's.
Let's assume that a webhook has been registered to be notified when certain events occur, such as stock depletion for a specific product.

During the registration of this webhook, several pieces of information were included in the POST request,
including the endpoint to be called upon the occurrence of an event, such as stock depletion for a certain product:

.. code-block:: json

    {
      "name": "a name",
      "url": "something/webhook/routing_name",
      "signature": "...",
      "events": ["out_of_stock_event"],
      ....
    }


Consider a scenario where, after several updates via API calls, a product's stock is depleted.
Now, let's assume the API has a mechanism that allows it to react and trigger the sending of webhooks in response to this event.
At this point, the API needs to be able to dispatch these webhook notifications to the endpoints specified by subscribers during their webhook registration.

Symfony Webhook and Symfony RemoteEvent, when combined with Symfony Messenger, are also useful for APIs responsible for dispatching webhooks.

For instance, you can utilize the specific :class:`Symfony\\Component\\Webhook\\Messenger\\SendWebhookMessage` and
:class:`Symfony\\Component\\Webhook\\Messenger\\SendWebhookHandler` provided to dispatch the webhook either synchronously or asynchronously using the Symfony Messenger component.

The SendWebhookMessage takes a :class:`Symfony\\Component\\Webhook\\Subscriber` as its first argument, which includes the destination URL and the mandatory secret.
If the secret is missing, an exception will be thrown.

As a second argument, it expects a :class:`Symfony\\Component\\RemoteEvent\\RemoteEvent` containing the webhook event name, the ID, and the payload, which is the substantial information you wish to communicate::

    $subscriber = new Subscriber($urlCallback, $secret);
    $event = new RemoteEvent(‘out_of_stock_event, ‘1’, […]);
    $this->bus->dispatch(new SendWebhookMessage($subscriber, $event));

The :class:`Symfony\\Component\\Webhook\\Messenger\\SendWebhookHandler` configures the headers, the body of the request,
and finally sign the headers before making an HTTP request to the specified URL using Symfony's HttpClient component::

By default, it will add the following headers:

1) Webhook-Event with the event name
2) Webhook-Id with the id of the event
3) Webhook-Signature with the signature, generated as a hmac (using sha256 by default) of the concatenation of the event name,
   event id and body, using the secret of the subscriber. The value of the header provides the algorithm used for the signature::

    -options: array:2 [▼
        "headers" => array:4 [▼
          "Webhook-Event" => "out_of_stockt"
          "Webhook-Id" => "1"
          "Content-Type" => "application/json"
          "Webhook-Signature" => "sha256=...."
        ]
        "body" => "{"id":1,"product":"...", ...}"
      ]

However it is also entirely possible to create your own mechanism by defining your own message - handler or by reusing differently the
:class:`Symfony\\Component\\Webhook\\Server\\TransportInterface` in your own logic and code structure to ensure that the webhook events are correctly sent to the correct destination.


.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
