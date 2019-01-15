.. index::
    single: MIME
    single: Components; MIME

The MIME Component
==================

    The MIME component allows manipulating MIME types.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/mime

Alternatively, you can clone the `<https://github.com/symfony/mime>`_ repository.

.. include:: /components/require_autoload.rst.inc

.. tip::

    You should have the ``fileinfo`` PHP extension installed for greater performance.

MIME Types
----------

The :class:`Symfony\\Component\\Mime\\MimeTypes` class manipulates the
relationships between MIME types and file name extensions::

    use Symfony\Component\Mime\MimeTypes;

    $mimeTypes = new MimeTypes();
    $exts = $mimeTypes->getExtensions('application/mbox');
    // $exts contains an array of extensions: ['mbox']

    $mimeTypes = $mimeTypes->getMimeTypes('mbox');
    // $mimeTypes contains an array of MIME types: ['application/mbox']

    // guess a mime type for a file
    $mimeType = $mimeTypes->guessMimeType('/some/path/to/image.gif');

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
            // return a MIME type based on the content of the file stored in $path
            // you MUST not use the filename to determine the MIME type.
            return 'text/plain';
        }
    }
