.. index::
   single: Tests

Performance
===========

Symfony2 is fast, right out of the box. Of course, if you really need speed,
there are many ways that you can make Symfony even faster. In this chapter,
you'll explore many of the most common and powerful ways to make your Symfony
application even faster.

.. index::
   single: Performance; Byte code cache

Use a Byte Code Cache (e.g. APC)
--------------------------------

One the best (and easiest) things that you should do to improve your performance
is to use a "byte code cache". The idea of a byte code cache is to remove
the need to constantly recompile the PHP source code. There are a number of
`byte code caches`_ available, some of which are open source. The most widely
used byte code cache is probably `APC`_

Using a byte code cache really has no downside, and Symfony2 has been architected
to perform really well in this type of environment.

Further Optimizations
~~~~~~~~~~~~~~~~~~~~~

Byte code caches usually monitor the source files for changes. This ensures
that if the source of a file changes, the byte code is recompiled automatically.
This is really convenient, but obviously adds overhead.

For this reason, some byte code caches offer an option to disable these checks.
Obviously, when disabling these checks, it will be up to the server admin
to ensure that the cache is cleared whenever any source files change. Otherwise,
the updates you've made won't be seen.

For example, to disable these checks in APC, simply add ``apc.stat=0`` to
your php.ini configuration.

.. index::
   single: Performance; Autoloader

Use an Autoloader that caches (e.g. ``ApcUniversalClassLoader``)
----------------------------------------------------------------

By default, the Symfony2 standard edition uses the ``UniversalClassLoader``
in the `autoloader.php`_ file. This autoloader is easy to use, as it will
automatically find any new classes that you've placed in the registered
directories.

Unfortunately, this comes at a cost, as the loader iterates over all configured
namespaces to find a particular file, making ``file_exists`` calls until it
finally finds the file it's looking for.

The simplest solution is to cache the location of each class after it's located
the first time. Symfony comes with a class - ``ApcUniversalClassLoader`` -
loader that extends the ``UniversalClassLoader`` and stores the class locations
in APC.

To use this class loader, simply adapt your ``autoloader.php`` as follows:

.. code-block:: php

    // app/autoload.php
    require __DIR__.'/../vendor/symfony/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

    use Symfony\Component\ClassLoader\ApcUniversalClassLoader;

    $loader = new ApcUniversalClassLoader('some caching unique prefix');
    // ...

.. note::

    When using the APC autoloader, if you add new classes, they will be found
    automatically and everything will work the same as before (i.e. no
    reason to "clear" the cache). However, if you change the location of a
    particular namespace or prefix, you'll need to flush your APC cache. Otherwise,
    the autoloader will still be looking at the old location for all classes
    inside that namespace.

.. index::
   single: Performance; Bootstrap files

Use Bootstrap Files
-------------------

To ensure optimal flexibility and code reuse, Symfony2 applications leverage
a variety of classes and 3rd party components. But loading all of these classes
from separate files on each request can result in some overhead. To reduce
this overhead, the Symfony2 Standard Edition provides a script to generate
a so-called `bootstrap file`_, consisting of multiple classes definitions
in a single file. By including this file (which contains a copy of many of
the core classes), Symfony no longer needs to include any of the source files
containing those classes. This will reduce disc IO quite a bit.

If you're using the Symfony2 Standard Edition, then you're probably already
using the bootstrap file. To be sure, open your front controller (usually
``app.php``) and check to make sure that the following line exists::

    require_once __DIR__.'/../app/bootstrap.php.cache';

Note that there are two disadvantages when using a bootstrap file:

* the file needs to be regenerated whenever any of the original sources change
  (i.e. when you update the Symfony2 source or vendor libraries);

* when debugging, one will need to place break points inside the bootstrap file.

If you're using Symfony2 Standard Edition, the bootstrap file is automatically
rebuilt after updating the vendor libraries via the ``php bin/vendors install``
command.

Bootstrap Files and Byte Code Caches
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Even when using a byte code cache, performance will improve when using a bootstrap
file since there will be less files to monitor for changes. Of course if this
feature is disabled in the byte code cache (e.g. ``apc.stat=0`` in APC), there
is no longer a reason to use a bootstrap file.

.. _`byte code caches`: http://en.wikipedia.org/wiki/List_of_PHP_accelerators
.. _`APC`: http://php.net/manual/en/book.apc.php
.. _`autoloader.php`: https://github.com/symfony/symfony-standard/blob/master/app/autoload.php
.. _`bootstrap file`: https://github.com/sensio/SensioDistributionBundle/blob/master/Resources/bin/build_bootstrap.php
