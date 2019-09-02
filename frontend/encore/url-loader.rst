Inlining files in CSS with Webpack URL Loader
=============================================

A simple technique to improve the performance of web applications is to reduce
the number of HTTP requests inlining small files as base64 encoded URLs in the
generated CSS files.

Webpack Encore provides this feature via Webpack's `URL Loader`_ plugin, but
it's disabled by default. First, add the URL loader to your project:

.. code-block:: terminal

    $ yarn add url-loader --dev

Then enable it in your ``webpack.config.js``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...
        .configureUrlLoader({
            fonts: { limit: 4096 },
            images: { limit: 4096 }
        })
    ;

The ``limit`` option defines the maximum size in bytes of the inlined files. In
the previous example, font and image files having a size below or equal to 4 KB
will be inlined and the rest of files will be processed as usual.

You can also use all the other options supported by the `URL Loader`_. If you
want to disable this loader for either images or fonts, remove the corresponding
key from the object that is passed to the ``configureUrlLoader()`` method:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...
        .configureUrlLoader({
            // 'fonts' is not defined, so only images will be inlined
            images: { limit: 4096 }
        })
    ;

.. _`URL Loader`: https://github.com/webpack-contrib/url-loader
