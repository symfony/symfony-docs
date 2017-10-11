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
project needs for every request. However, this behavior can confuse your debugger,
because the same class can be located in two different places: the original class
file and the big file which aggregates lots of classes.

This recipe shows you how you can tweak this caching mechanism to make it friendlier
when you need to debug code that involves Symfony classes.

The ``app_dev.php`` front controller reads as follows by default::

    // ...

    $loader = require __DIR__.'/../app/autoload.php';
    Debug::enable();

    $kernel = new AppKernel('dev', true);
    $kernel->loadClassCache();
    $request = Request::createFromGlobals();
    // ...

To make your debugger happier, disable the loading of all PHP class caches
by removing the call to ``loadClassCache()``::

    // ...

    $loader = require_once __DIR__.'/../app/autoload.php';
    Debug::enable();

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

.. versionadded:: 3.3
    The ``loadClassCache()`` was deprecated in Symfony 3.3 and removed in
    Symfony 4.0. No alternative is provided because this feature is useless
    when using PHP 7 or higher.

Useful Debugging Commands
-------------------------

When developing a large application, it can be hard to keep track of all the
different services, routes and translations. Luckily, Symfony has some commands
that can help you visualize and find the information.

``debug:container``
    Displays information about the contents of the Symfony container for all public
    services. To find only those matching a name, append the name as an argument.

``debug:config``
    Shows all configured bundles, their class and their alias.

``debug:event-dispatcher``
    Displays information about all the registered listeners in the event dispatcher.

``debug:router``
    Displays information about all configured routes in the application as a
    table with the name, method, scheme, host and path for each route.

``debug:translation <locale>``
    Shows a table of the translation key, the domain, the translation and the
    fallback translation for all known messages, if translations exist for
    the given locale.

.. tip::

    When in doubt how to use a console command, open the help section by
    appending the ``--help`` option.
