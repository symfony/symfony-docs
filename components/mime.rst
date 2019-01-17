.. index::
    single: MIME
    single: Components; MIME

The MIME Component
==================

    The MIME component provides utilities related to MIME types, such as
    guessing the MIME type of a given file.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/mime

Alternatively, you can clone the `<https://github.com/symfony/mime>`_ repository.

.. include:: /components/require_autoload.rst.inc

MIME Types Utilities
--------------------

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
indicates its priority (e.g. the first returned extension is the preferred one).

Guessing the MIME Type
----------------------

This component also guesses the MIME type of any given file::

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
~~~~~~~~~~~~~~~~~~~~~~~~~~

You can register your own MIME type guesser by creating a class that extends
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

.. _`fileinfo extension`: https://php.net/fileinfo
