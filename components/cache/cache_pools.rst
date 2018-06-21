.. index::
    single: Cache Pool
    single: APC Cache, APCu Cache
    single: Array Cache
    single: Chain Cache
    single: Doctrine Cache
    single: Filesystem Cache
    single: Memcached Cache
    single: PDO Cache, Doctrine DBAL Cache
    single: Redis Cache

.. _component-cache-cache-pools:

Cache Pools and Supported Adapters
==================================

Cache Pools are the logical repositories of cache items. They perform all the
common operations on items, such as saving them or looking for them. Cache pools
are independent from the actual cache implementation. Therefore, applications
can keep using the same cache pool even if the underlying cache mechanism
changes from a file system based cache to a Redis or database based cache.

.. _component-cache-creating-cache-pools:

Creating Cache Pools
--------------------

Cache Pools are created through the **cache adapters**, which are classes that
implement :class:`Symfony\\Component\\Cache\\Adapter\\AdapterInterface`. This
component provides several adapters ready to use in your applications.

.. toctree::
    :glob:
    :maxdepth: 1

    adapters/*

Looking for Cache Items
-----------------------

Cache Pools define three methods to look for cache items. The most common method
is ``getItem($key)``, which returns the cache item identified by the given key::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new FilesystemAdapter('app.cache');
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

The most common method to save cache items is
``Psr\\Cache\\CacheItemPoolInterface::save``, which stores the
item in the cache immediately (it returns ``true`` if the item was saved or
``false`` if some error occurred)::

    // ...
    $userFriends = $cache->getItem('user_'.$userId.'_friends');
    $userFriends->set($user->getFriends());
    $isSaved = $cache->save($userFriends);

Sometimes you may prefer to not save the objects immediately in order to
increase the application performance. In those cases, use the
``Psr\\Cache\\CacheItemPoolInterface::saveDeferred`` method to mark cache
items as "ready to be persisted" and then call to
``Psr\\Cache\\CacheItemPoolInterface::commit`` method when you are ready
to persist them all::

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
The most common is ``Psr\\Cache\\CacheItemPoolInterface::deleteItem``,
which deletes the cache item identified by the given key (it returns ``true``
when the item is successfully deleted or doesn't exist and ``false`` otherwise)::

    // ...
    $isDeleted = $cache->deleteItem('user_'.$userId);

Use the ``Psr\\Cache\\CacheItemPoolInterface::deleteItems`` method to
delete several cache items simultaneously (it returns ``true`` only if all the
items have been deleted, even when any or some of them don't exist)::

    // ...
    $areDeleted = $cache->deleteItems(array('category1', 'category2'));

Finally, to remove all the cache items stored in the pool, use the
``Psr\\Cache\\CacheItemPoolInterface::clear`` method (which returns ``true``
when all items are successfully deleted)::

    // ...
    $cacheIsEmpty = $cache->clear();

.. tip::

    If the cache component is used inside a Symfony application, you can remove
    *all items* from the *given pool(s)* using the following command (which resides within
    the :ref:`framework bundle <framework-bundle-configuration>`):

    .. code-block:: terminal

        $ php bin/console cache:pool:clear <cache-pool-name>

        # clears the "cache.app" pool
        $ php bin/console cache:pool:clear cache.app

        # clears the "cache.validation" and "cache.app" pool
        $ php bin/console cache:pool:clear cache.validation cache.app

.. _component-cache-cache-pool-prune:

Pruning Cache Items
-------------------

Some cache pools do not include an automated mechanism for pruning expired cache items.
For example, the :ref:`FilesystemAdapter <component-cache-filesystem-adapter>` cache
does not remove expired cache items *until an item is explicitly requested and determined to
be expired*, for example, via a call to ``Psr\\Cache\\CacheItemPoolInterface::getItem``.
Under certain workloads, this can cause stale cache entries to persist well past their
expiration, resulting in a sizable consumption of wasted disk or memory space from excess,
expired cache items.

This shortcoming has been solved through the introduction of
:class:`Symfony\\Component\\Cache\\PruneableInterface`, which defines the abstract method
:method:`Symfony\\Component\\Cache\\PruneableInterface::prune`. The
:ref:`ChainAdapter <component-cache-chain-adapter>`,
:ref:`FilesystemAdapter <component-cache-filesystem-adapter>`,
:ref:`PdoAdapter <pdo-doctrine-adapter>`, and
:ref:`PhpFilesAdapter <component-cache-files-adapter>` all implement this new interface,
allowing manual removal of stale cache items::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new FilesystemAdapter('app.cache');
    // ... do some set and get operations
    $cache->prune();

The :ref:`ChainAdapter <component-cache-chain-adapter>` implementation does not directly
contain any pruning logic itself. Instead, when calling the chain adapter's
:method:`Symfony\\Component\\Cache\\ChainAdapter::prune` method, the call is delegated to all
its compatible cache adapters (and those that do not implement ``PruneableInterface`` are
silently ignored)::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;
    use Symfony\Component\Cache\Adapter\ChainAdapter;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\Adapter\PdoAdapter;
    use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

    $cache = new ChainAdapter(array(
        new ApcuAdapter(),       // does NOT implement PruneableInterface
        new FilesystemAdapter(), // DOES implement PruneableInterface
        new PdoAdapter(),        // DOES implement PruneableInterface
        new PhpFilesAdapter(),   // DOES implement PruneableInterface
        // ...
    ));

    // prune will proxy the call to PdoAdapter, FilesystemAdapter and PhpFilesAdapter,
    // while silently skipping ApcuAdapter
    $cache->prune();

.. tip::

    If the cache component is used inside a Symfony application, you can prune
    *all items* from *all pools* using the following command (which resides within
    the :ref:`framework bundle <framework-bundle-configuration>`):

    .. code-block:: terminal

        $ php bin/console cache:pool:prune
