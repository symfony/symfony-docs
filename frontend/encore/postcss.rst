PostCSS and autoprefixing (postcss-loader)
==========================================

`PostCSS`_ is a CSS post-processing tool that can transform your CSS in a lot
of cool ways, like `autoprefixing`_, `linting`_ and more!

First, download ``postcss-loader`` and any plugins you want, like ``autoprefixer``:

.. code-block:: terminal

    $ yarn add --dev postcss-loader autoprefixer

Next, create a ``postcss.config.js`` file at the root of your project:

.. code-block:: javascript

    module.exports = {
        plugins: {
            // include whatever plugins you want
            // but make sure you install these via yarn or npm!

            // add browserslist config to package.json (see below)
            autoprefixer: {}
        }
    }

Then, Enable the loader in Encore!

.. code-block:: diff

    // webpack.config.js

    Encore
        // ...
    +     .enablePostCssLoader()
    ;

That's it! The ``postcss-loader`` will now be used for all CSS, Sass, etc files.

Adding browserslist to package.json
-----------------------------------

The ``autoprefixer`` (and many other tools) need to know what browsers you want to
support. The best-practice is to configure this directly in your ``package.json``
(so that all the tools can read this):

.. code-block:: diff

    {
    +     "browserslist": [ "last 2 versions", "ios >= 8" ]
    }

See `browserslist`_ for more details on the syntax.

.. note::

    Encore uses `babel-preset-env`_, which *also* needs to know which browsers you
    want to support. But this does *not* read the ``browserslist`` config key. You
    must configure the browsers separately via :doc:`configureBabel() </frontend/encore/babel>`.

.. _`PostCSS`: http://postcss.org/
.. _`autoprefixing`: https://github.com/postcss/autoprefixer
.. _`linting`: https://stylelint.io/
.. _`browserslist`: https://github.com/ai/browserslist
.. _`babel-preset-env`: https://github.com/babel/babel-preset-env