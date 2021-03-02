PostCSS and autoprefixing (postcss-loader)
==========================================

`PostCSS`_ is a CSS post-processing tool that can transform your CSS in a lot
of cool ways, like `autoprefixing`_, `linting`_ and more!

First, download ``postcss-loader`` and any plugins you want, like ``autoprefixer``:

.. code-block:: terminal

    $ yarn add postcss-loader autoprefixer --dev

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

Then, enable the loader in Encore!

.. code-block:: diff

      // webpack.config.js

      Encore
          // ...
    +     .enablePostCssLoader()
      ;

Because you just modified ``webpack.config.js``, stop and restart Encore.

That's it! The ``postcss-loader`` will now be used for all CSS, Sass, etc files.
You can also pass options to the `postcss-loader`_ by passing a callback:

.. code-block:: diff

      // webpack.config.js
    + const path = require('path');

      Encore
          // ...
    +     .enablePostCssLoader((options) => {
    +         options.postcssOptions = {
    +             // the directory where the postcss.config.js file is stored
    +             config: path.resolve(__dirname, 'sub-dir', 'custom.config.js'),
    +         };
    +     })
      ;

.. _browserslist_package_config:

Adding browserslist to ``package.json``
---------------------------------------

The ``autoprefixer`` (and many other tools) need to know what browsers you want to
support. The best-practice is to configure this directly in your ``package.json``
(so that all the tools can read this):

.. code-block:: diff

      {
    +  "browserslist": [
    +    "defaults"
    +  ]
      }

The ``defaults`` option is recommended for most users and would be equivalent
to the following browserslist:

.. code-block:: diff

      {
    +  "browserslist": [
    +    "> 0.5%",
    +    "last 2 versions",
    +    "Firefox ESR",
    +    "not dead"
    +  ]
      }

See `browserslist`_ for more details on the syntax.

.. _`PostCSS`: https://postcss.org/
.. _`autoprefixing`: https://github.com/postcss/autoprefixer
.. _`linting`: https://stylelint.io/
.. _`browserslist`: https://github.com/browserslist/browserslist
.. _`postcss-loader`: https://github.com/postcss/postcss-loader
