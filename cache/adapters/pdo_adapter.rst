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

Tag-Aware PDO Adapter
---------------------

.. versionadded:: 8.2

    The ``PdoTagAwareAdapter`` was introduced in Symfony 8.2.

:class:`Symfony\\Component\\Cache\\Adapter\\PdoTagAwareAdapter` takes the same
arguments as ``PdoAdapter`` and adds native support for
:ref:`cache tags <cache-component-tags>`. Tags live in a second table, with one
row per item and tag and an index on the tag column, so invalidating a tag is a
single ``DELETE`` statement::

    use Symfony\Component\Cache\Adapter\PdoTagAwareAdapter;

    $cache = new PdoTagAwareAdapter($databaseConnectionOrDSN);

    $item = $cache->getItem('cache_key');
    $item->set('cache_value');
    $item->tag(['tag_1', 'tag_2']);
    $cache->save($item);

    // removes every item tagged with "tag_1"
    $cache->invalidateTags(['tag_1']);

Prefer this adapter over wrapping ``PdoAdapter`` in a
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`, which stores the
tags as cache items and needs extra round trips to the database on every save
and invalidation.

The items table is created on the first call to the ``save()`` method and the
tags table the first time a tagged item is saved. Unlike ``PdoAdapter``, this
adapter doesn't expose a method to create them explicitly. Invalidating a tag
only deletes the cache items, so call ``prune()`` to also delete the tag rows
left behind.

On top of the options of ``PdoAdapter``, three options configure the tags table:

==========================  =============================
Option                      Default
==========================  =============================
``db_tags_table``           ``cache_tags``
``db_tags_col``             ``item_tag``
``db_tags_tag_index_name``  ``idx_cache_tags_item_tag``
==========================  =============================

.. _`DSN`: https://php.net/manual/pdo.drivers.php
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
