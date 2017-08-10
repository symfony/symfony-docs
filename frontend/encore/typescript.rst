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

Loader usage can be checked better in its `README`_ documentation.

    "Use webpack like normal, including ``webpack --watch`` and ``webpack-dev-server``,
    or through another build system using the Node.js API."

    -- Running section of ts-loader documentation


Checking types in a separate process
------------------------------------

In order to get `Faster builds`_ it is possible to enable the `fork-ts-checker-webpack-plugin`_ plugin.

.. code-block:: javascript

    .enableForkedTypeScriptTypesChecking(function (forkedTypesCheckOptions) {
        forkedTypesCheckOptions.tsconfig = './tsconfig.json',
        forkedTypesCheckOptions.tslint = './tslint.json',
    });

.. _`TypeScript`: https://www.typescriptlang.org/
.. _`ts-loader options`: https://github.com/TypeStrong/ts-loader#options
.. _`README`: https://github.com/TypeStrong/ts-loader#typescript-loader-for-webpack
.. _`Faster builds`: https://github.com/TypeStrong/ts-loader#faster-builds
.. _`fork-ts-checker-webpack-plugin`: https://github.com/Realytics/fork-ts-checker-webpack-plugin
