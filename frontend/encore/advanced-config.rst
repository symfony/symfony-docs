Advanced Webpack Config
=======================

Summarized, Encore generates the Webpack configuration that's used in your
``webpack.config.js`` file. Encore doesn't support adding all of Webpack's
`configuration options`_, because many can be added on your own.

For example, suppose you need to automatically resolve a new extension.
To do that, modify the config after fetching it from Encore:

.. code-block:: javascript

    // webpack.config.js

    const Encore = require('@symfony/webpack-encore');

    // ... all Encore config here

    // fetch the config, then modify it!
    const config = Encore.getWebpackConfig();

    // add an extension
    config.resolve.extensions.push('json');

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

Configuring Watching Options and Polling
----------------------------------------

Encore provides the method ``configureWatchOptions()`` to configure
`Watching Options`_ when running ``encore dev --watch`` or ``encore dev-server``:

.. code-block:: javascript

    Encore.configureWatchOptions(function(watchOptions) {
        // enable polling and check for changes every 250ms
        // polling is useful when running Encore inside a Virtual Machine
        watchOptions.poll = 250;
    });

Defining Multiple Webpack Configurations
----------------------------------------

Webpack supports passing an `array of configurations`_, which are processed in
parallel. Webpack Encore includes a ``reset()`` object allowing to reset the
state of the current configuration to build a new one:

.. code-block:: javascript

    // define the first configuration
    Encore
        .setOutputPath('public/build/first_build/')
        .setPublicPath('/build/first_build')
        .addEntry('app', './assets/app.js')
        .addStyleEntry('global', './assets/styles/global.scss')
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
        .setOutputPath('public/build/second_build/')
        .setPublicPath('/build/second_build')
        .addEntry('mobile', './assets/mobile.js')
        .addStyleEntry('mobile', './assets/styles/mobile.less')
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

    # if you use the Yarn package manager
    $ yarn encore dev --config-name firstConfig

    # if you use the npm package manager
    $ npm run dev -- --config-name firstConfig

Next, define the output directories of each build:

.. code-block:: yaml

    # config/packages/webpack_encore.yaml
    webpack_encore:
        output_path: '%kernel.project_dir%/public/default_build'
        builds:
            firstConfig: '%kernel.project_dir%/public/first_build'
            secondConfig: '%kernel.project_dir%/public/second_build'

Also define the asset manifests for each build:

.. code-block:: yaml

    # config/packages/assets.yaml
    framework:
        assets:
            packages:
                first_build:
                    json_manifest_path: '%kernel.project_dir%/public/first_build/manifest.json'
                second_build:
                    json_manifest_path: '%kernel.project_dir%/public/second_build/manifest.json'

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

The reason behind that message is that Encore needs to know a few things before
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
  - ``images`` (but use ``configureImageRule()`` instead)
  - ``fonts`` (but use ``configureFontRule()`` instead)
  - ``sass`` (alias ``scss``)
  - ``less``
  - ``stylus``
  - ``svelte``
  - ``vue``
  - ``eslint``
  - ``typescript`` (alias ``ts``)
  - ``handlebars``

Configuring Aliases When Importing or Requiring Modules
-------------------------------------------------------

The `Webpack resolve.alias option`_ allows to create aliases to simplify the
``import`` or ``require`` of certain modules (e.g. by aliasing commonly used ``src/``
folders). In Webpack Encore you can use this option via the ``addAliases()`` method:

.. code-block:: javascript

    Encore.addAliases({
        Utilities: path.resolve(__dirname, 'src/utilities/'),
        Templates: path.resolve(__dirname, 'src/templates/')
    })

With the above config, you could now import certain modules more concisely:

.. code-block:: diff

    -import Utility from '../../utilities/utility';
    +import Utility from 'Utilities/utility';

Excluding Some Dependencies from Output Bundles
-----------------------------------------------

The `Webpack externals option`_ allows to prevent bundling of certain imported
packages and instead retrieve those external dependencies at runtime. This feature
is mostly useful for JavaScript library developers, so you probably won't need it.

In Webpack Encore you can use this option via the ``addExternals()`` method:

.. code-block:: javascript

    // this won't include jQuery and React in the output bundles generated
    // by Webpack Encore. You'll need to load those dependencies yourself
    // (e.g. with a `<script>` tag) to make the application or website work.
    Encore.addExternals({
        jquery: 'jQuery',
        react: 'react'
    })

.. _`configuration options`: https://webpack.js.org/configuration/
.. _`array of configurations`: https://webpack.js.org/configuration/configuration-types/#exporting-multiple-configurations
.. _`Karma`: https://karma-runner.github.io
.. _`Watching Options`: https://webpack.js.org/configuration/watch/#watchoptions
.. _`Webpack resolve.alias option`: https://webpack.js.org/configuration/resolve/#resolvealias
.. _`Webpack externals option`: https://webpack.js.org/configuration/externals/
