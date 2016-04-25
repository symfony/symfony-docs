.. index::
   single: Cache Item
   single: Cache Expiration
   single: Cache Exceptions

Cache Items
===========

Cache items are each one of the information units stored in the cache as a
key/value pair. In the Cache component they are represented by the
:class:`Symfony\Component\Cache\CacheItem` class.

Cache Item Keys and Values
--------------------------

The **key** of a cache item is a UTF-8 encoded string which acts as its
identifier, so it must be unique for each cache pool. The PSR-6 standard limits
the key length to 64 characters, but Symfony allows to use longer keys (they are
encoded internally to reduce their size).

You can freely chose the keys, but they can only contain letters (A-Z, a-z),
numbers (0-9) and the ``_`` and ``.`` symbols. Other common symbols (such as
``{``, ``}``, ``(``, ``)``, ``/``, ``\`` and ``@``) are reserved for future uses.

The **value** of a cache item can be any data represented by a type which is
serializable by PHP, such as basic types (strings, integers, floats, boolean,
nulls), arrays and objects.

If it is not possible to return the exact saved value for any reason, implementing libraries MUST respond with a cache miss rather than corrupted data.

Creating Cache Items
--------------------

Cache items are created with the ``getItem($key)`` method of the cache pool. The
argument is the key of the item::

    // $cache pool object was created before
    $cachedNumProducts = $cache->getItem('stats.num_products');

Then, use the ``set($value)`` method to set the data stored in the cache item::

    // storing a simple integer
    $cachedNumProducts->set(4711);

    // storing an array
    $cachedNumProducts->set(array(
        'category1' => 4711,
        'category2' => 2387,
    ));

.. note::

    Creating a cache item and setting its value is not enough to save it in the
    cache. You must execute the ``save($cacheItem)`` method explicitly on the
    cache pool.

The key and the value of any given cache item can be obtained with the
corresponding *getter* methods::

    $cacheItem = $cache->getItem('logged_users');
    // ...
    $key = $cacheItem->getKey();
    $value = $cacheItem->get();

Cache Item Expiration
~~~~~~~~~~~~~~~~~~~~~

By default cache items are stored "permanently", which in practice means "as long
as allowed by the cache implementation used".

However, in some applications it's common to use cache items with a shorter
lifespan. Consider for example an application which caches the latest news just
for one minute. In those cases, use the ``expiresAfter()`` method to set the
number of seconds to cache the item::

    $latestNews = $cache->getItem('latest_news');
    $latestNews->expiresAfter(60);  // 60 seconds = 1 minute

    // this method also accepts \DateInterval instances
    $latestNews->expiresAfter(DateInterval::createFromDateString('1 hour'));

Cache items define another related method called ``expiresAt()`` to set the
exact date and time when the item will expire::

    $mostPopularNews = $cache->getItem('popular_news');
    $mostPopularNews->expiresAt(new \DateTime('tomorrow'));

Cache Item Hits and Misses
--------------------------

Using a cache mechanism is important to improve the application performance, but
it should not be required to make the application work. In fact, the Cache
standard states that caching errors should not result in application failures.

In practice this means that the ``getItem()`` method always returns an object
which implements the ``Psr\Cache\CacheItemInterface`` interface, even when the
cache item doesn't exist. Therefore, you don't have to deal with ``null`` values.

In order to decide if the returned object is correct or not, caches use the
concept of hits and misses:

* **Cache Hits** occur when the requested item is found in the cache, its value
  is not corrupted or invalid and it hasn't expired;
* **Cache Misses** are the opposite of hits, so they occur when the item is not
  found in the cache, its value is corrupted or invalid for any reason or the
  item has expired.

Cache item objects define a boolean ``isHit()`` method which returns ``true``
for cache hits::

    $latestNews = $cache->getItem('latest_news');
    $latestNews->expiresAfter(60);

    // check the item a few seconds after creating it
    $isHit = $latestNews->isHit(); // true

    // check the item 10 minutes after creating it
    $isHit = $latestNews->isHit(); // false
