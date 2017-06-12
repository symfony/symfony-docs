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

That's it! All files ending in ``.sass`` or ``.scss`` will be pre-processed.

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

That's it! All files ending in ``.less`` will be pre-processed.
