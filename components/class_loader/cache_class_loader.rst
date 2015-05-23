.. index::
    single: APC; ApcClassLoader
    single: ClassLoader; ApcClassLoader
    single: ClassLoader; Cache
    single: ClassLoader; XcacheClassLoader
    single: XCache; XcacheClassLoader

Cache a Class Loader
====================

Introduction
------------

Finding the file for a particular class can be an expensive task. Luckily,
the ClassLoader component comes with two classes to cache the mapping
from a class to its containing file. Both the :class:`Symfony\\Component\\ClassLoader\\ApcClassLoader`
and the :class:`Symfony\\Component\\ClassLoader\\XcacheClassLoader` wrap
around an object which implements a ``findFile()`` method to find the file
for a class.

.. note::

    Both the ``ApcClassLoader`` and the ``XcacheClassLoader`` can be used
    to cache Composer's `autoloader`_.

ApcClassLoader
--------------

``ApcClassLoader`` wraps an existing class loader and caches calls to its
``findFile()`` method using `APC`_::

    require_once '/path/to/src/Symfony/Component/ClassLoader/ApcClassLoader.php';

    // instance of a class that implements a findFile() method, like the ClassLoader
    $loader = ...;

    // sha1(__FILE__) generates an APC namespace prefix
    $cachedLoader = new ApcClassLoader(sha1(__FILE__), $loader);

    // register the cached class loader
    $cachedLoader->register();

    // deactivate the original, non-cached loader if it was registered previously
    $loader->unregister();

XcacheClassLoader
-----------------

``XcacheClassLoader`` uses `XCache`_ to cache a class loader. Registering
it is straightforward::

    require_once '/path/to/src/Symfony/Component/ClassLoader/XcacheClassLoader.php';

    // instance of a class that implements a findFile() method, like the ClassLoader
    $loader = ...;

    // sha1(__FILE__) generates an XCache namespace prefix
    $cachedLoader = new XcacheClassLoader(sha1(__FILE__), $loader);

    // register the cached class loader
    $cachedLoader->register();

    // deactivate the original, non-cached loader if it was registered previously
    $loader->unregister();

.. _APC:        http://php.net/manual/en/book.apc.php
.. _autoloader: https://getcomposer.org/doc/01-basic-usage.md#autoloading
.. _XCache:     http://xcache.lighttpd.net
