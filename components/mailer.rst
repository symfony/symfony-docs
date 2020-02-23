.. index::
   single: Mailer
   single: Components; Mailer

The Mailer Component
====================

    The Mailer component helps sending emails.

If you're using the Symfony Framework, read the
:doc:`Symfony Framework Mailer documentation </mailer>`.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/mailer

.. include:: /components/require_autoload.rst.inc

Usage
-----

The Mailer component has two main classes: a ``Transport`` and the ``Mailer`` itself::

    use Symfony\Component\Mailer\Mailer;
    use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

    $transport = new EsmtpTransport('localhost');
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

    use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
    use Symfony\Component\Mailer\Mailer;

    $transport = new GmailSmtpTransport('user', 'pass');
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
``smtp://user:pass@example.com`` or ``sendmail+smtp://default`` to use the
``sendmail`` binary. To disable the transport, use ``null://null``.

For third-party providers, refer to the following table:

==================== ========================================== =========================================== ========================================
 Provider             SMTP                                       HTTP                                        API
==================== ========================================== =========================================== ========================================
 Amazon SES           ses+smtp://ACCESS_KEY:SECRET_KEY@default   ses+https://ACCESS_KEY:SECRET_KEY@default   ses+api://ACCESS_KEY:SECRET_KEY@default
 Google Gmail         gmail+smtp://USERNAME:PASSWORD@default     n/a                                         n/a
 Mailchimp Mandrill   mandrill+smtp://USERNAME:PASSWORD@default  mandrill+https://KEY@default                mandrill+api://KEY@default
 Mailgun              mailgun+smtp://USERNAME:PASSWORD@default   mailgun+https://KEY:DOMAIN@default          mailgun+api://KEY:DOMAIN@default
 Postmark             postmark+smtp://ID:ID@default              n/a                                         postmark+api://KEY@default
 Sendgrid             sendgrid+smtp://apikey:KEY@default         n/a                                         sendgrid+api://KEY@default
==================== ========================================== =========================================== ========================================

.. note::

    When using SMTP, the default timeout for sending a message before throwing an
    exception is the value defined in the `default_socket_timeout`_ PHP.ini option.

    .. versionadded:: 5.1

        The usage of ``default_socket_timeout`` as the default timeout was
        introduced in Symfony 5.1.

Instead of choosing a specific protocol, you can also let Symfony pick the
best one by omitting it from the scheme: for instance, ``mailgun://KEY:DOMAIN@default``
is equivalent to ``mailgun+https://KEY:DOMAIN@default``.

If you want to override the default host for a provider (to debug an issue using
a service like ``requestbin.com``), change ``default`` by your host:

.. code-block:: bash

    mailgun+https://KEY:DOMAIN@example.com
    mailgun+https://KEY:DOMAIN@example.com:99

Note that the protocol is *always* HTTPs and cannot be changed.

High Availability
-----------------

Symfony's mailer supports `high availability`_ via a technique called "failover"
to ensure that emails are sent even if one mailer server fails .

A failover transport is configured with two or more transports and the
``failover`` keyword::

    $dsn = 'failover(postmark+api://ID@default sendgrid+smtp://KEY@default)';

The mailer will start using the first transport. If the sending fails, the
mailer won't retry it with the other transports, but it will switch to the next
transport automatically for the following deliveries.

Load Balancing
--------------

Symfony's mailer supports `load balancing`_ via a technique called "round-robin"
to distribute the mailing workload across multiple transports .

A round-robin transport is configured with two or more transports and the
``roundrobin`` keyword::

    $dsn = 'roundrobin(postmark+api://ID@default sendgrid+smtp://KEY@default)'

The mailer will start using a randomly selected transport and if it fails, it
will retry the same delivery with the next transports until one of them succeeds
(or until all of them fail).

.. versionadded:: 5.1

    The random selection of the first transport was introduced in Symfony 5.1.
    In previous Symfony versions the first transport was always selected first.

TLS Peer Verification
---------------------

By default, SMTP transports perform TLS peer verification. This behavior is
configurable with the ``verify_peer`` option. Although it's not recommended to
disable this verification for security reasons, it can be useful while developing
the application or when using a self-signed certificate::

    $dsn = 'smtp://user:pass@smtp.example.com?verify_peer=false'

.. versionadded:: 5.1

    The ``verify_peer`` option was introduced in Symfony 5.1.

Sending emails asynchronously
-----------------------------

If you want to send emails asynchronously, install the :doc:`Messenger component
</components/messenger>`.

.. code-block:: terminal

    $ composer require symfony/messenger

Then, instantiate and pass a ``MessageBus`` as a second argument to ``Mailer``::

    use Symfony\Component\Mailer\Envelope;
    use Symfony\Component\Mailer\Mailer;
    use Symfony\Component\Mailer\Messenger\MessageHandler;
    use Symfony\Component\Mailer\Messenger\SendEmailMessage;
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
    $mailer->send($email, new Envelope(
        new Address('sender@example.com'),
        [
            new Address('recipient@example.com'),
        ]
    ));

Learn More
-----------

To learn more about how to use the mailer component, refer to the
:doc:`Symfony Framework Mailer documentation </mailer>`.

.. _`high availability`: https://en.wikipedia.org/wiki/High_availability
.. _`load balancing`: https://en.wikipedia.org/wiki/Load_balancing_(computing)
.. _`default_socket_timeout`: https://www.php.net/manual/en/filesystem.configuration.php#ini.default-socket-timeout
