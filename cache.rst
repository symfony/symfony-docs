Cache
=====

Using a cache is a great way of making your application run quicker. The Symfony cache
component ships with many adapters to different storages. Every adapter is
developed for high performance.

The following example shows a typical usage of the cache::

    use Symfony\Contracts\Cache\ItemInterface;

    // The callable will only be executed on a cache miss.
    $value = $pool->get('my_cache_key', function (ItemInterface $item): string {
        $item->expiresAfter(3600);

        // ... do some HTTP request or heavy computations
        $computedValue = 'foobar';

        return $computedValue;
    });

    echo $value; // 'foobar'

    // ... and to remove the cache key
    $pool->delete('my_cache_key');

Symfony supports Cache Contracts and PSR-6/16 interfaces.
You can read more about these at the :doc:`component documentation </components/cache>`.

.. _cache-configuration-with-frameworkbundle:

Configuring Cache with FrameworkBundle
--------------------------------------

When configuring the cache component there are a few concepts you should know:

**Pool**
    This is a service that you will interact with. Each pool will always have
    its own namespace and cache items. There are never conflicts between pools.
**Adapter**
    An adapter is a *template* that you use to create pools.
**Provider**
    A provider is a service that some adapters use to connect to the storage.
    Redis and Memcached are examples of such adapters. If a DSN is used as the
    provider then a service is automatically created.

The Cache component comes with a series of adapters pre-configured:

* :doc:`cache.adapter.apcu </components/cache/adapters/apcu_adapter>`
* :doc:`cache.adapter.array </components/cache/adapters/array_cache_adapter>`
* :doc:`cache.adapter.doctrine_dbal </components/cache/adapters/doctrine_dbal_adapter>`
* :doc:`cache.adapter.filesystem </components/cache/adapters/filesystem_adapter>`
* :doc:`cache.adapter.memcached </components/cache/adapters/memcached_adapter>`
* :doc:`cache.adapter.pdo </components/cache/adapters/pdo_adapter>`
* :doc:`cache.adapter.psr6 </components/cache/adapters/proxy_adapter>`
* :doc:`cache.adapter.redis </components/cache/adapters/redis_adapter>`
* :ref:`cache.adapter.redis_tag_aware <redis-tag-aware-adapter>` (Redis adapter optimized to work with tags)

.. note::

    There's also a special ``cache.adapter.system`` adapter. It's recommended to
    use it for the :ref:`system cache <cache-app-system>`. This adapter uses some
    logic to dynamically select the best possible storage based on your system
    (either PHP files or APCu).

Some of these adapters could be configured via shortcuts.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                directory: '%kernel.cache_dir%/pools' # Only used with cache.adapter.filesystem

                default_doctrine_dbal_provider: 'doctrine.dbal.default_connection'
                default_psr6_provider: 'app.my_psr6_service'
                default_redis_provider: 'redis://localhost'
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
                    'default_memcached_provider' => 'memcached://localhost',
                    'default_pdo_provider' => 'pgsql:host=localhost',
                ],
            ],
        ]);

.. _cache-app-system:

System Cache and Application Cache
----------------------------------

Two cache pools are always enabled by default: ``cache.system`` and ``cache.app``.

``cache.system`` is used **internally** by Symfony components such as annotations,
the serializer, and validation. It is also **available for application** code,
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
automatically into any service argument typed as ``CacheItemPoolInterface``,
``AdapterInterface``, or ``CacheInterface``.

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
----------------------------------

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
-----------------------

Some providers have specific options that can be configured. The
:doc:`RedisAdapter </components/cache/adapters/redis_adapter>` allows you to
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

.. _cache-using-cache-tags:

Using Cache Tags
----------------

In applications with many cache keys it could be useful to organize the data stored
to be able to invalidate the cache more efficiently. One way to achieve that is to
use cache tags. One or more tags could be added to the cache item. All items with
the same tag could be invalidated with one function call::

    use Symfony\Contracts\Cache\ItemInterface;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;

    class SomeClass
    {
        // using autowiring to inject the cache pool
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

            // Remove all cache keys tagged with "bar"
            $this->myCachePool->invalidateTags(['bar']);
        }
    }

The cache adapter needs to implement :class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface`
to enable this feature. This could be added by using the following configuration.

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

.. note::

    The interface :class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface` is
    autowired to the ``cache.app`` service.

Clearing the Cache
------------------

To clear the cache you can use the ``bin/console cache:pool:clear [pool]`` command.
That will remove all the entries from your storage and you will have to recalculate
all the values. You can also group your pools into "cache clearers". There are 3 cache
clearers by default:

* ``cache.global_clearer``
* ``cache.system_clearer``
* ``cache.app_clearer``

The global clearer clears all the cache items in every pool. The system cache clearer
is used in the ``bin/console cache:clear`` command. The app clearer is the default
clearer.

To see all available cache pools:

.. code-block:: terminal

    $ php bin/console cache:pool:list

Clear one pool:

.. code-block:: terminal

    $ php bin/console cache:pool:clear my_cache_pool

Clear all custom pools:

.. code-block:: terminal

    $ php bin/console cache:pool:clear cache.app_clearer

Clear all cache pools:

.. code-block:: terminal

    $ php bin/console cache:pool:clear --all

Clear all cache pools except some:

.. code-block:: terminal

    $ php bin/console cache:pool:clear --all --exclude=my_cache_pool --exclude=another_cache_pool

Clear all caches everywhere:

.. code-block:: terminal

    $ php bin/console cache:pool:clear cache.global_clearer

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
-------------------------------------

The Cache component uses the `probabilistic early expiration`_ algorithm to
protect against the :ref:`cache stampede <cache_stampede-prevention>` problem.
This means that some cache items are elected for early-expiration while they are
still fresh.

By default, expired cache items are computed synchronously. However, you can
compute them asynchronously by delegating the value computation to a background
worker using the :doc:`Messenger component </components/messenger>`. In this case,
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

            // this is just a random example; here you must do your own calculation
            return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        }
    }

This cache value will be requested from a controller, another service, etc.
In the following example, the value is requested from a controller::

    // src/Controller/CacheController.php
    namespace App\Controller;

    use App\Cache\CacheComputation;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Contracts\Cache\CacheInterface;
    use Symfony\Contracts\Cache\ItemInterface;

    class CacheController extends AbstractController
    {
        #[Route('/cache', name: 'cache')]
        public function index(CacheInterface $asyncCache, CacheComputation $cacheComputation): Response
        {
            // pass to the cache the service method that refreshes the item
            $cachedValue = $asyncCache->get('my_value', $cacheComputation)

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
message will be sent through to bus to schedule a background computation to refresh
the value.

.. _`probabilistic early expiration`: https://en.wikipedia.org/wiki/Cache_stampede#Probabilistic_early_expiration
