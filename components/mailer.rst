.. index::
   single: Mailer
   single: Components; Mailer

The Mailer Component
====================

    The Mailer component helps sending emails.

.. versionadded:: 4.3

    The Mailer component was introduced in Symfony 4.3 and it's still
    considered an :doc:`experimental feature </contributing/code/experimental>`.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/mailer

.. include:: /components/require_autoload.rst.inc


Introduction
------------


Usage
-----

    use Symfony\Component\Mailer\Mailer;
    use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

    $transport = new SmtpTransport('localhost');
    $mailer = new Mailer($transport);
    $mailer->send($email);

Refer :doc:`Mime component </components/mime>` how to create `$email` object.


Transport
---------

By default, the only transport available in the mailer component is Smtp.
Below is the list of other popular providers with built in support.
- Amazon SES  : symfony/amazon-mailer
- Google Gmail : symfony/google-mailer
- Mandrill : symfony/mailchimp-mailer
- Mailgun : symfony/mailgun-mailer
- Postmark : symfony/postmark-mailer
- Sendgrid : symfony/sendgrid-mailer

For example to use google's gmail as a transport you need to install symfony/google-mailer.

.. code-block:: terminal

    $ composer require symfony/google-mailer

.. include:: /components/require_autoload.rst.inc


    use Symfony\Component\Mailer\Bridge\Google\Smtp\GmailTransport;

    $transport = new GmailTransport('user', 'pass');
    $transport->send($email);

Use a Dsn
---------

The mailer component provides a convenient way to create transport object from dsn string.

    use Symfony\Component\Mailer\Transport;

    $transport = Transport::fromDsn($dsn);

Where `$dns` as one of the form below.
- smtp://user:pass@gmail
- smtp://key@sendgrid
- smtp://null
- smtp://user:pass@mailgun
- http://key:domain@mailgun
- api://id@postmark

This provides a unified behaviour across all providers.
Easily switch from SMTP in dev to a "real" provider in production with same API.

Failover transport
------------------

You can create failover transport with the help of `||` operator.

Eg : 

    $dsn = 'api://id@postmark || smtp://key@sendgrid';

So if it fails at one transport, the mailer will attempt to send through the other transport.

RoundRobin
----------

If you want to send mails via multiple transports, you can use the `&&` operator between the transports.

Eg :
    
    $dsn = 'api://id@postmark && smtp://key@sendgrid'


Async
-----

If you want to use the async functionality you need to install `messenger` component.
By default, `$bus` is null and if it is not configured, mailer is always using sync functionality.
And async when `$bus` is configured for EnvelopedMessage.

.. code-block:: terminal

    $ composer require symfony/messenger

.. include:: /components/require_autoload.rst.inc

    use Symfony\Component\Mailer\Mailer;
    use Symfony\Component\Mailer\Messenger\MessageHandler;
    use Symfony\Component\Mailer\Messenger\SendEmailMessage;
    use Symfony\Component\Mailer\SmtpEnvelope;
    use Symfony\Component\Mailer\Transport;
    use Symfony\Component\Messenger\Handler\HandlersLocator;
    use Symfony\Component\Messenger\MessageBus;
    use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
    use Symfony\Component\Mime\Address;
    
    // .. $dsn = 'smtp://null';
    $transport = Transport::fromDsn($dsn);
    $handler = new MessageHandler($transport);

    $bus = new MessageBus([
        new HandleMessageMiddleware(new HandlersLocator([
            SendEmailMessage::class => ['message_hander' => $handler],
        ])),
    ]);

    $mailer = new Mailer($transport, $bus);

    $mailer->send($email, new SmtpEnvelope(
        new Address('sender@example.com'),
        [
            new Address('recepient@example.com'),
        ]
    ));