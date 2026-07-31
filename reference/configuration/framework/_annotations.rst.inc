annotations
~~~~~~~~~~~

.. _reference-annotations-cache:

cache
.....

**type**: ``string`` **default**: ``php_array``

This option can be one of the following values:

php_array
    Use a PHP array to cache annotations in memory
file
    Use the filesystem to cache annotations
none
    Disable the caching of annotations

debug
.....

**type**: ``boolean`` **default**: ``%kernel.debug%``

Whether to enable debug mode for caching. If enabled, the cache will
automatically update when the original file is changed (both with code and
annotation changes). For performance reasons, it is recommended to disable
debug mode in production, which will happen automatically if you use the
default value.

file_cache_dir
..............

**type**: ``string`` **default**: ``%kernel.cache_dir%/annotations``

The directory to store cache files for annotations, in case
``annotations.cache`` is set to ``'file'``.
