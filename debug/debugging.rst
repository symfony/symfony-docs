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

Useful Debugging Commands
-------------------------

When developing a large application, it can be hard to keep track of all the
different services, routes and translations. Luckily, Symfony has some commands
that can help you visualize and find the information.

``about``
    Shows information about the current project, such as the Symfony version,
    the Kernel and PHP.

``debug:container``
    Displays information about the contents of the Symfony container for all public
    services. To find only those matching a name, append the name as an argument.

``debug:config``
    Shows all configured bundles, their class and their alias.
    
``debug:form``
    Displays information about form types and their options.

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
