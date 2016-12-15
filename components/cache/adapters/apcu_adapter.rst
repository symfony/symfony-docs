.. index::
    single: Cache Pool
    single: APC Cache, APCu Cache

APCu Cache Adapter
==================

This adapter can increase the application performance very significantly,
because contents are cached in the shared memory of your server, which is much
faster than the file system. It requires to have installed and enabled the PHP
APCu extension. It's not recommended to use it when performing lots of write and
delete operations because it produces fragmentation in the APCu memory that can
degrade performance significantly::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;

    $cache = new ApcuAdapter(
        // the string prefixed to the keys of the items stored in this cache
        $namespace = '',
        // in seconds; applied to cache items that don't define their own lifetime
        // 0 means to store the cache items indefinitely (i.e. until the APC memory is deleted)
        $defaultLifetime = 0,
        // if present, this string is added to the namespace to simplify the
        // invalidation of the entire cache (e.g. when deploying the application)
        $version = null
    );
