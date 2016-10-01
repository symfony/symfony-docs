.. index::
   single: Debugging

How to Optimize your Development Environment for Debugging
==========================================================

When you work on a Symfony project on your local machine, you should use the
``dev`` environment (``app_dev.php`` front controller). This environment
configuration is optimized for two main purposes:

* Give the developer accurate feedback whenever something goes wrong (web
  debug toolbar, nice exception pages, profiler, ...);

* Be as similar as possible as the production environment to avoid problems
  when deploying the project.

Disabling the Bootstrap File and Class Caching
----------------------------------------------

And to make the production environment as fast as possible, Symfony creates
big PHP files in your cache containing the aggregation of PHP classes your
project needs for every request. However, this behavior can confuse your IDE
or your debugger. This recipe shows you how you can tweak this caching
mechanism to make it friendlier when you need to debug code that involves
Symfony classes.

The ``app_dev.php`` front controller reads as follows by default::

    // ...

    $loader = require_once __DIR__.'/../app/bootstrap.php.cache';
    require_once __DIR__.'/../app/AppKernel.php';

    $kernel = new AppKernel('dev', true);
    $kernel->loadClassCache();
    $request = Request::createFromGlobals();

To make your debugger happier, disable all PHP class caches by removing the
call to ``loadClassCache()`` and by replacing the require statements like
below::

    // ...

    // $loader = require_once __DIR__.'/../app/bootstrap.php.cache';
    $loader = require_once __DIR__.'/../app/autoload.php';
    require_once __DIR__.'/../app/AppKernel.php';

    $kernel = new AppKernel('dev', true);
    // $kernel->loadClassCache();
    $request = Request::createFromGlobals();

.. tip::

    If you disable the PHP caches, don't forget to revert after your debugging
    session.

Some IDEs do not like the fact that some classes are stored in different
locations. To avoid problems, you can either tell your IDE to ignore the PHP
cache files, or you can change the extension used by Symfony for these files::

    $kernel->loadClassCache('classes', '.php.cache');

Using the VarDumper
-------------------

To ease the debugging of a variable in your application, you can use the
``VarDumper`` component to dump the content of a variable. The component
provides an alternative to the PHP ``var_dump`` function, in the form of
``dump``.

It is as easy as the code below::

    // Create a variable with a value...
    $myVar = ...;

    // ... and dump it
    dump($myVar);

.. tip::

    The dumper is not limited to scalar values. Arrays and objects can also be
    visualized using the ``VarDumper``.

If the dumper is used on a command line, the result is a formatted string.
Otherwise, the result is a piece of HTML, which can be expanded to show nested
structures in the dumped value.
