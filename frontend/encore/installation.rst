Installing Encore
=================

First, make sure you `install Node.js`_ and also the `Yarn package manager`_.
The following instructions depend on whether you are installing Encore in a
Symfony application or not.

Installing Encore in Symfony Applications
-----------------------------------------

Run these commands to install both the PHP and JavaScript dependencies in your
project:

.. code-block:: terminal

    $ composer require symfony/webpack-encore-bundle
    $ yarn install

If you are using :ref:`Symfony Flex <symfony-flex>`, this will install and enable
the `WebpackEncoreBundle`_, create the ``assets/`` directory, add a
``webpack.config.js`` file, and add ``node_modules/`` to ``.gitignore``. You can
skip the rest of this article and go write your first JavaScript and CSS by
reading :doc:`/frontend/encore/simple-example`!

If you are not using Symfony Flex, you'll need to create all these directories
and files by yourself following the instructions shown in the next section.

Installing Encore in non Symfony Applications
---------------------------------------------

Install Encore into your project via Yarn:

.. code-block:: terminal

    $ yarn add @symfony/webpack-encore --dev

    # if you prefer npm, run this command instead:
    $ npm install @symfony/webpack-encore --save-dev

This command creates (or modifies) a ``package.json`` file and downloads
dependencies into a ``node_modules/`` directory. Yarn also creates/updates a
``yarn.lock`` (called ``package-lock.json`` if you use npm).

.. tip::

    You *should* commit ``package.json`` and ``yarn.lock`` (or ``package-lock.json``
    if using npm) to version control, but ignore ``node_modules/``.

Creating the webpack.config.js File
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, create a new ``webpack.config.js`` file at the root of your project. This
is the main config file for both Webpack and Webpack Encore:

.. code-block:: javascript

    const Encore = require('@symfony/webpack-encore');

    // Manually configure the runtime environment if not already configured yet by the "encore" command.
    // It's useful when you use tools that rely on webpack.config.js file.
    if (!Encore.isRuntimeEnvironmentConfigured()) {
        Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
    }

    Encore
        // directory where compiled assets will be stored
        .setOutputPath('public/build/')
        // public path used by the web server to access the output path
        .setPublicPath('/build')
        // only needed for CDN's or sub-directory deploy
        //.setManifestKeyPrefix('build/')

        /*
         * ENTRY CONFIG
         *
         * Add 1 entry for each "page" of your app
         * (including one that's included on every page - e.g. "app")
         *
         * Each entry will result in one JavaScript file (e.g. app.js)
         * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
         */
        .addEntry('app', './assets/app.js')
        //.addEntry('page1', './assets/page1.js')
        //.addEntry('page2', './assets/page2.js')

        // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
        .splitEntryChunks()

        // will require an extra script tag for runtime.js
        // but, you probably want this, unless you're building a single-page app
        .enableSingleRuntimeChunk()

        /*
         * FEATURE CONFIG
         *
         * Enable & configure other features below. For a full
         * list of features, see:
         * https://symfony.com/doc/current/frontend.html#adding-more-features
         */
        .cleanupOutputBeforeBuild()
        .enableBuildNotifications()
        .enableSourceMaps(!Encore.isProduction())
        // enables hashed filenames (e.g. app.abc123.css)
        .enableVersioning(Encore.isProduction())

        // enables @babel/preset-env polyfills
        .configureBabelPresetEnv((config) => {
            config.useBuiltIns = 'usage';
            config.corejs = 3;
        })

        // enables Sass/SCSS support
        //.enableSassLoader()

        // uncomment if you use TypeScript
        //.enableTypeScriptLoader()

        // uncomment to get integrity="..." attributes on your script & link tags
        // requires WebpackEncoreBundle 1.4 or higher
        //.enableIntegrityHashes(Encore.isProduction())

        // uncomment if you're having problems with a jQuery plugin
        //.autoProvidejQuery()

        // uncomment if you use API Platform Admin (composer require api-admin)
        //.enableReactPreset()
        //.addEntry('admin', './assets/admin.js')
    ;

    module.exports = Encore.getWebpackConfig();

Next, open the new ``assets/app.js`` file which contains some JavaScript code
*and* imports some CSS:

.. code-block:: javascript

    // assets/app.js
    /*
     * Welcome to your app's main JavaScript file!
     *
     * We recommend including the built version of this JavaScript file
     * (and its CSS file) in your base layout (base.html.twig).
     */

    // any CSS you import will output into a single css file (app.css in this case)
    import './styles/app.css';

    // Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
    // import $ from 'jquery';

    console.log('Hello Webpack Encore! Edit me in assets/app.js');

And the new ``assets/styles/app.css`` file:

.. code-block:: css

    /* assets/styles/app.css */
    body {
        background-color: lightgray;
    }

You'll customize and learn more about these file in :doc:`/frontend/encore/simple-example`.

.. caution::

    Some of the documentation will use features that are specific to Symfony or
    Symfony's `WebpackEncoreBundle`_. These are optional, and are special ways
    of pointing to the asset paths generated by Encore that enable features like
    :doc:`versioning </frontend/encore/versioning>` and
    :doc:`split chunks </frontend/encore/split-chunks>`.

.. _`install Node.js`: https://nodejs.org/en/download/
.. _`Yarn package manager`: https://yarnpkg.com/getting-started/install
.. _`WebpackEncoreBundle`: https://github.com/symfony/webpack-encore-bundle
