Caching based on Resources
==========================

When all configuration resources are loaded, you may want to process the
configuration values and combine them all in one file. This file acts
like a cache. Its contents don't have to be regenerated every time the
application runs – only when the configuration resources are modified.

For example, the Symfony Routing component allows you to load all routes,
and then dump a URL matcher or a URL generator based on these routes. In
this case, when one of the resources is modified (and you are working
in a development environment), the generated file should be invalidated
and regenerated. This can be accomplished by making use of the
:class:`Symfony\\Component\\Config\\ConfigCache` class.

The example below shows you how to collect resources, then generate some
code based on the resources that were loaded and write this code to the
cache. The cache also receives the collection of resources that were used
for generating the code. By looking at the "last modified" timestamp of
these resources, the cache can tell if it is still fresh or that its contents
should be regenerated::

    use Symfony\Component\Config\ConfigCache;
    use Symfony\Component\Config\Resource\FileResource;

    $cachePath = __DIR__.'/cache/appUserMatcher.php';

    // the second argument indicates whether or not you want to use debug mode
    $userMatcherCache = new ConfigCache($cachePath, true);

    if (!$userMatcherCache->isFresh()) {
        // fill this with an array of 'users.yaml' file paths
        $yamlUserFiles = ...;

        $resources = [];

        foreach ($yamlUserFiles as $yamlUserFile) {
            // see the article "Loading resources" to
            // know where $delegatingLoader comes from
            $delegatingLoader->load($yamlUserFile);
            $resources[] = new FileResource($yamlUserFile);
        }

        // the code for the UserMatcher is generated elsewhere
        $code = ...;

        $userMatcherCache->write($code, $resources);
    }

    // you may want to require the cached code:
    require $cachePath;

In debug mode, a ``.meta`` file will be created in the same directory as
the cache file itself. This ``.meta`` file contains the serialized resources,
whose timestamps are used to determine if the cache is still fresh. When
not in debug mode, the cache is considered to be "fresh" as soon as it exists,
and therefore no ``.meta`` file will be generated.

You can explicitly define the absolute path to the meta file::

    use Symfony\Component\Config\ConfigCache;
    use Symfony\Component\Config\Resource\FileResource;

    $cachePath = __DIR__.'/cache/appUserMatcher.php';

    // the third optional argument indicates the absolute path to the meta file
    $userMatcherCache = new ConfigCache($cachePath, true, '/my/absolute/path/to/cache.meta');
