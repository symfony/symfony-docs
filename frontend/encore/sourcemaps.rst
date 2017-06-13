Enabling Source Maps
====================

`Source maps`_ allow browsers to access the original code related to some
asset (e.g. the Sass code that was compiled to CSS or the TypeScript code that
was compiled to JavaScript). Source maps are useful for debugging purposes but
unnecessary when executing the application in production.

Encore inlines source maps in the compiled assets only in the development
environment, but you can control this behavior with the ``enableSourceMaps()``
method:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        // this is the default behavior...
        .enableSourceMaps(!Encore.isProduction())
        // ... but you can override it by passing a boolean value
        .enableSourceMaps(true)
    ;

.. _`Source maps`: https://developer.mozilla.org/en-US/docs/Tools/Debugger/How_to/Use_a_source_map
