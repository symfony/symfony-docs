CSS Preprocessors: Sass, etc. with Webpack Encore
=================================================

To use the Sass, LESS or Stylus pre-processors, enable the one you want in ``webpack.config.js``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        // enable just the one you want

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

.. _`Encore's index.js file`: https://github.com/symfony/webpack-encore/blob/master/index.js
