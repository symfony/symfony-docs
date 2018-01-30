.. index::
    single: Cache Pool
    single: Filesystem Cache

.. _component-cache-filesystem-adapter:

Filesystem Cache Adapter
========================

This adapter offers improved application performance for those who cannot install
tools like :ref:`APCu <apcu-adapter>` or :ref:`Redis <redis-adapter>` in their
environment. It stores the cache item expiration and content as regular files in
a collection of directories on a locally mounted filesystem.

.. tip::

    The performance of this adapter can be greatly increased by utilizing a
    temporary, in-memory filesystem, such as `tmpfs`_ on Linux, or one of the
    many other `RAM disk solutions`_ available.

The FilesystemAdapter can optionally be provided a namespace, default cache lifetime,
and cache root path as constructor parameters::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new FilesystemAdapter(

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

.. caution::

    The overhead of filesystem IO often makes this adapter one of the *slower*
    choices. If throughput is paramount, the in-memory adapters
    (:ref:`Apcu <apcu-adapter>`, :ref:`Memcached <memcached-adapter>`, and
    :ref:`Redis <redis-adapter>`) or the database adapters
    (:ref:`Doctrine <doctrine-adapter>` and :ref:`PDO <pdo-doctrine-adapter>`)
    are recommended.

.. note::

    Since Symfony 3.4, this adapter implements
    :class:`Symfony\\Component\\Cache\\PruneableInterface`, enabling manual
    :ref:`pruning of expired cache items <component-cache-cache-pool-prune>` by
    calling its ``prune()`` method.

.. _`tmpfs`: https://wiki.archlinux.org/index.php/tmpfs
.. _`RAM disk solutions`: https://en.wikipedia.org/wiki/List_of_RAM_drive_software
