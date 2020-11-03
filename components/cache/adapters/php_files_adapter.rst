.. index::
    single: Cache Pool
    single: PHP Files Cache

.. _component-cache-files-adapter:

PHP Files Cache Adapter
=======================

Similarly to :ref:`Filesystem Adapter <component-cache-filesystem-adapter>`, this cache
implementation writes cache entries out to disk, but unlike the Filesystem cache adapter,
the PHP Files cache adapter writes and reads back these cache files *as native PHP code*.
For example, caching the value ``['my', 'cached', 'array']`` will write out a cache
file similar to the following::

    <?php return [

        // the cache item expiration
        0 => 9223372036854775807,

        // the cache item contents
        1 => [
            0 => 'my',
            1 => 'cached',
            2 => 'array',
        ],

    ];

.. note::

    This adapter requires turning on the ``opcache.enable`` php.ini setting.
    As cache items are included and parsed as native PHP code and due to the way `OPcache`_
    handles file includes, this adapter has the potential to be much faster than other
    filesystem-based caches.

.. caution::

    While it supports updates and because it is using OPcache as a backend, this adapter is
    better suited for append-mostly needs. Using it in other scenarios might lead to
    periodical reset of the OPcache memory, potentially leading to degraded performance.

The PhpFilesAdapter can optionally be provided a namespace, default cache lifetime, and cache
directory path as constructor arguments::

    use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

    $cache = new PhpFilesAdapter(

        // a string used as the subdirectory of the root cache directory, where cache
        // items will be stored
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the files are deleted)
        $defaultLifetime = 0,

        // the main cache directory (the application needs read-write permissions on it)
        // if none is specified, a directory is created inside the system temporary directory
        $directory = null
    );

.. note::

    This adapter implements :class:`Symfony\\Component\\Cache\\PruneableInterface`,
    allowing for manual :ref:`pruning of expired cache entries <component-cache-cache-pool-prune>` by
    calling its ``prune()`` method.

.. _`OPcache`: https://www.php.net/manual/en/book.opcache.php
