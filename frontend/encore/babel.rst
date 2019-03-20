Configuring Babel
=================

`Babel`_ is automatically configured for all ``.js`` and ``.jsx`` files via the
``babel-loader`` with sensible defaults (e.g. with the ``@babel/preset-env`` and
``@babel/preset-react`` if requested).

Need to extend the Babel configuration further? The easiest way is via
``configureBabel()``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        .configureBabel(function(babelConfig) {
            // add additional presets
            babelConfig.presets.push('@babel/preset-flow');

            // no plugins are added by default, but you can add some
            babelConfig.plugins.push('styled-jsx/babel');
        }, {
            // node_modules is not processed through Babel by default
            // but you can whitelist specific modules to process
            include_node_modules: ['foundation-sites'],

            // or completely control the exclude rule (note that you
            // can't use both "include_node_modules" and "exclude" at
            // the same time)
            exclude: /bower_components/
        })
    ;

Configuring Browser Targets
---------------------------

The ``@babel/preset-env`` preset rewrites your JavaScript so that the final syntax
will work in whatever browsers you want. To configure the browsers that you need
to support, see :ref:`browserslist_package_config`.

After changing your "browserslist" config, you will need to manually remove the babel
cache directory:

.. code-block:: terminal

    $ On Unix run this command. On Windows, clear this directory manually
    $ rm -rf node_modules/.cache/babel-loader/

Creating a .babelrc File
------------------------

Instead of calling ``configureBabel()``, you could create a ``.babelrc`` file
at the root of your project. This is a more "standard" way of configuring
Babel, but it has a downside: as soon as a ``.babelrc`` file is present,
**Encore can no longer add any Babel configuration for you**. For example,
if you call ``Encore.enableReactPreset()``, the ``react`` preset will *not*
automatically be added to Babel: you must add it yourself in ``.babelrc``.

As soon as a ``.babelrc`` file is present, it will take priority over the Babel
configuration added by Encore.

.. _`Babel`: http://babeljs.io/
