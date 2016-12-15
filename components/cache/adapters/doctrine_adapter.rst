.. index::
    single: Cache Pool
    single: Doctrine Cache

Doctrine Cache Adapter
======================

This adapter wraps any `Doctrine Cache`_ provider so you can use them in your
application as if they were Symfony Cache adapters::

    use Doctrine\Common\Cache\SQLite3Cache;
    use Symfony\Component\Cache\Adapter\DoctrineAdapter;

    $sqliteDatabase = new \SQLite3(__DIR__.'/cache/data.sqlite');
    $doctrineCache = new SQLite3Cache($sqliteDatabase, 'tableName');
    $symfonyCache = new DoctrineAdapter($doctrineCache);

This adapter also defines two optional arguments called  ``namespace`` (default:
``''``) and ``defaultLifetime`` (default: ``0``) and adapts them to make them
work in the underlying Doctrine cache.

.. _`Doctrine Cache`: https://github.com/doctrine/cache
