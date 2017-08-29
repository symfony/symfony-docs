.. index::
    single: Routing; Debugging

How to Visualize And Debug Routes
=================================

While adding and customizing routes, it's helpful to be able to visualize
and get detailed information about your routes. A great way to see every
route in your application is via the ``debug:router`` console command. Execute
the command by running the following from the root of your project.

.. code-block:: terminal

    $ php app/console debug:router

.. versionadded:: 2.6
    Prior to Symfony 2.6, this command was called ``router:debug``.

This command will print a helpful list of *all* the configured routes in
your application:

.. code-block:: text

------------------ -------- -------- ------ ----------------------------------------------
 Name               Method   Scheme   Host   Path
------------------ -------- -------- ------ ----------------------------------------------
 homepage           ANY      ANY      ANY    /
 contact            GET      ANY      ANY    /contact
 contact_process    POST     ANY      ANY    /contact
 article_show       ANY      ANY      ANY    /articles/{_locale}/{year}/{title}.{_format}
 blog               ANY      ANY      ANY    /blog/{page}
 blog_show          ANY      ANY      ANY    /blog/{slug}

You can also get very specific information on a single route by including
the route name after the command:

.. code-block:: terminal

    $ php app/console debug:router article_show

Likewise, if you want to test whether a URL matches a given route, you can
use the ``router:match`` console command:

.. code-block:: terminal

    $ php app/console router:match /blog/my-latest-post

This command will print which route the URL matches.

.. code-block:: text

    Route "blog_show" matches

If you want to list the controller configured with each route, you can use the ``--show-controllers`` option:

.. code-block:: terminal

    $ php app/console debug:router --show-controllers

This command will print a list of *all* the configured routes in your application
with a new column *Controller* which shows the controller configred for a specific route:

.. code-block:: text

    --------------- -------- -------- ------ -------------- ---------------------
     Name            Method   Scheme   Host   Path           Controller
    --------------- -------- -------- ------ -------------- ---------------------
     blog_show       ANY      ANY      ANY    /blog/{slug}   AppBundle:Blog:show
