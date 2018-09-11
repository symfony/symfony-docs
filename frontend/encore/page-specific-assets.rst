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
        .addEntry('app', './assets/js/app.js')

    +     .addEntry('checkout', './assets/js/checkout.js')
    ;

Inside ``checkout.js``, add or require the JavaScript and CSS you need. Then, just
call the ``encore_entry_link_tags()`` and ``encore_entry_script_tags()`` functions
*only* on the checkout page to include the new ``script`` and ``link`` tags
(if any ``link`` tag is needed):

.. code-block:: twig

    {# templates/order/checkout.html.twig #}
    {# ... #}

    {#
        Assuming you're using Symfony's standard base.html.twig setup, add
        to the stylesheets and javascript blocks
    #}

    {% block javascripts %}
        {{ parent() }}

        {{ encore_entry_script_tags('checkout') }}
    {% endblock %}

    {% block stylesheets %}
        {{ parent() }}

        {{ encore_entry_link_tags('checkout') }}
    {% endblock %}

Multiple Entries Per Page?
--------------------------

Typically, you should include only *one* JavaScript entry per page. Think of the
checkout page as its own "app", where ``checkout.js`` includes all the functionality
you need.

However, it's pretty common to need to include some global JavaScript and CSS on
every page. For that reason, it usually makes sense to have one entry (e.g. ``app``)
that contains this global code and is included on every page (i.e. it's included
in the *layout* of your app). This means that you will always have one, global entry
on every page (e.g. ``app``) and you *may* have one page-specific JavaScript and
CSS file from a page-specific entry (e.g. ``checkout``).

.. tip::

    Be sure to use split chunks :doc:`shared entry </frontend/encore/split-chunks>`
    to avoid duplicating and shared code between your entry files.
