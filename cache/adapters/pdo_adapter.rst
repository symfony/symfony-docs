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

.. _`DSN`: https://php.net/manual/pdo.drivers.php
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
