Creating Page-Specific CSS/JS
=============================

If you're creating a single page app (SPA), then you probably only need to define
*one* entry in ``webpack.config.js``. But if you have multiple pages, you might
want page-specific CSS and JavaScript.

To learn how to set this up, see the :ref:`multiple-javascript-entries` example.

Multiple Entries Per Page?
--------------------------

Typically, you should include only *one* JavaScript entry per page. Think of the
checkout page as its own "app", where ``checkout.js`` includes all the functionality
you need.

However, it's pretty common to need to include some global JavaScript and CSS on
every page. For that reason, it usually makes sense to have one entry (e.g. ``app``)
that contains this global code (both JavaScript & CSS) and is included on every
page (i.e. it's included in the *layout* of your app). This means that you will
always have one, global entry on every page (e.g. ``app``) and you *may* have one
page-specific JavaScript and CSS file from a page-specific entry (e.g. ``checkout``).

.. tip::

    Be sure to use :doc:`split chunks </frontend/encore/split-chunks>`
    to avoid duplicate and shared code between your entry files.
