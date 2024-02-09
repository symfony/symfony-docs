Preventing Duplication by "Splitting" Shared Code with Webpack Encore
=====================================================================

Suppose you have multiple entry files and *each* requires ``jquery``. In this
case, *each* output file will contain jQuery, making your files much larger than
necessary. To solve this, you can ask webpack to analyze your files and *split* them
into additional files, which will contain "shared" code.

To enable this, call ``splitEntryChunks()``:

.. code-block:: diff

      // webpack.config.js
      Encore
          // ...

          // multiple entry files, which probably import the same code
          .addEntry('app', './assets/app.js')
          .addEntry('homepage', './assets/homepage.js')
          .addEntry('blog', './assets/blog.js')
          .addEntry('store', './assets/store.js')

    +     .splitEntryChunks()

Now, each output file (e.g. ``homepage.js``) *may* be split into multiple file
(e.g. ``homepage.js`` & ``vendors-node_modules_jquery_dist_jquery_js.js`` - the
filename of the second will be less obvious when you build for production). This
means that you *may* need to include *multiple* ``script`` tags (or ``link`` tags
for CSS) in your template. Encore creates an :ref:`entrypoints.json <encore-entrypointsjson-simple-description>`
file that lists exactly which CSS and JavaScript files are needed for each entry.

If you're using the ``encore_entry_link_tags()`` and ``encore_entry_script_tags()``
Twig functions from WebpackEncoreBundle, you don't need to do anything else! These
functions automatically read this file and render as many ``script`` or ``link``
tags as needed:

.. code-block:: html+twig

    {#
        May now render multiple script tags:
            <script src="/build/runtime.js" defer></script>
            <script src="/build/vendors-node_modules_jquery_dist_jquery_js.js" defer></script>
            <script src="/build/homepage.js" defer></script>
    #}
    {{ encore_entry_script_tags('homepage') }}

Controlling how Assets are Split
--------------------------------

The logic for *when* and *how* to split the files is controlled by the
`SplitChunksPlugin from Webpack`_. You can control the configuration passed to
this plugin with the ``configureSplitChunks()`` function:

.. code-block:: diff

      // webpack.config.js
      Encore
          // ...

          .splitEntryChunks()
    +     .configureSplitChunks(function(splitChunks) {
    +         // change the configuration
    +         splitChunks.minSize = 0;
    +     })

.. _`SplitChunksPlugin from Webpack`: https://webpack.js.org/plugins/split-chunks-plugin/
