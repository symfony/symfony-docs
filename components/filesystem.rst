.. index::
   single: Filesystem

The Filesystem Component
========================

    The Filesystem component provides basic utilities for the filesystem.


Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/filesystem`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/filesystem).

.. include:: /components/require_autoload.rst.inc

Usage
-----

The :class:`Symfony\\Component\\Filesystem\\Filesystem` class is the unique
endpoint for filesystem operations::

    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

    $fs = new Filesystem();

    try {
        $fs->mkdir('/tmp/random/dir/'.mt_rand());
    } catch (IOExceptionInterface $e) {
        echo "An error occurred while creating your directory at ".$e->getPath();
    }

.. note::

    Methods :method:`Symfony\\Component\\Filesystem\\Filesystem::mkdir`,
    :method:`Symfony\\Component\\Filesystem\\Filesystem::exists`,
    :method:`Symfony\\Component\\Filesystem\\Filesystem::touch`,
    :method:`Symfony\\Component\\Filesystem\\Filesystem::remove`,
    :method:`Symfony\\Component\\Filesystem\\Filesystem::chmod`,
    :method:`Symfony\\Component\\Filesystem\\Filesystem::chown` and
    :method:`Symfony\\Component\\Filesystem\\Filesystem::chgrp` can receive a
    string, an array or any object implementing :phpclass:`Traversable` as
    the target argument.

mkdir
~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::mkdir` creates a directory.
On POSIX filesystems, directories are created with a default mode value
`0777`. You can use the second argument to set your own mode::

    $fs->mkdir('/tmp/photos', 0700);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

.. note::

    This function ignores already existing directories.

exists
~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::exists` checks for the
presence of all files or directories and returns ``false`` if a file is missing::

    // this directory exists, return true
    $fs->exists('/tmp/photos');

    // rabbit.jpg exists, bottle.png does not exists, return false
    $fs->exists(array('rabbit.jpg', 'bottle.png'));

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

copy
~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::copy` is used to copy
files. If the target already exists, the file is copied only if the source
modification date is later than the target. This behavior can be overridden by
the third boolean argument::

    // works only if image-ICC has been modified after image.jpg
    $fs->copy('image-ICC.jpg', 'image.jpg');

    // image.jpg will be overridden
    $fs->copy('image-ICC.jpg', 'image.jpg', true);

touch
~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::touch` sets access and
modification time for a file. The current time is used by default. You can set
your own with the second argument. The third argument is the access time::

    // set modification time to the current timestamp
    $fs->touch('file.txt');
    // set modification time 10 seconds in the future
    $fs->touch('file.txt', time() + 10);
    // set access time 10 seconds in the past
    $fs->touch('file.txt', time(), time() - 10);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

chown
~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::chown` is used to change
the owner of a file. The third argument is a boolean recursive option::

    // set the owner of the lolcat video to www-data
    $fs->chown('lolcat.mp4', 'www-data');
    // change the owner of the video directory recursively
    $fs->chown('/video', 'www-data', true);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

chgrp
~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::chgrp` is used to change
the group of a file. The third argument is a boolean recursive option::

    // set the group of the lolcat video to nginx
    $fs->chgrp('lolcat.mp4', 'nginx');
    // change the group of the video directory recursively
    $fs->chgrp('/video', 'nginx', true);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

chmod
~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::chmod` is used to change
the mode of a file. The fourth argument is a boolean recursive option::

    // set the mode of the video to 0600
    $fs->chmod('video.ogg', 0600);
    // change the mod of the src directory recursively
    $fs->chmod('src', 0700, 0000, true);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

remove
~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::remove` is used to remove
files, symlinks, directories easily::

    $fs->remove(array('symlink', '/path/to/directory', 'activity.log'));

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

rename
~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::rename` is used to rename
files and directories::

    // rename a file
    $fs->rename('/tmp/processed_video.ogg', '/path/to/store/video_647.ogg');
    // rename a directory
    $fs->rename('/tmp/files', '/path/to/store/files');

symlink
~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::symlink` creates a
symbolic link from the target to the destination. If the filesystem does not
support symbolic links, a third boolean argument is available::

    // create a symbolic link
    $fs->symlink('/path/to/source', '/path/to/destination');
    // duplicate the source directory if the filesystem
    // does not support symbolic links
    $fs->symlink('/path/to/source', '/path/to/destination', true);

readlink
~~~~~~~~

.. versionadded:: 3.2
    The :method:`Symfony\\Component\\Filesystem\\Filesystem::readlink` method was introduced in Symfony 3.2.

:method:`Symfony\\Component\\Filesystem\\Filesystem::readlink` read links targets.

PHP's ``readlink()`` function returns the target of a symbolic link. However, its behavior
is completely different under Windows and Unix. On Windows systems, ``readlink()``
resolves recursively the children links of a link until a final target is found. On
Unix-based systems ``readlink()`` only resolves the next link.

The :method:`Symfony\\Component\\Filesystem\\Filesystem::readlink` method provided
by the Filesystem component always behaves in the same way::

    // returns the next direct target of the link without considering the existence of the target
    $fs->readlink('/path/to/link');

    // returns its absolute fully resolved final version of the target (if there are nested links, they are resolved)
    $fs->readlink('/path/to/link', true);

Its behavior is the following::

    public function readlink($path, $canonicalize = false)

* When ``$canonicalize`` is ``false``:
    * if ``$path`` does not exist or is not a link, it returns ``null``.
    * if ``$path`` is a link, it returns the next direct target of the link without considering the existence of the target.

* When ``$canonicalize`` is ``true``:
    * if ``$path`` does not exist, it returns null.
    * if ``$path`` exists, it returns its absolute fully resolved final version.

makePathRelative
~~~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::makePathRelative` returns
the relative path of a directory given another one::

    // returns '../'
    $fs->makePathRelative(
        '/var/lib/symfony/src/Symfony/',
        '/var/lib/symfony/src/Symfony/Component'
    );
    // returns 'videos/'
    $fs->makePathRelative('/tmp/videos', '/tmp')

mirror
~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::mirror` mirrors a
directory::

    $fs->mirror('/path/to/source', '/path/to/target');

isAbsolutePath
~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::isAbsolutePath` returns
``true`` if the given path is absolute, ``false`` otherwise::

    // return true
    $fs->isAbsolutePath('/tmp');
    // return true
    $fs->isAbsolutePath('c:\\Windows');
    // return false
    $fs->isAbsolutePath('tmp');
    // return false
    $fs->isAbsolutePath('../dir');

dumpFile
~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::dumpFile` allows you to
dump contents to a file. It does this in an atomic manner: it writes a temporary
file first and then moves it to the new file location when it's finished.
This means that the user will always see either the complete old file or
complete new file (but never a partially-written file)::

    $fs->dumpFile('file.txt', 'Hello World');

The ``file.txt`` file contains ``Hello World`` now.

appendToFile
~~~~~~~~~~~~

.. versionadded:: 3.3
    The :method:`Symfony\\Component\\Filesystem\\Filesystem::appendToFile`
    method was introduced in Symfony 3.3.

:method:`Symfony\\Component\\Filesystem\\Filesystem::appendToFile` adds new
contents at the end of some file::

    $fs->appendToFile('logs.txt', 'Email sent to user@example.com');

If either the file or its containing directory doesn't exist, this method
creates them before appending the contents.

Error Handling
--------------

Whenever something wrong happens, an exception implementing
:class:`Symfony\\Component\\Filesystem\\Exception\\ExceptionInterface` or
:class:`Symfony\\Component\\Filesystem\\Exception\\IOExceptionInterface` is thrown.

.. note::

    An :class:`Symfony\\Component\\Filesystem\\Exception\\IOException` is
    thrown if directory creation fails.

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    filesystem/*

.. _`Packagist`: https://packagist.org/packages/symfony/filesystem
