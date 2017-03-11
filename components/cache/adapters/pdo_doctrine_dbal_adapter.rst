.. index::
    single: Cache Pool
    single: PDO Cache, Doctrine DBAL Cache

.. _`pdo-doctrine-adapter`:

PDO & Doctrine DBAL Cache Adapter
=================================

.. versionadded:: 3.2

   The PDO & Doctrine DBAL adapter was introduced in Symfony 3.2.


This adapter stores the cache items in an SQL database. It requires a `PDO`_,
`Doctrine DBAL Connection`_, or `Data Source Name (DSN)`_ as its first parameter, and
optionally a namespace, default cache lifetime, and options array as its second,
third, and forth parameters::

    use Symfony\Component\Cache\Adapter\PdoAdapter;

    $cache = new PdoAdapter(

        // a PDO, a Doctrine DBAL connection or DSN for lazy connecting through PDO
        $databaseConnectionOrDSN,

        // the string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the database table is truncated or its rows are otherwise deleted)
        $defaultLifetime = 0,

        // an array of options for configuring the database connection
        $options = array()
    );

.. tip::

    When passed a `Data Source Name (DSN)`_ string (instead of a database connection
    class instance), the connection will be lazy-loaded when needed.

.. _`PDO`: http://php.net/manual/en/class.pdo.php
.. _`Doctrine DBAL Connection`: https://github.com/doctrine/dbal/blob/master/lib/Doctrine/DBAL/Connection.php
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
