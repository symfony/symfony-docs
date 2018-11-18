.. index::
   single: Cache
   single: Performance
   single: Components; Cache

.. _`cache-component`:

The Cache Component
===================

    The Cache component provides features covering simple to advanced caching needs.
    It natively implements `PSR-6`_ and the `Cache Contract`_ for greatest
    interoperability. It is designed for performance and resiliency, ships with
    ready to use adapters for the most common caching backends, including proxies for
    adapting from/to `Doctrine Cache`_ and `PSR-16`_. It enables tag-based invalidation
    and cache stampede protection.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/cache

Alternatively, you can clone the `<https://github.com/symfony/cache>`_ repository.

.. include:: /components/require_autoload.rst.inc

Cache Contracts versus PSR-6
----------------------------

This component includes *two* different approaches to caching:

:ref:`PSR-6 Caching <cache-component-psr6-caching>`:
     A generic cache system, which involves cache "pools" and cache "items".

:ref:`Cache Contracts <cache-component-contracts>`:
    A simple yet powerful way to store, fetch and remove values from a cache.

.. tip::

    Using the Cache Contracts approach is recommended: using it requires less
    code boilerplate and provides cache stampede protection by default.

.. tip::

    The component also contains adapters to convert between PSR-6, PSR-16 and
    Doctrine caches. See :doc:`/components/cache/psr6_psr16_adapters` and
    :doc:`/components/cache/adapters/doctrine_adapter`.

Available Cache Adapters
~~~~~~~~~~~~~~~~~~~~~~~~

The following cache adapters are available:

.. tip::

    To find out more about each of these classes, you can read the
    :doc:`PSR-6 Cache Pool </components/cache/cache_pools>` page.

* :class:`Symfony\\Component\\Cache\\Adapter\\ApcuAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\ArrayAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\ChainAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\DoctrineAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\FilesystemAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\MemcachedAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\NullAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\PdoAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\PhpArrayAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\PhpFilesAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\RedisAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\SimpleCacheAdapter`
* :class:`Symfony\\Component\\Cache\\Adapter\\TraceableAdapter`

.. _cache-component-psr6-caching:

More Generic Caching (PSR-6)
----------------------------

To use the more-generic, PSR-6 Caching abilities, you'll need to learn its key
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

Advanced Usage
--------------

.. toctree::
    :glob:
    :maxdepth: 1

    cache/*

.. _`PSR-6`: http://www.php-fig.org/psr/psr-6/
.. _`PSR-16`: http://www.php-fig.org/psr/psr-16/
.. _Doctrine Cache: https://www.doctrine-project.org/projects/cache.html
