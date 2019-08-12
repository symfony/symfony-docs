.. index::
   single: Mailer
   single: Components; Mailer

The Mailer Component
====================

    The Mailer component helps sending emails.

If you're using the Symfony Framework, read the
:doc:`Symfony Framework Mailer documentation </mailer>`.

.. versionadded:: 4.3

    The Mailer component was introduced in Symfony 4.3 and it's still
    considered an :doc:`experimental feature </contributing/code/experimental>`.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/mailer

.. include:: /components/require_autoload.rst.inc

Usage
-----

The Mailer component has two main classes: a ``Transport`` and the ``Mailer`` itself::

    use Symfony\Component\Mailer\Mailer;
    use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

    $transport = new SmtpTransport('localhost');
    $mailer = new Mailer($transport);
    $mailer->send($email);

The ``$email`` object is created via the :doc:`Mime component </components/mime>`.

Transport
---------

The only transport that comes pre-installed is SMTP.

Below is the list of other popular providers with built-in support:

==================  =============================================
Service             Install with
==================  =============================================
Amazon SES          ``composer require symfony/amazon-mailer``
Gmail               ``composer require symfony/google-mailer``
MailChimp           ``composer require symfony/mailchimp-mailer``
Mailgun             ``composer require symfony/mailgun-mailer``
Postmark            ``composer require symfony/postmark-mailer``
SendGrid            ``composer require symfony/sendgrid-mailer``
==================  =============================================

For example, suppose you want to use Google's Gmail SMTP server. First, install
it:

.. code-block:: terminal

    $ composer require symfony/google-mailer

Then, use the SMTP Gmail transport::

    use Symfony\Component\Mailer\Bridge\Google\Smtp\GmailTransport;

    $transport = new GmailTransport('user', 'pass');
    $mailer = new Mailer($transport);
    $mailer->send($email);

Each provider provides up to 3 transports: standard SMTP, HTTP (it uses the
provider's API but the body is created by the mailer component), API (it uses
the full API of the provider with no control over the body creation -- features
might be limited as well).

.. _mailer_dsn:

The mailer component provides a convenient way to create a transport from a
DSN::

    use Symfony\Component\Mailer\Transport;

    $transport = Transport::fromDsn($dsn);

Where ``$dsn`` depends on the provider you want to use. For plain SMTP, use
``smtp://user:pass@example.com`` or ``smtp://sendmail`` to use the ``sendmail``
binary. For third-party providers, refers to the following table:

==================== ================================== ================================== ================================
 Provider             SMTP                               HTTP                               API
==================== ================================== ================================== ================================
 Amazon SES           smtp://ACCESS_KEY:SECRET_KEY@ses   http://ACCESS_KEY:SECRET_KEY@ses   api://ACCESS_KEY:SECRET_KEY@ses
 Google Gmail         smtp://USERNAME:PASSWORD@gmail     n/a                                n/a
 Mailchimp Mandrill   smtp://USERNAME:PASSWORD@mandrill  http://KEY@mandrill                api://KEY@mandrill
 Mailgun              smtp://USERNAME:PASSWORD@mailgun   http://KEY:DOMAIN@mailgun          api://KEY:DOMAIN@mailgun
 Postmark             smtp://ID:ID@postmark              n/a                                api://KEY@postmark
 Sendgrid             smtp://apikey:KEY@sendgrid         n/a                                api://KEY@sendgrid
==================== ================================== ================================== ================================

Failover Transport
------------------

You can create failover transport with the help of `||` operator::

    $dsn = 'api://id@postmark || smtp://key@sendgrid';

So if the first transport fails, the mailer will attempt to send through the
second transport.

Round Robin
-----------

If you want to send emails by using multiple transports in a round-robin fashion,
you can use the ``&&`` operator between the transports::

    $dsn = 'api://id@postmark && smtp://key@sendgrid'

Sending emails asynchronously
-----------------------------

If you want to send emails asynchronously, install the :doc:`Messenger component
</components/messenger>`.

.. code-block:: terminal

    $ composer require symfony/messenger

Then, instantiate and pass a ``MessageBus`` as a second argument to ``Mailer``::

    use Symfony\Component\Mailer\Mailer;
    use Symfony\Component\Mailer\Messenger\MessageHandler;
    use Symfony\Component\Mailer\Messenger\SendEmailMessage;
    use Symfony\Component\Mailer\SmtpEnvelope;
    use Symfony\Component\Mailer\Transport;
    use Symfony\Component\Messenger\Handler\HandlersLocator;
    use Symfony\Component\Messenger\MessageBus;
    use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
    use Symfony\Component\Mime\Address;

    $dsn = 'change-dsn-accordingly';

    $transport = Transport::fromDsn($dsn);
    $handler = new MessageHandler($transport);

    $bus = new MessageBus([
        new HandleMessageMiddleware(new HandlersLocator([
            SendEmailMessage::class => [$handler],
        ])),
    ]);

    $mailer = new Mailer($transport, $bus);
    $mailer->send($email);

    // you can pass an optional Envelope
    $mailer->send($email, new SmtpEnvelope(
        new Address('sender@example.com'),
        [
            new Address('recipient@example.com'),
        ]
    ));

Learn More
-----------

To learn more about how to use the mailer component, refer to the
:doc:`Symfony Framework Mailer documentation </mailer>`.
