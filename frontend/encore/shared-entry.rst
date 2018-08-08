Creating a Shared Commons Entry
===============================

Suppose you have multiple entry files and *each* requires ``jquery``. In this
case, *each* output file will contain jQuery, slowing down your user's experience.
To solve this, you can *extract* the common libraries to a "shared" entry file
that's included on every page.

Suppose you already have an entry called ``app`` that's included on every page.
Update your code to use ``createSharedEntry()``:

.. code-block:: diff

    Encore
        // ...
    -     .addEntry('app', 'assets/js/app.js')
    +     .createSharedEntry('app', ['assets/js/app.js', 'assets/js/vendor.js])
        .addEntry('homepage', './assets/js/homepage.js')
        .addEntry('blog', './assets/js/blog.js')
        .addEntry('store', './assets/js/store.js')

As soon as you make this change, you need to include *one* extra JavaScript file
in your layout, *before* ``app.js``:

.. _encore-shared-entry-script:

.. code-block:: twig

    {# templates/base.html.twig #}
    <!-- these two files now must be included on every page -->
    <script src="{{ asset('build/manifest.js') }}"></script>
    <script src="{{ asset('build/app.js') }}"></script>

    <!-- you can still include page-specific JavaScript, like normal -->
    <script src="{{ asset('build/store.js') }}"></script>

    <!-- continue including app.css on every page -->
    <link rel="stylesheet" href="{{ asset('build/app.css') }}" />

Before making this change, if both ``app.js`` and ``store.js`` require ``jquery``,
then ``jquery`` would be packaged into *both* files, which is wasteful. By making
``app.js`` your "shard" entry, *any* code required by ``app.js`` (like jQuery) will
*no longer* be packaged into any other files. The same is true for any CSS.

Because ``app.js`` contains all the common code that other entry files depend on,
it's obvious that its script (and link) tag must be on every page. The other file
(``manifest.js``) is less obvious: it's needed so that Webpack knows how to load
these shared modules.

.. tip::

    The ``app.js`` file works best when its contents are changed *rarely*
    and you're using :ref:`long-term caching <encore-long-term-caching>`. Why?
    If ``app.js`` contains application code that *frequently* changes, then
    (when using versioning), its filename hash will frequently change. This means
    your users won't enjoy the benefits of long-term caching for this file (which
    is generally quite large).
