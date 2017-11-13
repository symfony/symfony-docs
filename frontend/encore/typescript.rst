Enabling TypeScript (ts-loader)
===============================

Want to use `TypeScript`_? No problem! First, install the dependencies:

.. code-block:: terminal

    $ yarn add --dev typescript ts-loader

Then, activate the ``ts-loader`` in ``webpack.config.js``:

.. code-block:: diff

    // webpack.config.js
    // ...

    Encore
        // ...
        .addEntry('main', './assets/main.ts')

        .enableTypeScriptLoader()
    ;

That's it! Any ``.ts`` files that you require will be processed correctly. You can
also configure the `ts-loader options`_ via a callback:

.. code-block:: javascript

    .enableTypeScriptLoader(function (typeScriptConfigOptions) {
        typeScriptConfigOptions.transpileOnly = true;
        typeScriptConfigOptions.configFileName = '/path/to/tsconfig.json';
    });

If React assets are enabled (``.enableReactPreset()``), any ``.tsx`` file will be
processed as well by ``ts-loader``.

More information about the ``ts-loader`` can be found in its `README`_.

Faster Builds with fork-ts-checker-webpack-plugin
-------------------------------------------------

By using `fork-ts-checker-webpack-plugin`_, you can run type checking in a separate
process, which can speedup compile time. To enable it, install the plugin:

.. code-block:: terminal

    $ yarn add --dev fork-ts-checker-webpack-plugin

Then enable it by calling:

.. code-block:: diff

    // webpack.config.js

    Encore
        // ...
        enableForkedTypeScriptTypesChecking()
    ;

This plugin requires that you have a `tsconfig.json`_ file that is setup correctly.

.. _`TypeScript`: https://www.typescriptlang.org/
.. _`ts-loader options`: https://github.com/TypeStrong/ts-loader#options
.. _`README`: https://github.com/TypeStrong/ts-loader#typescript-loader-for-webpack
.. _`fork-ts-checker-webpack-plugin`: https://www.npmjs.com/package/fork-ts-checker-webpack-plugin
.. _`tsconfig.json`: https://www.npmjs.com/package/fork-ts-checker-webpack-plugin#modules-resolution
