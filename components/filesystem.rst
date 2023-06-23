The Filesystem Component
========================

The Filesystem component provides platform-independent utilities for
filesystem operations and for file/directory paths manipulation.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/filesystem

.. include:: /components/require_autoload.rst.inc

Usage
-----

The component contains two main classes called :class:`Symfony\\Component\\Filesystem\\Filesystem`
and :class:`Symfony\\Component\\Filesystem\\Path`::

    use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Filesystem\Path;

    $filesystem = new Filesystem();

    try {
        $filesystem->mkdir(
            Path::normalize(sys_get_temp_dir().'/'.random_int(0, 1000)),
        );
    } catch (IOExceptionInterface $exception) {
        echo "An error occurred while creating your directory at ".$exception->getPath();
    }

Filesystem Utilities
--------------------

``mkdir``
~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::mkdir` creates a directory recursively.
On POSIX filesystems, directories are created with a default mode value
``0777``. You can use the second argument to set your own mode::

    $filesystem->mkdir('/tmp/photos', 0700);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

.. note::

    This function ignores already existing directories.

.. note::

    The directory permissions are affected by the current `umask`_.
    Set the ``umask`` for your webserver, use PHP's :phpfunction:`umask`
    function or use the :phpfunction:`chmod` function after the
    directory has been created.

``exists``
~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::exists` checks for the
presence of one or more files or directories and returns ``false`` if any of
them is missing::

    // if this absolute directory exists, returns true
    $filesystem->exists('/tmp/photos');

    // if rabbit.jpg exists and bottle.png does not exist, returns false
    // non-absolute paths are relative to the directory where the running PHP script is stored
    $filesystem->exists(['rabbit.jpg', 'bottle.png']);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

``copy``
~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::copy` makes a copy of a
single file (use :method:`Symfony\\Component\\Filesystem\\Filesystem::mirror` to
copy directories). If the target already exists, the file is copied only if the
source modification date is later than the target. This behavior can be overridden
by the third boolean argument::

    // works only if image-ICC has been modified after image.jpg
    $filesystem->copy('image-ICC.jpg', 'image.jpg');

    // image.jpg will be overridden
    $filesystem->copy('image-ICC.jpg', 'image.jpg', true);

``touch``
~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::touch` sets access and
modification time for a file. The current time is used by default. You can set
your own with the second argument. The third argument is the access time::

    // sets modification time to the current timestamp
    $filesystem->touch('file.txt');
    // sets modification time 10 seconds in the future
    $filesystem->touch('file.txt', time() + 10);
    // sets access time 10 seconds in the past
    $filesystem->touch('file.txt', time(), time() - 10);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

``chown``
~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::chown` changes the owner of
a file. The third argument is a boolean recursive option::

    // sets the owner of the lolcat video to www-data
    $filesystem->chown('lolcat.mp4', 'www-data');
    // changes the owner of the video directory recursively
    $filesystem->chown('/video', 'www-data', true);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

``chgrp``
~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::chgrp` changes the group of
a file. The third argument is a boolean recursive option::

    // sets the group of the lolcat video to nginx
    $filesystem->chgrp('lolcat.mp4', 'nginx');
    // changes the group of the video directory recursively
    $filesystem->chgrp('/video', 'nginx', true);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

``chmod``
~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::chmod` changes the mode or
permissions of a file. The fourth argument is a boolean recursive option::

    // sets the mode of the video to 0600
    $filesystem->chmod('video.ogg', 0600);
    // changes the mode of the src directory recursively
    $filesystem->chmod('src', 0700, 0000, true);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

``remove``
~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::remove` deletes files,
directories and symlinks::

    $filesystem->remove(['symlink', '/path/to/directory', 'activity.log']);

.. note::

    You can pass an array or any :phpclass:`Traversable` object as the first
    argument.

``rename``
~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::rename` changes the name
of a single file or directory::

    // renames a file
    $filesystem->rename('/tmp/processed_video.ogg', '/path/to/store/video_647.ogg');
    // renames a directory
    $filesystem->rename('/tmp/files', '/path/to/store/files');
    // if the target already exists, a third boolean argument is available to overwrite.
    $filesystem->rename('/tmp/processed_video2.ogg', '/path/to/store/video_647.ogg', true);

``symlink``
~~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::symlink` creates a
symbolic link from the target to the destination. If the filesystem does not
support symbolic links, a third boolean argument is available::

    // creates a symbolic link
    $filesystem->symlink('/path/to/source', '/path/to/destination');
    // duplicates the source directory if the filesystem
    // does not support symbolic links
    $filesystem->symlink('/path/to/source', '/path/to/destination', true);

``readlink``
~~~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::readlink` read links targets.

The :method:`Symfony\\Component\\Filesystem\\Filesystem::readlink` method
provided by the Filesystem component behaves in the same way on all operating
systems (unlike PHP's :phpfunction:`readlink` function)::

    // returns the next direct target of the link without considering the existence of the target
    $filesystem->readlink('/path/to/link');

    // returns its absolute fully resolved final version of the target (if there are nested links, they are resolved)
    $filesystem->readlink('/path/to/link', true);

Its behavior is the following:

* When ``$canonicalize`` is ``false``:

  * if ``$path`` does not exist or is not a link, it returns ``null``.
  * if ``$path`` is a link, it returns the next direct target of the link without considering the existence of the target.

* When ``$canonicalize`` is ``true``:

  * if ``$path`` does not exist, it returns null.
  * if ``$path`` exists, it returns its absolute fully resolved final version.

.. note::

    If you wish to canonicalize the path without checking its existence, you can
    use :method:`Symfony\\Component\\Filesystem\\Path::canonicalize` method instead.

``makePathRelative``
~~~~~~~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::makePathRelative` takes two
absolute paths and returns the relative path from the second path to the first one::

    // returns '../'
    $filesystem->makePathRelative(
        '/var/lib/symfony/src/Symfony/',
        '/var/lib/symfony/src/Symfony/Component'
    );
    // returns 'videos/'
    $filesystem->makePathRelative('/tmp/videos', '/tmp');

``mirror``
~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::mirror` copies all the
contents of the source directory into the target one (use the
:method:`Symfony\\Component\\Filesystem\\Filesystem::copy` method to copy single
files)::

    $filesystem->mirror('/path/to/source', '/path/to/target');

``isAbsolutePath``
~~~~~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::isAbsolutePath` returns
``true`` if the given path is absolute, ``false`` otherwise::

    // returns true
    $filesystem->isAbsolutePath('/tmp');
    // returns true
    $filesystem->isAbsolutePath('c:\\Windows');
    // returns false
    $filesystem->isAbsolutePath('tmp');
    // returns false
    $filesystem->isAbsolutePath('../dir');

``tempnam``
~~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::tempnam` creates a
temporary file with a unique filename, and returns its path, or throw an
exception on failure::

    // returns a path like : /tmp/prefix_wyjgtF
    $filesystem->tempnam('/tmp', 'prefix_');
    // returns a path like : /tmp/prefix_wyjgtF.png
    $filesystem->tempnam('/tmp', 'prefix_', '.png');

.. _filesystem-dumpfile:

``dumpFile``
~~~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::dumpFile` saves the given
contents into a file (creating the file and its directory if they don't exist).
It does this in an atomic manner: it writes a temporary file first and then moves
it to the new file location when it's finished. This means that the user will
always see either the complete old file or complete new file (but never a
partially-written file)::

    $filesystem->dumpFile('file.txt', 'Hello World');

The ``file.txt`` file contains ``Hello World`` now.

``appendToFile``
~~~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Filesystem\\Filesystem::appendToFile` adds new
contents at the end of some file::

    $filesystem->appendToFile('logs.txt', 'Email sent to user@example.com');
    // the third argument tells whether the file should be locked when writing to it
    $filesystem->appendToFile('logs.txt', 'Email sent to user@example.com', true);

If either the file or its containing directory doesn't exist, this method
creates them before appending the contents.

Path Manipulation Utilities
---------------------------

Dealing with file paths usually involves some difficulties:

- Platform differences: file paths look different on different platforms. UNIX
  file paths start with a slash ("/"), while Windows file paths start with a
  system drive ("C:"). UNIX uses forward slashes, while Windows uses backslashes
  by default.
- Absolute/relative paths: web applications frequently need to deal with absolute
  and relative paths. Converting one to the other properly is tricky and repetitive.

:class:`Symfony\\Component\\Filesystem\\Path` provides utility methods to tackle
those issues.

Canonicalization
~~~~~~~~~~~~~~~~

Returns the shortest path name equivalent to the given path. It applies the
following rules iteratively until no further processing can be done:

- "." segments are removed;
- ".." segments are resolved;
- backslashes ("\\") are converted into forward slashes ("/");
- root paths ("/" and "C:/") always terminate with a slash;
- non-root paths never terminate with a slash;
- schemes (such as "phar://") are kept;
- replace ``~`` with the user's home directory.

You can canonicalize a path with :method:`Symfony\\Component\\Filesystem\\Path::canonicalize`::

    echo Path::canonicalize('/var/www/vhost/webmozart/../config.ini');
    // => /var/www/vhost/config.ini

You can pass absolute paths and relative paths to the
:method:`Symfony\\Component\\Filesystem\\Path::canonicalize` method. When a
relative path is passed, ".." segments at the beginning of the path are kept::

    echo Path::canonicalize('../uploads/../config/config.yaml');
    // => ../config/config.yaml

Malformed paths are returned unchanged::

    echo Path::canonicalize('C:Programs/PHP/php.ini');
    // => C:Programs/PHP/php.ini

Converting Absolute/Relative Paths
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Absolute/relative paths can be converted with the methods
:method:`Symfony\\Component\\Filesystem\\Path::makeAbsolute`
and :method:`Symfony\\Component\\Filesystem\\Path::makeRelative`.

:method:`Symfony\\Component\\Filesystem\\Path::makeAbsolute` method expects a
relative path and a base path to base that relative path upon::

    echo Path::makeAbsolute('config/config.yaml', '/var/www/project');
    // => /var/www/project/config/config.yaml

If an absolute path is passed in the first argument, the absolute path is
returned unchanged::

    echo Path::makeAbsolute('/usr/share/lib/config.ini', '/var/www/project');
    // => /usr/share/lib/config.ini

The method resolves ".." segments, if there are any::

    echo Path::makeAbsolute('../config/config.yaml', '/var/www/project/uploads');
    // => /var/www/project/config/config.yaml

This method is very useful if you want to be able to accept relative paths (for
example, relative to the root directory of your project) and absolute paths at
the same time.

:method:`Symfony\\Component\\Filesystem\\Path::makeRelative` is the inverse
operation to :method:`Symfony\\Component\\Filesystem\\Path::makeAbsolute`::

    echo Path::makeRelative('/var/www/project/config/config.yaml', '/var/www/project');
    // => config/config.yaml

If the path is not within the base path, the method will prepend ".." segments
as necessary::

    echo Path::makeRelative('/var/www/project/config/config.yaml', '/var/www/project/uploads');
    // => ../config/config.yaml

Use :method:`Symfony\\Component\\Filesystem\\Path::isAbsolute` and
:method:`Symfony\\Component\\Filesystem\\Path::isRelative` to check whether a
path is absolute or relative::

    Path::isAbsolute('C:\Programs\PHP\php.ini')
    // => true

All four methods internally canonicalize the passed path.

Finding Longest Common Base Paths
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When you store absolute file paths on the file system, this leads to a lot of
duplicated information::

    return [
        '/var/www/vhosts/project/httpdocs/config/config.yaml',
        '/var/www/vhosts/project/httpdocs/config/routing.yaml',
        '/var/www/vhosts/project/httpdocs/config/services.yaml',
        '/var/www/vhosts/project/httpdocs/images/banana.gif',
        '/var/www/vhosts/project/httpdocs/uploads/images/nicer-banana.gif',
    ];

Especially when storing many paths, the amount of duplicated information is
noticeable. You can use :method:`Symfony\\Component\\Filesystem\\Path::getLongestCommonBasePath`
to check a list of paths for a common base path::

    Path::getLongestCommonBasePath(
        '/var/www/vhosts/project/httpdocs/config/config.yaml',
        '/var/www/vhosts/project/httpdocs/config/routing.yaml',
        '/var/www/vhosts/project/httpdocs/config/services.yaml',
        '/var/www/vhosts/project/httpdocs/images/banana.gif',
        '/var/www/vhosts/project/httpdocs/uploads/images/nicer-banana.gif'
    );
    // => /var/www/vhosts/project/httpdocs

Use this path together with :method:`Symfony\\Component\\Filesystem\\Path::makeRelative`
to shorten the stored paths::

    $bp = '/var/www/vhosts/project/httpdocs';

    return [
        $bp.'/config/config.yaml',
        $bp.'/config/routing.yaml',
        $bp.'/config/services.yaml',
        $bp.'/images/banana.gif',
        $bp.'/uploads/images/nicer-banana.gif',
    ];

:method:`Symfony\\Component\\Filesystem\\Path::getLongestCommonBasePath` always
returns canonical paths.

Use :method:`Symfony\\Component\\Filesystem\\Path::isBasePath` to test whether a
path is a base path of another path::

    Path::isBasePath("/var/www", "/var/www/project");
    // => true

    Path::isBasePath("/var/www", "/var/www/project/..");
    // => true

    Path::isBasePath("/var/www", "/var/www/project/../..");
    // => false

Finding Directories/Root Directories
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

PHP offers the function :phpfunction:`dirname` to obtain the directory path of a
file path. This method has a few quirks::

- ``dirname()`` does not accept backslashes on UNIX
- ``dirname("C:/Programs")`` returns "C:", not "C:/"
- ``dirname("C:/")`` returns ".", not "C:/"
- ``dirname("C:")`` returns ".", not "C:/"
- ``dirname("Programs")`` returns ".", not ""
- ``dirname()`` does not canonicalize the result

:method:`Symfony\\Component\\Filesystem\\Path::getDirectory` fixes these
shortcomings::

    echo Path::getDirectory("C:\Programs");
    // => C:/

Additionally, you can use :method:`Symfony\\Component\\Filesystem\\Path::getRoot`
to obtain the root of a path::

    echo Path::getRoot("/etc/apache2/sites-available");
    // => /

    echo Path::getRoot("C:\Programs\Apache\Config");
    // => C:/

Error Handling
--------------

Whenever something wrong happens, an exception implementing
:class:`Symfony\\Component\\Filesystem\\Exception\\ExceptionInterface` or
:class:`Symfony\\Component\\Filesystem\\Exception\\IOExceptionInterface` is thrown.

.. note::

    An :class:`Symfony\\Component\\Filesystem\\Exception\\IOException` is
    thrown if directory creation fails.

.. _`umask`: https://en.wikipedia.org/wiki/Umask
