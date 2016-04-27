.. index::
   single: Cache Pool
   single: APC Cache, APCu Cache
   single: Doctrine Cache
   single: Redis Cache

Cache Pools
===========

Cache Pools are the logical repositories of cache items. They perform all the
common operations on items, such as saving them or looking for them. Cache pools
are independent from the actual cache implementation. Therefore, applications
can keep using the same cache pool even if the underlying cache mechanism
changes from a filesystem based cache to a Redis or database based cache.

Creating Cache Pools
--------------------

Cache Pools are created through the **cache adapters**, which are classes that
implement the :class:`Psr\\Cache\\CacheItemPoolInterface` interface. This
component provides several adapters ready to use in your applications.

Array Cache Adapter
~~~~~~~~~~~~~~~~~~~

This adapter is only useful for testing purposes because contents are stored in
memory and not persisted in any way. Besides, some features explained later are
not available, such as the deferred saves::

    use Symfony\Component\Cache\Adapter\ArrayAdapter;

    $cache = new ArrayAdapter($defaultLifetime = 0, $storeSerialized = true);

``defaultLifetime``
    **type**: integer, **default value**: ``0``
    The default lifetime, in seconds, applied to cache items that don't define
    their own lifetime. The default value (``0``) means an "infinite" lifetime,
    but this adapter destroys the cache once the current PHP execution finishes.

``storeSerialized``
    **type**: boolean, **default value**: ``true``
    If ``true``, the values saved in the cache are serialized before storing them.

Filesystem Cache Adapter
~~~~~~~~~~~~~~~~~~~~~~~~

This adapter is useful when you want to improve the application performance but
can't install tools like APC or Redis in the server. This adapter stores the
contents as regular files in a set of directories on the local file system::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new FilesystemAdapter($namespace = '', $defaultLifetime = 0, $directory = null);

``namespace``
    **type**: string, **default value**: ``''`` (an empty string)
    The subdirectory created inside the main cache directory (defined in the
    third argument) to store the cache items.

``defaultLifetime``
    **type**: integer, **default value**: ``0``
    The default lifetime, in seconds, applied to cache items that don't define
    their own lifetime. The default value (``0``) means an "infinite" lifetime,
    which this adapter respects because items are actually persisted.

``directory``
    **type**: string, **default value**: ``null``
    The directory where the cache items are stored as files. Make sure that this
    directory has read-write permissions for your application. If no directory
    is defined, a new directory called ``symfony-cache/`` is created in the
    system's temporary directory.

APCu Cache Adapter
~~~~~~~~~~~~~~~~~~

This adapter can increase the application performance very significantly, because
contents are cached in the memory of your server, which is much faster than the
file system. It requires to have installed and enabled the PHP APC extension.
It's not recommended to use it when performing lots of write and delete
operations because it produces fragmentation in the APCu memory and that
degrades performance significantly::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;

    $cache = new ApcuAdapter($namespace = '', $defaultLifetime = 0);

``namespace``
    **type**: string, **default value**: ``''`` (an empty string)
    The string prefixed to the keys of the items stored in this cache.

``defaultLifetime``
    **type**: integer, **default value**: ``0``
    The default lifetime, in seconds, applied to cache items that don't define
    their own lifetime. The default value (``0``) means an "infinite" lifetime,
    which in this adapter ends when the web server is restarted or the APC memory
    is deleted in any other way.

Redis Cache Adapter
~~~~~~~~~~~~~~~~~~~

This adapter, similarly to APCu adapter, can increase the application performance
very significantly, because contents are cached in the memory of your server. It
requires to have installed Redis and have created a connection that implements
the ``\Redis`` class::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    $cache = new RedisAdapter(\Redis $redisConnection, $namespace = '', $defaultLifetime = 0);

``redisConnection``
    **type**: ``\Redis``, **default value**: (none, this argument is mandatory)
    The object that represents a valid connection to your Redis system.

``namespace``
    **type**: string, **default value**: ``''`` (an empty string)
    The string prefixed to the keys of the items stored in this cache.

``defaultLifetime``
    **type**: integer, **default value**: ``0``
    The default lifetime, in seconds, applied to cache items that don't define
    their own lifetime. The default value (``0``) means an "infinite" lifetime,
    which in this adapter ends when the server is restarted or the Redis memory
    is deleted in any other way.

Chain Cache Adapter
~~~~~~~~~~~~~~~~~~~

This adapter allows to combine any number of the previous adapters. Cache items
are fetched from the first adapter which contains them. Besides, cache items are
saved in all the given adapters, so this is a quick way of creating a cache
replication::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;
    use Symfony\Component\Cache\Adapter\ChainAdapter;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $apcCache = new ApcuAdapter();
    $fileCache = new FilesystemAdapter();

    $cache = new ChainAdapter(array($apcCache, $fileCache));

The second optional argument of ``ChainAdapter`` is the ``maxLifetime`` (default
``0``) which is the maximum lifetime of items propagated from lower adapters to
upper ones.

.. TODO: I don't understand the previous phrase, which is copied from the ChainAdapter code.

Proxy Cache Adapter
~~~~~~~~~~~~~~~~~~~

.. TODO: what is this adapter useful for?

Doctrine Cache Adapter
~~~~~~~~~~~~~~~~~~~~~~

This adapter wraps any `Doctrine Cache`_ provider so you can use them in your
application as if they were Symfony Cache adapters::

    use Doctrine\Common\Cache\SQLite3Cache;
    use Symfony\Component\Cache\Adapter\DoctrineAdapter;

    $doctrineCache = new SQLite3(__DIR__.'/cache/data.sqlite');
    $symfonyCache = new DoctrineAdapter($doctrineCache);

This adapter also defines two optional arguments called  ``namespace`` (default:
``''``) and ``defaultLifetime`` (default: ``0``) and adapts them to make them
work in the underlying Doctrine cache.

Looking for Cache Items
-----------------------

Cache Pools define three methods to look for cache items. The most common method
is ``getItem($key)``, which returns the cache item identified by the given key::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new FilesystemAdapter('app.cache')
    $latestNews = $cache->getItem('latest_news');

If no item is defined for the given key, the method doesn't return a ``null``
value but an empty object which implements the :class:`Symfony\\Component\\Cache\\CacheItem`
class.

If you need to fetch several cache items simultaneously, use instead the
``getItems(array($key1, $key2, ...))`` method::

    // ...
    $stocks = $cache->getItems(array('AAPL', 'FB', 'GOOGL', 'MSFT'));

Again, if any of the keys doesn't represent a valid cache item, you won't get
a ``null`` value but an empty ``CacheItem`` object.

The last method related to fetching cache items is ``hasItem($key)``, which
returns ``true`` if there is a cache item identified by the given key::

    // ...
    $hasBadges = $cache->hasItem('user_'.$userId.'_badges');

Saving Cache Items
------------------

The most common method to save cache items is ``save($item)``, which stores the
item in the cache immediately (it returns ``true`` if the item was saved or
``false`` if some error occurred)::

    // ...
    $userFriends = $cache->get('user_'.$userId.'_friends');
    $userFriends->set($user->getFriends());
    $isSaved = $cache->save($userFriends);

Sometimes you may prefer to not save the objects immediately in order to
increase the application performance. In those cases, use the
``saveDeferred($item)`` method to mark cache items as "ready to be persisted"
and then call to ``commit()`` method when you are ready to persist them all::

    // ...
    $isQueued = $cache->saveDeferred($userFriends);
    // ...
    $isQueued = $cache->saveDeferred($userPreferences);
    // ...
    $isQueued = $cache->saveDeferred($userRecentProducts);
    // ...
    $isSaved = $cache->commit();

The ``saveDeferred()`` method returns ``true`` when the cache item has been
successfully added to the "persist queue" and ``false`` otherwise. The ``commit()``
method returns ``true`` when all the pending items are successfully saved or
``false`` otherwise.

Removing Cache Items
--------------------

Cache Pools include methods to delete a cache item, some of them or all of them.
The most common is ``deleteItem($key)``, which deletes the cache item identified
by the given key (it returns ``true`` when the item is successfully deleted or
doesn't exist and ``false`` otherwise)::

    // ...
    $isDeleted = $cache->deleteItem('user_'.$userId);

Use the ``deleteItems(array($key1, $key2, ...))`` method to delete several cache
items simultaneously (it returns ``true`` only if all the items have been deleted,
even when any or some of them don't exist)::

    // ...
    $areDeleted = $cache->deleteItems(array('category1', 'category2'));

Finally, to remove all the cache items stored in the pool, use the ``clear()``
method (which returns ``true`` when all items are successfully deleted)::

    // ...
    $cacheIsEmpty = $cache->clear();

.. _`Doctrine Cache`: https://github.com/doctrine/cache
