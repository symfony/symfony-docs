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

That's it! The ``postcss-loader`` will now be used for all CSS, Sass, etc
files.

.. _`PostCSS`: http://postcss.org/
.. _`autoprefixing`: https://github.com/postcss/autoprefixer
.. _`linting`: https://stylelint.io/
