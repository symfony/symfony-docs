.. index::
    single: Cache Pool
    single: Proxy Cache

Proxy Cache Adapter
===================

This adapter is useful to integrate in your application cache pools not created
with the Symfony Cache component. As long as those cache pools implement the
``CacheItemPoolInterface`` interface, this adapter allows you to get items from
that external cache and save them in the Symfony cache of your application::

    use Symfony\Component\Cache\Adapter\ProxyAdapter;

    // ... create $nonSymfonyCache somehow
    $cache = new ProxyAdapter($nonSymfonyCache);

The adapter accepts two additional optional arguments: the namespace (``''`` by
default) and the default lifetime (``0`` by default).
