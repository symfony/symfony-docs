.. _reference-cache:

cache
~~~~~

.. _reference-cache-app:

app
...

**type**: ``string`` **default**: ``cache.adapter.filesystem``

The cache adapter used by the ``cache.app`` service. The FrameworkBundle
ships with multiple adapters: ``cache.adapter.apcu``, ``cache.adapter.system``,
``cache.adapter.filesystem``, ``cache.adapter.psr6``, ``cache.adapter.redis``,
``cache.adapter.memcached``, ``cache.adapter.pdo`` and
``cache.adapter.doctrine_dbal``.

There's also a special adapter called ``cache.adapter.array`` which stores
contents in memory using a PHP array and it's used to disable caching (mostly on
the ``dev`` environment).

.. tip::

    It might be tough to understand at the beginning, so to avoid confusion
    remember that all pools perform the same actions but on different media,
    depending on the adapter they are based on. Internally, a pool wraps the
    definition of an adapter.

default_doctrine_dbal_provider
..............................

**type**: ``string`` **default**: ``database_connection``

The service ID of the Doctrine DBAL connection to use as the default cache
provider. The provider is available as the ``cache.default_doctrine_dbal_provider``
service.

default_doctrine_provider
.........................

**type**: ``string``

The service name to use as your default Doctrine provider. The provider is
available as the ``cache.default_doctrine_provider`` service.

default_memcached_provider
..........................

**type**: ``string`` **default**: ``memcached://localhost``

The DSN used by the Memcached provider. The provider is available as the ``cache.default_memcached_provider``
service.

default_pdo_provider
....................

**type**: ``string`` **default**: ``doctrine.dbal.default_connection``

The service id of the database connection, which should be either a PDO or a
Doctrine DBAL instance. The provider is available as the ``cache.default_pdo_provider``
service.

default_psr6_provider
.....................

**type**: ``string``

The service name to use as your default PSR-6 provider. It is available as
the ``cache.default_psr6_provider`` service.

default_redis_provider
......................

**type**: ``string`` **default**: ``redis://localhost``

The DSN to use by the Redis provider. The provider is available as the ``cache.default_redis_provider``
service.

default_valkey_provider
.......................

**type**: ``string`` **default**: ``valkey://localhost``

The DSN to use by the Valkey provider. The provider is available as the ``cache.default_valkey_provider``
service.

directory
.........

**type**: ``string`` **default**: ``%kernel.cache_dir%/pools``

The path to the cache directory used by services inheriting from the
``cache.adapter.filesystem`` adapter (including ``cache.app``).

pools
.....

**type**: ``array``

A list of cache pools to be created by the framework extension.

.. seealso::

    For more information about how pools work, see :ref:`cache pools <component-cache-cache-pools>`.

To configure a Redis cache pool with a default lifetime of 1 hour, do the following:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            cache:
                pools:
                    cache.mycache:
                        adapter: cache.adapter.redis
                        default_lifetime: 3600

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:cache>
                    <framework:pool
                        name="cache.mycache"
                        adapter="cache.adapter.redis"
                        default-lifetime="3600"
                    />
                </framework:cache>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->cache()
                ->pool('cache.mycache')
                    ->adapters(['cache.adapter.redis'])
                    ->defaultLifetime(3600);
        };

adapter
"""""""

**type**: ``string`` **default**: ``cache.app``

The service name of the adapter to use. You can specify one of the default
services that follow the pattern ``cache.adapter.[type]``. Alternatively you
can specify another cache pool as base, which will make this pool inherit the
settings from the base pool as defaults.

.. note::

    Your service needs to implement the ``Psr\Cache\CacheItemPoolInterface`` interface.

clearer
"""""""

**type**: ``string``

The cache clearer used to clear your PSR-6 cache.

.. seealso::

    For more information, see :class:`Symfony\\Component\\HttpKernel\\CacheClearer\\Psr6CacheClearer`.

default_lifetime
""""""""""""""""

**type**: ``integer`` | ``string``

Default lifetime of your cache items. Give an integer value to set the default
lifetime in seconds. A string value could be an ISO 8601 time interval, like ``"PT5M"``
or a PHP date expression that is accepted by ``strtotime()``, like ``"5 minutes"``.

If no value is provided, the cache adapter will fall back to the default value on
the actual cache storage.

.. _reference-cache-pools-name:

name
""""

**type**: ``prototype``

Name of the pool you want to create.

.. note::

    Your pool name must differ from ``cache.app`` or ``cache.system``.

provider
""""""""

**type**: ``string``

Overwrite the default service name or DSN respectively, if you do not want to
use what is configured as ``default_X_provider`` under ``cache``. See the
description of the default provider setting above for information on how to
specify your specific provider.

public
""""""

**type**: ``boolean`` **default**: ``false``

Whether your service should be public or not.

tags
""""

**type**: ``boolean`` | ``string`` **default**: ``null``

Whether your service should be able to handle tags or not.
Can also be the service id of another cache pool where tags will be stored.

.. _reference-cache-prefix-seed:

prefix_seed
...........

**type**: ``string`` **default**: ``_%kernel.project_dir%.%kernel.container_class%``

This value is used as part of the "namespace" generated for the
cache item keys. A common practice is to use the unique name of the application
(e.g. ``symfony.com``) because that prevents naming collisions when deploying
multiple applications into the same path (on different servers) that share the
same cache backend.

It's also useful when using `blue/green deployment`_ strategies and more
generally, when you need to abstract out the actual deployment directory (for
example, when warming caches offline).

.. note::

    The ``prefix_seed`` option is used at compile time. This means
    that any change made to this value after container's compilation
    will have no effect.

.. _reference-cache-system:

system
......

**type**: ``string`` **default**: ``cache.adapter.system``

The cache adapter used by the ``cache.system`` service. It supports the same
adapters available for the ``cache.app`` service.

.. _`blue/green deployment`: https://martinfowler.com/bliki/BlueGreenDeployment.html
