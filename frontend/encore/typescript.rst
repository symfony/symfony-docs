Enabling TypeScript (ts-loader) with Webpack Encore
===================================================

Want to use `TypeScript`_? No problem! First, enable it:

.. code-block:: diff

      // webpack.config.js

      // ...
      Encore
          // ...
    +     .addEntry('main', './assets/main.ts')

    +     .enableTypeScriptLoader()

          // optionally enable forked type script for faster builds
          // https://www.npmjs.com/package/fork-ts-checker-webpack-plugin
          // requires that you have a tsconfig.json file that is setup correctly.
    +     //.enableForkedTypeScriptTypesChecking()
      ;

Then create an empty ``tsconfig.json`` file with the contents ``{}`` in the project
root folder (or in the folder where your TypeScript files are located; e.g. ``assets/``).
In ``tsconfig.json`` you can define more options, as shown in `tsconfig.json reference`_.

Then restart Encore. When you do, it will give you a command you can run to
install any missing dependencies. After running that command and restarting
Encore, you're done!

Any ``.ts`` files that you require will be processed correctly. You can
also configure the `ts-loader options`_ via the ``enableTypeScriptLoader()``
method.

.. code-block:: diff

      // webpack.config.js
      Encore
          // ...
          .addEntry('main', './assets/main.ts')

    -     .enableTypeScriptLoader()
    +     .enableTypeScriptLoader(function(tsConfig) {
    +         // You can use this callback function to adjust ts-loader settings
    +         // https://github.com/TypeStrong/ts-loader/blob/master/README.md#loader-options
    +         // For example:
    +         // tsConfig.silent = false
    +     })

              // ...
      ;

See the `Encore's index.js file`_ for detailed documentation and check
out the `tsconfig.json reference`_ and the `Webpack guide about Typescript`_.

If React is enabled (``.enableReactPreset()``), any ``.tsx`` file will also be
processed by ``ts-loader``.

.. _`TypeScript`: https://www.typescriptlang.org/
.. _`ts-loader options`: https://github.com/TypeStrong/ts-loader#options
.. _`Encore's index.js file`: https://github.com/symfony/webpack-encore/blob/master/index.js
.. _`tsconfig.json reference`: https://www.typescriptlang.org/docs/handbook/tsconfig-json.html
.. _`Webpack guide about Typescript`: https://webpack.js.org/guides/typescript/
