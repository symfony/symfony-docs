.. _`cache-component`:

The Cache Component
===================

    The Cache component provides features covering simple to advanced caching needs.
    It natively implements `PSR-6`_ and the `Cache Contracts`_ for greatest
    interoperability. It is designed for performance and resiliency, ships with
    ready to use adapters for the most common caching backends. It enables tag-based
    invalidation and cache stampede protection via locking and early expiration.

.. tip::

    The component also contains adapters to convert between PSR-6, PSR-16 and
    Doctrine caches. See :doc:`/components/cache/psr6_psr16_adapters` and
    :doc:`/components/cache/adapters/doctrine_adapter`.

    .. deprecated:: 5.4

        Support for Doctrine Cache was deprecated in Symfony 5.4
        and it will be removed in Symfony 6.0.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/cache

.. include:: /components/require_autoload.rst.inc

Cache Contracts versus PSR-6
----------------------------

This component includes *two* different approaches to caching:

:ref:`PSR-6 Caching <cache-component-psr6-caching>`:
    A generic cache system, which involves cache "pools" and cache "items".

:ref:`Cache Contracts <cache-component-contracts>`:
    A simpler yet more powerful way to cache values based on recomputation callbacks.

.. tip::

    Using the Cache Contracts approach is recommended: it requires less
    code boilerplate and provides cache stampede protection by default.

.. _cache-component-contracts:

Cache Contracts
---------------

All adapters support the Cache Contracts. They contain only two methods:
``get()`` and ``delete()``. There's no ``set()`` method because the ``get()``
method both gets and sets the cache values.

The first thing you need is to instantiate a cache adapter. The
:class:`Symfony\\Component\\Cache\\Adapter\\FilesystemAdapter` is used in this
example::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new FilesystemAdapter();

Now you can retrieve and delete cached data using this object. The first
argument of the ``get()`` method is a key, an arbitrary string that you
associate to the cached value so you can retrieve it later. The second argument
is a PHP callable which is executed when the key is not found in the cache to
generate and return the value::

    use Symfony\Contracts\Cache\ItemInterface;

    // The callable will only be executed on a cache miss.
    $value = $cache->get('my_cache_key', function (ItemInterface $item) {
        $item->expiresAfter(3600);

        // ... do some HTTP request or heavy computations
        $computedValue = 'foobar';

        return $computedValue;
    });

    echo $value; // 'foobar'

    // ... and to remove the cache key
    $cache->delete('my_cache_key');

.. note::

    Use cache tags to delete more than one key at the time. Read more at
    :doc:`/components/cache/cache_invalidation`.

Stampede Prevention
~~~~~~~~~~~~~~~~~~~

The Cache Contracts also come with built in `Stampede prevention`_. This will
remove CPU spikes at the moments when the cache is cold. If an example application
spends 5 seconds to compute data that is cached for 1 hour and this data is accessed
10 times every second, this means that you mostly have cache hits and everything
is fine. But after 1 hour, we get 10 new requests to a cold cache. So the data
is computed again. The next second the same thing happens. So the data is computed
about 50 times before the cache is warm again. This is where you need stampede
prevention.

The first solution is to use locking: only allow one PHP process (on a per-host basis)
to compute a specific key at a time. Locking is built-in by default, so
you don't need to do anything beyond leveraging the Cache Contracts.

The second solution is also built-in when using the Cache Contracts: instead of
waiting for the full delay before expiring a value, recompute it ahead of its
expiration date. The `Probabilistic early expiration`_ algorithm randomly fakes a
cache miss for one user while others are still served the cached value. You can
control its behavior with the third optional parameter of
:method:`Symfony\\Contracts\\Cache\\CacheInterface::get`,
which is a float value called "beta".

By default the beta is ``1.0`` and higher values mean earlier recompute. Set it
to ``0`` to disable early recompute and set it to ``INF`` to force an immediate
recompute::

    use Symfony\Contracts\Cache\ItemInterface;

    $beta = 1.0;
    $value = $cache->get('my_cache_key', function (ItemInterface $item) {
        $item->expiresAfter(3600);
        $item->tag(['tag_0', 'tag_1']);

        return '...';
    }, $beta);

Available Cache Adapters
~~~~~~~~~~~~~~~~~~~~~~~~

The following cache adapters are available:

.. toctree::
    :glob:
    :maxdepth: 1

    cache/adapters/*


.. _cache-component-psr6-caching:

Generic Caching (PSR-6)
-----------------------

To use the generic PSR-6 Caching abilities, you'll need to learn its key
concepts:

**Item**
    A single unit of information stored as a key/value pair, where the key is
    the unique identifier of the information and the value is its contents;
    see the :doc:`/components/cache/cache_items` article for more details.
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
basic API is the same as defined in the document. Before starting to cache information,
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
        // ... item does not exist in the cache
    }
    // retrieve the value stored by the item
    $total = $productsCount->get();

    // remove the cache item
    $cache->deleteItem('stats.products_count');

For a list of all of the supported adapters, see :doc:`/components/cache/cache_pools`.

Marshalling (Serializing) Data
------------------------------

.. note::

    `Marshalling`_ and `serializing`_ are similar concepts. Serializing is the
    process of translating an object state into a format that can be stored
    (e.g. in a file). Marshalling is the process of translating both the object
    state and its codebase into a format that can be stored or transmitted.

    Unmarshalling an object produces a copy of the original object, possibly by
    automatically loading the class definitions of the object.

Symfony uses *marshallers* (classes which implement
:class:`Symfony\\Component\\Cache\\Marshaller\\MarshallerInterface`) to process
the cache items before storing them.

The :class:`Symfony\\Component\\Cache\\Marshaller\\DefaultMarshaller` uses PHP's
``serialize()`` or ``igbinary_serialize()`` if the `Igbinary extension`_ is installed.
There are other *marshallers* that can encrypt or compress the data before storing it::

    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\DefaultMarshaller;
    use Symfony\Component\Cache\DeflateMarshaller;

    $marshaller = new DeflateMarshaller(new DefaultMarshaller());
    $cache = new RedisAdapter(new \Redis(), 'namespace', 0, $marshaller);

Advanced Usage
--------------

.. toctree::
    :glob:
    :maxdepth: 1

    cache/*

.. _`PSR-6`: https://www.php-fig.org/psr/psr-6/
.. _`Cache Contracts`: https://github.com/symfony/contracts/blob/master/Cache/CacheInterface.php
.. _`Stampede prevention`: https://en.wikipedia.org/wiki/Cache_stampede
.. _Probabilistic early expiration: https://en.wikipedia.org/wiki/Cache_stampede#Probabilistic_early_expiration
.. _`Marshalling`: https://en.wikipedia.org/wiki/Marshalling_(computer_science)
.. _`serializing`: https://en.wikipedia.org/wiki/Serialization
.. _`Igbinary extension`: https://github.com/igbinary/igbinary
