CSS Preprocessors: Sass, LESS, etc.
===================================

Using Sass
----------

To use the Sass pre-processor, install the dependencies:

.. code-block:: terminal

    $ yarn add --dev sass-loader node-sass

And enable it in ``webpack.config.js``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...
        .enableSassLoader()
    ;

That's it! All files ending in ``.sass`` or ``.scss`` will be pre-processed. You
can also pass options to ``sass-loader``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...
        .enableSassLoader(function(options) {
            // https://github.com/sass/node-sass#options
            // options.includePaths = [...]
        });
    ;

Using LESS
----------

To use the LESS pre-processor, install the dependencies:

.. code-block:: terminal

    $ yarn add --dev less-loader less

And enable it in ``webpack.config.js``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...
        .enableLessLoader()
    ;

That's it! All files ending in ``.less`` will be pre-processed. You can also pass
options to ``less-loader``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...
        .enableLessLoader(function(options) {
            // https://github.com/webpack-contrib/less-loader#examples
            // http://lesscss.org/usage/#command-line-usage-options
            // options.relativeUrls = false;
        });
    ;
