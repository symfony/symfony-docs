Looking up Routes from a Database: Symfony CMF DynamicRouter
============================================================

The core Symfony Routing System is excellent at handling complex sets
of routes. A highly optimized routing cache is dumped during
deployments.

However, when working with large amounts of data that each need a nice
readable URL (e.g. for search engine optimization purposes), the routing
can get slowed down. Additionally, if routes need to be edited by users,
the route cache would need to be rebuilt frequently.

For these cases, the ``DynamicRouter`` offers an alternative approach:

* Routes are stored in a database;
* There is a database index on the path field, the lookup scales to huge
  numbers of different routes;
* Writes only affect the index of the database, which is very efficient.

When all routes are known during deploy time and the number is not too
high, using a :doc:`custom route loader <custom_route_loader>` is the
preferred way to add more routes. When working with only one type of
objects, a slug parameter on the object and the ``#[ParamConverter]``
attribute works fine (see `FrameworkExtraBundle`_) .

The ``DynamicRouter`` is useful when you need ``Route`` objects with
the full feature set of Symfony. Each route can define a specific
controller so you can decouple the URL structure from your application
logic.

The DynamicRouter comes with built-in support for Doctrine ORM and Doctrine
PHPCR-ODM but offers the ``ContentRepositoryInterface`` to write a custom
loader, e.g. for another database type or a REST API or anything else.

The DynamicRouter is explained in the `Symfony CMF documentation`_.

.. _FrameworkExtraBundle: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`Symfony CMF documentation`: https://symfony.com/doc/current/cmf/bundles/routing/dynamic.html
