Webhook
=======

A webhook is a mechanism for sending event notifications between systems,
typically delivered via HTTP POST requests.

The Webhook component provides two primary capabilities:

#. **Consuming**: receive and process webhook calls from remote systems;
#. **Sending**: dispatch webhook callbacks to registered endpoints when events occur.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/webhook

Consuming Webhooks
------------------

The Webhook component, combined with RemoteEvent, enables you to receive and
process webhooks through three phases:

#. Receiving the webhook via a dedicated endpoint
#. Verifying the webhook and converting it to a RemoteEvent object
#. Consuming the event in your application logic

.. admonition:: Screencast
    :class: screencast

    Like video tutorials? Check out the `Webhook Component for Email Events screencast`_.

A Centralized Webhook Endpoint
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Webhook\\Controller\\WebhookController`
provides a single entry point for receiving all incoming webhooks, regardless
of their source (third-party services, custom APIs, etc.).

By default, any URL prefixed with ``/webhook`` routes to this controller. You
can customize this prefix in your routing configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/routes/webhook.yaml
        webhook:
            resource: '@FrameworkBundle/Resources/config/routing/webhook.php'
            prefix: /webhook  # customize as needed

    .. code-block:: php

        // config/routes/webhook.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return static function (RoutingConfigurator $routes): void {
            $routes->import('@FrameworkBundle/Resources/config/routing/webhook.php')
                ->prefix('/webhook');
        };

Next, configure the parser services that will handle incoming webhooks. The
controller uses a routing mechanism to map incoming requests to the appropriate parser:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/webhook.yaml
        framework:
            webhook:
                routing:
                    acme_webhook:  # routing name, maps to /webhook/acme_webhook
                        service: App\Webhook\AcmeWebhookRequestParser
                        secret: '%env(WEBHOOK_SECRET)%'  # optional

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $config): void {
            $config->webhook()
                ->routing('acme_webhook')
                ->service('App\Webhook\AcmeWebhookRequestParser')
                ->secret('%env(WEBHOOK_SECRET)%');
        };

The routing name becomes part of the webhook URL (e.g.,
``https://example.com/webhook/acme_webhook``). Each routing name must be
unique as it connects the webhook source to your consumer code.

All parsers are automatically injected into the WebhookController.

Parsing Webhook Requests
~~~~~~~~~~~~~~~~~~~~~~~~

Once a webhook request arrives at your endpoint, it must be parsed and validated
before your application can process it. Parsing involves verifying the request's
authenticity (typically via signature validation), extracting the payload, and
converting it into a :class:`Symfony\\Component\\RemoteEvent\\RemoteEvent` object.

Symfony provides two approaches to handle parsing:

* **Built-in parser**: use the standard
  :class:`Symfony\\Component\\Webhook\\Client\\RequestParser` for webhooks
  from other Symfony applications;
* **Custom parser**: create your own parser for webhooks from third-party
  services or custom APIs.

Using the Built-in Parser
.........................

For webhooks originating from other Symfony applications, you can use the
built-in :class:`Symfony\\Component\\Webhook\\Client\\RequestParser` instead
of creating a custom parser. This parser handles the standard Symfony webhook
request format:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            webhook:
                routing:
                    acme_webhook:
                        service: Symfony\Component\Webhook\Client\RequestParser
                        secret: '%env(WEBHOOK_SECRET)%'

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $config): void {
            $config->webhook()
                ->routing('acme_webhook')
                ->service(Symfony\Component\Webhook\Client\RequestParser::class)
                ->secret('%env(WEBHOOK_SECRET)%');
        };

The built-in parser automatically handles request validation and signature verification,
allowing you to focus on consuming the RemoteEvent in your application logic.

Creating a Custom Parser
........................

For webhooks from custom APIs, implement a parser using
:class:`Symfony\\Component\\Webhook\\Client\\RequestParserInterface` or
extend :class:`Symfony\\Component\\Webhook\\Client\\AbstractRequestParser`.

The easiest way is using the maker command:

.. code-block:: terminal

    $ php bin/console make:webhook

.. tip::

    The ``make:webhook`` command generates both the parser and consumer classes
    and updates your configuration automatically.

When extending :class:`Symfony\\Component\\Webhook\\Client\\AbstractRequestParser`,
you need to implement two methods:

* :method:`Symfony\\Component\\Webhook\\Client\\AbstractRequestParser::getRequestMatcher`
  to validate the incoming request format;
* :method:`Symfony\\Component\\Webhook\\Client\\AbstractRequestParser::doParse`
  to verify the webhook and parse it into a RemoteEvent.

::

    // src/Webhook/AcmeWebhookRequestParser.php
    namespace App\Webhook;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestMatcher\ChainRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcher\RequestMatcherInterface;
    use Symfony\Component\RemoteEvent\RemoteEvent;
    use Symfony\Component\Webhook\Client\AbstractRequestParser;

    final class AcmeWebhookRequestParser extends AbstractRequestParser
    {
        protected function getRequestMatcher(): RequestMatcherInterface
        {
            return new ChainRequestMatcher([
                new IsJsonRequestMatcher(),
                new MethodRequestMatcher('POST'),
            ]);
        }

        protected function doParse(
            Request $request,
            #[\SensitiveParameter] string $secret
        ): ?RemoteEvent {
            $payload = $request->toArray();

            return new RemoteEvent(
                $payload['event_type'],
                $payload['event_id'],
                $payload,
            );
        }
    }

The ``doParse()`` method receives the request and the secret. You should:

* Validate the request signature (typically HMAC-SHA256)
* Parse and validate the payload
* Throw a :class:`Symfony\\Component\\Webhook\\Exception\\RejectWebhookException`
  for invalid requests
* Return a :class:`Symfony\\Component\\RemoteEvent\\RemoteEvent` on success

Testing Your Parser
...................

Test your custom parser by extending :class:`Symfony\\Component\\Webhook\\Test\\AbstractRequestParserTestCase`.
This base class runs :method:`Symfony\\Component\\Webhook\\Test\\AbstractRequestParserTestCase::testParse`
with data from :method:`Symfony\\Component\\Webhook\\Test\\AbstractRequestParserTestCase::getPayloads`,
which loads files from ``Fixtures/*.json`` and pairs each with a ``.php`` expectation file::

    // tests/Webhook/AcmeWebhookRequestParserTest.php
    namespace App\Tests\Webhook;

    use App\Webhook\AcmeWebhookRequestParser;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Webhook\Test\AbstractRequestParserTestCase;

    class AcmeWebhookRequestParserTest extends AbstractRequestParserTestCase
    {
        protected function createRequestParser(): AcmeWebhookRequestParser
        {
            return new AcmeWebhookRequestParser();
        }

        // default createRequest() builds a POST request with Content-Type: application/json
        // override it to add provider-specific headers (e.g., webhook signatures) or change the method
        protected function createRequest(string $payload): Request
        {
            return Request::create('/', 'POST', [], [], [], // the routing is not actually tested
                [
                    'CONTENT_TYPE' => 'application/json', // add headers as needed
                ],
                $payload
            );
        }
    }

Create the fixture files that the base test expects (e.g. in
``tests/Webhook/Fixtures/resource.created.json``):

.. code-block:: json

    {
        "event_type": "resource.created",
        "event_id": "550e8400-e29b-41d4-a716-446655440000",
        "email": "user@example.com"
    }

and::

    // tests/Webhook/Fixtures/resource.created.php
    use Symfony\Component\RemoteEvent\RemoteEvent;

    return new RemoteEvent(
        name: 'resource.created',
        id: '550e8400-e29b-41d4-a716-446655440000',
        payload: [
            'event_type' => 'resource.created',
            'event_id' => '550e8400-e29b-41d4-a716-446655440000',
            'email' => 'user@example.com',
        ]
    );

Your test must implement :method:`Symfony\\Component\\Webhook\\Test\\AbstractRequestParserTestCase::createRequestParser`
to return an instance of your :class:`Symfony\\Component\\Webhook\\Client\\RequestParserInterface`
implementation.

You can also override the following methods in your test:

* :method:`Symfony\\Component\\Webhook\\Test\\AbstractRequestParserTestCase::getSecret`
  if your parser validates signatures
* :method:`Symfony\\Component\\Webhook\\Test\\AbstractRequestParserTestCase::getFixtureExtension`
  if your fixtures are not ``.json`` (e.g., ``.txt`` for form-encoded payloads)

Handling Complex Payload Transformations
........................................

For complex webhook payloads, use
:class:`Symfony\\Component\\RemoteEvent\\PayloadConverterInterface` to
encapsulate transformation logic::

    // src/RemoteEvent/AcmeWebhookPayloadConverter.php
    namespace App\RemoteEvent;

    use Symfony\Component\RemoteEvent\PayloadConverterInterface;
    use Symfony\Component\RemoteEvent\RemoteEvent;

    final class AcmeWebhookPayloadConverter implements PayloadConverterInterface
    {
        public function convert(array $payload): RemoteEvent
        {
            // map external event names to your domain events
            $eventName = match ($payload['event_type']) {
                'resource.created' => 'acme.resource_created',
                'resource.updated' => 'acme.resource_updated',
                'resource.deleted' => 'acme.resource_deleted',
                default => 'acme.unknown_event',
            };

            return new RemoteEvent($eventName, $payload['event_id'], $payload);
        }
    }

Then inject it into your parser::

    // src/Webhook/AcmeWebhookRequestParser.php
    namespace App\Webhook;

    use App\RemoteEvent\AcmeWebhookPayloadConverter;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\RemoteEvent\PayloadConverterInterface;
    use Symfony\Component\RemoteEvent\RemoteEvent;
    use Symfony\Component\Webhook\Client\AbstractRequestParser;
    use Symfony\Component\Webhook\Exception\RejectWebhookException;
    use Symfony\DependencyInjection\Attribute\Autowire;

    final class AcmeWebhookRequestParser extends AbstractRequestParser
    {
        public function __construct(
            #[Autowire(service: AcmeWebhookPayloadConverter::class)]
            private readonly PayloadConverterInterface $converter,
        ) {
        }

        // ... getRequestMatcher() as before

        protected function doParse(
            Request $request,
            #[\SensitiveParameter] string $secret,
        ): ?RemoteEvent {
            try {
                return $this->converter->convert($request->toArray());
            } catch (ParseException|\JsonException $e) {
                throw new RejectWebhookException(406, $e->getMessage(), $e);
            }
        }
    }

.. tip::

    For inspiration, look at the built-in
    :class:`Symfony\\Component\\Mailer\\Bridge\\Mailgun\\RemoteEvent\\MailgunPayloadConverter`.

Consuming the RemoteEvent
~~~~~~~~~~~~~~~~~~~~~~~~~

Whether processed synchronously or asynchronously (via Messenger), you need
a consumer implementing
:class:`Symfony\\Component\\RemoteEvent\\Consumer\\ConsumerInterface`.

The ``make:webhook`` command generates one automatically. Otherwise, create
it manually using the
:class:`Symfony\\Component\\RemoteEvent\\Attribute\\AsRemoteEventConsumer`
attribute::

    // src/RemoteEvent/AcmeWebhookConsumer.php
    namespace App\RemoteEvent;

    use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
    use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
    use Symfony\Component\RemoteEvent\RemoteEvent;

    #[AsRemoteEventConsumer('acme_webhook')]  // must match routing name
    final class AcmeWebhookConsumer implements ConsumerInterface
    {
        public function consume(RemoteEvent $event): void
        {
            // handle the event based on your business logic
        }
    }

The name passed to the ``AsRemoteEventConsumer`` attribute must match the
routing name defined in your webhook configuration.

Asynchronous Consuming
......................

By default, webhook consumers are invoked synchronously when the RemoteEvent
is dispatched. To process webhooks asynchronously, configure Messenger routing
for :class:`Symfony\\Component\\RemoteEvent\\Messenger\\ConsumeRemoteEventMessage`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                routing:
                    'Symfony\Component\RemoteEvent\Messenger\ConsumeRemoteEventMessage': async

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Component\RemoteEvent\Messenger\ConsumeRemoteEventMessage;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $config): void {
            $config->messenger()
                ->routing(ConsumeRemoteEventMessage::class)
                ->senders(['async']);
        };

With this configuration, consumers are invoked asynchronously via the
message bus. Without it, consumers are processed synchronously during
the webhook request.

Built-in Integrations
~~~~~~~~~~~~~~~~~~~~~

Symfony provides pre-built parsers for common services, so you don't need to
create custom parsers for them. You still need to create your own consumer
to handle the RemoteEvent according to your business logic.

Mailer Webhooks
...............

Receive delivery and engagement notifications from third-party mailers:

==============  ============================================
Mailer Service  Parser service name
==============  ============================================
AhaSend         ``mailer.webhook.request_parser.ahasend``
Brevo           ``mailer.webhook.request_parser.brevo``
Mandrill        ``mailer.webhook.request_parser.mailchimp``
MailerSend      ``mailer.webhook.request_parser.mailersend``
Mailgun         ``mailer.webhook.request_parser.mailgun``
Mailjet         ``mailer.webhook.request_parser.mailjet``
Mailomat        ``mailer.webhook.request_parser.mailomat``
Mailtrap        ``mailer.webhook.request_parser.mailtrap``
Postmark        ``mailer.webhook.request_parser.postmark``
Resend          ``mailer.webhook.request_parser.resend``
Sendgrid        ``mailer.webhook.request_parser.sendgrid``
Sweego          ``mailer.webhook.request_parser.sweego``
==============  ============================================

.. note::

    Install the third-party mailer provider you want to use as described in
    the documentation of the :ref:`Mailer component <mailer_3rd_party_transport>`.
    Mailgun is used as the provider in this document as an example.

Configure the routing:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            webhook:
                routing:
                    mailer_mailgun:
                        service: 'mailer.webhook.request_parser.mailgun'
                        secret: '%env(MAILER_MAILGUN_SECRET)%'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'webhook' => [
                    'routing' => [
                        'mailer_mailgun' => [
                            'service' => 'mailer.webhook.request_parser.mailgun',
                            'secret' => env('MAILER_MAILGUN_SECRET'),
                        ],
                    ],
                ],
            ],
        ]);

The routing name becomes part of your webhook URL (e.g.,
``https://example.com/webhook/mailer_mailgun``). Configure this URL at your
mailer provider and store the webhook secret in your environment (via the
:doc:`secrets management system </configuration/secrets>` or in a ``.env`` file).

Then create a consumer to handle delivery and engagement events::

    // src/RemoteEvent/MailerWebhookConsumer.php
    namespace App\RemoteEvent;

    use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
    use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
    use Symfony\Component\RemoteEvent\Event\Mailer\MailerDeliveryEvent;
    use Symfony\Component\RemoteEvent\Event\Mailer\MailerEngagementEvent;
    use Symfony\Component\RemoteEvent\RemoteEvent;

    #[AsRemoteEventConsumer('mailer_mailgun')]
    final class MailerWebhookConsumer implements ConsumerInterface
    {
        public function consume(RemoteEvent $event): void
        {
            if ($event instanceof MailerDeliveryEvent) {
                $this->handleDelivery($event);
            } elseif ($event instanceof MailerEngagementEvent) {
                $this->handleEngagement($event);
            }
        }

        private function handleDelivery(MailerDeliveryEvent $event): void
        {
            // Update message status in database, log delivery, etc.
        }

        private function handleEngagement(MailerEngagementEvent $event): void
        {
            // Handle opens, clicks, bounces, etc.
        }
    }

Notifier Webhooks
.................

Receive SMS status notifications from providers:

============  ==========================================
SMS service   Parser service name
============  ==========================================
LOX24         ``notifier.webhook.request_parser.lox24``
Smsbox        ``notifier.webhook.request_parser.smsbox``
Sweego        ``notifier.webhook.request_parser.sweego``
Twilio        ``notifier.webhook.request_parser.twilio``
Vonage        ``notifier.webhook.request_parser.vonage``
============  ==========================================

Configure similarly to mailers, then consume
:class:`Symfony\\Component\\RemoteEvent\\Event\\Sms\\SmsEvent`::

    // src/RemoteEvent/SmsWebhookConsumer.php
    namespace App\RemoteEvent;

    use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
    use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
    use Symfony\Component\RemoteEvent\Event\Sms\SmsEvent;
    use Symfony\Component\RemoteEvent\RemoteEvent;

    #[AsRemoteEventConsumer('notifier_twilio')]
    final class SmsWebhookConsumer implements ConsumerInterface
    {
        public function consume(RemoteEvent $event): void
        {
            if ($event instanceof SmsEvent) {
                $this->handleSms($event);
            }
        }

        private function handleSms(SmsEvent $event): void
        {
            // Update SMS delivery status in database, etc.
        }
    }

Sending Webhooks
----------------

The Webhook component also enables your application to dispatch webhook
callbacks to remote endpoints. This is useful when building APIs that notify
subscribers of important events.

To send webhooks, ensure you have installed both the HttpClient and Serializer components:

.. code-block:: terminal

    $ composer require symfony/http-client symfony/serializer

Basic Usage
~~~~~~~~~~~

To send a webhook, dispatch a
:class:`Symfony\\Component\\Webhook\\Messenger\\SendWebhookMessage` via
the Messenger component::

    use Symfony\Component\Messenger\MessageBusInterface;
    use Symfony\Component\RemoteEvent\RemoteEvent;
    use Symfony\Component\Webhook\Messenger\SendWebhookMessage;
    use Symfony\Component\Webhook\Subscriber;

    class StockNotifier
    {
        public function __construct(
            private readonly MessageBusInterface $messageBus,
        ) {
        }

        public function notifyOutOfStock(int $productId): void
        {
            $subscriber = new Subscriber(
                url: 'https://example.com/webhook/stock',
                secret: 'your-shared-secret',
            );

            $event = new RemoteEvent(
                name: 'resource.created',
                id: '550e8400-e29b-41d4-a716-446655440000',
                payload: [
                    'resource_id' => 12345,
                    'email' => 'user@example.com',
                    'created_at' => time(),
                ]
            );

            $this->messageBus->dispatch(
                new SendWebhookMessage($subscriber, $event)
            );
        }
    }

The message is processed by
:class:`Symfony\\Component\\Webhook\\Messenger\\SendWebhookHandler`, which:

#. Constructs the HTTP request body (JSON-encoded payload)
#. Adds standard headers: ``Webhook-Event`` (event name), ``Webhook-Id``
   (event ID), ``Webhook-Signature`` (HMAC-SHA256 signature of the concatenated
   event name, ID, and body), and ``Content-Type: application/json``
#. Signs the request using the subscriber's secret
#. Sends the HTTP request using the Symfony HttpClient component

Resulting HTTP Request
~~~~~~~~~~~~~~~~~~~~~~

When the webhook is sent, it generates an HTTP POST request with the following format:

.. code-block:: text

    POST /webhook/stock HTTP/1.1
    Host: example.com
    Content-Type: application/json
    Webhook-Event: resource.created
    Webhook-Id: 550e8400-e29b-41d4-a716-446655440000
    Webhook-Signature: sha256=9f86d081884c7d6d9ffd60bb51d3263112c4b2486f80fa12ab5807265dc789d6

    {
        "resource_id": 12345,
        "email": "user@example.com",
        "created_at": 1234567890
    }

By default, the signature uses HMAC-SHA256 of the concatenated event name,
event ID, and JSON body. Receiving endpoints should verify this signature
using the shared secret to ensure webhook authenticity.

Custom Sending Logic
~~~~~~~~~~~~~~~~~~~~

For advanced use cases, you can implement custom sending logic using
:class:`Symfony\\Component\\Webhook\\Server\\TransportInterface` to control
header generation, signing, and HTTP transport.

.. _`Webhook Component for Email Events screencast`: https://symfonycasts.com/screencast/mailtrap/email-event-webhook
