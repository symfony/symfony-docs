.. index::
   single: Bundle; Inheritance

How to Override any Part of a Bundle
====================================

This document is a quick reference for how to override different parts of
third-party bundles.

Templates
---------

For information on overriding templates, see
* :ref:`overriding-bundle-templates`.
* :doc:`/cookbook/bundles/inheritance`

Routing
-------

Routing is never automatically imported in Symfony2. If you want to include
the routes from any bundle, then they must be manually imported from somewhere
in your application (e.g. ``app/config/routing.yml``).

The easiest way to "override" a bundle's routing is to never import it at
all. Instead of importing a third-party bundle's routing, simply copying
that routing file into your application, modify it, and import it instead.

Controllers
-----------

Assuming the third-party bundle involved uses non-service controllers (which
is almost always the case), you can easily override controllers via bundle
inheritance. For more information, see :doc:`/cookbook/bundles/inheritance`.

Services & Configuration
------------------------

In progress...

Entities & Entity mapping
-------------------------

In progress...

Forms
-----

In progress...

Validation metadata
-------------------

In progress...

Translations
------------

In progress...