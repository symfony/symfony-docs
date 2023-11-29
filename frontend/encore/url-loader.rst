Inlining Images & Fonts in CSS with Webpack Encore
==================================================

A simple technique to improve the performance of web applications is to reduce
the number of HTTP requests inlining small files as base64 encoded URLs in the
generated CSS files.

You can enable this in ``webpack.config.js`` for images, fonts or both:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...
        .configureImageRule({
            // tell Webpack it should consider inlining
            type: 'asset',
            //maxSize: 4 * 1024, // 4 kb - the default is 8kb
        })

        .configureFontRule({
            type: 'asset',
            //maxSize: 4 * 1024
        })
    ;

This leverages Webpack `Asset Modules`_. You can read more about this and the
configuration there.

.. _`Asset Modules`: https://webpack.js.org/guides/asset-modules/
