Cache Items
===========

Cache items are the information units stored in the cache as a key/value pair.
In the Cache component they are represented by the
:class:`Symfony\\Component\\Cache\\CacheItem` class.
They are used in both the Cache Contracts and the PSR-6 interfaces.

Cache Item Keys and Values
--------------------------

The **key** of a cache item is a plain string which acts as its
identifier, so it must be unique for each cache pool. You can freely choose the
keys, but they should only contain letters (A-Z, a-z), numbers (0-9) and the
``_`` and ``.`` symbols. Other common symbols (such as ``{ } ( ) / \ @ :``) are
reserved by the PSR-6 standard for future uses.

The **value** of a cache item can be any data represented by a type which is
serializable by PHP, such as basic types (string, integer, float, boolean, null),
arrays and objects.

Creating Cache Items
--------------------

The only way to create cache items is via cache pools. When using the Cache
Contracts, they are passed as arguments to the recomputation callback::

    // $cache pool object was created before
    $productsCount = $cache->get('stats.products_count', function (ItemInterface $item): string {
        // [...]
    });

When using PSR-6, they are created with the ``getItem($key)`` method of the cache
pool::

    // $cache pool object was created before
    $productsCount = $cache->getItem('stats.products_count');

Then, use the ``Psr\Cache\CacheItemInterface::set`` method to set the data stored
in the cache item (this step is done automatically when using the Cache Contracts)::

    // storing a simple integer
    $productsCount->set(4711);
    $cache->save($productsCount);

    // storing an array
    $productsCount->set([
        'category1' => 4711,
        'category2' => 2387,
    ]);
    $cache->save($productsCount);

The key and the value of any given cache item can be obtained with the
corresponding *getter* methods::

    $cacheItem = $cache->getItem('exchange_rate');
    // ...
    $key = $cacheItem->getKey();
    $value = $cacheItem->get();

Cache Item Expiration
~~~~~~~~~~~~~~~~~~~~~

By default, cache items are stored permanently. In practice, this "permanent
storage" can vary greatly depending on the type of cache being used, as
explained in the :doc:`/components/cache/cache_pools` article.

However, in some applications it's common to use cache items with a shorter
lifespan. Consider for example an application which caches the latest news just
for one minute. In those cases, use the ``expiresAfter()`` method to set the
number of seconds to cache the item::

    $latestNews->expiresAfter(60);  // 60 seconds = 1 minute

    // this method also accepts \DateInterval instances
    $latestNews->expiresAfter(DateInterval::createFromDateString('1 hour'));

Cache items define another related method called ``expiresAt()`` to set the
exact date and time when the item will expire::

    $mostPopularNews->expiresAt(new \DateTime('tomorrow'));

Cache Item Hits and Misses
--------------------------

Using a cache mechanism is important to improve the application performance, but
it should not be required to make the application work. In fact, the PSR-6 document
wisely states that caching errors should not result in application failures.

In practice with PSR-6, this means that the ``getItem()`` method always returns an
object which implements the ``Psr\Cache\CacheItemInterface`` interface, even when
the cache item doesn't exist. Therefore, you don't have to deal with ``null`` return
values and you can safely store in the cache values such as ``false`` and ``null``.

In order to decide if the returned object represents a value coming from the storage
or not, caches use the concept of hits and misses:

* **Cache Hits** occur when the requested item is found in the cache, its value
  is not corrupted or invalid and it hasn't expired;
* **Cache Misses** are the opposite of hits, so they occur when the item is not
  found in the cache, its value is corrupted or invalid for any reason or the
  item has expired.

Cache item objects define a boolean ``isHit()`` method which returns ``true``
for cache hits::

    $latestNews = $cache->getItem('latest_news');

    if (!$latestNews->isHit()) {
        // do some heavy computation
        $news = ...;
        $cache->save($latestNews->set($news));
    } else {
        $news = $latestNews->get();
    }
