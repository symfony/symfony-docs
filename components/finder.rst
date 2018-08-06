.. index::
   single: Finder
   single: Components; Finder

The Finder Component
====================

    The Finder component finds files and directories via an intuitive fluent
    interface.

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
    $finder->files()->in(__DIR__);

    foreach ($finder as $file) {
        // dumps the absolute path
        var_dump($file->getRealPath());

        // dumps the relative path to the file, omitting the filename
        var_dump($file->getRelativePath());

        // dumps the relative path to the file
        var_dump($file->getRelativePathname());
    }

The ``$file`` is an instance of :class:`Symfony\\Component\\Finder\\SplFileInfo`
which extends PHP's own :phpclass:`SplFileInfo` to provide methods to work with relative
paths.

The above code prints the names of all the files in the current directory
recursively. The Finder class uses a fluent interface, so all methods return
the Finder instance.

.. tip::

    A Finder instance is a PHP :phpclass:`Iterator`. So, in addition to iterating over the
    Finder with ``foreach``, you can also convert it to an array with the
    :phpfunction:`iterator_to_array` method, or get the number of items with
    :phpfunction:`iterator_count`.

.. caution::

    The ``Finder`` object doesn't reset its internal state automatically.
    This means that you need to create a new instance if you do not want
    get mixed results.

.. caution::

    When searching through multiple locations passed to the
    :method:`Symfony\\Component\\Finder\\Finder::in` method, a separate iterator
    is created internally for every location. This means we have multiple result
    sets aggregated into one.
    Since :phpfunction:`iterator_to_array` uses keys of result sets by default,
    when converting to an array, some keys might be duplicated and their values
    overwritten. This can be avoided by passing ``false`` as a second parameter
    to :phpfunction:`iterator_to_array`.

Criteria
--------

There are lots of ways to filter and sort your results. You can also use the
:method:`Symfony\\Component\\Finder\\Finder::hasResults` method to check if
there's any file or directory matching the search criteria.

Location
~~~~~~~~

The location is the only mandatory criteria. It tells the finder which
directory to use for the search::

    $finder->in(__DIR__);

Search in several locations by chaining calls to
:method:`Symfony\\Component\\Finder\\Finder::in`::

    // search inside *both* directories
    $finder->in(array(__DIR__, '/elsewhere'));

    // same as above
    $finder->in(__DIR__)->in('/elsewhere');

Use wildcard characters to search in the directories matching a pattern::

    $finder->in('src/Symfony/*/*/Resources');

Each pattern has to resolve to at least one directory path.

Exclude directories from matching with the
:method:`Symfony\\Component\\Finder\\Finder::exclude` method::

    // directories passed as argument must be relative to the ones defined with the in() method
    $finder->in(__DIR__)->exclude('ruby');

It's also possible to ignore directories that you don't have permission to read::

    $finder->ignoreUnreadableDirs()->in(__DIR__);

As the Finder uses PHP iterators, you can pass any URL with a supported
`protocol`_::

    // always add a trailing slash when looking for in the FTP root dir
    $finder->in('ftp://example.com/');

    // you can also look for in a FTP directory
    $finder->in('ftp://example.com/pub/');

And it also works with user-defined streams::

    use Symfony\Component\Finder\Finder;

    $s3 = new \Zend_Service_Amazon_S3($key, $secret);
    $s3->registerStreamWrapper('s3');

    $finder = new Finder();
    $finder->name('photos*')->size('< 100K')->date('since 1 hour ago');
    foreach ($finder->in('s3://bucket-name') as $file) {
        // ... do something with the file
    }

.. note::

    Read the `Streams`_ documentation to learn how to create your own streams.

Files or Directories
~~~~~~~~~~~~~~~~~~~~

By default, the Finder returns files and directories; but the
:method:`Symfony\\Component\\Finder\\Finder::files` and
:method:`Symfony\\Component\\Finder\\Finder::directories` methods control that::

    $finder->files();

    $finder->directories();

If you want to follow links, use the ``followLinks()`` method::

    $finder->files()->followLinks();

By default, the iterator ignores popular VCS files. This can be changed with
the ``ignoreVCS()`` method::

    $finder->ignoreVCS(false);

Sorting
~~~~~~~

Sort the result by name or by type (directories first, then files)::

    $finder->sortByName();

    $finder->sortByType();

.. tip::

    By default, the ``sortByName()`` method uses the :phpfunction:`strcmp` PHP
    function (e.g. ``file1.txt``, ``file10.txt``, ``file2.txt``). Pass ``true``
    as its argument to use PHP's `natural sort order`_ algorithm instead (e.g.
    ``file1.txt``, ``file2.txt``, ``file10.txt``).

    .. versionadded:: 4.2
        The option to use the natural sort order was introduced in Symfony 4.2.

You can also define your own sorting algorithm with ``sort()`` method::

    $finder->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
        return strcmp($a->getRealPath(), $b->getRealPath());
    });

.. note::

    Notice that the ``sort*`` methods need to get all matching elements to do
    their jobs. For large iterators, it is slow.

File Name
~~~~~~~~~

Restrict files by name with the
:method:`Symfony\\Component\\Finder\\Finder::name` method::

    $finder->files()->name('*.php');

The ``name()`` method accepts globs, strings, regexes or an array of globs,
strings or regexes::

    $finder->files()->name('/\.php$/');

Multiple filenames can be defined by chaining calls or passing an array::

    $finder->files()->name('*.php')->name('*.twig');

    // same as above
    $finder->files()->name(array('*.php', '*.twig'));

The ``notName()`` method excludes files matching a pattern::

    $finder->files()->notName('*.rb');

Multiple filenames can be excluded by chaining calls or passing an array::

    $finder->files()->notName('*.rb')->notName('*.py');

    // same as above
    $finder->files()->notName(array('*.rb', '*.py'));

.. versionadded:: 4.2
    Support for passing arrays to ``name()`` and ``notName()`` was introduced
    in Symfony 4.2

File Contents
~~~~~~~~~~~~~

Restrict files by contents with the
:method:`Symfony\\Component\\Finder\\Finder::contains` method::

    $finder->files()->contains('lorem ipsum');

The ``contains()`` method accepts strings or regexes::

    $finder->files()->contains('/lorem\s+ipsum$/i');

The ``notContains()`` method excludes files containing given pattern::

    $finder->files()->notContains('dolor sit amet');

Path
~~~~

Restrict files and directories by path with the
:method:`Symfony\\Component\\Finder\\Finder::path` method::

    // matches files that contain "data" anywhere in their paths (files or directories)
    $finder->path('data');
    // for example this will match data/*.xml and data.xml if they exist
    $finder->path('data')->name('*.xml');

On all platforms slash (i.e. ``/``) should be used as the directory separator.

The ``path()`` method accepts a string, a regular expression or an array of
strings or regulars expressions::

    $finder->path('foo/bar');
    $finder->path('/^foo\/bar/');

Multiple paths can be defined by chaining calls or passing an array::

    $finder->path('data')->path('foo/bar');

    // same as above
    $finder->path(array('data', 'foo/bar'));

.. versionadded:: 4.2
    Passing an array as parameter for method ``path()`` and ``notPath()`` was
    introduced in Symfony 4.2

Internally, strings are converted into regular expressions by escaping slashes
and adding delimiters:

.. code-block:: text

    dirname    ===>    /dirname/
    a/b/c      ===>    /a\/b\/c/

The :method:`Symfony\\Component\\Finder\\Finder::notPath` method excludes files by path::

    $finder->notPath('other/dir');

Multiple paths can be excluded by chaining calls or passing an array::

    $finder->notPath('first/dir')->notPath('other/dir');

    // same as above
    $finder->notPath(array('first/dir', 'other/dir'));

.. versionadded:: 4.2
    Passing an array as method parameter was introduced in Symfony 4.2

File Size
~~~~~~~~~

Restrict files by size with the
:method:`Symfony\\Component\\Finder\\Finder::size` method::

    $finder->files()->size('< 1.5K');

Restrict by a size range by chaining calls or passing an array::

    $finder->files()->size('>= 1K')->size('<= 2K');

    // same as above
    $finder->files()->size(array('>= 1K', '<= 2K'));

.. versionadded:: 4.2
    Passing an array as method parameter was introduced in Symfony 4.2

The comparison operator can be any of the following: ``>``, ``>=``, ``<``, ``<=``,
``==``, ``!=``.

The target value may use magnitudes of kilobytes (``k``, ``ki``), megabytes
(``m``, ``mi``), or gigabytes (``g``, ``gi``). Those suffixed with an ``i`` use
the appropriate ``2**n`` version in accordance with the `IEC standard`_.

File Date
~~~~~~~~~

Restrict files by last modified dates with the
:method:`Symfony\\Component\\Finder\\Finder::date` method::

    $finder->date('since yesterday');

Restrict by a date range by chaining calls or passing an array::

    $finder->date('>= 2018-01-01')->size('<= 2018-12-31');

    // same as above
    $finder->date(array('>= 2018-01-01', '<= 2018-12-31'));

.. versionadded:: 4.2
    Passing an array as method parameter was introduced in Symfony 4.2

The comparison operator can be any of the following: ``>``, ``>=``, ``<``, ``<=``,
``==``. You can also use ``since`` or ``after`` as an alias for ``>``, and
``until`` or ``before`` as an alias for ``<``.

The target value can be any date supported by the `strtotime`_ function.

Directory Depth
~~~~~~~~~~~~~~~

By default, the Finder recursively traverse directories. Restrict the depth of
traversing with :method:`Symfony\\Component\\Finder\\Finder::depth`::

    $finder->depth('== 0');
    $finder->depth('< 3');

Restrict by a depth range by chaining calls or passing an array::

    $finder->depth('> 2')->depth('< 5');

    // same as above
    $finder->depth(array('> 2', '< 5'));

.. versionadded:: 4.2
    Passing an array as method parameter was introduced in Symfony 4.2

Custom Filtering
~~~~~~~~~~~~~~~~

To restrict the matching file with your own strategy, use
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

Reading Contents of Returned Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The contents of returned files can be read with
:method:`Symfony\\Component\\Finder\\SplFileInfo::getContents`::

    use Symfony\Component\Finder\Finder;

    $finder = new Finder();
    $finder->files()->in(__DIR__);

    foreach ($finder as $file) {
        $contents = $file->getContents();

        // ...
    }

.. _strtotime:          https://php.net/manual/en/datetime.formats.php
.. _protocol:           https://php.net/manual/en/wrappers.php
.. _Streams:            https://php.net/streams
.. _IEC standard:       https://physics.nist.gov/cuu/Units/binary.html
.. _Packagist:          https://packagist.org/packages/symfony/finder
.. _`natural sort order`: https://en.wikipedia.org/wiki/Natural_sort_order
