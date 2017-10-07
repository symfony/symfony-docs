.. index::
   single: Debugging

How to Optimize Your Development Environment for Debugging
==========================================================

When you work on a Symfony project on your local machine, you should use the
``dev`` environment (``app_dev.php`` front controller). This environment
configuration is optimized for two main purposes:

* Give the developer accurate feedback whenever something goes wrong (provided
  by the web debug toolbar, nice exception pages, profiler, ...);
* Be as close as possible to the production environment to avoid problems when
  deploying the project.

Using Interactive Debug Tools
-----------------------------

Interactive debug tools allow you to walk through the code step by step,
making it easier to identify which step is causing problems. Symfony works
with any PHP debug environment, among them:

 * `Xdebug`_, the most well-known PHP debugger;
 * `PsySH`_, a PHP `REPL`_ (Read-eval-print loop) debugger. Use the
   `FidryPsyshBundle`_ for a dedicated Symfony integration of PsySH.

Dumping Variables with the VarDumper
------------------------------------

To ease the debugging of a variable in your application, you can use the
:doc:`VarDumper component </components/var_dumper>` to dump the content of a
variable. The component provides the ``dump()`` function, an alternative to
PHP's :phpfunction:`var_dump()` function::

    // create a variable with a value
    $myVar = ...;

    // and dump it
    dump($myVar);

The dumper is not limited to scalar values. Arrays and objects can also be
visualized using the VarDumper. One of the most important advantages of using
``dump()`` is a nicer and more specialized dump of objects (e.g. Doctrine
internals are filtered out when dumping an entity proxy).

If the dumper is used on a command line, the result is a formatted string.
Otherwise, the result is a piece of HTML, which can be expanded to show nested
structures in the dumped value.

You can also dump values from inside templates:

.. code-block:: html+twig

    {# dumps the variable inline as HTML #}
    {{ dump(myVar) }}

    {# dumps the variable to the web debug toolbar to not modify the template #}
    {% dump myVar %}

Useful Debugging Commands
-------------------------

When developing a large application, it can be hard to keep track of all the
different services, routes and translations. Luckily, Symfony has some commands
that can help you visualize and find the information:

``debug:container``
    Displays information about the contents of the Symfony container for all public
    services. Append a service ID as an argument to find only those matching the ID.

``debug:config``
    Shows all configured bundles, their classes and their aliases.

``debug:event-dispatcher``
    Displays information about all the registered listeners in the event dispatcher.

``debug:router``
    Displays information about all configured routes in the application as a
    table with the name, method, scheme, host and path for each route.

``router:match <path_info>``
    Shows the route information matching the provided path info or an error if
    no route matches.

``debug:translation <locale>``
    Shows a table of the translation key, the domain, the translation and the
    fallback translation for all known messages if translations exist for
    the given locale.

.. tip::

    When in doubt how to use a console command, open the help section by
    appending the ``--help`` (``-h``) option.

.. tip::

    When in doubt how to use a console command, open the help section by
    appending the ``--help`` option.

.. _Xdebug: https://xdebug.org/
.. _PsySH: http://psysh.org/
.. _REPL: https://en.wikipedia.org/wiki/Read%E2%80%93eval%E2%80%93print_loop
.. _FidryPsyshBundle: https://github.com/theofidry/PsyshBundle
