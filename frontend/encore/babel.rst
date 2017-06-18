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
        
        // make sure to install any presets you want to use
        // yarn add babel-preset-es2017
        // modify the default Babel configuration
        .configureBabel(function(babelConfig) {
            babelConfig.presets.push('es2017');
        })
    ;

You can also create a standard ``.babelrc`` file at the root of your project.
Just make sure to configure it with all the presets you need: as soon as a
``.babelrc`` is present, Encore can no longer add *any* Babel configuration for
you!

.. _`Babel`: http://babeljs.io/
