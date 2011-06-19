.. index::
   single: Performance; Byye code cache

Using a Byte Code Cache
=======================

In order to get optimal performance from Symfony2, it is recommended that
you use a so called byte code cache. The idea of a byte code cache is to remove
the need to constantly recompile the PHP source code. There are a number of
`byte code caches`_ available, some of which are open source. The most widely
used byte code cache is probably `APC`_

Further Optimizations
---------------------

Byte code caches usually monitor the source files for changes. This ensures
that if the source of a file changes, the byte code is recompiled automatically.
Though convenient, this obviously adds some overhead.

For this reason, some byte code caches offer an option to disable these checks.
Obviously, when disabling these checks, it will be up to the server admin
to ensure that the cache is cleared whenever any source files change.

To disable these checks in APC, simply add ``apc.stat=0`` to your php.ini
configuration.

.. _`byte code caches`: http://en.wikipedia.org/wiki/List_of_PHP_accelerators
.. _`APC`: http://php.net/manual/en/book.apc.php