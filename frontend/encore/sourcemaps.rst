Enabling Source Maps with Webpack Encore
========================================

`Source maps`_ allow browsers to access the original code related to some
asset (e.g. the Sass code that was compiled to CSS or the TypeScript code that
was compiled to JavaScript). Source maps are useful for debugging purposes but
unnecessary when executing the application in production.

Encore's default ``webpack.config.js`` file enables source maps in the ``dev``
build:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        .enableSourceMaps(!Encore.isProduction())
    ;

.. _`Source maps`: https://developer.mozilla.org/en-US/docs/Tools/Debugger/How_to/Use_a_source_map
