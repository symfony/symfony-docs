.. index::
    single: Cache Pool
    single: APC Cache, APCu Cache

.. _apcu-adapter:

APCu Cache Adapter
==================

This adapter is a high-performance, shared memory cache. It can increase the
application performance very significantly because the cache contents are
stored in the shared memory of your server, a component that is much faster than
others, such as the file system.

.. caution::

    **Requirement:** The `APCu extension`_ must be installed and active to use
    this adapter.

This adapter can be provided an optional namespace string as its first parameter, a
default cache lifetime as its second parameter, and a version string as its third
parameter::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;

    $cache = new ApcuAdapter(

        // a string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the APCu memory is cleared)
        $defaultLifetime = 0,

        // when set, all keys prefixed by $namespace can be invalidated by changing
        // this $version string
        $version = null
    );

.. caution::

    It is *not* recommended to use this adapter when performing a large number of
    write and delete operations, as these operations result in fragmentation of the
    APCu memory, resulting in *significantly* degraded performance.

.. tip::

    Note that this adapters CRUD operations are specific to the PHP SAPI it is running
    under. This means adding a cache item using the CLI will not result in the item
    appearing under FPM. Likewise, deletion of an item using CGI will not result in the
    item being deleted under the CLI.

.. _`APCu extension`: https://pecl.php.net/package/APCu