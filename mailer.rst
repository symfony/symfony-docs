Sending Emails with Mailer
==========================

Installation
------------

Symfony's Mailer & :doc:`Mime </components/mime>` components form a *powerful* system
for creating and sending emails - complete with support for multipart messages, Twig
integration, CSS inlining, file attachments and a lot more. Get them installed with:

.. code-block:: terminal

    $ composer require symfony/mailer

.. _mailer-transport-setup:

Transport Setup
---------------

Emails are delivered via a "transport". Out of the box, you can deliver emails
over SMTP by configuring the DSN in your ``.env`` file (the ``user``,
``pass`` and ``port`` parameters are optional):

.. code-block:: env

    # .env
    MAILER_DSN=smtp://user:pass@smtp.example.com:port

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        framework:
            mailer:
                dsn: '%env(MAILER_DSN)%'

    .. code-block:: xml

        <!-- config/packages/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:mailer dsn="%env(MAILER_DSN)%"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/mailer.php
        use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

        return static function (ContainerConfigurator $containerConfigurator): void {
            $containerConfigurator->extension('framework', [
                'mailer' => [
                    'dsn' => env('MAILER_DSN'),
                ],
            ]);
        };

.. caution::

    If the username, password or host contain any character considered special in a
    URI (such as ``+``, ``@``, ``$``, ``#``, ``/``, ``:``, ``*``, ``!``), you must
    encode them. See `RFC 3986`_ for the full list of reserved characters or use the
    :phpfunction:`urlencode` function to encode them.

Using Built-in Transports
~~~~~~~~~~~~~~~~~~~~~~~~~

============  ========================================  ==============================================================
DSN protocol  Example                                   Description
============  ========================================  ==============================================================
smtp          ``smtp://user:pass@smtp.example.com:25``  Mailer uses an SMTP server to send emails
sendmail      ``sendmail://default``                    Mailer uses the local sendmail binary to send emails
native        ``native://default``                      Mailer uses the sendmail binary and options configured
                                                        in the ``sendmail_path`` setting of ``php.ini``. On Windows
                                                        hosts, Mailer fallbacks to ``smtp`` and ``smtp_port``
                                                        ``php.ini`` settings when ``sendmail_path`` is not configured.
============  ========================================  ==============================================================

.. caution::

    When using ``native://default``, if ``php.ini`` uses the ``sendmail -t``
    command, you won't have error reporting and ``Bcc`` headers won't be removed.
    It's highly recommended to NOT use ``native://default`` as you cannot control
    how sendmail is configured (prefer using ``sendmail://default`` if possible).

Using a 3rd Party Transport
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of using your own SMTP server or sendmail binary, you can send emails
via a third-party provider:

==================  ==============================================
Service             Install with
==================  ==============================================
Amazon SES          ``composer require symfony/amazon-mailer``
MailChimp           ``composer require symfony/mailchimp-mailer``
Mailgun             ``composer require symfony/mailgun-mailer``
Mailjet             ``composer require symfony/mailjet-mailer``
Postmark            ``composer require symfony/postmark-mailer``
SendGrid            ``composer require symfony/sendgrid-mailer``
Sendinblue          ``composer require symfony/sendinblue-mailer``
MailPace            ``composer require symfony/mailpace-mailer``
Infobip             ``composer require symfony/infobip-mailer``
==================  ==============================================

.. versionadded:: 6.2

    The Infobip integration was introduced in Symfony 6.2 and the ``MailPace``
    integration was renamed in Symfony 6.2 (in previous Symfony versions it was
    called ``OhMySMTP``).

.. note::

    As a convenience, Symfony also provides support for Gmail (``composer
    require symfony/google-mailer``), but this should not be used in
    production. In development, you should probably use an :ref:`email catcher
    <mail-catcher>` instead. Note that most supported providers also offer a
    free tier.

Each library includes a :ref:`Symfony Flex recipe <symfony-flex>` that will add
a configuration example to your ``.env`` file. For example, suppose you want to
use SendGrid. First, install it:

.. code-block:: terminal

    $ composer require symfony/sendgrid-mailer

You'll now have a new line in your ``.env`` file that you can uncomment:

.. code-block:: env

    # .env
    MAILER_DSN=sendgrid://KEY@default

The ``MAILER_DSN`` isn't a *real* address: it's a convenient format that
offloads most of the configuration work to mailer. The ``sendgrid`` scheme
activates the SendGrid provider that you just installed, which knows all about
how to deliver messages via SendGrid. The *only* part you need to change is the
``KEY`` placeholder.

Each provider has different environment variables that the Mailer uses to
configure the *actual* protocol, address and authentication for delivery. Some
also have options that can be configured with query parameters at the end of the
``MAILER_DSN`` - like ``?region=`` for Amazon SES or Mailgun. Some providers support
sending via ``http``, ``api`` or ``smtp``. Symfony chooses the best available
transport, but you can force to use one:

.. code-block:: env

    # .env
    # force to use SMTP instead of HTTP (which is the default)
    MAILER_DSN=sendgrid+smtp://$SENDGRID_KEY@default

This table shows the full list of available DSN formats for each third
party provider:

==================== ==================================================== =========================================== ========================================
Provider             SMTP                                                 HTTP                                        API
==================== ==================================================== =========================================== ========================================
Amazon SES           ses+smtp://USERNAME:PASSWORD@default                 ses+https://ACCESS_KEY:SECRET_KEY@default   ses+api://ACCESS_KEY:SECRET_KEY@default
Google Gmail         gmail+smtp://USERNAME:APP-PASSWORD@default           n/a                                         n/a
Mailchimp Mandrill   mandrill+smtp://USERNAME:PASSWORD@default            mandrill+https://KEY@default                mandrill+api://KEY@default
Mailgun              mailgun+smtp://USERNAME:PASSWORD@default             mailgun+https://KEY:DOMAIN@default          mailgun+api://KEY:DOMAIN@default
Mailjet              mailjet+smtp://ACCESS_KEY:SECRET_KEY@default         n/a                                         mailjet+api://ACCESS_KEY:SECRET_KEY@default
MailPace             mailpace+api://API_TOKEN@default                     n/a                                         mailpace+api://API_TOKEN@default
Postmark             postmark+smtp://ID@default                           n/a                                         postmark+api://KEY@default
Sendgrid             sendgrid+smtp://KEY@default                          n/a                                         sendgrid+api://KEY@default
Sendinblue           sendinblue+smtp://USERNAME:PASSWORD@default          n/a                                         sendinblue+api://KEY@default
Infobip              infobip+smtp://KEY@default                           n/a                                         infobip+api://KEY@BASE_URL
==================== ==================================================== =========================================== ========================================

.. caution::

    If your credentials contain special characters, you must URL-encode them.
    For example, the DSN ``ses+smtp://ABC1234:abc+12/345@default`` should be
    configured as ``ses+smtp://ABC1234:abc%2B12%2F345@default``

.. caution::

    If you want to use the ``ses+smtp`` transport together with :doc:`Messenger </messenger>`
    to :ref:`send messages in background <mailer-sending-messages-async>`,
    you need to add the ``ping_threshold`` parameter to your ``MAILER_DSN`` with
    a value lower than ``10``: ``ses+smtp://USERNAME:PASSWORD@default?ping_threshold=9``

.. note::

    When using SMTP, the default timeout for sending a message before throwing an
    exception is the value defined in the `default_socket_timeout`_ PHP.ini option.

.. note::

    To use Google Gmail, you must have a Google Account with 2-Step-Verification (2FA)
    enabled and you must use `App Password`_ to authenticate. Also note that Google
    revokes your App Passwords when you change your Google Account password and then
    you need to generate a new one.
    Using other methods (like ``XOAUTH2`` or the ``Gmail API``) are not supported currently.
    You should use Gmail for testing purposes only and use a real provider in production.

.. tip::

    If you want to override the default host for a provider (to debug an issue using
    a service like ``requestbin.com``), change ``default`` by your host:

    .. code-block:: env

        # .env
        MAILER_DSN=mailgun+https://KEY:DOMAIN@requestbin.com

    Note that the protocol is *always* HTTPs and cannot be changed.

High Availability
~~~~~~~~~~~~~~~~~

Symfony's mailer supports `high availability`_ via a technique called "failover"
to ensure that emails are sent even if one mailer server fails.

A failover transport is configured with two or more transports and the
``failover`` keyword:

.. code-block:: env

    MAILER_DSN="failover(postmark+api://ID@default sendgrid+smtp://KEY@default)"

The failover-transport starts using the first transport and if it fails, it
will retry the same delivery with the next transports until one of them succeeds
(or until all of them fail).

Load Balancing
~~~~~~~~~~~~~~

Symfony's mailer supports `load balancing`_ via a technique called "round-robin"
to distribute the mailing workload across multiple transports.

A round-robin transport is configured with two or more transports and the
``roundrobin`` keyword:

.. code-block:: env

    MAILER_DSN="roundrobin(postmark+api://ID@default sendgrid+smtp://KEY@default)"

The round-robin transport starts with a *randomly* selected transport and
then switches to the next available transport for each subsequent email.

As with the failover transport, round-robin retries deliveries until
a transport succeeds (or all fail). In contrast to the failover transport,
it *spreads* the load across all its transports.

TLS Peer Verification
~~~~~~~~~~~~~~~~~~~~~

By default, SMTP transports perform TLS peer verification. This behavior is
configurable with the ``verify_peer`` option. Although it's not recommended to
disable this verification for security reasons, it can be useful while developing
the application or when using a self-signed certificate::

    $dsn = 'smtp://user:pass@smtp.example.com?verify_peer=0';

Other Options
~~~~~~~~~~~~~

``command``
    Command to be executed by ``sendmail`` transport::

        $dsn = 'sendmail://default?command=/usr/sbin/sendmail%20-oi%20-t'

``local_domain``
    The domain name to use in ``HELO`` command::

        $dsn = 'smtps://smtp.example.com?local_domain=example.org'

``restart_threshold``
    The maximum number of messages to send before re-starting the transport. It
    can be used together with ``restart_threshold_sleep``::

        $dsn = 'smtps://smtp.example.com?restart_threshold=10&restart_threshold_sleep=1'

``restart_threshold_sleep``
    The number of seconds to sleep between stopping and re-starting the transport.
    It's common to combine it with ``restart_threshold``::

        $dsn = 'smtps://smtp.example.com?restart_threshold=10&restart_threshold_sleep=1'

``ping_threshold``
    The minimum number of seconds between two messages required to ping the server::

        $dsn = 'smtps://smtp.example.com?ping_threshold=200'

``max_per_second``
    The number of messages to send per second (0 to disable this limitation)::

        $dsn = 'smtps://smtp.example.com?max_per_second=2'

    .. versionadded:: 6.2

        The ``max_per_second`` option was introduced in Symfony 6.2.

Creating & Sending Messages
---------------------------

To send an email, get a :class:`Symfony\\Component\\Mailer\\Mailer`
instance by type-hinting :class:`Symfony\\Component\\Mailer\\MailerInterface`
and create an :class:`Symfony\\Component\\Mime\\Email` object::

    // src/Controller/MailerController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Component\Mime\Email;
    use Symfony\Component\Routing\Annotation\Route;

    class MailerController extends AbstractController
    {
        #[Route('/email')]
        public function sendEmail(MailerInterface $mailer): Response
        {
            $email = (new Email())
                ->from('hello@example.com')
                ->to('you@example.com')
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject('Time for Symfony Mailer!')
                ->text('Sending emails is fun again!')
                ->html('<p>See Twig integration for better HTML integration!</p>');

            $mailer->send($email);

            // ...
        }
    }

That's it! The message will be sent via the transport you configured. If the
transport is configured to :ref:`send emails asynchronously <mailer-sending-messages-async>`,
the message won't be actually sent until :doc:`a worker consumes it <messenger-worker>`.

Email Addresses
~~~~~~~~~~~~~~~

All the methods that require email addresses (``from()``, ``to()``, etc.) accept
both strings or address objects::

    // ...
    use Symfony\Component\Mime\Address;

    $email = (new Email())
        // email address as a simple string
        ->from('fabien@example.com')

        // email address as an object
        ->from(new Address('fabien@example.com'))

        // defining the email address and name as an object
        // (email clients will display the name)
        ->from(new Address('fabien@example.com', 'Fabien'))

        // defining the email address and name as a string
        // (the format must match: 'Name <email@example.com>')
        ->from(Address::create('Fabien Potencier <fabien@example.com>'))

        // ...
    ;

.. tip::

    Instead of calling ``->from()`` *every* time you create a new email, you can
    :ref:`configure emails globally <mailer-configure-email-globally>` to set the
    same ``From`` email to all messages.

.. note::

    The local part of the address (what goes before the ``@``) can include UTF-8
    characters, except for the sender address (to avoid issues with bounced emails).
    For example: ``föóbàr@example.com``, ``用户@example.com``, ``θσερ@example.com``, etc.

Use ``addTo()``, ``addCc()``, or ``addBcc()`` methods to add more addresses::

    $email = (new Email())
        ->to('foo@example.com')
        ->addTo('bar@example.com')
        ->cc('cc@example.com')
        ->addCc('cc2@example.com')

        // ...
    ;

Alternatively, you can pass multiple addresses to each method::

    $toAddresses = ['foo@example.com', new Address('bar@example.com')];

    $email = (new Email())
        ->to(...$toAddresses)
        ->cc('cc1@example.com', 'cc2@example.com')

        // ...
    ;

Message Headers
~~~~~~~~~~~~~~~

Messages include a number of header fields to describe their contents. Symfony
sets all the required headers automatically, but you can set your own headers
too. There are different types of headers (Id header, Mailbox header, Date
header, etc.) but most of the times you'll set text headers::

    $email = (new Email())
        ->getHeaders()
            // this non-standard header tells compliant autoresponders ("email holiday mode") to not
            // reply to this message because it's an automated email
            ->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply')

            // use an array if you want to add a header with multiple values
            // (for example in the "References" or "In-Reply-To" header)
            ->addIdHeader('References', ['123@example.com', '456@example.com'])

            // ...
    ;

.. tip::

    Instead of calling ``->addTextHeader()`` *every* time you create a new email, you can
    :ref:`configure emails globally <mailer-configure-email-globally>` to set the same
    headers to all sent emails.

Message Contents
~~~~~~~~~~~~~~~~

The text and HTML contents of the email messages can be strings (usually the
result of rendering some template) or PHP resources::

    $email = (new Email())
        // ...
        // simple contents defined as a string
        ->text('Lorem ipsum...')
        ->html('<p>Lorem ipsum...</p>')

        // attach a file stream
        ->text(fopen('/path/to/emails/user_signup.txt', 'r'))
        ->html(fopen('/path/to/emails/user_signup.html', 'r'))
    ;

.. tip::

    You can also use Twig templates to render the HTML and text contents. Read
    the `Twig: HTML & CSS`_ section later in this article to
    learn more.

File Attachments
~~~~~~~~~~~~~~~~

Use the ``addPart()`` method with a ``BodyFile`` to add files that exist on your file system::

    use Symfony\Component\Mime\Part\DataPart;
    use Symfony\Component\Mime\Part\File;
    // ...

    $email = (new Email())
        // ...
        ->addPart(new DataPart(new File('/path/to/documents/terms-of-use.pdf')))
        // optionally you can tell email clients to display a custom name for the file
        ->addPart(new DataPart(new File('/path/to/documents/privacy.pdf'), 'Privacy Policy'))
        // optionally you can provide an explicit MIME type (otherwise it's guessed)
        ->addPart(new DataPart(new File('/path/to/documents/contract.doc'), 'Contract', 'application/msword'))
    ;

Alternatively you can attach contents from a stream by passing it directly to the ``DataPart`` ::

    $email = (new Email())
        // ...
        ->addPart(new DataPart(fopen('/path/to/documents/contract.doc', 'r')))
    ;

.. deprecated:: 6.2

    In Symfony versions previous to 6.2, the methods ``attachFromPath()`` and
    ``attach()`` could be used to add attachments. These methods have been
    deprecated and replaced with ``addPart()``.

Embedding Images
~~~~~~~~~~~~~~~~

If you want to display images inside your email, you must embed them
instead of adding them as attachments. When using Twig to render the email
contents, as explained :ref:`later in this article <mailer-twig-embedding-images>`,
the images are embedded automatically. Otherwise, you need to embed them manually.

First, use the ``addPart()`` method to add an image from a
file or stream::

    $email = (new Email())
        // ...
        // get the image contents from a PHP resource
        ->addPart((new DataPart(fopen('/path/to/images/logo.png', 'r'), 'logo', 'image/png'))->asInline())
        // get the image contents from an existing file
        ->addPart((new DataPart(new File('/path/to/images/signature.gif'), 'footer-signature', 'image/gif'))->asInline())
    ;

Use the ``asInline()`` method to embed the content instead of attaching it.

The second optional argument of both methods is the image name ("Content-ID" in
the MIME standard). Its value is an arbitrary string used later to reference the
images inside the HTML contents::

    $email = (new Email())
        // ...
        ->addPart((new DataPart(fopen('/path/to/images/logo.png', 'r'), 'logo', 'image/png'))->asInline())
        ->addPart((new DataPart(new File('/path/to/images/signature.gif'), 'footer-signature', 'image/gif'))->asInline())

        // reference images using the syntax 'cid:' + "image embed name"
        ->html('<img src="cid:logo"> ... <img src="cid:footer-signature"> ...')

        // use the same syntax for images included as HTML background images
        ->html('... <div background="cid:footer-signature"> ... </div> ...')
    ;

.. versionadded:: 6.1

    The support of embedded images as HTML backgrounds was introduced in Symfony 6.1.

.. deprecated:: 6.2

    In Symfony versions previous to 6.2, the methods ``embedFromPath()`` and
    ``embed()`` could be used to embed images. These methods have been deprecated
    and replaced with ``addPart()`` together with inline ``DataPart`` objects.

.. _mailer-configure-email-globally:

Configuring Emails Globally
---------------------------

Instead of calling ``->from()`` on each Email you create, you can configure this
value globally so that it is set on all sent emails. The same is true with ``->to()``
and headers.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        framework:
            mailer:
                envelope:
                    sender: 'fabien@example.com'
                    recipients: ['foo@example.com', 'bar@example.com']
                headers:
                    From: 'Fabien <fabien@example.com>'
                    Bcc: 'baz@example.com'
                    X-Custom-Header: 'foobar'

    .. code-block:: xml

        <!-- config/packages/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:mailer>
                    <framework:envelope>
                        <framework:sender>fabien@example.com</framework:sender>
                        <framework:recipients>foo@example.com</framework:recipients>
                        <framework:recipients>bar@example.com</framework:recipients>
                    </framework:envelope>
                    <framework:header name="From">Fabien &lt;fabien@example.com&gt;</framework:header>
                    <framework:header name="Bcc">baz@example.com</framework:header>
                    <framework:header name="X-Custom-Header">foobar</framework:header>
                </framework:mailer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/mailer.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $mailer = $framework->mailer();
            $mailer
                ->envelope()
                    ->sender('fabien@example.com')
                    ->recipients(['foo@example.com', 'bar@example.com'])
            ;

            $mailer->header('From')->value('Fabien <fabien@example.com>');
            $mailer->header('Bcc')->value('baz@example.com');
            $mailer->header('X-Custom-Header')->value('foobar');
        };

.. caution::

    Some third-party providers don't support the usage of keywords like ``from``
    in the ``headers``. Check out your provider's documentation before setting
    any global header.

Handling Sending Failures
-------------------------

Symfony Mailer considers that sending was successful when your transport (SMTP
server or third-party provider) accepts the mail for further delivery. The message
can later be lost or not delivered because of some problem in your provider, but
that's out of reach for your Symfony application.

If there's an error when handing over the email to your transport, Symfony throws
a :class:`Symfony\\Component\\Mailer\\Exception\\TransportExceptionInterface`.
Catch that exception to recover from the error or to display some message::

    use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

    $email = new Email();
    // ...
    try {
        $mailer->send($email);
    } catch (TransportExceptionInterface $e) {
        // some error prevented the email sending; display an
        // error message or try to resend the message
    }

Debugging Emails
----------------

The :class:`Symfony\\Component\\Mailer\\SentMessage` object returned by the
``send()`` method of the :class:`Symfony\\Component\\Mailer\\Transport\\TransportInterface`
provides access to the original message (``getOriginalMessage()``) and to some
debug information (``getDebug()``) such as the HTTP calls done by the HTTP
transports, which is useful to debug errors.

.. note::

    Some mailer providers change the ``Message-Id`` when sending the email. The
    ``getMessageId()`` method from ``SentMessage`` always returns the definitive
    ID of the message (being the original random ID generated by Symfony or the
    new ID generated by the mailer provider).

The exceptions related to mailer transports (those which implement
:class:`Symfony\\Component\\Mailer\\Exception\\TransportException`) also provide
this debug information via the ``getDebug()`` method.

.. _mailer-twig:

Twig: HTML & CSS
----------------

The Mime component integrates with the :ref:`Twig template engine <twig-language>`
to provide advanced features such as CSS style inlining and support for HTML/CSS
frameworks to create complex HTML email messages. First, make sure Twig is installed:

.. code-block:: terminal

    $ composer require symfony/twig-bundle

    # or if you're using the component in a non-Symfony app:
    # composer require symfony/twig-bridge

HTML Content
~~~~~~~~~~~~

To define the contents of your email with Twig, use the
:class:`Symfony\\Bridge\\Twig\\Mime\\TemplatedEmail` class. This class extends
the normal :class:`Symfony\\Component\\Mime\\Email` class but adds some new methods
for Twig templates::

    use Symfony\Bridge\Twig\Mime\TemplatedEmail;

    $email = (new TemplatedEmail())
        ->from('fabien@example.com')
        ->to(new Address('ryan@example.com'))
        ->subject('Thanks for signing up!')

        // path of the Twig template to render
        ->htmlTemplate('emails/signup.html.twig')

        // pass variables (name => value) to the template
        ->context([
            'expiration_date' => new \DateTime('+7 days'),
            'username' => 'foo',
        ])
    ;

Then, create the template:

.. code-block:: html+twig

    {# templates/emails/signup.html.twig #}
    <h1>Welcome {{ email.toName }}!</h1>

    <p>
        You signed up as {{ username }} the following email:
    </p>
    <p><code>{{ email.to[0].address }}</code></p>

    <p>
        <a href="#">Click here to activate your account</a>
        (this link is valid until {{ expiration_date|date('F jS') }})
    </p>

The Twig template has access to any of the parameters passed in the ``context()``
method of the ``TemplatedEmail`` class and also to a special variable called
``email``, which is an instance of
:class:`Symfony\\Bridge\\Twig\\Mime\\WrappedTemplatedEmail`.

Text Content
~~~~~~~~~~~~

When the text content of a ``TemplatedEmail`` is not explicitly defined, it is
automatically generated from the HTML contents.

Symfony uses the following strategy when generating the text version of an
email:

* If an explicit HTML to text converter has been configured (see
  :ref:`twig.mailer.html_to_text_converter
  <config-twig-html-to-text-converter>`), it calls it;

* If not, and if you have `league/html-to-markdown`_ installed in your
  application, it uses it to turn HTML into Markdown (so the text email has
  some visual appeal);

* Otherwise, it applies the :phpfunction:`strip_tags` PHP function to the
  original HTML contents.

If you want to define the text content yourself, use the ``text()`` method
explained in the previous sections or the ``textTemplate()`` method provided by
the ``TemplatedEmail`` class:

.. code-block:: diff

    + use Symfony\Bridge\Twig\Mime\TemplatedEmail;

     $email = (new TemplatedEmail())
         // ...

         ->htmlTemplate('emails/signup.html.twig')
    +     ->textTemplate('emails/signup.txt.twig')
         // ...
     ;

.. _mailer-twig-embedding-images:

Embedding Images
~~~~~~~~~~~~~~~~

Instead of dealing with the ``<img src="cid: ...">`` syntax explained in the
previous sections, when using Twig to render email contents you can refer to
image files as usual. First, to simplify things, define a Twig namespace called
``images`` that points to whatever directory your images are stored in:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...

            paths:
                # point this wherever your images live
                '%kernel.project_dir%/assets/images': images

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->

                <!-- point this wherever your images live -->
                <twig:path namespace="images">%kernel.project_dir%/assets/images</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig) {
            // ...

            // point this wherever your images live
            $twig->path('%kernel.project_dir%/assets/images', 'images');
        };

Now, use the special ``email.image()`` Twig helper to embed the images inside
the email contents:

.. code-block:: html+twig

    {# '@images/' refers to the Twig namespace defined earlier #}
    <img src="{{ email.image('@images/logo.png') }}" alt="Logo">

    <h1>Welcome {{ email.toName }}!</h1>
    {# ... #}

.. _mailer-inline-css:

Inlining CSS Styles
~~~~~~~~~~~~~~~~~~~

Designing the HTML contents of an email is very different from designing a
normal HTML page. For starters, most email clients only support a subset of all
CSS features. In addition, popular email clients like Gmail don't support
defining styles inside ``<style> ... </style>`` sections and you must **inline
all the CSS styles**.

CSS inlining means that every HTML tag must define a ``style`` attribute with
all its CSS styles. This can make organizing your CSS a mess. That's why Twig
provides a ``CssInlinerExtension`` that automates everything for you. Install
it with:

.. code-block:: terminal

    $ composer require twig/extra-bundle twig/cssinliner-extra

The extension is enabled automatically. To use it, wrap the entire template
with the ``inline_css`` filter:

.. code-block:: html+twig

    {% apply inline_css %}
        <style>
            {# here, define your CSS styles as usual #}
            h1 {
                color: #333;
            }
        </style>

        <h1>Welcome {{ email.toName }}!</h1>
        {# ... #}
    {% endapply %}

Using External CSS Files
........................

You can also define CSS styles in external files and pass them as
arguments to the filter:

.. code-block:: html+twig

    {% apply inline_css(source('@styles/email.css')) %}
        <h1>Welcome {{ username }}!</h1>
        {# ... #}
    {% endapply %}

You can pass unlimited number of arguments to ``inline_css()`` to load multiple
CSS files. For this example to work, you also need to define a new Twig namespace
called ``styles`` that points to the directory where ``email.css`` lives:

.. _mailer-css-namespace:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...

            paths:
                # point this wherever your css files live
                '%kernel.project_dir%/assets/styles': styles

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->

                <!-- point this wherever your css files live -->
                <twig:path namespace="styles">%kernel.project_dir%/assets/styles</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig) {
            // ...

            // point this wherever your css files live
            $twig->path('%kernel.project_dir%/assets/styles', 'styles');
        };

.. _mailer-markdown:

Rendering Markdown Content
~~~~~~~~~~~~~~~~~~~~~~~~~~

Twig provides another extension called ``MarkdownExtension`` that lets you
define the email contents using `Markdown syntax`_. To use this, install the
extension and a Markdown conversion library (the extension is compatible with
several popular libraries):

.. code-block:: terminal

    # instead of league/commonmark, you can also use erusev/parsedown or michelf/php-markdown
    $ composer require twig/extra-bundle twig/markdown-extra league/commonmark

The extension adds a ``markdown_to_html`` filter, which you can use to convert parts or
the entire email contents from Markdown to HTML:

.. code-block:: twig

    {% apply markdown_to_html %}
        Welcome {{ email.toName }}!
        ===========================

        You signed up to our site using the following email:
        `{{ email.to[0].address }}`

        [Click here to activate your account]({{ url('...') }})
    {% endapply %}

.. _mailer-inky:

Inky Email Templating Language
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Creating beautifully designed emails that work on every email client is so
complex that there are HTML/CSS frameworks dedicated to that. One of the most
popular frameworks is called `Inky`_. It defines a syntax based on some HTML-like
tags which are later transformed into the real HTML code sent to users:

.. code-block:: html

    <!-- a simplified example of the Inky syntax -->
    <container>
        <row>
            <columns>This is a column.</columns>
        </row>
    </container>

Twig provides integration with Inky via the ``InkyExtension``. First, install
the extension in your application:

.. code-block:: terminal

    $ composer require twig/extra-bundle twig/inky-extra

The extension adds an ``inky_to_html`` filter, which can be used to convert
parts or the entire email contents from Inky to HTML:

.. code-block:: html+twig

    {% apply inky_to_html %}
        <container>
            <row class="header">
                <columns>
                    <spacer size="16"></spacer>
                    <h1 class="text-center">Welcome {{ email.toName }}!</h1>
                </columns>

                {# ... #}
            </row>
        </container>
    {% endapply %}

You can combine all filters to create complex email messages:

.. code-block:: twig

    {% apply inky_to_html|inline_css(source('@styles/foundation-emails.css')) %}
        {# ... #}
    {% endapply %}

This makes use of the :ref:`styles Twig namespace <mailer-css-namespace>` we created
earlier. You could, for example, `download the foundation-emails.css file`_
directly from GitHub and save it in ``assets/styles``.

.. _signing-and-encrypting-messages:

Signing and Encrypting Messages
-------------------------------

It's possible to sign and/or encrypt email messages to increase their
integrity/security. Both options can be combined to encrypt a signed message
and/or to sign an encrypted message.

Before signing/encrypting messages, make sure to have:

* The `OpenSSL PHP extension`_ properly installed and configured;
* A valid `S/MIME`_ security certificate.

.. tip::

    When using OpenSSL to generate certificates, make sure to add the
    ``-addtrust emailProtection`` command option.

Signing Messages
~~~~~~~~~~~~~~~~

When signing a message, a cryptographic hash is generated for the entire content
of the message (including attachments). This hash is added as an attachment so
the recipient can validate the integrity of the received message. However, the
contents of the original message are still readable for mailing agents not
supporting signed messages, so you must also encrypt the message if you want to
hide its contents.

You can sign messages using either ``S/MIME`` or ``DKIM``. In both cases, the
certificate and private key must be `PEM encoded`_, and can be either created
using for example OpenSSL or obtained at an official Certificate Authority (CA).
The email recipient must have the CA certificate in the list of trusted issuers
in order to verify the signature.

S/MIME Signer
.............

`S/MIME`_ is a standard for public key encryption and signing of MIME data. It
requires using both a certificate and a private key::

    use Symfony\Component\Mime\Crypto\SMimeSigner;
    use Symfony\Component\Mime\Email;

    $email = (new Email())
        ->from('hello@example.com')
        // ...
        ->html('...');

    $signer = new SMimeSigner('/path/to/certificate.crt', '/path/to/certificate-private-key.key');
    // if the private key has a passphrase, pass it as the third argument
    // new SMimeSigner('/path/to/certificate.crt', '/path/to/certificate-private-key.key', 'the-passphrase');

    $signedEmail = $signer->sign($email);
    // now use the Mailer component to send this $signedEmail instead of the original email

.. tip::

    The ``SMimeSigner`` class defines other optional arguments to pass
    intermediate certificates and to configure the signing process using a
    bitwise operator options for :phpfunction:`openssl_pkcs7_sign` PHP function.

DKIM Signer
...........

`DKIM`_ is an email authentication method that affixes a digital signature,
linked to a domain name, to each outgoing email messages. It requires a private
key but not a certificate::

    use Symfony\Component\Mime\Crypto\DkimSigner;
    use Symfony\Component\Mime\Email;

    $email = (new Email())
        ->from('hello@example.com')
        // ...
        ->html('...');

    // first argument: same as openssl_pkey_get_private(), either a string with the
    // contents of the private key or the absolute path to it (prefixed with 'file://')
    // second and third arguments: the domain name and "selector" used to perform a DNS lookup
    // (the selector is a string used to point to a specific DKIM public key record in your DNS)
    $signer = new DkimSigner('file:///path/to/private-key.key', 'example.com', 'sf');
    // if the private key has a passphrase, pass it as the fifth argument
    // new DkimSigner('file:///path/to/private-key.key', 'example.com', 'sf', [], 'the-passphrase');

    $signedEmail = $signer->sign($email);
    // now use the Mailer component to send this $signedEmail instead of the original email

    // DKIM signer provides many config options and a helper object to configure them
    use Symfony\Component\Mime\Crypto\DkimOptions;

    $signedEmail = $signer->sign($email, (new DkimOptions())
        ->bodyCanon('relaxed')
        ->headerCanon('relaxed')
        ->headersToIgnore(['Message-ID'])
        ->toArray()
    );

Encrypting Messages
~~~~~~~~~~~~~~~~~~~

When encrypting a message, the entire message (including attachments) is
encrypted using a certificate. Therefore, only the recipients that have the
corresponding private key can read the original message contents::

    use Symfony\Component\Mime\Crypto\SMimeEncrypter;
    use Symfony\Component\Mime\Email;

    $email = (new Email())
        ->from('hello@example.com')
        // ...
        ->html('...');

    $encrypter = new SMimeEncrypter('/path/to/certificate.crt');
    $encryptedEmail = $encrypter->encrypt($email);
    // now use the Mailer component to send this $encryptedEmail instead of the original email

You can pass more than one certificate to the ``SMimeEncrypter`` constructor
and it will select the appropriate certificate depending on the ``To`` option::

    $firstEmail = (new Email())
        // ...
        ->to('jane@example.com');

    $secondEmail = (new Email())
        // ...
        ->to('john@example.com');

    // the second optional argument of SMimeEncrypter defines which encryption algorithm is used
    // (it must be one of these constants: https://www.php.net/manual/en/openssl.ciphers.php)
    $encrypter = new SMimeEncrypter([
        // key = email recipient; value = path to the certificate file
        'jane@example.com' => '/path/to/first-certificate.crt',
        'john@example.com' => '/path/to/second-certificate.crt',
    ]);

    $firstEncryptedEmail = $encrypter->encrypt($firstEmail);
    $secondEncryptedEmail = $encrypter->encrypt($secondEmail);

.. _multiple-email-transports:

Multiple Email Transports
-------------------------

You may want to use more than one mailer transport for delivery of your messages.
This can be configured by replacing the ``dsn`` configuration entry with a
``transports`` entry, like:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        framework:
            mailer:
                transports:
                    main: '%env(MAILER_DSN)%'
                    alternative: '%env(MAILER_DSN_IMPORTANT)%'

    .. code-block:: xml

        <!-- config/packages/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:mailer>
                    <framework:transport name="main">%env(MAILER_DSN)%</framework:transport>
                    <framework:transport name="alternative">%env(MAILER_DSN_IMPORTANT)%</framework:transport>
                </framework:mailer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/mailer.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->mailer()
                ->transport('main', env('MAILER_DSN'))
                ->transport('alternative', env('MAILER_DSN_IMPORTANT'))
            ;
        };

By default the first transport is used. The other transports can be selected by
adding an ``X-Transport`` header (which Mailer will remove automatically from
the final email)::

    // Send using first transport ("main"):
    $mailer->send($email);

    // ... or use the transport "alternative":
    $email->getHeaders()->addTextHeader('X-Transport', 'alternative');
    $mailer->send($email);

.. _mailer-sending-messages-async:

Sending Messages Async
----------------------

When you call ``$mailer->send($email)``, the email is sent to the transport immediately.
To improve performance, you can leverage :doc:`Messenger </messenger>` to send
the messages later via a Messenger transport.

Start by following the :doc:`Messenger </messenger>` documentation and configuring
a transport. Once everything is set up, when you call ``$mailer->send()``, a
:class:`Symfony\\Component\\Mailer\\Messenger\\SendEmailMessage` message will
be dispatched through the default message bus (``messenger.default_bus``). Assuming
you have a transport called ``async``, you can route the message there:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async: "%env(MESSENGER_TRANSPORT_DSN)%"

                routing:
                    'Symfony\Component\Mailer\Messenger\SendEmailMessage': async

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:transport name="async">%env(MESSENGER_TRANSPORT_DSN)%</framework:transport>
                    <framework:routing message-class="Symfony\Component\Mailer\Messenger\SendEmailMessage">
                        <framework:sender service="async"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->messenger()
                ->transport('async')->dsn(env('MESSENGER_TRANSPORT_DSN'));

            $framework->messenger()
                ->routing('Symfony\Component\Mailer\Messenger\SendEmailMessage')
                ->senders(['async']);
        };

Thanks to this, instead of being delivered immediately, messages will be sent
to the transport to be handled later (see :ref:`messenger-worker`). Note that
the "rendering" of the email (computed headers, body rendering, ...) is also
deferred and will only happen just before the email is sent by the Messenger
handler.

.. versionadded:: 6.2

    The following example about rendering the email before calling
    ``$mailer->send($email)`` works as of Symfony 6.2.

When sending an email asynchronously, its instance must be serializable.
This is always the case for :class:`Symfony\\Bridge\\Twig\\Mime\\Email`
instances, but when sending a
:class:`Symfony\\Bridge\\Twig\\Mime\\TemplatedEmail`, you must ensure that
the ``context`` is serializable. If you have non-serializable variables,
like Doctrine entities, either replace them with more specific variables or
render the email before calling ``$mailer->send($email)``::

    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Component\Mime\BodyRendererInterface;

    public function action(MailerInterface $mailer, BodyRendererInterface $bodyRenderer)
    {
        $email = (new TemplatedEmail())
            ->htmlTemplate($template)
            ->context($context)
        ;
        $bodyRenderer->render($email);

        $mailer->send($email);
    }

You can configure which bus is used to dispatch the message using the ``message_bus`` option.
You can also set this to ``false`` to call the Mailer transport directly and
disable asynchronous delivery.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        framework:
            mailer:
                message_bus: app.another_bus

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:mailer
                    message_bus="app.another_bus"
                >
                </framework:mailer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/mailer.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->mailer()
                ->messageBus('app.another_bus');
        };

.. note::

    In cases of long-running scripts, and when Mailer uses the
    :class:`Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport`
    you may manually disconnect from the SMTP server to avoid keeping
    an open connection to the SMTP server in between sending emails.
    You can do so by using the ``stop()`` method.

.. versionadded:: 6.1

    The :method:`Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport::stop`
    method was made public in Symfony 6.1.

You can also select the transport by adding an ``X-Bus-Transport`` header (which
will be remove automatically from the final message)::

    // Use the bus transport "app.another_bus":
    $email->getHeaders()->addTextHeader('X-Bus-Transport', 'app.another_bus');
    $mailer->send($email);

.. versionadded:: 6.2

    The ``X-Bus-Transport`` header support was introduced in Symfony 6.2.

Adding Tags and Metadata to Emails
----------------------------------

Certain 3rd party transports support email *tags* and *metadata*, which can be used
for grouping, tracking and workflows. You can add those by using the
:class:`Symfony\\Component\\Mailer\\Header\\TagHeader` and
:class:`Symfony\\Component\\Mailer\\Header\\MetadataHeader` classes. If your transport
supports headers, it will convert them to their appropriate format::

    use Symfony\Component\Mailer\Header\MetadataHeader;
    use Symfony\Component\Mailer\Header\TagHeader;

    $email->getHeaders()->add(new TagHeader('password-reset'));
    $email->getHeaders()->add(new MetadataHeader('Color', 'blue'));
    $email->getHeaders()->add(new MetadataHeader('Client-ID', '12345'));

If your transport does not support tags and metadata, they will be added as custom headers:

.. code-block:: text

    X-Tag: password-reset
    X-Metadata-Color: blue
    X-Metadata-Client-ID: 12345

The following transports currently support tags and metadata:

* MailChimp
* Mailgun
* Postmark
* Sendgrid
* Sendinblue

The following transports only support tags:

* MailPace

The following transports only support metadata:

* Amazon SES (note that Amazon refers to this feature as "tags", but Symfony
  calls it "metadata" because it contains a key and a value)

.. versionadded:: 6.1

    Metadata support for Amazon SES was introduced in Symfony 6.1.

Draft Emails
------------

.. versionadded:: 6.1

    ``Symfony\Component\Mime\DraftEmail`` was introduced in 6.1.

:class:`Symfony\\Component\\Mime\\DraftEmail` is a special instance of
:class:`Symfony\\Component\\Mime\\Email`. Its purpose is to build up an email
(with body, attachments, etc) and make available to download as an ``.eml`` with
the ``X-Unsent`` header. Many email clients can open these files and interpret
them as *draft emails*. You can use these to create advanced ``mailto:`` links.

Here's an example of making one available to download::

    // src/Controller/DownloadEmailController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\ResponseHeaderBag;
    use Symfony\Component\Mime\DraftEmail;
    use Symfony\Component\Routing\Annotation\Route;

    class DownloadEmailController extends AbstractController
    {
        #[Route('/download-email')]
        public function __invoke(): Response
        {
            $message = (new DraftEmail())
                ->html($this->renderView(/* ... */))
                ->attach(/* ... */)
            ;

            $response = new Response($message->toString());
            $contentDisposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'download.eml'
            );
            $response->headers->set('Content-Type', 'message/rfc822');
            $response->headers->set('Content-Disposition', $contentDisposition);

            return $response;
        }
    }

.. note::

    As it's possible for :class:`Symfony\\Component\\Mime\\DraftEmail`'s to be created
    without a To/From they cannot be sent with the mailer.

Mailer Events
-------------

MessageEvent
~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\Mailer\\Event\\MessageEvent`

``MessageEvent`` allows to change the Mailer message and the envelope before
the email is sent::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Mailer\Event\MessageEvent;
    use Symfony\Component\Mime\Email;

    public function onMessage(MessageEvent $event): void
    {
        $message = $event->getMessage();
        if (!$message instanceof Email) {
            return;
        }
        // do something with the message
    }

If you want to stop the Message from being sent, call ``reject()`` (it will
also stop the event propagation)::

    use Symfony\Component\Mailer\Event\MessageEvent;

    public function onMessage(MessageEvent $event): void
    {
        $event->reject();
    }

.. versionadded:: 6.3

    The ``reject()`` method was introduced in Symfony 6.3.

.. tip::

    When using a ``MessageEvent`` listener to
    :doc:`sign the email contents <signing-and-encrypting-messages>`, run it as
    late as possible (e.g. setting a negative priority for it) so the email
    contents are not set or modified after signing them.

Execute this command to find out which listeners are registered for this event
and their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher "Symfony\Component\Mailer\Event\MessageEvent"

QueuingMessageEvent
~~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\Mailer\\Event\\QueuingMessageEvent`

.. versionadded:: 6.2

    The ``QueuingMessageEvent`` class was introduced in Symfony 6.2.

``QueuingMessageEvent`` allows to add some logic before the email is sent to
the Messenger bus (this event is not dispatched when no bus is configured); it
extends ``MessageEvent`` to allow adding Messenger stamps to the Messenger
message sent to the bus::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Mailer\Event\QueuingMessageEvent;
    use Symfony\Component\Mime\Email;

    public function onMessage(QueuingMessageEvent $event): void
    {
        $message = $event->getMessage();
        if (!$message instanceof Email) {
            return;
        }
        // do something with the message (logging, ...)

        // and/or add some Messenger stamps
        $event->addStamp(new SomeMessengerStamp());
    }

This event lets listeners do something before a message is sent to the queue
(like adding stamps or logging) but any changes to the message or the envelope
are discarded. To change the message or the envelope, listen to
``MessageEvent`` instead.

Execute this command to find out which listeners are registered for this event
and their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher "Symfony\Component\Mailer\Event\QueuingMessageEvent"

SentMessageEvent
~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\Mailer\\Event\\SentMessageEvent`

.. versionadded:: 6.2

    The ``SentMessageEvent`` event was introduced in Symfony 6.2.

``SentMessageEvent`` allows you to act on the :class:`Symfony\\Component\\\Mailer\\\SentMessage`
class to access the original message (``getOriginalMessage()``) and some debugging
information (``getDebug()``) such as the HTTP calls made by the HTTP transports,
which is useful for debugging errors::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Mailer\Event\SentMessageEvent;
    use Symfony\Component\Mailer\SentMessage;

    public function onMessage(SentMessageEvent $event): void
    {
        $message = $event->getMessage();
        if (!$message instanceof SentMessage) {
            return;
        }

        // do something with the message
    }

Execute this command to find out which listeners are registered for this event
and their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher "Symfony\Component\Mailer\Event\SentMessageEvent"

FailedMessageEvent
~~~~~~~~~~~~~~~~~~

**Event Class**: :class:`Symfony\\Component\\Mailer\\Event\\FailedMessageEvent`

.. versionadded:: 6.2

    The ``FailedMessageEvent`` event was introduced in Symfony 6.2.

``FailedMessageEvent`` allows acting on the the initial message in case of a failure::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Mailer\Event\FailedMessageEvent;

    public function onMessage(FailedMessageEvent $event): void
    {
        // e.g you can get more information on this error when sending an email
        $event->getError();

        // do something with the message
    }

Execute this command to find out which listeners are registered for this event
and their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher "Symfony\Component\Mailer\Event\FailedMessageEvent"

Development & Debugging
-----------------------

.. _mail-catcher:

Enabling an Email Catcher
~~~~~~~~~~~~~~~~~~~~~~~~~

When developing locally, it is recommended to use an email catcher. If you have
enabled Docker support via Symfony recipes, an email catcher is automatically
configured. In addition, if you are using the :doc:`Symfony local web server
</setup/symfony_server>`, the mailer DSN is automatically exposed via the
:ref:`symfony binary Docker integration <symfony-server-docker>`.

Sending Test Emails
~~~~~~~~~~~~~~~~~~~

Symfony provides a command to send emails, which is useful during development
to test if sending emails works correctly:

.. code-block:: terminal

    # the only mandatory argument is the recipient address
    # (check the command help to learn about its options)
    $ php bin/console mailer:test someone@example.com

This command bypasses the :doc:`Messenger bus </messenger>`, if configured, to
ease testing emails even when the Messenger consumer is not running.

.. versionadded:: 6.2

    The ``mailer:test`` command was introduced in Symfony 6.2.

Disabling Delivery
~~~~~~~~~~~~~~~~~~

While developing (or testing), you may want to disable delivery of messages
entirely. You can do this by using ``null://null`` as the mailer DSN, either in
your :ref:`.env configuration files <configuration-multiple-env-files>` or in
the mailer configuration file (e.g. in the ``dev`` or ``test`` environments):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        when@dev:
            framework:
                mailer:
                    dsn: 'null://null'

    .. code-block:: xml

        <!-- config/packages/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:mailer dsn="null://null"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/mailer.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->mailer()
                ->dsn('null://null');
        };

.. note::

    If you're using Messenger and routing to a transport, the message will *still*
    be sent to that transport.

Always Send to the same Address
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of disabling delivery entirely, you might want to *always* send emails to
a specific address, instead of the *real* address:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        when@dev:
            framework:
                mailer:
                    envelope:
                        recipients: ['youremail@example.com']

    .. code-block:: xml

        <!-- config/packages/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:mailer>
                    <framework:envelope>
                        <framework:recipient>youremail@example.com</framework:recipient>
                    </framework:envelope>
                </framework:mailer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/mailer.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->mailer()
                ->envelope()
                    ->recipients(['youremail@example.com'])
            ;
        };

Write a Functional Test
~~~~~~~~~~~~~~~~~~~~~~~

Symfony provides lots of :ref:`built-in mailer assertions <mailer-assertions>`
to functionally test that an email was sent, its contents or headers, etc.
They are available in test classes extending
:class:`Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase` or when using
the :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\MailerAssertionsTrait`::

    // tests/Controller/MailControllerTest.php
    namespace App\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class MailControllerTest extends WebTestCase
    {
        public function testMailIsSentAndContentIsOk()
        {
            $client = static::createClient();
            $client->request('GET', '/mail/send');
            $this->assertResponseIsSuccessful();

            $this->assertEmailCount(1);

            $email = $this->getMailerMessage();

            $this->assertEmailHtmlBodyContains($email, 'Welcome');
            $this->assertEmailTextBodyContains($email, 'Welcome');
        }
    }

.. _`high availability`: https://en.wikipedia.org/wiki/High_availability
.. _`load balancing`: https://en.wikipedia.org/wiki/Load_balancing_(computing)
.. _`download the foundation-emails.css file`: https://github.com/foundation/foundation-emails/blob/develop/dist/foundation-emails.css
.. _`league/html-to-markdown`: https://github.com/thephpleague/html-to-markdown
.. _`Markdown syntax`: https://commonmark.org/
.. _`Inky`: https://get.foundation/emails/docs/inky.html
.. _`S/MIME`: https://en.wikipedia.org/wiki/S/MIME
.. _`DKIM`: https://en.wikipedia.org/wiki/DomainKeys_Identified_Mail
.. _`OpenSSL PHP extension`: https://www.php.net/manual/en/book.openssl.php
.. _`PEM encoded`: https://en.wikipedia.org/wiki/Privacy-Enhanced_Mail
.. _`default_socket_timeout`: https://www.php.net/manual/en/filesystem.configuration.php#ini.default-socket-timeout
.. _`RFC 3986`: https://www.ietf.org/rfc/rfc3986.txt
.. _`App Password`: https://support.google.com/accounts/answer/185833
