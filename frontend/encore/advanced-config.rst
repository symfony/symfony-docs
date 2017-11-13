Advanced Webpack Config
=======================

Quite simply, Encore generates the Webpack configuration that's used in your
``webpack.config.js`` file. Encore doesn't support adding all of Webpack's
`configuration options`_, because many can be easily added on your own.

For example, suppose you need to set `Webpack's watchOptions`_ setting. To do that,
modify the config after fetching it from Encore:

.. code-block:: javascript

    // webpack.config.js

    var Encore = require('@symfony/webpack-encore');

    // ... all Encore config here

    // fetch the config, then modify it!
    var config = Encore.getWebpackConfig();
    config.watchOptions = { poll: true, ignored: /node_modules/ };

    // other examples: add an alias or extension
    // config.resolve.alias.local = path.resolve(__dirname, './resources/src');
    // config.resolve.extensions.push('json');

    // export the final config
    module.exports = config;

But be careful not to accidentally override any config from Encore:

.. code-block:: javascript

    // webpack.config.js
    // ...

    // GOOD - this modifies the config.resolve.extensions array
    config.resolve.extensions.push('json');

    // BAD - this replaces any extensions added by Encore
    // config.resolve.extensions = ['json'];

.. _`configuration options`: https://webpack.js.org/configuration/
.. _`Webpack's watchOptions`: https://webpack.js.org/configuration/watch/#watchoptions
