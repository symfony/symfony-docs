.. index::
   single: Tests

Performance
===========

Symfony is fast, right out of the box. Of course, if you really need speed, the
following performance checklists show how to make your application even faster.

Symfony Application Checklist
-----------------------------

...

Install APCu Polyfill if your server uses APC
    If your production server still uses the legacy APC PHP extension instead of
    OPcache, install the `APCu Polyfill component`_ in your application to enable
    compatibility with `APCu PHP functions`_ and unlock support for advanced
    Symfony features, such as the APCu Cache adapter.

Development Machine Checklist
-----------------------------

...

Production Server Checklist
---------------------------

* :ref:`Use the OPcache byte code cache <performance-use-opcache>`
* :ref:`Configure OPcache for maximum performance <performance-configure-opcache>`
* :ref:`Don't check PHP timestamps <performance-dont-check-timestamps>`
* :ref:`Configure the PHP realpath Cache <performance-configure-realpath-cache>`
* :ref:`Optimize Composer Autoloader <performance-optimize-composer-autoloader>`

.. index::
   single: Performance; Byte code cache

.. _performance-use-opcache:

Use the OPcache byte code cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

OPcache stores the compiled PHP files to avoid having to recompile them for
every request. There are some `byte code caches`_ available, but as of PHP
5.5, PHP comes with `OPcache`_ built-in. For older versions, the most widely
used byte code cache is `APC`_.

.. _performance-configure-opcache:

Configure OPcache for maximum performance
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default OPcache configuration is not suited for Symfony application, so
it's recommended to change these settings as follows:

.. code-block:: ini

    ; php.ini
    ; maximum memory that OPcache can use to store compiled PHP files
    opcache.memory_consumption=256M

    ; maximum number of files that can be stored in the cache
    opcache.max_accelerated_files=20000

.. _performance-dont-check-timestamps:

Don't check PHP timestamps
~~~~~~~~~~~~~~~~~~~~~~~~~~

In production servers, PHP files should never change, unless a new application
version is deployed. However, by default OPcache checks if cached files have
changed their contents since caching them. This check introduces some overhead
that can be avoided as follows:

.. code-block:: ini

    ; php.ini

    ; after each deploy, call `opcache_reset()` or restart the web server
    ; to empty the cache and regenerate the cached files. Otherwise you won't
    ; see the updates made in the application
    opcache.validate_timestamps=0

.. note::

    The OPcache is different for the web server and the command console.
    You cannot clear the web server OPcache by executing some command
    in your terminal. You either need to restart the web server or call the
    ``opcache_reset()`` function via the web server (i.e. by having this in
    a script that you execute over the web).

.. _performance-configure-realpath-cache:

Configure the PHP realpath Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a relative path is transformed into its real and absolute path, PHP
caches the result to improve performance. The default config of this cache
is not suited for applications that open many PHP files, such as Symfony.
It's recommended to change these settings as follows:

.. code-block:: ini

    ; php.ini
    ; maximum memory allocated to store the results
    realpath_cache_size=4096K

    ; save the results for 10 minutes (600 seconds)
    realpath_cache_ttl=600

.. index::
   single: Performance; Autoloader

.. _performance-optimize-composer-autoloader:

Optimize Composer Autoloader
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The class loader used while developing the application is optimized to find
new and changed classes. In production servers, PHP files should never change,
unless a new application version is deployed. That's why you can optimize
Composer's autoloader to scan the entire application once and build a "class map",
which is a big array of the locations of all the classes and it's stored
in ``vendor/composer/autoload_classmap.php``.

Execute this command to generate the class map (and make it part of your
deployment process too):

.. code-block:: bash

    $ composer dump-autoload --optimize --no-dev --classmap-authoritative

* ``--optimize`` dumps every PSR-0 and PSR-4 compatible class used in your
  application;
* ``--no-dev`` excludes the classes that are only needed in the development
  environment (e.g. tests);
* ``--classmap-authoritative`` prevents Composer from scanning the file
  system for classes that are not found in the class map.


-----

.. TODO:: reword the rest of this article.


Caching the Autoloader with APC
-------------------------------

Another solution is to cache the location of each class after it's located
the first time. Symfony comes with a class - :class:`Symfony\\Component\\ClassLoader\\ApcClassLoader` -
that does exactly this. To use it, just adapt your front controller file.
If you're using the Standard Distribution, this code should already be available
as comments in this file::

    // app.php
    // ...

    $loader = require_once __DIR__.'/../app/bootstrap.php.cache';

    // Use APC for autoloading to improve performance
    // Change 'sf2' by the prefix you want in order
    // to prevent key conflict with another application
    /*
    $loader = new ApcClassLoader('sf2', $loader);
    $loader->register(true);
    */

    // ...

For more details, see :doc:`/components/class_loader/cache_class_loader`.

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

To ensure optimal flexibility and code reuse, Symfony applications leverage
a variety of classes and 3rd party components. But loading all of these classes
from separate files on each request can result in some overhead. To reduce
this overhead, the Symfony Standard Edition provides a script to generate
a so-called `bootstrap file`_, consisting of multiple classes definitions
in a single file. By including this file (which contains a copy of many of
the core classes), Symfony no longer needs to include any of the source files
containing those classes. This will reduce disc IO quite a bit.

If you're using the Symfony Standard Edition, then you're probably already
using the bootstrap file. To be sure, open your front controller (usually
``app.php``) and check to make sure that the following line exists::

    require_once __DIR__.'/../app/bootstrap.php.cache';

Note that there are two disadvantages when using a bootstrap file:

* the file needs to be regenerated whenever any of the original sources change
  (i.e. when you update the Symfony source or vendor libraries);

* when debugging, one will need to place break points inside the bootstrap file.

If you're using the Symfony Standard Edition, the bootstrap file is automatically
rebuilt after updating the vendor libraries via the ``composer install`` command.

Bootstrap Files and Byte Code Caches
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Even when using a byte code cache, performance will improve when using a bootstrap
file since there will be fewer files to monitor for changes. Of course, if this
feature is disabled in the byte code cache (e.g. ``apc.stat=0`` in APC), there
is no longer a reason to use a bootstrap file.

Learn more
----------

* :doc:`/http_cache/varnish`
* :doc:`/http_cache/form_csrf_caching`

.. _`byte code caches`: https://en.wikipedia.org/wiki/List_of_PHP_accelerators
.. _`OPcache`: http://php.net/manual/en/book.opcache.php
.. _`APC`: http://php.net/manual/en/book.apc.php
.. _`APCu Polyfill component`: https://github.com/symfony/polyfill-apcu
.. _`APCu PHP functions`: http://php.net/manual/en/ref.apcu.php
.. _`bootstrap file`: https://github.com/sensiolabs/SensioDistributionBundle/blob/master/Composer/ScriptHandler.php
