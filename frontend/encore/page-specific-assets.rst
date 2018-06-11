Creating Page-Specific CSS/JS
=============================

If you're creating a single page app (SPA), then you probably only need to define
*one* entry in ``webpack.config.js``. But if you have multiple pages, you might
want page-specific CSS and JavaScript.

For example, suppose you have a checkout page that has its own JavaScript. Create
a new ``checkout`` entry:

.. code-block:: diff

    // webpack.config.js

    Encore
        // an existing entry
        .addEntry('app', './assets/js/main.js')
        // a global styles entry
        .addStyleEntry('global', './assets/css/global.scss')

    +     .addEntry('checkout', './assets/js/checkout.js')
    ;

Inside ``checkout.js``, add or require the JavaScript and CSS you need. Then, just
include a ``script`` tag for ``checkout.js`` on the checkout page (and a ``link``
tag for ``checkout.css`` if you import any CSS).

Multiple Entries Per Page?
--------------------------

Typically, you should include only *one* JavaScript entry per page. This means
the checkout page will include ``checkout.js``, but will *not* include the
``app.js`` that's used on the other pages. Think of the checkout page as its
own "app", where ``checkout.js`` includes all the functionality you need.

However, if there is some global JavaScript that you want included on *every*
page, you *can* create an entry that contains that code and include both that
entry *and* your page-specific entry. For example, suppose that the ``app``
entry above contains JavaScript you want on every page. In that case, include
both ``app.js`` and ``checkout.js`` on the checkout page.

.. tip::

    Be sure to create a :doc:`shared entry </frontend/encore/shared-entry>` to avoid duplicating
    the Webpack bootstrap logic and any shared modules.
