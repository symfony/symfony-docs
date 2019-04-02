.. index::
   single: MIME
   single: MIME Messages
   single: Components; MIME

The Mime Component
==================

    The MIME component allows manipulating the MIME messages used to send emails.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/mime

Alternatively, you can clone the `<https://github.com/symfony/mime>`_ repository.

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

This purpose of this component is to create the email messages. Use the
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

.. TODO


Embedding Images
----------------

If you want to display images inside your email contents, you must embed them
instead of adding them as attachments. First, use the ``embed()`` or
``embedFromPath()`` method to add an image from a file or resource::

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
        ->html('<img src="cid:logo" /> ... <img src="cid:footer-signature" /> ...')
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

Message Headers
---------------

.. TODO


Raw Message Composition
-----------------------

Before continuing, it's important to have a look at the low level structure of
an email message. Consider a message which includes some content as both text
and HTML, a single PNG image included in those contents and a PDF file attached
to it. The MIME standard structures that message as the following tree:

.. code-block:: text

    multipart/mixed
    ├── multipart/related
    │   ├── multipart/alternative
    │   │   ├── text/plain
    │   │   └── text/html
    │   └── image/png
    └── application/pdf

This is the purpose of each MIME message part:

* ``multipart/alternative``: used when two or more parts are alternatives of the
  same (or very similar) content. The preferred format must be added last.
* ``multipart/mixed``: used to send different content types in the same message,
  such as when attaching files.
* ``multipart/related``: used to indicate that each message part is a component
  of an aggregate whole. The most common usage is to display images embedded
  in the message contents.

.. TODO

Learn More
----------

.. TODO: link to Twig integration, etc.

.. _`MIME`: https://en.wikipedia.org/wiki/MIME
