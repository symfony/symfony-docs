.. index::
   single: Finder

How to locate Files
===================

The :namespace:`Symfony\\Component\\Finder` component helps you to find files
and directories quickly and easily.

Usage
-----

The :class:`Symfony\\Component\\Finder\\Finder` class finds files and/or
directories::

    use Symfony\Component\Finder\Finder;

    $finder = new Finder();
    $finder->files()->in(__DIR__);

    foreach ($finder as $file) {
        print $file->getRealpath()."\n";
    }

The ``$file`` is an instance of :phpclass:`SplFileInfo`.

The above code prints the names of all the files in the current directory
recursively. The Finder class uses a fluent interface, so all methods return
the Finder instance.

.. tip::

    A Finder instance is a PHP `Iterator`_. So, instead of iterating over the
    Finder with ``foreach``, you can also convert it to an array with the
    :phpfunction:`iterator_to_array` method, or get the number of items with
    :phpfunction:`iterator_count`.

Criteria
--------

Location
~~~~~~~~

The location is the only mandatory criteria. It tells the finder which
directory to use for the search::

    $finder->in(__DIR__);

Search in several locations by chaining calls to
:method:`Symfony\\Component\\Finder\\Finder::in`::

    $finder->files()->in(__DIR__)->in('/elsewhere');

Exclude directories from matching with the
:method:`Symfony\\Component\\Finder\\Finder::exclude` method::

    $finder->in(__DIR__)->exclude('ruby');

As the Finder uses PHP iterators, you can pass any URL with a supported
`protocol`_::

    $finder->in('ftp://example.com/pub/');

And it also works with user-defined streams::

    use Symfony\Component\Finder\Finder;

    $s3 = new \Zend_Service_Amazon_S3($key, $secret);
    $s3->registerStreamWrapper("s3");

    $finder = new Finder();
    $finder->name('photos*')->size('< 100K')->date('since 1 hour ago');
    foreach ($finder->in('s3://bucket-name') as $file) {
        // do something

        print $file->getFilename()."\n";
    }

.. note::

    Read the `Streams`_ documentation to learn how to create your own streams.

Files or Directories
~~~~~~~~~~~~~~~~~~~~~

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

.. note::

    Notice that the ``sort*`` methods need to get all matching elements to do
    their jobs. For large iterators, it is slow.

You can also define your own sorting algorithm with ``sort()`` method::

    $sort = function (\SplFileInfo $a, \SplFileInfo $b)
    {
        return strcmp($a->getRealpath(), $b->getRealpath());
    };

    $finder->sort($sort);

File Name
~~~~~~~~~

Restrict files by name with the
:method:`Symfony\\Component\\Finder\\Finder::name` method::

    $finder->files()->name('*.php');

The ``name()`` method accepts globs, strings, or regexes::

    $finder->files()->name('/\.php$/');

The ``notNames()`` method excludes files matching a pattern::

    $finder->files()->notName('*.rb');

File Size
~~~~~~~~~

Restrict files by size with the
:method:`Symfony\\Component\\Finder\\Finder::size` method::

    $finder->files()->size('< 1.5K');

Restrict by a size range by chaining calls::

    $finder->files()->size('>= 1K')->size('<= 2K');

The comparison operator can be any of the following: ``>``, ``>=``, ``<``, '<=',
'=='.

The target value may use magnitudes of kilobytes (``k``, ``ki``), megabytes
(``m``, ``mi``), or gigabytes (``g``, ``gi``). Those suffixed with an ``i`` use
the appropriate ``2**n`` version in accordance with the `IEC standard`_.

File Date
~~~~~~~~~

Restrict files by last modified dates with the
:method:`Symfony\\Component\\Finder\\Finder::date` method::

    $finder->date('since yesterday');

The comparison operator can be any of the following: ``>``, ``>=``, ``<``, '<=',
'=='. You can also use ``since`` or ``after`` as an alias for ``>``, and
``until`` or ``before`` as an alias for ``<``.

The target value can be any date supported by the `strtotime`_ function.

Directory Depth
~~~~~~~~~~~~~~~

By default, the Finder recursively traverse directories. Restrict the depth of
traversing with :method:`Symfony\\Component\\Finder\\Finder::depth`::

    $finder->depth('== 0');
    $finder->depth('< 3');

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
it is called with the file as a :phpclass:`SplFileInfo` instance. The file is
excluded from the result set if the Closure returns ``false``.

.. _strtotime:   http://www.php.net/manual/en/datetime.formats.php
.. _Iterator:     http://www.php.net/manual/en/spl.iterators.php
.. _protocol:     http://www.php.net/manual/en/wrappers.php
.. _Streams:      http://www.php.net/streams
.. _IEC standard: http://physics.nist.gov/cuu/Units/binary.html
