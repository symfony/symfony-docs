Cache
=====

Caching improves application performance by storing the result of expensive
operations, such as database queries, HTTP requests to external services or
heavy computations, so future requests can read it instantly instead of
recomputing it.

The Symfony Cache component implements both the `PSR-6`_ standard and the
simpler `Cache Contracts`_ for greatest interoperability. It's designed for
performance and resiliency, and ships with ready to use adapters for the most
common caching backends: Redis, Memcached, APCu, relational databases, the
filesystem, etc. It also provides advanced features such as tag-based
invalidation and cache :ref:`stampede prevention <cache_stampede-prevention>`.
You can use it in any PHP application, with or without Symfony.

.. seealso::

    This article explains how to cache arbitrary data in your application
    code. If you want to cache entire HTTP responses and serve them without
    running your application, read the :doc:`HTTP Cache </http_cache>` article.

Installation
------------

In Symfony applications, this component is already installed because it's
a dependency of the framework itself. In other applications, run this command to
install the component before using it:

.. code-block:: terminal

    $ composer require symfony/cache

.. include:: /components/require_autoload.rst.inc

.. _component-cache-cache-pools:

Cache Pools, Adapters and Items
-------------------------------

Before using the cache for the first time, learn about its main concepts:

**Item**
    A single unit of information stored as a key/value pair, where the key is
    the unique identifier of the information and the value is its contents.

**Pool**
    A logical repository of cache items. All cache operations (saving items,
    looking for items, etc.) are performed through the pool. Applications can
    define as many pools as needed and cache keys from different pools *never*
    collide, even if they share the same backend.

**Adapter**
    The class that implements the actual caching mechanism to store the
    information in the filesystem, in a Redis server, in a database, etc.
    When using the component in any PHP application, you create pools by
    instantiating adapters. In Symfony applications, adapters are *templates*
    used to create pools via configuration (e.g. ``cache.adapter.redis``).

**Provider**
    A service that some adapters use to connect to the storage (e.g. a Redis
    or Memcached connection). This concept only exists in Symfony applications;
    when using a DSN string as the provider, the connection service is created
    automatically.

Cache Contracts versus PSR-6
----------------------------

This component includes two different approaches to caching:

:ref:`Cache Contracts <cache-basic-usage>`:
    A simple yet powerful way to cache values based on recomputation callbacks.

:ref:`PSR-6 Caching <cache-component-psr6-caching>`:
    A generic cache system, which involves cache pools and cache items.

**Using the Cache Contracts approach is recommended**: it requires less code
boilerplate and provides cache stampede protection by default. For this reason,
this article explains all the caching features using the Cache Contracts. If you
need to use the generic PSR-6 API instead (e.g. when integrating a third-party
library that requires it), read the section about
:ref:`using PSR-6 cache pools <cache-component-psr6-caching>` at the end of this
article, which explains how to perform the same operations with that API.

.. _cache-basic-usage:

Basic Usage
-----------

The `Cache Contracts`_ define only two methods: ``get()`` and ``delete()``.
There's no ``set()`` method because the ``get()`` method both gets and sets
the cache values.

The first thing you need is a cache pool, which is an object implementing
:class:`Symfony\\Contracts\\Cache\\CacheInterface`. In Symfony applications,
type-hint a service or controller argument with that interface to inject the
:ref:`cache.app pool <cache-app-system>`. When using the component in any PHP
application, create the pool by instantiating one of the
:ref:`cache adapters <component-cache-creating-cache-pools>`:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Service/NewsProvider.php
        namespace App\Service;

        use Symfony\Contracts\Cache\CacheInterface;

        class NewsProvider
        {
            // the "cache.app" pool is injected thanks to autowiring
            public function __construct(
                private CacheInterface $cache,
            ) {
            }

            // ...
        }

    .. code-block:: php-standalone

        use Symfony\Component\Cache\Adapter\FilesystemAdapter;

        $cache = new FilesystemAdapter();

Now you can retrieve and delete cached data using this object. The first
argument of the ``get()`` method is a key, an arbitrary string that you
associate to the cached value so you can retrieve it later. The second argument
is a PHP callable which is executed when the key is not found in the cache to
generate and return the value::

    use Symfony\Contracts\Cache\ItemInterface;

    // the callable will only be executed on a cache miss
    $value = $cache->get('my_cache_key', function (ItemInterface $item): string {
        $item->expiresAfter(3600);

        // ... do some HTTP request or heavy computations
        $computedValue = 'foobar';

        return $computedValue;
    });

    echo $value; // 'foobar'

    // ... and to remove the cache key
    $cache->delete('my_cache_key');

.. note::

    Use :ref:`cache tags <cache-using-cache-tags>` to delete more than one
    key at a time.

The callback passed to the ``get()`` method provides two extra features:

* **Early expiration detection**: call the
  :method:`Symfony\\Contracts\\Cache\\ItemInterface::isHit` method inside the
  callback; if it returns ``true``, the value is being recomputed ahead of its
  expiration date because of the :ref:`stampede prevention <cache_stampede-prevention>`
  mechanism;
* **Discarding the value**: the callback can accept a second ``bool &$save``
  argument passed by reference. If you set ``$save`` to ``false`` inside the
  callback, the returned value *won't* be stored in the backend.

.. _cache-items:

Cache Items
-----------

Cache items are the information units stored in the cache as a key/value pair.
In the Cache component they are represented by the
:class:`Symfony\\Component\\Cache\\CacheItem` class. They are used in both the
Cache Contracts and the PSR-6 interfaces.

Cache Item Keys and Values
~~~~~~~~~~~~~~~~~~~~~~~~~~

The **key** of a cache item is a plain string which acts as its
identifier, so it must be unique for each cache pool. You can freely choose the
keys, but they should only contain letters (A-Z, a-z), numbers (0-9) and the
``_`` and ``.`` symbols. Other common symbols (such as ``{ } ( ) / \ @ :``) are
reserved by the PSR-6 standard for future uses.

The **value** of a cache item can be any data represented by a type which is
serializable by PHP, such as basic types (string, integer, float, boolean, null),
arrays and objects.

Creating Cache Items
~~~~~~~~~~~~~~~~~~~~

The only way to create cache items is via cache pools. When using the Cache
Contracts, they are passed as arguments to the recomputation callback, where
you can configure them. For example, you can set their expiration time::

    $productsCount = $cache->get('stats.products_count', function (ItemInterface $item): int {
        $item->expiresAfter(3600); // cache for 1 hour

        // ... compute the value
        return 4711;
    });

The value of the cache item (i.e. the value returned by the callback) is set
automatically, so you don't have to deal with the cache item outside of the
callback.

.. _cache-component-expiration:

Cache Item Expiration
~~~~~~~~~~~~~~~~~~~~~

By default, cache items are stored permanently. In practice, this "permanent
storage" can vary greatly depending on the :ref:`adapter you use
<component-cache-creating-cache-pools>` (e.g. the APCu adapter loses its
contents when the server restarts).

However, in some applications it's common to use cache items with a shorter
lifespan. Consider for example an application which caches the latest news
for one minute only. In those cases, use the ``expiresAfter()`` method to set the
number of seconds to cache the item::

    $latestNews->expiresAfter(60);  // 60 seconds = 1 minute

    // this method also accepts \DateInterval instances
    $latestNews->expiresAfter(DateInterval::createFromDateString('1 hour'));

Cache items define another related method called ``expiresAt()`` to set the
exact date and time when the item will expire::

    $mostPopularNews->expiresAt(new \DateTime('tomorrow'));

Cache pools can also define a default lifetime applied to all the items that
don't define their own. Use the ``default_lifetime`` option in Symfony
applications or the adapter constructor argument when using the component in
any PHP application.

.. _component-cache-creating-cache-pools:

Available Cache Adapters
------------------------

Cache adapters are the classes that implement the actual caching mechanism.
All of them support the Cache Contracts and the PSR-6 interfaces, so you can
create cache pools with any of them.

In Symfony applications, several adapters are pre-configured as services, so
you can use them when defining your cache pools:

* :doc:`cache.adapter.apcu </cache/adapters/apcu_adapter>`
* :doc:`cache.adapter.array </cache/adapters/array_cache_adapter>`
* :doc:`cache.adapter.doctrine_dbal </cache/adapters/doctrine_dbal_adapter>`
* :doc:`cache.adapter.filesystem </cache/adapters/filesystem_adapter>`
* :doc:`cache.adapter.memcached </cache/adapters/memcached_adapter>`
* :doc:`cache.adapter.pdo </cache/adapters/pdo_adapter>`
* :doc:`cache.adapter.psr6 </cache/adapters/proxy_adapter>`
* :doc:`cache.adapter.redis </cache/adapters/redis_adapter>`
* :ref:`cache.adapter.redis_tag_aware <redis-tag-aware-adapter>` (Redis adapter optimized to work with tags)
* :doc:`cache.adapter.valkey </cache/adapters/redis_adapter>`
* :ref:`cache.adapter.valkey_tag_aware <redis-tag-aware-adapter>` (Valkey adapter optimized to work with tags)

.. note::

    There's also a special ``cache.adapter.system`` adapter. It's recommended to
    use it for the :ref:`system cache <cache-app-system>`. This adapter uses some
    logic to dynamically select the best possible storage based on your system
    (either PHP files or APCu).

The component provides some other adapters that are not pre-configured as
services in Symfony applications (e.g. ``ChainAdapter`` and
``PhpFilesAdapter``). Read the dedicated article of each adapter to learn how
to configure and use them:

.. toctree::
    :glob:
    :maxdepth: 1

    cache/adapters/*

.. _cache-configuration-with-frameworkbundle:

Configuring Cache with FrameworkBundle
--------------------------------------

In Symfony applications, cache pools are defined with the ``framework.cache``
configuration. Some of the built-in adapters can be configured via shortcuts,
which define the default provider used by all the pools based on that adapter:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                directory: '%kernel.cache_dir%/pools' # Only used with cache.adapter.filesystem

                default_doctrine_dbal_provider: 'doctrine.dbal.default_connection'
                default_psr6_provider: 'app.my_psr6_service'
                default_redis_provider: 'redis://localhost'
                default_valkey_provider: 'valkey://localhost'
                default_memcached_provider: 'memcached://localhost'
                default_pdo_provider: 'pgsql:host=localhost'

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'cache' => [
                    'directory' => '%kernel.cache_dir%/pools', // Only used with cache.adapter.filesystem
                    'default_doctrine_dbal_provider' => 'doctrine.dbal.default_connection',
                    'default_psr6_provider' => 'app.my_psr6_service',
                    'default_redis_provider' => 'redis://localhost',
                    'default_valkey_provider' => 'valkey://localhost',
                    'default_memcached_provider' => 'memcached://localhost',
                    'default_pdo_provider' => 'pgsql:host=localhost',
                ],
            ],
        ]);

.. _cache-app-system:

System Cache and Application Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Two cache pools are always enabled by default: ``cache.system`` and ``cache.app``.

``cache.system`` is used **internally** by Symfony components such as annotations,
the serializer and validation. It is also **available for application** code,
but only under specific constraints:

#. Entries must be derivable from source code and regeneratable during cache
   warmup via a ``CacheWarmer``.
#. Cached content must only change when the source code changes (i.e. on deployment,
   not at runtime); treat it as read-only after deployment.

By default, ``cache.system`` uses ``cache.adapter.system``, which writes to the
filesystem and chains APCu when available. In most cases, the default is the
right choice.

.. tip::

    While it is possible to reconfigure the ``system`` cache, it's recommended
    to keep the default configuration applied to it by Symfony.

``cache.app`` is a **general-purpose data cache** for application and bundle code.
Data in this pool does not need to be flushed on deployment. It defaults to
``cache.adapter.filesystem``, but configuring a faster adapter like Redis is
recommended when available (this ensures cached data survives deployments and
is shared across multiple instances in a multi-server setup).

:ref:`Custom pools <cache-create-pools>` default to ``cache.app`` as their adapter
unless configured otherwise. When using **autowiring**, ``cache.app`` is injected
automatically into any service argument typed as ``Psr\Cache\CacheItemPoolInterface``,
:class:`Symfony\\Contracts\\Cache\\CacheInterface` or
:class:`Symfony\\Contracts\\Cache\\NamespacedPoolInterface`.

You can configure the adapter used by each predefined pool via the ``app`` and
``system`` keys:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                app: cache.adapter.filesystem
                system: cache.adapter.system

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'cache' => [
                    'app' => 'cache.adapter.filesystem',
                    'system' => 'cache.adapter.system',
                ],
            ],
        ]);

.. _cache-create-pools:

Creating Custom (Namespaced) Pools
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also create more customized pools:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                default_memcached_provider: 'memcached://localhost'

                pools:
                    # creates a "custom_thing.cache" service
                    # autowireable via "CacheInterface $customThingCache"
                    # uses the "app" cache configuration
                    custom_thing.cache:
                        adapter: cache.app

                    # creates a "my_cache_pool" service
                    # autowireable via "CacheInterface $myCachePool"
                    my_cache_pool:
                        adapter: cache.adapter.filesystem

                    # uses the default_memcached_provider from above
                    acme.cache:
                        adapter: cache.adapter.memcached

                    # control adapter's configuration
                    foobar.cache:
                        adapter: cache.adapter.memcached
                        provider: 'memcached://user:password@example.com'

                    # uses the "foobar.cache" pool as its backend but controls
                    # the lifetime and (like all pools) has a separate cache namespace
                    short_cache:
                        adapter: foobar.cache
                        default_lifetime: 60

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'cache' => [
                    'default_memcached_provider' => 'memcached://localhost',
                    'pools' => [
                        // creates a "custom_thing.cache" service
                        // autowireable via "CacheInterface $customThingCache"
                        // uses the "app" cache configuration
                        'custom_thing.cache' => [
                            'adapter' => 'cache.app',
                        ],
                        // creates a "my_cache_pool" service
                        // autowireable via "CacheInterface $myCachePool"
                        'my_cache_pool' => [
                            'adapter' => 'cache.adapter.filesystem',
                        ],
                        // uses the default_memcached_provider from above
                        'acme.cache' => [
                            'adapter' => 'cache.adapter.memcached',
                        ],
                        // control adapter's configuration
                        'foobar.cache' => [
                            'adapter' => 'cache.adapter.memcached',
                            'provider' => 'memcached://user:password@example.com',
                        ],
                        // uses the "foobar.cache" pool as its backend but controls
                        // the lifetime and (like all pools) has a separate cache namespace
                        'short_cache' => [
                            'adapter' => 'foobar.cache',
                            'default_lifetime' => 60,
                        ],
                    ],
                ],
            ],
        ]);

Each pool manages a set of independent cache keys: keys from different pools
*never* collide, even if they share the same backend. This is achieved by prefixing
keys with a namespace that's generated by hashing the name of the pool, the name
of the cache adapter class and a :ref:`configurable seed <reference-cache-prefix-seed>`
that defaults to the project directory and compiled container class.

Each custom pool becomes a service whose service ID is the name of the pool
(e.g. ``custom_thing.cache``). An autowiring alias is also created for each pool
using the camel case version of its name - e.g. ``custom_thing.cache`` can be
injected automatically by naming the argument ``$customThingCache`` and type-hinting it
with either :class:`Symfony\\Contracts\\Cache\\CacheInterface` or
``Psr\Cache\CacheItemPoolInterface``::

    use Symfony\Contracts\Cache\CacheInterface;
    // ...

    // from a controller method
    public function listProducts(CacheInterface $customThingCache): Response
    {
        // ...
    }

    // in a service
    public function __construct(private CacheInterface $customThingCache)
    {
        // ...
    }

When using the component in any PHP application, pools are namespaced with the
first constructor argument of the adapters, which also allow configuring the
default lifetime of the items::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    // creates a pool that namespaces its keys with "my_app" and
    // whose items expire after one hour by default
    $cache = new FilesystemAdapter('my_app', 3600);

.. tip::

    If you need the namespace to be interoperable with a third-party app,
    you can take control over auto-generation by setting the ``namespace``
    attribute of the ``cache.pool`` service tag. For example, you can
    override the service definition of the adapter:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                # ...

                app.cache.adapter.redis:
                    parent: 'cache.adapter.redis'
                    tags:
                        - { name: 'cache.pool', namespace: 'my_custom_namespace' }

Custom Provider Options
~~~~~~~~~~~~~~~~~~~~~~~

Some providers have specific options that can be configured. The
:doc:`RedisAdapter </cache/adapters/redis_adapter>` allows you to
create providers with the options ``timeout``, ``retry_interval``. etc. To use these
options with non-default values you need to create your own ``\Redis`` provider
and use that when configuring the pool.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                pools:
                    cache.my_redis:
                        adapter: cache.adapter.redis
                        provider: app.my_custom_redis_provider

        services:
            app.my_custom_redis_provider:
                class: \Redis
                factory: ['Symfony\Component\Cache\Adapter\RedisAdapter', 'createConnection']
                arguments:
                    - 'redis://localhost'
                    - { retry_interval: 2, timeout: 10 }

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Cache\Adapter\RedisAdapter;

        return App::config([
            'framework' => [
                'cache' => [
                    'pools' => [
                        'cache.my_redis' => [
                            'adapter' => 'cache.adapter.redis',
                            'provider' => 'app.my_custom_redis_provider',
                        ],
                    ],
                ],
            ],
            'services' => [
                'app.my_custom_redis_provider' => [
                    'class' => \Redis::class,
                    'factory' => [RedisAdapter::class, 'createConnection'],
                    'arguments' => ['redis://localhost', ['retry_interval' => 2, 'timeout' => 10]],
                ],
            ],
        ]);

Creating a Cache Chain
----------------------

Different cache adapters have different strengths and weaknesses. Some might be
really quick but optimized to store small items and some may be able to contain
a lot of data but are quite slow. To get the best of both worlds you may use a
chain of adapters.

A cache chain combines several cache pools into a single one. When storing an
item in a cache chain, Symfony stores it in all pools sequentially. When
retrieving an item, Symfony tries to get it from the first pool. If it's not
found, it tries the next pools until the item is found or an exception is thrown.
Because of this behavior, it's recommended to define the adapters in the chain
in order from fastest to slowest.

If an error happens when storing an item in a pool, Symfony stores it in the
other pools and no exception is thrown. Later, when the item is retrieved,
Symfony stores the item automatically in all the missing pools.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                pools:
                    my_cache_pool:
                        default_lifetime: 31536000  # One year
                        adapters:
                          - cache.adapter.array
                          - cache.adapter.apcu
                          - {name: cache.adapter.redis, provider: 'redis://user:password@example.com'}

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'cache' => [
                    'pools' => [
                        'my_cache_pool' => [
                            'default_lifetime' => 31536000, // One year
                            'adapters' => [
                                'cache.adapter.array',
                                'cache.adapter.apcu',
                                ['name' => 'cache.adapter.redis', 'provider' => 'redis://user:password@example.com'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    .. code-block:: php-standalone

        use Symfony\Component\Cache\Adapter\ApcuAdapter;
        use Symfony\Component\Cache\Adapter\ArrayAdapter;
        use Symfony\Component\Cache\Adapter\ChainAdapter;
        use Symfony\Component\Cache\Adapter\RedisAdapter;

        $cache = new ChainAdapter([
            new ArrayAdapter(),
            new ApcuAdapter(),
            new RedisAdapter(
                RedisAdapter::createConnection('redis://user:password@example.com')
            ),
        ], 31536000); // one year

Creating Sub-Namespaces
-----------------------

Sometimes you need to create context-dependent variations of data that should be
cached. For example, the data used to render a dashboard page may be expensive
to generate and unique per user, so you can't cache the same data for everyone.

In such cases, Symfony allows you to create different cache contexts using
namespaces. A cache namespace is an arbitrary string that identifies a set of
related cache items. Most cache adapters provided by the component implement the
:class:`Symfony\\Contracts\\Cache\\NamespacedPoolInterface`, which provides the
:method:`Symfony\\Contracts\\Cache\\NamespacedPoolInterface::withSubNamespace`
method (some adapters, such as ``PhpArrayAdapter`` and ``Psr16Adapter``, don't
support namespaces).

This method allows you to namespace cached items by transparently prefixing their keys::

    $userCache = $cache->withSubNamespace(sprintf('user-%d', $user->getId()));

    $userCache->get('dashboard_data', function (ItemInterface $item): string {
        $item->expiresAfter(3600);

        return '...';
    });

In this example, the cache item uses the ``dashboard_data`` key, but it will be
stored internally under a namespace based on the current user ID. This is handled
automatically, so you **don't** need to manually prefix keys like ``user-27.dashboard_data``.

In Symfony applications, the ``cache.app`` pool and all the pools defined under
the ``framework.cache.pools`` option support sub-namespaces. You can also inject
the ``cache.app`` pool by type-hinting a service or controller argument with
:class:`Symfony\\Contracts\\Cache\\NamespacedPoolInterface`.

There are no guidelines or restrictions on how to define cache namespaces.
You can make them as granular or as generic as your application requires::

    $localeCache = $cache->withSubNamespace($request->getLocale());

    $flagCache = $cache->withSubNamespace(
        $featureToggle->isEnabled('new_checkout') ? 'checkout-v2' : 'checkout-v1'
    );

    $channel = $request->attributes->get('_route')?->startsWith('api_') ? 'api' : 'web';
    $channelCache = $cache->withSubNamespace($channel);

.. tip::

    You can combine cache namespaces with :ref:`cache tags <cache-using-cache-tags>`
    for more advanced needs.

There is no built-in way to invalidate caches by namespace. Instead, the recommended
approach is to change the namespace itself. For this reason, it's common to include
static or dynamic versioning data in the cache namespace::

    // for simple applications, an incrementing static version number may be enough
    $userCache = $cache->withSubNamespace(sprintf('v1-user-%d', $user->getId()));

    // other applications may use dynamic versioning based on the date (e.g. monthly)
    $userCache = $cache->withSubNamespace(sprintf('%s-user-%d', date('Ym'), $user->getId()));

    // or even invalidate the cache when the user data changes
    $checksum = hash('xxh128', $user->getUpdatedAt()->format(DATE_ATOM));
    $userCache = $cache->withSubNamespace(sprintf('user-%d-%s', $user->getId(), $checksum));

.. _cache_stampede-prevention:

Preventing Cache Stampede
-------------------------

The Cache Contracts come with built-in `Stampede prevention`_. This will
remove CPU spikes at the moments when the cache is cold. If an example application
spends 5 seconds to compute data that is cached for 1 hour and this data is accessed
10 times every second, this means that you mostly have cache hits and everything
is fine. But after 1 hour, the application receives 10 new requests to a cold cache.
So the data is computed again. The next second the same thing happens. So the data
is computed about 50 times before the cache is warm again. This is where you need
stampede prevention.

The first solution is to use locking: only allow one PHP process (on a per-host basis)
to compute a specific key at a time. Locking is built-in by default, so
you don't need to do anything beyond leveraging the Cache Contracts.

The second solution is also built-in when using the Cache Contracts: instead of
waiting for the full delay before expiring a value, recompute it ahead of its
expiration date. The `probabilistic early expiration`_ algorithm randomly fakes a
cache miss for one user while others are still served the cached value. You can
control its behavior with the third optional parameter of
:method:`Symfony\\Contracts\\Cache\\CacheInterface::get`,
which is a float value called "beta".

By default the beta is ``1.0`` and higher values mean earlier recompute. Set it
to ``0`` to disable early recompute and set it to ``INF`` to force an immediate
recompute::

    use Symfony\Contracts\Cache\ItemInterface;

    $beta = 1.0;
    $value = $cache->get('my_cache_key', function (ItemInterface $item): string {
        $item->expiresAfter(3600);

        return '...';
    }, $beta);

.. note::

    The beta parameter only has an effect when the cached item defines an
    expiration. Also, stampede prevention only applies to the Cache Contracts:
    when using the PSR-6 methods (``getItem()``, ``save()``, etc.) you have to
    protect the application against cache stampedes yourself.

.. _cache-custom-marshaller-per-pool:

Configuring a Custom Marshaller per Cache Pool
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.1

    The ``marshaller`` option for cache pools was introduced in Symfony 8.1.

The above configuration decorates the ``cache.default_marshaller`` service, so
the custom marshaller applies to **all** cache pools. If you need a custom
marshaller only for specific pools, use the ``marshaller`` option instead:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                pools:
                    cache.encrypted:
                        adapter: cache.adapter.filesystem
                        marshaller: 'app.sodium_marshaller'

        services:
            app.sodium_marshaller:
                class: Symfony\Component\Cache\Marshaller\SodiumMarshaller
                arguments:
                    - ['%env(base64:CACHE_DECRYPTION_KEY)%']
                    - '@cache.default_marshaller'

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Cache\Marshaller\SodiumMarshaller;

        return App::config([
            'framework' => [
                'cache' => [
                    'pools' => [
                        'cache.encrypted' => [
                            'adapter' => 'cache.adapter.filesystem',
                            'marshaller' => 'app.sodium_marshaller',
                        ],
                    ],
                ],
            ],
            'services' => [
                'app.sodium_marshaller' => [
                    'class' => SodiumMarshaller::class,
                    'arguments' => [
                        [env('CACHE_DECRYPTION_KEY')->base64()],
                        service('cache.default_marshaller'),
                    ],
                ],
            ],
        ]);

This approach lets you mix different marshalling strategies across pools
(e.g. encrypting data in one pool and compressing it in another) while
leaving other pools with the default marshaller:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                pools:
                    cache.tokens:
                        adapter: cache.adapter.redis
                        marshaller: 'app.sodium_marshaller'
                    cache.large_data:
                        adapter: cache.adapter.filesystem
                        marshaller: 'app.deflate_marshaller'
                    cache.regular:
                        adapter: cache.adapter.filesystem

        services:
            app.sodium_marshaller:
                class: Symfony\Component\Cache\Marshaller\SodiumMarshaller
                arguments:
                    - ['%env(base64:CACHE_DECRYPTION_KEY)%']
                    - '@cache.default_marshaller'

            app.deflate_marshaller:
                class: Symfony\Component\Cache\Marshaller\DeflateMarshaller
                arguments:
                    - '@cache.default_marshaller'

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Cache\Marshaller\DeflateMarshaller;
        use Symfony\Component\Cache\Marshaller\SodiumMarshaller;

        return App::config([
            'framework' => [
                'cache' => [
                    'pools' => [
                        'cache.tokens' => [
                            'adapter' => 'cache.adapter.redis',
                            'marshaller' => 'app.sodium_marshaller',
                        ],
                        'cache.large_data' => [
                            'adapter' => 'cache.adapter.filesystem',
                            'marshaller' => 'app.deflate_marshaller',
                        ],
                        'cache.regular' => [
                            'adapter' => 'cache.adapter.filesystem',
                        ],
                    ],
                ],
            ],
            'services' => [
                'app.sodium_marshaller' => [
                    'class' => SodiumMarshaller::class,
                    'arguments' => [
                        [env('CACHE_DECRYPTION_KEY')->base64()],
                        service('cache.default_marshaller'),
                    ],
                ],
                'app.deflate_marshaller' => [
                    'class' => DeflateMarshaller::class,
                    'arguments' => [
                        service('cache.default_marshaller'),
                    ],
                ],
            ],
        ]);

Computing Cache Values Asynchronously
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Because of the probabilistic early expiration algorithm, some cache items are
elected for early-expiration while they are still fresh. By default, those cache
items are recomputed synchronously. However, in Symfony applications you can
compute them asynchronously by delegating the value computation to a background
worker using the :doc:`Messenger component </messenger>`. In this case,
when an item is queried, its cached value is immediately returned and a
:class:`Symfony\\Component\\Cache\\Messenger\\EarlyExpirationMessage` is
dispatched through a Messenger bus.

When this message is handled by a message consumer, the refreshed cache value is
computed asynchronously. The next time the item is queried, the refreshed value
will be fresh and returned.

First, create a service that will compute the item's value::

    // src/Cache/CacheComputation.php
    namespace App\Cache;

    use Psr\Cache\CacheItemInterface;
    use Symfony\Contracts\Cache\CallbackInterface;

    class CacheComputation implements CallbackInterface
    {
        public function __invoke(CacheItemInterface $item, bool &$save): string
        {
            $item->expiresAfter(5);

            // this is a random example; here you must do your own calculation
            return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        }
    }

This cache value will be requested from a controller, another service, etc.
In the following example, the value is requested from a controller::

    // src/Controller/CacheController.php
    namespace App\Controller;

    use App\Cache\CacheComputation;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Contracts\Cache\CacheInterface;
    use Symfony\Contracts\Cache\ItemInterface;

    class CacheController extends AbstractController
    {
        #[Route('/cache', name: 'cache')]
        public function index(CacheInterface $asyncCache, CacheComputation $cacheComputation): Response
        {
            // pass to the cache the service method that refreshes the item
            $cachedValue = $asyncCache->get('my_value', $cacheComputation);

            // ...
        }
    }

Finally, configure a new cache pool (e.g. called ``async.cache``) that will use
a message bus to compute values in a worker:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            cache:
                pools:
                    async.cache:
                        early_expiration_message_bus: messenger.default_bus

            messenger:
                transports:
                    async_bus: '%env(MESSENGER_TRANSPORT_DSN)%'
                routing:
                    'Symfony\Component\Cache\Messenger\EarlyExpirationMessage': async_bus

    .. code-block:: php

        // config/framework/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Cache\Messenger\EarlyExpirationMessage;

        return App::config([
            'framework' => [
                'cache' => [
                    'pools' => [
                        'async.cache' => [
                            'early_expiration_message_bus' => 'messenger.default_bus',
                        ],
                    ],
                ],
                'messenger' => [
                    'transports' => [
                        'async_bus' => env('MESSENGER_TRANSPORT_DSN'),
                    ],
                    'routing' => [
                        EarlyExpirationMessage::class => 'async_bus',
                    ],
                ],
            ],
        ]);

You can now start the consumer:

.. code-block:: terminal

    $ php bin/console messenger:consume async_bus

That's it! Now, whenever an item is queried from this cache pool, its cached
value will be returned immediately. If it is elected for early-expiration, a
message will be sent through the bus to schedule a background computation to refresh
the value.

.. _cache-invalidation:

Invalidating the Cache
----------------------

Cache invalidation is the process of removing all cached items related to a
change in the state of your model. The most basic kind of invalidation is direct
item deletion. But when the state of a primary resource has spread across
several cached items, keeping them in sync can be difficult.

The Symfony Cache component provides two mechanisms to help solve this problem:

* :ref:`Tags-based invalidation <cache-using-cache-tags>` for managing data dependencies;
* :ref:`Expiration based invalidation <cache-component-expiration>` for time-related dependencies.

.. _cache-using-cache-tags:
.. _cache-component-tags:

Using Cache Tags
~~~~~~~~~~~~~~~~

In applications with many cache keys it could be useful to organize the data
stored to be able to invalidate the cache more efficiently. To do that, attach
one or more tags to each cached item. Each tag is a plain string identifier
that you can use at any time to remove all the items associated with this tag.

To attach tags to cached items, use the
:method:`Symfony\\Contracts\\Cache\\ItemInterface::tag` method. To remove all
the items associated with some tag, use the
:method:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface::invalidateTags`
method of the cache pool:

.. configuration-block::

    .. code-block:: php-symfony

        use Symfony\Contracts\Cache\ItemInterface;
        use Symfony\Contracts\Cache\TagAwareCacheInterface;

        class SomeClass
        {
            // using autowiring to inject the "my_cache_pool" pool
            // defined in the configuration shown below
            public function __construct(
                private TagAwareCacheInterface $myCachePool,
            ) {
            }

            public function someMethod(): void
            {
                $value0 = $this->myCachePool->get('item_0', function (ItemInterface $item): string {
                    $item->tag(['foo', 'bar']);

                    return 'debug';
                });

                $value1 = $this->myCachePool->get('item_1', function (ItemInterface $item): string {
                    $item->tag('foo');

                    return 'debug';
                });

                // remove all cache keys tagged with "bar"
                $this->myCachePool->invalidateTags(['bar']);

                // if you know the cache key, you can also delete the item directly
                $this->myCachePool->delete('item_1');
            }
        }

    .. code-block:: php-standalone

        use Symfony\Component\Cache\Adapter\FilesystemAdapter;
        use Symfony\Component\Cache\Adapter\TagAwareAdapter;
        use Symfony\Contracts\Cache\ItemInterface;

        $cache = new TagAwareAdapter(new FilesystemAdapter());

        $value0 = $cache->get('item_0', function (ItemInterface $item): string {
            $item->tag(['foo', 'bar']);

            return 'debug';
        });

        $value1 = $cache->get('item_1', function (ItemInterface $item): string {
            $item->tag('foo');

            return 'debug';
        });

        // remove all cache keys tagged with "bar"
        $cache->invalidateTags(['bar']);

        // if you know the cache key, you can also delete the item directly
        $cache->delete('item_1');

Using tag invalidation is very convenient when tracking cache keys becomes
difficult.

The cache pool needs to implement
:class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface` to enable this
feature. In Symfony applications, use the ``tags`` option of the pool:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                pools:
                    my_cache_pool:
                        adapter: cache.adapter.redis_tag_aware
                        tags: true

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'cache' => [
                    'pools' => [
                        'my_cache_pool' => [
                            'adapter' => 'cache.adapter.redis_tag_aware',
                            'tags' => true,
                        ],
                    ],
                ],
            ],
        ]);

.. note::

    In Symfony applications, when a service or controller argument is
    type-hinted with :class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface`,
    autowiring injects the ``cache.app.taggable`` service, a tag aware pool
    based on the ``cache.app`` pool. This means you can use cache tags without
    defining any custom pool.

Tags are stored in the same pool by default. This is good in most scenarios. But
sometimes it might be better to store the tags in a different pool. That could be
achieved by specifying the adapter.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                pools:
                    my_cache_pool:
                        adapter: cache.adapter.redis
                        tags: tag_pool
                    tag_pool:
                        adapter: cache.adapter.apcu

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'cache' => [
                    'pools' => [
                        'my_cache_pool' => [
                            'adapter' => 'cache.adapter.redis',
                            'tags' => 'tag_pool',
                        ],
                        'tag_pool' => [
                            'adapter' => 'cache.adapter.apcu',
                        ],
                    ],
                ],
            ],
        ]);

Tag Aware Adapters
~~~~~~~~~~~~~~~~~~

To store tags, you need to wrap a cache adapter with the
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter` class or implement
:class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface` and its
``invalidateTags()`` method. In Symfony applications, the ``tags: true`` option
shown in the previous section wraps the pool adapter automatically.

.. note::

    When using a Redis backend, consider using :ref:`RedisTagAwareAdapter <redis-tag-aware-adapter>`
    which is optimized for this purpose. When using the filesystem, consider
    using :ref:`FilesystemTagAwareAdapter <filesystem-tag-aware-adapter>`.

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

In Symfony applications, this is what the ``tags: tag_pool`` option shown in the
previous section does for you.

.. note::

    :class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`
    implements :class:`Symfony\\Component\\Cache\\PruneableInterface`,
    enabling manual
    :ref:`pruning of expired cache entries <component-cache-cache-pool-prune>` by
    calling its ``prune()`` method (assuming the wrapped adapter itself implements
    :class:`Symfony\\Component\\Cache\\PruneableInterface`).

.. _component-cache-cache-pool-prune:

Pruning Cache Items
-------------------

Some cache backends, such as Redis, Memcached and APCu, remove expired items
automatically. Others, such as the filesystem or database based caches, don't
include a mechanism to do that. For example, the
:doc:`FilesystemAdapter </cache/adapters/filesystem_adapter>` doesn't remove
expired cache items *until an item is explicitly requested and determined to
be expired*. Under certain workloads, this can cause stale cache entries to
persist well past their expiration, resulting in a sizable consumption of
wasted disk or memory space from excess, expired cache items.

To solve this, the component provides the
:class:`Symfony\\Component\\Cache\\PruneableInterface`, which defines the
:method:`Symfony\\Component\\Cache\\PruneableInterface::prune` method to remove
all the expired items from the storage. The adapters that need this feature
implement it: :doc:`ChainAdapter </cache/adapters/chain_adapter>`,
:doc:`DoctrineDbalAdapter </cache/adapters/doctrine_dbal_adapter>`,
:doc:`FilesystemAdapter </cache/adapters/filesystem_adapter>`,
:doc:`PdoAdapter </cache/adapters/pdo_adapter>` and
:doc:`PhpFilesAdapter </cache/adapters/php_files_adapter>`, as well as the
adapters that wrap other adapters (such as ``TagAwareAdapter``, ``ProxyAdapter``
and ``Psr16Adapter``), which delegate the pruning to the wrapped adapter::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;

    $cache = new FilesystemAdapter('app.cache');
    // ... do some set and get operations
    $cache->prune();

The :doc:`ChainAdapter </cache/adapters/chain_adapter>` implementation does not directly
contain any pruning logic itself. Instead, when calling the chain adapter's
:method:`Symfony\\Component\\Cache\\Adapter\\ChainAdapter::prune` method, the call is delegated to all
its compatible cache adapters (and those that do not implement ``PruneableInterface`` are
silently ignored)::

    use Symfony\Component\Cache\Adapter\ApcuAdapter;
    use Symfony\Component\Cache\Adapter\ChainAdapter;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\Adapter\PdoAdapter;
    use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

    $cache = new ChainAdapter([
        new ApcuAdapter(),       // does NOT implement PruneableInterface
        new FilesystemAdapter(), // DOES implement PruneableInterface
        new PdoAdapter(),        // DOES implement PruneableInterface
        new PhpFilesAdapter(),   // DOES implement PruneableInterface
        // ...
    ]);

    // prune will proxy the call to PdoAdapter, FilesystemAdapter and PhpFilesAdapter,
    // while silently skipping ApcuAdapter
    $cache->prune();

.. tip::

    In Symfony applications, you can prune the expired items of *all pools*
    with the following command:

    .. code-block:: terminal

        $ php bin/console cache:pool:prune

.. _cache-clearing:

Clearing the Cache
------------------

In Symfony applications, you can delete items from cache pools with the
``cache:pool:*`` commands. To see all available cache pools:

.. code-block:: terminal

    $ php bin/console cache:pool:list

Delete one item from one pool:

.. code-block:: terminal

    $ php bin/console cache:pool:delete <cache-pool-name> <cache-key-name>

    # deletes the "cache_key" item from the "cache.app" pool
    $ php bin/console cache:pool:delete cache.app cache_key

Clear one pool:

.. code-block:: terminal

    $ php bin/console cache:pool:clear my_cache_pool

Clear all cache pools:

.. code-block:: terminal

    $ php bin/console cache:pool:clear --all

Clear all cache pools except some:

.. code-block:: terminal

    $ php bin/console cache:pool:clear --all --exclude=my_cache_pool --exclude=another_cache_pool

Clear cache by tag(s):

.. code-block:: terminal

    # invalidate tag1 from all taggable pools
    $ php bin/console cache:pool:invalidate-tags tag1

    # invalidate tag1 & tag2 from all taggable pools
    $ php bin/console cache:pool:invalidate-tags tag1 tag2

    # invalidate tag1 & tag2 from cache.app pool
    $ php bin/console cache:pool:invalidate-tags tag1 tag2 --pool=cache.app

    # invalidate tag1 & tag2 from cache1 & cache2 pools
    $ php bin/console cache:pool:invalidate-tags tag1 tag2 -p cache1 -p cache2

You can also group your pools into "cache clearers", used to clear several
related pools at once. There are 3 cache clearers by default:

* ``cache.global_clearer``: clears all the cache items in every pool;
* ``cache.system_clearer``: clears the pools used by the
  ``bin/console cache:clear`` command;
* ``cache.app_clearer``: the default clearer, which clears ``cache.app``
  and the custom pools.

Pass the name of the clearer to the ``cache:pool:clear`` command to clear all
its pools. For example, to clear all caches everywhere:

.. code-block:: terminal

    $ php bin/console cache:pool:clear cache.global_clearer

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
the cache items before storing them. Marshallers are designed to wrap each
other, so each of them applies its own processing to the result of the previous
one.

The :class:`Symfony\\Component\\Cache\\Marshaller\\DefaultMarshaller` uses PHP's
``serialize()`` function by default, but you can optionally use the ``igbinary_serialize()``
function from the `Igbinary extension`_::

    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
    use Symfony\Component\Cache\Marshaller\DeflateMarshaller;

    $marshaller = new DeflateMarshaller(new DefaultMarshaller());
    // you can optionally use the Igbinary extension if you have it installed
    // $marshaller = new DeflateMarshaller(new DefaultMarshaller(useIgbinarySerialize: true));

    $cache = new RedisAdapter(new \Redis(), 'namespace', 0, $marshaller);

There are other *marshallers* that can encrypt or compress the data before storing it.

In Symfony applications, the marshaller used by all cache pools is defined by
the ``cache.default_marshaller`` service. You can decorate that service to
change how cached data is processed, as shown in the next section.

Encrypting the Cache
--------------------

To encrypt the cache using ``libsodium``, you can use the
:class:`Symfony\\Component\\Cache\\Marshaller\\SodiumMarshaller`.

First, you need to generate a secure key and add it to your :doc:`secret
store </configuration/secrets>` as ``CACHE_DECRYPTION_KEY``:

.. code-block:: terminal

    $ php -r 'echo base64_encode(sodium_crypto_box_keypair());'

Then, register the ``SodiumMarshaller`` service using this key:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml

        # ...
        services:
            Symfony\Component\Cache\Marshaller\SodiumMarshaller:
                decorates: cache.default_marshaller
                arguments:
                    - ['%env(base64:CACHE_DECRYPTION_KEY)%']
                    # use multiple keys in order to rotate them
                    #- ['%env(base64:CACHE_DECRYPTION_KEY)%', '%env(base64:OLD_CACHE_DECRYPTION_KEY)%']
                    - '@.inner'

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Cache\Marshaller\SodiumMarshaller;

        return App::config([
            'services' => [
                SodiumMarshaller::class => [
                    'decorates' => 'cache.default_marshaller',
                    'arguments' => [
                        [env('CACHE_DECRYPTION_KEY')->base64()],
                        // use multiple keys in order to rotate them
                        // [env('CACHE_DECRYPTION_KEY')->base64(), env('OLD_CACHE_DECRYPTION_KEY')->base64()]
                        service('.inner'),
                    ],
                ],
            ],
        ]);

.. danger::

    This will encrypt the values of the cache items, but not the cache keys. Be
    careful not to leak sensitive data in the keys.

When configuring multiple keys, the first key will be used for reading and
writing, and the additional key(s) will only be used for reading. Once all
cache items encrypted with the old key have expired, you can completely remove
``OLD_CACHE_DECRYPTION_KEY``.

.. _cache-component-psr6-caching:

Using PSR-6 Cache Pools
-----------------------

The previous sections of this article explain how to cache contents using the
recommended :ref:`Cache Contracts <cache-basic-usage>`. This section explains how
to perform the same operations using the generic `PSR-6`_ caching API, which you
might need for example when integrating a third-party library that requires it.

PSR-6 cache pools implement the ``Psr\Cache\CacheItemPoolInterface``: in
Symfony applications, type-hint a service or controller argument with that
interface to inject the ``cache.app`` pool; in any PHP application,
instantiate one of the :ref:`cache adapters <component-cache-creating-cache-pools>`
(see the :ref:`basic usage section <cache-basic-usage>` for full examples).
The :ref:`cache item keys and values <cache-items>` and the
:ref:`expiration methods <cache-component-expiration>` explained earlier in
this article also apply to PSR-6.

Looking for Cache Items
~~~~~~~~~~~~~~~~~~~~~~~

Cache pools define three methods to look for cache items. The most common
method is ``getItem($key)``, which returns the cache item identified by the
given key::

    $latestNews = $cache->getItem('latest_news');

If no item is defined for the given key, the method doesn't return a ``null``
value but an empty object which implements the
:class:`Symfony\\Component\\Cache\\CacheItem` class.

If you need to fetch several cache items simultaneously, use instead the
``getItems([$key1, $key2, ...])`` method::

    $stocks = $cache->getItems(['AAPL', 'FB', 'GOOGL', 'MSFT']);

Again, if any of the keys doesn't represent a valid cache item, you won't get
a ``null`` value but an empty ``CacheItem`` object.

The last method related to fetching cache items is ``hasItem($key)``, which
returns ``true`` if there is a cache item identified by the given key::

    $hasBadges = $cache->hasItem('user_'.$userId.'_badges');

Cache Item Hits and Misses
~~~~~~~~~~~~~~~~~~~~~~~~~~

Using a cache mechanism is important to improve the application performance,
but it should not be required to make the application work. In fact, the PSR-6
standard states that caching errors should not result in application failures.

That's why the ``getItem()`` method always returns an object which implements
the ``Psr\Cache\CacheItemInterface`` interface, even when the cache item
doesn't exist. Therefore, you don't have to deal with ``null`` return values
and you can safely store in the cache values such as ``false`` and ``null``.

In order to decide if the returned object represents a value coming from the
storage or not, caches use the concept of hits and misses:

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

Saving Cache Items
~~~~~~~~~~~~~~~~~~

Unlike the Cache Contracts, the PSR-6 API doesn't set the value of the cache
items automatically. Use the ``Psr\Cache\CacheItemInterface::set`` method to
do that::

    // storing a simple integer
    $productsCount = $cache->getItem('stats.products_count');
    $productsCount->set(4711);

    // storing an array
    $productsCount->set([
        'category1' => 4711,
        'category2' => 2387,
    ]);

The key and the value of any given cache item can be obtained with the
corresponding *getter* methods::

    $cacheItem = $cache->getItem('exchange_rate');
    // ...
    $key = $cacheItem->getKey();
    $value = $cacheItem->get();

Setting the value of an item doesn't store it in the cache. The most common
method to do that is ``Psr\Cache\CacheItemPoolInterface::save``, which stores
the item in the cache immediately (it returns ``true`` if the item was saved
or ``false`` if some error occurred)::

    $userFriends = $cache->getItem('user_'.$userId.'_friends');
    $userFriends->set($user->getFriends());
    $isSaved = $cache->save($userFriends);

Sometimes you may prefer to not save the objects immediately in order to
increase the application performance. In those cases, use the
``Psr\Cache\CacheItemPoolInterface::saveDeferred`` method to mark cache
items as "ready to be persisted" and then call to
``Psr\Cache\CacheItemPoolInterface::commit`` method when you are ready
to persist them all::

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
~~~~~~~~~~~~~~~~~~~~

Cache pools include methods to delete a cache item, some of them or all of them.
The most common is ``Psr\Cache\CacheItemPoolInterface::deleteItem``,
which deletes the cache item identified by the given key (it returns ``true``
when the item is successfully deleted or doesn't exist and ``false`` otherwise)::

    $isDeleted = $cache->deleteItem('user_'.$userId);

Use the ``Psr\Cache\CacheItemPoolInterface::deleteItems`` method to
delete several cache items simultaneously (it returns ``true`` only if all the
items have been deleted, even when any or some of them don't exist)::

    $areDeleted = $cache->deleteItems(['category1', 'category2']);

Finally, to remove all the cache items stored in the pool, use the
``Psr\Cache\CacheItemPoolInterface::clear`` method (which returns ``true``
when all items are successfully deleted)::

    $cacheIsEmpty = $cache->clear();

.. tip::

    In Symfony applications, you can also remove items from cache pools using
    :ref:`console commands <cache-clearing>`.

.. _cache-psr6-psr16-adapters:

Interoperability between PSR-6 and PSR-16 Caches
------------------------------------------------

Sometimes, you may have a cache object that implements the `PSR-16`_ standard,
but need to pass it to an object that expects a :ref:`PSR-6 <cache-component-psr6-caching>`
cache pool. Or, you might have the opposite situation. The Cache component
contains two classes for bidirectional interoperability between PSR-6 and
PSR-16 caches.

Using a PSR-16 Cache Object as a PSR-6 Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Consider a class that requires a PSR-6 cache pool object::

    use Psr\Cache\CacheItemPoolInterface;

    // an example class that requires a PSR-6 cache object
    class GitHubApiClient
    {
        // ...

        public function __construct(CacheItemPoolInterface $cachePool)
        {
            // ...
        }
    }

If you already have a PSR-16 cache object and want to pass it to that class,
wrap it with the :class:`Symfony\\Component\\Cache\\Adapter\\Psr16Adapter`
class, which makes it compatible with PSR-6::

    use Symfony\Component\Cache\Adapter\Psr16Adapter;

    // $psr16Cache is the PSR-16 object that you want to use as a PSR-6 one

    // a PSR-6 cache that uses your cache internally!
    $psr6Cache = new Psr16Adapter($psr16Cache);

    // now use this wherever you want
    $githubApiClient = new GitHubApiClient($psr6Cache);

Using a PSR-6 Cache Object as a PSR-16 Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Consider now a class that requires a PSR-16 cache object::

    use Psr\SimpleCache\CacheInterface;

    // an example class that requires a PSR-16 cache object
    class GitHubApiClient
    {
        // ...

        public function __construct(CacheInterface $cache)
        {
            // ...
        }
    }

If you already have a PSR-6 cache pool object and want to pass it to that
class, wrap it with the :class:`Symfony\\Component\\Cache\\Psr16Cache` class,
which makes it compatible with PSR-16::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\Psr16Cache;

    // the PSR-6 cache object that you want to use
    $psr6Cache = new FilesystemAdapter();

    // a PSR-16 cache that uses your cache internally!
    $psr16Cache = new Psr16Cache($psr6Cache);

    // now use this wherever you want
    $githubApiClient = new GitHubApiClient($psr16Cache);

.. _`PSR-6`: https://www.php-fig.org/psr/psr-6/
.. _`PSR-16`: https://www.php-fig.org/psr/psr-16/
.. _`Cache Contracts`: https://github.com/symfony/contracts/blob/master/Cache/CacheInterface.php
.. _`Stampede prevention`: https://en.wikipedia.org/wiki/Cache_stampede
.. _`probabilistic early expiration`: https://en.wikipedia.org/wiki/Cache_stampede#Probabilistic_early_expiration
.. _`Marshalling`: https://en.wikipedia.org/wiki/Marshalling_(computer_science)
.. _`serializing`: https://en.wikipedia.org/wiki/Serialization
.. _`Igbinary extension`: https://github.com/igbinary/igbinary
