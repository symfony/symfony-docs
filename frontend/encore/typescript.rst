Enabling TypeScript (ts-loader)
===============================

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

Then restart Encore. When you do, it will give you a command you can run to
install any missing dependencies. After running that command and restarting
Encore, you're done!

Any ``.ts`` files that you require will be processed correctly. You can
also configure the `ts-loader options`_ via the ``enableTypeScriptLoader()``
method. See the `Encore's index.js file`_ for detailed documentation.

If React is enabled (``.enableReactPreset()``), any ``.tsx`` file will also be
processed by ``ts-loader``.

.. _`TypeScript`: https://www.typescriptlang.org/
.. _`ts-loader options`: https://github.com/TypeStrong/ts-loader#options
.. _`Encore's index.js file`: https://github.com/symfony/webpack-encore/blob/master/index.js
