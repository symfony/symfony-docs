.. index::
    single: Cache

Cache
=====

Using cache is a great way of making your application run quicker. The Symfony cache
component is shipped with many adapters to different storages. Every adapter is
developed for high performance.

Basic uses of the cache looks like this::

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

Symfony supports PSR-6 and PSR-16 cache interfaces. You can read more about
these at the :doc:`component documentation </components/cache>`.

.. versionadded:: 4.2

    The cache contracts were introduced in Symfony 4.2.

Configuring Cache with FrameworkBundle
--------------------------------------

When configuring the cache component there are a few concepts you should know
of:

**Pool**
    This is a service that you will interact with. Each pool will always have
    its own namespace and cache items. There is never a conflict between pools.
**Adapter**
    An adapter is a *template* that you use to create Pools.
**Provider**
    A provider is a service that some adapters are using to connect to the storage.
    Redis and Memcached are example of such adapters. If a DSN is used as the
    provider then a service is automatically created.

There are two pools that are always enabled by default. They are ``cache.app`` and
``cache.system``. The system cache is use for things like annotations, serializer,
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
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:cache app="cache.adapter.filesystem"
                    system="cache.adapter.system"
                />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        $container->loadFromExtension('framework', [
            'cache' => [
                'app' => 'cache.adapter.filesystem',
                'system' => 'cache.adapter.system',
            ],
        ]);

The Cache component comes with a series of adapters already created:

* :doc:`cache.adapter.apcu </components/cache/adapters/apcu_adapter>`
* :doc:`cache.adapter.array </components/cache/adapters/array_cache_adapter>`
* :doc:`cache.adapter.doctrine </components/cache/adapters/doctrine_adapter>`
* :doc:`cache.adapter.filesystem </components/cache/adapters/filesystem_adapter>`
* :doc:`cache.adapter.memcached </components/cache/adapters/memcached_adapter>`
* :doc:`cache.adapter.pdo </components/cache/adapters/pdo_doctrine_dbal_adapter>`
* :doc:`cache.adapter.redis </components/cache/adapters/redis_adapter>`
* :doc:`PHPFileAdapter </components/cache/adapters/php_files_adapter>`
* :doc:`PHPArrayAdapter </components/cache/adapters/php_array_cache_adapter>`

* :doc:`ChainAdapter </components/cache/adapters/chain_adapter>`
* :doc:`ProxyAdapter </components/cache/adapters/proxy_adapter>`
* ``cache.adapter.psr6``

* ``cache.adapter.system``
* ``NullAdapter``

Some of these adapters could be configured via shortcuts. Using these shortcuts
will create pool with service id of ``cache.[type]``

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
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <!--
                default_doctrine_provider: Service: cache.doctrine
                default_psr6_provider: Service: cache.psr6
                default_redis_provider: Service: cache.redis
                default_memcached_provider: Service: cache.memcached
                default_pdo_provider: Service: cache.pdo
                -->
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
        $container->loadFromExtension('framework', [
            'cache' => [
                // Only used with cache.adapter.filesystem
                'directory' => '%kernel.cache_dir%/pools',

                // Service: cache.doctrine
                'default_doctrine_provider' => 'app.doctrine_cache',
                // Service: cache.psr6
                'default_psr6_provider' => 'app.my_psr6_service',
                // Service: cache.redis
                'default_redis_provider' => 'redis://localhost',
                // Service: cache.memcached
                'default_memcached_provider' => 'memcached://localhost',
                // Service: cache.pdo
                'default_pdo_provider' => 'doctrine.dbal.default_connection',
            ],
        ]);

Creating Custom Pools
---------------------

You can also create more customized pools. All you need is an adapter:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                default_memcached_provider: 'memcached://localhost'
                pools:
                    my_cache_pool:
                        adapter: cache.adapter.array
                    cache.acme:
                        adapter: cache.adapter.memcached
                    cache.foobar:
                        adapter: cache.adapter.memcached
                        provider: 'memcached://user:password@example.com'

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:cache default_memcached_provider="memcached://localhost">
                  <framework:pool name="my_cache_pool" adapter="cache.adapter.array"/>
                  <framework:pool name="cache.acme" adapter="cache.adapter.memcached"/>
                  <framework:pool name="cache.foobar" adapter="cache.adapter.memcached" provider="memcached://user:password@example.com"/>
                </framework:cache>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        $container->loadFromExtension('framework', [
            'cache' => [
                'default_memcached_provider' => 'memcached://localhost',
                'pools' => [
                    'my_cache_pool' => [
                        'adapter' => 'cache.adapter.array',
                    ],
                    'cache.acme' => [
                        'adapter' => 'cache.adapter.memcached',
                    ],
                    'cache.foobar' => [
                        'adapter' => 'cache.adapter.memcached',
                        'provider' => 'memcached://user:password@example.com',
                    ],
                ],
            ],
        ]);


The configuration above will create 3 services: ``my_cache_pool``, ``cache.acme``
and ``cache.foobar``.  The ``my_cache_pool`` pool is using the ArrayAdapter
and the other two are using the :doc:`MemcachedAdapter </components/cache/adapters/memcached_adapter>`.
The ``cache.acme`` pool is using the Memcached server on localhost and ``cache.foobar``
is using the Memcached server at example.com.

For advanced configurations it could sometimes be useful to use a pool as an adapter.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                app: my_configured_app_cache
                pools:
                    my_cache_pool:
                        adapter: cache.adapter.memcached
                        provider: 'memcached://user:password@example.com'
                    cache.short_cache:
                        adapter: my_cache_pool
                        default_lifetime: 60
                    cache.long_cache:
                        adapter: my_cache_pool
                        default_lifetime: 604800
                    my_configured_app_cache:
                        # "cache.adapter.filesystem" is the default for "cache.app"
                        adapter: cache.adapter.filesystem
                        default_lifetime: 3600

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:cache app="my_cache_pool">
                  <framework:pool name="my_cache_pool" adapter="cache.adapter.memcached" provider="memcached://user:password@example.com"/>
                  <framework:pool name="cache.short_cache" adapter="my_cache_pool" default_lifetime="604800"/>
                  <framework:pool name="cache.long_cache" adapter="my_cache_pool" default_lifetime="604800"/>
                  <!-- "cache.adapter.filesystem" is the default for "cache.app" -->
                  <framework:pool name="my_configured_app_cache" adapter="cache.adapter.filesystem" default_lifetime="3600"/>
                </framework:cache>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        $container->loadFromExtension('framework', [
            'cache' => [
                'app' => 'my_configured_app_cache',
                'pools' => [
                    'my_cache_pool' => [
                        'adapter' => 'cache.adapter.memcached',
                        'provider' => 'memcached://user:password@example.com',
                    ],
                    'cache.short_cache' => [
                        'adapter' => 'cache.adapter.memcached',
                        'default_lifetime' => 60,
                    ],
                    'cache.long_cache' => [
                        'adapter' => 'cache.adapter.memcached',
                        'default_lifetime' => 604800,
                    ],
                    'my_configured_app_cache' => [
                        // "cache.adapter.filesystem" is the default for "cache.app"
                        'adapter' => 'cache.adapter.filesystem',
                        'default_lifetime' => 3600,
                    ],
                ],
            ],
        ]);

Custom Provider Options
-----------------------

Some providers have specific options that could be configured. The
:doc:`RedisAdapter </components/cache/adapters/redis_adapter>` allows you to
create providers with option ``timeout``, ``retry_interval``. etc. To use these
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
                    - [ retry_interval: 2, timeout: 10 ]

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
                  <framework:pool name="cache.my_redis" adapter="cache.adapter.redis" provider="app.my_custom_redis_provider"/>
                </framework:cache>
            </framework:config>

            <services>
                <service id="app.my_custom_redis_provider" class="\Redis">
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
        $container->loadFromExtension('framework', [
            'cache' => [
                'pools' => [
                    'cache.my_redis' => [
                        'adapter' => 'cache.adapter.redis',
                        'provider' => 'app.my_custom_redis_provider',
                    ],
                ],
            ],
        ]);

        $container->getDefinition('app.my_custom_redis_provider', \Redis::class)
            ->addArgument('redis://localhost')
            ->addArgument([
                'retry_interval' => 2,
                'timeout' => 10
            ]);

Creating a Cache Chain
----------------------

Different cache adapters has different strengths and weaknesses. Some might be really
quick but small and some may be able to contain a lot of data but are quite slow.
To get the best of both worlds you may use a chain of adapters. The idea is to
first look at the quick adapter and then move on to slower adapters. In the worst
case the value needs to be recalculated.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                pools:
                    my_cache_pool:
                        adapter: app.my_cache_chain_adapter
                    cache.my_redis:
                        adapter: cache.adapter.redis
                        provider: 'redis://user:password@example.com'

        services:
            app.my_cache_chain_adapter:
                class: Symfony\Component\Cache\Adapter\ChainAdapter
                arguments:
                    - ['cache.adapter.array', 'cache.my_redis', 'cache.adapter.file']
                    - 31536000 # One year

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
                  <framework:pool name="my_cache_pool" adapter="app.my_cache_chain_adapter"/>
                  <framework:pool name="cache.my_redis" adapter="cache.adapter.redis" provider="redis://user:password@example.com"/>
                </framework:cache>
            </framework:config>

            <services>
                <service id="app.my_cache_chain_adapter" class="Symfony\Component\Cache\Adapter\ChainAdapter">
                    <argument type="collection">
                        <argument type="service" value="cache.adapter.array"/>
                        <argument type="service" value="cache.my_redis"/>
                        <argument type="service" value="cache.adapter.file"/>
                    </argument>
                    <argument>31536000</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        $container->loadFromExtension('framework', [
            'cache' => [
                'pools' => [
                    'my_cache_pool' => [
                        'adapter' => 'app.my_cache_chain_adapter',
                    ],
                    'cache.my_redis' => [
                        'adapter' => 'cache.adapter.redis',
                        'provider' => 'redis://user:password@example.com',
                    ],
                ],
            ],
        ]);

        $container->getDefinition('app.my_cache_chain_adapter', \Symfony\Component\Cache\Adapter\ChainAdapter::class)
            ->addArgument([
                new Reference('cache.adapter.array'),
                new Reference('cache.my_redis'),
                new Reference('cache.adapter.file'),
            ])
            ->addArgument(31536000);

.. note::

    In this configuration there is a ``cache.my_redis`` pool that is used as an
    adapter in the ``app.my_cache_chain_adapter``


Using Cache Tags
----------------

In applications with many cache keys it could be useful to organize the data stored
to be able to invalidate the cache more efficient. One way to achieve that is to
use cache tags. One or more tags could be added to the cache item. All items with
the same key could be invalidate with one function call::

    use Symfony\Contracts\Cache\ItemInterface;

    $value0 = $pool->get('item_0', function (ItemInterface $item) {
        $item->tag(['foo', 'bar'])

        return 'debug';
    });

    $value1 = $pool->get('item_1', function (ItemInterface $item) {
        $item->tag('foo')

        return 'debug';
    });

    // Remove all cache keys tagged with "bar"
    $pool->invalidateTags(['bar']);

The cache adapter needs to implement :class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface``
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
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:cache>
                  <framework:pool name="my_cache_pool" adapter="cache.adapter.redis" tags="true"/>
                </framework:cache>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        $container->loadFromExtension('framework', [
            'cache' => [
                'pools' => [
                    'my_cache_pool' => [
                        'adapter' => 'cache.adapter.redis',
                        'tags' => true,
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
        $container->loadFromExtension('framework', [
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
        ]);

.. note::

    The interface :class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface`` is
    autowired to the ``cache.app`` service.

Clearing the Cache
------------------

To clear the cache you can use the ``bin/console cache:pool:clear [pool]`` command.
That will remove all the entries from your storage and you wil have to recalculate
all values. You can also group your pools into "cache clearers". There are 3 cache
clearers by default:

* ``cache.global_clearer``
* ``cache.system_clearer``
* ``cache.app_clearer``

The global clearer clears all the cache in every pool. The system cache clearer
is used in the ``bin/console cache:clear`` command. The app clearer is the default
clearer.

Clear one pool:

.. code-block:: terminal

    $ php bin/console cache:pool:clear my_cache_pool

Clear all custom pools:

.. code-block:: terminal

    $ php bin/console cache:pool:clear cache.app_clearer

Clear all caches everywhere:

.. code-block:: terminal

    $ php bin/console cache:pool:clear cache.global_clearer
