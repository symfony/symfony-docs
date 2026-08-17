PDO Cache Adapter
=================

The PDO adapters store the cache items in a table of an SQL database.

.. note::

    This adapter implements :class:`Symfony\\Component\\Cache\\PruneableInterface`,
    allowing for manual :ref:`pruning of expired cache entries <component-cache-cache-pool-prune>`
    by calling the ``prune()`` method.

The :class:`Symfony\\Component\\Cache\\Adapter\\PdoAdapter` requires a :phpclass:`PDO`,
or `DSN`_ as its first parameter. You can pass a namespace, default cache
lifetime, options array and :ref:`marshaller <cache-component-marshalling>`
as the other optional arguments::

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
        $options = [],

        // an optional marshaller object used to serialize the cache items before storing them
        $marshaller = null
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

    The ``PdoTagAwareAdapter`` was introduced in Symfony 8.2.

The :class:`Symfony\\Component\\Cache\\Adapter\\PdoTagAwareAdapter` takes
the same arguments as ``PdoAdapter`` and adds native support for
:ref:`cache tags <cache-component-tags>`. It stores the tags in a second table
(one row per item and tag, with an index on the tag column), so invalidating
tags runs a single ``DELETE`` query::

    use Symfony\Component\Cache\Adapter\PdoTagAwareAdapter;

    $cache = new PdoTagAwareAdapter($databaseConnectionOrDSN);

    $item = $cache->getItem('cache_key');
    $item->set('cache_value');
    $item->tag(['tag_1', 'tag_2']);
    $cache->save($item);

    // deletes all the items tagged with "tag_1"
    $cache->invalidateTags(['tag_1']);

Use this adapter instead of wrapping ``PdoAdapter`` with the
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`, which stores
the tag versions as regular cache items and needs extra queries to check them
when reading and saving tagged items.

Both tables are created automatically when they are first needed. Unlike
``PdoAdapter``, this adapter doesn't provide a method to create them explicitly.

Invalidating tags deletes the tagged items but keeps their rows in the tags
table. Call the ``prune()`` method to also delete the rows of the tags table
that don't belong to any existing item.

In addition to the options of ``PdoAdapter``, this adapter defines the following
options to configure the tags table:

==========================  ===========================
Option                      Default value
==========================  ===========================
``db_tags_table``           ``cache_tags``
``db_tags_col``             ``item_tag``
``db_tags_tag_index_name``  ``idx_cache_tags_item_tag``
==========================  ===========================

.. _`DSN`: https://php.net/manual/pdo.drivers.php
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
