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

Emails are delivered via a "transport". And without installing anything else, you
can deliver emails over ``smtp`` by configuring your ``.env`` file:

.. code-block:: bash

    # .env
    MAILER_DSN=smtp://user:pass@smtp.example.com

.. warning::

    If you are migrating from Swiftmailer (and the Swiftmailer bundle), be
    warned that the DSN format is different.

Using a 3rd Party Transport
~~~~~~~~~~~~~~~~~~~~~~~~~~~

But an easier option is to send emails via a 3rd party provider. Mailer supports
several - install whichever you want:

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

Each library includes a :ref:`Symfony Flex recipe <symfony-flex>` that will add
example configuration to your ``.env`` file. For example, suppose you want to
use SendGrid. First, install it:

.. code-block:: terminal

    $ composer require symfony/sendgrid-mailer

You'll now have a new line in your ``.env`` file that you can uncomment:

.. code-block:: bash

    # .env
    MAILER_DSN=sendgrid://KEY@default

The ``MAILER_DSN`` isn't a *real* address: it's a simple format that offloads
most of the configuration work to mailer. The ``sendgrid`` scheme activates the
SendGrid provider that you just installed, which knows all about how to deliver
messages to SendGrid.

The *only* part you need to change is to replace ``KEY`` in the ``MAILER_DSN`` (in
``.env`` or ``.env.local``).

Each provider has different environment variables that the Mailer uses to
configure the *actual* protocol, address and authentication for delivery. Some
also have options that can be configured with query parameters at the end of the
``MAILER_DSN`` - like ``?region=`` for Amazon SES or Mailgun. Some providers support
sending via ``http``, ``api`` or ``smtp``. Symfony chooses the best available
transport, but you can force to use one:

.. code-block:: bash

    # .env
    # force to use SMTP instead of HTTP (which is the default)
    MAILER_DSN=sendgrid+smtp://$SENDGRID_KEY@default

.. tip::

    Check the :ref:`DSN formats <mailer_dsn>` for all supported providers.

Creating & Sending Messages
---------------------------

To send an email, autowire the mailer using
:class:`Symfony\\Component\\Mailer\\MailerInterface` (service id ``mailer.mailer``)
and create an :class:`Symfony\\Component\\Mime\\Email` object::

    // src/Controller/MailerController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Component\Mime\Email;

    class MailerController extends AbstractController
    {
        /**
         * @Route("/email")
         */
        public function sendEmail(MailerInterface $mailer)
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

That's it! The message will be sent via whatever transport you configured.

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
        ->from(Address::fromString('Fabien Potencier <fabien@example.com>'))

        // ...
    ;

.. tip::

    Instead of calling ``->from()`` *every* time you create a new email, you can
    create an :doc:`event subscriber </event_dispatcher>` and listen to the
    :class:`Symfony\\Component\\Mailer\\Event\\MessageEvent` event to set the
    same ``From`` email to all messages.

Multiple addresses are defined with the ``addXXX()`` methods::

    $email = (new Email())
        ->to('foo@example.com')
        ->addTo('bar@example.com')
        ->addTo('baz@example.com')

        // ...
    ;

Alternatively, you can pass multiple addresses to each method::

    $toAddresses = ['foo@example.com', new Address('bar@example.com')];

    $email = (new Email())
        ->to(...$toAddresses)
        ->cc('cc1@example.com', 'cc2@example.com')

        // ...
    ;

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
        // you can also use an absolute URL if your PHP config allows getting URLs using fopen()
        // (this is not recommended because your application may or may not work depending on PHP config)
        ->attachFromPath('http://example.com/path/to/documents/contract.doc', 'Contract', 'application/msword')
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

.. code-block:: yaml

    # config/packages/twig.yaml
    twig:
        # ...

        paths:
            # point this wherever your images live
            '%kernel.project_dir%/assets/images': images

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

    {% apply inline_css(source('@css/email.css')) %}
        <h1>Welcome {{ username }}!</h1>
        {# ... #}
    {% endapply %}

You can pass unlimited number of arguments to ``inline_css()`` to load multiple
CSS files. For this example to work, you also need to define a new Twig namespace
called ``css`` that points to the directory where ``email.css`` lives:

.. _mailer-css-namespace:

.. code-block:: yaml

    # config/packages/twig.yaml
    twig:
        # ...

        paths:
            # point this wherever your css files live
            '%kernel.project_dir%/assets/css': css

.. _mailer-markdown:

Rendering Markdown Content
~~~~~~~~~~~~~~~~~~~~~~~~~~

Twig provides another extension called ``MarkdownExtension`` that lets you
define the email contents using `Markdown syntax`_. To use this, install the
extension and a Markdown conversion library (the extension is compatible with
several popular libraries):

.. code-block:: terminal

    # instead of league/commonmark, you can also use erusev/parsedown or michelf/php-markdown
    $ composer require twig/markdown-extra league/commonmark

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
popular frameworks is called `Inky`_. It defines a syntax based on some simple
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

    {% apply inky_to_html|inline_css(source('@css/foundation-emails.css')) %}
        {# ... #}
    {% endapply %}

This makes use of the :ref:`css Twig namespace <mailer-css-namespace>` we created
earlier. You could, for example, `download the foundation-emails.css file`_
directly from GitHub and save it in ``assets/css``.

Signing and Encrypting Messages
-------------------------------

It's possible to sign and/or encrypt email messages applying the `S/MIME`_
standard to increase their integrity/security. Both options can be combined to
encrypt a signed message and/or to sign an encrypted message.

Before signing/encrypting messages, make sure to have:

* The `OpenSSL PHP extension`_ properly installed and configured;
* A valid `S/MIME`_ security certificate.

Signing Messages
~~~~~~~~~~~~~~~~

When signing a message, a cryptographic hash is generated for the entire content
of the message (including attachments). This hash is added as an attachment so
the recipient can validate the integrity of the received message. However, the
contents of the original message are still readable for mailing agents not
supporting signed messages, so you must also encrypt the message if you want to
hide its contents::

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

The certificate and private key must be `PEM encoded`_, and can be either
created using for example OpenSSL or obtained at an official Certificate
Authority (CA). The email recipient must have the CA certificate in the list of
trusted issuers in order to verify the signature.

.. tip::

    When using OpenSSL to generate certificates, make sure to add the
    ``-addtrust emailProtection`` command option.

.. tip::

    The ``SMimeSigner`` class defines other optional arguments to pass
    intermediate certificates and to configure the signing process using a
    bitwise operator options for :phpfunction:`openssl_pkcs7_sign` PHP function.

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

You can pass more than one certificate to the ``SMimeEncrypter()`` constructor
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

Multiple Email Transports
-------------------------

You may want to use more than one mailer transport for delivery of your messages.
This can be configured by replacing the ``dsn`` configuration entry with a
``transports`` entry, like:

.. code-block:: yaml

    # config/packages/mailer.yaml
    framework:
        mailer:
            transports:
                main: '%env(MAILER_DSN)%'
                alternative: '%env(MAILER_DSN_IMPORTANT)%'

By default the first transport is used. The other transports can be used by
adding a text header ``X-Transport`` to an email::

    // Send using first "main" transport ...
    $mailer->send($email);

    // ... or use the "alternative" one
    $email->getHeaders()->addTextHeader('X-Transport', 'alternative');
    $mailer->send($email);

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
                    'Symfony\Component\Mailer\Messenger\SendEmailMessage':  async

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
                    <framework:routing message-class="Symfony\Component\Mailer\Messenger\SendEmailMessage">
                        <framework:sender service="async"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'routing' => [
                    'Symfony\Component\Mailer\Messenger\SendEmailMessage' => 'async',
                ],
            ],
        ]);

Thanks to this, instead of being delivered immediately, messages will be sent to
the transport to be handled later (see :ref:`messenger-worker`).

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

* Postmark
* Mailgun
* MailChimp

Development & Debugging
-----------------------

Disabling Delivery
~~~~~~~~~~~~~~~~~~

While developing (or testing), you may want to disable delivery of messages entirely.
You can do this by forcing Mailer to use the ``NullTransport`` in only the ``dev``
environment:

.. code-block:: yaml

    # config/packages/dev/mailer.yaml
    framework:
        mailer:
            dsn: 'null://null'

.. note::

    If you're using Messenger and routing to a transport, the message will *still*
    be sent to that transport.

Always Send to the same Address
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of disabling delivery entirely, you might want to *always* send emails to
a specific address, instead of the *real* address. To do that, you can take
advantage of the ``EnvelopeListener`` and register it *only* for the ``dev``
environment:

.. code-block:: yaml

    # config/services_dev.yaml
    services:
        mailer.dev.set_recipients:
            class: Symfony\Component\Mailer\EventListener\EnvelopeListener
            tags: ['kernel.event_subscriber']
            arguments:
                $sender: null
                $recipients: ['youremail@example.com']

.. _`download the foundation-emails.css file`: https://github.com/foundation/foundation-emails/blob/develop/dist/foundation-emails.css
.. _`league/html-to-markdown`: https://github.com/thephpleague/html-to-markdown
.. _`Markdown syntax`: https://commonmark.org/
.. _`Inky`: https://get.foundation/emails/docs/inky.html
.. _`S/MIME`: https://en.wikipedia.org/wiki/S/MIME
.. _`OpenSSL PHP extension`: https://www.php.net/manual/en/book.openssl.php
.. _`PEM encoded`: https://en.wikipedia.org/wiki/Privacy-Enhanced_Mail
