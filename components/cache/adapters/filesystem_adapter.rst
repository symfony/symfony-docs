Filesystem Cache Adapter
========================

This adapter offers improved application performance for those who cannot install
tools like :doc:`APCu </components/cache/adapters/apcu_adapter>` or :doc:`Redis </components/cache/adapters/redis_adapter>` in their
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
    (:doc:`Apcu </components/cache/adapters/apcu_adapter>`, :doc:`Memcached </components/cache/adapters/memcached_adapter>`,
    and :doc:`Redis </components/cache/adapters/redis_adapter>`) or the database adapters
    (:doc:`Doctrine DBAL </components/cache/adapters/doctrine_dbal_adapter>`, :doc:`PDO </components/cache/adapters/pdo_adapter>`)
    are recommended.

.. note::

    This adapter implements :class:`Symfony\\Component\\Cache\\PruneableInterface`,
    enabling manual :ref:`pruning of expired cache items <component-cache-cache-pool-prune>`
    by calling its ``prune()`` method.

.. _filesystem-tag-aware-adapter:

Working with Tags
-----------------

In order to use tag-based invalidation, you can wrap your adapter in
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`, but it's often
more interesting to use the dedicated :class:`Symfony\\Component\\Cache\\Adapter\\FilesystemTagAwareAdapter`.
Since tag invalidation logic is implemented using links on filesystem, this
adapter offers better read performance when using tag-based invalidation::

    use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;

    $cache = new FilesystemTagAwareAdapter();


.. _`tmpfs`: https://wiki.archlinux.org/index.php/tmpfs
.. _`RAM disk solutions`: https://en.wikipedia.org/wiki/List_of_RAM_drive_software
