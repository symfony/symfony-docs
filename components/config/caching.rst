.. index::
   single: Config; Caching based on resources

Caching based on resources
==========================

When all configuration resources are loaded, you may want to process the configuration values and
combine them all in one file. This file acts like a cache. It’s contents don’t have to be
regenerated every time the application runs – only when the configuration resources are modified.

For example, the Symfony Routing component allows you to load all routes, and then dump a URL
matcher or a URL generator based on these routes. In this case, when one of the resources is
modified (and you are working in a development environment), the generated file should be
invalidated and regenerated. This can be accomplished by making use of the
:class:`Symfony\\Component\\Config\\ConfigCache` class.

The example below shows you how to collect resources, then generate some code based on the
resources that were loaded, and write this code to the cache. The cache also receives the
collection of resources that were used for generating the code. By looking at the “last modified”
timestamp of these resources, the cache can tell if it is still fresh or that it’s contents should
be regenerated.

.. code-block:: php

    use Symfony\Component\Config\ConfigCache;

    // $yamlUserFiles is filled before with an array of 'users.yml' file paths

    $resources = array();

    foreach ($yamlUserFiles as $yamlUserFile) {
        $resources[] = new FileResource($yamlUserFile);
    }

    $cachePath = __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'appUserMatcher.php';

    $userMatcherCache = new ConfigCache($cachePath, true);
    // the second constructor argument indicates whether or not we are in debug mode

    if (!$userMatcherCache->isFresh()) {
        foreach ($resources as $resource) {
            $delegatingLoader->load($resource->getResource());
        }

        // The code for the UserMatcher is generated elsewhere
        // $code = ...;
        $userMatcherCache->write($code, $resources);
    }

    // you may want to require the cached code:
    require $cachePath;

A ``.meta`` file is created in the same directory as the cache file itself. This ``.meta`` file
contains the serialized resources, for later reference.
