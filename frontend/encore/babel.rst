Configuring Babel
=================

`Babel`_ is automatically configured for all ``.js`` and ``.jsx`` files via the
``babel-loader`` with sensible defaults (e.g. with the ``env`` preset and
``react`` if requested).

Need to extend the Babel configuration further? The easiest way is via
``configureBabel()``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...
        
        // first, install any presets you want to use (e.g. yarn add babel-preset-es2017)
        // then, modify the default Babel configuration
        .configureBabel(function(babelConfig) {
            // add additional presets
            babelConfig.presets.push('es2017');

            // no plugins are added by default, but you can add some
            // babelConfig.plugins.push('styled-jsx/babel');
        })
    ;

Creating a .babelrc File
------------------------

Instead of calling ``configureBabel()``, you could create a ``.babelrc`` file
at the root of your project. This is a more "standard" way of configuring
Babel, but it has a downside: as soon as a ``.babelrc`` file is present,
**Encore can no longer add any Babel configuration for you**. For example,
if you call ``Encore.enableReactPreset()``, the ``react`` preset will *not*
automatically be added to Babel: you must add it yourself in ``.babelrc``.

An example ``.babelrc`` file might look like this:

.. code-block:: json

    {
        presets: [
            ['env', {
                modules: false,
                targets: {
                    browsers: '> 1%',
                    uglify: true
                },
                useBuiltIns: true
            }]
        ]
    }

.. _`Babel`: http://babeljs.io/
