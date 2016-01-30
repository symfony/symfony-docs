.. index::
   single: Routing; Multiple Routers

Using the Symfony CMF ChainRouter to Combine Multiple Routers
=============================================================

The Symfony CMF ``ChainRouter`` allows to use more than one router. A main
use case is to keep the :doc:`default symfony routing system </book/routing>`
available when writing a custom router.

.. caution::

    If you simply need a way to load routes determined in a different
    way, using :doc:`a custom route loader <custom_route_loader>` is
    simpler than writing your own controller.
    Writing a custom controller is justified when the routes can not be
    statically determined.

TODO: a bit of code examples?
