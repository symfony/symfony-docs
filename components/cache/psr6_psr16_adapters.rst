Adapters For Interoperability between PSR-6 and PSR-16 Cache
============================================================

Sometimes, you may have a Cache object that implements the `PSR-16`_
standard, but need to pass it to an object that expects a :ref:`PSR-6 <cache-component-psr6-caching>`
cache adapter. Or, you might have the opposite situation. The cache component contains
two classes for bidirectional interoperability between PSR-6 and PSR-16 caches.

Using a PSR-16 Cache Object as a PSR-6 Cache
--------------------------------------------

Suppose you want to work with a class that requires a PSR-6 Cache pool object. For
example::

    use Psr\Cache\CacheItemPoolInterface;

    // just a made-up class for the example
    class GitHubApiClient
    {
        // ...

        // this requires a PSR-6 cache object
        public function __construct(CacheItemPoolInterface $cachePool)
        {
            // ...
        }
    }

But, you already have a PSR-16 cache object, and you'd like to pass this to the class
instead. No problem! The Cache component provides the
:class:`Symfony\\Component\\Cache\\Adapter\\Psr16Adapter` class for exactly
this use-case::

    use Symfony\Component\Cache\Adapter\Psr16Adapter;

    // $psr16Cache is the PSR-16 object that you want to use as a PSR-6 one

    // a PSR-6 cache that uses your cache internally!
    $psr6Cache = new Psr16Adapter($psr16Cache);

    // now use this wherever you want
    $githubApiClient = new GitHubApiClient($psr6Cache);

Using a PSR-6 Cache Object as a PSR-16 Cache
--------------------------------------------

Suppose you want to work with a class that requires a PSR-16 Cache object. For
example::

    use Psr\SimpleCache\CacheInterface;

    // just a made-up class for the example
    class GitHubApiClient
    {
        // ...

        // this requires a PSR-16 cache object
        public function __construct(CacheInterface $cache)
        {
            // ...
        }
    }

But, you already have a PSR-6 cache pool object, and you'd like to pass this to
the class instead. No problem! The Cache component provides the
:class:`Symfony\\Component\\Cache\\Psr16Cache` class for exactly
this use-case::

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\Psr16Cache;

    // the PSR-6 cache object that you want to use
    $psr6Cache = new FilesystemAdapter();

    // a PSR-16 cache that uses your cache internally!
    $psr16Cache = new Psr16Cache($psr6Cache);

    // now use this wherever you want
    $githubApiClient = new GitHubApiClient($psr16Cache);

.. _`PSR-16`: https://www.php-fig.org/psr/psr-16/
