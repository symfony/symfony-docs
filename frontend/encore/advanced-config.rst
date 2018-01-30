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

Defining Multiple Webpack Configurations
----------------------------------------

Webpack supports passing an `array of configurations`_, which are processed in
parallel. Webpack Encore includes a ``reset()`` object allowing to reset the
state of the current configuration to build a new one:

.. code-block:: javascript

    // define the first configuration
    Encore
        .setOutputPath('web/build/')
        .setPublicPath('/build')
        .addEntry('app', './assets/js/main.js')
        .addStyleEntry('global', './assets/css/global.scss')
        .enableSassLoader()
        .autoProvidejQuery()
        .enableSourceMaps(!Encore.isProduction())
    ;

    // build the first configuration
    const firstConfig = Encore.getWebpackConfig();

    // reset Encore to build the second config
    Encore.reset();

    // define the second configuration
    Encore
        .setOutputPath('web/build/')
        .setPublicPath('/build')
        .addEntry('mobile', './assets/js/mobile.js')
        .addStyleEntry('mobile', './assets/css/mobile.less')
        .enableLessLoader()
        .enableSourceMaps(!Encore.isProduction())
    ;

    // build the second configuration
    const secondConfig = Encore.getWebpackConfig();

    // export the final configuration as an array of multiple configurations
    module.exports = [firstConfig, secondConfig];

.. _`configuration options`: https://webpack.js.org/configuration/
.. _`Webpack's watchOptions`: https://webpack.js.org/configuration/watch/#watchoptions
.. _`array of configurations`: https://github.com/webpack/docs/wiki/configuration#multiple-configurations
