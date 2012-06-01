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

In order to override a form type, it has to be registered as a service (meaning
it is tagged as "form.type"). You can then override it as you would override any
service as explained in "Services & Configuration". This, of course, will only
work if the type is referred to by its alias rather than being instantiated,
e.g.:

.. code-block:: php
    $builder->add('name', 'custom_type');

rather than

.. code-block:: php
    $builder->add('name', new CustomType());

Validation metadata
-------------------

In progress...

Translations
------------

In progress...