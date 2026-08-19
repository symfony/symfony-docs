PDO Cache Adapter
=================

The PDO adapters store the cache items in a table of an SQL database.

.. note::

    This adapter implements :class:`Symfony\\Component\\Cache\\PruneableInterface`,
    allowing for manual :ref:`pruning of expired cache entries <component-cache-cache-pool-prune>`
    by calling the ``prune()`` method.

The :class:`Symfony\\Component\\Cache\\Adapter\\PdoAdapter` requires a :phpclass:`PDO`,
or `DSN`_ as its first parameter. You can pass a namespace,
default cache lifetime, and options array as the other optional arguments::

    use Symfony\Component\Cache\Adapter\PdoAdapter;

    $cache = new PdoAdapter(

        // a PDO connection or DSN for lazy connecting through PDO
        $databaseConnectionOrDSN,

        // the string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the database table is truncated or its rows are otherwise deleted)
        $defaultLifetime = 0,

        // an array of options for configuring the database table and connection
        $options = []
    );

The table where values are stored is created automatically on the first call to
the :method:`Symfony\\Component\\Cache\\Adapter\\PdoAdapter::save` method.
You can also create this table explicitly by calling the
:method:`Symfony\\Component\\Cache\\Adapter\\PdoAdapter::createTable` method in
your code.

.. tip::

    When passed a `Data Source Name (DSN)`_ string (instead of a database connection
    class instance), the connection will be lazy-loaded when needed.

.. _pdo-tag-aware-adapter:

Working with Tags
-----------------

.. versionadded:: 8.2

    The ``PdoTagAwareAdapter`` and the ``cache.adapter.pdo_tag_aware`` service
    were introduced in Symfony 8.2.

In order to use tag-based invalidation, you can wrap the adapter in
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`. However, the
dedicated :class:`Symfony\\Component\\Cache\\Adapter\\PdoTagAwareAdapter`
stores the tags in the same database, so the tag invalidation logic runs in the
database itself::

    use Symfony\Component\Cache\Adapter\PdoTagAwareAdapter;

    $cache = new PdoTagAwareAdapter($databaseConnectionOrDSN);

Tags are stored in their own table, created on the first save like the table of
the cache items. Use the ``db_tags_table``, ``db_tags_col`` and
``db_tags_tag_index_name`` options to change the name of that table, of its tag
column and of its index.

In Symfony applications, use the ``cache.adapter.pdo_tag_aware`` adapter. Pools
based on it are tag aware on their own, so Symfony doesn't wrap them in a
``TagAwareAdapter``. Their ``provider`` option is optional and defaults to the
``framework.cache.default_pdo_provider`` value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cache.yaml
        framework:
            cache:
                pools:
                    my_cache_pool:
                        adapter: cache.adapter.pdo_tag_aware
                        provider: 'pgsql:host=localhost'

    .. code-block:: php

        // config/packages/cache.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'cache' => [
                    'pools' => [
                        'my_cache_pool' => [
                            'adapter' => 'cache.adapter.pdo_tag_aware',
                            'provider' => 'pgsql:host=localhost',
                        ],
                    ],
                ],
            ],
        ]);

.. _`DSN`: https://php.net/manual/pdo.drivers.php
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
