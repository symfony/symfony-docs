.. _pdo-doctrine-adapter:

PDO & Doctrine DBAL Cache Adapter
=================================

The PDO and Doctrine DBAL adapters store the cache items in a table of an SQL database.

.. note::

    These adapters implement :class:`Symfony\\Component\\Cache\\PruneableInterface`,
    allowing for manual :ref:`pruning of expired cache entries <component-cache-cache-pool-prune>`
    by calling the ``prune()`` method.

Using PHP PDO
-------------

The :class:`Symfony\\Component\\Cache\\Adapter\\PdoAdapter` requires a :phpclass:`PDO`,
or `Data Source Name (DSN)`_ as its first parameter. You can pass a namespace,
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
    class instance), the connection will be lazy-loaded when needed. DBAL Connection
    are lazy-loaded by default; some additional options may be necessary to detect
    the database engine and version without opening the connection.

Using Doctrine DBAL
-------------------

The :class:`Symfony\\Component\\Cache\\Adapter\\DoctrineDbalAdapter` requires a
`Doctrine DBAL Connection`_, or `Doctrine DBAL URL`_ as its first parameter.
You can pass a namespace, default cache lifetime, and options array as the other
optional arguments::

    use Symfony\Component\Cache\Adapter\DoctrineDbalAdapter;

    $cache = new DoctrineDbalAdapter(

        // a Doctrine DBAL connection or DBAL URL
        $databaseConnectionOrURL,

        // the string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the database table is truncated or its rows are otherwise deleted)
        $defaultLifetime = 0,

        // an array of options for configuring the database table and connection
        $options = []
    );

.. note::

    DBAL Connection are lazy-loaded by default; some additional options may be
    necessary to detect the database engine and version without opening the
    connection.

.. _`Doctrine DBAL Connection`: https://github.com/doctrine/dbal/blob/master/src/Connection.php
.. _`Doctrine DBAL URL`: https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
