Doctrine DBAL Cache Adapter
===========================

The Doctrine DBAL adapters store the cache items in a table of an SQL database.

.. note::

    This adapter implements :class:`Symfony\\Component\\Cache\\PruneableInterface`,
    allowing for manual :ref:`pruning of expired cache entries <component-cache-cache-pool-prune>`
    by calling the ``prune()`` method.

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

The adapter uses SQL syntax that is optimized for database server that it is connected to.
The following database servers are known to be compatible:

* MySQL 5.7 and newer
* MariaDB 10.2 and newer
* Oracle 10g and newer
* SQL Server 2012 and newer
* SQLite 3.24 or later
* PostgreSQL 9.5 or later

.. note::

    Newer releases of Doctrine DBAL might increase these minimal versions. Check
    the manual page on `Doctrine DBAL Platforms`_ if your database server is
    compatible with the installed Doctrine DBAL version.

.. _`Doctrine DBAL Connection`: https://github.com/doctrine/dbal/blob/master/src/Connection.php
.. _`Doctrine DBAL URL`: https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/configuration.html#connecting-using-a-url
.. _`Doctrine DBAL Platforms`: https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/platforms.html
