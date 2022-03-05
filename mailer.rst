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
                    'dsn' => '%env(MAILER_DSN)%',
                ],
            ]);
        };

.. caution::

    If the username, password or host contain any character considered special in a
    URI (such as ``+``, ``@``, ``$``, ``#``, ``/``, ``:``, ``*``, ``!``), you must
    encode them. See `RFC 3986`_ for the full list of reserved characters or use the
    :phpfunction:`urlencode` function to encode them.

.. caution::

    If you are migrating from Swiftmailer (and the Swiftmailer bundle), be
    warned that the DSN format is different.

Using Built-in Transports
~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.2

    The native protocol was introduced in Symfony 5.2.

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

Instead of using your own SMTP server or sendmail binary, you can send emails via a 3rd party
provider. Mailer supports several - install whichever you want:

==================  ==============================================
Service             Install with
==================  ==============================================
Amazon SES          ``composer require symfony/amazon-mailer``
Gmail               ``composer require symfony/google-mailer``
MailChimp           ``composer require symfony/mailchimp-mailer``
Mailgun             ``composer require symfony/mailgun-mailer``
Mailjet             ``composer require symfony/mailjet-mailer``
Postmark            ``composer require symfony/postmark-mailer``
SendGrid            ``composer require symfony/sendgrid-mailer``
Sendinblue          ``composer require symfony/sendinblue-mailer``
OhMySMTP            ``composer require symfony/oh-my-smtp-mailer``
==================  ==============================================

.. versionadded:: 5.2

    The Sendinblue integration was introduced in Symfony 5.2.

.. versionadded:: 5.4

    The OhMySMTP integration was introduced in Symfony 5.4.

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
Google Gmail         gmail+smtp://USERNAME:PASSWORD@default               n/a                                         n/a
Mailchimp Mandrill   mandrill+smtp://USERNAME:PASSWORD@default            mandrill+https://KEY@default                mandrill+api://KEY@default
Mailgun              mailgun+smtp://USERNAME:PASSWORD@default             mailgun+https://KEY:DOMAIN@default          mailgun+api://KEY:DOMAIN@default
Mailjet              mailjet+smtp://ACCESS_KEY:SECRET_KEY@default         n/a                                         mailjet+api://ACCESS_KEY:SECRET_KEY@default
Postmark             postmark+smtp://ID@default                           n/a                                         postmark+api://KEY@default
Sendgrid             sendgrid+smtp://KEY@default                          n/a                                         sendgrid+api://KEY@default
Sendinblue           sendinblue+smtp://USERNAME:PASSWORD@default          n/a                                         sendinblue+api://KEY@default
OhMySMTP             ohmysmtp+smtp://API_TOKEN@default                    n/a                                         ohmysmtp+api://API_TOKEN@default
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

    .. versionadded:: 5.4

        The ``ping_threshold`` option for ``ses-smtp`` was introduced in Symfony 5.4.

.. note::

    When using SMTP, the default timeout for sending a message before throwing an
    exception is the value defined in the `default_socket_timeout`_ PHP.ini option.

    .. versionadded:: 5.1

        The usage of ``default_socket_timeout`` as the default timeout was
        introduced in Symfony 5.1.

.. tip::

    If you want to override the default host for a provider (to debug an issue using
    a service like ``requestbin.com``), change ``default`` by your host:

    .. code-block:: env

        # .env
        MAILER_DSN=mailgun+https://KEY:DOMAIN@requestbin.com
        MAILER_DSN=mailgun+https://KEY:DOMAIN@requestbin.com:99

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

.. versionadded:: 5.1

    The random selection of the first transport was introduced in Symfony 5.1.
    In previous Symfony versions the first transport was always selected first.

TLS Peer Verification
~~~~~~~~~~~~~~~~~~~~~

By default, SMTP transports perform TLS peer verification. This behavior is
configurable with the ``verify_peer`` option. Although it's not recommended to
disable this verification for security reasons, it can be useful while developing
the application or when using a self-signed certificate::

    $dsn = 'smtp://user:pass@smtp.example.com?verify_peer=0';

.. versionadded:: 5.1

    The ``verify_peer`` option was introduced in Symfony 5.1.

Other Options
~~~~~~~~~~~~~

``command``
    Command to be executed by ``sendmail`` transport::

        $dsn = 'sendmail://default?command=/usr/sbin/sendmail%20-oi%20-t'

    .. versionadded:: 5.2

        This option was introduced in Symfony 5.2.


``local_domain``
    The domain name to use in ``HELO`` command::

        $dsn = 'smtps://smtp.example.com?local_domain=example.org'

    .. versionadded:: 5.2

        This option was introduced in Symfony 5.2.

``restart_threshold``
    The maximum number of messages to send before re-starting the transport. It
    can be used together with ``restart_threshold_sleep``::

        $dsn = 'smtps://smtp.example.com?restart_threshold=10&restart_threshold_sleep=1'

    .. versionadded:: 5.2

        This option was introduced in Symfony 5.2.

``restart_threshold_sleep``
    The number of seconds to sleep between stopping and re-starting the transport.
    It's common to combine it with ``restart_threshold``::

        $dsn = 'smtps://smtp.example.com?restart_threshold=10&restart_threshold_sleep=1'

    .. versionadded:: 5.2

        This option was introduced in Symfony 5.2.

``ping_threshold``
    The minimum number of seconds between two messages required to ping the server::

        $dsn = 'smtps://smtp.example.com?ping_threshold=200'

    .. versionadded:: 5.2

        This option was introduced in Symfony 5.2.

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

That's it! The message will be sent via the transport you configured.

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

    .. versionadded:: 5.2

        Support for UTF-8 characters in email addresses was introduced in Symfony 5.2.

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
            // this header tells auto-repliers ("email holiday mode") to not
            // reply to this message because it's an automated email
            ->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply')

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

Use the ``attachFromPath()`` method to attach files that exist on your file system::

    $email = (new Email())
        // ...
        ->attachFromPath('/path/to/documents/terms-of-use.pdf')
        // optionally you can tell email clients to display a custom name for the file
        ->attachFromPath('/path/to/documents/privacy.pdf', 'Privacy Policy')
        // optionally you can provide an explicit MIME type (otherwise it's guessed)
        ->attachFromPath('/path/to/documents/contract.doc', 'Contract', 'application/msword')
    ;

Alternatively you can use the ``attach()`` method to attach contents from a stream::

    $email = (new Email())
        // ...
        ->attach(fopen('/path/to/documents/contract.doc', 'r'))
    ;

Embedding Images
~~~~~~~~~~~~~~~~

If you want to display images inside your email, you must embed them
instead of adding them as attachments. When using Twig to render the email
contents, as explained :ref:`later in this article <mailer-twig-embedding-images>`,
the images are embedded automatically. Otherwise, you need to embed them manually.

First, use the ``embed()`` or ``embedFromPath()`` method to add an image from a
file or stream::

    $email = (new Email())
        // ...
        // get the image contents from a PHP resource
        ->embed(fopen('/path/to/images/logo.png', 'r'), 'logo')
        // get the image contents from an existing file
        ->embedFromPath('/path/to/images/signature.gif', 'footer-signature')
    ;

The second optional argument of both methods is the image name ("Content-ID" in
the MIME standard). Its value is an arbitrary string used later to reference the
images inside the HTML contents::

    $email = (new Email())
        // ...
        ->embed(fopen('/path/to/images/logo.png', 'r'), 'logo')
        ->embedFromPath('/path/to/images/signature.gif', 'footer-signature')
        // reference images using the syntax 'cid:' + "image embed name"
        ->html('<img src="cid:logo"> ... <img src="cid:footer-signature"> ...')
    ;

.. _mailer-configure-email-globally:

Configuring Emails Globally
---------------------------

Instead of calling ``->from()`` on each Email you create, you can configure this
value globally so that it is set on all sent emails. The same is true with ``->to()``
and headers.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/mailer.yaml
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

.. versionadded:: 5.2

    The ``headers`` option was introduced in Symfony 5.2.

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

When the text content of a ``TemplatedEmail`` is not explicitly defined, mailer
will generate it automatically by converting the HTML contents into text. If you
have `league/html-to-markdown`_ installed in your application,
it uses that to turn HTML into Markdown (so the text email has some visual appeal).
Otherwise, it applies the :phpfunction:`strip_tags` PHP function to the original
HTML contents.

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

.. versionadded:: 5.2

    The DKIM signer was introduced in Symfony 5.2.

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
                ->transport('main', '%env(MAILER_DSN)%')
                ->transport('alternative', '%env(MAILER_DSN_IMPORTANT)%')
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
                ->transport('async')->dsn('%env(MESSENGER_TRANSPORT_DSN)%');

            $framework->messenger()
                ->routing('Symfony\Component\Mailer\Messenger\SendEmailMessage')
                ->senders(['async']);
        };


Thanks to this, instead of being delivered immediately, messages will be sent to
the transport to be handled later (see :ref:`messenger-worker`).

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

.. versionadded:: 5.1

    The ``message_bus`` option was introduced in Symfony 5.1.

Adding Tags and Metadata to Emails
----------------------------------

.. versionadded:: 5.1

    The :class:`Symfony\\Component\\Mailer\\Header\\TagHeader` and
    :class:`Symfony\\Component\\Mailer\\Header\\MetadataHeader` classes were
    introduced in Symfony 5.1.

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

.. versionadded:: 5.4

    The tag and metadata support for Sendgrid was introduced in Symfony 5.4.

The following transports only support tags:

* OhMySMTP

Development & Debugging
-----------------------

Disabling Delivery
~~~~~~~~~~~~~~~~~~

While developing (or testing), you may want to disable delivery of messages
entirely. You can do this by using ``null://null`` as the mailer DSN, either in
your :ref:`.env configuration files <configuration-multiple-env-files>` or in
the mailer configuration file (e.g. in the ``dev`` or ``test`` environments):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/mailer.yaml
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

        # config/packages/dev/mailer.yaml
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

To functionally test that an email was sent, and even assert the email content or headers,
you can use the built in assertions::

    // tests/Controller/MailControllerTest.php
    namespace App\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class MailControllerTest extends WebTestCase
    {
        use MailerAssertionsTrait;

        public function testMailIsSentAndContentIsOk()
        {
            $client = $this->createClient();
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
