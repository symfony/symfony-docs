.. index::
   single: Cache
   single: Performance
   single: Components; Cache

The Cache Component
===================

    The Cache component provides a strict `PSR-6`_ implementation for adding
    cache to your applications. It is designed to have a low overhead and it
    ships with ready to use adapters for the most common caching backends.

.. versionadded:: 3.1
    The Cache component was introduced in Symfony 3.1.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/cache`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/cache).

Key Concepts
------------

Before starting to use the Cache component, it's important that you learn the
meaning of some key concepts:

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
    adapters for common caching backends (Redis, APCu, Doctrine, Filesystem, PDO)

Basic Usage
-----------

This component is a strict implementation of `PSR-6`_, which means that the API
is the same as defined in the standard. Before starting to cache information,
create the cache pool using any of the built-in adapters. For example, to create
a filesystem-based cache, instantiate :class:`Symfony\\Component\\Cache\\Adapter\\FilesystemAdapter`::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new FilesystemAdapter();

Now you can create, retrieve, updated and delete items using this cache pool::

    // create a new item by trying to get it from the cache
    $numProducts = $cache->getItem('stats.num_products');

    // assign a value to the item and save it
    $numProducts->set(4711);
    $cache->save($numProducts);

    // retrieve the cache item
    $numProducts = $cache->getItem('stats.num_products');
    if (!$numProducts->isHit()) {
        // ... item does not exists in the cache
    }
    // retrieve the value stored by the item
    $total = $numProducts->get();

    // remove the cache item
    $cache->deleteItem('stats.num_products');
    
Or you can use redis-based cache, instantiate :class:`Symfony\\Component\\Cache\\Adapter\\RedisAdapter`::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    $connection = RedisAdapter::createConnection('redis://127.0.0.1:6379/0');
    $cache = new RedisAdapter($connection);


Advanced Usage
--------------

.. toctree::
    :glob:
    :maxdepth: 1

    cache/*

.. _`PSR-6`: http://www.php-fig.org/psr/psr-6/
.. _Packagist: https://packagist.org/packages/symfony/cache
