Advanced Webpack Config
=======================

Summarized, Encore generates the Webpack configuration that's used in your
``webpack.config.js`` file. Encore doesn't support adding all of Webpack's
`configuration options`_, because many can be added on your own.

For example, suppose you need to set `Webpack's watchOptions`_ setting. To do that,
modify the config after fetching it from Encore:

.. TODO update the following config example when https://github.com/symfony/webpack-encore/pull/486 is merged and configureWatchOptions() is introduced

.. code-block:: javascript

    // webpack.config.js

    var Encore = require('@symfony/webpack-encore');

    // ... all Encore config here

    // fetch the config, then modify it!
    var config = Encore.getWebpackConfig();
    // if you run 'encore dev --watch'
    config.watchOptions = { poll: true, ignored: /node_modules/ };
    // if you run 'encore dev-server'
    config.devServer.watchOptions = { poll: true, ignored: /node_modules/ };

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
        .setOutputPath('public/build/')
        .setPublicPath('/build')
        .addEntry('app', './assets/js/app.js')
        .addStyleEntry('global', './assets/css/global.scss')
        .enableSassLoader()
        .autoProvidejQuery()
        .enableSourceMaps(!Encore.isProduction())
    ;

    // build the first configuration
    const firstConfig = Encore.getWebpackConfig();

    // Set a unique name for the config (needed later!)
    firstConfig.name = 'firstConfig';

    // reset Encore to build the second config
    Encore.reset();

    // define the second configuration
    Encore
        .setOutputPath('public/build/')
        .setPublicPath('/build')
        .addEntry('mobile', './assets/js/mobile.js')
        .addStyleEntry('mobile', './assets/css/mobile.less')
        .enableLessLoader()
        .enableSourceMaps(!Encore.isProduction())
    ;

    // build the second configuration
    const secondConfig = Encore.getWebpackConfig();

    // Set a unique name for the config (needed later!)
    secondConfig.name = 'secondConfig';

    // export the final configuration as an array of multiple configurations
    module.exports = [firstConfig, secondConfig];

When running Encore, both configurations will be built in parallel. If you
prefer to build configs separately, pass the ``--config-name`` option:

.. code-block:: terminal

    $ yarn encore dev --config-name firstConfig

Next, define the output directories of each build:

.. code-block:: yaml

    # config/packages/webpack_encore.yaml
    webpack_encore:
        output_path: '%kernel.public_dir%/public/default_build'
        builds:
            firstConfig: '%kernel.public_dir%/public/first_build'
            secondConfig: '%kernel.public_dir%/public/second_build'

Finally, use the third optional parameter of the ``encore_entry_*_tags()``
functions to specify which build to use:

.. code-block:: twig

    {# Using the entrypoints.json file located in ./public/first_build #}
    {{ encore_entry_script_tags('app', null, 'firstConfig') }}
    {{ encore_entry_link_tags('global', null, 'firstConfig') }}

    {# Using the entrypoints.json file located in ./public/second_build #}
    {{ encore_entry_script_tags('mobile', null, 'secondConfig') }}
    {{ encore_entry_link_tags('mobile', null, 'secondConfig') }}

Generating a Webpack Configuration Object without using the Command-Line Interface
----------------------------------------------------------------------------------

Ordinarily you would use your ``webpack.config.js`` file by calling Encore
from the command-line interface. But sometimes, having access to the generated
Webpack configuration can be required by tools that don't use Encore (for
instance a test-runner such as `Karma`_).

The problem is that if you try generating that Webpack configuration object
without using the ``encore`` command you will encounter the following error:

.. code-block:: text

    Error: Encore.setOutputPath() cannot be called yet because the runtime environment doesn't appear to be configured. Make sure you're using the encore executable or call Encore.configureRuntimeEnvironment() first if you're purposely not calling Encore directly.

The reason behind that message is that Encore needs to know a few thing before
being able to create a configuration object, the most important one being what
the target environment is.

To solve this issue you can use ``configureRuntimeEnvironment``. This method
must be called from a JavaScript file **before** requiring ``webpack.config.js``.

For instance:

.. code-block:: javascript

    const Encore = require('@symfony/webpack-encore');

    // Set the runtime environment
    Encore.configureRuntimeEnvironment('dev');

    // Retrieve the Webpack configuration object
    const webpackConfig = require('./webpack.config');

If needed, you can also pass to that method all the options that you would
normally use from the command-line interface:

.. code-block:: javascript

    Encore.configureRuntimeEnvironment('dev-server', {
        // Same options you would use with the
        // CLI utility, with their name in camelCase.
        https: true,
        keepPublicPath: true,
    });

Having the full control on Loaders Rules
----------------------------------------

The method ``configureLoaderRule()`` provides a clean way to configure Webpack loaders rules (``module.rules``, see `Configuration <https://webpack.js.org/concepts/loaders/#configuration>`_).

This is a low-level method. All your modifications will be applied just before pushing the loaders rules to Webpack.
It means that you can override the default configuration provided by Encore, which may break things. Be careful when using it.

One use might be to configure the ``eslint-loader`` to lint Vue files too.
The following code is equivalent:

.. code-block:: javascript

    // Manually
    const webpackConfig = Encore.getWebpackConfig();

    const eslintLoader = webpackConfig.module.rules.find(rule => rule.loader === 'eslint-loader');
    eslintLoader.test = /\.(jsx?|vue)$/;

    return webpackConfig;

    // Using Encore.configureLoaderRule()
    Encore.configureLoaderRule('eslint', loaderRule => {
        loaderRule.test = /\.(jsx?|vue)$/
    });

    return Encore.getWebpackConfig();

The following loaders are configurable with ``configureLoaderRule()``:
  - ``javascript`` (alias ``js``)
  - ``css``
  - ``images``
  - ``fonts``
  - ``sass`` (alias ``scss``)
  - ``less``
  - ``stylus``
  - ``vue``
  - ``eslint``
  - ``typescript`` (alias ``ts``)
  - ``handlebars``

.. _`configuration options`: https://webpack.js.org/configuration/
.. _`Webpack's watchOptions`: https://webpack.js.org/configuration/watch/#watchoptions
.. _`array of configurations`: https://github.com/webpack/docs/wiki/configuration#multiple-configurations
.. _`Karma`: https://karma-runner.github.io
