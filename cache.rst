.. index::
    single: Cache

Cache
=====

Using a cache is a great way of making your application run quicker. The Symfony cache
component ships with many adapters to different storages. Every adapter is
developed for high performance.

The following example shows a typical usage of the cache::

    use Symfony\Contracts\Cache\ItemInterface;

    // The callable will only be executed on a cache miss.
    $value = $pool->get('my_cache_key', function (ItemInterface $item) {
        $item->expiresAfter(3600);

        // ... do some HTTP request or heavy computations
        $computedValue = 'foobar';

        return $computedValue;
    });

    echo $value; // 'foobar'

    // ... and to remove the cache key
    $pool->delete('my_cache_key');

Symfony supports Cache Contracts, PSR-6/16 and Doctrine Cache interfaces.
You can read more about these at the :doc:`component documentation </components/cache>`.

.. deprecated:: 5.4

    Support for Doctrine Cache was deprecated in Symfony 5.4
    and it will be removed in Symfony 6.0.

.. _cache-configuration-with-frameworkbundle:

Configuring Cache with FrameworkBundle
--------------------------------------

When configuring the cache component there are a few concepts you should know
of:

**Pool**
    This is a service that you will interact with. Each pool will always have
    its own namespace and cache items. There is never a conflict between pools.
**Adapter**
    An adapter is a *template* that you use to create pools.
**Provider**
    A provider is a service that some adapters use to connect to the storage.
    Redis and Memcached are examples of such adapters. If a DSN is used as the
    provider then a service is automatically created.

There are two pools that are always enabled by default. They are ``cache.app`` and
``cache.system``. The system cache is used for things like annotations, serializer,
and validation. The ``cache.app`` can be used in your code. You can configure which
adapter (template) they use by using the ``app`` and ``system`` key like:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                app: cache.adapter.filesystem
                system: cache.adapter.system

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:cache app="cache.adapter.filesystem"
                    system="cache.adapter.system"
                />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->cache()
                ->app('cache.adapter.filesystem')
                ->system('cache.adapter.system')
            ;
        };


The Cache component comes with a series of adapters pre-configured:

* :doc:`cache.adapter.apcu </components/cache/adapters/apcu_adapter>`
* :doc:`cache.adapter.array </components/cache/adapters/array_cache_adapter>`
* :doc:`cache.adapter.doctrine </components/cache/adapters/doctrine_adapter>`
* :doc:`cache.adapter.filesystem </components/cache/adapters/filesystem_adapter>`
* :doc:`cache.adapter.memcached </components/cache/adapters/memcached_adapter>`
* :doc:`cache.adapter.pdo </components/cache/adapters/pdo_doctrine_dbal_adapter>`
* :doc:`cache.adapter.psr6 </components/cache/adapters/proxy_adapter>`
* :doc:`cache.adapter.redis </components/cache/adapters/redis_adapter>`
* :ref:`cache.adapter.redis_tag_aware <redis-tag-aware-adapter>` (Redis adapter optimized to work with tags)

.. versionadded:: 5.2

    ``cache.adapter.redis_tag_aware`` has been introduced in Symfony 5.2.

Some of these adapters could be configured via shortcuts. Using these shortcuts
will create pools with service IDs that follow the pattern ``cache.[type]``.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                directory: '%kernel.cache_dir%/pools' # Only used with cache.adapter.filesystem

                # service: cache.doctrine
                default_doctrine_provider: 'app.doctrine_cache'
                # service: cache.psr6
                default_psr6_provider: 'app.my_psr6_service'
                # service: cache.redis
                default_redis_provider: 'redis://localhost'
                # service: cache.memcached
                default_memcached_provider: 'memcached://localhost'
                # service: cache.pdo
                default_pdo_provider: 'doctrine.dbal.default_connection'

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!--
                default_doctrine_provider: Service: cache.doctrine
                default_psr6_provider: Service: cache.psr6
                default_redis_provider: Service: cache.redis
                default_memcached_provider: Service: cache.memcached
                default_pdo_provider: Service: cache.pdo
                -->
                <!-- "directory" attribute is only used with cache.adapter.filesystem -->
                <framework:cache directory="%kernel.cache_dir%/pools"
                    default_doctrine_provider="app.doctrine_cache"
                    default_psr6_provider="app.my_psr6_service"
                    default_redis_provider="redis://localhost"
                    default_memcached_provider="memcached://localhost"
                    default_pdo_provider="doctrine.dbal.default_connection"
                />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->cache()
                // Only used with cache.adapter.filesystem
                ->directory('%kernel.cache_dir%/pools')
                // Service: cache.doctrine
                ->defaultDoctrineProvider('app.doctrine_cache')
                // Service: cache.psr6
                ->defaultPsr6Provider('app.my_psr6_service')
                // Service: cache.redis
                ->defaultRedisProvider('redis://localhost')
                // Service: cache.memcached
                ->defaultMemcachedProvider('memcached://localhost')
                // Service: cache.pdo
                ->defaultPdoProvider('doctrine.dbal.default_connection')
            ;
        };

.. deprecated:: 5.4

    The ``default_doctrine_provider`` option was deprecated in Symfony 5.4 and
    it will be removed in Symfony 6.0.

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

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:cache default-memcached-provider="memcached://localhost">
                    <!-- creates a "custom_thing.cache" service
                         autowireable via "CacheInterface $customThingCache"
                         uses the "app" cache configuration -->
                    <framework:pool name="custom_thing.cache" adapter="cache.app"/>

                    <!-- creates a "my_cache_pool" service
                         autowireable via "CacheInterface $myCachePool" -->
                    <framework:pool name="my_cache_pool" adapter="cache.adapter.filesystem"/>

                    <!-- uses the default_memcached_provider from above -->
                    <framework:pool name="acme.cache" adapter="cache.adapter.memcached"/>

                    <!-- control adapter's configuration -->
                    <framework:pool name="foobar.cache" adapter="cache.adapter.memcached"
                        provider="memcached://user:password@example.com"
                    />

                    <!-- uses the "foobar.cache" pool as its backend but controls
                         the lifetime and (like all pools) has a separate cache namespace -->
                    <framework:pool name="short_cache" adapter="foobar.cache" default-lifetime="60"/>
                </framework:cache>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $cache = $framework->cache();
            $cache->defaultMemcachedProvider('memcached://localhost');

            // creates a "custom_thing.cache" service
            // autowireable via "CacheInterface $customThingCache"
            // uses the "app" cache configuration
            $cache->pool('custom_thing.cache')
                ->adapters(['cache.app']);

            // creates a "my_cache_pool" service
            // autowireable via "CacheInterface $myCachePool"
            $cache->pool('my_cache_pool')
                ->adapters(['cache.adapter.filesystem']);

            // uses the default_memcached_provider from above
            $cache->pool('acme.cache')
                ->adapters(['cache.adapter.memcached']);

             // control adapter's configuration
            $cache->pool('foobar.cache')
                ->adapters(['cache.adapter.memcached'])
                ->provider('memcached://user:password@example.com');

            $cache->pool('short_cache')
                ->adapters(['foobar.cache'])
                ->defaultLifetime(60);
        };

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

    // from a controller method
    public function listProducts(CacheInterface $customThingCache)
    {
        // ...
    }

    // in a service
    public function __construct(CacheInterface $customThingCache)
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
                app.cache.adapter.redis:
                    parent: 'cache.adapter.redis'
                    tags:
                        - { name: 'cache.pool', namespace: 'my_custom_namespace' }

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <service id="app.cache.adapter.redis" parent="cache.adapter.redis">
                        <tag name="cache.pool" namespace="my_custom_namespace"/>
                    </service>
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return function(ContainerConfigurator $configurator) {
                $services = $configurator->services();

                $services->set('app.cache.adapter.redis')
                    ->parent('cache.adapter.redis')
                    ->tag('cache.pool', ['namespace' => 'my_custom_namespace']);
            };

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

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:cache>
                    <framework:pool name="cache.my_redis" adapter="cache.adapter.redis" provider="app.my_custom_redis_provider"/>
                </framework:cache>
            </framework:config>

            <services>
                <service id="app.my_custom_redis_provider" class="\Redis">
                    <factory class="Symfony\Component\Cache\Adapter\RedisAdapter" method="createConnection"/>
                    <argument>redis://localhost</argument>
                    <argument type="collection">
                        <argument key="retry_interval">2</argument>
                        <argument key="timeout">10</argument>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        use Symfony\Component\Cache\Adapter\RedisAdapter;
        use Symfony\Component\DependencyInjection\ContainerBuilder;
        use Symfony\Config\FrameworkConfig;

        return static function (ContainerBuilder $container, FrameworkConfig $framework) {
            $framework->cache()
                ->pool('cache.my_redis')
                    ->adapters(['cache.adapter.redis'])
                    ->provider('app.my_custom_redis_provider');


            $container->register('app.my_custom_redis_provider', \Redis::class)
                ->setFactory([RedisAdapter::class, 'createConnection'])
                ->addArgument('redis://localhost')
                ->addArgument([
                    'retry_interval' => 2,
                    'timeout' => 10
                ])
            ;
        };

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

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:cache>
                    <framework:pool name="my_cache_pool" default-lifetime="31536000">
                        <framework:adapter name="cache.adapter.array"/>
                        <framework:adapter name="cache.adapter.apcu"/>
                        <framework:adapter name="cache.adapter.redis" provider="redis://user:password@example.com"/>
                    </framework:pool>
                </framework:cache>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->cache()
                ->pool('my_cache_pool')
                    ->defaultLifetime(31536000) // One year
                    ->adapters([
                        'cache.adapter.array',
                        'cache.adapter.apcu',
                        ['name' => 'cache.adapter.redis', 'provider' => 'redis://user:password@example.com'],
                    ])
            ;
        };

Using Cache Tags
----------------

In applications with many cache keys it could be useful to organize the data stored
to be able to invalidate the cache more efficiently. One way to achieve that is to
use cache tags. One or more tags could be added to the cache item. All items with
the same key could be invalidated with one function call::

    use Symfony\Contracts\Cache\ItemInterface;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;

    class SomeClass
    {
        private $myCachePool;

        // using autowiring to inject the cache pool
        public function __construct(TagAwareCacheInterface $myCachePool)
        {
            $this->myCachePool = $myCachePool;
        }

        public function someMethod()
        {
            $value0 = $this->myCachePool->get('item_0', function (ItemInterface $item) {
                $item->tag(['foo', 'bar']);

                return 'debug';
            });

            $value1 = $this->myCachePool->get('item_1', function (ItemInterface $item) {
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
                        adapter: cache.adapter.redis
                        tags: true

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:cache>
                  <framework:pool name="my_cache_pool" adapter="cache.adapter.redis" tags="true"/>
                </framework:cache>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->cache()
                ->pool('my_cache_pool')
                    ->tags(true)
                    ->adapters(['cache.adapter.redis'])
            ;
        };

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

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:cache>
                  <framework:pool name="my_cache_pool" adapter="cache.adapter.redis" tags="tag_pool"/>
                  <framework:pool name="tag_pool" adapter="cache.adapter.apcu"/>
                </framework:cache>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->cache()
                ->pool('my_cache_pool')
                    ->tags('tag_pool')
                    ->adapters(['cache.adapter.redis'])
            ;

            $framework->cache()
                ->pool('tag_pool')
                    ->adapters(['cache.adapter.apcu'])
            ;
        };

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

Clear all caches everywhere:

.. code-block:: terminal

    $ php bin/console cache:pool:clear cache.global_clearer

Encrypting the Cache
--------------------

.. versionadded:: 5.1

    The :class:`Symfony\\Component\\Cache\\Marshaller\\SodiumMarshaller`
    class was introduced in Symfony 5.1.

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
                    - '@Symfony\Component\Cache\Marshaller\SodiumMarshaller.inner'

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->

            <services>
                <service id="Symfony\Component\Cache\Marshaller\SodiumMarshaller" decorates="cache.default_marshaller">
                    <argument type="collection">
                        <argument>env(base64:CACHE_DECRYPTION_KEY)</argument>
                        <!-- use multiple keys in order to rotate them -->
                        <!-- <argument>env(base64:OLD_CACHE_DECRYPTION_KEY)</argument> -->
                    </argument>
                    <argument type="service" id="Symfony\Component\Cache\Marshaller\SodiumMarshaller.inner"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        use Symfony\Component\Cache\Marshaller\SodiumMarshaller;
        use Symfony\Component\DependencyInjection\ChildDefinition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setDefinition(SodiumMarshaller::class, new ChildDefinition('cache.default_marshaller'))
            ->addArgument(['env(base64:CACHE_DECRYPTION_KEY)'])
            // use multiple keys in order to rotate them
            //->addArgument(['env(base64:CACHE_DECRYPTION_KEY)', 'env(base64:OLD_CACHE_DECRYPTION_KEY)'])
            ->addArgument(new Reference(SodiumMarshaller::class.'.inner'));

.. caution::

    This will encrypt the values of the cache items, but not the cache keys. Be
    careful not the leak sensitive data in the keys.

When configuring multiple keys, the first key will be used for reading and
writing, and the additional key(s) will only be used for reading. Once all
cache items encrypted with the old key have expired, you can completely remove
``OLD_CACHE_DECRYPTION_KEY``.
