.. index::
   single: Cache
   single: Performance
   single: Components; Cache

.. _`cache-component`:

The Cache Component
===================

    The Cache component provides an extended `PSR-6`_ implementation as well as
    a `PSR-16`_ "Simple Cache" implementation for adding cache to your applications.
    It is designed for performance and resiliency, and ships with ready to use
    adapters for the most common caching backends, including proxies for adapting
    from/to `Doctrine Cache`_.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/cache

Alternatively, you can clone the `<https://github.com/symfony/cache>`_ repository.

.. include:: /components/require_autoload.rst.inc

Cache (PSR-6) Versus Simple Cache (PSR-16)
------------------------------------------

This component includes *two* different approaches to caching:

:ref:`PSR-6 Caching <cache-component-psr6-caching>`:
     A fully-featured cache system, which includes cache "pools", more advanced
     cache "items", and :ref:`cache tagging for invalidation <cache-component-tags>`.

:ref:`PSR-16 Simple Caching <cache-component-psr16-caching>`:
    A simple way to store, fetch and remove items from a cache.

Both methods support the *same* cache adapters and will give you very similar performance.

.. tip::

    The component also contains adapters to convert between PSR-6 and PSR-16 caches.
    See :doc:`/components/cache/psr6_psr16_adapters`.

.. _cache-component-psr16-caching:

Simple Caching (PSR-16)
-----------------------

This part of the component is an implementation of `PSR-16`_, which means that its
basic API is the same as defined in the standard. First, create a cache object from
one of the built-in cache classes. For example, to create a filesystem-based cache,
instantiate :class:`Symfony\\Component\\Cache\\Simple\\FilesystemCache`::

    use Symfony\Component\Cache\Simple\FilesystemCache;

    $cache = new FilesystemCache();

Now you can create, retrieve, update and delete items using this object::

    // save a new item in the cache
    $cache->set('stats.products_count', 4711);

    // or set it with a custom ttl
    // $cache->set('stats.products_count', 4711, 3600);

    // retrieve the cache item
    if (!$cache->has('stats.products_count')) {
        // ... item does not exists in the cache
    }

    // retrieve the value stored by the item
    $productsCount = $cache->get('stats.products_count');

    // or specify a default value, if the key doesn't exist
    // $productsCount = $cache->get('stats.products_count', 100);

    // remove the cache key
    $cache->delete('stats.products_count');

    // clear *all* cache keys
    $cache->clear();

You can also work with multiple items at once::

    $cache->setMultiple(array(
        'stats.products_count' => 4711,
        'stats.users_count' => 1356,
    ));

    $stats = $cache->getMultiple(array(
        'stats.products_count',
        'stats.users_count',
    ));

    $cache->deleteMultiple(array(
        'stats.products_count',
        'stats.users_count',
    ));

Available Simple Cache (PSR-16) Classes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The following cache adapters are available:

.. tip::

    To find out more about each of these classes, you can read the
    :doc:`PSR-6 Cache Pool </components/cache/cache_pools>` page. These "Simple"
    (PSR-16) cache classes aren't identical to the PSR-6 Adapters on that page, but
    each share constructor arguments and use-cases.

* :class:`Symfony\\Component\\Cache\\Simple\\ApcuCache`
* :class:`Symfony\\Component\\Cache\\Simple\\ArrayCache`
* :class:`Symfony\\Component\\Cache\\Simple\\ChainCache`
* :class:`Symfony\\Component\\Cache\\Simple\\DoctrineCache`
* :class:`Symfony\\Component\\Cache\\Simple\\FilesystemCache`
* :class:`Symfony\\Component\\Cache\\Simple\\MemcachedCache`
* :class:`Symfony\\Component\\Cache\\Simple\\NullCache`
* :class:`Symfony\\Component\\Cache\\Simple\\PdoCache`
* :class:`Symfony\\Component\\Cache\\Simple\\PhpArrayCache`
* :class:`Symfony\\Component\\Cache\\Simple\\PhpFilesCache`
* :class:`Symfony\\Component\\Cache\\Simple\\RedisCache`
* :class:`Symfony\\Component\\Cache\\Simple\\TraceableCache`

.. _cache-component-psr6-caching:

More Advanced Caching (PSR-6)
-----------------------------

To use the more-advanced, PSR-6 Caching abilities, you'll need to learn its key
concepts:

**Item**
    A single unit of information stored as a key/value pair, where the key is
    the unique identifier of the information and the value is its contents;
**Pool**
    A logical repository of cache items. All cache operations (saving items,
    looking for items, etc.) are performed through the pool. Applications can
    define as many pools as needed.
**Adapter**
    It implements the actual caching mechanism to store the information in the
    filesystem, in a database, etc. The component provides several ready to use
    adapters for common caching backends (Redis, APCu, Doctrine, PDO, etc.)

Basic Usage (PSR-6)
-------------------

This part of the component is an implementation of `PSR-6`_, which means that its
basic API is the same as defined in the standard. Before starting to cache information,
create the cache pool using any of the built-in adapters. For example, to create
a filesystem-based cache, instantiate :class:`Symfony\\Component\\Cache\\Adapter\\FilesystemAdapter`::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new FilesystemAdapter();

Now you can create, retrieve, update and delete items using this cache pool::

    // create a new item by trying to get it from the cache
    $productsCount = $cache->getItem('stats.products_count');

    // assign a value to the item and save it
    $productsCount->set(4711);
    $cache->save($productsCount);

    // retrieve the cache item
    $productsCount = $cache->getItem('stats.products_count');
    if (!$productsCount->isHit()) {
        // ... item does not exists in the cache
    }
    // retrieve the value stored by the item
    $total = $productsCount->get();

    // remove the cache item
    $cache->deleteItem('stats.products_count');

For a list of all of the supported adapters, see :doc:`/components/cache/cache_pools`.

Advanced Usage (PSR-6)
----------------------

.. toctree::
    :glob:
    :maxdepth: 1

    cache/*

.. _`PSR-6`: http://www.php-fig.org/psr/psr-6/
.. _`PSR-16`: http://www.php-fig.org/psr/psr-16/
.. _Doctrine Cache: https://www.doctrine-project.org/projects/cache.html
