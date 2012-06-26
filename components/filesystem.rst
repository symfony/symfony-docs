.. index::
   single: Filesystem

The Filesystem Component
========================

    The Filesystem components provides basic utilities for the filesystem.

.. versionadded:: 2.1
    The Filesystem Component is new to Symfony 2.1. Previously, the ``Filesystem``
    class was located in the ``HttpKernel`` component.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Filesystem);
* Install it via PEAR ( `pear.symfony.com/Filesystem`);
* Install it via Composer (`symfony/filesystem` on Packagist).

Usage
-----

The :class:`Symfony\\Component\\Filesystem\\Filesystem` class is the unique
endpoint for filesystem operations::

    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Filesystem\Exception\IOException;

    $fs = new Filesystem();

    try {
        $fs->mkdir('/tmp/random/dir/' . mt_rand());
    } catch (IOException $e) {
        echo "An error occured while creating your directory";
    }

.. note::

    Methods :method:`Symfony\\Component\\Filesystem\\Filesystem::mkdir`,
    :method:`Symfony\\Component\\Filesystem\\Filesystem::chown`,
    :method:`Symfony\\Component\\Filesystem\\Filesystem::chgrp`,
    :method:`Symfony\\Component\\Filesystem\\Filesystem::chown`,
    :method:`Symfony\\Component\\Filesystem\\Filesystem::remove` and
    :method:`Symfony\\Component\\Filesystem\\Filesystem::touch` can receive a
    string, an array or any object implementing :phpclass:`Traversable` as
    the target argument.


Mkdir
~~~~~

Mkdir creates directory. On posix filesystems, directories are created with a
default mode value `0777`. You can use the second argument to set your own mode::

    $fs->mkdir('/tmp/photos', 0700);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

Exists
~~~~~~

Exists checks for the presence of all files or directories and returns false if a
file is missing::

    // this directory exists, return true
    $fs->exists('/tmp/photos');

    // rabbit.jpg exists, bottle.png does not exists, return false
    $fs->exists(array('rabbit.jpg', 'bottle.png'));

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

Copy
~~~~

This method is used to copy files. If the target already exists, the file is
copied only if the source modification date is earlier than the target. This
behavior can be overridden by the third boolean argument::

    // works only if image-ICC has been modified after image.jpg
    $fs->copy('image-ICC.jpg', 'image.jpg');

    // image.jpg will be overridden
    $fs->copy('image-ICC.jpg', 'image.jpg', true);

Touch
~~~~~

Touch sets access and modification time for a file. The current time is used by
default. You can set your own with the second argument. The third argument is
the access time::

    // set modification time to the current timestamp
    $fs->touch('file.txt');
    // set modification time 10 seconds in the future
    $fs->touch('file.txt', time() + 10);
    // set access time 10 seconds in the past
    $fs->touch('file.txt', time(), time() - 10);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

Chown
~~~~~

Chown is used to change the owner of a file. The third argument is a boolean
recursive option::

    // set the owner of the lolcat video to www-data
    $fs->chown('lolcat.mp4', 'www-data');
    // change the owner of the video directory recursively
    $fs->chown('/video', 'www-data', true);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

Chgrp
~~~~~

Chgrp is used to change the group of a file. The third argument is a boolean
recursive option::

    // set the group of the lolcat video to nginx
    $fs->chgrp('lolcat.mp4', 'nginx');
    // change the group of the video directory recursively
    $fs->chgrp('/video', 'nginx', true);


.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

Chmod
~~~~~

Chmod is used to change the mode of a file. The third argument is a boolean
recursive option::

    // set the mode of the video to 0600
    $fs->chmod('video.ogg', 0600);
    // change the mod of the src directory recursively
    $fs->chmod('src', 0700, true);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

Remove
~~~~~~

Remove let's you remove files, symlink, directories easily::

    $fs->remove(array('symlink', '/path/to/directory', 'activity.log'));

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

Rename
~~~~~~

Rename is used to rename files and directories::

    //rename a file
    $fs->rename('/tmp/processed_video.ogg', '/path/to/store/video_647.ogg');
    //rename a directory
    $fs->rename('/tmp/files', '/path/to/store/files');

symlink
~~~~~~~

Creates a symbolic link from the target to the destination. If the filesystem
does not support symbolic links, a third boolean argument is available::

    // create a symbolic link
    $fs->symlink('/path/to/source', '/path/to/destination');
    // duplicate the source directory if the filesystem does not support symbolic links
    $fs->symlink('/path/to/source', '/path/to/destination', true);

makePathRelative
~~~~~~~~~~~~~~~~

Return the relative path of a directory given another one::

    // returns '../'
    $fs->makePathRelative('/var/lib/symfony/src/Symfony/', '/var/lib/symfony/src/Symfony/Component');
    // returns 'videos'
    $fs->makePathRelative('/tmp', '/tmp/videos');

mirror
~~~~~~

Mirrors a directory::

    $fs->mirror('/path/to/source', '/path/to/target');

isAbsolutePath
~~~~~~~~~~~~~~

isAbsolutePath returns true if the given path is absolute, false otherwise::

    // return true
    $fs->isAbsolutePath('/tmp');
    // return true
    $fs->isAbsolutePath('c:\\Windows');
    // return false
    $fs->isAbsolutePath('tmp');
    // return false
    $fs->isAbsolutePath('../dir');

Error Handling
--------------

Whenever something wrong happens, an exception implementing
:class:`Symfony\\Component\\Filesystem\\Exception\\ExceptionInterface` is
thrown.

.. note::

    Prior to version 2.1, :method:`Symfony\\Component\\Filesystem\\Filesystem::mkdir`
    returned a boolean and did not throw exceptions. As of 2.1, a
    :class:`Symfony\\Component\\Filesystem\\Exception\\IOException` is
    thrown if a directory creation fails.
