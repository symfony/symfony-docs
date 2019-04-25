.. index::
   single: MIME
   single: MIME Messages
   single: Components; MIME

The Mime Component
==================

    The MIME component allows manipulating the MIME messages used to send emails
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
  to have an absolute control over every single part of the email message.

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
        ->priority(1)
        ->subject('Important Notification')
        ->text('Lorem ipsum...')
        ->html('<h1>Lorem ipsum</h1> <p>...</p>')
    ;

This only purpose of this component is to create the email messages. Use the
:doc:`Mailer component </components/mailer>` to actually send them. In Symfony
applications, it's easier to use the :doc:`Mailer integration </email>`.

Email Addresses
---------------

All the methods that require email addresses (``from()``, ``to()``, etc.) accept
both strings and objects::

    // ...
    use Symfony\Component\Mime\Address;
    use Symfony\Component\Mime\NamedAddress;

    $email = (new Email())
        // email address as a simple string
        ->from('fabien@symfony.com')

        // email address as an object
        ->from(new Address('fabien@symfony.com'))

        // email address as an object (email clients will display the name
        // instead of the email address)
        ->from(new NamedAddress('fabien@symfony.com', 'Fabien'))

        // ...
    ;

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
----------------

The text and HTML contents of the email messages can be strings (usually the
result of rendering some template) or PHP resources::

    $email = (new Email())
        // ...
        // simple contents defined as a string
        ->text('Lorem ipsum...')
        ->html('<p>Lorem ipsum...</p>')

        // contents obtained from a PHP resource
        ->text(fopen('/path/to/emails/user_signup.txt', 'r'))
        ->html(fopen('/path/to/emails/user_signup.html', 'r'))
    ;

.. tip::

    You can also use Twig templates to render the HTML and text contents. Read
    the :ref:`mime-component-twig-integration` section later in this article to
    learn more.

Embedding Images
----------------

If you want to display images inside your email contents, you must embed them
instead of adding them as attachments. When using Twig to render the email
contents, as explained :ref:`later in this article <embedding-images-in-emails-with-twig>`
the images are embedded automatically. Otherwise, you need to embed them manually.

First, use the ``embed()`` or ``embedFromPath()`` method to add an image from a
file or resource::

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
        ->html('<img src="cid:logo"/> ... <img src="cid:footer-signature"/> ...')
    ;

File Attachments
----------------

Use the ``attachFromPath()`` method to attach files that exist in your file system::

    $email = (new Email())
        // ...
        ->attachFromPath('/path/to/documents/terms-of-use.pdf')
        // optionally you can tell email clients to display a custom name for the file
        ->attachFromPath('/path/to/documents/privacy.pdf', 'Privacy Policy')
        // optionally you can provide an explicit MIME type (otherwise it's guessed)
        ->attachFromPath('/path/to/documents/contract.doc', 'Contract', 'application/msword')
    ;

Alternatively you can use the ``attach()`` method to attach contents generated
with PHP resources::

    $email = (new Email())
        // ...
        ->attach(fopen('/path/to/documents/contract.doc', 'r'))
    ;

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
    $htmlContent = new TextPart('<h1>Lorem ipsum</h1> <p>...</p>', 'html');
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
    ), 'html');
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

.. _mime-component-twig-integration:

Twig Integration
----------------

The Mime component integrates with the :doc:`Twig template engine </templating>`
to provide advanced features such as CSS style inlining and support for HTML/CSS
frameworks to create complex HTML email messages.

Rendering Email Contents with Twig
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you define the contents of your email in Twig templates, use the
:class:`Symfony\\Bridge\\Twig\\Mime\\TemplatedEmail` class. This class extends
from the :class:`Symfony\\Component\\Mime\\Email` class explained above and adds
some utility methods for Twig templates::

    use Symfony\Bridge\Twig\Mime\TemplatedEmail;

    $email = (new TemplatedEmail())
        ->from('fabien@symfony.com')
        ->fo('foo@example.com')
        // ...

        // this method defines the path of the Twig template to render
        ->htmlTemplate('messages/user/signup.html.twig')

        // this method defines the parameters (name => value) passed to templates
        ->context([
            'expiration_date' => new \DateTime('+7 days'),
            'username' => 'foo',
        ])
    ;

Once the email object has been created, you must set up Twig to define where
templates are located and then, use the
:class:`Symfony\\Bridge\\Twig\\Mime\\BodyRenderer` class to render the template
and update the email message contents with the results. All this is done
automatically when using the component inside a Symfony application::

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

The last step is to create the Twig template used to render the contents:

.. code-block:: html+twig

    <h1>Welcome {{ username }}!</h1>

    <p>You signed up to our site using the following email:</p>
    <p><code>{{ email.to }}</code></p>

    <p><a href="{{ url('...') }}">Click here to activate your account</a></p>

The Twig template has access to any of the parameters passed in the ``context``
method of the ``TemplatedEmail`` class and also to a special variable called
``email``. This variable is an instance of the
:class:`Symfony\\Bridge\\Twig\\Mime\\WrappedTemplatedEmail` class which gives
access to some of the email message properties.

When the text content of the message is not defined explicitly, the
``BodyRenderer()`` class generates it automatically converting the HTML contents
into text. If you have `league/html-to-markdown`_ installed in your application,
it uses that to turn HTML into Markdown. Otherwise, it applies the
:phpfunction:`strip_tags` PHP function to the original HTML contents.

If you prefer to define the text content yourself, use the ``text()`` method
explained in the previous sections or the ``textTemplate()`` method provided by
the ``TemplatedEmail`` class::

    use Symfony\Bridge\Twig\Mime\TemplatedEmail;

    $email = (new TemplatedEmail())
        ->from('fabien@symfony.com')
        ->fo('foo@example.com')
        // ...

        ->textTemplate('messages/user/signup.txt.twig')
        ->htmlTemplate('messages/user/signup.html.twig')

        ->context([
            'expiration_date' => new \DateTime('+7 days'),
            'username' => 'foo',
        ])
    ;

.. _embedding-images-in-emails-with-twig:

Embedding Images in Emails with Twig
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of dealing with the ``<img src="cid: ..."/>`` syntax explained in the
previous sections, when using Twig to render email contents you can refer to
image files as usual. First, define a Twig namespace called ``images`` to
simplify things later::

    // ...

    $templateLoader = new FilesystemLoader(__DIR__.'/templates');
    $templatedLoader->addPath(__DIR__.'/images', 'images');
    $twig = new Environment($templateLoader);

Now, use the special ``email.image()`` Twig helper to embed the images inside
the email contents:

.. code-block:: html+twig

    {# '@images/' refers to the Twig namespace defined earlier #}
    <img src="{{ email.image('@images/logo.png') }}"/>

    <h1>Welcome {{ username }}!</h1>
    {# ... #}

Inlining CSS Styles in Emails with Twig
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Designing the HTML contents of an email is very different from designing a
normal HTML page. For starters, most email clients only support a subset of all
CSS features. In addition, popular email clients such as Gmail don't support
defining styles inside ``<style> ... </style>`` sections and you must **inline
all the CSS styles**.

CSS inlining means that every HTML tag must define a ``style`` attribute with
all its CSS styles. This not only increases the email byte size significantly
but also makes it impossible to manage for complex emails. That's why Twig
provides a ``CssInlinerExtension`` that automates everything for you. First,
install the Twig extension in your application:

.. code-block:: terminal

    $ composer require twig/cssinliner-extension

Now, enable the extension (this is done automatically in Symfony applications)::

    // ...
    use Twig\CssInliner\CssInlinerExtension;

    $loader = new FilesystemLoader(__DIR__.'/templates');
    $twig = new Environment($loader);
    $twig->addExtension(new CssInlinerExtension());

Finally, wrap the entire template contents with the ``inline_css`` filter:

.. code-block:: html+twig

    {% filter inline_css %}
        <style>
            {# here, define your CSS styles as usual #}
        </style>

        <h1>Welcome {{ username }}!</h1>
        {# ... #}
    {% endfilter %}

You can also define some or all CSS styles in external files and pass them as
arguments of the filter:

.. code-block:: html+twig

    {# '@css/' refers to the Twig namespace defined earlier #}
    {% filter inline_css('@css/mailing.css') %}
        <style>
            {# here, define your CSS styles as usual #}
        </style>

        <h1>Welcome {{ username }}!</h1>
        {# ... #}
    {% endfilter %}

Rendering Markdown Contents in Emails with Twig
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Twig provides another extension called ``MarkdownExtension`` that lets you
define the email contents using the `Markdown syntax`_. In addition to the
extension, you must also install a Markdown conversion library (the extension is
compatible with all the popular libraries):

.. code-block:: terminal

    $ composer require twig/markdown-extension

    # these libraries are compatible too: erusev/parsedown, michelf/php-markdown
    $ composer require league/commonmark

Now, enable the extension (this is done automatically in Symfony applications)::

    // ...
    use Twig\Markdown\MarkdownExtension;

    $loader = new FilesystemLoader(__DIR__.'/templates');
    $twig = new Environment($loader);
    $twig->addExtension(new MarkdownExtension());

Finally, use the ``markdown`` filter to convert parts or the entire email
contents from Markdown to HTML:

.. code-block:: twig

    {% filter markdown %}
        Welcome {{ username }}!
        =======================

        You signed up to our site using the following email:
        `{{ email.to }}`

        [Click here to activate your account]({{ url('...') }})
    {% endfilter %}

Using the Inky Email Templating Language with Twig
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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

    $ composer require twig/inky-extension

Now, enable the extension (this is done automatically in Symfony applications)::

    // ...
    use Twig\Inky\InkyExtension;

    $loader = new FilesystemLoader(__DIR__.'/templates');
    $twig = new Environment($loader);
    $twig->addExtension(new InkyExtension());

Finally, use the ``inky`` filter to convert parts or the entire email
contents from Inky to HTML:

.. code-block:: html+twig

    {% filter inky %}
        <container>
            <row class="header">
                <columns>
                    <spacer size="16"></spacer>
                    <h1 class="text-center">Welcome {{ username }}!</h1>
                </columns>

                {# ... #}
            </row>
        </container>
    {% endfilter %}

You can combine all filters to create complex email messages:

.. code-block:: twig

    {% filter inky|inline_css(source('@zurb/stylesheets/main.css')) %}
        {# ... #}
    {% endfilter %}

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

    $mimeTypes = $mimeTypes->getMimeTypes('js');
    // $mimeTypes = ['application/javascript', 'application/x-javascript', 'text/javascript']
    $mimeTypes = $mimeTypes->getMimeTypes('apk');
    // $mimeTypes = ['application/vnd.android.package-archive']

These methods return arrays with one or more elements. The element position
indicates its priority, so the first returned extension is the preferred one.

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

You can register your own MIME type guesser by creating a class that implements
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

.. _`MIME`: https://en.wikipedia.org/wiki/MIME
.. _`league/html-to-markdown`: https://github.com/thephpleague/html-to-markdown
.. _`Markdown syntax`: https://commonmark.org/
.. _`Inky`: https://foundation.zurb.com/emails.html
.. _`MIME types`: https://en.wikipedia.org/wiki/Media_type
.. _`fileinfo extension`: https://php.net/fileinfo
