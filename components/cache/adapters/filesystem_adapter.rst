.. index::
    single: Cache Pool
    single: Filesystem Cache

Filesystem Cache Adapter
========================

This adapter is useful when you want to improve the application performance but
can't install tools like APCu or Redis in the server. This adapter stores the
contents as regular files in a set of directories on the local file system::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new FilesystemAdapter(
        // the subdirectory of the main cache directory where cache items are stored
        $namespace = '',
        // in seconds; applied to cache items that don't define their own lifetime
        // 0 means to store the cache items indefinitely (i.e. until the files are deleted)
        $defaultLifetime = 0,
        // the main cache directory (the application needs read-write permissions on it)
        // if none is specified, a directory is created inside the system temporary directory
        $directory = null
    );
