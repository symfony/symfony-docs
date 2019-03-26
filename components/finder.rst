.. index::
   single: Finder
   single: Components; Finder

The Finder Component
====================

    The Finder component finds files and directories based on different criteria
    (name, file size, modification time, etc.) via an intuitive fluent interface.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/finder

Alternatively, you can clone the `<https://github.com/symfony/finder>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

The :class:`Symfony\\Component\\Finder\\Finder` class finds files and/or
directories::

    use Symfony\Component\Finder\Finder;

    $finder = new Finder();
    // find all files in the current directory
    $finder->files()->in(__DIR__);

    // check if there are any search results
    if ($finder->hasResults()) {
        // ...
    }

    foreach ($finder as $file) {
        $absoluteFilePath = $file->getRealPath();
        $fileNameWithExtension = $file->getRelativePathname();

        // ...
    }

The ``$file`` variable is an instance of
:class:`Symfony\\Component\\Finder\\SplFileInfo` which extends PHP's own
:phpclass:`SplFileInfo` to provide methods to work with relative paths.

.. caution::

    The ``Finder`` object doesn't reset its internal state automatically.
    This means that you need to create a new instance if you do not want
    get mixed results.

Searching for Files and Directories
-----------------------------------

The component provides lots of methods to define the search criteria. They all
can be chained because they implement a `fluent interface`_.

Location
~~~~~~~~

The location is the only mandatory criteria. It tells the finder which
directory to use for the search::

    $finder->in(__DIR__);

Search in several locations by chaining calls to
:method:`Symfony\\Component\\Finder\\Finder::in`::

    // search inside *both* directories
    $finder->in([__DIR__, '/elsewhere']);

    // same as above
    $finder->in(__DIR__)->in('/elsewhere');

Use ``*`` as a wildcard character to search in the directories matching a
pattern (each pattern has to resolve to at least one directory path)::

    $finder->in('src/Symfony/*/*/Resources');

Exclude directories from matching with the
:method:`Symfony\\Component\\Finder\\Finder::exclude` method::

    // directories passed as argument must be relative to the ones defined with the in() method
    $finder->in(__DIR__)->exclude('ruby');

It's also possible to ignore directories that you don't have permission to read::

    $finder->ignoreUnreadableDirs()->in(__DIR__);

As the Finder uses PHP iterators, you can pass any URL with a supported
`PHP wrapper for URL-style protocols`_ (``ftp://``, ``zlib://``, etc.)::

    // always add a trailing slash when looking for in the FTP root dir
    $finder->in('ftp://example.com/');

    // you can also look for in a FTP directory
    $finder->in('ftp://example.com/pub/');

And it also works with user-defined streams::

    use Symfony\Component\Finder\Finder;

    // register a 's3://' wrapper with the official AWS SDK
    $s3Client = new Aws\S3\S3Client([/* config options */]);
    $s3Client->registerStreamWrapper();

    $finder = new Finder();
    $finder->name('photos*')->size('< 100K')->date('since 1 hour ago');
    foreach ($finder->in('s3://bucket-name') as $file) {
        // ... do something with the file
    }

.. seealso::

    Read the `PHP streams`_ documentation to learn how to create your own streams.

Files or Directories
~~~~~~~~~~~~~~~~~~~~

By default, the Finder returns files and directories; but the
:method:`Symfony\\Component\\Finder\\Finder::files` and
:method:`Symfony\\Component\\Finder\\Finder::directories` methods control that::

    // look for files only; ignore directories
    $finder->files();

    // look for directories only; ignore files
    $finder->directories();

If you want to follow `symbolic links`_, use the ``followLinks()`` method::

    $finder->files()->followLinks();

Version Control Files
~~~~~~~~~~~~~~~~~~~~~

`Version Control Systems`_ (or "VCS" for short), such as Git and Mercurial,
create some special files to store their metadata. Those files are ignored by
default when looking for files and directories, but you can change this with the
``ignoreVCS()`` method::

    $finder->ignoreVCS(false);

File Name
~~~~~~~~~

Find files by name with the
:method:`Symfony\\Component\\Finder\\Finder::name` method::

    $finder->files()->name('*.php');

The ``name()`` method accepts globs, strings, regexes or an array of globs,
strings or regexes::

    $finder->files()->name('/\.php$/');

Multiple filenames can be defined by chaining calls or passing an array::

    $finder->files()->name('*.php')->name('*.twig');

    // same as above
    $finder->files()->name(['*.php', '*.twig']);

The ``notName()`` method excludes files matching a pattern::

    $finder->files()->notName('*.rb');

Multiple filenames can be excluded by chaining calls or passing an array::

    $finder->files()->notName('*.rb')->notName('*.py');

    // same as above
    $finder->files()->notName(['*.rb', '*.py']);

File Contents
~~~~~~~~~~~~~

Find files by content with the
:method:`Symfony\\Component\\Finder\\Finder::contains` method::

    $finder->files()->contains('lorem ipsum');

The ``contains()`` method accepts strings or regexes::

    $finder->files()->contains('/lorem\s+ipsum$/i');

The ``notContains()`` method excludes files containing given pattern::

    $finder->files()->notContains('dolor sit amet');

Path
~~~~

Find files and directories by path with the
:method:`Symfony\\Component\\Finder\\Finder::path` method::

    // matches files that contain "data" anywhere in their paths (files or directories)
    $finder->path('data');
    // for example this will match data/*.xml and data.xml if they exist
    $finder->path('data')->name('*.xml');

Use the forward slash (i.e. ``/``) as the directory separator on all platforms,
including Windows. The component makes the necessary conversion internally.

The ``path()`` method accepts a string, a regular expression or an array of
strings or regulars expressions::

    $finder->path('foo/bar');
    $finder->path('/^foo\/bar/');

Multiple paths can be defined by chaining calls or passing an array::

    $finder->path('data')->path('foo/bar');

    // same as above
    $finder->path(['data', 'foo/bar']);

Internally, strings are converted into regular expressions by escaping slashes
and adding delimiters:

=====================  =======================
Original Given String  Regular Expression Used
=====================  =======================
``dirname``            ``/dirname/``
``a/b/c``              ``/a\/b\/c/``
=====================  =======================

The :method:`Symfony\\Component\\Finder\\Finder::notPath` method excludes files
by path::

    $finder->notPath('other/dir');

Multiple paths can be excluded by chaining calls or passing an array::

    $finder->notPath('first/dir')->notPath('other/dir');

    // same as above
    $finder->notPath(['first/dir', 'other/dir']);

.. versionadded:: 4.2

    Support for passing arrays to ``notPath()`` was introduced in Symfony
    4.2

File Size
~~~~~~~~~

Find files by size with the
:method:`Symfony\\Component\\Finder\\Finder::size` method::

    $finder->files()->size('< 1.5K');

Restrict by a size range by chaining calls or passing an array::

    $finder->files()->size('>= 1K')->size('<= 2K');

    // same as above
    $finder->files()->size(['>= 1K', '<= 2K']);

The comparison operator can be any of the following: ``>``, ``>=``, ``<``,
``<=``, ``==``, ``!=``.

The target value may use magnitudes of kilobytes (``k``, ``ki``), megabytes
(``m``, ``mi``), or gigabytes (``g``, ``gi``). Those suffixed with an ``i`` use
the appropriate ``2**n`` version in accordance with the `IEC standard`_.

File Date
~~~~~~~~~

Find files by last modified dates with the
:method:`Symfony\\Component\\Finder\\Finder::date` method::

    $finder->date('since yesterday');

Restrict by a date range by chaining calls or passing an array::

    $finder->date('>= 2018-01-01')->date('<= 2018-12-31');

    // same as above
    $finder->date(['>= 2018-01-01', '<= 2018-12-31']);

The comparison operator can be any of the following: ``>``, ``>=``, ``<``,
``<=``, ``==``. You can also use ``since`` or ``after`` as an alias for ``>``,
and ``until`` or ``before`` as an alias for ``<``.

The target value can be any date supported by :phpfunction:`strtotime`.

Directory Depth
~~~~~~~~~~~~~~~

By default, the Finder recursively traverses directories. Restrict the depth of
traversing with :method:`Symfony\\Component\\Finder\\Finder::depth`::

    $finder->depth('== 0');
    $finder->depth('< 3');

Restrict by a depth range by chaining calls or passing an array::

    $finder->depth('> 2')->depth('< 5');

    // same as above
    $finder->depth(['> 2', '< 5']);

Custom Filtering
~~~~~~~~~~~~~~~~

To filter results with your own strategy, use
:method:`Symfony\\Component\\Finder\\Finder::filter`::

    $filter = function (\SplFileInfo $file)
    {
        if (strlen($file) > 10) {
            return false;
        }
    };

    $finder->files()->filter($filter);

The ``filter()`` method takes a Closure as an argument. For each matching file,
it is called with the file as a :class:`Symfony\\Component\\Finder\\SplFileInfo`
instance. The file is excluded from the result set if the Closure returns
``false``.

Sorting Results
---------------

Sort the results by name or by type (directories first, then files)::

    $finder->sortByName();

    $finder->sortByType();

.. tip::

    By default, the ``sortByName()`` method uses the :phpfunction:`strcmp` PHP
    function (e.g. ``file1.txt``, ``file10.txt``, ``file2.txt``). Pass ``true``
    as its argument to use PHP's `natural sort order`_ algorithm instead (e.g.
    ``file1.txt``, ``file2.txt``, ``file10.txt``).

Sort the files and directories by the last accessed, changed or modified time::

    $finder->sortByAccessedTime();

    $finder->sortByChangedTime();

    $finder->sortByModifiedTime();

You can also define your own sorting algorithm with the ``sort()`` method::

    $finder->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
        return strcmp($a->getRealPath(), $b->getRealPath());
    });

You can reverse any sorting by using the ``reverseSorting()`` method::

    // results will be sorted "Z to A" instead of the default "A to Z"
    $finder->sortByName()->reverseSorting();

.. note::

    Notice that the ``sort*`` methods need to get all matching elements to do
    their jobs. For large iterators, it is slow.

Transforming Results into Arrays
--------------------------------

A Finder instance is an :phpclass:`IteratorAggregate` PHP class. So, in addition
to iterating over the Finder results with ``foreach``, you can also convert it
to an array with the :phpfunction:`iterator_to_array` function, or get the
number of items with :phpfunction:`iterator_count`.

If you call to the :method:`Symfony\\Component\\Finder\\Finder::in` method more
than once to search through multiple locations, pass ``false`` as a second
parameter to :phpfunction:`iterator_to_array` to avoid issues (a separate
iterator is created for each location and, if you don't pass ``false`` to
:phpfunction:`iterator_to_array`, keys of result sets are used and some of them
might be duplicated and their values overwritten).

Reading Contents of Returned Files
----------------------------------

The contents of returned files can be read with
:method:`Symfony\\Component\\Finder\\SplFileInfo::getContents`::

    use Symfony\Component\Finder\Finder;

    $finder = new Finder();
    $finder->files()->in(__DIR__);

    foreach ($finder as $file) {
        $contents = $file->getContents();

        // ...
    }

.. _`fluent interface`: https://en.wikipedia.org/wiki/Fluent_interface
.. _`symbolic links`: https://en.wikipedia.org/wiki/Symbolic_link
.. _`Version Control Systems`: https://en.wikipedia.org/wiki/Version_control
.. _`PHP wrapper for URL-style protocols`: https://php.net/manual/en/wrappers.php
.. _`PHP streams`: https://php.net/streams
.. _`IEC standard`: https://physics.nist.gov/cuu/Units/binary.html
.. _`Packagist`: https://packagist.org/packages/symfony/finder
.. _`natural sort order`: https://en.wikipedia.org/wiki/Natural_sort_order
