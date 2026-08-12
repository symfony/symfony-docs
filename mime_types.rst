Working with MIME Types
=======================

`MIME`_ (Multipurpose Internet Mail Extensions) is an Internet standard that
was designed to extend the format of emails. The content types defined by MIME
standards (also known as `MIME types`_ and "media types") are also important
in communication protocols outside of email, such as HTTP. That's why Symfony
provides utilities to work with MIME types via its Mime component.

.. seealso::

    This article explains the MIME type utilities of the Mime component. To
    create and send email messages, read the :doc:`Mailer </mailer>` article.

Installation
------------

The Mime component is already installed in applications that use the
:doc:`Mailer </mailer>`. In any other application, run this command to
install it:

.. code-block:: terminal

    $ composer require symfony/mime

.. include:: /components/require_autoload.rst.inc

Converting MIME Types into File Extensions
------------------------------------------

The :class:`Symfony\\Component\\Mime\\MimeTypes` class transforms between
MIME types and file name extensions. In Symfony applications, inject this
object in your services using the
:class:`Symfony\\Component\\Mime\\MimeTypesInterface` type-hint; in any other
application, create it yourself:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Service/SomeService.php
        namespace App\Service;

        use Symfony\Component\Mime\MimeTypesInterface;

        class SomeService
        {
            public function __construct(
                private MimeTypesInterface $mimeTypes,
            ) {
            }

            // ...
        }

    .. code-block:: php-standalone

        use Symfony\Component\Mime\MimeTypes;

        $mimeTypes = new MimeTypes();

Use the ``getExtensions()`` and ``getMimeTypes()`` methods to transform
between MIME types and file name extensions::

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

Guessing the MIME Type of a File
--------------------------------

Another useful utility lets you guess the MIME type of any given file::

    $mimeType = $mimeTypes->guessMimeType('/some/path/to/image.gif');
    // Guessing is not based on the file name, so $mimeType will be 'image/gif'
    // only if the given file is truly a GIF image

Guessing the MIME type is a time-consuming process that requires inspecting
part of the file contents. Symfony applies multiple guessing mechanisms, one
of them based on the PHP `fileinfo extension`_. It's recommended to install
that extension to improve the guessing performance.

Adding a MIME Type Guesser
~~~~~~~~~~~~~~~~~~~~~~~~~~

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

In Symfony applications, MIME type guessers must be
:ref:`registered as services <service-container-creating-service>` and
:doc:`tagged </service_container/tags>` with the ``mime.mime_type_guesser``
tag. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.

In any other application, register the guesser explicitly (the most recently
registered guessers take precedence)::

    $mimeTypes->registerGuesser(new SomeMimeTypeGuesser());

.. _`MIME`: https://en.wikipedia.org/wiki/MIME
.. _`MIME types`: https://en.wikipedia.org/wiki/Media_type
.. _`fileinfo extension`: https://www.php.net/fileinfo
