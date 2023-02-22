Webhook
=======

.. versionadded:: 6.3

    The Webhook component was introduced in Symfony 6.3 and is marked
    as experimental.

The Webhook component aims at easing the setup of callback functions
through HTTP in your application.

Webhooks allow third-party services to communicate with your application
without you having to make a request to the third-party application.
By providing a webhook URL to the remote service, it will be able to
send a request to your URL when a predefined event occurs in their system.
Unlike an API, it is the third-party service that defines the payload that
will be sent to your own endpoint. It is therefore up to you to adapt to
the service to handle such payload accordingly.

As an example of use case, some mail providers set up a service allowing
you to retrieve the status of one or more emails you have sent
and thus know if they have been delivered or not.
These services then use your webhook to send you this
information once they have it, so that you can store this information
and process it on your side.

Installation
------------

You can install the Webhook component with:

.. code-block:: terminal

    $ composer require symfony/webhook

.. include:: /components/require_autoload.rst.inc

Basic Usage
-----------

Parse The Incoming Request
~~~~~~~~~~~~~~~~~~~~~~~~~~

Each type of webhook has its own parser. A parser is a service class that has
the ability to verify that a request meets certain preconditions to be
processed. If so, it returns an event to be handled in your application.
Here is an example of a basic parser::

    namespace App\Webhook;

    use Symfony\Component\HttpFoundation\ChainRequestMatcher;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcherInterface;
    use Symfony\Component\RemoteEvent\Exception\ParseException;
    use Symfony\Component\Webhook\Client\AbstractRequestParser;
    use Symfony\Component\Webhook\Exception\RejectWebhookException;

    final class MailerWebhookParser extends AbstractRequestParser
    {
        protected function getRequestMatcher(): RequestMatcherInterface
        {
            return new ChainRequestMatcher([
                new MethodRequestMatcher('POST'),
                new IsJsonRequestMatcher(),
            ]);
        }

        protected function doParse(Request $request, string $secret): ?RemoteEvent
        {
            $content = $request->toArray();
            if (!isset($content['signature']['token'])) {
                throw new RejectWebhookException(406, 'Payload is malformed.');
            }

            // you can also do some checks on $secret to ensure the authenticity
            // of the request

            return new RemoteEvent('mailer_callback.event', 'event-id', $content);
        }
    }

.. tip::

    If you need more flexibility, you can also create your parser by
    implementing the
    :class:`Symfony\\Component\\Webhook\\Client\\RequestParserInterface`
    interface.

The Webhook component handles webhooks by type. Each webhook type is handled
by a request parser like the one in the previous example. The component defines
the ``/webhook/{type}`` URL. The routing must be done like this:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            webhook:
                routing:
                    my_mailer_webhook:
                        service: App\Webhook\MailerWebhookParser
                        secret: 'some secret' # this secret is usually shared with the provider to authenticate the request

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
                    <framework:routing>
                        <framework:service>App\Webhook\MailerWebhookParser</framework:service>
                        <framework:secret>some secret</framework:secret>
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
                ->routing('my_mailer_webhook')
                ->service(MailerWebhookParser::class)
                ->secret('some secret')
            ;
        };

All requests made to ``/webhook/my_mailer_webhook`` will now be handled by
the ``MailerWebhookParser`` request parser.

A few parsers are already bundled in the component. They allow you to
quickly parse requests from some third-party providers:

- :class:`Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Webhook\\MailgunRequestParser`
- :class:`Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Webhook\\PostmarkRequestParser`
- :class:`Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Webhook\\TwilioRequestParser`

.. note::

    When using the Webhook component in a framework context, these classes
    are declared as services and can also be accessed through their aliases, for
    example ``mailer.webhook.request_parser.mailgun``.

If the webhook request matches all preconditions of the request parser, the
``doParse()`` method is executed and returns a
:class:`Symfony\\Component\\RemoteEvent\\RemoteEvent`.

.. _webhook_consume-remote-events:

Consume Remote Events
~~~~~~~~~~~~~~~~~~~~~

Once a remote event has been created and returned by the request parser, it has
to be consumed by a **remote event consumer**. A remote event consumer can be
declared by implementing the
:class:`Symfony\\Component\\RemoteEvent\\Consumer\\ConsumerInterface` and by
using the
:class:`Symfony\\Component\\RemoteEvent\\Attribute\\AsRemoteEventConsumer` attribute.
You will be able to pass the remote event name to consume in the attribute.
The consumer must implement the ``consume()`` method::

    use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
    use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
    use Symfony\Component\RemoteEvent\RemoteEvent;

    #[AsRemoteEventConsumer(name: 'my_mailer_webhook')]
    class MailerCallbackEventConsumer implements ConsumerInterface
    {
        public function consume(RemoteEvent $event): void
        {
            // Process the event returned by your parser
        }
    }

You can then create your own logic in the ``consume()`` method.

Handle Complex Payloads
-----------------------

When you set up a webhook, there is a chance that the payload you
receive will contain a lot of information. It may then be necessary
to move the logic of transforming the payload into a remote event in
another service. The Webhook component provides an interface for this:
the :class:`Symfony\\Component\\RemoteEvent\\PayloadConverterInterface`.
This allows to create a converter that will be used in the
request parser. By coupling the converter with a custom remote event
extending :class:`Symfony\\Component\\RemoteEvent\\RemoteEvent`, you
can pass any type of information to your remote event consumer::

    // src/RemoteEvent/GenericMailerRemoteEvent.php
    namespace App\RemoteEvent;

    use Symfony\Component\RemoteEvent\RemoteEvent;

    // We first create a new remote event specialized in mailer events
    class GenericMailerRemoteEvent extends RemoteEvent
    {
        public const EVENT_NAME = 'mailer.remote_event';

        public function __construct(string $mailId, array $payload, private readonly bool $delivered)
        {
            parent::__construct(self::EVENT_NAME, $mailId, $payload);
        }

        public function isDelivered(): bool
        {
            return $this->delivered;
        }
    }

    // src/RemoteEvent/MailerProviderPayloadConverter.php
    namespace App\RemoteEvent;

    use Symfony\Component\RemoteEvent\Exception\ParseException;
    use Symfony\Component\RemoteEvent\PayloadConverterInterface;

    // This converter transforms a specific provider payload to
    // our generic mailer remote event
    class SpecificMailerProviderPayloadConverter implements PayloadConverterInterface
    {
        public function convert(array $payload): MailerRemoteEvent
        {
            // Payload contains all the information your email provider sends you
            if (!isset($payload['mail_uid'])) {
                throw new ParseException('This payload must contain a mail uid.');
            }

            return new MailerRemoteEvent($payload['mail_uid'], $payload, $payload['delivered'] ?? false);
        }
    }

The ``SpecificMailerProviderPayloadConverter`` can now be injected in our request parser
and be used to return the remote event::

    namespace App\Webhook;

    use App\RemoteEvent\SpecificMailerProviderPayloadConverter;
    use Symfony\Component\HttpFoundation\ChainRequestMatcher;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcherInterface;
    use Symfony\Component\RemoteEvent\Exception\ParseException;
    use Symfony\Component\Webhook\Client\AbstractRequestParser;
    use Symfony\Component\Webhook\Exception\RejectWebhookException;

    final class MailerWebhookParser extends AbstractRequestParser
    {
        public function __construct(
            private readonly SpecificMailerProviderPayloadConverter $converter
        ) {
        }

        protected function getRequestMatcher(): RequestMatcherInterface
        {
            return new ChainRequestMatcher([
                new MethodRequestMatcher('POST'),
                new IsJsonRequestMatcher(),
            ]);
        }

        protected function doParse(Request $request, string $secret): ?RemoteEvent
        {
            $content = $request->toArray();

            try {
                // Use the new converter to create our custom remote event
                return $this->converter->convert($content);
            } catch (ParseException $e) {
                throw new RejectWebhookException(406, $e->getMessage(), $e);
            }
        }
    }

Validate Request Preconditions
------------------------------

By using the :class:`Symfony\\Component\\HttpFoundation\\ChainRequestMatcher`,
it is possible to build a powerful request validation chain to determine
if the request that arrived in your webhook endpoint is valid and can
be processed. The following matchers are available:

:class:`Symfony\\Component\\HttpFoundation\\RequestMatcher\\AttributesRequestMatcher`
    Matches a request's attributes against a regex. For example, if your request
    URL looks like ``/users/{name}``, you may want to check that the ``name``
    attribute only contains alphanumeric characters with a regex like
    ``[a-zA-Z0-9]+``.

:class:`Symfony\\Component\\HttpFoundation\\RequestMatcher\\ExpressionRequestMatcher`
    Matches the request against an expression using the ExpressionLanguage component.

:class:`Symfony\\Component\\HttpFoundation\\RequestMatcher\\HostRequestMatcher`
    Matches the host that sent the request.

:class:`Symfony\\Component\\HttpFoundation\\RequestMatcher\\IpsRequestMatcher`
    Matches an IP or a set of IPs from where the request comes from.

:class:`Symfony\\Component\\HttpFoundation\\RequestMatcher\\IsJsonRequestMatcher`
    Matches the request content is a valid JSON.

:class:`Symfony\\Component\\HttpFoundation\\RequestMatcher\\MethodRequestMatcher`
    Matches the request uses a given HTTP method.

:class:`Symfony\\Component\\HttpFoundation\\RequestMatcher\\PathRequestMatcher`
    Matches the request path.

:class:`Symfony\\Component\\HttpFoundation\\RequestMatcher\\PortRequestMatcher`
    Matches the request port.

:class:`Symfony\\Component\\HttpFoundation\\RequestMatcher\\SchemeRequestMatcher`
    Matches the request scheme.

Combining multiple request matchers allows to precisely determine if the
webhook is a legitimate request from a known host when combined with
a signature request mechanism::

    namespace App\Webhook;

    use Symfony\Component\HttpFoundation\ChainRequestMatcher;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestMatcher\IpsRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcher\SchemeRequestMatcher;
    use Symfony\Component\HttpFoundation\RequestMatcherInterface;
    use Symfony\Component\RemoteEvent\RemoteEvent;
    use Symfony\Component\Webhook\Client\AbstractRequestParser;

    final class MailerWebhookParser extends AbstractRequestParser
    {
        protected function getRequestMatcher(): RequestMatcherInterface
        {
            return new ChainRequestMatcher([
                new MethodRequestMatcher('POST'),
                new IsJsonRequestMatcher(),
                new IpsRequestMatcher(/** A set of known IPs given by a provider */),
                new SchemeRequestMatcher('https'),
            ]);
        }

        protected function doParse(Request $request, string $secret): ?RemoteEvent
        {
            // Verify the signature contained in the request thanks to the
            // secret, then return a RemoteEvent
        }
    }
