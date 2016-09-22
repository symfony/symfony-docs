.. index::
   single: Cache; Invalidation
   single: Cache; Tags
   single: Cache; Hierarchical

Cache Invalidation
==================

Cache invalidation is the process of removing all cached items related to a
change in the state of your model. The most basic kind of invalidation is direct
items deletion. But when the state of a primary resource has spread accross
several cached items, keeping them in sync can be difficult.

The Symfony Cache component provides three mechanisms to help solve this problem:

* Tags based invalidation for managing data dependencies;
* Hierarchical invalidation for context dependent data;
* Expiration based invalidation for time related dependencies.

.. versionadded:: 3.2
    Tags based and hierarchical invalidations were introduced in Symfony 3.2.

Using Cache Tags
----------------

To benefit from tags based invalidation, you need to attach the proper tags to
each cached items. Each tag is a plain string identifier that you can use at any
time to trigger the removal of all items that had this tag attached to them.

To attach tags to cached items, you need to use the
:method:`Symfony\\Component\\Cache\\CacheItem::tag` method that is implemented by
cache items, as returned by cache adapters::

    $item = $cache->getItem('cache_key');
    // ...
    // add one or more tags
    $item->tag('tag_1');
    $item->tag(array('tag_2', 'tag_3'));
    $cache->save($item);

If ``$cache`` implements :class:`Symfony\\Component\\Cache\\TagAwareAdapterInterface`,
you can invalidate the cached items by calling
:method:`Symfony\\Component\\Cache\\TagAwareAdapterInterface::invalidateTags`::

    // invalidate all items related to `tag_2`
    $cache->invalidateTags('tag_2');

    // or invalidate all items related to `tag_1` or `tag_3`
    $cache->invalidateTags(array('tag_1', 'tag_3'));

    // if you know the cache key, you can of course delete directly
    $cache->deleteItem('cache_key');

Using tags invalidation is very useful when tracking cache keys becomes difficult.

Tag Aware Adapters
~~~~~~~~~~~~~~~~~~

To store tags, you need to wrap a cache adapter with the
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter` class or implement
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapterInterface` and its only
:method:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapterInterface::invalidateTags`
method.

The :class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter` class implements
instantaneous invalidation (time complexity is ``O(N)`` where ``N`` is the number
of invalidated tags). It needs one or two cache adapters: the first required
one is used to store cached items; the second optional one is used to store tags
and their invalidation version number (conceptually similar to their latest
invalidation date). When only one adapter is used, items and tags are all stored
in the same place. By using two adapters, you can e.g. store some big cached items
on the filesystem or in the database and keep tags in a Redis database to sync all
your fronts and have very fast invalidation checks::

    use Symfony\Component\Cache\Adapter\TagAwareAdapter;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\Adapter\RedisAdapter;

    $cache = new TagAwareAdapter(
        // Adapter for cached items
        new FilesystemAdapter(),
        // Adapter for tags
        new RedisAdapter('redis://localhost')
    );

Using Cache Hierarchies
-----------------------

By using adapters that implement
:class:`Symfony\\Component\\Cache\\Adapter\\ContextAwareAdapterInterface`,
you can create nested cache pools from existing ones. Each nested cache pool
deals with its own set of cached items, but clearing their parents clears them
recursively.

Nested cache pools are derivated from their parents by calling
:method:`Symfony\\Component\\Cache\\Adapter\\ContextAwareAdapterInterface::withContext`.
The method takes a single ``$context`` argument, which is a string that
identifies and isolates their cached items subsets. Appart from this, derivated
pools share everything with their parents, esp. any database connection they might
manage.

You can use such contextualized hierarchies everywhere you would otherwise
directly prefix your cache keys, this prefix being the "context" of the cached
items that use it in their keys.

For example, you could use hierachical pools to cache language dependent
variations of your content::

    $databaseCachePool = new PdoAdapter($pdoConnection);

    $lang = 'en';
    $enCachePool = $databaseCachePool->withContext($lang);

    $lang = 'fr';
    $frCachePool = $databaseCachePool->withContext($lang);

    // Get the same "front-page" item but from different lang context
    $enFrontPage = $enCachePool->getItem('front-page');
    $frFrontPage = $frCachePool->getItem('front-page');

    // This clears also $enCachePool and $frCachePool
    $databaseCachePool->clear();

.. note::

    Invalidating by tags affects both parents and children pools.

Using Cache Expiration
----------------------

If your data is valid only for a limited period of time, you can specify their
lifetime or their expiration date with the PSR-6 interface, as explained in the
:doc:`/components/cache/cache_items` article.
