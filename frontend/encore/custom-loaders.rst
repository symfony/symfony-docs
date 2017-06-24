Adding Custom Loaders
=====================

Encore already comes with a variety of different loaders that you can use out of the box,
but if there is a specific loader that you want to use that is not currently supported, then you
can easily add your own loader through the ``addLoader`` function.
The ``addLoader`` takes any valid webpack rules config.

If, for example, you want to add the `handlebars-loader`_, you can just ``addLoader`` with
your loader config

.. code-block:: javascript

    Encore
        // ...
        .addLoader({ test: /\.handlebars$/, loader: 'handlebars-loader' })

Since the loader config accepts any valid Webpack rules object, you can pass any
additional information your need for the loader

.. code-block:: twig

    Encore
        // ...
        .addLoader(
            {
                test: /\.handlebars$/,
			    loader: 'handlebars-loader',
			    query: {
				    helperDirs: [
					    __dirname + '/helpers1',
					    __dirname + '/helpers2',
                    ],
                    partialDirs: [
                        path.join(__dirname, 'templates', 'partials')
                    ]
			    }
            }
        )