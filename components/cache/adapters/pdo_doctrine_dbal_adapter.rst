.. index::
    single: Cache Pool
    single: PDO Cache, Doctrine DBAL Cache

PDO & Doctrine DBAL Cache Adapter
=================================

.. versionadded:: 3.2

   The PDO & Doctrine DBAL adapter was introduced in Symfony 3.2.


This adapter stores the cached items a SQL database accessed through a PDO or a
Doctrine DBAL connection::

    use Symfony\Component\Cache\Adapter\PdoAdapter;

    $cache = new PdoAdapter(
        // a PDO, a Doctrine DBAL connection or DSN for lazy connecting through PDO
        $databaseConnectionOrDSN,
        // the string prefixed to the keys of the items stored in this cache
        $namespace = '',
        // in seconds; applied to cache items that don't define their own lifetime
        // 0 means to store the cache items indefinitely (i.e. until the database is cleared)
        $defaultLifetime = 0,
        // an array of options for configuring the database connection
        $options = array()
    );
