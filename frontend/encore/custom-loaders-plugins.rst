Adding Custom Loaders & Plugins with Webpack Encore
===================================================

Adding Custom Loaders
---------------------

Encore already comes with a variety of different loaders out of the box,
but if there is a specific loader that you want to use that is not currently supported, you
can add your own loader through the ``addLoader`` function.
The ``addLoader`` takes any valid webpack rules config.

If, for example, you want to add the `handlebars-loader`_, call ``addLoader`` with
your loader config

.. code-block:: javascript

    Encore
        // ...
        .addLoader({ test: /\.handlebars$/, loader: 'handlebars-loader' })
    ;

Since the loader config accepts any valid Webpack rules object, you can pass any
additional information your need for the loader

.. code-block:: javascript

    Encore
        // ...
        .addLoader({
            test: /\.handlebars$/,
            loader: 'handlebars-loader',
            options: {
                helperDirs: [
                    __dirname + '/helpers1',
                    __dirname + '/helpers2',
                ],
                partialDirs: [
                    path.join(__dirname, 'templates', 'partials')
                ]
            }
        })
    ;

Adding Custom Plugins
---------------------

Encore uses a variety of different `plugins`_ internally. But, you can add your own
via the ``addPlugin()`` method. For example, if you use `Moment.js`_, you might want
to use the `IgnorePlugin`_ (see `moment/moment#2373`_):

.. code-block:: diff

      // webpack.config.js
    + var webpack = require('webpack');

      Encore
          // ...

    +     .addPlugin(new webpack.IgnorePlugin({
    +         resourceRegExp: /^\.\/locale$/,
    +         contextRegExp: /moment$/,
    +     }))
      ;

.. _`handlebars-loader`: https://github.com/pcardune/handlebars-loader
.. _`plugins`: https://webpack.js.org/plugins/
.. _`Moment.js`: https://momentjs.com/
.. _`IgnorePlugin`: https://webpack.js.org/plugins/ignore-plugin/
.. _`moment/moment#2373`: https://github.com/moment/moment/issues/2373
