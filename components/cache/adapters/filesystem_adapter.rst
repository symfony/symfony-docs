.. index::
    single: Cache Pool
    single: Filesystem Cache

Filesystem Cache Adapter
========================

This adapter is useful when you want to improve the application performance but
can't install tools like APCu or Redis on the server. This adapter stores the
contents as regular files in a set of directories on the local file system.

This adapter can optionally be provided a namespace, default cache lifetime, and
directory path, as its first, second, and third parameters::

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

.. tip::

    This adapter is generally the *slowest* due to the overhead of file IO. If throughput is paramount,
    the in-memory adapters (such as :ref:`APCu <apcu-adapter>`, :ref:`Memcached <memcached-adapter>`,
    and :ref:`Redis <redis-adapter>`) or the database adapters (such as
    :ref:`Doctrine <doctrine-adapter>` and :ref:`PDO & Doctrine <pdo-doctrine-adapter>`) are recommended.
