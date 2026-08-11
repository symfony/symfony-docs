APCu Cache Adapter
==================

This adapter is a high-performance, shared memory cache. It can *significantly*
increase an application's performance, as its cache contents are stored in shared
memory, a component appreciably faster than many others, such as the filesystem.

.. warning::

    **Requirement:** The `APCu extension`_ must be installed and active to use
    this adapter.

The ApcuAdapter can optionally be provided a namespace, default cache lifetime,
and cache items version string as constructor arguments::

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

.. warning::

    Use of this adapter is discouraged in write/delete heavy workloads, as these
    operations cause memory fragmentation that results in significantly degraded performance.

.. tip::

    This adapter's CRUD operations are specific to the PHP SAPI it is running under. This
    means cache operations (such as additions, deletions, etc) using the CLI will not be
    available under the FPM or CGI SAPIs.

.. _`APCu extension`: https://pecl.php.net/package/APCu
