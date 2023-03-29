The Mime Component
==================

    The Mime component allows manipulating the MIME messages used to send emails
    and provides utilities related to MIME types.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/mime

.. include:: /components/require_autoload.rst.inc

Introduction
------------

`MIME`_ (Multipurpose Internet Mail Extensions) is an Internet standard that
extends the original basic format of emails to support features like:

* Headers and text contents using non-ASCII characters;
* Message bodies with multiple parts (e.g. HTML and plain text contents);
* Non-text attachments: audio, video, images, PDF, etc.

The entire MIME standard is complex and huge, but Symfony abstracts all that
complexity to provide two ways of creating MIME messages:

* A high-level API based on the :class:`Symfony\\Component\\Mime\\Email` class
  to quickly create email messages with all the common features;
* A low-level API based on the :class:`Symfony\\Component\\Mime\\Message` class
  to have absolute control over every single part of the email message.

Usage
-----

Use the :class:`Symfony\\Component\\Mime\\Email` class and their *chainable*
methods to compose the entire email message::

    use Symfony\Component\Mime\Email;

    $email = (new Email())
        ->from('fabien@symfony.com')
        ->to('foo@example.com')
        ->cc('bar@example.com')
        ->bcc('baz@example.com')
        ->replyTo('fabien@symfony.com')
        ->priority(Email::PRIORITY_HIGH)
        ->subject('Important Notification')
        ->text('Lorem ipsum...')
        ->html('<h1>Lorem ipsum</h1> <p>...</p>')
    ;

The only purpose of this component is to create the email messages. Use the
:doc:`Mailer component </mailer>` to actually send them.

Twig Integration
----------------

The Mime component comes with excellent integration with Twig, allowing you to
create messages from Twig templates, embed images, inline CSS and more. Details
on how to use those features can be found in the Mailer documentation:
:ref:`Twig: HTML & CSS <mailer-twig>`.

But if you're using the Mime component without the Symfony framework, you'll need
to handle a few setup details.

Twig Setup
~~~~~~~~~~

To integrate with Twig, use the :class:`Symfony\\Bridge\\Twig\\Mime\\BodyRenderer`
class to render the template and update the email message contents with the results::

    // ...
    use Symfony\Bridge\Twig\Mime\BodyRenderer;
    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    // when using the Mime component inside a full-stack Symfony application, you
    // don't need to do this Twig setup. You only have to inject the 'twig' service
    $loader = new FilesystemLoader(__DIR__.'/templates');
    $twig = new Environment($loader);

    $renderer = new BodyRenderer($twig);
    // this updates the $email object contents with the result of rendering
    // the template defined earlier with the given context
    $renderer->render($email);

Inlining CSS Styles (and other Extensions)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To use the :ref:`inline_css <mailer-inline-css>` filter, first install the Twig
extension:

.. code-block:: terminal

    $ composer require twig/cssinliner-extra

Now, enable the extension::

    // ...
    use Twig\Extra\CssInliner\CssInlinerExtension;

    $loader = new FilesystemLoader(__DIR__.'/templates');
    $twig = new Environment($loader);
    $twig->addExtension(new CssInlinerExtension());

The same process should be used for enabling other extensions, like the
:ref:`MarkdownExtension <mailer-markdown>` and :ref:`InkyExtension <mailer-inky>`.

Creating Raw Email Messages
---------------------------

This is useful for advanced applications that need absolute control over every
email part. It's not recommended for applications with regular email
requirements because it adds complexity for no real gain.

Before continuing, it's important to have a look at the low level structure of
an email message. Consider a message which includes some content as both text
and HTML, a single PNG image embedded in those contents and a PDF file attached
to it. The MIME standard allows structuring this message in different ways, but
the following tree is the one that works on most email clients:

.. code-block:: text

    multipart/mixed
    ├── multipart/related
    │   ├── multipart/alternative
    │   │   ├── text/plain
    │   │   └── text/html
    │   └── image/png
    └── application/pdf

This is the purpose of each MIME message part:

* ``multipart/alternative``: used when two or more parts are alternatives of the
  same (or very similar) content. The preferred format must be added last.
* ``multipart/mixed``: used to send different content types in the same message,
  such as when attaching files.
* ``multipart/related``: used to indicate that each message part is a component
  of an aggregate whole. The most common usage is to display images embedded
  in the message contents.

When using the low-level :class:`Symfony\\Component\\Mime\\Message` class to
create the email message, you must keep all the above in mind to define the
different parts of the email by hand::

    use Symfony\Component\Mime\Header\Headers;
    use Symfony\Component\Mime\Message;
    use Symfony\Component\Mime\Part\Multipart\AlternativePart;
    use Symfony\Component\Mime\Part\TextPart;

    $headers = (new Headers())
        ->addMailboxListHeader('From', ['fabien@symfony.com'])
        ->addMailboxListHeader('To', ['foo@example.com'])
        ->addTextHeader('Subject', 'Important Notification')
    ;

    $textContent = new TextPart('Lorem ipsum...');
    $htmlContent = new TextPart('<h1>Lorem ipsum</h1> <p>...</p>', null, 'html');
    $body = new AlternativePart($textContent, $htmlContent);

    $email = new Message($headers, $body);

Embedding images and attaching files is possible by creating the appropriate
email multiparts::

    // ...
    use Symfony\Component\Mime\Part\DataPart;
    use Symfony\Component\Mime\Part\Multipart\MixedPart;
    use Symfony\Component\Mime\Part\Multipart\RelatedPart;

    // ...
    $embeddedImage = new DataPart(fopen('/path/to/images/logo.png', 'r'), null, 'image/png');
    $imageCid = $embeddedImage->getContentId();

    $attachedFile = new DataPart(fopen('/path/to/documents/terms-of-use.pdf', 'r'), null, 'application/pdf');

    $textContent = new TextPart('Lorem ipsum...');
    $htmlContent = new TextPart(sprintf(
        '<img src="cid:%s"/> <h1>Lorem ipsum</h1> <p>...</p>', $imageCid
    ), null, 'html');
    $bodyContent = new AlternativePart($textContent, $htmlContent);
    $body = new RelatedPart($bodyContent, $embeddedImage);

    $messageParts = new MixedPart($body, $attachedFile);

    $email = new Message($headers, $messageParts);

Serializing Email Messages
--------------------------

Email messages created with either the ``Email`` or ``Message`` classes can be
serialized because they are simple data objects::

    $email = (new Email())
        ->from('fabien@symfony.com')
        // ...
    ;

    $serializedEmail = serialize($email);

A common use case is to store serialized email messages, include them in a
message sent with the :doc:`Messenger component </components/messenger>` and
recreate them later when sending them. Use the
:class:`Symfony\\Component\\Mime\\RawMessage` class to recreate email messages
from their serialized contents::

    use Symfony\Component\Mime\RawMessage;

    // ...
    $serializedEmail = serialize($email);

    // later, recreate the original message to actually send it
    $message = new RawMessage(unserialize($serializedEmail));

MIME Types Utilities
--------------------

Although MIME was designed mainly for creating emails, the content types (also
known as `MIME types`_ and "media types") defined by MIME standards are also of
importance in communication protocols outside of email, such as HTTP. That's
why this component also provides utilities to work with MIME types.

The :class:`Symfony\\Component\\Mime\\MimeTypes` class transforms between
MIME types and file name extensions::

    use Symfony\Component\Mime\MimeTypes;

    $mimeTypes = new MimeTypes();
    $exts = $mimeTypes->getExtensions('application/javascript');
    // $exts = ['js', 'jsm', 'mjs']
    $exts = $mimeTypes->getExtensions('image/jpeg');
    // $exts = ['jpeg', 'jpg', 'jpe']

    $types = $mimeTypes->getMimeTypes('js');
    // $types = ['application/javascript', 'application/x-javascript', 'text/javascript']
    $types = $mimeTypes->getMimeTypes('apk');
    // $types = ['application/vnd.android.package-archive']

These methods return arrays with one or more elements. The element position
indicates its priority, so the first returned extension is the preferred one.

.. _components-mime-type-guess:

Guessing the MIME Type
~~~~~~~~~~~~~~~~~~~~~~

Another useful utility allows to guess the MIME type of any given file::

    use Symfony\Component\Mime\MimeTypes;

    $mimeTypes = new MimeTypes();
    $mimeType = $mimeTypes->guessMimeType('/some/path/to/image.gif');
    // Guessing is not based on the file name, so $mimeType will be 'image/gif'
    // only if the given file is truly a GIF image

Guessing the MIME type is a time-consuming process that requires inspecting
part of the file contents. Symfony applies multiple guessing mechanisms, one
of them based on the PHP `fileinfo extension`_. It's recommended to install
that extension to improve the guessing performance.

Adding a MIME Type Guesser
..........................

You can add your own MIME type guesser by creating a class that implements
:class:`Symfony\\Component\\Mime\\MimeTypeGuesserInterface`::

    namespace App;

    use Symfony\Component\Mime\MimeTypeGuesserInterface;

    class SomeMimeTypeGuesser implements MimeTypeGuesserInterface
    {
        public function isGuesserSupported(): bool
        {
            // return true when the guesser is supported (might depend on the OS for instance)
            return true;
        }

        public function guessMimeType(string $path): ?string
        {
            // inspect the contents of the file stored in $path to guess its
            // type and return a valid MIME type ... or null if unknown

            return '...';
        }
    }

MIME type guessers must be :ref:`registered as services <service-container-creating-service>`
and :doc:`tagged </service_container/tags>` with the ``mime.mime_type_guesser`` tag.
If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.

.. _`MIME`: https://en.wikipedia.org/wiki/MIME
.. _`MIME types`: https://en.wikipedia.org/wiki/Media_type
.. _`fileinfo extension`: https://www.php.net/fileinfo
