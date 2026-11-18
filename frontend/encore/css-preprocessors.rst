CSS Preprocessors: Sass, etc. with Webpack Encore
=================================================

To use the Sass, LESS or Stylus pre-processors, enable the one you want in ``webpack.config.js``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        // enable only the one you want

        // processes files ending in .scss or .sass
        .enableSassLoader()

        // processes files ending in .less
        .enableLessLoader()

        // processes files ending in .styl
        .enableStylusLoader()
    ;

Then restart Encore. When you do, it will give you a command you can run to
install any missing dependencies. After running that command and restarting
Encore, you're done!

You can also pass configuration options to each of the loaders. See the
`Encore's index.js file`_ for detailed documentation.

.. tip::

    Since Encore 6.0, ``sass-loader`` uses the `modern Sass API`_ by default.
    This means that some options have changed. For example, ``includePaths`` is
    now ``loadPaths``:

    .. code-block:: javascript

        // webpack.config.js
        // ...

        Encore
            // ...

            // with the modern API (default):
            .enableSassLoader((options) => {
                options.loadPaths = [/* ... */];
            })

            // if you need the legacy API (not recommended):
            .enableSassLoader((options) => {
                options.api = 'legacy';
                options.includePaths = [/* ... */];
            })
        ;

.. _`Encore's index.js file`: https://github.com/symfony/webpack-encore/blob/master/index.js
.. _`modern Sass API`: https://sass-lang.com/documentation/js-api/interfaces/options
