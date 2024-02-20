Cache Invalidation
==================

Cache invalidation is the process of removing all cached items related to a
change in the state of your model. The most basic kind of invalidation is direct
item deletion. But when the state of a primary resource has spread across
several cached items, keeping them in sync can be difficult.

The Symfony Cache component provides two mechanisms to help solve this problem:

* :ref:`Tags-based invalidation <cache-component-tags>` for managing data dependencies;
* :ref:`Expiration based invalidation <cache-component-expiration>` for time-related dependencies.

.. _cache-component-tags:

Using Cache Tags
----------------

To benefit from tags-based invalidation, you need to attach the proper tags to
each cached item. Each tag is a plain string identifier that you can use at any
time to trigger the removal of all items associated with this tag.

To attach tags to cached items, you need to use the
:method:`Symfony\\Contracts\\Cache\\ItemInterface::tag` method that is implemented by
cache items::

    $item = $cache->get('cache_key', function (ItemInterface $item): string {
        // [...]
        // add one or more tags
        $item->tag('tag_1');
        $item->tag(['tag_2', 'tag_3']);

        return $cachedValue;
    });

If ``$cache`` implements :class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface`,
you can invalidate the cached items by calling
:method:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface::invalidateTags`::

    // invalidate all items related to `tag_1` or `tag_3`
    $cache->invalidateTags(['tag_1', 'tag_3']);

    // if you know the cache key, you can also delete the item directly
    $cache->delete('cache_key');

Using tag invalidation is very useful when tracking cache keys becomes difficult.

Tag Aware Adapters
~~~~~~~~~~~~~~~~~~

To store tags, you need to wrap a cache adapter with the
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter` class or implement
:class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface` and its
:method:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapterInterface::invalidateTags`
method.

.. note::

    When using a Redis backend, consider using :ref:`RedisTagAwareAdapter <redis-tag-aware-adapter>`
    which is optimized for this purpose. When using filesystem, likewise consider to use
    :ref:`FilesystemTagAwareAdapter <filesystem-tag-aware-adapter>`.

The :class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter` class implements
instantaneous invalidation (time complexity is ``O(N)`` where ``N`` is the number
of invalidated tags). It needs one or two cache adapters: the first required
one is used to store cached items; the second optional one is used to store tags
and their invalidation version number (conceptually similar to their latest
invalidation date). When only one adapter is used, items and tags are all stored
in the same place. By using two adapters, you can e.g. store some big cached items
on the filesystem or in the database and keep tags in a Redis database to sync all
your fronts and have very fast invalidation checks::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Adapter\TagAwareAdapter;

    $cache = new TagAwareAdapter(
        // Adapter for cached items
        new FilesystemAdapter(),
        // Adapter for tags
        new RedisAdapter('redis://localhost')
    );

.. note::

    :class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`
    implements :class:`Symfony\\Component\\Cache\\PruneableInterface`,
    enabling manual
    :ref:`pruning of expired cache entries <component-cache-cache-pool-prune>` by
    calling its :method:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter::prune`
    method (assuming the wrapped adapter itself implements
    :class:`Symfony\\Component\\Cache\\PruneableInterface`).

.. _cache-component-expiration:

Using Cache Expiration
----------------------

If your data is valid only for a limited period of time, you can specify their
lifetime or their expiration date with the PSR-6 interface, as explained in the
:doc:`/components/cache/cache_items` article.
