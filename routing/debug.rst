.. index::
    single: Routing; Debugging

How to Visualize And Debug Routes
=================================

While adding and customizing routes, it's helpful to be able to visualize
and get detailed information about your routes. A great way to see every
route in your application is via the ``debug:router`` console command, which
lists *all* the configured routes in your application:

.. code-block:: terminal

    $ php app/console debug:router

    homepage              ANY       /
    contact               GET       /contact
    contact_process       POST      /contact
    article_show          ANY       /articles/{_locale}/{year}/{title}.{_format}
    blog                  ANY       /blog/{page}
    blog_show             ANY       /blog/{slug}

You can also get very specific information on a single route by including
the route name as the command argument:

.. code-block:: terminal

    $ php app/console debug:router article_show

Likewise, if you want to test whether a URL matches a given route, use the
``router:match`` command. This is useful to debug routing issues and find out
which route is associated with the given URL:

.. code-block:: terminal

    $ php app/console router:match /blog/my-latest-post

    Route "blog_show" matches
