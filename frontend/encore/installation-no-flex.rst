Encore Installation (without Symfony Flex)
==========================================

.. tip::

    If your project uses Symfony Flex, read :doc:`/frontend/encore/installation`
    for easier instructions.

Installing Encore
-----------------

Install Encore into your project via Yarn:

.. code-block:: terminal

    $ yarn add @symfony/webpack-encore --dev

.. note::

    If you prefer to use `npm`_, no problem! Run ``npm install @symfony/webpack-encore --save-dev``. 

This command creates (or modifies) a ``package.json`` file and downloads dependencies
into a ``node_modules/`` directory. Yarn also creates/updates a ``yarn.lock``
(called ``package-lock.json`` if you use npm version 5+).

.. tip::

    You *should* commit ``package.json`` and ``yarn.lock`` (or ``package-lock.json``
    if using npm 5) to version control, but ignore ``node_modules/``.

Creating the webpack.config.js File
-----------------------------------

Next, create a new ``webpack.config.js`` file at the root of your project:

.. code-block:: js

    var Encore = require('@symfony/webpack-encore');

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
         * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
         */
        .addEntry('app', './assets/js/app.js')
        //.addEntry('page1', './assets/js/page1.js')
        //.addEntry('page2', './assets/js/page2.js')

        .cleanupOutputBeforeBuild()
        .enableSourceMaps(!Encore.isProduction())
        // enables hashed filenames (e.g. app.abc123.css)
        .enableVersioning(Encore.isProduction())

        // uncomment if you use TypeScript
        //.enableTypeScriptLoader()

        // uncomment if you use Sass/SCSS files
        //.enableSassLoader()

        // uncomment if you're having problems with a jQuery plugin
        //.autoProvidejQuery()
    ;

    module.exports = Encore.getWebpackConfig();

Next, create a new ``assets/js/app.js`` file with some basic JavaScript *and*
import some JavaScript:

.. code-block:: javascript

    // assets/js/app.js
    
    require('../css/app.css');
    
    console.log('Hello Webpack Encore');

And the new ``assets/css/app.css`` file:

.. code-block:: css

    // assets/css/app.css
    body {
        background-color: lightgray;
    }

You'll customize and learn more about these file in :doc:`/frontend/encore/simple-example`.

.. _`npm`: https://www.npmjs.com/
